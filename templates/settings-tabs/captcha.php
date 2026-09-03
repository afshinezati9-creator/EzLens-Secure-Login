<?php
/**
 * تب تنظیمات کپچا - بدون اموجی
 */
$captcha_type = $settings['captcha_type'] ?? 'math';
$is_recaptcha = (strpos($captcha_type, 'recaptcha') !== false);
?>
<div class="settings-tab-content">
    <h2>تنظیمات کپچا</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">تنظیمات مربوط به کد امنیتی</p>

    <div class="setting-row">
        <label for="captcha_type">نوع کپچا</label>
        <select id="captcha_type" name="captcha_type">
            <option value="math" <?php selected($captcha_type, 'math'); ?>>ریاضی (Math)</option>
            <option value="recaptcha_v2" <?php selected($captcha_type, 'recaptcha_v2'); ?>>Google reCAPTCHA v2</option>
            <option value="recaptcha_v3" <?php selected($captcha_type, 'recaptcha_v3'); ?>>Google reCAPTCHA v3</option>
            <option value="honeypot" <?php selected($captcha_type, 'honeypot'); ?>>هانی‌پات (Honeypot)</option>
        </select>
        <span class="hint">پیش‌فرض: ریاضی</span>
    </div>

    <div class="setting-row">
        <label for="captcha_for_otp_login">کپچا برای ورود با شماره موبایل</label>
        <div class="toggle">
            <input type="checkbox" id="captcha_for_otp_login" name="captcha_for_otp_login" value="1" <?php checked($settings['captcha_for_otp_login'] ?? '0', '1'); ?>>
            <span class="label-text">فعال‌سازی کپچا در OTP</span>
        </div>
        <span class="hint">در صورت غیرفعال بودن، کپچا در فرآیند OTP نمایش داده نمی‌شود</span>
    </div>

    <div class="google-captcha-fields" style="<?php echo $is_recaptcha ? '' : 'display:none;'; ?>">
        <div class="setting-row">
            <label for="captcha_site_key">Site Key</label>
            <input type="text" id="captcha_site_key" name="captcha_site_key" value="<?php echo esc_attr($settings['captcha_site_key'] ?? ''); ?>" placeholder="کلید Site Key را وارد کنید">
            <span class="hint">از کنسول Google reCAPTCHA دریافت کنید</span>
        </div>

        <div class="setting-row">
            <label for="captcha_secret_key">Secret Key</label>
            <input type="text" id="captcha_secret_key" name="captcha_secret_key" value="<?php echo esc_attr($settings['captcha_secret_key'] ?? ''); ?>" placeholder="کلید Secret Key را وارد کنید">
            <span class="hint">از کنسول Google reCAPTCHA دریافت کنید</span>
        </div>

        <div class="setting-row" style="border-bottom:none;padding-bottom:0;">
            <div style="flex:1;padding:8px 12px;background:#ebf8ff;border-radius:6px;font-size:13px;color:#2a69ac;border:1px solid #bee3f8;">
                <strong>نحوه دریافت کلیدها:</strong>
                <ol style="margin:4px 0 0 20px;">
                    <li>به <a href="https://www.google.com/recaptcha/admin" target="_blank">کنسول Google reCAPTCHA</a> بروید.</li>
                    <li>یک پروژه جدید ایجاد کنید و نوع reCAPTCHA را انتخاب کنید.</li>
                    <li>دامنه سایت خود را اضافه کنید (مثلاً <code>localhost</code> یا <code>ezlens.ir</code>).</li>
                    <li>کلیدهای <strong>Site Key</strong> و <strong>Secret Key</strong> را کپی کنید.</li>
                </ol>
            </div>
        </div>
    </div>
</div>