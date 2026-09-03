<?php
/**
 * تب تنظیمات SMTP - بدون اموجی
 */
?>
<div class="settings-tab-content">
    <h2>تنظیمات SMTP (ارسال ایمیل)</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">برای اطمینان از ارسال ایمیل، تنظیمات SMTP را کامل کنید.</p>

    <div class="setting-row">
        <label for="smtp_enabled">فعال‌سازی SMTP</label>
        <div class="toggle">
            <input type="checkbox" id="smtp_enabled" name="smtp_enabled" value="1" <?php checked($settings['smtp_enabled'] ?? '0', '1'); ?>>
            <span class="label-text">ارسال ایمیل از طریق SMTP</span>
        </div>
    </div>

    <div class="smtp-fields" style="<?php echo ($settings['smtp_enabled'] ?? '0') === '1' ? '' : 'display:none;'; ?>">
        <div class="setting-row">
            <label for="smtp_host">سرور SMTP</label>
            <input type="text" id="smtp_host" name="smtp_host" value="<?php echo esc_attr($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>" placeholder="smtp.gmail.com">
            <span class="hint">مثلاً smtp.gmail.com</span>
        </div>

        <div class="setting-row">
            <label for="smtp_port">پورت</label>
            <input type="number" id="smtp_port" name="smtp_port" value="<?php echo esc_attr($settings['smtp_port'] ?? 587); ?>" placeholder="587">
            <span class="hint">پورت‌های رایج: 587 (TLS)، 465 (SSL)</span>
        </div>

        <div class="setting-row">
            <label for="smtp_encryption">رمزنگاری</label>
            <select id="smtp_encryption" name="smtp_encryption">
                <option value="tls" <?php selected($settings['smtp_encryption'] ?? 'tls', 'tls'); ?>>TLS</option>
                <option value="ssl" <?php selected($settings['smtp_encryption'] ?? 'tls', 'ssl'); ?>>SSL</option>
                <option value="none" <?php selected($settings['smtp_encryption'] ?? 'tls', 'none'); ?>>بدون رمزنگاری</option>
            </select>
            <span class="hint">پیش‌فرض: TLS</span>
        </div>

        <div class="setting-row">
            <label for="smtp_username">نام کاربری</label>
            <input type="text" id="smtp_username" name="smtp_username" value="<?php echo esc_attr($settings['smtp_username'] ?? ''); ?>" placeholder="your@email.com">
            <span class="hint">معمولاً آدرس ایمیل کامل</span>
        </div>

        <div class="setting-row">
            <label for="smtp_password">رمز عبور</label>
            <input type="password" id="smtp_password" name="smtp_password" value="<?php echo esc_attr($settings['smtp_password'] ?? ''); ?>" placeholder="••••••••">
            <span class="hint">رمز عبور یا App Password (برای Gmail)</span>
        </div>

        <div class="setting-row">
            <label for="smtp_auth">احراز هویت</label>
            <div class="toggle">
                <input type="checkbox" id="smtp_auth" name="smtp_auth" value="1" <?php checked($settings['smtp_auth'] ?? '1', '1'); ?>>
                <span class="label-text">فعال (پیش‌فرض)</span>
            </div>
        </div>

        <div class="setting-row" style="border-top:1px solid #e2e8f0;padding-top:14px;margin-top:6px;flex-direction:column;align-items:flex-start;">
            <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;width:100%;">
                <label style="min-width:100px;font-weight:600;font-size:13px;color:#0f172a;">ایمیل تست:</label>
                <input type="email" id="smtp_test_email" value="<?php echo esc_attr(get_bloginfo('admin_email')); ?>" style="flex:1;min-width:150px;padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;font-family:inherit;font-size:13px;">
                <button type="button" id="smtp_test_send" class="button button-primary">ارسال تست</button>
                <span id="smtp_test_result" style="font-size:13px;font-weight:500;"></span>
            </div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">برای تست، یک ایمیل وارد کنید و روی «ارسال تست» کلیک کنید.</div>
        </div>
    </div>
</div>