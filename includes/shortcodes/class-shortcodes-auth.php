<?php
/**
 * شورت‌کدهای ورود، ثبت‌نام و OTP – با مسیر جدید
 * @version 3.0.0
 */
class EzLens_Auth_Shortcodes_Auth {

    /**
     * رندر صفحه ورود/ثبت‌نام مشتری (مسیر جدید)
     */
    public static function render_minimal_auth() {
        if (!EzLens_Auth_Settings::is_page_enabled('customer-login')) {
            return self::get_disabled_message('ورود مشتری', 'صفحه ورود مشتری غیرفعال است.');
        }
        if (is_user_logged_in()) {
            return self::get_logged_in_message();
        }

        $settings = EzLens_Auth_Settings::get_all();
        $primary_color = $settings['primary_color'] ?? '#2b6cb0';
        $page_subtitle = EzLens_Auth_Settings::get_page_subtitle('customer-login');

        // ✅ مسیر جدید به frontend/pages/login.php
        ob_start();
        include EZLAUTH_FRONTEND_DIR . 'pages/login.php';
        return ob_get_clean();
    }

    /**
     * رندر صفحه ورود مدیر (مسیر بدون تغییر)
     */
    public static function render_admin_login() {
        if (!EzLens_Auth_Settings::is_page_enabled('admin-login')) {
            wp_redirect(wp_login_url());
            exit;
        }
        if (is_user_logged_in() && current_user_can('manage_options')) {
            wp_redirect(admin_url());
            exit;
        }

        $page_title = EzLens_Auth_Settings::get_page_title('admin-login');
        $page_subtitle = EzLens_Auth_Settings::get_page_subtitle('admin-login');
        $primary_color = EzLens_Auth_Settings::get('primary_color');
        $warning_text = EzLens_Auth_Settings::get('admin_login_warning');

        // admin-login.php هنوز در templates/ است
        ob_start();
        include EZLAUTH_TEMPLATES_DIR . 'admin-login.php';
        return ob_get_clean();
    }

    // ============================================================
    // AJAX Handlers (همان‌های قبلی)
    // ============================================================

    public static function ajax_otp_send() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $mobile = EzLens_Auth_Helper::normalize_mobile($_POST['mobile'] ?? '');
        if (!preg_match('/^09\d{9}$/', $mobile)) {
            wp_send_json_error(['message' => 'شماره موبایل نامعتبر است.']);
            return;
        }

        if (EzLens_Auth_Settings::get('captcha_for_otp_login')) {
            $captcha_type = EzLens_Auth_Settings::get('captcha_type');
            if ($captcha_type === 'math') {
                $captcha_answer = trim($_POST['captcha_answer'] ?? '');
                $captcha_num1 = (int)($_POST['captcha_num1'] ?? 0);
                $captcha_num2 = (int)($_POST['captcha_num2'] ?? 0);
                if ($captcha_answer === '' || (int)$captcha_answer !== ($captcha_num1 + $captcha_num2)) {
                    wp_send_json_error(['message' => 'کد امنیتی اشتباه است.']);
                    return;
                }
            }
        }

        if (!EzLens_Auth_OTP::can_request($mobile, 'login')) {
            wp_send_json_error(['message' => 'تعداد درخواست بیش از حد مجاز. لطفاً یک دقیقه صبر کنید.']);
            return;
        }

        $code = EzLens_Auth_OTP::store($mobile, 'login');
        if (!$code) {
            error_log('OTP Store Failed: ' . $mobile);
            wp_send_json_error(['message' => 'خطا در ذخیره کد تأیید. لطفاً مجدداً تلاش کنید.']);
            return;
        }
        error_log('OTP Send - Mobile: ' . $mobile . ', Code: ' . $code);

        if (EzLens_Auth_SMS::is_enabled()) {
            $sms = new EzLens_Auth_SMS();
            $result = $sms->send_verify($mobile, $code);
            if (!$result['success']) {
                wp_send_json_error(['message' => $result['message']]);
                return;
            }
        }

