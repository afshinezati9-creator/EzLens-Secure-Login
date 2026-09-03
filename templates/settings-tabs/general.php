<?php
/**
 * تب تنظیمات عمومی – بدون اموجی
 */
?>
<div class="settings-tab-content">
    <h2>تنظیمات اصلی</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">تنظیمات پایه و اصلی پلاگین</p>

    <div class="setting-row">
        <label for="admin_login_slug">آدرس ورود مدیر</label>
        <input type="text" id="admin_login_slug" name="admin_login_slug" value="<?php echo esc_attr($settings['admin_login_slug'] ?? 'admin-secret'); ?>" placeholder="admin-secret">
        <span class="hint">پیش‌فرض: <code>admin-secret</code></span>
    </div>

    <div class="setting-row">
        <label for="customer_login_slug">آدرس ورود مشتری</label>
        <input type="text" id="customer_login_slug" name="customer_login_slug" value="<?php echo esc_attr($settings['customer_login_slug'] ?? 'login'); ?>" placeholder="login">
        <span class="hint">پیش‌فرض: <code>login</code></span>
    </div>

    <div class="setting-row">
        <label for="max_attempts">تعداد تلاش مجاز</label>
        <input type="number" id="max_attempts" name="max_attempts" value="<?php echo esc_attr($settings['max_attempts'] ?? 5); ?>" min="3" max="20">
        <span class="hint">پس از این تعداد، کاربر قفل می‌شود</span>
    </div>

    <div class="setting-row">
        <label for="lockout_minutes">زمان قفل (دقیقه)</label>
        <input type="number" id="lockout_minutes" name="lockout_minutes" value="<?php echo esc_attr($settings['lockout_minutes'] ?? 15); ?>" min="5" max="60">
        <span class="hint">مدت قفل پس از تلاش‌های ناموفق</span>
    </div>

    <div class="setting-row">
        <label for="enable_logging">فعال‌سازی لاگ‌گیری</label>
        <div class="toggle">
            <input type="checkbox" id="enable_logging" name="enable_logging" value="1" <?php checked($settings['enable_logging'] ?? '1', '1'); ?>>
            <span class="label-text">ثبت ورود/خروج کاربران</span>
        </div>
    </div>

    <div class="setting-row">
        <label for="log_retention_days">حذف خودکار لاگ‌ها (روز)</label>
        <input type="number" id="log_retention_days" name="log_retention_days" value="<?php echo esc_attr($settings['log_retention_days'] ?? 30); ?>" min="7" max="365">
        <span class="hint">پس از این مدت، لاگ‌ها پاک می‌شوند</span>
    </div>
</div>