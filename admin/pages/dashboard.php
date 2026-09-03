<?php
/**
 * کلاس مدیریت منوی ادمین و صفحات – نسخه بازسازیشده با مسیرهای جدید
 * @version 3.0.0
 */
class EzLens_Auth_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_ezlens_auth_save_settings', [$this, 'save_settings']);
        add_action('admin_post_ezlens_auth_reset_settings', [$this, 'reset_settings']);
        add_action('admin_post_ezlens_auth_save_code', [$this, 'save_code']);
        add_action('admin_post_ezlens_auth_reset_code', [$this, 'reset_code']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'EzLens Secure Login',
            'EzLens Auth',
            'manage_options',
            'ezlens-auth',
            [$this, 'render_dashboard'],
            'dashicons-lock',
            30
        );

        add_submenu_page('ezlens-auth', 'داشبورد', '📋 داشبورد', 'manage_options', 'ezlens-auth', [$this, 'render_dashboard']);
        add_submenu_page('ezlens-auth', 'تنظیمات عمومی', '⚙️ تنظیمات', 'manage_options', 'ezlens-auth-settings', [$this, 'render_settings']);
        add_submenu_page('ezlens-auth', 'ویرایش صفحات', '✏️ ویرایش صفحات', 'manage_options', 'ezlens-auth-editor', [$this, 'render_editor']);
        add_submenu_page('ezlens-auth', 'گزارش‌ها', '📊 گزارش‌ها', 'manage_options', 'ezlens-auth-logs', [$this, 'render_logs']);
        add_submenu_page('ezlens-auth', 'بکاپ و API', '💾 بکاپ و API', 'manage_options', 'ezlens-auth-backup', [$this, 'render_backup']);
        add_submenu_page('ezlens-auth', 'کمپینگ تبلیغاتی', '📢 کمپینگ', 'manage_options', 'ezlens-auth-campaign', [$this, 'render_campaign']);
        add_submenu_page('ezlens-auth', 'پشتیبانی (تیکت‌ها)', '💬 پشتیبانی', 'manage_options', 'ezlens-auth-support', [$this, 'render_support']);
        
        // ===== ✅ منوی جدید: ویژگی‌های محصول =====
        add_submenu_page(
            'ezlens-auth',
            'ویژگی‌های محصول',
            '🧩 ویژگی‌های محصول',
            'manage_options',
            'ezlens-product-options',
            [$this, 'render_product_options']
        );
    }

    public function render_dashboard() {
        $stats = $this->get_dashboard_stats();
        $users_with_phone = $this->get_recent_users_with_phone(20);
        include EZLAUTH_TEMPLATES_DIR . 'admin-dashboard.php';
    }

    public function render_settings() {
        include EZLAUTH_ADMIN_DIR . 'pages/settings.php';
    }

    public function render_editor() {
        $section = isset($_GET['section']) ? sanitize_key($_GET['section']) : 'customer-login';
        $codes = $this->get_saved_codes($section);
        include EZLAUTH_ADMIN_DIR . 'pages/editor.php';
    }

    public function render_logs() {
        $logs = EzLens_Auth_Logger::get_recent(null, 50);
        include EZLAUTH_ADMIN_DIR . 'pages/logs.php';
    }

    public function render_backup() {
        include EZLAUTH_ADMIN_DIR . 'pages/backup.php';
    }

    public function render_campaign() {
        include EZLAUTH_ADMIN_DIR . 'pages/campaign.php';
    }

    public function render_support() {
        include EZLAUTH_ADMIN_DIR . 'pages/support.php';
    }

    /**
     * ✅ نمایش صفحه ویژگی‌های محصول (لیست پالت‌ها یا ویرایشگر)
     * بر اساس پارامتر action در URL
     */
    public function render_product_options() {
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
        
        if ($action === 'add' || $action === 'edit') {
            // نمایش ویرایشگر (فرم افزودن/ویرایش)
            $editor_file = EZLAUTH_MODULES_DIR . 'product-options/admin/pages/template-editor.php';
            if (file_exists($editor_file)) {
                include $editor_file;
            } else {
                echo '<div class="wrap"><h1>🧩 ویژگی‌های محصول</h1><p style="color:#dc2626;">فایل ویرایشگر یافت نشد. لطفاً فایل template-editor.php را در مسیر زیر قرار دهید:<br><code>' . esc_html(EZLAUTH_MODULES_DIR . 'product-options/admin/pages/template-editor.php') . '</code></p></div>';
            }
        } else {
            // نمایش لیست پالت‌ها
            $list_file = EZLAUTH_MODULES_DIR . 'product-options/admin/pages/template-list.php';
            if (file_exists($list_file)) {
                include $list_file;
            } else {
                echo '<div class="wrap"><h1>🧩 ویژگی‌های محصول</h1><p style="color:#dc2626;">فایل لیست پالت‌ها یافت نشد. لطفاً فایل template-list.php را در مسیر زیر قرار دهید:<br><code>' . esc_html(EZLAUTH_MODULES_DIR . 'product-options/admin/pages/template-list.php') . '</code></p></div>';
            }
        }
    }

    private function get_dashboard_stats() {
        $cache_key = 'ezlens_dashboard_stats';
        $stats = wp_cache_get($cache_key, 'ezlens');
        if ($stats !== false) {
            return $stats;
        }

        $users = count_users();
        $stats = [
            'users'   => $users['total_users'],
            'admins'  => $users['avail_roles']['administrator'] ?? 0,
            'logins'  => EzLens_Auth_Logger::get_stats('login'),
            'logouts' => EzLens_Auth_Logger::get_stats('logout'),
            'failed'  => 0,
            'recent'  => EzLens_Auth_Logger::get_recent(null, 10),
        ];
        wp_cache_set($cache_key, $stats, 'ezlens', 300);
        return $stats;
    }

    public function get_recent_users_with_phone($limit = 20) {
        $cache_key = 'ezlens_recent_users_' . $limit;
        $users_data = wp_cache_get($cache_key, 'ezlens');
        if ($users_data !== false) {
            return $users_data;
        }

        $user_query = new WP_User_Query([
            'number'  => $limit,
            'orderby' => 'registered',
            'order'   => 'DESC',
            'fields'  => ['ID', 'user_login', 'user_email', 'display_name', 'user_registered'],
        ]);

        $users = $user_query->get_results();
        $users_with_phone = [];

        if (!empty($users)) {
            $user_ids = wp_list_pluck($users, 'ID');
            $phone_meta = [];
            foreach ($user_ids as $uid) {
                $phone = get_user_meta($uid, 'billing_phone', true);
                if (empty($phone)) {
                    $phone = get_user_meta($uid, 'user_phone', true);
                }
                $phone_meta[$uid] = $phone ?: '—';
            }

            foreach ($users as $user) {
                $users_with_phone[] = (object) [
                    'id'           => $user->ID,
                    'username'     => $user->user_login,
                    'display_name' => $user->display_name,
                    'email'        => $user->user_email,
                    'phone'        => $phone_meta[$user->ID] ?? '—',
                    'registered'   => $user->user_registered,
                ];
            }
        }

        wp_cache_set($cache_key, $users_with_phone, 'ezlens', 300);
        return $users_with_phone;
    }

    public function save_settings() {
        check_admin_referer('ezlens_auth_save_settings');
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }

        $defaults = EzLens_Auth_Settings::get_defaults();
        $checkbox_keys = [
            'enable_otp_login', 'enable_manual_login', 'enable_registration',
            'enable_forgot_password', 'enable_logging', 'enable_2fa', 'block_invalid_ips',
            'smtp_auth', 'smtp_enabled', 'sms_enabled', 'captcha_for_otp_login',
            'enable_api', 'enable_email_verification', 'enable_phone_verification',
            'enable_customer_login', 'enable_lost_password', 'enable_user_panel',
            'enable_admin_login', 'email_logging',
        ];

        foreach (array_keys($defaults) as $key) {
            if (isset($_POST[$key])) {
                $value = sanitize_text_field($_POST[$key]);
                EzLens_Auth_Settings::set($key, $value);
            } elseif (in_array($key, $checkbox_keys)) {
                EzLens_Auth_Settings::set($key, '0');
            }
        }

        $this->clear_stats_cache();
        EzLens_Auth_Settings::clear_cache();
        wp_redirect(admin_url('admin.php?page=ezlens-auth-settings&saved=1'));
        exit;
    }

    public function reset_settings() {
        check_admin_referer('ezlens_auth_reset_settings');
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }
        EzLens_Auth_Settings::reset_all();
        $this->clear_stats_cache();
        wp_redirect(admin_url('admin.php?page=ezlens-auth-settings&reset=1'));
        exit;
    }

    public function save_code() {
        check_admin_referer('ezlens_auth_save_code');
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }
        $section = sanitize_key($_POST['section']);
        $html = wp_kses_post($_POST['html']);
        $css = sanitize_textarea_field($_POST['css']);
        $js = sanitize_textarea_field($_POST['js']);
        update_option('ezlens_auth_codes_' . $section, [
            'html' => $html,
            'css'  => $css,
            'js'   => $js,
        ]);
        wp_redirect(admin_url('admin.php?page=ezlens-auth-editor&section=' . $section . '&saved=1'));
        exit;
    }

    public function reset_code() {
        check_admin_referer('ezlens_auth_reset_code');
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }
        $section = sanitize_key($_POST['section']);
        delete_option('ezlens_auth_codes_' . $section);
        wp_redirect(admin_url('admin.php?page=ezlens-auth-editor&section=' . $section . '&reset=1'));
        exit;
    }

    private function get_saved_codes($section) {
        $defaults = $this->get_default_codes($section);
        $saved = get_option('ezlens_auth_codes_' . $section, []);
        return wp_parse_args($saved, $defaults);
    }

    private function get_default_codes($section) {
        $base = EZLAUTH_TEMPLATES_DIR . 'defaults/';
        $files = [
            'html' => $section . '.html',
            'css'  => $section . '.css',
            'js'   => $section . '.js',
        ];
        $codes = [];
        foreach ($files as $key => $file) {
            $path = $base . $file;
            $codes[$key] = file_exists($path) ? file_get_contents($path) : '';
        }
        return $codes;
    }

    private function clear_stats_cache() {
        wp_cache_delete('ezlens_dashboard_stats', 'ezlens');
        wp_cache_delete('ezlens_recent_users_20', 'ezlens');
        EzLens_Auth_Settings::clear_cache();
    }
}