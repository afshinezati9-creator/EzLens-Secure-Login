<?php
/**
 * تب تنظیمات OTP – بدون اموجی
 */
?>
<div class="settings-tab-content">
    <h2>تنظیمات کد تأیید (OTP)</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">تنظیمات مربوط به کدهای یکبارمصرف</p>

    <div class="setting-row">
        <label for="otp_expiry_minutes">زمان انقضای کد (دقیقه)</label>
        <input type="number" id="otp_expiry_minutes" name="otp_expiry_minutes" value="<?php echo esc_attr($settings['otp_expiry_minutes'] ?? 2); ?>" min="1" max="10">
        <span class="hint">مدت اعتبار کد تأیید</span>
    </div>

    <div class="setting-row">
        <label for="otp_max_attempts">تعداد تلاش مجاز</label>
        <input type="number" id="otp_max_attempts" name="otp_max_attempts" value="<?php echo esc_attr($settings['otp_max_attempts'] ?? 5); ?>" min="3" max="10">
        <span class="hint">پس از این تعداد، کد باطل می‌شود</span>
    </div>

    <div class="setting-row">
        <label for="otp_request_limit">محدودیت درخواست (در دقیقه)</label>
        <input type="number" id="otp_request_limit" name="otp_request_limit" value="<?php echo esc_attr($settings['otp_request_limit'] ?? 3); ?>" min="1" max="5">
        <span class="hint">تعداد دفعات درخواست کد در هر دقیقه</span>
    </div>
</div>