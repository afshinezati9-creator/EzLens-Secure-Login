<?php
/**
 * Heading / divider / spacer / html.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Structural_Renderer
 */
class EzLens_PO_Structural_Renderer {

    /**
     * @param string $key
     * @param array  $field
     * @param int    $template_id
     */
    public static function render($key, array $field, $template_id) {
        $type     = sanitize_key($field['type'] ?? 'heading');
        $label    = sanitize_text_field($field['label'] ?? '');
        $settings = is_array($field['settings'] ?? null) ? $field['settings'] : array();

        echo '<div class="ezlens-field-structural ezlens-field-type-' . esc_attr($type) . '" data-field-key="' . esc_attr($key) . '" data-template-id="' . esc_attr((string) absint($template_id)) . '">';

        if ($type === 'heading') {
            $level = in_array(($settings['level'] ?? 'h3'), array('h2', 'h3', 'h4', 'h5', 'h6'), true) ? $settings['level'] : 'h3';
            echo '<' . $level . ' class="ezlens-field-heading">' . esc_html($label) . '</' . $level . '>';
            if (!empty($field['description'])) {
                echo '<p class="ezlens-field-desc">' . esc_html($field['description']) . '</p>';
            }
        } elseif ($type === 'divider') {
            $thickness = max(1, (int) ($settings['thickness'] ?? 1));
            $color     = sanitize_hex_color($settings['color'] ?? '#e2e8f0') ?: '#e2e8f0';
            echo '<hr class="ezlens-field-divider" style="border:0;border-top:' . esc_attr((string) $thickness) . 'px solid ' . esc_attr($color) . ';">';
        } elseif ($type === 'spacer') {
            $h = max(4, (int) ($settings['height'] ?? 16));
            echo '<div class="ezlens-field-spacer" style="height:' . esc_attr((string) $h) . 'px" aria-hidden="true"></div>';
        } elseif ($type === 'html') {
            $html = $field['content'] ?? $field['html'] ?? '';
            echo '<div class="ezlens-field-html">' . wp_kses_post($html) . '</div>';
        }

        echo '</div>';
    }
}