        wp_send_json_success(['message' => 'کد تأیید با موفقیت ارسال شد.']);
    }

    public static function ajax_otp_verify() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $mobile = EzLens_Auth_Helper::normalize_mobile($_POST['mobile'] ?? '');
        $code = sanitize_text_field($_POST['code'] ?? '');

        if (!preg_match('/^09\d{9}$/', $mobile)) {
            wp_send_json_error(['message' => 'شماره موبایل نامعتبر است.']);
            return;
        }
        if (strlen($code) !== 6) {
            wp_send_json_error(['message' => 'کد باید ۶ رقم باشد.']);
            return;
        }

        error_log('OTP Verify - Mobile: ' . $mobile . ', Code: ' . $code);

        $result = EzLens_Auth_OTP::verify($mobile, $code, 'login');
        if (!$result['success']) {
            error_log('OTP Verify Failed: ' . print_r($result, true));
            wp_send_json_error(['message' => $result['message']]);
            return;
        }

        $user = get_user_by('login', $mobile);
        if (!$user) {
            $password = wp_generate_password(12, true);
            $user_id = wp_create_user($mobile, $password, $mobile . '@ezlens.ir');
            if (!is_wp_error($user_id)) {
                update_user_meta($user_id, 'billing_phone', $mobile);
                update_user_meta($user_id, 'user_phone', $mobile);
                $user = get_user_by('id', $user_id);
            } else {
                wp_send_json_error(['message' => 'خطا در ایجاد حساب کاربری.']);
                return;
            }
        }

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        do_action('wp_login', $user->user_login, $user);
        EzLens_Auth_Logger::log($user->ID, 'login');

        wp_send_json_success([
            'message'  => 'ورود موفقیت‌آمیز.',
            'redirect' => apply_filters('ezlens_auth_login_redirect', home_url(), $user)
        ]);
    }

    public static function ajax_login() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $ip = $_SERVER['REMOTE_ADDR'];
        $max_attempts = (int) EzLens_Auth_Settings::get('max_attempts') ?: 5;
        $lockout_minutes = (int) EzLens_Auth_Settings::get('lockout_minutes') ?: 15;

        $lock_message = EzLens_Auth_Helper::get_login_lock_message($ip, $max_attempts, $lockout_minutes);
        if ($lock_message) {
            wp_send_json_error(['message' => $lock_message]);
            return;
        }

        $captcha_type = EzLens_Auth_Settings::get('captcha_type');
        if ($captcha_type === 'math') {
            $captcha_answer = trim($_POST['captcha_answer'] ?? '');
            $captcha_num1 = (int)($_POST['captcha_num1'] ?? 0);
            $captcha_num2 = (int)($_POST['captcha_num2'] ?? 0);
            if ($captcha_answer === '' || (int)$captcha_answer !== ($captcha_num1 + $captcha_num2)) {
                wp_send_json_error(['message' => 'کد امنیتی اشتباه است.']);
                return;
            }
        }

        $username = sanitize_text_field($_POST['log'] ?? '');
        $password = $_POST['pwd'] ?? '';
        $remember = isset($_POST['rememberme']) ? (int)$_POST['rememberme'] : 0;

        if (empty($username) || empty($password)) {
            EzLens_Auth_Helper::increment_login_attempts($ip, $lockout_minutes);
            wp_send_json_error(['message' => 'لطفاً نام کاربری و رمز عبور را وارد کنید.']);
            return;
        }

        $user = wp_signon([
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => (bool)$remember
        ], is_ssl());

        if (is_wp_error($user)) {
            EzLens_Auth_Logger::log(0, 'failed_login');
            EzLens_Auth_Helper::increment_login_attempts($ip, $lockout_minutes);
            wp_send_json_error(['message' => 'نام کاربری یا رمز عبور اشتباه است.']);
            return;
        }

        EzLens_Auth_Helper::reset_login_attempts($ip);

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, (bool)$remember);
        do_action('wp_login', $user->user_login, $user);

        wp_send_json_success([
            'message'  => 'ورود موفقیت‌آمیز.',
            'redirect' => apply_filters('ezlens_auth_login_redirect', home_url(), $user)
        ]);
    }

    public static function ajax_register() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        if (!EzLens_Auth_Settings::get('enable_registration')) {
            wp_send_json_error(['message' => 'ثبت‌نام در حال حاضر غیرفعال است.']);
            return;
        }

        $captcha_type = EzLens_Auth_Settings::get('captcha_type');
        if ($captcha_type === 'math') {
            $captcha_answer = trim($_POST['captcha_answer'] ?? '');
            $captcha_num1 = (int)($_POST['captcha_num1'] ?? 0);
            $captcha_num2 = (int)($_POST['captcha_num2'] ?? 0);
            if ($captcha_answer === '' || (int)$captcha_answer !== ($captcha_num1 + $captcha_num2)) {
                wp_send_json_error(['message' => 'کد امنیتی اشتباه است.']);
                return;
            }
        }

        $email = sanitize_email($_POST['user_email'] ?? '');
        $phone = sanitize_text_field($_POST['user_phone'] ?? '');
        $password = $_POST['user_pass'] ?? '';

        if (empty($email) || empty($phone) || empty($password)) {
            wp_send_json_error(['message' => 'لطفاً تمامی فیلدها را پر کنید.']);
            return;
        }
        if (strlen($password) < 8) {
            wp_send_json_error(['message' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.']);
            return;
        }

        $phone = EzLens_Auth_Helper::normalize_mobile($phone);
        if (!preg_match('/^09\d{9}$/', $phone)) {
            wp_send_json_error(['message' => 'شماره موبایل نامعتبر است.']);
            return;
        }
        if (!is_email($email)) {
            wp_send_json_error(['message' => 'ایمیل نامعتبر است.']);
            return;
        }
        if (username_exists($phone)) {
            wp_send_json_error(['message' => 'این شماره موبایل قبلاً ثبت شده است.']);
            return;
        }
        if (email_exists($email)) {
            wp_send_json_error(['message' => 'این ایمیل ثبت شده است.']);
            return;
        }

        $email_verification = EzLens_Auth_Settings::is_email_verification_enabled();
        $phone_verification = EzLens_Auth_Settings::is_phone_verification_enabled();

        if (!$email_verification && !$phone_verification) {
            $user_id = self::create_user($phone, $password, $email, $phone);
            if (is_wp_error($user_id)) {
                wp_send_json_error(['message' => $user_id->get_error_message()]);
                return;
            }
            self::login_user($user_id);
            wp_send_json_success([
                'message'  => 'حساب شما ساخته شد.',
                'redirect' => apply_filters('ezlens_auth_register_redirect', home_url('/my-account/'), $user_id)
            ]);
            return;
        }

        $session_key = 'ezlens_pending_user_' . wp_generate_password(16, false);
        set_transient($session_key, [
            'username' => $phone,
            'email'    => $email,
            'phone'    => $phone,
            'password_hash' => wp_hash_password($password),
        ], HOUR_IN_SECONDS);

        $codes_sent = [];
        if ($email_verification) {
            $code = EzLens_Auth_OTP::store($email, 'register_email');
            self::send_verification_email($email, $phone, $code);
            $codes_sent[] = 'ایمیل';
        }
        if ($phone_verification) {
            $code = EzLens_Auth_OTP::store($phone, 'register_phone');
            if (EzLens_Auth_SMS::is_enabled()) {
                $sms = new EzLens_Auth_SMS();
                $sms->send_verify($phone, $code);
            }
            $codes_sent[] = 'شماره موبایل';
        }

        wp_send_json_success([
            'message'              => 'کد تأیید به ' . implode(' و ', $codes_sent) . ' ارسال شد.',
            'requires_verification' => true,
            'session_key'          => $session_key,
        ]);
    }

    public static function ajax_verify_register() {
        check_ajax_referer('minimal_auth_secure_nonce_v5', 'security');

        $session_key = sanitize_text_field($_POST['session_key'] ?? '');
        $code = sanitize_text_field($_POST['code'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'email');

        if (empty($session_key) || empty($code) || strlen($code) !== 6) {
            wp_send_json_error(['message' => 'اطلاعات نامعتبر است.']);
            return;
        }

        $user_data = get_transient($session_key);
        if (!$user_data) {
            wp_send_json_error(['message' => 'مدت تایید منقضی شده. دوباره ثبت‌نام کنید.']);
            return;
        }

        $identifier = ($type === 'email') ? $user_data['email'] : $user_data['phone'];
        $action = ($type === 'email') ? 'register_email' : 'register_phone';

        $result = EzLens_Auth_OTP::verify($identifier, $code, $action);
        if (!$result['success']) {
            wp_send_json_error(['message' => $result['message']]);
            return;
        }

        if ($type === 'email') {
            set_transient($session_key . '_verified_email', true, HOUR_IN_SECONDS);
        } else {
            set_transient($session_key . '_verified_phone', true, HOUR_IN_SECONDS);
        }

        $email_verification = EzLens_Auth_Settings::is_email_verification_enabled();
        $phone_verification = EzLens_Auth_Settings::is_phone_verification_enabled();

        $all_verified = true;
        if ($email_verification && !get_transient($session_key . '_verified_email')) $all_verified = false;
        if ($phone_verification && !get_transient($session_key . '_verified_phone')) $all_verified = false;

        if (!$all_verified) {
            wp_send_json_success([
                'message'  => '✅ کد تأیید شد. روش دیگر را نیز تأیید کنید.',
                'next_step' => true,
                'remaining' => ($email_verification && !get_transient($session_key . '_verified_email')) ? 'email' : 'phone'
            ]);
            return;
        }

        $user_id = self::create_user_from_hash(
            $user_data['username'],
            $user_data['password_hash'],
            $user_data['email'],
            $user_data['phone']
        );
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
            return;
        }

        delete_transient($session_key);
        delete_transient($session_key . '_verified_email');
        delete_transient($session_key . '_verified_phone');

        self::login_user($user_id);

        wp_send_json_success([
            'message'  => '✅ ثبت‌نام تکمیل شد.',
            'redirect' => apply_filters('ezlens_auth_register_redirect', home_url('/my-account/'), $user_id)
        ]);
    }

    // ============================================================
    // توابع کمکی (داخلی)
    // ============================================================

    private static function get_disabled_message($title, $message) {
        return '<div style="text-align:center;padding:60px 20px;font-family:IRANYekan,Tahoma,sans-serif;">
            <div style="font-size:48px;margin-bottom:16px;">⛔</div>
            <h2 style="font-size:22px;font-weight:700;color:#0f172a;margin:0 0 8px;">' . esc_html($title) . '</h2>
            <p style="color:#64748b;margin:0 0 20px;">' . esc_html($message) . '</p>
            <a href="' . home_url() . '" style="display:inline-block;padding:12px 32px;background:#2b6cb0;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">بازگشت به صفحه اصلی</a>
        </div>';
    }

    private static function get_logged_in_message() {
        return '<div style="text-align:center;padding:60px 20px;font-family:IRANYekan,Tahoma,sans-serif;">
            <div style="font-size:48px;margin-bottom:16px;">👋</div>
            <h2 style="font-size:22px;font-weight:700;color:#0f172a;margin:0 0 8px;">شما وارد شده‌اید</h2>
            <p style="color:#64748b;margin:0 0 20px;">برای دسترسی به این صفحه، خارج شوید.</p>
            <a href="' . wp_logout_url(home_url()) . '" style="display:inline-block;padding:12px 32px;background:#dc2626;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">خروج از حساب</a>
        </div>';
    }

    private static function create_user($username, $password, $email, $phone) {
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) return $user_id;

        $default_role = EzLens_Auth_Settings::get_default_user_role();
        $user = new WP_User($user_id);
        $user->set_role($default_role);

        update_user_meta($user_id, 'user_phone', $phone);
        update_user_meta($user_id, 'billing_phone', $phone);
        update_user_meta($user_id, 'registration_ip', $_SERVER['REMOTE_ADDR'] ?? '');
        update_user_meta($user_id, 'registration_date', current_time('mysql'));

        do_action('ezlens_auth_user_registered', $user_id, [
            'username' => $username,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => $default_role
        ]);

        return $user_id;
    }

    private static function create_user_from_hash($username, $password_hash, $email, $phone) {
        global $wpdb;
        
        if (username_exists($username) || email_exists($email)) {
            return new WP_Error('user_exists', 'این کاربر قبلاً ثبت شده است.');
        }

        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $password_hash,
            'role'       => EzLens_Auth_Settings::get_default_user_role(),
        ]);

        if (is_wp_error($user_id)) return $user_id;

        update_user_meta($user_id, 'user_phone', $phone);
        update_user_meta($user_id, 'billing_phone', $phone);
        update_user_meta($user_id, 'registration_ip', $_SERVER['REMOTE_ADDR'] ?? '');
        update_user_meta($user_id, 'registration_date', current_time('mysql'));

        do_action('ezlens_auth_user_registered', $user_id, [
            'username' => $username,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => EzLens_Auth_Settings::get_default_user_role()
        ]);

        return $user_id;
    }

    private static function login_user($user_id) {
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        do_action('wp_login', get_userdata($user_id)->user_login, get_userdata($user_id));
    }

    private static function send_verification_email($email, $name, $code) {
        $template = EzLens_Auth_Settings::get('email_verification_template') ?: 
            'سلام {name}\n\nبرای تکمیل ثبت‌نام در {site_name}، کد تأیید زیر را وارد کنید:\n{code}\n\nاین کد تا {expiry} دقیقه اعتبار دارد.';
        $subject = '🔐 کد تأیید ثبت‌نام در ' . get_bloginfo('name');
        EzLens_Auth_Helper::send_email($email, $subject, $template, [
            '{name}' => $name,
            '{code}' => $code
        ]);
    }
}