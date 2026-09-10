<?php
/**
 * Shared helpers for field renderers.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Field_Render_Helpers
 */
class EzLens_PO_Field_Render_Helpers {

    /**
     * @param float $amount
     * @return string
     */
    public static function price_label($amount) {
        if (class_exists('EzLens_Product_Options_Helpers')) {
            return EzLens_Product_Options_Helpers::format_price_fa($amount);
        }
        if (function_exists('wc_price')) {
            return wp_strip_all_tags(wc_price($amount));
        }
        return (string) $amount;
    }

    /**
     * @param string $key
     * @param int    $template_id
     * @return string
     */
    public static function field_id($key, $template_id) {
        return 'ezlens_field_' . absint($template_id) . '_' . sanitize_key($key);
    }

    /**
     * @param string $key
     * @param int    $template_id
     * @return string
     */
    public static function field_name($key, $template_id) {
        return 'ezlens_options[' . absint($template_id) . '][' . sanitize_key($key) . ']';
    }

    /**
     * @param array $field
     * @return array{label:string,placeholder:string,required:bool,price:float,options:array,settings:array,description:string}
     */
    public static function normalize(array $field) {
        return array(
            'label'       => sanitize_text_field($field['label'] ?? ''),
            'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
            'required'    => !empty($field['required']),
            'price'       => isset($field['price']) ? max(0, (float) $field['price']) : 0,
            'options'     => is_array($field['options'] ?? null) ? $field['options'] : array(),
            'settings'    => is_array($field['settings'] ?? null) ? $field['settings'] : array(),
            'description' => sanitize_textarea_field($field['description'] ?? ''),
            'type'        => sanitize_key($field['type'] ?? 'text'),
        );
    }

    /**
     * Open field wrapper.
     *
     * @param string $key
     * @param int    $template_id
     * @param array  $meta from normalize()
     * @param string $extra_class
     */
    public static function open_wrapper($key, $template_id, array $meta, $extra_class = '') {
        $type = $meta['type'];
        $class = 'ezlens-field-wrapper ezlens-field-type-' . $type;
        if ($extra_class !== '') {
            $class .= ' ' . $extra_class;
        }
        $cond = '';
        // conditions passed separately if needed
        echo '<div class="' . esc_attr($class) . '" data-field-key="' . esc_attr($key) . '" data-template-id="' . esc_attr((string) absint($template_id)) . '" data-field-type="' . esc_attr($type) . '">';
    }

    /**
     * @param array $meta
     * @param string $id
     * @param bool   $skip_label
     */
    public static function label(array $meta, $id, $skip_label = false) {
        if ($skip_label) {
            return;
        }
        $star = !empty($meta['required']) ? ' <span class="required">*</span>' : '';
        echo '<label class="ezlens-field-label" for="' . esc_attr($id) . '">' . esc_html($meta['label']) . $star . '</label>';
    }

    /**
     * @param array $meta
     */
    public static function description(array $meta) {
        if ($meta['description'] === '') {
            return;
        }
        echo '<p class="ezlens-field-desc">' . esc_html($meta['description']) . '</p>';
    }

    public static function close_wrapper() {
        echo '</div>';
    }
}
