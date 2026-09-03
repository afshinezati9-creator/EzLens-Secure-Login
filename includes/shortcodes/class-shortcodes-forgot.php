<?php
/**
 * شورت‌کد فراموشی رمز عبور – با مسیر جدید (اختیاری)
 * @version 3.0.0
 */
class EzLens_Auth_Shortcodes_Forgot {

    public static function render_lost_password() {
        // اگر صفحه فعال است، فایل جدید را نمایش بده
        if (EzLens_Auth_Settings::is_page_enabled('lost-password')) {
            ob_start();
            include EZLAUTH_FRONTEND_DIR . 'pages/forgot-password.php';
            return ob_get_clean();
        }
        
        // در غیر این صورت پیام غیرفعال
        return '<div style="text-align:center;padding:40px;font-family:IRANYekan,Tahoma,sans-serif;">این صفحه غیرفعال است. لطفاً از صفحه ورود استفاده کنید.</div>';
    }

    // ============================================================
    // فراموشی رمز مشتری – پیامک
    // ============================================================

    public static function ajax_forgot_send_sms() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $mobile = EzLens_Auth_Helper::normalize_mobile($_POST['mobile'] ?? '');
        if (!preg_match('/^09\d{9}$/', $mobile)) {
            wp_send_json_error(['message' => 'شماره موبایل نامعتبر است.']);
            return;
        }

        $user = EzLens_Auth_Helper::get_user_by_phone($mobile);
        if (!$user) {
            wp_send_json_error(['message' => 'این شماره موبایل ثبت نشده است.']);
            return;
        }

        if (!EzLens_Auth_OTP::can_request($mobile, 'forgot_sms')) {
            wp_send_json_error(['message' => 'تعداد درخواست بیش از حد مجاز. یک دقیقه صبر کنید.']);
            return;
        }

        $code = EzLens_Auth_OTP::store($mobile, 'forgot_sms');
        if (EzLens_Auth_SMS::is_enabled()) {
            $sms = new EzLens_Auth_SMS();
            $result = $sms->send_verify($mobile, $code);
            if (!$result['success']) {
                wp_send_json_error(['message' => $result['message']]);
                return;
            }
        }

