<?php if (!defined('ABSPATH')) exit; $s=$settings; ?>
<div class="settings-tab-content">
<h2>اتصال و API</h2>
<p class="hint">این بخش برای اتصال آینده نرم‌افزار مدیران EzLens و اپ موبایل طراحی شده است. هیچ کلید یا اطلاعات حساس در پاسخ API عمومی برگردانده نمی‌شود.</p>
<section class="card" style="padding:18px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;margin-bottom:18px;">
<h3>Webhook خروجی</h3>
<div class="setting-row"><label>فعال‌سازی</label><input type="checkbox" name="webhook_enabled" value="1" <?php checked($s['webhook_enabled'],'1'); ?>></div>
<div class="setting-row"><label>URL مقصد</label><input type="url" name="webhook_url" value="<?php echo esc_attr($s['webhook_url']); ?>" placeholder="https://manager.example/api/webhook"></div>
<div class="setting-row"><label>Secret</label><input type="password" name="webhook_secret" value="<?php echo esc_attr($s['webhook_secret']); ?>" autocomplete="new-password"></div>
<p class="hint">رویدادهای فعلی: campaign.sent و support.ticket_created. امضای HMAC-SHA256 در هدر ارسال می‌شود.</p>
</section>
<section class="card" style="padding:18px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;">
<h3>API اپلیکیشن EzLens</h3>
<div class="setting-row"><label>فعال بودن API اصلی</label><input type="checkbox" name="enable_api" value="1" <?php checked($s['enable_api'],'1'); ?>></div>
<div class="setting-row"><label>محدودیت درخواست در دقیقه</label><input type="number" min="10" max="600" name="api_rate_limit" value="<?php echo esc_attr($s['api_rate_limit']); ?>"></div>
<div class="setting-row"><label>اعتبار توکن اپ (روز)</label><input type="number" min="1" max="365" name="app_token_days" value="<?php echo esc_attr($s['app_token_days']); ?>"></div>
<div class="setting-row"><label>OTP اپ</label><input type="checkbox" name="app_otp_enabled" value="1" <?php checked($s['app_otp_enabled'],'1'); ?>></div>
<div style="background:#f8fafc;padding:12px;border-radius:8px;line-height:1.9">مسیر پایه: <code>/wp-json/ezlens-app/v1/</code><br>Endpointهای اصلی: <code>/me</code>، <code>/orders</code>، <code>/tickets</code>، <code>/notifications</code>، <code>/manager/customers</code>، <code>/manager/orders</code><br>احراز اپ با Bearer Token انجام می‌شود و توکن خام در دیتابیس ذخیره نمی‌شود.</div>
</section>
</div>
