<?php
/**
 * تب تنظیمات امنیت - بدون اموجی
 */
?>
<div class="settings-tab-content">
    <h2>تنظیمات امنیت</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">تنظیمات امنیتی پلاگین</p>

    <div class="setting-row">
        <label for="enable_2fa">فعال‌سازی 2FA</label>
        <div class="toggle">
            <input type="checkbox" id="enable_2fa" name="enable_2fa" value="1" <?php checked($settings['enable_2fa'] ?? '0', '1'); ?>>
            <span class="label-text">احراز هویت دو مرحله‌ای</span>
        </div>
        <span class="hint">برای ورود مدیران (نسخه آینده)</span>
    </div>

    <div class="setting-row">
        <label for="block_invalid_ips">مسدودسازی IPهای نامعتبر</label>
        <div class="toggle">
            <input type="checkbox" id="block_invalid_ips" name="block_invalid_ips" value="1" <?php checked($settings['block_invalid_ips'] ?? '1', '1'); ?>>
            <span class="label-text">فعال</span>
        </div>
    </div>

    <div class="setting-row">
        <label for="allowed_ips">لیست IPهای مجاز</label>
        <input type="text" id="allowed_ips" name="allowed_ips" value="<?php echo esc_attr($settings['allowed_ips'] ?? ''); ?>" placeholder="192.168.1.1, 10.0.0.1">
        <span class="hint">جداسازی با کاما، خالی = همه</span>
    </div>

    <div class="setting-row">
        <label for="admin_max_attempts">محدودیت تلاش برای مدیر</label>
        <input type="number" id="admin_max_attempts" name="admin_max_attempts" value="<?php echo esc_attr($settings['admin_max_attempts'] ?? 3); ?>" min="2" max="10">
        <span class="hint">تعداد تلاش مجاز برای ورود مدیر</span>
    </div>

    <div class="setting-row">
        <label for="admin_login_warning">پیام هشدار ورود مدیر</label>
        <input type="text" id="admin_login_warning" name="admin_login_warning" value="<?php echo esc_attr($settings['admin_login_warning'] ?? 'آدرس‌های wp-admin و wp-login غیرفعال شده‌اند'); ?>" placeholder="متن هشدار">
    </div>
</div>
<div class="settings-tab-content" style="margin-top:16px;border-top:1px solid #e2e8f0;padding-top:16px;">
    <h3>حفاظت محتوای صفحات EzLens</h3>
    <p class="hint">فقط روی صفحات مرتبط با EzLens اعمال می‌شود و به پنل مدیریت یا WooCommerce آسیب نمی‌زند.</p>
    <div class="setting-row"><label>فعال‌سازی</label><input type="checkbox" name="frontend_protection_enabled" value="1" <?php checked($settings['frontend_protection_enabled'] ?? '0','1'); ?>></div>
    <div class="setting-row"><label>جلوگیری از Copy / Cut</label><input type="checkbox" name="frontend_disable_copy" value="1" <?php checked($settings['frontend_disable_copy'] ?? '0','1'); ?>></div>
    <div class="setting-row"><label>جلوگیری از راست‌کلیک</label><input type="checkbox" name="frontend_disable_context" value="1" <?php checked($settings['frontend_disable_context'] ?? '0','1'); ?>></div>
    <div class="setting-row"><label>جلوگیری از انتخاب متن</label><input type="checkbox" name="frontend_disable_selection" value="1" <?php checked($settings['frontend_disable_selection'] ?? '0','1'); ?>></div>
</div>
<div class="settings-tab-content" style="margin-top:16px;border-top:1px solid #e2e8f0;padding-top:16px;">
<h3>پشتیبانی و اعلان‌ها</h3>
<div class="setting-row"><label>اعلان ایمیلی پشتیبانی</label><input type="checkbox" name="support_email_notifications" value="1" <?php checked($settings['support_email_notifications'] ?? '1','1'); ?>></div>
<div class="setting-row"><label>اعلان مدیر</label><input type="checkbox" name="support_notify_admin_email" value="1" <?php checked($settings['support_notify_admin_email'] ?? '1','1'); ?>></div>
<div class="setting-row"><label>ایمیل مدیر</label><input type="email" name="support_admin_email" value="<?php echo esc_attr($settings['support_admin_email'] ?? ''); ?>" placeholder="خالی = ایمیل مدیریت وردپرس"></div>
<div class="setting-row"><label>حداکثر حجم فایل (MB)</label><input type="number" name="support_max_attachment_mb" value="<?php echo esc_attr($settings['support_max_attachment_mb'] ?? '5'); ?>" min="1" max="25"></div>
</div>
