<?php
/**
 * کلاس مدیریت تغییر مسیر ورود و نمایش صفحات سفارشی
 * @version 3.0.0 – کاملاً سازگار با ساختار جدید
 */
class EzLens_Auth_Login {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'intercept_login'], 1);
        add_action('wp_login', [$this, 'on_login'], 10, 2);
        add_action('wp_logout', [$this, 'on_logout']);
        add_filter('login_url', [$this, 'custom_login_url'], 10, 2);
        add_filter('site_url', [$this, 'filter_site_url'], 10, 4);
        add_filter('wp_redirect', [$this, 'filter_wp_redirect'], 10, 2);
        add_filter('login_redirect', [$this, 'login_redirect'], 10, 3);
    }

    public function login_redirect($redirect_to, $request, $user) {
        if (!is_wp_error($user) && is_object($user)) {
            if (in_array('administrator', (array) $user->roles)) {
                return admin_url();
            }
            if (function_exists('wc_get_page_permalink')) {
                $panel_page = wc_get_page_permalink('myaccount');
                if ($panel_page) {
                    return $panel_page;
                }
            }
            $panel_url = home_url('/my-account/');
            return apply_filters('ezlens_auth_customer_redirect', $panel_url, $user);
        }
        return $redirect_to;
    }

    public function intercept_login() {
        $admin_slug = EzLens_Auth_Settings::get('admin_login_slug');
        $customer_slug = EzLens_Auth_Settings::get('customer_login_slug');

        $request_uri = $_SERVER['REQUEST_URI'];
        $request_path = strtok($request_uri, '?');
        $home_path = parse_url(home_url(), PHP_URL_PATH);
        if ($home_path && strpos($request_path, $home_path) === 0) {
            $request_path = substr($request_path, strlen($home_path));
        }
        $request_path = trim($request_path, '/');

        $admin_slug_trim = trim($admin_slug, '/');
        $customer_slug_trim = trim($customer_slug, '/');

        if ($request_path === $admin_slug_trim || isset($_GET[$admin_slug])) {
            $this->show_custom_login_page(true);
            exit;
        }

        if ($request_path === $customer_slug_trim || isset($_GET[$customer_slug])) {
            $this->show_custom_login_page(false);
            exit;
        }

        if (is_admin() && !wp_doing_ajax() && !defined('DOING_AJAX')) {
            $current_user = wp_get_current_user();
            if (!is_user_logged_in() || !in_array('administrator', (array) $current_user->roles)) {
                $this->show_404();
                exit;
            }
        }

        if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) {
            if (!isset($_GET['action']) || !in_array($_GET['action'], ['logout', 'rp', 'postpass'])) {
                $this->show_404();
                exit;
            }
        }
    }

    private function show_404() {
        status_header(404);
        nocache_headers();
        include get_404_template();
        exit;
    }

    /**
     * نمایش صفحه ورود سفارشی – با مسیرهای جدید Assets
     */
    private function show_custom_login_page($is_admin = true) {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            wp_redirect(admin_url());
            exit;
        }

        ob_start();

        if ($is_admin) {
            // ✅ مسیر جدید قالب مدیر
            $template_path = EZLAUTH_TEMPLATES_DIR . 'admin-login.php';
            if (file_exists($template_path)) {
                $page_title = EzLens_Auth_Settings::get_page_title('admin-login');
                $page_subtitle = EzLens_Auth_Settings::get_page_subtitle('admin-login');
                $primary_color = EzLens_Auth_Settings::get('primary_color') ?: '#2b6cb0';
                $logo_url = EzLens_Auth_Settings::get('logo_url') ?: '';
                $login_slug = EzLens_Auth_Settings::get('admin_login_slug');
                $shop_url = home_url();
                $login_url = home_url('/' . $login_slug);
                $warning_text = EzLens_Auth_Settings::get('admin_login_warning');
                $nonce = wp_create_nonce('minimal_auth_secure_nonce_v5');
                $ajax_url = admin_url('admin-ajax.php');

                include $template_path;
            } else {
                echo '<h1>قالب ورود مدیر یافت نشد</h1>';
            }
        } else {
            // ✅ مسیر جدید قالب مشتری (اگر مستقیماً از اینجا فراخوانی شود)
            $template_path = EZLAUTH_FRONTEND_DIR . 'pages/login.php';
            if (file_exists($template_path)) {
                $settings = EzLens_Auth_Settings::get_all();
                $primary_color = $settings['primary_color'] ?? '#2b6cb0';
                $page_subtitle = EzLens_Auth_Settings::get_page_subtitle('customer-login');
                $shop_url = home_url();
                $ajax_nonce = wp_create_nonce('minimal_auth_secure_nonce_v5');
                $enable_otp_login = $settings['enable_otp_login'] ?? '1';
                $enable_manual_login = $settings['enable_manual_login'] ?? '1';
                $enable_registration = $settings['enable_registration'] ?? '1';
                $enable_forgot_password = $settings['enable_forgot_password'] ?? '1';
                $captcha_for_otp = $settings['captcha_for_otp_login'] ?? '0';
                $otp_expiry = $settings['otp_expiry_minutes'] ?? 2;
                $active_tabs = [];
                if ($enable_otp_login === '1') $active_tabs[] = 'otp';
                if ($enable_manual_login === '1') $active_tabs[] = 'login';
                if ($enable_registration === '1') $active_tabs[] = 'register';
                $default_tab = !empty($active_tabs) ? $active_tabs[0] : '';
                $has_active_tab = !empty($active_tabs);
                $captcha_num1 = rand(1, 9);
                $captcha_num2 = rand(1, 9);

                include $template_path;
            } else {
                echo '<h1>قالب ورود مشتری یافت نشد</h1>';
            }
        }

        $content = ob_get_clean();

        // ===== بارگذاری فایل‌های CSS و JS با مسیرهای جدید =====
        $version = EZLAUTH_VERSION;
        
        // ✅ مسیرهای جدید (frontend/assets/...)
        $core_css_url = EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-core.css';
        $auth_css_url = EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-auth.css';
        $admin_css_url = EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-admin.css';
        $resp_css_url = EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-responsive.css';
        
        $auth_js_url = EZLAUTH_PLUGIN_URL . 'frontend/assets/js/auth.js';
        $admin_login_js_url = EZLAUTH_PLUGIN_URL . 'frontend/assets/js/admin-login.js';

        // نسخه‌ها (بر اساس زمان تغییر فایل)
        $core_css_ver = file_exists(EZLAUTH_FRONTEND_DIR . 'assets/css/frontend-core.css') ? filemtime(EZLAUTH_FRONTEND_DIR . 'assets/css/frontend-core.css') : $version;
        $auth_css_ver = file_exists(EZLAUTH_FRONTEND_DIR . 'assets/css/frontend-auth.css') ? filemtime(EZLAUTH_FRONTEND_DIR . 'assets/css/frontend-auth.css') : $version;
        $admin_css_ver = file_exists(EZLAUTH_FRONTEND_DIR . 'assets/css/frontend-admin.css') ? filemtime(EZLAUTH_FRONTEND_DIR . 'assets/css/frontend-admin.css') : $version;
        $resp_css_ver = file_exists(EZLAUTH_FRONTEND_DIR . 'assets/css/frontend-responsive.css') ? filemtime(EZLAUTH_FRONTEND_DIR . 'assets/css/frontend-responsive.css') : $version;
        $auth_js_ver = file_exists(EZLAUTH_FRONTEND_DIR . 'assets/js/auth.js') ? filemtime(EZLAUTH_FRONTEND_DIR . 'assets/js/auth.js') : $version;
        $admin_login_js_ver = file_exists(EZLAUTH_FRONTEND_DIR . 'assets/js/admin-login.js') ? filemtime(EZLAUTH_FRONTEND_DIR . 'assets/js/admin-login.js') : $version;

        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('minimal_auth_secure_nonce_v5');
        $otp_expiry = (int) EzLens_Auth_Settings::get('otp_expiry_minutes') ?: 2;

        $frontend_data = [
            'ajax_url'   => $ajax_url,
            'nonce'      => $nonce,
            'otp_expiry' => $otp_expiry,
        ];
        $js_data = 'var ezlens_frontend = ' . wp_json_encode($frontend_data) . ';';

        // jQuery (از هسته وردپرس)
        $jquery_url = includes_url('js/jquery/jquery.min.js');
        $jquery_migrate_url = includes_url('js/jquery/jquery-migrate.min.js');

        echo '<!DOCTYPE html>
