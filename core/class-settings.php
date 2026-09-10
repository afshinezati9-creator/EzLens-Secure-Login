<?php
/**
 * کلاس مدیریت تنظیمات پلاگین با کش (فاز ۵ - نسخه کامل با کش‌شکنی)
 * @version 2.3.5 – اضافه شدن تنظیمات Product Options
 */
class EzLens_Auth_Settings {

    private static $sensitive_keys = [
        'sms_api_key',
        'smtp_password',
        'captcha_secret_key',
        'otp_sms_api_key',
        'otp_sms_token',
        'campaign_sms_api_key',
        'campaign_sms_token',
        'campaign_email_smtp_password',
        'webhook_secret',
    ];

    private static $cached_settings = null;
    private static $cache_key = 'ezlens_auth_settings';

    /**
     * دریافت مقادیر پیش‌فرض تمام تنظیمات
     */
    public static function get_defaults() {
        return [
            'admin_login_slug'      => 'admin-secret',
            'customer_login_slug'   => 'login',
            'enable_registration'   => '1',
            'captcha_type'          => 'math',
            'captcha_site_key'      => '',
            'captcha_secret_key'    => '',
            'max_attempts'          => 5,
            'lockout_minutes'       => 15,
            'enable_logging'        => '1',
            'log_retention_days'    => 30,

            'enable_customer_login' => '1',
            'enable_lost_password'  => '1',
            'enable_user_panel'     => '1',
            'enable_admin_login'    => '1',

            // روش‌های ورود
            'enable_otp_login'      => '1',
            'enable_manual_login'   => '1',
            'enable_forgot_password'=> '1',

            'enable_email_verification' => '0',
            'enable_phone_verification' => '0',
            'default_user_role'         => 'customer',

            'email_from'            => 'noreply@ezlens.ir',
            'email_from_name'       => 'ایزی لنز',
            'email_logging'         => '1',
            'email_reset_template'  => 'سلام {name}، لینک بازیابی شما: {reset_url}',
            'email_welcome_template' => 'به ایزی‌لنز خوش آمدید {name}!',
            'email_forgot_otp_template' => 'سلام {name}\n\nشما درخواست بازیابی رمز عبور خود را در {site_name} ثبت کرده‌اید.\nکد تأیید شما: {code}\n\nاین کد تا {expiry} دقیقه اعتبار دارد.',
            'email_verification_template' => 'سلام {name}\n\nبرای تکمیل ثبت‌نام در {site_name}، کد تأیید زیر را وارد کنید:\n{code}\n\nاین کد تا {expiry} دقیقه اعتبار دارد.',
            'email_otp_template'    => 'کد تأیید شما: {code}',
            'email_order_template'  => 'سفارش شما با موفقیت ثبت شد. شماره سفارش: {order_id}',
            'email_promo_template'  => 'تخفیف ویژه برای شما: {discount}',
            'email_test_subject' => 'تست ایمیل EzLens',

            'smtp_enabled'          => '0',
            'smtp_host'             => 'smtp.gmail.com',
            'smtp_port'             => '587',
            'smtp_encryption'       => 'tls',
            'smtp_username'         => '',
            'smtp_password'         => '',
            'smtp_auth'             => '1',

            'otp_expiry_minutes'    => 2,
            'otp_max_attempts'      => 5,
            'otp_request_limit'     => 3,

            'sms_enabled'           => '0',
            'sms_api_key'           => '',
            'sms_line_number'       => '',
            'sms_template_id'       => '',

            // پیام‌رسانی جدید: OTP کاملاً مستقل از Campaign
            'otp_sms_enabled'       => '0',
            'otp_sms_provider'      => 'sms_ir',
            'otp_sms_api_key'       => '',
            'otp_sms_line'          => '',
            'otp_sms_template'      => '',
            'otp_sms_endpoint'      => '',
            'otp_sms_token'         => '',

            'campaign_sms_enabled'  => '0',
            'campaign_sms_provider' => 'sms_ir',
            'campaign_sms_api_key'  => '',
            'campaign_sms_line'     => '',
            'campaign_sms_template' => '',
            'campaign_sms_endpoint' => '',
            'campaign_sms_token'   => '',

            'campaign_email_enabled'    => '0',
            'campaign_email_from'       => 'noreply@ezlens.ir',
            'campaign_email_from_name'  => 'ایزی‌لنز',
            'campaign_batch_size'       => '25',
            'campaign_delay_ms'         => '300',
            'campaign_track_enabled'    => '1',
            'campaign_unsubscribe_enabled' => '0',
            'campaign_max_recipients'   => '5000',
            'campaign_retry_attempts'  => '2',
            'campaign_batch_timeout'   => '25',
            'webhook_enabled' => '0',
            'webhook_url' => '',
            'webhook_secret' => '',
            'api_rate_limit' => '60',
            'app_token_days' => '30',
            'app_otp_enabled' => '1',
            'campaign_schedule_enabled' => '1',
            'support_email_notifications' => '1',
            'support_notify_admin_email' => '1',
            'support_admin_email' => '',
            'support_max_attachment_mb' => '5',

            // حفاظت محتوای فرانت‌اند؛ فقط در صفحات EzLens
            'frontend_protection_enabled' => '0',
            'frontend_disable_copy'       => '0',
            'frontend_disable_context'    => '0',
            'frontend_disable_selection'  => '0',

            'primary_color'         => '#2b6cb0',
            'button_color'          => '#2b6cb0',
            'bg_color'              => '#0f172a',
            'logo_url'              => '',
            'font_family'           => 'Vazirmatn',

            'enable_2fa'            => '0',
            'block_invalid_ips'     => '1',
            'allowed_ips'           => '',
            'admin_max_attempts'    => 3,
            'admin_login_warning'   => '⚠️ آدرس‌های wp-admin و wp-login غیرفعال شده‌اند',

            'captcha_for_otp_login' => '0',
            'api_key'               => '',
            'enable_api'            => '1',
            'tracking_base_url'     => 'https://tracking.post.ir/?id=',

            // ===== ✨ تنظیمات جدید ماژول ویژگی‌های محصول =====
            'product_options_enabled'           => '1',
            'product_options_max_upload_size'   => '5',
            'product_options_allowed_extensions'=> 'jpg,jpeg,png,pdf',
            'product_options_show_title'        => '1',
            'product_options_show_price_in_cart'=> '1',
            'product_options_default_layout'    => '1',
            'product_options_required_fields'   => '1',
        ];
    }

