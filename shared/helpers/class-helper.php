<?php
/**
 * کلاس کمکی با توابع مشترک (فاز ۶ - افزودن Rate Limiting)
 * EzLens Auth - Helper
 * @version 2.3.3
 */
class EzLens_Auth_Helper {

    /**
     * کلید رمزگذاری (از wp-config.php)
     */
    private static function get_encryption_key() {
        $key = defined('AUTH_KEY') ? AUTH_KEY : '';
        if (empty($key)) {
            $key = hash('sha256', home_url() . 'ezlens_salt');
        }
        return $key;
    }

    /**
     * رمزگذاری یک رشته
     */
    public static function encrypt($plain_text) {
        if (empty($plain_text)) {
            return '';
        }

        $key = self::get_encryption_key();
        $method = 'AES-256-CBC';
        $iv_length = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        $encrypted = openssl_encrypt($plain_text, $method, $key, 0, $iv);
        if ($encrypted === false) {
            return false;
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * رمزگشایی یک رشته
     */
    public static function decrypt($encrypted_text) {
        if (empty($encrypted_text)) {
            return '';
        }

        $key = self::get_encryption_key();
        $method = 'AES-256-CBC';
        $iv_length = openssl_cipher_iv_length($method);
        
        $data = base64_decode($encrypted_text);
        if ($data === false || strlen($data) < $iv_length) {
            return false;
        }

        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);

        return openssl_decrypt($encrypted, $method, $key, 0, $iv);
    }

    // ============================================================
    // توابع نرمال‌سازی و جستجو
    // ============================================================

    public static function normalize_mobile($mobile) {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($mobile) > 11 && substr($mobile, 0, 4) === '0098') {
            $mobile = '0' . substr($mobile, 4);
        } elseif (strlen($mobile) == 12 && substr($mobile, 0, 3) === '989') {
            $mobile = '0' . substr($mobile, 2);
        } elseif (strlen($mobile) == 10 && substr($mobile, 0, 1) === '9') {
            $mobile = '0' . $mobile;
        } elseif (strlen($mobile) == 10 && substr($mobile, 0, 1) !== '0') {
            $mobile = '0' . $mobile;
        }
        return $mobile;
    }

    public static function get_user_by_phone($phone) {
        $phone = self::normalize_mobile($phone);
        $user = get_user_by('login', $phone);
        if ($user) return $user;

        $users = get_users([
            'meta_key'   => 'billing_phone',
            'meta_value' => $phone,
            'number'     => 1,
            'fields'     => 'ID',
        ]);
        if (!empty($users)) return get_user_by('id', $users[0]);

        $users = get_users([
            'meta_key'   => 'user_phone',
            'meta_value' => $phone,
            'number'     => 1,
            'fields'     => 'ID',
        ]);
        if (!empty($users)) return get_user_by('id', $users[0]);

        $phone_without_zero = ltrim($phone, '0');
        if ($phone_without_zero !== $phone) {
            $users = get_users([
                'meta_key'   => 'billing_phone',
                'meta_value' => $phone_without_zero,
                'number'     => 1,
                'fields'     => 'ID',
            ]);
            if (!empty($users)) return get_user_by('id', $users[0]);
            $users = get_users([
                'meta_key'   => 'user_phone',
                'meta_value' => $phone_without_zero,
                'number'     => 1,
                'fields'     => 'ID',
            ]);
            if (!empty($users)) return get_user_by('id', $users[0]);
        }
        return false;
    }

    // ============================================================
    // توابع ارسال ایمیل
    // ============================================================

    public static function send_email($to, $subject, $template, $placeholders = []) {
        $site_name = get_bloginfo('name');
        $defaults = [
            '{site_name}' => $site_name,
            '{expiry}'    => (int) EzLens_Auth_Settings::get('otp_expiry_minutes') ?: 2,
        ];
        $placeholders = array_merge($defaults, $placeholders);
        $message = str_replace(array_keys($placeholders), array_values($placeholders), $template);

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . EzLens_Auth_Settings::get('email_from_name') . ' <' . EzLens_Auth_Settings::get('email_from') . '>'
        ];

        if (EzLens_Auth_Settings::get('smtp_enabled')) {
            add_action('phpmailer_init', function($phpmailer) {
                $phpmailer->isSMTP();
                $phpmailer->Host       = EzLens_Auth_Settings::get('smtp_host');
                $phpmailer->Port       = (int) EzLens_Auth_Settings::get('smtp_port');
                $phpmailer->SMTPSecure = EzLens_Auth_Settings::get('smtp_encryption');
                $phpmailer->SMTPAuth   = (bool) EzLens_Auth_Settings::get('smtp_auth');
                $phpmailer->Username   = EzLens_Auth_Settings::get('smtp_username');
                $phpmailer->Password   = EzLens_Auth_Settings::get('smtp_password');
            });
        }

        $sent = wp_mail($to, $subject, $message, $headers);
        remove_all_actions('phpmailer_init');
        return $sent;
    }

    // ============================================================
    // ===== NEW: توابع Rate Limiting برای ورود =====
    // ============================================================

    /**
     * بررسی تعداد تلاش‌های ناموفق ورود از یک IP
     * @param string $ip آدرس IP
     * @param int $limit حداکثر تلاش مجاز (پیش‌فرض ۵)
     * @param int $lockout_minutes مدت زمان قفل (پیش‌فرض ۱۵ دقیقه)
     * @return bool true اگر مجاز است، false اگر قفل شده
     */
    public static function check_login_attempts($ip, $limit = 5, $lockout_minutes = 15) {
        $key = 'ezlens_login_attempts_' . md5($ip);
        $attempts = get_transient($key) ?: 0;
        return $attempts < $limit;
    }

    /**
     * افزایش تعداد تلاش‌های ناموفق ورود از یک IP
     * @param string $ip آدرس IP
     * @param int $lockout_minutes مدت زمان قفل (پیش‌فرض ۱۵ دقیقه)
     */
    public static function increment_login_attempts($ip, $lockout_minutes = 15) {
        $key = 'ezlens_login_attempts_' . md5($ip);
        $attempts = get_transient($key) ?: 0;
        set_transient($key, $attempts + 1, $lockout_minutes * MINUTE_IN_SECONDS);
    }

    /**
     * بازنشانی تلاش‌های ناموفق ورود از یک IP (پس از ورود موفق)
     * @param string $ip آدرس IP
     */
    public static function reset_login_attempts($ip) {
        $key = 'ezlens_login_attempts_' . md5($ip);
        delete_transient($key);
    }

    /**
     * دریافت پیام خطای قفل شدن بر اساس تعداد تلاش‌ها
     * @param string $ip آدرس IP
     * @param int $limit حداکثر تلاش مجاز
     * @param int $lockout_minutes مدت زمان قفل
     * @return string|null پیام خطا یا null اگر قفل نشده باشد
     */
    public static function get_login_lock_message($ip, $limit = 5, $lockout_minutes = 15) {
        $key = 'ezlens_login_attempts_' . md5($ip);
        $attempts = get_transient($key) ?: 0;
        if ($attempts >= $limit) {
            return sprintf(
                'تعداد تلاش‌های ناموفق بیش از حد مجاز (%s بار). لطفاً %s دقیقه دیگر تلاش کنید.',
                $limit,
                $lockout_minutes
            );
        }
        return null;
    }
}