<html dir="rtl" lang="fa-IR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود - ' . esc_html(get_bloginfo('name')) . '</title>
    <link rel="stylesheet" href="' . esc_url($core_css_url) . '?ver=' . $core_css_ver . '">
    <link rel="stylesheet" href="' . esc_url($auth_css_url) . '?ver=' . $auth_css_ver . '">
    <link rel="stylesheet" href="' . esc_url($admin_css_url) . '?ver=' . $admin_css_ver . '">
    <link rel="stylesheet" href="' . esc_url($resp_css_url) . '?ver=' . $resp_css_ver . '">
    <style>
        body { margin:0; padding:0; background: #f1f5f9; font-family: Vazirmatn, IRANYekan, Tahoma, Arial, sans-serif; }
        .admin-auth-wrap { min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .min-auth-wrapper { margin: 20px auto; }
    </style>
</head>
<body>
    ' . $content . '
    <script src="' . esc_url($jquery_url) . '?ver=' . $version . '"></script>
    <script src="' . esc_url($jquery_migrate_url) . '?ver=' . $version . '"></script>
    <script>' . $js_data . '</script>
    <script src="' . esc_url($auth_js_url) . '?ver=' . $auth_js_ver . '"></script>
    <script src="' . esc_url($admin_login_js_url) . '?ver=' . $admin_login_js_ver . '"></script>
</body>
</html>';
        exit;
    }

    public function on_login($user_login, $user) {
        if (EzLens_Auth_Settings::get('enable_logging')) {
            EzLens_Auth_Logger::log($user->ID, 'login');
        }
    }

    public function on_logout() {
        if (EzLens_Auth_Settings::get('enable_logging')) {
            $user_id = get_current_user_id();
            if ($user_id) {
                EzLens_Auth_Logger::log($user_id, 'logout');
            }
        }
    }

    public function custom_login_url($url, $redirect) {
        $slug = EzLens_Auth_Settings::get('admin_login_slug');
        return home_url('/' . $slug);
    }

    public function filter_site_url($url, $path, $scheme, $blog_id) {
        if ($path === 'wp-login.php') {
            $slug = EzLens_Auth_Settings::get('admin_login_slug');
            return home_url('/' . $slug);
        }
        return $url;
    }

    public function filter_wp_redirect($location, $status) {
        if (strpos($location, 'wp-login.php') !== false) {
            $slug = EzLens_Auth_Settings::get('admin_login_slug');
            return home_url('/' . $slug);
        }
        return $location;
    }
}