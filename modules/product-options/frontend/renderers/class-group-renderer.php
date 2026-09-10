<?php
/**
 * Group field — nested children grid.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Group_Renderer
 */
class EzLens_PO_Group_Renderer {

    /**
     * @param string   $key
     * @param array    $field
     * @param int      $template_id
     * @param callable $child_renderer function($child_key, $child_field, $template_id)
     */
    public static function render($key, array $field, $template_id, $child_renderer) {
        $meta     = EzLens_PO_Field_Render_Helpers::normalize($field);
        $settings = $meta['settings'];
        $columns  = max(1, min(4, (int) ($settings['columns'] ?? 1)));
        $children = is_array($field['children'] ?? null) ? $field['children'] : array();

        echo '<div class="ezlens-field-group-wrap" data-field-key="' . esc_attr($key) . '" data-template-id="' . esc_attr((string) absint($template_id)) . '">';
        if ($meta['label'] !== '') {
            echo '<div class="ezlens-group-title">' . esc_html($meta['label']) . '</div>';
        }
        if ($meta['description'] !== '') {
            echo '<p class="ezlens-field-desc">' . esc_html($meta['description']) . '</p>';
        }
        echo '<div class="ezlens-field-group" style="--ezlens-cols:' . esc_attr((string) $columns) . '">';
        foreach ($children as $ck => $child) {
            if (!is_array($child)) {
                continue;
            }
            $ckey = isset($child['name']) ? sanitize_key($child['name']) : sanitize_key((string) $ck);
            if ($ckey === '') {
                continue;
            }
            call_user_func($child_renderer, $key . '_' . $ckey, $child, $template_id);
        }
        echo '</div></div>';
    }
}
