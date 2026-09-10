<?php
/**
 * Server-side pricing for Product Options (multi-template aware).
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

use EzLens\ProductOptions\Services\ConditionEvaluator;

/**
 * Class EzLens_Product_Options_Pricing_Engine
 */
class EzLens_Product_Options_Pricing_Engine {

    /** @var self|null */
    private static $instance = null;

    /** @var EzLens_Product_Options_Template_Manager */
    private $manager;

    /** @var ConditionEvaluator */
    private $conditions;

    /**
     * @return self
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->manager    = EzLens_Product_Options_Template_Manager::get_instance();
        $this->conditions = class_exists('EzLens\\ProductOptions\\Services\\ConditionEvaluator')
            ? new ConditionEvaluator()
            : null;

        add_filter('woocommerce_add_cart_item_data', array($this, 'capture_base_price'), 20, 3);
        add_action('woocommerce_before_calculate_totals', array($this, 'apply_cart_prices'), 20);
    }

    public function capture_base_price($cart_item_data, $product_id, $variation_id) {
        $product = wc_get_product($variation_id ?: $product_id);
        if (!$product) {
            return $cart_item_data;
        }

        $options = isset($cart_item_data['ezlens_options']) && is_array($cart_item_data['ezlens_options'])
            ? $cart_item_data['ezlens_options']
            : array();
        if (empty($options) && empty($cart_item_data['ezlens_product_id'])) {
            return $cart_item_data;
        }

        $template_product_id = !empty($cart_item_data['ezlens_product_id'])
            ? absint($cart_item_data['ezlens_product_id'])
            : absint($product_id);

        $cart_item_data['ezlens_price_base']  = $this->format_price($product->get_price());
        $cart_item_data['ezlens_price_extra'] = $this->calculate_extra_price($options, $template_product_id);
        return $cart_item_data;
    }

    public function apply_cart_prices($cart) {
        if (is_admin() && !wp_doing_ajax()) {
            return;
        }
        if (!$cart || !is_a($cart, 'WC_Cart')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (empty($cart_item['ezlens_options']) || !is_array($cart_item['ezlens_options'])) {
                continue;
            }
            if (empty($cart_item['data']) || !is_object($cart_item['data'])) {
                continue;
            }

            $product_id = !empty($cart_item['ezlens_product_id'])
                ? absint($cart_item['ezlens_product_id'])
                : absint($cart_item['product_id'] ?? 0);
            if (!$product_id) {
                continue;
            }

            $extra = $this->calculate_extra_price($cart_item['ezlens_options'], $product_id);
            $cart->cart_contents[ $cart_item_key ]['ezlens_price_extra'] = $extra;

            $base = isset($cart_item['ezlens_price_base'])
                ? (float) $cart_item['ezlens_price_base']
                : (float) $cart_item['data']->get_price();
            $cart_item['data']->set_price($this->format_price($base + $extra));
        }
    }

    /**
     * Options keys may be "tid:field" (multi-slot) or plain field keys (legacy).
     *
     * @param array $options
     * @param int   $product_id
     * @return float
     */
    public function calculate_extra_price($options, $product_id) {
        if (!is_array($options) || !$product_id) {
            return 0;
        }

        $by_template = array();
        $legacy      = array();

        foreach ($options as $key => $value) {
            if (is_string($key) && strpos($key, ':') !== false) {
                list($tid, $fk) = explode(':', $key, 2);
                $tid = absint($tid);
                if ($tid <= 0) {
                    continue;
                }
                if (!isset($by_template[ $tid ])) {
                    $by_template[ $tid ] = array();
                }
                $by_template[ $tid ][ $fk ] = $value;
            } else {
                $legacy[ $key ] = $value;
            }
        }

        $total = 0.0;

        if (!empty($by_template)) {
            foreach ($by_template as $tid => $vals) {
                $template = $this->manager->get($tid);
                if (!$template || !is_array($template['fields'] ?? null)) {
                    continue;
                }
                $total += $this->calculate_fields_price($template['fields'], $vals);
            }
        } elseif (!empty($legacy)) {
            $template = $this->manager->get_template_for_product($product_id);
            if ($template && is_array($template['fields'] ?? null)) {
                $total += $this->calculate_fields_price($template['fields'], $legacy);
            }
        }

        // Also sum any active slots that were not in options but have zero — not needed.

        return $this->format_price($total);
    }

    /**
     * @param array  $fields
     * @param array  $options
     * @param string $prefix
     * @return float
     */
    private function calculate_fields_price($fields, $options, $prefix = '') {
        $total = 0.0;
        foreach ($fields as $key => $field) {
            if (!is_array($field)) {
                continue;
            }
            $field_key = isset($field['name']) ? sanitize_key($field['name']) : (string) $key;
            if ($field_key === '' || strpos((string) $key, '_code_') === 0 || strpos($field_key, '_code_') === 0) {
                continue;
            }

            $lookup_key = $prefix !== '' ? $prefix . '_' . $field_key : $field_key;
            $conditions = $field['conditions'] ?? $field['conditional_logic'] ?? array();
            if ($this->conditions && !$this->conditions->matches($conditions, $options)) {
                continue;
            }

            $value = $options[ $lookup_key ] ?? null;
            if ($this->is_empty_value($value)) {
                // Still process children? only if parent not required empty skip children with prefix
                if (!empty($field['children']) && is_array($field['children'])) {
                    $total += $this->calculate_fields_price($field['children'], $options, $lookup_key);
                }
                continue;
            }

            $type   = sanitize_key($field['type'] ?? 'text');
            $total += isset($field['price']) ? $this->positive_price($field['price']) : 0;

            if (in_array($type, array('select', 'radio', 'checkbox', 'image_select'), true)) {
                $total += $this->calculate_option_price($field['options'] ?? array(), $value, $type);
            }

            if (!empty($field['children']) && is_array($field['children'])) {
                $total += $this->calculate_fields_price($field['children'], $options, $lookup_key);
            }
        }
        return $total;
    }

    private function calculate_option_price($options, $value, $type) {
        if (!is_array($options)) {
            return 0;
        }
        $selected = $type === 'checkbox' ? (is_array($value) ? $value : array($value)) : array($value);
        $total    = 0.0;
        foreach ($selected as $selected_value) {
            foreach ($options as $option) {
                if (!is_array($option)) {
                    continue;
                }
                $option_value = isset($option['value']) ? (string) $option['value'] : '';
                if ($option_value !== '' && (string) $selected_value === $option_value) {
                    $total += isset($option['price']) ? $this->positive_price($option['price']) : 0;
                    break;
                }
            }
        }
        return $total;
    }

    private function positive_price($value) {
        $price = is_numeric($value) ? (float) $value : 0;
        return $price > 0 ? $price : 0;
    }

    private function format_price($value) {
        return (float) wc_format_decimal(max(0, (float) $value), wc_get_price_decimals());
    }

    private function is_empty_value($value) {
        return is_array($value) ? empty($value) : ($value === null || $value === '');
    }
}

EzLens_Product_Options_Pricing_Engine::get_instance();
