<?php
/**
 * تب تنظیمات ظاهر - بدون اموجی
 */
?>
<div class="settings-tab-content">
    <h2>تنظیمات ظاهر</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">تنظیمات ظاهری صفحات پلاگین</p>

    <div class="setting-row">
        <label for="primary_color">رنگ اصلی برند</label>
        <input type="text" id="primary_color" name="primary_color" value="<?php echo esc_attr($settings['primary_color'] ?? '#2b6cb0'); ?>" placeholder="#2b6cb0">
        <span class="hint">کد HEX</span>
    </div>

    <div class="setting-row">
        <label for="button_color">رنگ دکمه‌ها</label>
        <input type="text" id="button_color" name="button_color" value="<?php echo esc_attr($settings['button_color'] ?? '#2b6cb0'); ?>" placeholder="#2b6cb0">
        <span class="hint">کد HEX</span>
    </div>

    <div class="setting-row">
        <label for="bg_color">رنگ پس‌زمینه صفحات</label>
        <input type="text" id="bg_color" name="bg_color" value="<?php echo esc_attr($settings['bg_color'] ?? '#0f172a'); ?>" placeholder="#0f172a">
        <span class="hint">کد HEX</span>
    </div>

    <div class="setting-row">
        <label for="logo_url">لوگو</label>
        <input type="text" id="logo_url" name="logo_url" value="<?php echo esc_url($settings['logo_url'] ?? ''); ?>" placeholder="آدرس تصویر لوگو">
        <span class="hint">مسیر فایل تصویر</span>
    </div>

    <div class="setting-row">
        <label for="font_family">فونت فارسی</label>
        <select id="font_family" name="font_family">
            <option value="IRANYekan" <?php selected($settings['font_family'] ?? 'IRANYekan', 'IRANYekan'); ?>>ایران یکان</option>
            <option value="Vazirmatn" <?php selected($settings['font_family'] ?? '', 'Vazirmatn'); ?>>وزیرمتن</option>
            <option value="Shabnam" <?php selected($settings['font_family'] ?? '', 'Shabnam'); ?>>شبنم</option>
            <option value="custom" <?php selected($settings['font_family'] ?? '', 'custom'); ?>>سفارشی (CSS)</option>
        </select>
        <span class="hint">فونت اصلی سایت</span>
    </div>

    <!-- ===== بخش عنوان و زیرنویس صفحه ورود ===== -->
    <div class="setting-row" style="border-top:2px solid #e2e8f0;padding-top:16px;margin-top:12px;">
        <label for="page_title_customer-login">عنوان صفحه ورود مشتری</label>
        <input type="text" id="page_title_customer-login" name="page_title_customer-login" 
               value="<?php echo esc_attr(EzLens_Auth_Settings::get_page_setting('customer-login', 'title')); ?>" 
               placeholder="خالی = نام سایت">
        <span class="hint">اگر خالی باشد، نام سایت نمایش داده می‌شود.</span>
    </div>
    <div class="setting-row" style="border-bottom:none;padding-bottom:0;">
        <label for="page_subtitle_customer-login">زیرنویس صفحه ورود مشتری</label>
        <input type="text" id="page_subtitle_customer-login" name="page_subtitle_customer-login" 
               value="<?php echo esc_attr(EzLens_Auth_Settings::get_page_setting('customer-login', 'subtitle')); ?>" 
               placeholder="خالی = نمایش داده نشود">
        <span class="hint">اگر خالی باشد، زیرنویس نمایش داده نمی‌شود.</span>
    </div>
</div>