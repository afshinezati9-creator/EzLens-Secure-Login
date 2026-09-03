<?php
/**
 * تب فعال‌سازی صفحات - بدون اموجی
 */
$pages = [
    'customer-login' => 'ورود مشتری',
    'lost-password'  => 'فراموشی رمز',
    'user-panel'     => 'پنل کاربری',
    'admin-login'    => 'ورود مدیر',
];
$shortcode_map = [
    'customer-login' => '[minimal_auth]',
    'lost-password'  => '[ezlens_lost_password]',
    'user-panel'     => '[modern_user_panel]',
    'admin-login'    => '[admin_login_page]',
];
?>
<div class="settings-tab-content">
    <h2>فعال‌سازی صفحات</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">در صورت غیرفعال‌سازی هر صفحه، کاربران به صفحه ورود وردپرس هدایت می‌شوند.</p>

    <?php foreach ($pages as $key => $label):
        $option_key = 'enable_' . str_replace('-', '_', $key);
        $enabled = $settings[$option_key] ?? '1';
    ?>
    <div class="setting-row" style="border-bottom:1px solid #e2e8f0;padding:8px 0;">
        <label style="min-width:140px;"><?php echo esc_html($label); ?></label>
        <div class="toggle">
            <input type="checkbox" name="<?php echo esc_attr($option_key); ?>" value="1" <?php checked($enabled, '1'); ?>>
            <span class="label-text">فعال</span>
        </div>
        <span class="hint" style="margin-right:auto;">
            شورت‌کد: <code style="background:#edf2f7;padding:1px 8px;border-radius:4px;direction:ltr;">
                <?php echo esc_html($shortcode_map[$key] ?? ''); ?>
            </code>
        </span>
    </div>
    <?php endforeach; ?>
</div>