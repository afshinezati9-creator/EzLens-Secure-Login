<?php if (!defined('ABSPATH')) exit; $s=$settings; $providers=['sms_ir'=>'SMS.ir','kavenegar'=>'کاوه نگار','custom'=>'سرویس سفارشی (HTTP API)']; ?>
<div class="settings-tab-content ezlens-messaging-settings">
<h2>تنظیمات پیام‌رسانی</h2><p class="hint">OTP ورود و ثبت‌نام کاملاً از Campaign جداست؛ تغییر سرویس Campaign روی کد تأیید مشتری اثر نمی‌گذارد.</p>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;">
<section class="card" style="padding:18px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;">
<h3>پیامک کد تأیید (OTP)</h3>
<div class="setting-row"><label>فعال‌سازی</label><input type="checkbox" name="otp_sms_enabled" value="1" <?php checked($s['otp_sms_enabled'],'1'); ?>></div>
<div class="setting-row"><label>سرویس‌دهنده</label><select name="otp_sms_provider" class="widefat"><?php foreach($providers as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($s['otp_sms_provider'],$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
<div class="setting-row"><label>API Key</label><input type="password" name="otp_sms_api_key" value="<?php echo esc_attr($s['otp_sms_api_key']); ?>" autocomplete="new-password"></div>
<div class="setting-row"><label>شماره خط</label><input type="text" name="otp_sms_line" value="<?php echo esc_attr($s['otp_sms_line']); ?>"></div>
<div class="setting-row"><label>Template / Lookup</label><input type="text" name="otp_sms_template" value="<?php echo esc_attr($s['otp_sms_template']); ?>"><span class="hint">SMS.ir: شناسه عددی قالب؛ کاوه نگار: نام قالب</span></div>
<div class="setting-row"><label>Endpoint سفارشی</label><input type="url" name="otp_sms_endpoint" value="<?php echo esc_attr($s['otp_sms_endpoint']); ?>"></div>
<div class="setting-row"><label>Token سفارشی</label><input type="password" name="otp_sms_token" value="<?php echo esc_attr($s['otp_sms_token']); ?>" autocomplete="new-password"></div>
<div class="setting-row"><input type="text" id="ezlens_otp_test_target" value="09123456789" placeholder="شماره تست"><button type="button" class="button button-primary ezlens-msg-test" data-channel="otp">ارسال تست OTP</button><span class="ezlens-msg-test-result"></span></div>
<div style="background:#f8fafc;padding:12px;border-radius:8px;font-size:12px;line-height:1.9;color:#475569;">اگر پیام OTP دیر می‌رسد، از همین بخش سرویس را تست می‌کنیم. زمان پاسخ API و پاسخ خام سرویس در لاگ ثبت می‌شود؛ بنابراین مشخص می‌شود تأخیر از سایت است یا ارائه‌دهنده.</div>
</section>
<section class="card" style="padding:18px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;">
<h3>پیام‌رسانی Campaign</h3>
<div class="setting-row"><label>فعال‌سازی SMS Campaign</label><input type="checkbox" name="campaign_sms_enabled" value="1" <?php checked($s['campaign_sms_enabled'],'1'); ?>></div>
<div class="setting-row"><label>سرویس SMS Campaign</label><select name="campaign_sms_provider" class="widefat"><?php foreach($providers as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($s['campaign_sms_provider'],$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
<div class="setting-row"><label>API Key</label><input type="password" name="campaign_sms_api_key" value="<?php echo esc_attr($s['campaign_sms_api_key']); ?>" autocomplete="new-password"></div>
<div class="setting-row"><label>شماره خط</label><input type="text" name="campaign_sms_line" value="<?php echo esc_attr($s['campaign_sms_line']); ?>"></div>
<div class="setting-row"><label>Template ID</label><input type="text" name="campaign_sms_template" value="<?php echo esc_attr($s['campaign_sms_template']); ?>"><span class="hint">برای SMS.ir قالب ارسال Campaign را جداگانه بسازید.</span></div>
<div class="setting-row"><label>Endpoint سفارشی</label><input type="url" name="campaign_sms_endpoint" value="<?php echo esc_attr($s['campaign_sms_endpoint']); ?>"></div>
<div class="setting-row"><label>Token سفارشی</label><input type="password" name="campaign_sms_token" value="<?php echo esc_attr($s['campaign_sms_token']); ?>" autocomplete="new-password"></div>
<hr>
<div class="setting-row"><label>فعال‌سازی Email Campaign</label><input type="checkbox" name="campaign_email_enabled" value="1" <?php checked($s['campaign_email_enabled'],'1'); ?>></div>
<div class="setting-row"><label>From Email</label><input type="email" name="campaign_email_from" value="<?php echo esc_attr($s['campaign_email_from']); ?>"></div>
<div class="setting-row"><label>نام فرستنده</label><input type="text" name="campaign_email_from_name" value="<?php echo esc_attr($s['campaign_email_from_name']); ?>"></div>
<div class="setting-row"><label>Batch Size</label><input type="number" name="campaign_batch_size" min="1" max="200" value="<?php echo esc_attr($s['campaign_batch_size']); ?>"></div>
<div class="setting-row"><label>فاصله ارسال (ms)</label><input type="number" name="campaign_delay_ms" min="0" max="5000" value="<?php echo esc_attr($s['campaign_delay_ms']); ?>"></div>
<div class="setting-row"><label>رهگیری باز شدن ایمیل</label><input type="checkbox" name="campaign_track_enabled" value="1" <?php checked($s['campaign_track_enabled'],'1'); ?>></div>
<div class="setting-row"><label>حداکثر مخاطب</label><input type="number" name="campaign_max_recipients" min="100" max="50000" value="<?php echo esc_attr($s['campaign_max_recipients']); ?>"></div>
<div class="setting-row"><input type="text" id="ezlens_campaign_test_target" value="09123456789" placeholder="شماره تست"><input type="text" id="ezlens_campaign_test_message" value="این پیام برای تست Campaign است." placeholder="متن تست"><button type="button" class="button button-primary ezlens-msg-test" data-channel="campaign">ارسال تست Campaign SMS</button><span class="ezlens-msg-test-result"></span></div>
<div class="setting-row"><input type="email" id="ezlens_email_test_target" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>"><button type="button" class="button" id="ezlens-email-test">ارسال تست ایمیل Campaign</button><span id="ezlens-email-test-result"></span></div>
</section>
</div>
</div>
