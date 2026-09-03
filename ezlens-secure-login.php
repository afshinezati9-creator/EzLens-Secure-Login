<?php
/**
 * Plugin Name: EzLens Secure Login
 * Plugin URI: https://ezlens.ir
 * Description: راه‌حل جامع احراز هویت، پنل کاربری، تیکت پشتیبانی، کمپینگ تبلیغاتی و داشبورد مدیریت برای فروشگاه‌های وردپرسی | طراحی و توسعه توسط افشین عزتی
 * Version: 5.4.0
 * Author: افشین عزتی
 * Author URI: https://ezlens.ir
 * License: GPL v2 or later
 * Text Domain: ezlens-auth
 * Domain Path: /languages
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

if (!defined('EZLAUTH_VERSION')) {
    define('EZLAUTH_VERSION', '5.4.0');
}

if (!defined('EZLAUTH_PLUGIN_DIR')) {
    define('EZLAUTH_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('EZLAUTH_PLUGIN_URL')) {
    define('EZLAUTH_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('EZLAUTH_PLUGIN_BASENAME')) {
    define('EZLAUTH_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// ===== مسیرهای جدید (بعد از بازسازی) =====
define('EZLAUTH_CORE_DIR', EZLAUTH_PLUGIN_DIR . 'core/');
define('EZLAUTH_MODULES_DIR', EZLAUTH_PLUGIN_DIR . 'modules/');
define('EZLAUTH_ADMIN_DIR', EZLAUTH_PLUGIN_DIR . 'admin/');
define('EZLAUTH_FRONTEND_DIR', EZLAUTH_PLUGIN_DIR . 'frontend/');
define('EZLAUTH_API_DIR', EZLAUTH_PLUGIN_DIR . 'api/');
define('EZLAUTH_SHARED_DIR', EZLAUTH_PLUGIN_DIR . 'shared/');
define('EZLAUTH_INCLUDES_DIR', EZLAUTH_PLUGIN_DIR . 'includes/');
define('EZLAUTH_TEMPLATES_DIR', EZLAUTH_PLUGIN_DIR . 'templates/');

// New core foundation (non-invasive, legacy-compatible)
$ezlens_autoloader = EZLAUTH_PLUGIN_DIR . 'bootstrap/autoloader.php';
if (is_readable($ezlens_autoloader)) require_once $ezlens_autoloader;
if (class_exists('\EzLens\Core\Plugin')) {\EzLens\Core\Plugin::instance()->boot();}


/*
|--------------------------------------------------------------------------
| 1. Load Core Classes (با مسیرهای جدید)
|--------------------------------------------------------------------------
*/

