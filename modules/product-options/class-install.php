<?php
if (!defined('ABSPATH')) exit;

class EzLens_Product_Options_Install {
    private static $instance = null;
    private $option_name = 'ezlens_product_options_tables_created';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'create_table']);
    }

    public function create_table() {
        // اگر قبلاً جدول ساخته شده، دیگر کاری نکن
        if (get_option($this->option_name, false)) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ezlens_option_templates';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text DEFAULT NULL,
            fields longtext NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_by bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_by (created_by),
            KEY created_at (created_at)
        ) {$charset}";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // ثبت آپشن تا دیگر تکرار نشود
        update_option($this->option_name, true);
    }
}

// مقداردهی اولیه
EzLens_Product_Options_Install::get_instance();

// Pricing Engine در هر بار لود ماژول Product Options در دسترس باشد.
$ezlens_pricing_engine = EZLAUTH_MODULES_DIR . 'product-options/class-pricing-engine.php';
if (is_readable($ezlens_pricing_engine)) {
    require_once $ezlens_pricing_engine;
}
