<?php
/**
 * کلاس سفارشی‌سازی‌های مربوط به کاربران
 * EzLens Auth - User Customizations
 */
class EzLens_Auth_User_Customizations {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // استایل صفحه ویرایش کاربر
        add_action('admin_head', [$this, 'user_edit_style']);
        
        // تغییر نام ستون نام کاربری
        add_filter('manage_users_columns', [$this, 'modify_user_columns']);
        add_filter('manage_users_custom_column', [$this, 'modify_user_column_value'], 10, 3);
        
        // تنظیم شماره موبایل به عنوان نام کاربری در صفحه ویرایش
        add_action('show_user_profile', [$this, 'set_phone_as_username']);
        add_action('edit_user_profile', [$this, 'set_phone_as_username']);
        
        // افزودن ستون شماره موبایل در لیست کاربران
        add_filter('manage_users_columns', [$this, 'add_phone_column'], 20);
        add_filter('manage_users_custom_column', [$this, 'render_phone_column'], 20, 3);
        
        // افزودن فیلد شماره موبایل به پروفایل کاربر
        add_action('show_user_profile', [$this, 'add_phone_field']);
        add_action('edit_user_profile', [$this, 'add_phone_field']);
        add_action('personal_options_update', [$this, 'save_phone_field']);
        add_action('edit_user_profile_update', [$this, 'save_phone_field']);
    }

    /**
     * استایل مینیمال و مدرن برای صفحه ویرایش کاربر
     */
    public function user_edit_style() {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->base, ['user-edit', 'profile', 'user-new'])) {
            return;
        }

        echo '
        <style>
            #profile-page {
                max-width: 900px;
                margin: 0 auto;
                padding: 10px 0;
            }
            #profile-page .wp-heading-inline {
                font-size: 24px;
                font-weight: 700;
                color: #0f172a;
            }
            #profile-page .page-title-action {
                border-radius: 8px;
                border-color: #e2e8f0;
                background: #f8fafc;
                color: #1e293b;
                font-weight: 500;
                transition: all 0.2s;
            }
            #profile-page .page-title-action:hover {
                background: #edf2f7;
                border-color: #cbd5e1;
            }
            #profile-page .form-table {
                background: #fff;
                border-radius: 12px;
                padding: 20px 24px 12px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.04);
                border: 1px solid #e2e8f0;
                margin-bottom: 20px;
                width: 100%;
            }
            #profile-page h2 {
                font-size: 18px;
                font-weight: 700;
                color: #0f172a;
                margin: 28px 0 12px;
                padding-bottom: 8px;
                border-bottom: 2px solid #e2e8f0;
            }
            #profile-page .form-table th {
                padding: 14px 12px 14px 0;
                font-weight: 600;
                color: #1a202c;
                font-size: 14px;
                width: 180px;
            }
            #profile-page .form-table td {
                padding: 14px 12px;
            }
            #profile-page .form-table input[type="text"],
            #profile-page .form-table input[type="email"],
            #profile-page .form-table input[type="password"],
            #profile-page .form-table input[type="number"],
            #profile-page .form-table textarea,
            #profile-page .form-table select {
                border-radius: 8px;
                border: 1px solid #e2e8f0;
                padding: 10px 14px;
                font-size: 14px;
                transition: all 0.2s;
                width: 100%;
                max-width: 400px;
                background: #f8fafc;
                color: #0f172a;
                font-family: inherit;
            }
            #profile-page .form-table input:focus,
            #profile-page .form-table textarea:focus,
            #profile-page .form-table select:focus {
                border-color: #2b6cb0;
                box-shadow: 0 0 0 3px rgba(43,108,176,0.08);
                background: #fff;
                outline: none;
            }
            #profile-page .button-primary {
                background: #2b6cb0;
                border-color: #2b6cb0;
                border-radius: 8px;
                padding: 8px 28px;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.2s;
            }
            #profile-page .button-primary:hover {
                background: #2c5282;
                border-color: #2c5282;
                box-shadow: 0 4px 12px rgba(43,108,176,0.2);
            }
            #profile-page .button-secondary {
                border-radius: 8px;
                border-color: #e2e8f0;
                background: #f8fafc;
            }
            #profile-page .button-secondary:hover {
                background: #edf2f7;
                border-color: #cbd5e1;
            }
            #profile-page .description {
                color: #718096;
                font-size: 13px;
                margin-top: 4px;
                display: block;
            }
            @media (max-width: 782px) {
                #profile-page .form-table th {
                    width: auto;
                    display: block;
                    padding-bottom: 4px;
                }
                #profile-page .form-table td {
                    display: block;
                    padding-top: 4px;
                }
                #profile-page .form-table input[type="text"],
                #profile-page .form-table input[type="email"],
                #profile-page .form-table input[type="password"] {
                    max-width: 100%;
                }
                #profile-page .wp-heading-inline {
                    font-size: 20px;
                }
            }
            @media (prefers-color-scheme: dark) {
                #profile-page .form-table {
                    background: #1e293b;
                    border-color: #334155;
                }
                #profile-page .wp-heading-inline {
                    color: #f1f5f9;
                }
                #profile-page h2 {
                    color: #f1f5f9;
                    border-color: #334155;
                }
                #profile-page .form-table th {
                    color: #e2e8f0;
                }
                #profile-page .form-table input[type="text"],
                #profile-page .form-table input[type="email"],
                #profile-page .form-table input[type="password"],
                #profile-page .form-table textarea,
                #profile-page .form-table select {
                    background: #0f172a;
                    border-color: #334155;
                    color: #f1f5f9;
                }
                #profile-page .form-table input:focus {
                    border-color: #60a5fa;
                    box-shadow: 0 0 0 3px rgba(96,165,250,0.15);
                }
                #profile-page .description {
                    color: #94a3b8;
                }
                #profile-page .button-primary {
                    background: #3b82f6;
                    border-color: #3b82f6;
                }
                #profile-page .button-primary:hover {
                    background: #2563eb;
                    border-color: #2563eb;
                }
                #profile-page .button-secondary {
                    background: #1e293b;
                    border-color: #334155;
                    color: #e2e8f0;
                }
                #profile-page .button-secondary:hover {
                    background: #334155;
                }
            }
        </style>
        ';
    }

    /**
     * تغییر نام ستون "نام کاربری" به "نام کاربری / موبایل"
     */
    public function modify_user_columns($columns) {
        if (isset($columns['username'])) {
            $columns['username'] = 'نام کاربری / موبایل';
        }
        return $columns;
    }

    /**
     * نمایش شماره موبایل در ستون نام کاربری
     */
    public function modify_user_column_value($value, $column_name, $user_id) {
        if ($column_name === 'username') {
            $phone = get_user_meta($user_id, 'billing_phone', true);
            if (empty($phone)) {
                $phone = get_user_meta($user_id, 'user_phone', true);
            }
            if ($phone) {
                return esc_html($phone);
            }
        }
        return $value;
    }

    /**
     * تنظیم شماره موبایل به عنوان نام کاربری در صفحه ویرایش (با JS)
     */
    public function set_phone_as_username($user) {
        $phone = get_user_meta($user->ID, 'billing_phone', true);
        if (empty($phone)) {
            $phone = get_user_meta($user->ID, 'user_phone', true);
        }
        if ($phone) {
            ?>
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                var usernameField = document.getElementById("user_login");
                if (usernameField) {
                    usernameField.value = "<?php echo esc_js($phone); ?>";
                    usernameField.readOnly = true;
                    var desc = usernameField.closest('td').querySelector('.description');
                    if (desc) {
                        desc.textContent = 'شماره موبایل به‌عنوان نام کاربری تنظیم شده است و قابل تغییر نیست.';
                    }
                }
            });
            </script>
            <?php
        }
    }

    /**
     * افزودن ستون شماره موبایل در لیست کاربران
     */
    public function add_phone_column($columns) {
        $columns['user_phone'] = '📱 موبایل';
        return $columns;
    }

    /**
     * رندر ستون شماره موبایل در لیست کاربران
     */
    public function render_phone_column($value, $column_name, $user_id) {
        if ($column_name === 'user_phone') {
            $phone = get_user_meta($user_id, 'billing_phone', true);
            if (empty($phone)) {
                $phone = get_user_meta($user_id, 'user_phone', true);
            }
            return $phone ? esc_html($phone) : '—';
        }
        return $value;
    }

    /**
     * افزودن فیلد شماره موبایل به پروفایل کاربر (برای ادمین)
     */
    public function add_phone_field($user) {
        $phone = get_user_meta($user->ID, 'billing_phone', true);
        ?>
        <h2>📱 اطلاعات تماس EzLens</h2>
        <table class="form-table">
            <tr>
                <th><label for="ezlens_user_phone">شماره موبایل</label></th>
                <td>
                    <input type="tel" name="ezlens_user_phone" id="ezlens_user_phone" 
                           value="<?php echo esc_attr($phone); ?>" 
                           class="regular-text" placeholder="09123456789">
                    <p class="description">این شماره به‌عنوان نام کاربری و برای ارسال پیامک استفاده می‌شود.</p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * ذخیره شماره موبایل از پروفایل کاربر
     */
    public function save_phone_field($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        if (isset($_POST['ezlens_user_phone'])) {
            $phone = sanitize_text_field($_POST['ezlens_user_phone']);
            if ($phone && preg_match('/^09\d{9}$/', $phone)) {
                update_user_meta($user_id, 'billing_phone', $phone);
                update_user_meta($user_id, 'user_phone', $phone);
            }
        }
    }
}

// مقداردهی اولیه
EzLens_Auth_User_Customizations::get_instance();