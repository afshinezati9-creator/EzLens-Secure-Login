<?php
/**
 * درخواست‌های AJAX تنظیمات و بکاپ (نسخه نهایی با nonce یکپارچه)
 * @version 2.6.0
 */
class EzLens_Auth_Ajax_Settings {

    public static function init() {
        add_action('wp_ajax_ezlens_get_stats', [__CLASS__, 'get_stats']);
        add_action('wp_ajax_ezlens_export_data', [__CLASS__, 'export_data']);
        add_action('wp_ajax_ezlens_import_data', [__CLASS__, 'import_data']);
        add_action('wp_ajax_ezlens_regenerate_api_key', [__CLASS__, 'regenerate_api_key']);
        add_action('wp_ajax_ezlens_toggle_api', [__CLASS__, 'toggle_api']);
        add_action('wp_ajax_ezlens_save_settings_ajax', [__CLASS__, 'save_settings_ajax']);
        add_action('wp_ajax_ezlens_load_settings_tab', [__CLASS__, 'load_settings_tab']);
        add_action('wp_ajax_ezlens_messaging_test', [__CLASS__, 'messaging_test']);
    }

    // ============================================================
    // ذخیره تنظیمات با AJAX (اصلاح nonce)
    // ============================================================
    public static function save_settings_ajax() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $defaults = EzLens_Auth_Settings::get_defaults();
        $textarea_keys = [
            'email_reset_template','email_welcome_template','email_forgot_otp_template',
            'email_verification_template','email_otp_template','email_order_template',
            'email_promo_template','allowed_ips','admin_login_warning','webhook_url'
        ];
        $email_keys = ['email_from','campaign_email_from','support_admin_email'];
        $url_keys = ['webhook_url','tracking_base_url'];
        $checkbox_keys = [
            'enable_otp_login','enable_manual_login','enable_registration','enable_logging',
            'enable_2fa','block_invalid_ips','smtp_auth','smtp_enabled','sms_enabled',
            'captcha_for_otp_login','enable_api','enable_email_verification','enable_phone_verification',
            'otp_sms_enabled','campaign_sms_enabled','campaign_email_enabled','campaign_track_enabled',
            'campaign_unsubscribe_enabled','frontend_protection_enabled','frontend_disable_copy',
            'frontend_disable_context','frontend_disable_selection','webhook_enabled','app_otp_enabled',
            'campaign_schedule_enabled','support_email_notifications','support_notify_admin_email'
        ];
        $sensitive_keys = [
            'sms_api_key','smtp_password','captcha_secret_key','otp_sms_api_key','otp_sms_token',
            'campaign_sms_api_key','campaign_sms_token','campaign_email_smtp_password','webhook_secret'
        ];

        $submitted = 0;
        $changed = [];

        // IMPORTANT: this endpoint saves ONLY fields submitted by the current AJAX form.
        // This prevents an AJAX-loaded tab from resetting settings belonging to other tabs.
        foreach ($defaults as $key => $default) {
            if (!array_key_exists($key, $_POST)) {
                continue;
            }

            $raw = wp_unslash($_POST[$key]);
            if (is_array($raw)) {
                $raw = '';
            }

            if (in_array($key, $checkbox_keys, true)) {
                $value = ((string)$raw === '1' || (string)$raw === 'on') ? '1' : '0';
            } elseif (in_array($key, $email_keys, true)) {
                $value = sanitize_email($raw);
            } elseif (in_array($key, $url_keys, true)) {
                $value = esc_url_raw($raw);
            } elseif (in_array($key, $textarea_keys, true)) {
                $value = sanitize_textarea_field($raw);
            } else {
                $value = sanitize_text_field($raw);
            }

            // Empty sensitive fields mean "keep existing secret" unless explicitly cleared elsewhere.
            if (in_array($key, $sensitive_keys, true) && $value === '' && (string)EzLens_Auth_Settings::get($key) !== '') {
                continue;
            }

            EzLens_Auth_Settings::set($key, $value);
            $changed[] = $key;
            $submitted++;
        }

        EzLens_Auth_Settings::clear_cache();
        wp_cache_delete('ezlens_login_settings', 'ezlens');

