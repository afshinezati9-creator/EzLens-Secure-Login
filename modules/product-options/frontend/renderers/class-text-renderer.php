<?php
/**
 * Text / email / phone / number / textarea renderers.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Text_Renderer
 */
class EzLens_PO_Text_Renderer {

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

        $req   = $meta['required'] ? ' required' : '';
        $price = $meta['price'] > 0 ? ' data-price="' . esc_attr((string) $meta['price']) . '"' : '';

        EzLens_PO_Field_Render_Helpers::open_wrapper($key, $template_id, $meta);
        EzLens_PO_Field_Render_Helpers::label($meta, $id);

        if ($type === 'textarea') {
            echo '<textarea id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="ezlens-field-input ezlens-input-textarea" rows="3" placeholder="' . esc_attr($meta['placeholder']) . '" data-field-key="' . esc_attr($key) . '"' . $price . $req . '></textarea>';
        } elseif ($type === 'number') {
            $settings = $meta['settings'];
            echo '<div class="ezlens-input-with-addon">';
            echo '<input type="number" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="ezlens-field-input" placeholder="' . esc_attr($meta['placeholder']) . '" data-field-key="' . esc_attr($key) . '"' . $price . $req;
            if (isset($settings['min'])) {
                echo ' min="' . esc_attr($settings['min']) . '"';
            }
            if (isset($settings['max'])) {
                echo ' max="' . esc_attr($settings['max']) . '"';
            }
            if (isset($settings['step'])) {
                echo ' step="' . esc_attr($settings['step']) . '"';
            }
            echo ' inputmode="decimal">';
            echo '</div>';
        } else {
            $input_type = $type === 'email' ? 'email' : ($type === 'phone' ? 'tel' : 'text');
            $inputmode  = $type === 'phone' ? ' tel' : ($type === 'email' ? ' email' : ' text');
            echo '<div class="ezlens-input-shell">';
            if ($type === 'phone' && class_exists('EzLens_Product_Options_Helpers')) {
                $icon = EzLens_Product_Options_Helpers::icon_svg('phone', 'modern', 'ezlens-icon ezlens-input-icon');
                if ($icon === '') {
                    $icon = EzLens_Product_Options_Helpers::icon_svg('phone', '', 'ezlens-icon ezlens-input-icon');
                }
                if ($icon) {
                    echo '<span class="ezlens-input-icon-wrap">' . $icon . '</span>'; // phpcs:ignore
                }
            }
            if ($type === 'email' && class_exists('EzLens_Product_Options_Helpers')) {
                $icon = EzLens_Product_Options_Helpers::icon_svg('mail', 'modern', 'ezlens-icon ezlens-input-icon');
                if ($icon === '') {
                    $icon = EzLens_Product_Options_Helpers::icon_svg('mail', '', 'ezlens-icon ezlens-input-icon');
                }
                if ($icon) {
                    echo '<span class="ezlens-input-icon-wrap">' . $icon . '</span>'; // phpcs:ignore
                }
            }
            echo '<input type="' . esc_attr($input_type) . '" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="ezlens-field-input" placeholder="' . esc_attr($meta['placeholder']) . '" data-field-key="' . esc_attr($key) . '" inputmode="' . esc_attr(trim($inputmode)) . '"' . $price . $req . '>';
            echo '</div>';
        }

        EzLens_PO_Field_Render_Helpers::description($meta);
        EzLens_PO_Field_Render_Helpers::close_wrapper();
    }
}
