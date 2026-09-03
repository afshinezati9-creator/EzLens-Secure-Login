<?php
/**
 * قالب صفحه ورود/ثبت‌نام مشتری (نسخه نهایی اصلاح‌شده - فقط شماره موبایل)
 * با کش‌شکنی تنظیمات و نمایش صحیح تب‌ها
 * @version 2.3.5
 */

if (!defined('ABSPATH')) exit;

// ===== دریافت تنظیمات با کش‌شکنی =====
$settings = EzLens_Auth_Settings::get_all();

// ===== اطمینان از خواندن مقادیر صحیح =====
$primary_color = isset($settings['primary_color']) ? $settings['primary_color'] : '#2b6cb0';
$otp_expiry = isset($settings['otp_expiry_minutes']) ? (int) $settings['otp_expiry_minutes'] : 2;

$page_title = EzLens_Auth_Settings::get_page_title('customer-login');
$page_subtitle = EzLens_Auth_Settings::get_page_subtitle('customer-login');

$enable_otp_login = isset($settings['enable_otp_login']) ? $settings['enable_otp_login'] : '1';
$enable_manual_login = isset($settings['enable_manual_login']) ? $settings['enable_manual_login'] : '1';
$enable_registration = isset($settings['enable_registration']) ? $settings['enable_registration'] : '1';
$enable_forgot_password = isset($settings['enable_forgot_password']) ? $settings['enable_forgot_password'] : '1';
$captcha_for_otp = isset($settings['captcha_for_otp_login']) ? $settings['captcha_for_otp_login'] : '0';

// ===== ساخت تب‌های فعال =====
$active_tabs = [];
if ($enable_otp_login === '1') $active_tabs[] = 'otp';
if ($enable_manual_login === '1') $active_tabs[] = 'login';
if ($enable_registration === '1') $active_tabs[] = 'register';

$default_tab = !empty($active_tabs) ? $active_tabs[0] : '';
$has_active_tab = !empty($active_tabs);

$captcha_num1 = rand(1, 9);
$captcha_num2 = rand(1, 9);
$ajax_nonce = wp_create_nonce('minimal_auth_secure_nonce_v5');
$shop_url = home_url();

// ===== آیکون‌های Heroicons (پوشه 20/solid) =====
$icon_base = 'assets/icons/20/solid/';

$icon_phone = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'phone.svg');
$icon_login = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'key.svg');
$icon_user_plus = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'user-plus.svg');
$icon_refresh = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'arrow-path-rounded-square.svg');
$icon_chevron_left = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'chevron-left.svg');
$icon_mail = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'envelope.svg');
$icon_eye = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'eye.svg');
$icon_eye_off = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'eye-slash.svg');
$icon_check = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'check.svg');
$icon_shield = file_get_contents(EZLAUTH_PLUGIN_DIR . $icon_base . 'shield-exclamation.svg');

// Fallback
if (!$icon_phone) $icon_phone = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>';
if (!$icon_login) $icon_login = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
if (!$icon_user_plus) $icon_user_plus = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>';
if (!$icon_refresh) $icon_refresh = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>';
if (!$icon_chevron_left) $icon_chevron_left = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>';
if (!$icon_mail) $icon_mail = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
if (!$icon_eye) $icon_eye = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
if (!$icon_eye_off) $icon_eye_off = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
if (!$icon_check) $icon_check = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';
if (!$icon_shield) $icon_shield = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>';
?>

