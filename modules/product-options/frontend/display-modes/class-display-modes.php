<?php
/**
 * Display mode wrappers: inline | accordion | ajax_modal
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Display_Modes
 */
class EzLens_PO_Display_Modes {

    /**
     * Open wrapper for a slot.
     *
     * @param array $slot
     * @param array $template hydrated template
     */
    public static function open(array $slot, array $template) {
        $display = sanitize_key($slot['display'] ?? 'inline');
        $tid     = absint($slot['template_id'] ?? $template['id'] ?? 0);
        $title   = $slot['label'] !== '' ? $slot['label'] : ($template['title'] ?? '');
        $title   = sanitize_text_field((string) $title);
        $uid     = 'ezlens-po-slot-' . $tid . '-' . substr(md5($display . $title), 0, 6);

        echo '<div class="ezlens-po-slot ezlens-po-display-' . esc_attr($display) . '" data-template-id="' . esc_attr((string) $tid) . '" data-display="' . esc_attr($display) . '" id="' . esc_attr($uid) . '">';

        if ($display === 'accordion') {
            echo '<button type="button" class="ezlens-po-acc-toggle" aria-expanded="false" data-target="' . esc_attr($uid) . '-body">';
            if (class_exists('EzLens_Product_Options_Helpers')) {
                $icon = EzLens_Product_Options_Helpers::icon_svg('chevron-down', 'modern', 'ezlens-icon ezlens-po-acc-icon');
                if ($icon) {
                    echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            }
            echo '<span class="ezlens-po-acc-label">' . esc_html($title !== '' ? $title : 'گزینه‌های محصول') . '</span>';
            echo '</button>';
            echo '<div class="ezlens-po-acc-body" id="' . esc_attr($uid) . '-body" hidden>';
            return;
        }

        if ($display === 'ajax_modal') {
            echo '<button type="button" class="ezlens-po-modal-trigger" data-template-id="' . esc_attr((string) $tid) . '" data-slot-title="' . esc_attr($title) . '">';
            if (class_exists('EzLens_Product_Options_Helpers')) {
                $icon = EzLens_Product_Options_Helpers::icon_svg('sliders', '', 'ezlens-icon');
                if (!$icon) {
                    $icon = EzLens_Product_Options_Helpers::icon_svg('settings', 'modern', 'ezlens-icon');
                }
                if ($icon) {
                    echo $icon; // phpcs:ignore
                }
            }
            echo '<span>' . esc_html($title !== '' ? $title : 'انتخاب گزینه‌ها') . '</span>';
            echo '</button>';
            // Hidden container holds fields for form submit; modal mirrors/edits them.
            echo '<div class="ezlens-po-modal-fields" id="' . esc_attr($uid) . '-fields">';
            if ($title !== '') {
                echo '<div class="ezlens-po-slot-title ezlens-po-sr-only">' . esc_html($title) . '</div>';
            }
            return;
        }

        // inline
        if ($title !== '') {
            echo '<div class="ezlens-po-slot-title">' . esc_html($title) . '</div>';
        }
        echo '<div class="ezlens-po-fields">';
    }

    /**
     * Close wrapper.
     *
     * @param array $slot
     */
    public static function close(array $slot) {
        $display = sanitize_key($slot['display'] ?? 'inline');

        if ($display === 'accordion') {
            echo '</div></div>'; // body + slot
            return;
        }
        if ($display === 'ajax_modal') {
            echo '</div></div>'; // fields + slot
            return;
        }
        echo '</div></div>'; // fields + slot
    }
}