        wp_send_json_success(['message' => 'کد تأیید به شماره موبایل شما ارسال شد.']);
    }

    public static function ajax_forgot_verify_sms() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $mobile = EzLens_Auth_Helper::normalize_mobile($_POST['mobile'] ?? '');
        $code = sanitize_text_field($_POST['code'] ?? '');

        if (!preg_match('/^09\d{9}$/', $mobile)) {
            wp_send_json_error(['message' => 'شماره موبایل نامعتبر است.']);
            return;
        }

        $result = EzLens_Auth_OTP::verify($mobile, $code, 'forgot_sms');
        if (!$result['success']) {
            wp_send_json_error(['message' => $result['message']]);
            return;
        }

        wp_send_json_success([
            'message'  => 'هویت شما تأیید شد. لطفاً رمز خود را تغییر دهید.',
            'redirect' => home_url('/my-account/')
        ]);
    }

    // ============================================================
    // فراموشی رمز مشتری – ایمیل
    // ============================================================

    public static function ajax_forgot_send_email() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $email = sanitize_email($_POST['email'] ?? '');
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => 'ایمیل نامعتبر است.']);
            return;
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            wp_send_json_error(['message' => 'این ایمیل ثبت نشده است.']);
            return;
        }

        $mobile = get_user_meta($user->ID, 'billing_phone', true) ?: get_user_meta($user->ID, 'user_phone', true);
        if (empty($mobile)) {
            wp_send_json_error(['message' => 'برای این کاربر شماره موبایل ثبت نشده است.']);
            return;
        }

        $mobile = EzLens_Auth_Helper::normalize_mobile($mobile);
        if (!EzLens_Auth_OTP::can_request($mobile, 'forgot_email')) {
            wp_send_json_error(['message' => 'تعداد درخواست بیش از حد مجاز. یک دقیقه صبر کنید.']);
            return;
        }

        $code = EzLens_Auth_OTP::store($mobile, 'forgot_email');

        $template = EzLens_Auth_Settings::get('email_forgot_otp_template') ?:
            'سلام {name}\n\nشما درخواست بازیابی رمز خود را در {site_name} ثبت کرده‌اید.\nکد تأیید شما: {code}\n\nاین کد تا {expiry} دقیقه اعتبار دارد.';
        $subject = '🔐 کد تأیید بازیابی رمز عبور در ' . get_bloginfo('name');

        $sent = EzLens_Auth_Helper::send_email($email, $subject, $template, [
            '{name}' => $user->display_name,
            '{code}' => $code
        ]);

        if ($sent) {
            wp_send_json_success(['message' => 'کد تأیید به ایمیل شما ارسال شد.']);
        } else {
            error_log('EzLens Auth: ارسال ایمیل فراموشی رمز برای ' . $email . ' ناموفق.');
            wp_send_json_error(['message' => 'خطا در ارسال ایمیل. تنظیمات SMTP را بررسی کنید.']);
        }
    }

    public static function ajax_forgot_verify_email() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $email = sanitize_email($_POST['email'] ?? '');
        $code = sanitize_text_field($_POST['code'] ?? '');

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => 'ایمیل نامعتبر است.']);
            return;
        }
        if (strlen($code) !== 6) {
            wp_send_json_error(['message' => 'کد باید ۶ رقم باشد.']);
            return;
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            wp_send_json_error(['message' => 'این ایمیل ثبت نشده است.']);
            return;
        }

        $mobile = get_user_meta($user->ID, 'billing_phone', true) ?: get_user_meta($user->ID, 'user_phone', true);
        if (empty($mobile)) {
            wp_send_json_error(['message' => 'شماره موبایل ثبت نشده است.']);
            return;
        }

        $mobile = EzLens_Auth_Helper::normalize_mobile($mobile);
        $result = EzLens_Auth_OTP::verify($mobile, $code, 'forgot_email');

        if (!$result['success']) {
            wp_send_json_error(['message' => $result['message']]);
            return;
        }

        wp_send_json_success([
            'message'  => 'هویت شما تأیید شد. لطفاً رمز خود را تغییر دهید.',
            'redirect' => home_url('/my-account/')
        ]);
    }

    // ============================================================
    // فراموشی رمز مدیر (AJAX)
    // ============================================================

    public static function ajax_admin_forgot_send() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $identifier = sanitize_text_field($_POST['identifier'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'email');

        if (empty($identifier)) {
            wp_send_json_error(['message' => 'لطفاً ایمیل یا شماره موبایل خود را وارد کنید.']);
            return;
        }

        $user = false;
        if ($type === 'email' && is_email($identifier)) {
            $user = get_user_by('email', $identifier);
        } elseif ($type === 'phone') {
            $phone = EzLens_Auth_Helper::normalize_mobile($identifier);
            if (preg_match('/^09\d{9}$/', $phone)) {
                $user = EzLens_Auth_Helper::get_user_by_phone($phone);
            }
        }

        if (!$user || !user_can($user->ID, 'manage_options')) {
            wp_send_json_error(['message' => 'کاربر مدیر با این اطلاعات یافت نشد.']);
            return;
        }

        $mobile = get_user_meta($user->ID, 'billing_phone', true) ?: get_user_meta($user->ID, 'user_phone', true);
        $identifier_for_otp = ($type === 'email') ? $user->user_email : EzLens_Auth_Helper::normalize_mobile($mobile);
        $action = 'admin_forgot_' . $type;

        if (!EzLens_Auth_OTP::can_request($identifier_for_otp, $action)) {
            wp_send_json_error(['message' => 'تعداد درخواست بیش از حد مجاز. یک دقیقه صبر کنید.']);
            return;
        }

        $code = EzLens_Auth_OTP::store($identifier_for_otp, $action);

        if ($type === 'email') {
            $template = 'سلام {name}\n\nشما درخواست بازیابی رمز مدیریت خود را در {site_name} ثبت کرده‌اید.\nکد تأیید شما: {code}\n\nاین کد تا {expiry} دقیقه اعتبار دارد.';
            $subject = '🔐 کد تأیید بازیابی رمز مدیریت';
            EzLens_Auth_Helper::send_email($user->user_email, $subject, $template, [
                '{name}' => $user->display_name,
                '{code}' => $code
            ]);
        } else {
            if (EzLens_Auth_SMS::is_enabled()) {
                $sms = new EzLens_Auth_SMS();
                $sms->send_verify($mobile, $code);
            }
        }

        wp_send_json_success([
            'message' => 'کد تأیید ارسال شد.',
            'user_id' => $user->ID,
            'type'    => $type
        ]);
    }

    public static function ajax_admin_forgot_verify() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $user_id = (int)($_POST['user_id'] ?? 0);
        $code = sanitize_text_field($_POST['code'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'email');

        if (!$user_id || strlen($code) !== 6) {
            wp_send_json_error(['message' => 'اطلاعات نامعتبر است.']);
            return;
        }

        $user = get_user_by('id', $user_id);
        if (!$user || !user_can($user, 'manage_options')) {
            wp_send_json_error(['message' => 'کاربر یافت نشد.']);
            return;
        }

        $mobile = get_user_meta($user->ID, 'billing_phone', true) ?: get_user_meta($user->ID, 'user_phone', true);
        $identifier = ($type === 'email') ? $user->user_email : EzLens_Auth_Helper::normalize_mobile($mobile);
        $action = 'admin_forgot_' . $type;

        $result = EzLens_Auth_OTP::verify($identifier, $code, $action);
        if (!$result['success']) {
            wp_send_json_error(['message' => $result['message']]);
            return;
        }

        $new_password = wp_generate_password(12, true);
        wp_set_password($new_password, $user_id);

        $template = "سلام {name}\n\nرمز عبور جدید شما برای ورود به مدیریت {site_name}:\nرمز عبور: {$new_password}\n\nلطفاً پس از ورود، رمز خود را تغییر دهید.";
        $subject = '🔐 رمز عبور جدید مدیریت';
        EzLens_Auth_Helper::send_email($user->user_email, $subject, $template, [
            '{name}' => $user->display_name
        ]);

        wp_send_json_success([
            'message'  => '✅ رمز عبور جدید به ایمیل شما ارسال شد.',
            'redirect' => home_url('/admin-secret')
        ]);
    }
}