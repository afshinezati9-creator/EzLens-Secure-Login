<?php
if (!defined('ABSPATH')) exit;
$s = $settings;
?>
<div class="settings-tab-content">
    <h2>تنظیمات ایمیل</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">تنظیمات ایمیل‌های سیستمی و زیرساخت Campaign Email</p>

    <div class="setting-row">
        <label for="email_from">ارسال‌کننده (From)</label>
        <input type="email" id="email_from" name="email_from" value="<?php echo esc_attr($s['email_from'] ?? 'noreply@ezlens.ir'); ?>" placeholder="noreply@example.com">
        <span class="hint">آدرس پیش‌فرض ایمیل‌های سیستمی</span>
    </div>

    <div class="setting-row">
        <label for="email_from_name">نام نمایشی</label>
        <input type="text" id="email_from_name" name="email_from_name" value="<?php echo esc_attr($s['email_from_name'] ?? 'ایزی لنز'); ?>" placeholder="ایزی لنز">
    </div>

    <div class="setting-row">
        <label for="email_logging">گزارش ایمیل</label>
        <div class="toggle">
            <input type="checkbox" id="email_logging" name="email_logging" value="1" <?php checked($s['email_logging'] ?? '1', '1'); ?>>
            <span class="label-text">ذخیره وضعیت و زمان پاسخ ارسال ایمیل</span>
        </div>
    </div>

    <div class="setting-row">
        <label for="email_test_subject">موضوع ایمیل تست</label>
        <input type="text" id="email_test_subject" name="email_test_subject" value="<?php echo esc_attr($s['email_test_subject'] ?? 'تست ایمیل EzLens'); ?>">
    </div>

    <div class="setting-row">
        <label for="email_reset_template">قالب ایمیل بازیابی (لینک)</label>
        <textarea id="email_reset_template" name="email_reset_template" rows="3"><?php echo esc_textarea($s['email_reset_template'] ?? 'سلام {name}، لینک بازیابی شما: {reset_url}'); ?></textarea>
        <span class="hint">متغیرها: {name} و {reset_url}</span>
    </div>

    <div class="setting-row">
        <label for="email_welcome_template">قالب ایمیل خوش‌آمدگویی</label>
        <textarea id="email_welcome_template" name="email_welcome_template" rows="3"><?php echo esc_textarea($s['email_welcome_template'] ?? 'به ایزی‌لنز خوش آمدید {name}!'); ?></textarea>
        <span class="hint">متغیر: {name}</span>
    </div>

    <div class="setting-row" style="border-top:1px solid #e2e8f0;padding-top:14px;margin-top:6px;">
        <label for="email_forgot_otp_template">قالب ایمیل فراموشی رمز (کد تأیید)</label>
        <textarea id="email_forgot_otp_template" name="email_forgot_otp_template" rows="4"><?php echo esc_textarea($s['email_forgot_otp_template'] ?? "سلام {name}\n\nشما درخواست بازیابی رمز عبور خود را در {site_name} ثبت کرده‌اید.\nکد تأیید شما: {code}\n\nاین کد تا {expiry} دقیقه اعتبار دارد."); ?></textarea>
        <span class="hint">متغیرها: {name}، {site_name}، {code}، {expiry}</span>
    </div>

    <div class="setting-row">
        <label for="email_verification_template">قالب ایمیل تأیید ثبت‌نام</label>
        <textarea id="email_verification_template" name="email_verification_template" rows="4"><?php echo esc_textarea($s['email_verification_template'] ?? "سلام {name}\n\nبرای تکمیل ثبت‌نام در {site_name}، کد تأیید زیر را وارد کنید:\n{code}\n\nاین کد تا {expiry} دقیقه اعتبار دارد."); ?></textarea>
        <span class="hint">متغیرها: {name}، {site_name}، {code}، {expiry}</span>
    </div>

    <div class="setting-row" style="border-top:1px solid #e2e8f0;padding-top:14px;margin-top:6px;">
        <label>تست ارسال</label>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <input type="email" id="ezlens-email-test-settings-target" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" placeholder="email@example.com">
            <button type="button" class="button button-primary" id="ezlens-email-test-settings">ارسال ایمیل تست</button>
            <span id="ezlens-email-test-settings-result"></span>
        </div>
        <span class="hint">برای تست اتصال واقعی، ابتدا SMTP را تنظیم کنید یا مطمئن شوید WordPress شما امکان ارسال wp_mail دارد.</span>
    </div>

    <div style="padding:10px 12px;background:#f0fdf4;border-radius:8px;font-size:12px;color:#065f46;border:1px solid #bbf7d0;margin-top:8px;line-height:1.9;">
        <strong>مثال:</strong> سلام {name}، کد تأیید شما: {code} (معتبر تا {expiry} دقیقه)
    </div>
</div>
