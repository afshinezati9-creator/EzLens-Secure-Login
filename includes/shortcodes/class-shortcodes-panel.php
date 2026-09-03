<?php
/**
 * شورت‌کد پنل کاربری – با مسیر جدید
 * @version 3.0.0
 */
class EzLens_Auth_Shortcodes_Panel {

    public static function render_user_panel() {
        if (!EzLens_Auth_Settings::is_page_enabled('user-panel')) {
            return self::get_disabled_message('پنل کاربری', 'پنل کاربری غیرفعال است.');
        }

        if (!is_user_logged_in()) {
            $login_url = function_exists('wc_get_page_permalink') 
                ? wc_get_page_permalink('myaccount') 
                : wp_login_url(get_permalink());
            return '<div class="ezu-login-required" style="text-align:center;padding:60px 20px;font-family:IRANYekan,Tahoma,sans-serif;">
                <div style="font-size:48px;margin-bottom:16px;">🔒</div>
                <h2 style="font-size:22px;font-weight:700;color:#0f172a;margin:0 0 8px;">برای مشاهده پنل وارد شوید</h2>
                <p style="color:#64748b;margin:0 0 20px;">لطفاً وارد حساب کاربری خود شوید.</p>
                <a href="' . esc_url($login_url) . '" style="display:inline-block;padding:12px 32px;background:#2b6cb0;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">ورود به حساب کاربری</a>
            </div>';
        }

        // ✅ مسیر جدید به frontend/pages/user-panel.php
        ob_start();
        include EZLAUTH_FRONTEND_DIR . 'pages/user-panel.php';
        return ob_get_clean();
    }

    // ============================================================
    // AJAX Handlers (همان‌های قبلی)
    // ============================================================

