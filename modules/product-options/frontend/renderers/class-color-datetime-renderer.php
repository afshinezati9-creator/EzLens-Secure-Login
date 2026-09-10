<?php
/**
 * Color / date / time field renderers.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Color_Datetime_Renderer
 */
class EzLens_PO_Color_Datetime_Renderer {

    /**
     * @param string $key
     * @param array  $field
     * @param int    $template_id
     */
    public static function render($key, array $field, $template_id) {
        $meta = EzLens_PO_Field_Render_Helpers::normalize($field);
        $id   = EzLens_PO_Field_Render_Helpers::field_id($key, $template_id);
        $name = EzLens_PO_Field_Render_Helpers::field_name($key, $template_id);
        $type = $meta['type'];
        $req  = $meta['required'] ? ' required' : '';
        $price = $meta['price'] > 0 ? ' data-price="' . esc_attr((string) $meta['price']) . '"' : '';

        EzLens_PO_Field_Render_Helpers::open_wrapper($key, $template_id, $meta);
        EzLens_PO_Field_Render_Helpers::label($meta, $id);

        if ($type === 'color') {
            $default = sanitize_hex_color($meta['settings']['default'] ?? '') ?: '#2563eb';
            echo '<div class="ezlens-color-shell">';
            echo '<input type="color" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="ezlens-field-color" value="' . esc_attr($default) . '" data-field-key="' . esc_attr($key) . '"' . $price . $req . '>';
            echo '<input type="text" class="ezlens-field-input ezlens-color-hex" value="' . esc_attr($default) . '" maxlength="7" spellcheck="false" aria-label="کد رنگ">';
            echo '</div>';
        } else {
            $input_type = $type === 'time' ? 'time' : 'date';
            echo '<div class="ezlens-input-shell">';
            if (class_exists('EzLens_Product_Options_Helpers')) {
                $icon_name = $type === 'time' ? 'clock' : 'calendar';
                $icon = EzLens_Product_Options_Helpers::icon_svg($icon_name, 'modern', 'ezlens-icon ezlens-input-icon');
                if ($icon === '') {
                    $icon = EzLens_Product_Options_Helpers::icon_svg($icon_name, '', 'ezlens-icon ezlens-input-icon');
                }
                if ($icon) {
                    echo '<span class="ezlens-input-icon-wrap">' . $icon . '</span>'; // phpcs:ignore
                }
            }
            echo '<input type="' . esc_attr($input_type) . '" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="ezlens-field-input" data-field-key="' . esc_attr($key) . '"' . $price . $req . '>';
            echo '</div>';
        }

        EzLens_PO_Field_Render_Helpers::description($meta);
        EzLens_PO_Field_Render_Helpers::close_wrapper();
    }
}
