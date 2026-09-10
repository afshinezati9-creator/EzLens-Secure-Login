<?php
/**
 * Product Options module bootstrap & single table installer.
 *
 * Phase 0: one entry point for table creation, src autoload, and class wiring.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_Product_Options_Install
 */
class EzLens_Product_Options_Install {

    /** @var self|null */
    private static $instance = null;

    /** @var string */
    private $option_name = 'ezlens_product_options_db_version';

    /** @var string */
    const DB_VERSION = '2.0.0';

    /**
     * @return self
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_src();
        $this->load_helpers();

        add_action('init', array($this, 'maybe_install'), 5);
        add_action('plugins_loaded', array($this, 'boot'), 20);
    }

    /**
     * Manual install hook (activation).
     */
    public static function install() {
        $self = self::get_instance();
        $self->create_table(true);
        update_option($self->option_name, self::DB_VERSION, false);
    }

    /**
     * Load namespaced src classes (no Composer required).
     */
    private function load_src() {
        $base = EZLAUTH_MODULES_DIR . 'product-options/src/';

        $map = array(
            'EzLens\\ProductOptions\\Repositories\\TemplateRepository' => $base . 'Repositories/TemplateRepository.php',
            'EzLens\\ProductOptions\\Services\\FieldSchemaValidator'    => $base . 'Services/FieldSchemaValidator.php',
            'EzLens\\ProductOptions\\Services\\ConditionEvaluator'      => $base . 'Services/ConditionEvaluator.php',
            'EzLens\\ProductOptions\\Services\\PresetLibrary'           => $base . 'Services/PresetLibrary.php',
            'EzLens\\ProductOptions\\Services\\TemplateService'         => $base . 'Services/TemplateService.php',
            'EzLens\\ProductOptions\\Services\\ProductSlotsService'     => $base . 'Services/ProductSlotsService.php',
        );

        foreach ($map as $class => $path) {
            if (!class_exists($class, false) && is_readable($path)) {
                require_once $path;
            }
        }
    }

    /**
     * @return void
     */
    private function load_helpers() {
        $path = EZLAUTH_MODULES_DIR . 'product-options/includes/class-helpers.php';
        if (is_readable($path)) {
            require_once $path;
        }
    }

    /**
     * @return void
     */
    public function maybe_install() {
        $current = get_option($this->option_name, '');
        if ($current === self::DB_VERSION) {
            // Still ensure table exists (e.g. DB restored without options).
            $repo_class = 'EzLens\\ProductOptions\\Repositories\\TemplateRepository';
            if (class_exists($repo_class)) {
                $repo = new $repo_class();
                if (!$repo->table_exists()) {
                    $this->create_table(true);
                }
            }
            return;
        }
        $this->create_table(true);
        update_option($this->option_name, self::DB_VERSION, false);
    }

    /**
     * Single source of truth for schema.
     *
     * @param bool $force
     */
    public function create_table($force = false) {
        global $wpdb;

        $table   = $wpdb->prefix . 'ezlens_option_templates';
        $charset = $wpdb->get_charset_collate();

        // Case-insensitive existence check.
        $exists = false;
        $found  = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) = LOWER(%s) LIMIT 1',
                $table
            )
        );
        if ($found) {
            $exists = true;
            $table  = $found;
        }

        if ($exists && !$force) {
            return;
        }

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text DEFAULT NULL,
            fields longtext NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_by bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY created_by (created_by),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Clear stale "tables created" flag from older installs.
        delete_option('ezlens_product_options_tables_created');
    }

    /**
     * Load runtime classes and fire singletons.
     */
    public function boot() {
        $dir = EZLAUTH_MODULES_DIR . 'product-options/';

        $files = array(
            'includes/class-po-settings.php',
            'class-template-manager.php',
            'class-ajax-handler.php',
            'class-field-renderer.php',
            'class-pricing-engine.php',
            'class-woocommerce-integration.php',
            'class-order-display.php',
            'frontend/class-cart-display.php',
            'admin/meta-box/class-meta-box.php', // Phase 1 multi-slot UI
            'admin/meta-box.php', // thin loader (no-op if class already loaded)
        );

        foreach ($files as $file) {
            $path = $dir . $file;
            if (is_readable($path)) {
                require_once $path;
            }
        }

        // Admin page templates (list/editor/settings) must ONLY be included
        // from menu callbacks — never on every admin request (causes output leak).
        if (is_admin()) {
            $preset = $dir . 'admin/class-preset-library-page.php';
            if (is_readable($preset)) {
                require_once $preset;
            }
        }

        // Singletons self-register on construct via get_instance() at file bottom
        // or explicit boot here for classes that do not auto-boot.
        if (class_exists('EzLens_Product_Options_Template_Manager')) {
            EzLens_Product_Options_Template_Manager::get_instance();
        }
        if (class_exists('EzLens_Product_Options_Ajax')) {
            EzLens_Product_Options_Ajax::get_instance();
        }
        if (class_exists('EzLens_Product_Options_Field_Renderer')) {
            // Actual class name in file.
        }
        // Field renderer / pricing / woocommerce / order-display call get_instance at file end.
    }
}

EzLens_Product_Options_Install::get_instance();
