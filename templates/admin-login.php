<?php
/**
 * قالب صفحه ورود مدیران (نسخه کامل اصلاح‌شده با آیکون‌های کامل)
 * @version 2.3.5
 */

$page_title = EzLens_Auth_Settings::get_page_title('admin-login');
$page_subtitle = EzLens_Auth_Settings::get_page_subtitle('admin-login');
$primary_color = EzLens_Auth_Settings::get('primary_color') ?: '#2b6cb0';
$logo_url = EzLens_Auth_Settings::get('logo_url') ?: '';
$login_slug = EzLens_Auth_Settings::get('admin_login_slug');
$shop_url = home_url();
$login_url = home_url('/' . $login_slug);

$captcha_num1 = rand(1, 9);
$captcha_num2 = rand(1, 9);
$ajax_url = admin_url('admin-ajax.php');
$nonce = wp_create_nonce('minimal_auth_secure_nonce_v5');

// ===== آیکون‌های SVG (همه متغیرها تعریف شده‌اند) =====
$icon_user = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/user-alt-1-svgrepo-com.svg');
$icon_eye = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/eye-svgrepo-com.svg');
$icon_eye_off = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/eye-off-svgrepo-com.svg');
$icon_refresh = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/refresh-cw-svgrepo-com.svg');
$icon_chevron_left = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/chevron-left-square-svgrepo-com.svg');
$icon_lock = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/lock-password-unlocked-svgrepo-com.svg');
$icon_logout = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/log-out-off-out-svgrepo-com.svg');
$icon_phone = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/phone-svgrepo-com.svg');
$icon_email = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/mail-svgrepo-com.svg');
$icon_check = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/check-svgrepo-com.svg');

// ===== Fallback برای آیکون‌ها (اگر فایل وجود نداشت) =====
if (!$icon_user) $icon_user = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
if (!$icon_eye) $icon_eye = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
if (!$icon_eye_off) $icon_eye_off = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
if (!$icon_refresh) $icon_refresh = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>';
if (!$icon_chevron_left) $icon_chevron_left = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>';
if (!$icon_lock) $icon_lock = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>';
if (!$icon_logout) $icon_logout = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
if (!$icon_phone) $icon_phone = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>';
if (!$icon_email) $icon_email = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
if (!$icon_check) $icon_check = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';

// ===== استایل اصلاح آیکون‌ها =====
?>
<style>
    .admin-auth-wrap svg {
        width: 18px !important;
        height: 18px !important;
        stroke: currentColor;
        fill: none;
        flex-shrink: 0;
    }
    .admin-auth-wrap .admin-auth-badge svg {
        width: 16px !important;
        height: 16px !important;
    }
    .admin-auth-wrap .toggle-password svg {
        width: 20px !important;
        height: 20px !important;
    }
    .admin-auth-wrap .admin-auth-back svg {
        width: 16px !important;
        height: 16px !important;
    }
    .admin-auth-wrap .admin-auth-forgot-tab svg {
        width: 16px !important;
        height: 16px !important;
    }
    .admin-auth-wrap .admin-auth-forgot-btn svg {
        width: 16px !important;
        height: 16px !important;
    }
    .admin-auth-wrap .captcha-reload svg {
        width: 16px !important;
        height: 16px !important;
    }
</style>