function ezlens_auth_load_core_classes() {

    $core_files = array(
        // Shared helpers
        EZLAUTH_SHARED_DIR . 'helpers/class-helper.php',
        
        // Core
        EZLAUTH_CORE_DIR . 'class-settings.php',
        EZLAUTH_CORE_DIR . 'class-upgrader.php',
        EZLAUTH_CORE_DIR . 'class-cron.php',
        
        // Modules - Messaging
        EZLAUTH_MODULES_DIR . 'messaging/class-messaging.php',
        EZLAUTH_MODULES_DIR . 'messaging/providers/class-provider-interface.php',
        EZLAUTH_MODULES_DIR . 'messaging/providers/class-sms-ir-provider.php',
        EZLAUTH_MODULES_DIR . 'messaging/providers/class-kavenegar-provider.php',
        EZLAUTH_MODULES_DIR . 'messaging/providers/class-custom-provider.php',
        EZLAUTH_MODULES_DIR . 'messaging/class-sms.php',
        
        // Modules - Analytics
        EZLAUTH_MODULES_DIR . 'analytics/class-logger.php',
        EZLAUTH_MODULES_DIR . 'analytics/class-audit.php',
        EZLAUTH_MODULES_DIR . 'analytics/class-health.php',
        
        // Modules - Auth
        EZLAUTH_MODULES_DIR . 'auth/class-otp.php',
        EZLAUTH_MODULES_DIR . 'auth/class-login.php',
        EZLAUTH_MODULES_DIR . 'auth/class-app-auth.php',
        
        // Modules - Campaign
        EZLAUTH_MODULES_DIR . 'campaign/class-campaign.php',
        EZLAUTH_MODULES_DIR . 'campaign/class-campaign-queue.php',
        EZLAUTH_MODULES_DIR . 'campaign/class-campaign-compliance.php',
        
        // Modules - Support
        EZLAUTH_MODULES_DIR . 'support/class-support.php',
        
        // Modules - Customer
        EZLAUTH_MODULES_DIR . 'customer/class-notifications.php',
        EZLAUTH_MODULES_DIR . 'customer/class-order-meta.php',
        EZLAUTH_MODULES_DIR . 'customer/class-order-tracking.php',
        EZLAUTH_MODULES_DIR . 'customer/class-user-customizations.php',
        
        // Modules - Product Options
        EZLAUTH_MODULES_DIR . 'product-options/class-install.php',
        EZLAUTH_MODULES_DIR . 'product-options/class-template-manager.php',
        EZLAUTH_MODULES_DIR . 'product-options/class-ajax-handler.php',
        EZLAUTH_MODULES_DIR . 'product-options/admin/meta-box.php',
        EZLAUTH_MODULES_DIR . 'product-options/class-field-renderer.php',
        EZLAUTH_MODULES_DIR . 'product-options/class-order-display.php', // ← خط جدید
        
        // API
        EZLAUTH_API_DIR . 'class-api.php',
        EZLAUTH_API_DIR . 'class-api-v2.php',
        EZLAUTH_API_DIR . 'class-webhooks.php',
    );

    foreach ($core_files as $file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }

    // راه‌اندازی سرویس‌های اصلی
    if (class_exists('EzLens_Auth_Upgrader')) {
        EzLens_Auth_Upgrader::maybe_upgrade();
    }

    if (class_exists('EzLens_Auth_Login')) {
        EzLens_Auth_Login::get_instance();
    }

    foreach (['EzLens_Auth_Audit','EzLens_Auth_Notifications','EzLens_Auth_Campaign_Compliance','EzLens_Auth_App_Auth','EzLens_Auth_Campaign_Queue','EzLens_Auth_Webhooks','EzLens_Auth_API','EzLens_Auth_API_V2'] as $service) {
        if (class_exists($service)) {
            $service::get_instance();
        }
    }

    // مقداردهی ماژول ویژگی‌های محصول
    if (class_exists('EzLens_Product_Options')) {
        EzLens_Product_Options::get_instance();
    }

    // مقداردهی ماژول مدیریت قالب
    if (class_exists('EzLens_Template_Manager')) {
        EzLens_Template_Manager::get_instance();
    }

    // مقداردهی ماژول AJAX Handler
    if (class_exists('EzLens_Product_Options_Ajax_Handler')) {
        EzLens_Product_Options_Ajax_Handler::get_instance();
    }

    // مقداردهی ماژول رندر فیلدها
    if (class_exists('EzLens_Field_Renderer')) {
        EzLens_Field_Renderer::get_instance();
    }

    // مقداردهی ماژول نمایش سفارش (جدید)
    if (class_exists('EzLens_Order_Display')) {
        EzLens_Order_Display::get_instance();
    }
}

add_action(
    'plugins_loaded',
    'ezlens_auth_load_core_classes'
);


/*
|--------------------------------------------------------------------------
| 2. Admin Class (با مسیر جدید)
|--------------------------------------------------------------------------
*/

if (is_admin()) {
    $admin_file = EZLAUTH_ADMIN_DIR . 'pages/dashboard.php';
    if (file_exists($admin_file)) {
        require_once $admin_file;
    }
    if (class_exists('EzLens_Auth_Admin')) {
        EzLens_Auth_Admin::get_instance();
    }
}


/*
|--------------------------------------------------------------------------
| 3. Shortcodes (با مسیر جدید)
|--------------------------------------------------------------------------
*/