<div class="min-auth-wrapper" id="minAuthWrapper" 
     data-ajaxurl="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" 
     data-nonce="<?php echo esc_attr($ajax_nonce); ?>">
     
    <div class="min-auth-logo">
        <?php if (!empty($page_title)): ?>
            <h1><?php echo esc_html($page_title); ?></h1>
        <?php else: ?>
            <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
        <?php endif; ?>
        
        <?php if (!empty($page_subtitle)): ?>
            <div class="subtitle"><?php echo esc_html($page_subtitle); ?></div>
        <?php endif; ?>
    </div>

    <?php if (!$has_active_tab): ?>
        <div class="min-auth-empty">
            <div class="icon"><?php echo $icon_shield; ?></div>
            <h3>دسترسی غیرفعال</h3>
            <p>هیچ روش ورودی فعال نیست. با مدیر سایت تماس بگیرید.</p>
            <a href="<?php echo esc_url($shop_url); ?>" class="min-auth-back">
                <?php echo $icon_chevron_left; ?>
                بازگشت به فروشگاه
            </a>
        </div>
    <?php else: ?>
        
        <?php if (count($active_tabs) > 1): ?>
        <div class="min-auth-tabs">
            <?php if ($enable_otp_login === '1'): ?>
                <button class="min-auth-tab <?php echo ($default_tab === 'otp') ? 'active' : ''; ?>" data-target="otp">
                    <?php echo $icon_phone; ?>
                    ورود با موبایل
                </button>
            <?php endif; ?>
            <?php if ($enable_manual_login === '1'): ?>
                <button class="min-auth-tab <?php echo ($default_tab === 'login') ? 'active' : ''; ?>" data-target="login">
                    <?php echo $icon_login; ?>
                    ورود با موبایل
                </button>
            <?php endif; ?>
            <?php if ($enable_registration === '1'): ?>
                <button class="min-auth-tab" data-target="register">
                    <?php echo $icon_user_plus; ?>
                    ثبت‌نام
                </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="min-auth-message" id="minAuthMessage" role="alert" aria-live="polite"></div>

        <!-- OTP -->
        <?php if ($enable_otp_login === '1'): ?>
        <form class="min-auth-form <?php echo ($default_tab === 'otp') ? 'active' : ''; ?>" id="otpForm">
            <input type="hidden" name="action" value="ezlens_otp_send">
            <input type="hidden" name="security" value="<?php echo esc_attr($ajax_nonce); ?>">
            <div class="min-auth-input-group">
                <label>شماره موبایل</label>
                <input type="tel" name="mobile" placeholder="09123456789" required>
            </div>
            <div class="min-auth-captcha otp-captcha" style="display:<?php echo ($captcha_for_otp === '1') ? 'flex' : 'none'; ?>;">
                <span class="captcha-label">کد امنیتی:</span>
                <span class="captcha-numbers">
                    <span id="otpNum1"><?php echo $captcha_num1; ?></span>
                    <span>+</span>
                    <span id="otpNum2"><?php echo $captcha_num2; ?></span>
                </span>
                <span>=</span>
                <input type="text" class="captcha-input" id="otpCaptcha" placeholder="?">
                <button type="button" class="captcha-reload" id="otpReload">
                    <?php echo $icon_refresh; ?>
                </button>
                <input type="hidden" id="otpCaptchaNum1" value="<?php echo $captcha_num1; ?>">
                <input type="hidden" id="otpCaptchaNum2" value="<?php echo $captcha_num2; ?>">
            </div>
            <button class="min-auth-btn" type="submit" id="otpSendBtn">ارسال کد تأیید</button>
            <div id="otpVerifySection" style="display:none; margin-top:14px;">
                <div class="min-auth-input-group">
                    <label>کد تأیید</label>
                    <input type="text" name="otp_code" id="otpCode" placeholder="کد ۶ رقمی" maxlength="6">
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin:4px 0 10px;padding:0 4px;font-size:14px;">
                    <span id="otpTimer" style="font-weight:700;color:#2b6cb0;font-size:16px;">
                        <?php echo str_pad($otp_expiry, 2, '0', STR_PAD_LEFT); ?>:00
                    </span>
                    <span style="font-size:12px;color:#64748b;">زمان باقیمانده</span>
                </div>
                <button class="min-auth-btn" type="button" id="otpVerifyBtn">
                    <?php echo $icon_check; ?>
                    تأیید و ورود
                </button>
                <button class="min-auth-btn" type="button" id="otpResendBtn" style="background:transparent;color:<?php echo esc_attr($primary_color); ?>;box-shadow:none;margin-top:8px;font-weight:600;font-size:14px;" disabled>
                    <?php echo $icon_refresh; ?>
                    ارسال مجدد
                </button>
                <p style="font-size:12px;color:#64748b;text-align:center;margin-top:6px;">کد تا <strong><?php echo (int)$otp_expiry; ?></strong> دقیقه معتبر است.</p>
            </div>
            <a class="min-auth-back" href="<?php echo esc_url($shop_url); ?>">
                <?php echo $icon_chevron_left; ?>
                بازگشت به فروشگاه
            </a>
        </form>
        <?php endif; ?>

        <!-- ورود دستی با شماره موبایل (بدون نام کاربری) -->
        <?php if ($enable_manual_login === '1'): ?>
        <form class="min-auth-form <?php echo ($default_tab === 'login') ? 'active' : ''; ?>" id="loginForm">
            <input type="hidden" name="action" value="min_auth_login">
            <input type="hidden" name="security" value="<?php echo esc_attr($ajax_nonce); ?>">
            <div class="min-auth-input-group">
                <label>شماره موبایل</label>
                <input type="tel" name="log" placeholder="09123456789" required>
            </div>
            <div class="min-auth-input-group">
                <label>رمز عبور</label>
                <div class="password-wrap" style="position:relative;">
                    <input type="password" name="pwd" placeholder="••••••••" required>
                    <button type="button" class="toggle-password" aria-label="نمایش رمز عبور" tabindex="-1" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;cursor:pointer;font-size:18px;color:#94a3b8;">
                        <?php echo $icon_eye; ?>
                    </button>
                </div>
            </div>
            <div class="min-auth-extra">
                <label class="min-auth-remember">
                    <input type="checkbox" name="rememberme" value="1" checked>
                    مرا به خاطر بسپار
                </label>
                <?php if ($enable_forgot_password === '1'): ?>
                    <button type="button" class="min-auth-forgot-link" id="showForgot">
                        <?php echo $icon_chevron_left; ?>
                        فراموشی رمز؟
                    </button>
                <?php endif; ?>
            </div>
            <div class="min-auth-captcha">
                <span class="captcha-label">کد امنیتی:</span>
                <span class="captcha-numbers">
                    <span id="loginNum1"><?php echo $captcha_num1; ?></span>
                    <span>+</span>
                    <span id="loginNum2"><?php echo $captcha_num2; ?></span>
                </span>
                <span>=</span>
                <input type="text" class="captcha-input" id="loginCaptcha" placeholder="?">
                <button type="button" class="captcha-reload" id="loginReload">
                    <?php echo $icon_refresh; ?>
                </button>
                <input type="hidden" id="loginCaptchaNum1" value="<?php echo $captcha_num1; ?>">
                <input type="hidden" id="loginCaptchaNum2" value="<?php echo $captcha_num2; ?>">
            </div>
            <button class="min-auth-btn" type="submit">ورود به سایت</button>
            <a class="min-auth-back" href="<?php echo esc_url($shop_url); ?>">
                <?php echo $icon_chevron_left; ?>
                بازگشت به فروشگاه
            </a>
        </form>
        <?php endif; ?>

        <!-- ثبت‌نام (بدون فیلد نام کاربری) -->
        <?php if ($enable_registration === '1'): ?>
        <form class="min-auth-form" id="registerForm">
            <input type="hidden" name="action" value="min_auth_register">
            <input type="hidden" name="security" value="<?php echo esc_attr($ajax_nonce); ?>">
            <div class="min-auth-input-group">
                <label>ایمیل</label>
                <input type="email" name="user_email" placeholder="example@email.com" required>
            </div>
            <div class="min-auth-input-group">
                <label>شماره موبایل</label>
                <input type="tel" name="user_phone" placeholder="09123456789" required>
            </div>
            <div class="min-auth-input-group">
                <label>رمز عبور</label>
                <div class="password-wrap" style="position:relative;">
                    <input type="password" name="user_pass" placeholder="حداقل ۸ کاراکتر" required minlength="8">
                    <button type="button" class="toggle-password" aria-label="نمایش رمز عبور" tabindex="-1" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);background:transparent;border:none;cursor:pointer;font-size:18px;color:#94a3b8;">
                        <?php echo $icon_eye; ?>
                    </button>
                </div>
            </div>
            <div class="min-auth-captcha">
                <span class="captcha-label">کد امنیتی:</span>
                <span class="captcha-numbers">
                    <span id="regNum1"><?php echo $captcha_num1; ?></span>
                    <span>+</span>
                    <span id="regNum2"><?php echo $captcha_num2; ?></span>
                </span>
                <span>=</span>
                <input type="text" class="captcha-input" id="regCaptcha" placeholder="?">
                <button type="button" class="captcha-reload" id="regReload">
                    <?php echo $icon_refresh; ?>
                </button>
                <input type="hidden" id="regCaptchaNum1" value="<?php echo $captcha_num1; ?>">
                <input type="hidden" id="regCaptchaNum2" value="<?php echo $captcha_num2; ?>">
            </div>
            <button class="min-auth-btn" type="submit">عضویت در سایت</button>
            <a class="min-auth-back" href="<?php echo esc_url($shop_url); ?>">
                <?php echo $icon_chevron_left; ?>
                بازگشت به فروشگاه
            </a>
        </form>
        <?php endif; ?>
    <?php endif; ?>

    <!-- فراموشی رمز (اورلی) -->
    <?php if ($enable_forgot_password === '1'): ?>
    <div class="min-auth-forgot-overlay" id="forgotOverlay">
        <div class="min-auth-forgot-box">
            <button class="min-auth-forgot-close" id="closeForgot">
                <span style="font-size:24px;line-height:1;">×</span>
            </button>
            <h3>فراموشی رمز عبور</h3>
            <p>یکی از روش‌های زیر را انتخاب کنید.</p>
            <div class="min-auth-forgot-tabs">
                <button class="min-auth-forgot-tab active" data-target="forgotSms">
                    <?php echo $icon_phone; ?>
                    پیامک
                </button>
                <button class="min-auth-forgot-tab" data-target="forgotEmail">
                    <?php echo $icon_mail; ?>
                    ایمیل
                </button>
            </div>
            <form class="min-auth-forgot-form active" id="forgotSmsForm">
                <input type="hidden" name="action" value="ezlens_forgot_send_sms">
                <input type="hidden" name="security" value="<?php echo esc_attr($ajax_nonce); ?>">
                <div class="min-auth-input-group">
                    <label>شماره موبایل</label>
                    <input type="tel" name="mobile" placeholder="09123456789" required>
                </div>
                <div class="min-auth-message forgot-msg"></div>
                <button class="min-auth-forgot-btn" type="submit">ارسال کد</button>
            </form>
            <form class="min-auth-forgot-form" id="forgotEmailForm">
                <input type="hidden" name="action" value="ezlens_forgot_send_email">
                <input type="hidden" name="security" value="<?php echo esc_attr($ajax_nonce); ?>">
                <div class="min-auth-input-group">
                    <label>آدرس ایمیل</label>
                    <input type="email" name="email" placeholder="example@email.com" required>
                </div>
                <div class="min-auth-message forgot-msg"></div>
                <button class="min-auth-forgot-btn" type="submit">ارسال کد</button>
            </form>
            <button class="min-auth-forgot-back" id="backToLogin">
                <?php echo $icon_chevron_left; ?>
                بازگشت
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>