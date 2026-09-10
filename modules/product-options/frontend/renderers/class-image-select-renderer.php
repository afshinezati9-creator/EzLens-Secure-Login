<?php
/**
 * Image select field — card grid.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Image_Select_Renderer
 */
class EzLens_PO_Image_Select_Renderer {

    /**
     * @param string $key
     * @param array  $field
     * @param int    $template_id
     */
    public static function render($key, array $field, $template_id) {
        $meta = EzLens_PO_Field_Render_Helpers::normalize($field);
        $id   = EzLens_PO_Field_Render_Helpers::field_id($key, $template_id);
        $name = EzLens_PO_Field_Render_Helpers::field_name($key, $template_id);
        $req  = $meta['required'] ? ' required' : '';

        EzLens_PO_Field_Render_Helpers::open_wrapper($key, $template_id, $meta, 'ezlens-image-select-wrap');
        EzLens_PO_Field_Render_Helpers::label($meta, $id);

        echo '<div class="ezlens-image-select-grid" role="radiogroup" aria-label="' . esc_attr($meta['label']) . '">';
        foreach ($meta['options'] as $i => $opt) {
            if (!is_array($opt)) {
                continue;
            }
            $ov    = (string) ($opt['value'] ?? '');
            $ol    = (string) ($opt['label'] ?? $ov);
            $op    = isset($opt['price']) ? max(0, (float) $opt['price']) : 0;
            $img   = esc_url($opt['image'] ?? $opt['img'] ?? $opt['url'] ?? '');
            $oid   = $id . '_' . (int) $i;
            $suffix = $op > 0 ? EzLens_PO_Field_Render_Helpers::price_label($op) : '';

            echo '<label class="ezlens-image-card" for="' . esc_attr($oid) . '">';
            echo '<input type="radio" id="' . esc_attr($oid) . '" name="' . esc_attr($name) . '" value="' . esc_attr($ov) . '" data-price="' . esc_attr((string) $op) . '" data-field-key="' . esc_attr($key) . '"' . $req . '>';
            echo '<span class="ezlens-image-card-inner">';
            if ($img !== '') {
                echo '<span class="ezlens-image-card-media"><img src="' . $img . '" alt="' . esc_attr($ol) . '" loading="lazy"></span>';
            } else {
                echo '<span class="ezlens-image-card-media ezlens-image-card-placeholder">' . esc_html(mb_substr($ol, 0, 1)) . '</span>';
            }
            echo '<span class="ezlens-image-card-body">';
            echo '<span class="ezlens-image-card-label">' . esc_html($ol) . '</span>';
            if ($suffix !== '') {
                echo '<span class="ezlens-image-card-price">+' . esc_html($suffix) . '</span>';
            }
            echo '</span>';
            echo '<span class="ezlens-image-card-check" aria-hidden="true"></span>';
            echo '</span>';
            echo '</label>';
        }
        echo '</div>';

        EzLens_PO_Field_Render_Helpers::description($meta);
        EzLens_PO_Field_Render_Helpers::close_wrapper();
    }
}