    public static function ajax_save_profile() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفاً وارد شوید.']);
            return;
        }

        $user_id = get_current_user_id();
        $fn = sanitize_text_field($_POST['first_name'] ?? '');
        $ln = sanitize_text_field($_POST['last_name'] ?? '');
        $phone = sanitize_text_field($_POST['billing_phone'] ?? '');

        if ($fn === '' || $ln === '') {
            wp_send_json_error(['message' => 'نام و نام خانوادگی الزامی است.']);
            return;
        }

        if ($phone && !preg_match('/^09\d{9}$/', $phone)) {
            wp_send_json_error(['message' => 'موبایل نامعتبر است.']);
            return;
        }

        wp_update_user([
            'ID'            => $user_id,
            'first_name'    => $fn,
            'last_name'     => $ln,
            'display_name'  => trim($fn . ' ' . $ln)
        ]);
        update_user_meta($user_id, 'billing_first_name', $fn);
        update_user_meta($user_id, 'billing_last_name', $ln);
        if ($phone) update_user_meta($user_id, 'billing_phone', $phone);

        wp_cache_delete('ezu_stats_' . $user_id, 'ezu');

        wp_send_json_success(['message' => 'پروفایل ذخیره شد.']);
    }

    public static function ajax_save_address() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفاً وارد شوید.']);
            return;
        }

        $user_id = get_current_user_id();
        $keys = ['billing_first_name', 'billing_last_name', 'billing_state', 'billing_city', 
                 'billing_address_1', 'billing_address_2', 'billing_postcode', 'billing_phone'];

        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $value = sanitize_text_field($_POST[$key]);
                if ($key === 'billing_phone' && $value && !preg_match('/^09\d{9}$/', $value)) {
                    wp_send_json_error(['message' => 'موبایل نامعتبر است.']);
                    return;
                }
                update_user_meta($user_id, $key, $value);
            }
        }
        update_user_meta($user_id, 'billing_country', 'IR');

        wp_cache_delete('ezu_stats_' . $user_id, 'ezu');

        wp_send_json_success(['message' => 'آدرس ذخیره شد.']);
    }

    public static function ajax_change_password() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفاً وارد شوید.']);
            return;
        }

        $user_id = get_current_user_id();
        $current = $_POST['current_pass'] ?? '';
        $new = $_POST['new_pass'] ?? '';
        $new2 = $_POST['new_pass2'] ?? '';

        $user = get_user_by('id', $user_id);
        if (!$user || !wp_check_password($current, $user->user_pass, $user_id)) {
            wp_send_json_error(['message' => 'رمز فعلی اشتباه است.']);
            return;
        }

        if (strlen($new) < 8) {
            wp_send_json_error(['message' => 'رمز جدید حداقل ۸ کاراکتر.']);
            return;
        }
        if (!hash_equals($new, $new2)) {
            wp_send_json_error(['message' => 'تکرار رمز یکسان نیست.']);
            return;
        }

        wp_set_password($new, $user_id);
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        wp_cache_delete('ezu_stats_' . $user_id, 'ezu');

        wp_send_json_success(['message' => 'رمز عبور تغییر کرد.']);
    }

    public static function ajax_upload_avatar() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفاً وارد شوید.']);
            return;
        }

        if (empty($_FILES['ezu_avatar']['name'])) {
            wp_send_json_error(['message' => 'فایلی انتخاب نشده.']);
            return;
        }

        $file = $_FILES['ezu_avatar'];
        if ($file['size'] > 2 * 1024 * 1024) {
            wp_send_json_error(['message' => 'حداکثر ۲ مگابایت.']);
            return;
        }

        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            wp_send_json_error(['message' => 'فقط JPG/PNG/WEBP مجاز است.']);
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('ezu_avatar', 0);
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => 'آپلود ناموفق.']);
            return;
        }

        $user_id = get_current_user_id();
        $old = (int) get_user_meta($user_id, 'ezu_avatar_id', true);
        if ($old) wp_delete_attachment($old, true);

        update_user_meta($user_id, 'ezu_avatar_id', $attachment_id);
        
        wp_cache_delete('ezu_stats_' . $user_id, 'ezu');

        wp_send_json_success([
            'message' => 'عکس پروفایل ذخیره شد.',
            'avatar_url' => wp_get_attachment_image_url($attachment_id, 'thumbnail')
        ]);
    }

    public static function ajax_remove_avatar() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفاً وارد شوید.']);
            return;
        }

        $user_id = get_current_user_id();
        $old = (int) get_user_meta($user_id, 'ezu_avatar_id', true);
        if ($old) {
            wp_delete_attachment($old, true);
            delete_user_meta($user_id, 'ezu_avatar_id');
        }
        
        wp_cache_delete('ezu_stats_' . $user_id, 'ezu');

        wp_send_json_success(['message' => 'عکس پروفایل حذف شد.']);
    }

    public static function ajax_upload_prescription() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفاً وارد شوید.']);
            return;
        }

        if (empty($_FILES['ezu_prescription']['name'])) {
            wp_send_json_error(['message' => 'فایلی انتخاب نشده.']);
            return;
        }

        $file = $_FILES['ezu_prescription'];
        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(['message' => 'حداکثر ۵ مگابایت.']);
            return;
        }

        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 
                    'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            wp_send_json_error(['message' => 'فرمت مجاز: JPG, PNG, GIF, WEBP, PDF.']);
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('ezu_prescription', 0);
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => 'آپلود ناموفق.']);
            return;
        }

        $user_id = get_current_user_id();
        $file_url = wp_get_attachment_url($attachment_id);
        $file_path = get_attached_file($attachment_id);
        $file_size = size_format(filesize($file_path), 1);
        $date = date_i18n('Y/m/d H:i');

        $prescriptions = get_user_meta($user_id, 'ezu_prescriptions', true);
        if (!is_array($prescriptions)) $prescriptions = [];

        $prescriptions[] = [
            'id'   => $attachment_id,
            'file' => $file_url,
            'date' => $date,
            'size' => $file_size,
        ];

        update_user_meta($user_id, 'ezu_prescriptions', $prescriptions);
        
        wp_cache_delete('ezu_stats_' . $user_id, 'ezu');

        wp_send_json_success(['message' => 'نسخه با موفقیت آپلود شد.']);
    }

    public static function ajax_delete_prescription() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفاً وارد شوید.']);
            return;
        }

        $index = (int)($_POST['prescription_index'] ?? -1);
        if ($index < 0) {
            wp_send_json_error(['message' => 'شناسه نامعتبر.']);
            return;
        }

        $user_id = get_current_user_id();
        $prescriptions = get_user_meta($user_id, 'ezu_prescriptions', true);
        if (!is_array($prescriptions) || !isset($prescriptions[$index])) {
            wp_send_json_error(['message' => 'نسخه یافت نشد.']);
            return;
        }

        $item = $prescriptions[$index];
        if (!empty($item['id'])) {
            wp_delete_attachment($item['id'], true);
        }

        unset($prescriptions[$index]);
        update_user_meta($user_id, 'ezu_prescriptions', array_values($prescriptions));
        
        wp_cache_delete('ezu_stats_' . $user_id, 'ezu');

        wp_send_json_success(['message' => 'نسخه حذف شد.']);
    }

    // ============================================================
    // توابع کمکی
    // ============================================================

    private static function get_disabled_message($title, $message) {
        return '<div style="text-align:center;padding:60px 20px;font-family:IRANYekan,Tahoma,sans-serif;">
            <div style="font-size:48px;margin-bottom:16px;">⛔</div>
            <h2 style="font-size:22px;font-weight:700;color:#0f172a;margin:0 0 8px;">' . esc_html($title) . '</h2>
            <p style="color:#64748b;margin:0 0 20px;">' . esc_html($message) . '</p>
            <a href="' . home_url() . '" style="display:inline-block;padding:12px 32px;background:#2b6cb0;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">بازگشت به صفحه اصلی</a>
        </div>';
    }
}