    /**
     * دریافت یک مقدار تنظیمات با کلید (با کش)
     */
    public static function get($key) {
        $all = self::get_all();
        return $all[$key] ?? null;
    }

    /**
     * دریافت تمام تنظیمات (با کش)
     */
    public static function get_all() {
        if (self::$cached_settings !== null) {
            return self::$cached_settings;
        }

        $cached = wp_cache_get(self::$cache_key, 'ezlens');
        if ($cached !== false) {
            self::$cached_settings = $cached;
            return $cached;
        }

        $defaults = self::get_defaults();
        $values = [];
        foreach (array_keys($defaults) as $key) {
            $value = get_option('ezlens_auth_' . $key);
            if ($value === false) {
                $values[$key] = $defaults[$key] ?? null;
            } elseif (in_array($key, self::$sensitive_keys) && !empty($value)) {
                $decrypted = EzLens_Auth_Helper::decrypt($value);
                $values[$key] = ($decrypted !== false) ? $decrypted : $value;
            } else {
                $values[$key] = $value;
            }
        }

        wp_cache_set(self::$cache_key, $values, 'ezlens', 300);
        self::$cached_settings = $values;
        return $values;
    }

    /**
     * ذخیره یک مقدار تنظیمات و پاک‌سازی کش
     */
    public static function set($key, $value) {
        if (in_array($key, self::$sensitive_keys) && !empty($value)) {
            $value = EzLens_Auth_Helper::encrypt($value);
            if ($value === false) {
                error_log('EzLens Auth: Encryption failed for key ' . $key);
            }
        }
        update_option('ezlens_auth_' . $key, $value);
        self::clear_cache();
    }

