<?php
if (!defined('ABSPATH')) exit;

use EzLens\ProductOptions\Services\ConditionEvaluator;

/** Server-side WooCommerce integration for Product Options. */
class EzLens_Product_Options_WooCommerce_Integration {
    private static $instance = null;
    private $manager;
    private $conditions;

    public static function get_instance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();
        $this->conditions = new ConditionEvaluator();
        add_filter('woocommerce_add_to_cart_validation', [$this, 'validate'], 20, 5);
        add_filter('woocommerce_add_cart_item_data', [$this, 'enrich_cart_item_data'], 30, 3);
    }

    public function validate($passed, $product_id, $quantity, $variation_id = 0, $variations = []) {
        $product = wc_get_product($variation_id ?: $product_id);
        $template_product_id = ($product && $product->is_type('variation')) ? $product->get_parent_id() : absint($product_id);
        $template = $this->manager->get_template_for_product($template_product_id);
        if (!$template || !is_array($template['fields'] ?? null)) return $passed;
        $raw = isset($_POST['ezlens_options']) && is_array($_POST['ezlens_options']) ? wp_unslash($_POST['ezlens_options']) : [];
        $options = $this->sanitize_tree($raw, $template['fields']);
        $this->validate_fields($template['fields'], $options, '', $passed);
        return $passed;
    }

    public function enrich_cart_item_data($cart_item_data, $product_id, $variation_id) {
        $product = wc_get_product($variation_id ?: $product_id);
        $template_product_id = ($product && $product->is_type('variation')) ? $product->get_parent_id() : absint($product_id);
        $template = $this->manager->get_template_for_product($template_product_id);
        if (!$template || !is_array($template['fields'] ?? null)) return $cart_item_data;
        $raw = isset($_POST['ezlens_options']) && is_array($_POST['ezlens_options']) ? wp_unslash($_POST['ezlens_options']) : [];
        $options = $this->sanitize_tree($raw, $template['fields']);
        if (!$options) return $cart_item_data;
        $cart_item_data['ezlens_options'] = $options;
        $cart_item_data['ezlens_product_id'] = $template_product_id;
        $cart_item_data['ezlens_options_key'] = md5(wp_json_encode($options));
        return $cart_item_data;
    }

    private function sanitize_tree($raw, $fields, $prefix = '') {
        $out = [];
        foreach ($fields as $key => $field) {
            if (!is_array($field) || strpos((string)$key, '_code_') === 0) continue;
            $full = $prefix !== '' ? $prefix . '_' . $key : (string)$key;
            if (array_key_exists($full, $raw)) {
                $value = $this->sanitize_value($raw[$full], $field);
                if ($value !== '' && $value !== []) $out[$full] = $value;
            }
            if (!empty($field['children']) && is_array($field['children'])) $out = array_merge($out, $this->sanitize_tree($raw, $field['children'], $full));
        }
        return $out;
    }

    private function sanitize_value($value, $field) {
        $type = sanitize_key($field['type'] ?? 'text');
        if (is_array($value)) $value = array_values(array_map('sanitize_text_field', $value));
        elseif ($type === 'email') $value = sanitize_email($value);
        elseif ($type === 'number') $value = is_numeric($value) ? (string)(float)$value : '';
        elseif ($type === 'upload') $value = esc_url_raw($value);
        else $value = sanitize_text_field($value);
        if (in_array($type, ['select','radio','checkbox','image_select'], true)) {
            $allowed = [];
            foreach (($field['options'] ?? []) as $option) if (is_array($option) && isset($option['value'])) $allowed[] = (string)$option['value'];
            if (is_array($value)) $value = array_values(array_intersect(array_map('strval', $value), $allowed));
            elseif (!in_array((string)$value, $allowed, true)) $value = '';
        }
        return $value;
    }

    private function validate_fields($fields, $options, $prefix, &$passed) {
        foreach ($fields as $key => $field) {
            if (!is_array($field) || strpos((string)$key, '_code_') === 0) continue;
            $full = $prefix !== '' ? $prefix . '_' . $key : (string)$key;
            $conditions = $field['conditions'] ?? $field['conditional_logic'] ?? [];
            if (!$this->conditions->matches($conditions, $options)) continue;
            if (!empty($field['required']) && $this->empty_value($options[$full] ?? '')) {
                wc_add_notice(sprintf('لطفاً فیلد «%s» را تکمیل کنید.', sanitize_text_field($field['label'] ?? $full)), 'error');
                $passed = false;
            }
            if (!empty($field['children']) && is_array($field['children'])) $this->validate_fields($field['children'], $options, $full, $passed);
        }
    }

    private function empty_value($value) { return is_array($value) ? empty($value) : ($value === null || $value === ''); }
}

EzLens_Product_Options_WooCommerce_Integration::get_instance();