<div class="admin-auth-wrap" data-ajaxurl="<?php echo esc_url($ajax_url); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
    <div class="admin-auth-container">
        <!-- لوگو -->
        <div class="admin-auth-logo">
            <?php if ($logo_url): ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" style="max-width:140px;height:auto;">
            <?php else: ?>
                <div class="admin-auth-logo-text"><?php echo esc_html(get_bloginfo('name')); ?></div>
            <?php endif; ?>
        </div>

        <!-- باکس ورود -->
        <div class="admin-auth-card">
            <div class="admin-auth-badge">
                <?php echo $icon_lock; ?>
                ورود مدیران
            </div>
            <h2><?php echo esc_html($page_title); ?></h2>
            <p class="admin-auth-subtitle"><?php echo esc_html($page_subtitle); ?></p>
            <p class="admin-auth-url-hint">
                آدرس جدید: <code><?php echo esc_html($login_url); ?></code>
            </p>

            <form id="admin-login-form" method="post" autocomplete="off">
                <!-- Honeypot -->
                <div class="admin-auth-honeypot" style="position:absolute;left:-9999px;top:-9999px;opacity:0;pointer-events:none;height:0;width:0;">
                    <label for="aa_hp">لطفاً این فیلد را خالی بگذارید</label>
                    <input type="text" name="aa_hp" id="aa_hp" value="">
                </div>

                <!-- فیلد نام کاربری -->
                <div class="admin-auth-field">
                    <label for="admin_username">نام کاربری مدیریت</label>
                    <div class="field-input-wrap">
                        <input type="text" id="admin_username" name="log" required autocomplete="username" placeholder="نام کاربری خود را وارد کنید" autofocus>
                        <span class="field-icon"><?php echo $icon_user; ?></span>
                    </div>
                </div>

                <!-- فیلد رمز عبور با چشم -->
                <div class="admin-auth-field">
                    <label for="admin_password">رمز عبور</label>
                    <div class="field-input-wrap password-wrap">
                        <input type="password" id="admin_password" name="pwd" required autocomplete="current-password" placeholder="رمز عبور خود را وارد کنید">
                        <button type="button" class="toggle-password" aria-label="نمایش رمز عبور" tabindex="-1">
                            <span class="eye-open"><?php echo $icon_eye; ?></span>
                            <span class="eye-closed" style="display:none;"><?php echo $icon_eye_off; ?></span>
                        </button>
                    </div>
                </div>

                <!-- گزینه‌های اضافی -->
                <div class="admin-auth-extra">
                    <label class="admin-auth-remember">
                        <input type="checkbox" name="rememberme" value="1" checked>
                        مرا به خاطر بسپار
                    </label>
                    <button type="button" class="admin-auth-forgot-btn" id="showAdminForgot">
                        <?php echo $icon_lock; ?>
                        فراموشی رمز؟
                    </button>
                </div>

                <!-- کپچا -->
                <div class="admin-auth-captcha">
                    <span class="captcha-label">کد امنیتی:</span>
                    <span class="captcha-numbers">
                        <span><?php echo $captcha_num1; ?></span>
                        <span>+</span>
                        <span><?php echo $captcha_num2; ?></span>
                    </span>
                    <span class="captcha-equals">=</span>
                    <input type="text" class="captcha-input" name="captcha_answer" placeholder="?" required autocomplete="off" inputmode="numeric" pattern="[0-9]*">
                    <button type="button" class="captcha-reload" title="تولید کد جدید">
                        <?php echo $icon_refresh; ?>
                    </button>
                    <input type="hidden" name="captcha_num1" value="<?php echo $captcha_num1; ?>">
                    <input type="hidden" name="captcha_num2" value="<?php echo $captcha_num2; ?>">
                </div>

                <!-- دکمه ورود -->
                <button type="submit" class="admin-auth-btn" id="adminLoginBtn">ورود به مدیریت</button>

                <!-- پیام‌ها -->
                <div class="admin-auth-msg" id="admin-login-msg" role="alert" aria-live="polite"></div>

                <!-- هشدار -->
                <div class="admin-auth-warning">
                    <?php echo esc_html(EzLens_Auth_Settings::get('admin_login_warning') ?: 'آدرس‌های wp-admin و wp-login غیرفعال شده‌اند'); ?>
                </div>

                <!-- بازگشت به فروشگاه -->
                <a class="admin-auth-back" href="<?php echo esc_url($shop_url); ?>">
                    <?php echo $icon_logout; ?>
                    بازگشت به فروشگاه
                </a>
            </form>
        </div>
    </div>

    <!-- فراموشی رمز مدیر (اورلی) -->
    <div class="admin-auth-forgot-overlay" id="adminForgotOverlay">
        <div class="admin-auth-forgot-box">
            <button class="admin-auth-forgot-close" id="closeAdminForgot">✕</button>
            <h3>فراموشی رمز عبور</h3>
            <p>یکی از روش‌های زیر را انتخاب کنید تا کد تأیید دریافت کنید.</p>

            <div class="admin-auth-forgot-tabs">
                <button class="admin-auth-forgot-tab active" data-target="adminForgotEmail">
                    <?php echo $icon_email; ?>
                    ایمیل
                </button>
                <button class="admin-auth-forgot-tab" data-target="adminForgotPhone">
                    <?php echo $icon_phone; ?>
                    شماره موبایل
                </button>
            </div>

            <!-- فرم ایمیل -->
            <form class="admin-auth-forgot-form active" id="adminForgotEmailForm">
                <input type="hidden" name="action" value="ezlens_admin_forgot_send">
                <input type="hidden" name="type" value="email">
                <input type="hidden" name="security" value="<?php echo esc_attr($nonce); ?>">
                <div class="admin-auth-input-group">
                    <label>آدرس ایمیل مدیریت</label>
                    <input type="email" name="identifier" placeholder="admin@example.com" required>
                </div>
                <div class="admin-auth-message forgot-msg"></div>
                <button class="admin-auth-forgot-btn" type="submit">ارسال کد</button>
            </form>

            <!-- فرم شماره موبایل -->
            <form class="admin-auth-forgot-form" id="adminForgotPhoneForm">
                <input type="hidden" name="action" value="ezlens_admin_forgot_send">
                <input type="hidden" name="type" value="phone">
                <input type="hidden" name="security" value="<?php echo esc_attr($nonce); ?>">
                <div class="admin-auth-input-group">
                    <label>شماره موبایل</label>
                    <input type="tel" name="identifier" placeholder="09123456789" required>
                </div>
                <div class="admin-auth-message forgot-msg"></div>
                <button class="admin-auth-forgot-btn" type="submit">ارسال کد</button>
            </form>

            <!-- بخش تأیید کد -->
            <div id="adminForgotVerifySection" style="display:none; margin-top:16px;">
                <div class="admin-auth-input-group">
                    <label>کد تأیید</label>
                    <input type="text" id="adminForgotCode" placeholder="کد ۶ رقمی" maxlength="6" inputmode="numeric">
                    <input type="hidden" id="adminForgotUserId" value="">
                    <input type="hidden" id="adminForgotType" value="">
                </div>
                <button class="admin-auth-forgot-btn" type="button" id="adminForgotVerifyBtn">
                    <?php echo $icon_check; ?>
                    تأیید کد
                </button>
                <p style="font-size:12px;color:#64748b;text-align:center;margin-top:6px;">⏱️ کد تا <strong><?php echo (int)EzLens_Auth_Settings::get('otp_expiry_minutes'); ?></strong> دقیقه معتبر است.</p>
            </div>

            <button class="admin-auth-forgot-back" id="backToAdminLogin">
                <?php echo $icon_chevron_left; ?>
                بازگشت به ورود
            </button>
        </div>
    </div>
</div>