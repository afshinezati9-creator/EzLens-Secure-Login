<?php
/**
 * درخواست‌های AJAX تست (SMS, SMTP)
 */
class EzLens_Auth_Ajax_Tests {

    public static function init() {
        add_action('wp_ajax_ezlens_sms_test', [__CLASS__, 'sms_test']);
        add_action('wp_ajax_ezlens_smtp_test', [__CLASS__, 'smtp_test']);
    }

    public static function sms_test() {
        check_ajax_referer('ezlens_sms_test', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $mobile = sanitize_text_field($_POST['mobile'] ?? '');
        if (empty($mobile) || !preg_match('/^09\d{9}$/', $mobile)) {
            wp_send_json_error(['message' => 'شماره موبایل نامعتبر است.']);
            return;
        }

        if (!class_exists('EzLens_Auth_SMS')) {
            require_once EZLAUTH_PLUGIN_DIR . 'includes/class-sms.php';
        }

        $sms = new EzLens_Auth_SMS();
        $code = rand(100000, 999999);
        $result = $sms->send_verify($mobile, $code);

        if ($result['success']) {
            wp_send_json_success(['message' => 'کد تست با موفقیت ارسال شد.']);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }

    public static function smtp_test() {
        check_ajax_referer('ezlens_smtp_test', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $email = sanitize_email($_POST['email'] ?? '');
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => 'ایمیل نامعتبر است.']);
            return;
        }

        $subject = '🔧 تست SMTP از ' . get_bloginfo('name');
        $message = "این یک ایمیل تست از پلاگین EzLens Secure Login است.\n\nاگر این ایمیل را دریافت کرده‌اید، تنظیمات SMTP شما به‌درستی کار می‌کند.";
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . EzLens_Auth_Settings::get('email_from_name') . ' <' . EzLens_Auth_Settings::get('email_from') . '>'
        ];

        // اگر SMTP فعال باشد، phpmailer_init قبلاً در Helper مدیریت می‌شود
        $sent = wp_mail($email, $subject, $message, $headers);

        if ($sent) {
            wp_send_json_success(['message' => 'ایمیل تست ارسال شد.']);
        } else {
            wp_send_json_error(['message' => 'ارسال ایمیل ناموفق. تنظیمات SMTP را بررسی کنید.']);
        }
    }
}

EzLens_Auth_Ajax_Tests::init();