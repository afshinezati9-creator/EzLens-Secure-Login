<?php
/**
 * کلاس مدیریت REST API و کلیدهای دسترسی خارجی
 * EzLens Auth - API Class
 * @version 2.1.4 (اصلاحات امنیتی)
 */
class EzLens_Auth_API {

    private static $instance = null;
    private $namespace = 'ezlens/v1';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('admin_init', [$this, 'generate_api_key_if_missing']);
    }

    /**
     * تولید کلید API در صورت عدم وجود
     */
    public function generate_api_key_if_missing() {
        if (empty(EzLens_Auth_Settings::get('api_key'))) {
            EzLens_Auth_Settings::set('api_key', $this->generate_key());
        }
    }

    /**
     * تولید کلید امنیتی تصادفی
     */
    private function generate_key() {
        return 'ezl_' . bin2hex(random_bytes(32));
    }

    /**
     * بازسازی کلید API
     */
    public function regenerate_key() {
        $new_key = $this->generate_key();
        EzLens_Auth_Settings::set('api_key', $new_key);
        return $new_key;
    }

    /**
     * ثبت مسیرهای REST API
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/stats', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_stats'],
            'permission_callback' => [$this, 'verify_api_key'],
        ]);

        register_rest_route($this->namespace, '/logs', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_logs'],
            'permission_callback' => [$this, 'verify_api_key'],
        ]);

        register_rest_route($this->namespace, '/settings', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_settings'],
            'permission_callback' => [$this, 'verify_api_key'],
        ]);

        register_rest_route($this->namespace, '/settings', [
            'methods'             => 'POST',
            'callback'            => [$this, 'update_settings'],
            'permission_callback' => [$this, 'verify_api_key'],
        ]);
    }

    /**
     * احراز هویت با کلید API (اصلاح‌شده – بررسی خالی بودن)
     */
    public function verify_api_key($request) {
        $api_key = $request->get_header('X-API-Key');
        
        // ۱. اگر کلید ارسال نشده باشد
        if (empty($api_key)) {
            return false;
        }

        $stored_key = EzLens_Auth_Settings::get('api_key');
        
        // ۲. اگر کلید ذخیره‌شده خالی باشد (نباید اتفاق بیفتد، اما برای اطمینان)
        if (empty($stored_key)) {
            return false;
        }

        // ۳. مقایسه امن با hash_equals
        return hash_equals($stored_key, $api_key);
    }

    /**
     * دریافت آمار
     */
    public function get_stats() {
        $users = count_users();
        return rest_ensure_response([
            'success' => true,
            'data'    => [
                'users'   => $users['total_users'],
                'admins'  => $users['avail_roles']['administrator'] ?? 0,
                'logins'  => EzLens_Auth_Logger::get_stats('login'),
                'logouts' => EzLens_Auth_Logger::get_stats('logout'),
                'failed_attempts' => EzLens_Auth_Logger::get_stats('failed_login'),
                'timestamp' => current_time('mysql'),
            ]
        ]);
    }

    /**
     * دریافت لاگ‌ها
     */
    public function get_logs($request) {
        $limit = $request->get_param('limit') ? (int)$request->get_param('limit') : 20;
        $action = $request->get_param('action') ? sanitize_text_field($request->get_param('action')) : null;

        if ($action && !in_array($action, ['login', 'logout', 'failed_login'])) {
            return new WP_Error('invalid_action', 'عمل نامعتبر است.', ['status' => 400]);
        }

        $logs = EzLens_Auth_Logger::get_recent($action, $limit);
        return rest_ensure_response([
            'success' => true,
            'data'    => $logs,
            'count'   => count($logs)
        ]);
    }

    /**
     * دریافت تنظیمات (به جز کلیدهای حساس)
     * اصلاح‌شده: sms_api_key نیز حذف می‌شود
     */
    public function get_settings() {
        $settings = EzLens_Auth_Settings::get_all();
        
        // حذف کلیدهای حساس
        unset($settings['api_key']);
        unset($settings['captcha_secret_key']);
        unset($settings['sms_api_key']); // 🔴 بحرانی: اضافه شد
        
        return rest_ensure_response([
            'success' => true,
            'data'    => $settings
        ]);
    }

    /**
     * به‌روزرسانی تنظیمات (فقط برای کلیدهای غیرحساس)
     * اصلاح‌شده: sms_api_key از لیست مجاز حذف شد
     */
    public function update_settings($request) {
        $params = $request->get_json_params();
        if (empty($params) || !is_array($params)) {
            return new WP_Error('invalid_data', 'داده نامعتبر است.', ['status' => 400]);
        }

        // لیست کلیدهای مجاز برای به‌روزرسانی
        // 🔴 بحرانی: sms_api_key حذف شد
        $allowed_keys = [
            'admin_login_slug', 'enable_registration', 'captcha_type',
            'max_attempts', 'lockout_minutes', 'enable_logging',
            'log_retention_days', 'email_from', 'email_from_name',
            'email_reset_template', 'email_welcome_template',
            'primary_color', 'button_color', 'bg_color', 'logo_url', 'font_family',
            'enable_2fa', 'block_invalid_ips', 'allowed_ips',
            'admin_max_attempts', 'admin_login_warning',
            'sms_enabled', 'sms_line_number', 'sms_template_id' // sms_api_key حذف شد
        ];

        $updated = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $allowed_keys)) {
                EzLens_Auth_Settings::set($key, sanitize_text_field($value));
                $updated[] = $key;
            }
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'تنظیمات به‌روزرسانی شد.',
            'updated' => $updated
        ]);
    }
}