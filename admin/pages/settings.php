<?php
/**
 * قالب صفحه تنظیمات عمومی (تب‌دار با AJAX) – با دکمه ذخیره اصلاح‌شده
 * EzLens Auth - Admin Settings
 * @version 2.3.4
 */

$settings = EzLens_Auth_Settings::get_all();
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

// آیکون‌های SVG
$icon_settings = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/settings-svgrepo-com.svg');
$icon_otp = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/lock-password-unlocked-svgrepo-com.svg');
$icon_sms = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/phone-calling-svgrepo-com.svg');
$icon_email = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/mail-svgrepo-com.svg');
$icon_smtp = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/send-svgrepo-com.svg');
$icon_captcha = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/shield-slash-alt-1-svgrepo-com.svg');
$icon_appearance = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/edit-3-svgrepo-com.svg');
$icon_security = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/shield-slash-alt-1-svgrepo-com.svg');
$icon_tracking = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/map-pin-svgrepo-com.svg');
$icon_pages = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/package-box-ui-2-svgrepo-com.svg');
$icon_integration = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/server.svg');
$icon_login_methods = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/user-plus-svgrepo-com.svg');
$icon_save = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/download-svgrepo-com.svg');
$icon_reset = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/refresh-cw-svgrepo-com.svg');

// fallback برای آیکون‌ها
if (!$icon_integration) $icon_integration = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/></svg>';
if (!$icon_login_methods) $icon_login_methods = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>';
if (!$icon_settings) $icon_settings = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00 .73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="3"/></svg>';
if (!$icon_otp) $icon_otp = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>';
if (!$icon_sms) $icon_sms = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>';
if (!$icon_email) $icon_email = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
if (!$icon_smtp) $icon_smtp = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>';
if (!$icon_captcha) $icon_captcha = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>';
if (!$icon_appearance) $icon_appearance = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
if (!$icon_security) $icon_security = $icon_captcha;
if (!$icon_tracking) $icon_tracking = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';
if (!$icon_pages) $icon_pages = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>';
if (!$icon_save) $icon_save = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>';
if (!$icon_reset) $icon_reset = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>';
?>

<div class="wrap ezlens-settings">
    <h1 class="wp-heading-inline">
        <?php echo $icon_settings; ?>
        تنظیمات عمومی
    </h1>
    <p class="description">تنظیمات پایه و اصلی پلاگین – هر بخش در یک تب جداگانه</p>

    <?php if (isset($_GET['saved'])): ?>
        <div class="ezlens-notice success">تنظیمات با موفقیت ذخیره شد.</div>
    <?php endif; ?>
    <?php if (isset($_GET['reset'])): ?>
        <div class="ezlens-notice info">تنظیمات به حالت پیش‌فرض بازگشت.</div>
    <?php endif; ?>

    <!-- ===== منوی تب‌ها ===== -->
    <div class="ezlens-settings-tabs">
        <button class="settings-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>" data-tab="general">
            <?php echo $icon_settings; ?>
            عمومی
        </button>
        <button class="settings-tab <?php echo $active_tab === 'otp' ? 'active' : ''; ?>" data-tab="otp">
            <?php echo $icon_otp; ?>
            OTP
        </button>
        <button class="settings-tab <?php echo $active_tab === 'messaging' ? 'active' : ''; ?>" data-tab="messaging">
            <?php echo $icon_sms; ?>
            پیام‌رسانی
        </button>
        <button class="settings-tab <?php echo $active_tab === 'sms' ? 'active' : ''; ?>" data-tab="sms">
            <?php echo $icon_sms; ?>
            SMS
        </button>
        <button class="settings-tab <?php echo $active_tab === 'email' ? 'active' : ''; ?>" data-tab="email">
            <?php echo $icon_email; ?>
            ایمیل
        </button>
        <button class="settings-tab <?php echo $active_tab === 'smtp' ? 'active' : ''; ?>" data-tab="smtp">
            <?php echo $icon_smtp; ?>
            SMTP
        </button>
        <button class="settings-tab <?php echo $active_tab === 'captcha' ? 'active' : ''; ?>" data-tab="captcha">
            <?php echo $icon_captcha; ?>
            کپچا
        </button>
        <button class="settings-tab <?php echo $active_tab === 'appearance' ? 'active' : ''; ?>" data-tab="appearance">
            <?php echo $icon_appearance; ?>
            ظاهر
        </button>
        <button class="settings-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>" data-tab="security">
            <?php echo $icon_security; ?>
            امنیت
        </button>
        <button class="settings-tab <?php echo $active_tab === 'tracking' ? 'active' : ''; ?>" data-tab="tracking">
            <?php echo $icon_tracking; ?>
            رهگیری
        </button>
        <button class="settings-tab <?php echo $active_tab === 'pages' ? 'active' : ''; ?>" data-tab="pages">
            <?php echo $icon_pages; ?>
            صفحات
        </button>
        <button class="settings-tab <?php echo $active_tab === 'login-methods' ? 'active' : ''; ?>" data-tab="login-methods">
            <?php echo $icon_login_methods; ?>
            روش‌های ورود
        </button>
        <button class="settings-tab <?php echo $active_tab === 'integration' ? 'active' : ''; ?>" data-tab="integration">
            <?php echo $icon_integration; ?>
            اتصال و API
        </button>
    </div>

    <!-- ===== محتوای تب‌ها (با AJAX بارگذاری می‌شود) ===== -->
    <div class="ezlens-settings-content" id="settingsContent">
        <div style="text-align:center;padding:40px;color:#94a3b8;">در حال بارگذاری...</div>
    </div>

    <!-- ===== کارت اقدامات (پایین) ===== -->
    <div class="card card-actions" style="margin-top:20px;">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <!-- ✅ دکمه ذخیره با id="saveAllSettings" -->
            <button type="button" id="saveAllSettings" class="button button-primary" style="padding:8px 24px;font-weight:600;">
                <?php echo $icon_save; ?>
                ذخیره تنظیمات این بخش
            </button>
            <a href="<?php echo admin_url('admin-post.php?action=ezlens_auth_reset_settings'); ?>" class="button" style="background:#e53e3e;color:#fff;border-color:#e53e3e;padding:8px 24px;font-weight:600;" onclick="return confirm('آیا مطمئن هستید؟ تمام تنظیمات به حالت پیش‌فرض بازنشانی می‌شوند.')">
                <?php echo $icon_reset; ?>
                بازنشانی همه
            </a>
            <span style="font-size:12px;color:#a0aec0;margin-right:auto;">تنظیمات پس از فشردن دکمه ذخیره، به‌صورت امن در سایت ثبت می‌شوند.</span>
        </div>
    </div>
</div>

<!-- استایل‌های مربوط به تنظیمات به فایل admin.css منتقل شده‌اند -->