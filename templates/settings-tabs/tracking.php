<?php
/**
 * تب تنظیمات رهگیری سفارش - بدون اموجی
 */
?>
<div class="settings-tab-content">
    <h2>تنظیمات رهگیری سفارش</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">لینک پایه برای پیگیری کد رهگیری سفارشات.</p>

    <div class="setting-row">
        <label for="tracking_base_url">لینک پایه پیگیری</label>
        <input type="text" id="tracking_base_url" name="tracking_base_url" 
               value="<?php echo esc_attr($settings['tracking_base_url'] ?? 'https://tracking.post.ir/?id='); ?>" 
               placeholder="https://tracking.post.ir/?id=">
        <span class="hint">کد رهگیری به انتهای این لینک اضافه می‌شود.</span>
    </div>

    <div style="padding:8px 12px;background:#ebf8ff;border-radius:6px;font-size:12px;color:#2a69ac;border:1px solid #bee3f8;margin-top:4px;">
        <strong>مثال:</strong> اگر لینک پایه <code>https://tracking.post.ir/?id=</code> باشد، کد رهگیری <code>12345</code> به لینک <code>https://tracking.post.ir/?id=12345</code> تبدیل می‌شود.
    </div>
</div>