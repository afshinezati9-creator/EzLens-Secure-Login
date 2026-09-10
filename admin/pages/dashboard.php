<?php
/**
 * کلاس مدیریت منوی ادمین و صفحات – نسخه با آیکون‌های SVG اختصاصی و مدرن
 * @version 3.0.9 – اضافه شدن منوی فرآیند خرید با زیرمنوهای اختصاصی درون EzLens Auth
 */
class EzLens_Auth_Admin {

    private static $instance = null;
    
    // مسیر پوشه آیکون‌های مدرن
    const ICON_PATH = 'assets/icons/modern/';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_head', [$this, 'add_custom_menu_icons_css']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        add_action('admin_post_ezlens_auth_save_settings', [$this, 'save_settings']);
        add_action('admin_post_ezlens_auth_reset_settings', [$this, 'reset_settings']);
        add_action('admin_post_ezlens_auth_save_code', [$this, 'save_code']);
        add_action('admin_post_ezlens_auth_reset_code', [$this, 'reset_code']);
        add_action('admin_post_ezlens_save_product_options_settings', [$this, 'save_product_options_settings']);
    }

    /**
     * افزودن منوهای ادمین با آیکون‌های SVG سفارشی
     */
    public function add_admin_menu() {
        // ===== منوی اصلی =====
        add_menu_page(
            'EzLens Secure Login',
            'EzLens Auth',
            'manage_options',
            'ezlens-auth',
            [$this, 'render_dashboard'],
            $this->get_svg_icon('home'),
            30
        );

        // ===== زیرمنوهای سطح اول =====
        add_submenu_page(
            'ezlens-auth',
            'داشبورد',
            '<span class="ezlens-menu-icon" data-icon="home"></span> داشبورد',
            'manage_options',
            'ezlens-auth',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'ezlens-auth',
            'تنظیمات عمومی',
            '<span class="ezlens-menu-icon" data-icon="settings"></span> تنظیمات',
            'manage_options',
            'ezlens-auth-settings',
            [$this, 'render_settings']
        );

        add_submenu_page(
            'ezlens-auth',
            'ویرایش صفحات',
            '<span class="ezlens-menu-icon" data-icon="edit"></span> ویرایش صفحات',
            'manage_options',
            'ezlens-auth-editor',
            [$this, 'render_editor']
        );

        add_submenu_page(
            'ezlens-auth',
            'گزارش‌ها',
            '<span class="ezlens-menu-icon" data-icon="chart"></span> گزارش‌ها',
            'manage_options',
            'ezlens-auth-logs',
            [$this, 'render_logs']
        );

        add_submenu_page(
            'ezlens-auth',
            'بکاپ و API',
            '<span class="ezlens-menu-icon" data-icon="database"></span> بکاپ و API',
            'manage_options',
            'ezlens-auth-backup',
            [$this, 'render_backup']
        );

        add_submenu_page(
            'ezlens-auth',
            'کمپینگ تبلیغاتی',
            '<span class="ezlens-menu-icon" data-icon="mail"></span> کمپینگ',
            'manage_options',
            'ezlens-auth-campaign',
            [$this, 'render_campaign']
        );

        add_submenu_page(
            'ezlens-auth',
            'پشتیبانی (تیکت‌ها)',
            '<span class="ezlens-menu-icon" data-icon="support"></span> پشتیبانی',
            'manage_options',
            'ezlens-auth-support',
            [$this, 'render_support']
        );

        // ============================================================
        // 🧩 منوی ویژگی‌های محصول (سطح اول) با زیرمنوهای اختصاصی
        // ============================================================
        add_submenu_page(
            'ezlens-auth',
            'ویژگی‌های محصول',
            '<span class="ezlens-menu-icon" data-icon="layers"></span> ویژگی‌های محصول',
            'manage_options',
            'ezlens-product-options',
            [$this, 'render_product_options']
        );

        // ===== زیرمنوهای ویژگی‌های محصول =====
        add_submenu_page(
            'ezlens-product-options',
            'لیست پالت‌ها',
            '<span class="ezlens-menu-icon ezlens-submenu-icon" data-icon="grid"></span> لیست پالت‌ها',
            'manage_options',
            'ezlens-product-options',
            [$this, 'render_product_options']
        );

        add_submenu_page(
            'ezlens-product-options',
            'پالت جدید',
            '<span class="ezlens-menu-icon ezlens-submenu-icon" data-icon="plus"></span> پالت جدید',
            'manage_options',
            'ezlens-product-options-add',
            [$this, 'render_product_options_add']
        );

        add_submenu_page(
            'ezlens-product-options',
            'تنظیمات ویژگی‌ها',
            '<span class="ezlens-menu-icon ezlens-submenu-icon" data-icon="settings"></span> تنظیمات ویژگی‌ها',
            'manage_options',
            'ezlens-product-options-settings',
            [$this, 'render_product_options_settings']
        );

        // ============================================================
        // 🛒 منوی فرآیند خرید (سطح اول) با زیرمنوهای اختصاصی
        // ============================================================
        add_submenu_page(
            'ezlens-auth',
            'فرآیند خرید',
            '<span class="ezlens-menu-icon" data-icon="shopping-cart"></span> فرآیند خرید',
            'manage_options',
            'ezlens-purchase-process',
            [$this, 'render_purchase_list']
        );

        // ===== زیرمنوهای فرآیند خرید =====
        add_submenu_page(
            'ezlens-purchase-process',
            'لیست فایل‌ها',
            '<span class="ezlens-menu-icon ezlens-submenu-icon" data-icon="grid"></span> لیست فایل‌ها',
            'manage_options',
            'ezlens-purchase-process-list',
            [$this, 'render_purchase_list']
        );

        add_submenu_page(
            'ezlens-purchase-process',
            'فایل جدید',
            '<span class="ezlens-menu-icon ezlens-submenu-icon" data-icon="plus"></span> فایل جدید',
            'manage_options',
            'ezlens-purchase-process-add',
            [$this, 'render_purchase_add']
        );
    }

    /**
     * دریافت کد SVG آیکون به‌صورت base64 برای منوی اصلی
     */
    private function get_svg_icon($name) {
        $icon_path = EZLAUTH_PLUGIN_DIR . self::ICON_PATH . $name . '.svg';
        if (file_exists($icon_path)) {
            $svg_content = file_get_contents($icon_path);
            $base64 = base64_encode($svg_content);
            return 'data:image/svg+xml;base64,' . $base64;
        }
        return 'dashicons-lock';
    }

    /**
     * افزودن استایل‌های اختصاصی برای منو
     */
    public function enqueue_admin_styles() {
        wp_add_inline_style('admin-menu', $this->get_custom_menu_styles());
    }

    /**
     * استایل‌های CSS مینیمال و مدرن برای منو
     */
    private function get_custom_menu_styles() {
        $icon_url = EZLAUTH_PLUGIN_URL . self::ICON_PATH;
        return <<<CSS
        /* ============================================================
           استایل مینیمال و مدرن برای منوی EzLens Auth
           ============================================================ */
        
        /* ---- پایه آیکون‌ها با تکنیک Mask برای گرادیانت ---- */
        .ezlens-menu-icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-left: 8px;
            vertical-align: middle;
            background: linear-gradient(135deg, #031f8a 0%, #5c8092 100%);
            mask-size: contain;
            mask-repeat: no-repeat;
            mask-position: center;
            -webkit-mask-size: contain;
            -webkit-mask-repeat: no-repeat;
            -webkit-mask-position: center;
            transition: background 0.3s ease, transform 0.2s ease;
            flex-shrink: 0;
        }
        
        /* زیرمنوها: آیکون کوچک‌تر و رنگ ملایم‌تر */
        .ezlens-submenu-icon {
            width: 16px;
            height: 16px;
            margin-left: 6px;
            background: linear-gradient(135deg, #5c8092 0%, #8aa9b8 100%);
        }
        
        /* ---- آیکون‌های اختصاصی با دیتا-آتریبیوت ---- */
        .ezlens-menu-icon[data-icon="home"] {
            mask-image: url('{$icon_url}home.svg');
            -webkit-mask-image: url('{$icon_url}home.svg');
        }
        .ezlens-menu-icon[data-icon="settings"] {
            mask-image: url('{$icon_url}settings.svg');
            -webkit-mask-image: url('{$icon_url}settings.svg');
        }
        .ezlens-menu-icon[data-icon="edit"] {
            mask-image: url('{$icon_url}edit.svg');
            -webkit-mask-image: url('{$icon_url}edit.svg');
        }
        .ezlens-menu-icon[data-icon="chart"] {
            mask-image: url('{$icon_url}chart.svg');
            -webkit-mask-image: url('{$icon_url}chart.svg');
        }
        .ezlens-menu-icon[data-icon="database"] {
            mask-image: url('{$icon_url}database.svg');
            -webkit-mask-image: url('{$icon_url}database.svg');
        }
        .ezlens-menu-icon[data-icon="mail"] {
            mask-image: url('{$icon_url}mail.svg');
            -webkit-mask-image: url('{$icon_url}mail.svg');
        }
        .ezlens-menu-icon[data-icon="support"] {
            mask-image: url('{$icon_url}support.svg');
            -webkit-mask-image: url('{$icon_url}support.svg');
        }
        .ezlens-menu-icon[data-icon="layers"] {
            mask-image: url('{$icon_url}layers.svg');
            -webkit-mask-image: url('{$icon_url}layers.svg');
        }
        .ezlens-menu-icon[data-icon="grid"] {
            mask-image: url('{$icon_url}grid.svg');
            -webkit-mask-image: url('{$icon_url}grid.svg');
        }
        .ezlens-menu-icon[data-icon="plus"] {
            mask-image: url('{$icon_url}plus-square.svg');
            -webkit-mask-image: url('{$icon_url}plus-square.svg');
        }
        /* آیکون جدید برای فرآیند خرید */
        .ezlens-menu-icon[data-icon="shopping-cart"] {
            mask-image: url('{$icon_url}shopping-cart.svg');
            -webkit-mask-image: url('{$icon_url}shopping-cart.svg');
        }
        
        /* ---- استایل آیتم‌های منو ---- */
        #adminmenu .wp-has-submenu .wp-submenu li a,
        #adminmenu .wp-not-current-submenu .wp-submenu li a {
            display: flex;
            align-items: center;
            padding: 8px 16px 8px 12px;
            border-radius: 10px;
            margin: 2px 8px;
            transition: all 0.25s ease;
            font-weight: 400;
            font-size: 13px;
            color: #94a3b8;
            background: transparent;
            border: none;
            position: relative;
        }
        
        /* ---- هاور: پالت دور کل آیتم ---- */
        #adminmenu .wp-has-submenu .wp-submenu li a:hover {
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12), inset 0 0 0 1px rgba(255, 255, 255, 0.06);
            color: #e2e8f0;
            transform: translateX(-2px);
        }
        
        #adminmenu .wp-has-submenu .wp-submenu li a:hover .ezlens-menu-icon {
            background: linear-gradient(135deg, #5c8092 0%, #8aa9b8 100%);
            transform: scale(1.05);
        }
        
        #adminmenu .wp-has-submenu .wp-submenu li a:hover .ezlens-submenu-icon {
            background: linear-gradient(135deg, #8aa9b8 0%, #b0c8d4 100%);
        }
        
        /* ---- آیتم فعال (منتخب) ---- */
        #adminmenu .wp-has-submenu .wp-submenu li.current a,
        #adminmenu .wp-has-submenu .wp-submenu li a.current {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15), inset 0 0 0 1px rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            font-weight: 600;
        }
        
        #adminmenu .wp-has-submenu .wp-submenu li.current a .ezlens-menu-icon,
        #adminmenu .wp-has-submenu .wp-submenu li a.current .ezlens-menu-icon {
            background: linear-gradient(135deg, #031f8a 0%, #1a4a7a 100%);
        }
        
        #adminmenu .wp-has-submenu .wp-submenu li.current a .ezlens-submenu-icon,
        #adminmenu .wp-has-submenu .wp-submenu li a.current .ezlens-submenu-icon {
            background: linear-gradient(135deg, #031f8a 0%, #1a4a7a 100%);
        }
        
        /* ---- زیرمنوها: تورفتگی و خط عمودی برای تمایز ---- */
        .wp-submenu .wp-submenu-wrap {
            position: relative;
        }
        
        /* خط عمودی برای زیرمنوهای سطح دوم */
        .wp-submenu li a[class*="ezlens-submenu-icon"] {
            padding-left: 28px !important;
            position: relative;
        }
        
        .wp-submenu li a[class*="ezlens-submenu-icon"]::before {
            content: '';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 2px;
            height: 60%;
            background: linear-gradient(180deg, rgba(92, 128, 146, 0.3) 0%, rgba(92, 128, 146, 0.1) 100%);
            border-radius: 2px;
            transition: height 0.3s ease, background 0.3s ease;
        }
        
        .wp-submenu li a[class*="ezlens-submenu-icon"]:hover::before {
            height: 80%;
            background: linear-gradient(180deg, #5c8092 0%, rgba(92, 128, 146, 0.2) 100%);
        }
        
        .wp-submenu li.current a[class*="ezlens-submenu-icon"]::before {
            height: 80%;
            background: linear-gradient(180deg, #031f8a 0%, rgba(3, 31, 138, 0.3) 100%);
        }
        
        /* ---- آیکون منوی اصلی (سطح اول) ---- */
        #adminmenu .toplevel_page_ezlens-auth .wp-menu-image {
            background: transparent !important;
        }
        
        #adminmenu .toplevel_page_ezlens-auth .wp-menu-image img {
            filter: brightness(0) invert(1) !important;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
        
        #adminmenu .toplevel_page_ezlens-auth .wp-menu-image img:hover {
            opacity: 1;
        }
        
        /* ---- تنظیمات responsive برای موبایل ---- */
        @media screen and (max-width: 782px) {
            .ezlens-menu-icon {
                width: 18px;
                height: 18px;
                margin-left: 4px;
            }
            .ezlens-submenu-icon {
                width: 14px;
                height: 14px;
                margin-left: 4px;
            }
            #adminmenu .wp-has-submenu .wp-submenu li a {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
        
        /* ---- انیمیشن ملایم برای هاور ---- */
        #adminmenu .wp-has-submenu .wp-submenu li a {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        #adminmenu .wp-has-submenu .wp-submenu li a .ezlens-menu-icon {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s ease;
        }
        CSS;
    }

    /**
     * متد قدیمی برای compat – دیگر استفاده نمی‌شود اما برای سازگاری نگه داشته می‌شود
     */
    public function add_custom_menu_icons_css() {
        // این متد توسط enqueue_admin_styles جایگزین شده است
    }

    // ============================================================
    //  متدهای رندر صفحات
    // ============================================================
    
    public function render_product_options() {
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
        if ($action === 'edit') {
            $editor_file = EZLAUTH_MODULES_DIR . 'product-options/admin/pages/template-editor.php';
            if (file_exists($editor_file)) {
                include $editor_file;
            } else {
                echo '<div class="wrap"><h1>✏️ ویرایش پالت</h1><p style="color:#dc2626;">فایل ویرایشگر یافت نشد.</p></div>';
            }
            return;
        }
        $list_file = EZLAUTH_MODULES_DIR . 'product-options/admin/pages/template-list.php';
        if (file_exists($list_file)) {
            include $list_file;
        } else {
            echo '<div class="wrap"><h1>🧩 ویژگی‌های محصول</h1><p style="color:#dc2626;">فایل لیست پالت‌ها یافت نشد.</p></div>';
        }
    }

    public function render_product_options_add() {
        $editor_file = EZLAUTH_MODULES_DIR . 'product-options/admin/pages/template-editor.php';
        if (file_exists($editor_file)) {
            $_GET['action'] = 'add';
            include $editor_file;
        } else {
            echo '<div class="wrap"><h1>➕ افزودن پالت جدید</h1><p style="color:#dc2626;">فایل ویرایشگر یافت نشد.</p></div>';
        }
    }

    public function render_product_options_settings() {
        $settings_file = EZLAUTH_MODULES_DIR . 'product-options/admin/pages/template-settings.php';
        if (file_exists($settings_file)) {
            include $settings_file;
        } else {
            echo '<div class="wrap"><h1>⚙️ تنظیمات ویژگی‌های محصول</h1><p style="color:#dc2626;">فایل تنظیمات یافت نشد.</p></div>';
        }
    }

    public function save_product_options_settings() {
        check_admin_referer('ezlens_product_options_settings', 'product_options_nonce');
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }
        $keys = [
            'product_options_enabled',
            'product_options_max_upload_size',
            'product_options_allowed_extensions',
            'product_options_show_title',
            'product_options_show_price_in_cart',
            'product_options_default_layout',
            'product_options_required_fields',
        ];
        $checkbox_keys = [
            'product_options_enabled',
            'product_options_show_title',
            'product_options_show_price_in_cart',
            'product_options_required_fields',
        ];
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $value = sanitize_text_field($_POST[$key]);
                EzLens_Auth_Settings::set($key, $value);
            } elseif (in_array($key, $checkbox_keys)) {
                EzLens_Auth_Settings::set($key, '0');
            }
        }
        EzLens_Auth_Settings::clear_cache();
        wp_redirect(admin_url('admin.php?page=ezlens-product-options-settings&saved=1'));
        exit;
    }

    // ============================================================
    // 🛒 متدهای رندر فرآیند خرید
    // ============================================================

    /**
     * رندر صفحه لیست فایل‌های فرآیند خرید
     */
    public function render_purchase_list() {
        $list_file = EZLAUTH_MODULES_DIR . 'purchase-process/admin/pages/list.php';
        if (file_exists($list_file)) {
            include $list_file;
        } else {
            echo '<div class="wrap"><h1>🛒 فرآیند خرید</h1><p style="color:#dc2626;">فایل لیست یافت نشد.</p></div>';
        }
    }

    /**
     * رندر صفحه افزودن فایل جدید فرآیند خرید
     */
    public function render_purchase_add() {
        $edit_file = EZLAUTH_MODULES_DIR . 'purchase-process/admin/pages/edit.php';
        if (file_exists($edit_file)) {
            include $edit_file;
        } else {
            echo '<div class="wrap"><h1>➕ افزودن فایل جدید</h1><p style="color:#dc2626;">فایل ویرایش یافت نشد.</p></div>';
        }
    }

    // ============================================================
    // متدهای آمار
    // ============================================================

    /**
     * دریافت آمار ماژول فرآیند خرید
     */
    private function get_purchase_process_stats() {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_purchase_scripts';
        
        // بررسی وجود جدول
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return [
                'total'   => 0,
                'active'  => 0,
                'inactive' => 0,
                'exists'  => false,
            ];
        }
        
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $active = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 1");
        
        return [
            'total'    => $total,
            'active'   => $active,
            'inactive' => $total - $active,
            'exists'   => true,
        ];
    }

    public function render_dashboard() {
        $stats = $this->get_dashboard_stats();
        $users_with_phone = $this->get_recent_users_with_phone(20);
        $purchase_stats = $this->get_purchase_process_stats();
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