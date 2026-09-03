<?php
if (!defined('ABSPATH')) exit;

/**
 * WooCommerce integration guard for Product Options.
 * Keeps validation authoritative on the server before an item reaches the cart.
 */
class EzLens_Product_Options_WooCommerce_Integration {
    private static $instance = null;
    private $manager;
    private const OPERATORS = ['equals','not_equals','contains','not_contains','greater_than','less_than','greater_or_equal','less_or_equal','empty','not_empty'];

    public static function get_instance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();
        add_filter('woocommerce_add_to_cart_validation', [$this, 'validate'], 20, 5);
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

    private function sanitize_tree($raw, $fields, $prefix = '') {
        $out = [];
        foreach ($fields as $key => $field) {
            if (!is_array($field) || strpos((string)$key, '_code_') === 0) continue;
            $full = $prefix !== '' ? $prefix . '_' . $key : (string)$key;
            if (array_key_exists($full, $raw)) {
                $value = $this->sanitize_value($raw[$full], $field);
                if ($value !== '' && $value !== []) $out[$full] = $value;
            }
            if (!empty($field['children']) && is_array($field['children'])) {
                $out = array_merge($out, $this->sanitize_tree($raw, $field['children'], $full));
            }
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
            if (!$this->conditions_match($conditions, $options)) continue;

            $value = $options[$full] ?? '';
            if (!empty($field['required']) && $this->empty_value($value)) {
                wc_add_notice(sprintf('لطفاً فیلد «%s» را تکمیل کنید.', sanitize_text_field($field['label'] ?? $full)), 'error');
                $passed = false;
            }
            if (!empty($field['children']) && is_array($field['children'])) {
                $this->validate_fields($field['children'], $options, $full, $passed);
            }
        }
    }

    private function conditions_match($conditions, $options) {
        if (!is_array($conditions) || empty($conditions['rules']) || !is_array($conditions['rules'])) return true;
        $results = [];
        foreach ($conditions['rules'] as $rule) {
            if (!is_array($rule)) continue;
            $field = sanitize_key($rule['field'] ?? $rule['field_key'] ?? '');
            $operator = sanitize_key($rule['operator'] ?? 'equals');
            if (!$field || !in_array($operator, self::OPERATORS, true)) continue;
            $actual = $options[$field] ?? '';
            $expected = $rule['value'] ?? '';
            if (is_array($actual)) {
                $actual = array_map('strval', $actual);
                if ($operator === 'equals' || $operator === 'contains') $results[] = in_array((string)$expected, $actual, true);
                elseif ($operator === 'not_equals' || $operator === 'not_contains') $results[] = !in_array((string)$expected, $actual, true);
                else $actual = implode(',', $actual);
            }
            if (!is_bool($results[count($results)-1] ?? null) || count($results) === 0 || (is_array($actual) === false && !is_bool(end($results)))) {
                $a = is_array($actual) ? implode(',', $actual) : (string)$actual;
                $e = is_array($expected) ? implode(',', $expected) : (string)$expected;
                switch ($operator) {
                    case 'not_equals': $results[] = $a !== $e; break;
                    case 'contains': $results[] = strpos($a, $e) !== false; break;
                    case 'not_contains': $results[] = strpos($a, $e) === false; break;
                    case 'greater_than': $results[] = is_numeric($a) && is_numeric($e) && (float)$a > (float)$e; break;
                    case 'less_than': $results[] = is_numeric($a) && is_numeric($e) && (float)$a < (float)$e; break;
                    case 'greater_or_equal': $results[] = is_numeric($a) && is_numeric($e) && (float)$a >= (float)$e; break;
                    case 'less_or_equal': $results[] = is_numeric($a) && is_numeric($e) && (float)$a <= (float)$e; break;
                    case 'empty': $results[] = $a === ''; break;
                    case 'not_empty': $results[] = $a !== ''; break;
                    case 'equals': $results[] = $a === $e; break;
                }
            }
        }
        if (!$results) return true;
        return ($conditions['logic'] ?? 'all') === 'any' ? in_array(true, $results, true) : !in_array(false, $results, true);
    }

    private function empty_value($value) { return is_array($value) ? empty($value) : ($value === null || $value === ''); }
}

EzLens_Product_Options_WooCommerce_Integration::get_instance();
