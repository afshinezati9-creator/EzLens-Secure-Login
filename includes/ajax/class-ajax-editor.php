<?php
/**
 * درخواست‌های AJAX ویرایشگر کد
 */
class EzLens_Auth_Ajax_Editor {

    public static function init() {
        add_action('wp_ajax_ezlens_save_code', [__CLASS__, 'save_code']);
        add_action('wp_ajax_ezlens_reset_code', [__CLASS__, 'reset_code']);
        add_action('wp_ajax_ezlens_preview_page', [__CLASS__, 'preview_page']);
        add_action('wp_ajax_ezlens_toggle_page', [__CLASS__, 'toggle_page']);
        add_action('wp_ajax_ezlens_save_page_settings', [__CLASS__, 'save_page_settings']);
    }

    public static function save_code() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $section = sanitize_key($_POST['section']);
        update_option('ezlens_auth_codes_' . $section, [
            'html' => wp_kses_post($_POST['html']),
            'css'  => sanitize_textarea_field($_POST['css']),
            'js'   => sanitize_textarea_field($_POST['js']),
        ]);
        wp_send_json_success('کدها ذخیره شدند.');
    }

    public static function reset_code() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $section = sanitize_key($_POST['section']);
        delete_option('ezlens_auth_codes_' . $section);
        wp_send_json_success('کدها بازنشانی شدند.');
    }

    public static function preview_page() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }

        $html = wp_kses_post($_POST['html']);
        $css = sanitize_textarea_field($_POST['css']);
        $js = sanitize_textarea_field($_POST['js']);

        echo '<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8"><style>' . $css . '</style></head><body>';
        echo $html;
        echo '<script>' . $js . '</script>';
        echo '</body></html>';
        exit;
    }

    public static function toggle_page() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $section = sanitize_key($_POST['section']);
        $status = sanitize_text_field($_POST['status']);
        update_option('ezlens_auth_enable_' . str_replace('-', '_', $section), $status);

        wp_send_json_success([
            'message'     => 'وضعیت صفحه تغییر کرد.',
            'status'      => $status,
            'status_text' => $status === '1' ? 'فعال' : 'غیرفعال'
        ]);
    }

    public static function save_page_settings() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $section = sanitize_key($_POST['section']);
        $settings = [
            'page_title_' . $section          => sanitize_text_field($_POST['page_title'] ?? ''),
            'page_subtitle_' . $section       => sanitize_text_field($_POST['page_subtitle'] ?? ''),
            'page_primary_color_' . $section  => sanitize_text_field($_POST['primary_color'] ?? ''),
            'page_redirect_url_' . $section   => esc_url_raw($_POST['redirect_url'] ?? ''),
        ];

        foreach ($settings as $key => $value) {
            update_option('ezlens_auth_' . $key, $value);
        }
        wp_send_json_success('تنظیمات صفحه ذخیره شد.');
    }
}

// ثبت اکشن‌ها
EzLens_Auth_Ajax_Editor::init();