<?php
/**
 * تب روش‌های ورود – فعال/غیرفعال‌سازی مستقل (نسخه نهایی)
 * @version 2.6.0
 */
$settings = EzLens_Auth_Settings::get_all();
?>
<div class="settings-tab-content">
    <h2>روش‌های ورود و ثبت‌نام</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">
        در صورت غیرفعال‌سازی هر روش، آن گزینه از صفحه ورود/ثبت‌نام حذف می‌شود.
    </p>

    <div class="setting-row">
        <label for="enable_otp_login">ورود با شماره موبایل (OTP)</label>
        <div class="toggle">
            <input type="checkbox" id="enable_otp_login" name="enable_otp_login" value="1" 
                   <?php checked($settings['enable_otp_login'] ?? '1', '1'); ?>>
            <span class="label-text">فعال</span>
        </div>
        <span class="hint">کاربر با شماره موبایل و کد تأیید وارد می‌شود.</span>
    </div>

    <div class="setting-row">
        <label for="enable_manual_login">ورود دستی (شماره موبایل و رمز)</label>
        <div class="toggle">
            <input type="checkbox" id="enable_manual_login" name="enable_manual_login" value="1" 
                   <?php checked($settings['enable_manual_login'] ?? '1', '1'); ?>>
            <span class="label-text">فعال</span>
        </div>
        <span class="hint">کاربر با شماره موبایل و رمز عبور وارد می‌شود.</span>
    </div>

    <div class="setting-row" style="border-bottom:none;padding-bottom:0;">
        <label for="enable_registration">ثبت‌نام</label>
        <div class="toggle">
            <input type="checkbox" id="enable_registration" name="enable_registration" value="1" 
                   <?php checked($settings['enable_registration'] ?? '1', '1'); ?>>
            <span class="label-text">فعال</span>
        </div>
        <span class="hint">کاربران جدید می‌توانند در سایت ثبت‌نام کنند.</span>
    </div>

    <div style="padding:10px 14px;background:#ebf8ff;border-radius:6px;font-size:13px;color:#2a69ac;border:1px solid #bee3f8;margin-top:12px;">
        <strong>نکته:</strong> اگر هر سه روش غیرفعال شوند، پیام «هیچ روش ورودی فعال نیست» در صفحه ورود نمایش داده می‌شود.
    </div>
</div>