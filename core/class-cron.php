<?php
/**
 * کلاس مدیریت کرون جاب برای پاک‌سازی خودکار
 * EzLens Auth - Cron
 * @version 1.0
 */
class EzLens_Auth_Cron {

    private static $instance = null;
    private $hook_name = 'ezlens_auth_daily_cleanup';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // ثبت اکشن برای کرون جاب
        add_action($this->hook_name, [$this, 'run_cleanup']);
        
        // برنامه‌ریزی کرون جاب در زمان بارگذاری وردپرس
        add_action('wp_loaded', [$this, 'schedule_cleanup']);
        
        // لغو برنامه‌ریزی در صورت غیرفعال‌سازی پلاگین (در فایل اصلی مدیریت می‌شود)
    }

    /**
     * برنامه‌ریزی کرون جاب روزانه
     */
    public function schedule_cleanup() {
        if (!wp_next_scheduled($this->hook_name)) {
            // اجرا در نیمه‌شب هر روز
            $timestamp = strtotime('tomorrow 00:00:00');
            wp_schedule_event($timestamp, 'daily', $this->hook_name);
        }
    }

    /**
     * لغو برنامه‌ریزی کرون جاب
     */
    public function unschedule_cleanup() {
        $timestamp = wp_next_scheduled($this->hook_name);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->hook_name);
        }
        wp_clear_scheduled_hook($this->hook_name);
    }

    /**
     * اجرای عملیات پاک‌سازی (توسط کرون جاب فراخوانی می‌شود)
     */
    public function run_cleanup() {
        // ۱. پاک‌سازی لاگ‌های قدیمی
        $retention_days = (int) EzLens_Auth_Settings::get('log_retention_days') ?: 30;
        if ($retention_days > 0) {
            EzLens_Auth_Logger::clean_old($retention_days);
            error_log('EzLens Auth: Cleaned logs older than ' . $retention_days . ' days.');
        }

        // ۲. پاک‌سازی کدهای OTP منقضی‌شده
        EzLens_Auth_OTP::clean_expired();
        error_log('EzLens Auth: Cleaned expired OTP codes.');

        // ۳. (اختیاری) پاک‌سازی کش‌های مربوط به لاگ‌ها
        wp_cache_delete('ezlens_stats_logins');
        wp_cache_delete('ezlens_stats_logouts');

        return true;
    }

    /**
     * بررسی وضعیت کرون جاب (برای نمایش در داشبورد)
     */
    public function is_scheduled() {
        return wp_next_scheduled($this->hook_name) !== false;
    }

    /**
     * دریافت زمان اجرای بعدی
     */
    public function get_next_run_time() {
        $timestamp = wp_next_scheduled($this->hook_name);
        return $timestamp ? date_i18n('Y/m/d H:i:s', $timestamp) : '—';
    }
}