        wp_send_json_success([
            'message' => $submitted > 0
                ? 'تنظیمات این بخش با موفقیت ذخیره شد.'
                : 'تغییری برای ذخیره ارسال نشده بود.',
            'saved_count' => $submitted,
            'changed_keys' => $changed,
            'timestamp' => current_time('mysql'),
        ]);
    }

    // ============================================================
    // بارگذاری تب تنظیمات (اصلاح nonce)
    // ============================================================
    public static function load_settings_tab() {
        // ✅ اصلاح: استفاده از ezlens_auth_nonce
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $tab = sanitize_key($_POST['tab'] ?? 'general');
        $settings = EzLens_Auth_Settings::get_all();

        ob_start();
        self::render_tab_content($tab, $settings);
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    private static function render_tab_content($tab, $settings) {
        $captcha_type = $settings['captcha_type'] ?? 'math';
        $is_recaptcha = (strpos($captcha_type, 'recaptcha') !== false);

        switch ($tab) {
            case 'general':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/general.php';
                break;
            case 'otp':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/otp.php';
                break;
            case 'messaging':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/messaging.php';
                break;
            case 'sms':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/sms.php';
                break;
            case 'email':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/email.php';
                break;
            case 'smtp':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/smtp.php';
                break;
            case 'captcha':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/captcha.php';
                break;
            case 'appearance':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/appearance.php';
                break;
            case 'security':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/security.php';
                break;
            case 'tracking':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/tracking.php';
                break;
            case 'pages':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/pages.php';
                break;
            case 'login-methods':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/login-methods.php';
                break;
            case 'integration':
                include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/integration.php';
                break;
            default:
                echo '<p>بخش مورد نظر یافت نشد.</p>';
        }
    }

    // ============================================================
    // متدهای دیگر (اصلاح nonce)
    // ============================================================

    public static function get_stats() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $users = count_users();
        wp_send_json_success([
            'users'     => $users['total_users'],
            'admins'    => $users['avail_roles']['administrator'] ?? 0,
            'logins'    => EzLens_Auth_Logger::get_stats('login'),
            'logouts'   => EzLens_Auth_Logger::get_stats('logout'),
            'recent_logins'  => EzLens_Auth_Logger::get_recent('login', 5),
            'recent_logouts' => EzLens_Auth_Logger::get_recent('logout', 5),
        ]);
    }

    public static function export_data() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $data = [
            'version' => EZLAUTH_VERSION,
            'export_date' => current_time('mysql'),
            'site_url' => home_url(),
            'settings' => EzLens_Auth_Settings::get_all(),
            'codes' => [],
            'logs' => EzLens_Auth_Logger::get_recent(null, 100),
        ];

        $sections = ['customer-login', 'lost-password', 'user-panel', 'admin-login'];
        foreach ($sections as $section) {
            $codes = get_option('ezlens_auth_codes_' . $section, []);
            if (!empty($codes)) {
                $data['codes'][$section] = $codes;
            }
        }

        unset($data['settings']['api_key']);
        unset($data['settings']['captcha_secret_key']);
        unset($data['settings']['sms_api_key']);

        $json = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'ezlens-backup-' . date('Y-m-d-H-i') . '.json';

        wp_send_json_success([
            'content' => $json,
            'filename' => $filename,
        ]);
    }

    public static function import_data() {
        // این متد از admin-post استفاده می‌کند و نیازی به AJAX nonce ندارد
        if (!current_user_can('manage_options')) {
            wp_die('دسترسی غیرمجاز');
        }

        check_admin_referer('ezlens_import_data', 'import_nonce');

        if (empty($_FILES['import_file']['tmp_name'])) {
            wp_redirect(admin_url('admin.php?page=ezlens-auth-backup&error=' . urlencode('فایلی انتخاب نشده است.')));
            exit;
        }

        $file = $_FILES['import_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($ext !== 'json') {
            wp_redirect(admin_url('admin.php?page=ezlens-auth-backup&error=' . urlencode('فایل باید JSON باشد.')));
            exit;
        }

        $content = file_get_contents($file['tmp_name']);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_redirect(admin_url('admin.php?page=ezlens-auth-backup&error=' . urlencode('فرمت JSON نامعتبر است.')));
            exit;
        }

        if (empty($data['version']) || empty($data['settings'])) {
            wp_redirect(admin_url('admin.php?page=ezlens-auth-backup&error=' . urlencode('فایل معتبر نیست.')));
            exit;
        }

        $sensitive_keys = ['api_key', 'captcha_secret_key', 'sms_api_key'];
        foreach ($data['settings'] as $key => $value) {
            if (!in_array($key, $sensitive_keys)) {
                EzLens_Auth_Settings::set($key, $value);
            }
        }

        if (!empty($data['codes'])) {
            foreach ($data['codes'] as $section => $codes) {
                if (is_array($codes)) {
                    update_option('ezlens_auth_codes_' . $section, $codes);
                }
            }
        }

        wp_redirect(admin_url('admin.php?page=ezlens-auth-backup&imported=1'));
        exit;
    }

    public static function regenerate_api_key() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        if (!class_exists('EzLens_Auth_API')) {
            require_once EZLAUTH_PLUGIN_DIR . 'includes/class-api.php';
        }

        $api_class = EzLens_Auth_API::get_instance();
        $new_key = $api_class->regenerate_key();

        wp_send_json_success([
            'api_key' => $new_key,
            'message' => 'کلید API با موفقیت بازسازی شد.'
        ]);
    }

    public static function toggle_api() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
        }

        $status = sanitize_text_field($_POST['status']);
        EzLens_Auth_Settings::set('enable_api', $status);

        wp_send_json_success([
            'status' => $status,
            'message' => 'وضعیت API تغییر کرد.'
        ]);
    }

    public static function messaging_test() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('دسترسی غیرمجاز');
        $channel=sanitize_key($_POST['channel']??'otp');
        $target=sanitize_text_field($_POST['target']??'');
        $message=isset($_POST['message']) ? wp_kses_post(wp_unslash($_POST['message'])) : '';
        if($channel==='email'){ $target=sanitize_email($target); if(!is_email($target)) wp_send_json_error(['message'=>'ایمیل نامعتبر است.','reason_code'=>'invalid_email']); }
        else { $target=EzLens_Auth_Helper::normalize_mobile($target); if(!preg_match('/^09\d{9}$/',$target)) wp_send_json_error(['message'=>'شماره موبایل نامعتبر است.','reason_code'=>'invalid_mobile']); }
        $result=EzLens_Auth_Messaging::get_instance()->test($channel,$target,$message);
        $payload=['message'=>$result['success']?'تست با موفقیت ارسال شد.':($result['message']??'ارسال تست ناموفق بود.'),'result'=>$result];
        if(!empty($result['success'])) wp_send_json_success($payload);
        wp_send_json_error($payload);
    }
}

EzLens_Auth_Ajax_Settings::init();