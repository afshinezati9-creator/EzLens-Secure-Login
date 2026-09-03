<?php
/**
 * بارگذار شورت‌کدها – نسخه بازسازیشده با مسیرهای جدید
 */
class EzLens_Auth_Shortcodes_Loader {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_shortcodes();
    }

    private function load_shortcodes() {
        // فایل‌های شورت‌کد در مسیر جدید (هنوز در includes/shortcodes/ هستند)
        require_once EZLAUTH_INCLUDES_DIR . 'shortcodes/class-shortcodes-auth.php';
        require_once EZLAUTH_INCLUDES_DIR . 'shortcodes/class-shortcodes-forgot.php';
        require_once EZLAUTH_INCLUDES_DIR . 'shortcodes/class-shortcodes-panel.php';

        add_shortcode('minimal_auth', [EzLens_Auth_Shortcodes_Auth::class, 'render_minimal_auth']);
        add_shortcode('ezlens_lost_password', [EzLens_Auth_Shortcodes_Forgot::class, 'render_lost_password']);
        add_shortcode('modern_user_panel', [EzLens_Auth_Shortcodes_Panel::class, 'render_user_panel']);
        add_shortcode('admin_login_page', [EzLens_Auth_Shortcodes_Auth::class, 'render_admin_login']);
    }
}

// مقداردهی اولیه
EzLens_Auth_Shortcodes_Loader::get_instance();