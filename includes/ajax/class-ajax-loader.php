<?php
/**
 * بارگذار درخواست‌های AJAX – نسخه بازسازیشده با مسیرهای جدید
 */
class EzLens_Auth_Ajax_Loader {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_ajax_handlers();
    }

    private function load_ajax_handlers() {
        // فایل‌های AJAX در مسیر جدید (هنوز در includes/ajax/ هستند)
        require_once EZLAUTH_INCLUDES_DIR . 'ajax/class-ajax-editor.php';
        require_once EZLAUTH_INCLUDES_DIR . 'ajax/class-ajax-settings.php';
        require_once EZLAUTH_INCLUDES_DIR . 'ajax/class-ajax-auth.php';
        require_once EZLAUTH_INCLUDES_DIR . 'ajax/class-ajax-tests.php';
        require_once EZLAUTH_INCLUDES_DIR . 'ajax/class-ajax-support.php';
        require_once EZLAUTH_INCLUDES_DIR . 'ajax/class-campaign-ajax.php';
    }
}

// مقداردهی اولیه (فقط در صورت نیاز)
if (wp_doing_ajax()) {
    EzLens_Auth_Ajax_Loader::get_instance();
}