<?php
/**
 * Professional upload field UI (dropzone) — Phase 3.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Upload_Renderer
 */
class EzLens_PO_Upload_Renderer {

    /** Max size bytes (must match server). */
    const MAX_SIZE = 5242880; // 5 MB

    /**
     * Accept attribute for file input.
     *
     * @return string
     */
    public static function accept_attr() {
        return '.jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,image/jpeg,image/png,image/gif,image/webp,application/pdf';
    }

    /**
     * Human-readable allowed types (FA).
     *
     * @return string
     */
    public static function allowed_label() {
        return 'JPG، PNG، WEBP، GIF، PDF، DOC — حداکثر ۵ مگابایت';
    }

    /**
     * Render dropzone markup.
     *
     * @param string $id            Input id.
     * @param string $name          Hidden input name (stores URL after upload).
     * @param string $key           Field key.
     * @param int    $template_id   Template id.
     * @param bool   $required      Required flag.
     * @param float  $price         Optional field price.
     */
    public static function render($id, $name, $key, $template_id, $required = false, $price = 0) {
        $required_attr = $required ? ' required' : '';
        $data_price    = $price > 0 ? ' data-price="' . esc_attr((string) $price) . '"' : '';
        $max_label     = class_exists('EzLens_Product_Options_Helpers')
            ? EzLens_Product_Options_Helpers::to_persian_digits('5')
            : '5';

        $icon = '';
        if (class_exists('EzLens_Product_Options_Helpers')) {
            $icon = EzLens_Product_Options_Helpers::icon_svg('upload', 'modern', 'ezlens-icon ezlens-upload-icon');
            if ($icon === '') {
                $icon = EzLens_Product_Options_Helpers::icon_svg('upload', '', 'ezlens-icon ezlens-upload-icon');
            }
        }

        echo '<div class="ezlens-upload-box" data-field-key="' . esc_attr($key) . '" data-template-id="' . esc_attr((string) absint($template_id)) . '" data-max-size="' . esc_attr((string) self::MAX_SIZE) . '">';

        // Hidden value submitted with cart form.
        echo '<input type="hidden" name="' . esc_attr($name) . '" class="ezlens-upload-value" value=""' . $data_price . ' data-field-key="' . esc_attr($key) . '">';

        // Dropzone surface (file input visually hidden).
        echo '<div class="ezlens-upload-dropzone" tabindex="0" role="button" aria-label="آپلود فایل">';
        echo '<input type="file" id="' . esc_attr($id) . '" class="ezlens-field-upload ezlens-upload-input" accept="' . esc_attr(self::accept_attr()) . '" data-field-key="' . esc_attr($key) . '" data-template-id="' . esc_attr((string) absint($template_id)) . '"' . $required_attr . '>';

        echo '<div class="ezlens-upload-idle">';
        if ($icon) {
            echo '<span class="ezlens-upload-icon-wrap">' . $icon . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            echo '<span class="ezlens-upload-icon-fallback" aria-hidden="true">⬆</span>';
        }
        echo '<span class="ezlens-upload-title">فایل را بکشید و رها کنید</span>';
        echo '<span class="ezlens-upload-sub">یا برای انتخاب کلیک کنید</span>';
        echo '<span class="ezlens-upload-hint">' . esc_html(self::allowed_label()) . '</span>';
        echo '</div>';

        // Progress state.
        echo '<div class="ezlens-upload-progressing" hidden>';
        echo '<div class="ezlens-upload-progress-track"><div class="ezlens-upload-progress-bar"></div></div>';
        echo '<span class="ezlens-upload-progress-text">در حال آپلود… <span class="ezlens-upload-pct">۰٪</span></span>';
        echo '</div>';

        // Success / preview state.
        echo '<div class="ezlens-upload-done" hidden>';
        echo '<div class="ezlens-upload-preview-row">';
        echo '<div class="ezlens-upload-thumb"></div>';
        echo '<div class="ezlens-upload-meta">';
        echo '<span class="ezlens-upload-filename"></span>';
        echo '<span class="ezlens-upload-status-ok">آپلود موفق</span>';
        echo '</div>';
        echo '<button type="button" class="ezlens-upload-remove" title="حذف فایل" aria-label="حذف فایل">×</button>';
        echo '</div>';
        echo '</div>';

        // Error state.
        echo '<div class="ezlens-upload-error" hidden>';
        echo '<span class="ezlens-upload-error-msg"></span>';
        echo '<button type="button" class="ezlens-upload-retry" type="button">تلاش دوباره</button>';
        echo '</div>';

        echo '</div>'; // dropzone
        echo '</div>'; // box
    }
}
