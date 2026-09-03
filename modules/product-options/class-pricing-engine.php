<?php
if (!defined('ABSPATH')) exit;

/**
 * Server-side pricing engine for Product Options.
 *
 * The browser may display prices for UX, but this class is the only
 * authority used to calculate the extra amount that reaches the cart.
 */
class EzLens_Product_Options_Pricing_Engine {
    private static $instance = null;
    private $manager;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();

        // Capture the real product price before WooCommerce starts calculating totals.
        add_filter('woocommerce_add_cart_item_data', [$this, 'capture_base_price'], 20, 3);

        // Apply the server-calculated option price to the cart item.
        add_action('woocommerce_before_calculate_totals', [$this, 'apply_cart_prices'], 20);
    }

    /**
     * Store the product price as it was when the item entered the cart.
     */
    public function capture_base_price($cart_item_data, $product_id, $variation_id) {
        $product = wc_get_product($variation_id ?: $product_id);
        if (!$product) {
            return $cart_item_data;
        }

        $options = isset($cart_item_data['ezlens_options']) && is_array($cart_item_data['ezlens_options'])
            ? $cart_item_data['ezlens_options']
            : [];

        if (empty($options) && empty($cart_item_data['ezlens_product_id'])) {
            return $cart_item_data;
        }

        $cart_item_data['ezlens_price_base'] = $this->format_price($product->get_price());

        $template_product_id = !empty($cart_item_data['ezlens_product_id'])
            ? absint($cart_item_data['ezlens_product_id'])
            : absint($product_id);

        if ($template_product_id) {
            $cart_item_data['ezlens_price_extra'] = $this->calculate_extra_price($options, $template_product_id);
        }

        return $cart_item_data;
    }

    /**
     * Recalculate and apply the authoritative server-side price.
     */
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

            $product_id = !empty($cart_item['ezlens_product_id'])
                ? absint($cart_item['ezlens_product_id'])
                : 0;

            if (!$product_id && !empty($cart_item['product_id'])) {
                $product_id = absint($cart_item['product_id']);
            }

            if (!$product_id || empty($cart_item['data']) || !is_object($cart_item['data'])) {
                continue;
            }

            $extra = $this->calculate_extra_price($cart_item['ezlens_options'], $product_id);

            // Keep the authoritative amount in the cart item as well.
            $cart->cart_contents[$cart_item_key]['ezlens_price_extra'] = $extra;

            $base = isset($cart_item['ezlens_price_base'])
                ? $cart_item['ezlens_price_base']
                : $cart_item['data']->get_price();

            $base = $this->format_price($base);
            $final_price = $this->format_price($base + $extra);

            $cart_item['data']->set_price($final_price);
        }
    }

    /**
     * Calculate extra price exclusively from the saved template schema and
     * the submitted/sanitized option values. Never trusts client-side prices.
     */
    public function calculate_extra_price($options, $product_id) {
        if (!is_array($options) || !$product_id) {
            return 0;
        }

        $template = $this->manager->get_template_for_product($product_id);
        if (!$template) {
            return 0;
        }

        $fields = $template['fields'] ?? [];
        if (!is_array($fields)) {
            return 0;
        }

        return $this->calculate_fields_price($fields, $options);
    }

    private function calculate_fields_price($fields, $options) {
        $total = 0;

        foreach ($fields as $key => $field) {
            if (!is_array($field) || strpos((string) $key, '_code_') === 0) {
                continue;
            }

            $type = isset($field['type']) ? sanitize_key($field['type']) : 'text';
            $value = $options[$key] ?? null;

            // Empty values do not activate a field-level price.
            if ($this->is_empty_value($value)) {
                continue;
            }

            $field_price = isset($field['price']) ? $this->positive_price($field['price']) : 0;
            $total += $field_price;

            if (in_array($type, ['select', 'radio', 'checkbox', 'image_select'], true)) {
                $total += $this->calculate_option_price($field['options'] ?? [], $value, $type);
            }

            // Groups can contain independently priced child fields.
            if (!empty($field['children']) && is_array($field['children'])) {
                $total += $this->calculate_fields_price($field['children'], $options);
            }
        }

        return $this->format_price($total);
    }

    private function calculate_option_price($options, $value, $type) {
        if (!is_array($options)) {
            return 0;
        }

        $selected_values = $type === 'checkbox'
            ? (is_array($value) ? $value : [$value])
            : [$value];

        $total = 0;

        foreach ($selected_values as $selected) {
            foreach ($options as $option) {
                if (!is_array($option)) {
                    continue;
                }

                $option_value = isset($option['value']) ? (string) $option['value'] : '';
                if ($option_value === '' || (string) $selected !== $option_value) {
                    continue;
                }

                $total += isset($option['price']) ? $this->positive_price($option['price']) : 0;
                break;
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
        if (is_array($value)) {
            return empty($value);
        }

        return $value === null || $value === '';
    }
}

EzLens_Product_Options_Pricing_Engine::get_instance();