    /**
     * پاک‌سازی کش تنظیمات (نسخه کامل)
     */
    public static function clear_cache() {
        wp_cache_delete(self::$cache_key, 'ezlens');
        self::$cached_settings = null;
        wp_cache_delete('ezlens_login_settings', 'ezlens');
        $sections = ['customer-login', 'lost-password', 'user-panel', 'admin-login'];
        foreach ($sections as $section) {
            wp_cache_delete('ezlens_page_title_' . $section, 'ezlens');
            wp_cache_delete('ezlens_page_subtitle_' . $section, 'ezlens');
        }
        wp_cache_delete('ezlens_dashboard_stats', 'ezlens');
        wp_cache_delete('ezlens_recent_users_20', 'ezlens');
    }

    /**
     * بازنشانی تمام تنظیمات
     */
    public static function reset_all() {
        $defaults = self::get_defaults();
        foreach ($defaults as $key => $value) {
            self::set($key, $value);
        }
        self::clear_cache();
    }

    // ===== توابع کمکی با کش =====

    public static function get_page_setting($section, $setting) {
        $key = 'page_' . $setting . '_' . $section;
        return get_option('ezlens_auth_' . $key, '');
    }

    public static function set_page_setting($section, $setting, $value) {
        $key = 'page_' . $setting . '_' . $section;
        update_option('ezlens_auth_' . $key, $value);
        wp_cache_delete('ezlens_page_' . $setting . '_' . $section, 'ezlens');
        self::clear_cache();
    }

    public static function get_page_title($section) {
        $cache_key = 'ezlens_page_title_' . $section;
        $title = wp_cache_get($cache_key, 'ezlens');
        if ($title !== false) return $title;
        $title = self::get_page_setting($section, 'title') ?: self::get_default_page_title($section);
        wp_cache_set($cache_key, $title, 'ezlens', 300);
        return $title;
    }

    public static function get_page_subtitle($section) {
        $cache_key = 'ezlens_page_subtitle_' . $section;
        $subtitle = wp_cache_get($cache_key, 'ezlens');
        if ($subtitle !== false) return $subtitle;
        $subtitle = self::get_page_setting($section, 'subtitle') ?: self::get_default_page_subtitle($section);
        wp_cache_set($cache_key, $subtitle, 'ezlens', 300);
        return $subtitle;
    }

    private static function get_default_page_title($section) {
        $titles = [
            'customer-login' => 'ورود / ثبت‌نام',
            'lost-password'  => 'فراموشی رمز عبور',
            'user-panel'     => 'پنل کاربری',
            'admin-login'    => 'ورود به مدیریت',
        ];
        return $titles[$section] ?? 'صفحه';
    }

    private static function get_default_page_subtitle($section) {
        $subtitles = [
            'customer-login' => 'به فروشگاه سلامت بینایی خوش آمدید',
            'lost-password'  => 'ایمیل خود را وارد کنید',
            'user-panel'     => 'مدیریت حساب کاربری',
            'admin-login'    => 'ورود به پنل مدیریت',
        ];
        return $subtitles[$section] ?? '';
    }

    public static function is_page_enabled($section) {
        return (bool) self::get('enable_' . str_replace('-', '_', $section));
    }

    public static function is_email_verification_enabled() {
        return (bool) self::get('enable_email_verification');
    }

    public static function is_phone_verification_enabled() {
        return (bool) self::get('enable_phone_verification');
    }

    public static function get_default_user_role() {
        $role = self::get('default_user_role');
        if (empty($role)) {
            $role = class_exists('WooCommerce') ? 'customer' : 'subscriber';
        }
        return $role;
    }

    public static function get_login_settings() {
        $cache_key = 'ezlens_login_settings';
        $settings = wp_cache_get($cache_key, 'ezlens');
        if ($settings !== false) return $settings;
        $all = self::get_all();
        $keys = [
            'enable_otp_login', 'enable_manual_login', 'enable_registration',
            'enable_forgot_password', 'captcha_for_otp_login', 'otp_expiry_minutes',
            'primary_color', 'button_color', 'bg_color', 'logo_url', 'font_family',
        ];
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $all[$key] ?? null;
        }
        wp_cache_set($cache_key, $settings, 'ezlens', 300);
        return $settings;
    }
}