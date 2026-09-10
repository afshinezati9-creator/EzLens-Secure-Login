<?php
/**
 * Select / radio / checkbox renderers.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Choice_Renderer
 */
class EzLens_PO_Choice_Renderer {

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
        $base_price = $meta['price'] > 0 ? ' data-price="' . esc_attr((string) $meta['price']) . '"' : '';

        EzLens_PO_Field_Render_Helpers::open_wrapper($key, $template_id, $meta);

        if ($type === 'select') {
            EzLens_PO_Field_Render_Helpers::label($meta, $id);
            echo '<div class="ezlens-select-shell">';
            echo '<select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="ezlens-field-input ezlens-select" data-field-key="' . esc_attr($key) . '"' . $base_price . $req . '>';
            $ph = $meta['placeholder'] !== '' ? $meta['placeholder'] : 'انتخاب کنید';
            echo '<option value="">' . esc_html($ph) . '</option>';
            foreach ($meta['options'] as $opt) {
                if (!is_array($opt)) {
                    continue;
                }
                $ov = (string) ($opt['value'] ?? '');
                $ol = (string) ($opt['label'] ?? $ov);
                $op = isset($opt['price']) ? max(0, (float) $opt['price']) : 0;
                $suffix = $op > 0 ? ' (+' . EzLens_PO_Field_Render_Helpers::price_label($op) . ')' : '';
                echo '<option value="' . esc_attr($ov) . '" data-price="' . esc_attr((string) $op) . '">' . esc_html($ol . $suffix) . '</option>';
            }
            echo '</select>';
            echo '<span class="ezlens-select-caret" aria-hidden="true"></span>';
            echo '</div>';
        } elseif ($type === 'radio') {
            EzLens_PO_Field_Render_Helpers::label($meta, $id, false);
            echo '<div class="ezlens-choice-list ezlens-radio-list" role="radiogroup" aria-label="' . esc_attr($meta['label']) . '">';
            foreach ($meta['options'] as $i => $opt) {
                if (!is_array($opt)) {
                    continue;
                }
                $ov = (string) ($opt['value'] ?? '');
                $ol = (string) ($opt['label'] ?? $ov);
                $op = isset($opt['price']) ? max(0, (float) $opt['price']) : 0;
                $oid = $id . '_' . (int) $i;
                $suffix = $op > 0 ? ' <span class="ezlens-opt-price">+' . esc_html(EzLens_PO_Field_Render_Helpers::price_label($op)) . '</span>' : '';
                echo '<label class="ezlens-choice-item" for="' . esc_attr($oid) . '">';
                echo '<input type="radio" id="' . esc_attr($oid) . '" name="' . esc_attr($name) . '" value="' . esc_attr($ov) . '" data-price="' . esc_attr((string) $op) . '" data-field-key="' . esc_attr($key) . '"' . $req . '>';
                echo '<span class="ezlens-choice-ui"></span>';
                echo '<span class="ezlens-choice-text">' . esc_html($ol) . $suffix . '</span>';
                echo '</label>';
            }
            echo '</div>';
        } else { // checkbox
            $has_opts = !empty($meta['options']);
            if ($has_opts) {
                EzLens_PO_Field_Render_Helpers::label($meta, $id, false);
                echo '<div class="ezlens-choice-list ezlens-check-list">';
                foreach ($meta['options'] as $i => $opt) {
                    if (!is_array($opt)) {
                        continue;
                    }
                    $ov = (string) ($opt['value'] ?? '');
                    $ol = (string) ($opt['label'] ?? $ov);
                    $op = isset($opt['price']) ? max(0, (float) $opt['price']) : 0;
                    $oid = $id . '_' . (int) $i;
                    $suffix = $op > 0 ? ' <span class="ezlens-opt-price">+' . esc_html(EzLens_PO_Field_Render_Helpers::price_label($op)) . '</span>' : '';
                    echo '<label class="ezlens-choice-item" for="' . esc_attr($oid) . '">';
                    echo '<input type="checkbox" id="' . esc_attr($oid) . '" name="' . esc_attr($name) . '[]" value="' . esc_attr($ov) . '" data-price="' . esc_attr((string) $op) . '" data-field-key="' . esc_attr($key) . '">';
                    echo '<span class="ezlens-choice-ui ezlens-choice-ui-check"></span>';
                    echo '<span class="ezlens-choice-text">' . esc_html($ol) . $suffix . '</span>';
                    echo '</label>';
                }
                echo '</div>';
            } else {
                echo '<label class="ezlens-choice-item ezlens-choice-single" for="' . esc_attr($id) . '">';
                echo '<input type="checkbox" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="1" data-field-key="' . esc_attr($key) . '"' . $base_price . $req . '>';
                echo '<span class="ezlens-choice-ui ezlens-choice-ui-check"></span>';
                $star = $meta['required'] ? ' <span class="required">*</span>' : '';
                echo '<span class="ezlens-choice-text">' . esc_html($meta['label']) . $star . '</span>';
                echo '</label>';
            }
        }

        EzLens_PO_Field_Render_Helpers::description($meta);
        EzLens_PO_Field_Render_Helpers::close_wrapper();
    }
}
