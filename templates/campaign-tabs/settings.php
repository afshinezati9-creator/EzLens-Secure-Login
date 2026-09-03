<?php if (!defined('ABSPATH')) exit; $s=EzLens_Auth_Settings::get_all(); ?>
<div class="campaign-tab-content">
<h2>تنظیمات کمپین</h2><p style="color:#64748b;font-size:13px">ارسال Campaign از OTP مستقل است و از تنظیمات «پیام‌رسانی» استفاده می‌کند.</p>
<div class="setting-row"><label>حداکثر مخاطب</label><input type="number" name="campaign_max_recipients" value="<?php echo esc_attr($s['campaign_max_recipients']); ?>" min="100" max="50000"></div>
<div class="setting-row"><label>Batch Size</label><input type="number" name="campaign_batch_size" value="<?php echo esc_attr($s['campaign_batch_size']); ?>" min="1" max="200"></div>
<div class="setting-row"><label>فاصله بین ارسال‌ها (ms)</label><input type="number" name="campaign_delay_ms" value="<?php echo esc_attr($s['campaign_delay_ms']); ?>" min="0" max="5000"></div>
<div class="setting-row"><label>رهگیری باز شدن ایمیل</label><input type="checkbox" name="campaign_track_enabled" value="1" <?php checked($s['campaign_track_enabled'],'1'); ?>></div>
<div class="setting-row"><label>لغو اشتراک ایمیل</label><input type="checkbox" name="campaign_unsubscribe_enabled" value="1" <?php checked($s['campaign_unsubscribe_enabled'],'1'); ?>></div>
<p class="hint">برای فعال‌سازی سرویس‌ها، API و قالب‌ها به تب «پیام‌رسانی» بروید و با «ذخیره همه تنظیمات» ذخیره کنید.</p>
</div>