function ezlens_auth_load_shortcode_classes() {
    $file = EZLAUTH_INCLUDES_DIR . 'shortcodes/class-shortcodes-loader.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
add_action('init', 'ezlens_auth_load_shortcode_classes');


/*
|--------------------------------------------------------------------------
| 4. AJAX Classes (با مسیر جدید)
|--------------------------------------------------------------------------
*/

function ezlens_auth_load_ajax_classes() {
    if (!wp_doing_ajax()) {
        return;
    }
    $file = EZLAUTH_INCLUDES_DIR . 'ajax/class-ajax-loader.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
add_action('init', 'ezlens_auth_load_ajax_classes', 5);


/*
|--------------------------------------------------------------------------
| 5. Main Class
|--------------------------------------------------------------------------
*/

class EzLens_Auth {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {

        register_activation_hook(
            __FILE__,
            array($this, 'activate')
        );

        register_deactivation_hook(
            __FILE__,
            array($this, 'deactivate')
        );

        add_action(
            'admin_enqueue_scripts',
            array($this, 'enqueue_admin_assets'),
            9999
        );

        add_action(
            'wp_enqueue_scripts',
            array($this, 'enqueue_frontend_assets')
        );

        add_action('wp_footer', array($this, 'render_frontend_protection'), 9999);

        add_action(
            'wp_print_scripts',
            array($this, 'remove_unwanted_scripts'),
            99999
        );

        add_action(
            'admin_print_scripts',
            array($this, 'remove_unwanted_scripts'),
            99999
        );

        add_action(
            'wp_print_footer_scripts',
            array($this, 'remove_unwanted_scripts'),
            99999
        );

        add_action(
            'admin_print_footer_scripts',
            array($this, 'remove_unwanted_scripts'),
            99999
        );

        add_filter(
            'script_loader_src',
            array($this, 'block_share_modal_src'),
            99999,
            2
        );

        add_filter(
            'script_loader_tag',
            array($this, 'block_share_modal_script'),
            99999,
            3
        );

        add_filter(
            'plugin_action_links_' . EZLAUTH_PLUGIN_BASENAME,
            array($this, 'add_action_links')
        );
    }

    public function add_action_links($links) {
        $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=ezlens-auth-settings')) . '">⚙️ تنظیمات</a>';
        $dashboard_link = '<a href="' . esc_url(admin_url('admin.php?page=ezlens-auth')) . '">📊 داشبورد</a>';
        $support_link = '<a href="' . esc_url(admin_url('admin.php?page=ezlens-auth-support')) . '">💬 پشتیبانی</a>';
        $docs_link = '<a href="' . esc_url(admin_url('admin.php?page=ezlens-auth-backup')) . '">🔑 API</a>';
        array_unshift($links, $settings_link, $dashboard_link, $support_link, $docs_link);
        return $links;
    }

    public function activate() {
        $required_files = array(
            EZLAUTH_INCLUDES_DIR . 'class-logger.php',
            EZLAUTH_INCLUDES_DIR . 'class-otp.php',
            EZLAUTH_INCLUDES_DIR . 'class-settings.php',
            EZLAUTH_INCLUDES_DIR . 'helpers/class-helper.php',
            EZLAUTH_INCLUDES_DIR . 'class-cron.php',
            EZLAUTH_INCLUDES_DIR . 'class-campaign.php',
            EZLAUTH_INCLUDES_DIR . 'class-support.php',
            EZLAUTH_INCLUDES_DIR . 'class-audit.php',
            EZLAUTH_INCLUDES_DIR . 'class-notifications.php',
            EZLAUTH_INCLUDES_DIR . 'class-health.php',
            EZLAUTH_INCLUDES_DIR . 'class-webhooks.php',
            EZLAUTH_INCLUDES_DIR . 'class-app-auth.php',
            EZLAUTH_INCLUDES_DIR . 'class-campaign-compliance.php',
            EZLAUTH_INCLUDES_DIR . 'class-campaign-queue.php',
            EZLAUTH_INCLUDES_DIR . 'class-api-v2.php',
            EZLAUTH_INCLUDES_DIR . 'class-upgrader.php',
            EZLAUTH_INCLUDES_DIR . 'class-messaging.php',
            EZLAUTH_INCLUDES_DIR . 'messaging/class-provider-interface.php',
            EZLAUTH_INCLUDES_DIR . 'messaging/class-sms-ir-provider.php',
            EZLAUTH_INCLUDES_DIR . 'messaging/class-kavenegar-provider.php',
            EZLAUTH_INCLUDES_DIR . 'messaging/class-custom-provider.php',
        );

        foreach ($required_files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }

        if (class_exists('EzLens_Auth_Logger')) {
            EzLens_Auth_Logger::create_table();
        }
        if (class_exists('EzLens_Auth_OTP')) {
            EzLens_Auth_OTP::create_table();
        }
        if (class_exists('EzLens_Auth_Campaign')) {
            $campaign = EzLens_Auth_Campaign::get_instance();
            $campaign->create_tables();
        }
        if (class_exists('EzLens_Auth_Support')) {
            $support = EzLens_Auth_Support::get_instance();
            $support->create_tables();
        }
        foreach (['EzLens_Auth_Audit','EzLens_Auth_Notifications','EzLens_Auth_Campaign_Compliance','EzLens_Auth_App_Auth'] as $service) {
            if (class_exists($service)) {
                $service::get_instance();
            }
        }

        $defaults = array();
        if (class_exists('EzLens_Auth_Settings')) {
            $defaults = EzLens_Auth_Settings::get_defaults();
        }
        foreach ($defaults as $key => $value) {
            if (false === get_option('ezlens_auth_' . $key)) {
                update_option('ezlens_auth_' . $key, $value);
            }
        }

        if (class_exists('EzLens_Auth_Upgrader')) {
            EzLens_Auth_Upgrader::install();
            update_option('ezlens_auth_db_version', EzLens_Auth_Upgrader::DB_VERSION, false);
        }

        $page_settings = array(
            'page_title_customer-login' => 'ورود / ثبت‌نام',
            'page_subtitle_customer-login' => 'به فروشگاه سلامت بینایی خوش آمدید',
            'page_title_lost-password' => 'فراموشی رمز عبور',
            'page_subtitle_lost-password' => 'ایمیل خود را وارد کنید',
            'page_title_user-panel' => 'پنل کاربری',
            'page_subtitle_user-panel' => 'مدیریت حساب کاربری',
            'page_title_admin-login' => 'ورود به مدیریت',
            'page_subtitle_admin-login' => 'ورود به پنل مدیریت',
        );
        foreach ($page_settings as $key => $value) {
            if (false === get_option('ezlens_auth_' . $key)) {
                add_option('ezlens_auth_' . $key, $value);
            }
        }

        if (class_exists('EzLens_Auth_Cron')) {
            $cron = EzLens_Auth_Cron::get_instance();
            $cron->schedule_cleanup();
        }

        flush_rewrite_rules();
    }

    public function deactivate() {
        $cron_path = EZLAUTH_INCLUDES_DIR . 'class-cron.php';
        if (file_exists($cron_path)) {
            require_once $cron_path;
        }
        if (class_exists('EzLens_Auth_Cron')) {
            $cron = EzLens_Auth_Cron::get_instance();
            $cron->unschedule_cleanup();
        }
        if (class_exists('EzLens_Auth_Campaign_Queue')) {
            EzLens_Auth_Campaign_Queue::get_instance()->unschedule();
        }
        flush_rewrite_rules();
    }

    public function remove_unwanted_scripts() {
        $handles = array(
            'share-modal',
            'share_modal',
            'woodmart-share',
            'wd-share-modal',
            'woodmart-share-modal',
            'xts-share-modal',
        );
        foreach ($handles as $handle) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
        if (is_admin()) {
            $current_screen = function_exists('get_current_screen') ? get_current_screen() : null;
            if ($current_screen && strpos($current_screen->id, 'ezlens-auth') !== false) {
                wp_dequeue_script('bws-captcha-script');
                wp_deregister_script('bws-captcha-script');
                wp_dequeue_style('bws-captcha-style');
                wp_deregister_style('bws-captcha-style');
            }
        }
    }

    public function block_share_modal_src($src, $handle) {
        $needle = strtolower((string)$src);
        if (strpos($needle, 'share-modal.js') !== false || strpos(strtolower((string)$handle), 'share-modal') !== false || strpos(strtolower((string)$handle), 'share_modal') !== false) {
            return '';
        }
        return $src;
    }

    public function block_share_modal_script($tag, $handle, $src) {
        $is_share_modal = (
            strpos($handle, 'share-modal') !== false ||
            strpos($handle, 'share_modal') !== false ||
            strpos($handle, 'share-modal.js') !== false ||
            strpos($src, 'share-modal.js') !== false
        );
        if ($is_share_modal) {
            return '';
        }
        return $tag;
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Assets (با URL درست)
    |--------------------------------------------------------------------------
    */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ezlens-auth') === false && strpos($hook, 'ezlens-auth-backup') === false) {
            return;
        }
        $this->remove_unwanted_scripts();
        wp_enqueue_script('jquery');

        // Admin CSS
        $css_path = EZLAUTH_PLUGIN_DIR . 'admin/assets/css/admin.css';
        $css_version = file_exists($css_path) ? filemtime($css_path) : EZLAUTH_VERSION;
        wp_enqueue_style('ezlens-auth-admin', EZLAUTH_PLUGIN_URL . 'admin/assets/css/admin.css', array(), $css_version);

        // Campaign assets
        if (strpos($hook, 'ezlens-auth-campaign') !== false) {
            $campaign_css = EZLAUTH_PLUGIN_DIR . 'admin/assets/css/admin-campaign.css';
            if (file_exists($campaign_css)) {
                wp_enqueue_style('ezlens-auth-campaign', EZLAUTH_PLUGIN_URL . 'admin/assets/css/admin-campaign.css', array(), filemtime($campaign_css));
            }
            $campaign_js = EZLAUTH_PLUGIN_DIR . 'admin/assets/js/admin-campaign.js';
            if (file_exists($campaign_js)) {
                wp_enqueue_script('ezlens-auth-campaign', EZLAUTH_PLUGIN_URL . 'admin/assets/js/admin-campaign.js', array('jquery'), filemtime($campaign_js), true);
            }
        }

        // Support assets
        if (strpos($hook, 'ezlens-auth-support') !== false) {
            $support_css = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/support.css';
            if (file_exists($support_css)) {
                wp_enqueue_style('ezlens-support-admin', EZLAUTH_PLUGIN_URL . 'frontend/assets/css/support.css', array(), filemtime($support_css));
            }
        }

        // Main admin JS
        $js_path = EZLAUTH_PLUGIN_DIR . 'admin/assets/js/admin.js';
        if (file_exists($js_path)) {
            $js_version = filemtime($js_path);
            wp_enqueue_script('ezlens-auth-admin', EZLAUTH_PLUGIN_URL . 'admin/assets/js/admin.js', array('jquery'), $js_version, true);
            wp_localize_script('ezlens-auth-admin', 'ezlens_auth_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ezlens_auth_nonce'),
                'version' => EZLAUTH_VERSION,
            ));
        }

        add_action('admin_print_scripts', array($this, 'remove_unwanted_scripts'), 99999);
    }

    /*
    |--------------------------------------------------------------------------
    | Frontend Assets (با URL درست)
    |--------------------------------------------------------------------------
    */
    public function enqueue_frontend_assets() {
        global $post;
        $has_auth = false;
        $has_panel = false;
        $has_admin = false;

        if ($post) {
            $shortcodes = array('minimal_auth', 'ezlens_lost_password', 'modern_user_panel', 'admin_login_page', 'ezlens_support_chat');
            foreach ($shortcodes as $sc) {
                if (has_shortcode($post->post_content, $sc)) {
                    if (in_array($sc, array('minimal_auth', 'ezlens_lost_password'), true)) {
                        $has_auth = true;
                    }
                    if ($sc === 'modern_user_panel' || $sc === 'ezlens_support_chat') {
                        $has_panel = true;
                    }
                    if ($sc === 'admin_login_page') {
                        $has_admin = true;
                    }
                }
            }
        }

        $admin_slug = '';
        if (class_exists('EzLens_Auth_Settings')) {
            $admin_slug = EzLens_Auth_Settings::get('admin_login_slug');
        }
        if (!empty($admin_slug) && isset($_SERVER['REQUEST_URI']) && strpos(wp_unslash($_SERVER['REQUEST_URI']), '/' . $admin_slug) !== false) {
            $has_admin = true;
        }

        if (function_exists('is_account_page') && is_account_page()) {
            $has_panel = true;
            if (!is_user_logged_in()) {
                $has_auth = true;
            }
        }

        if (!$has_auth && !$has_panel && !$has_admin) {
            return;
        }

        // Vazirmatn
        wp_enqueue_style('ezlens-vazirmatn', EZLAUTH_PLUGIN_URL . 'assets/fonts/vazirmatn/Vazirmatn.css', array(), EZLAUTH_VERSION);
        wp_enqueue_script('jquery');

        // Core CSS
        $core_css_path = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/frontend-core.css';
        $core_version = file_exists($core_css_path) ? filemtime($core_css_path) : EZLAUTH_VERSION;
        wp_enqueue_style('ezlens-frontend-core', EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-core.css', array(), $core_version);

        if ($has_auth) {
            $auth_css_path = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/frontend-auth.css';
            if (file_exists($auth_css_path)) {
                wp_enqueue_style('ezlens-frontend-auth', EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-auth.css', array('ezlens-frontend-core'), filemtime($auth_css_path));
            }
        }

        if ($has_panel) {
            $panel_css_path = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/frontend-panel.css';
            if (file_exists($panel_css_path)) {
                wp_enqueue_style('ezlens-frontend-panel', EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-panel.css', array('ezlens-frontend-core'), filemtime($panel_css_path));
            }
        }

        if ($has_admin) {
            $admin_css_path = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/frontend-admin.css';
            if (file_exists($admin_css_path)) {
                wp_enqueue_style('ezlens-frontend-admin', EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-admin.css', array('ezlens-frontend-core'), filemtime($admin_css_path));
            }
        }

        $responsive_css = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/frontend-responsive.css';
        if (file_exists($responsive_css)) {
            wp_enqueue_style('ezlens-frontend-responsive', EZLAUTH_PLUGIN_URL . 'frontend/assets/css/frontend-responsive.css', array('ezlens-frontend-core'), filemtime($responsive_css));
        }

        if ($has_panel) {
            $support_css = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/support.css';
            if (file_exists($support_css)) {
                wp_enqueue_style('ezlens-support', EZLAUTH_PLUGIN_URL . 'frontend/assets/css/support.css', array(), filemtime($support_css));
            }
        }

        $js_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('minimal_auth_secure_nonce_v5'),
            'otp_expiry' => class_exists('EzLens_Auth_Settings') ? ((int) EzLens_Auth_Settings::get('otp_expiry_minutes') ?: 2) : 2,
        );

        if ($has_auth) {
            $auth_js = EZLAUTH_PLUGIN_DIR . 'frontend/assets/js/auth.js';
            if (file_exists($auth_js)) {
                wp_enqueue_script('ezlens-auth-js', EZLAUTH_PLUGIN_URL . 'frontend/assets/js/auth.js', array('jquery'), filemtime($auth_js), true);
                wp_localize_script('ezlens-auth-js', 'ezlens_frontend', $js_data);
            }
        }

        if ($has_panel) {
            $panel_js = EZLAUTH_PLUGIN_DIR . 'frontend/assets/js/panel.js';
            if (file_exists($panel_js)) {
                wp_enqueue_script('ezlens-panel-js', EZLAUTH_PLUGIN_URL . 'frontend/assets/js/panel.js', array('jquery'), filemtime($panel_js), true);
                wp_localize_script('ezlens-panel-js', 'ezlens_frontend', $js_data);
            }
        }

        if ($has_admin) {
            $admin_js = EZLAUTH_PLUGIN_DIR . 'frontend/assets/js/admin-login.js';
            if (file_exists($admin_js)) {
                wp_enqueue_script('ezlens-admin-login-js', EZLAUTH_PLUGIN_URL . 'frontend/assets/js/admin-login.js', array('jquery'), filemtime($admin_js), true);
                wp_localize_script('ezlens-admin-login-js', 'ezlens_frontend', $js_data);
            }
        }
    }

    public function render_frontend_protection() {
        if (is_admin() || EzLens_Auth_Settings::get('frontend_protection_enabled') !== '1') return;
        $copy = EzLens_Auth_Settings::get('frontend_disable_copy') === '1';
        $context = EzLens_Auth_Settings::get('frontend_disable_context') === '1';
        $selection = EzLens_Auth_Settings::get('frontend_disable_selection') === '1';
        if (!$copy && !$context && !$selection) return;
        ?>
        <style id="ezlens-frontend-protection"><?php if($selection): ?>body.ezlens-protected *{user-select:none!important;-webkit-user-select:none!important;}<?php endif; ?></style>
        <script id="ezlens-frontend-protection-js">(function(){var c=<?php echo $copy?'true':'false'; ?>,m=<?php echo $context?'true':'false'; ?>;document.documentElement.classList.add('ezlens-protected');if(c){document.addEventListener('copy',function(e){e.preventDefault();});document.addEventListener('cut',function(e){e.preventDefault();});}if(m){document.addEventListener('contextmenu',function(e){e.preventDefault();});}})();</script>
        <?php
    }
}

/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/
EzLens_Auth::get_instance();