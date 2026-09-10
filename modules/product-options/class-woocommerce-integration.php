<?php
/**
 * Server-side WooCommerce validation for Product Options (multi-template).
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

use EzLens\ProductOptions\Services\ConditionEvaluator;

/**
 * Class EzLens_Product_Options_WooCommerce_Integration
 */
class EzLens_Product_Options_WooCommerce_Integration {

    /** @var self|null */
    private static $instance = null;

    /** @var EzLens_Product_Options_Template_Manager */
    private $manager;

    /** @var ConditionEvaluator|null */
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

        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate'), 20, 5);
        // Cart enrichment is owned by FieldRenderer (priority 10); keep a safety net at 15.
        add_filter('woocommerce_add_cart_item_data', array($this, 'enrich_cart_item_data'), 15, 3);
    }

    public function validate($passed, $product_id, $quantity, $variation_id = 0, $variations = array()) {
        $product = wc_get_product($variation_id ?: $product_id);
        $pid     = ($product && $product->is_type('variation')) ? $product->get_parent_id() : absint($product_id);

        $slots = $this->manager->get_slots_service()->get_slots($pid);
        $raw   = isset($_POST['ezlens_options']) && is_array($_POST['ezlens_options'])
            ? wp_unslash($_POST['ezlens_options'])
            : array();

        if (empty($slots)) {
            // Legacy single template.
            $template = $this->manager->get_template_for_product($pid);
            if (!$template || !is_array($template['fields'] ?? null)) {
                return $passed;
            }
            $options = $this->sanitize_tree(is_array($raw) ? $raw : array(), $template['fields']);
            $this->validate_fields($template['fields'], $options, '', $passed, $template['title'] ?? '');
            return $passed;
        }

        foreach ($slots as $slot) {
            $tid = absint($slot['template_id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }
            $template = $this->manager->get($tid);
            if (!$template || !is_array($template['fields'] ?? null)) {
                continue;
            }
            $bucket = array();
            if (isset($raw[ $tid ]) && is_array($raw[ $tid ])) {
                $bucket = $raw[ $tid ];
            } elseif (isset($raw[ (string) $tid ]) && is_array($raw[ (string) $tid ])) {
                $bucket = $raw[ (string) $tid ];
            }
            $options = $this->sanitize_tree($bucket, $template['fields']);
            $label   = ($slot['label'] ?? '') !== '' ? $slot['label'] : ($template['title'] ?? '');
            $this->validate_fields($template['fields'], $options, '', $passed, $label);
        }

        return $passed;
    }

    public function enrich_cart_item_data($cart_item_data, $product_id, $variation_id) {
        // If FieldRenderer already filled options, leave them.
        if (!empty($cart_item_data['ezlens_options'])) {
            return $cart_item_data;
        }

        $product = wc_get_product($variation_id ?: $product_id);
        $pid     = ($product && $product->is_type('variation')) ? $product->get_parent_id() : absint($product_id);

        $raw = isset($_POST['ezlens_options']) && is_array($_POST['ezlens_options'])
            ? wp_unslash($_POST['ezlens_options'])
            : array();
        if (empty($raw)) {
            return $cart_item_data;
        }

        $slots   = $this->manager->get_slots_service()->get_slots($pid);
        $options = array();

        if (empty($slots)) {
            $template = $this->manager->get_template_for_product($pid);
            if ($template && is_array($template['fields'] ?? null)) {
                $options = $this->sanitize_tree($raw, $template['fields']);
            }
        } else {
            foreach ($slots as $slot) {
                $tid = absint($slot['template_id'] ?? 0);
                if ($tid <= 0) {
                    continue;
                }
                $template = $this->manager->get($tid);
                if (!$template) {
                    continue;
                }
                $bucket = array();
                if (isset($raw[ $tid ]) && is_array($raw[ $tid ])) {
                    $bucket = $raw[ $tid ];
                } elseif (isset($raw[ (string) $tid ]) && is_array($raw[ (string) $tid ])) {
                    $bucket = $raw[ (string) $tid ];
                }
                foreach ($this->sanitize_tree($bucket, $template['fields'] ?? array()) as $k => $v) {
                    $options[ $tid . ':' . $k ] = $v;
                }
            }
        }

        if (!$options) {
            return $cart_item_data;
        }
        $cart_item_data['ezlens_options']     = $options;
        $cart_item_data['ezlens_product_id']  = $pid;
        $cart_item_data['ezlens_options_key'] = md5(wp_json_encode($options));
        return $cart_item_data;
    }

    private function sanitize_tree($raw, $fields, $prefix = '') {
        $out = array();
        if (!is_array($fields) || !is_array($raw)) {
            return $out;
        }
        foreach ($fields as $key => $field) {
            if (!is_array($field) || strpos((string) $key, '_code_') === 0) {
                continue;
            }
            $field_key = isset($field['name']) ? sanitize_key($field['name']) : (string) $key;
            $full      = $prefix !== '' ? $prefix . '_' . $field_key : $field_key;
            if (array_key_exists($full, $raw)) {
                $value = $this->sanitize_value($raw[ $full ], $field);
                if ($value !== '' && $value !== array()) {
                    $out[ $full ] = $value;
                }
            }
            if (!empty($field['children']) && is_array($field['children'])) {
                $out = array_merge($out, $this->sanitize_tree($raw, $field['children'], $full));
            }
        }
        return $out;
    }

    private function sanitize_value($value, $field) {
        $type = sanitize_key($field['type'] ?? 'text');
        if (is_array($value)) {
            $value = array_values(array_map('sanitize_text_field', $value));
        } elseif ($type === 'email') {
            $value = sanitize_email($value);
        } elseif ($type === 'number') {
            $value = is_numeric($value) ? (string) (float) $value : '';
        } elseif ($type === 'upload') {
            $value = esc_url_raw($value);
        } else {
            $value = sanitize_text_field($value);
        }
        if (in_array($type, array('select', 'radio', 'checkbox', 'image_select'), true)) {
            $allowed = array();
            foreach (($field['options'] ?? array()) as $option) {
                if (is_array($option) && isset($option['value'])) {
                    $allowed[] = (string) $option['value'];
                }
            }
            if (is_array($value)) {
                $value = array_values(array_intersect(array_map('strval', $value), $allowed));
            } elseif (!in_array((string) $value, $allowed, true)) {
                $value = '';
            }
        }
        return $value;
    }

    private function validate_fields($fields, $options, $prefix, &$passed, $context = '') {
        foreach ($fields as $key => $field) {
            if (!is_array($field) || strpos((string) $key, '_code_') === 0) {
                continue;
            }
            $field_key = isset($field['name']) ? sanitize_key($field['name']) : (string) $key;
            $full      = $prefix !== '' ? $prefix . '_' . $field_key : $field_key;
            $conditions = $field['conditions'] ?? $field['conditional_logic'] ?? array();
            if ($this->conditions && !$this->conditions->matches($conditions, $options)) {
                continue;
            }
            if (!empty($field['required']) && $this->empty_value($options[ $full ] ?? '')) {
                $label = sanitize_text_field($field['label'] ?? $full);
                if ($context !== '') {
                    $label = sanitize_text_field($context) . ' — ' . $label;
                }
                wc_add_notice(sprintf('لطفاً فیلد «%s» را تکمیل کنید.', $label), 'error');
                $passed = false;
            }
            if (!empty($field['children']) && is_array($field['children'])) {
                $this->validate_fields($field['children'], $options, $full, $passed, $context);
            }
        }
    }

    private function empty_value($value) {
        return is_array($value) ? empty($value) : ($value === null || $value === '');
    }
}

EzLens_Product_Options_WooCommerce_Integration::get_instance();
