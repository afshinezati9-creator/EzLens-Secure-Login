# Product Options — فاز ۰ (زیرساخت)

**تاریخ:** 2026-09-08  
**هدف:** مهاجرت معماری، meta چند‌اسلات، helperها، امنیت پایه — بدون تغییر بزرگ UX

## چه چیزی تغییر کرد

### ۱. Bootstrap یکپارچه (`class-install.php`)
- لود صریح کلاس‌های `src/` (Repository + Services + ProductSlotsService)
- لود `includes/class-helpers.php`
- **تنها نقطه** ساخت جدول `ezlens_option_templates` با چک case-insensitive (`information_schema`)
- حذف وابستگی به option قدیمی `ezlens_product_options_tables_created`
- نسخه DB: `ezlens_product_options_db_version = 2.0.0`
- boot کلاس‌های runtime (manager, ajax, field-renderer, pricing, woocommerce, order-display, meta-box)

### ۲. Facade برای Template_Manager
- دیگر SQL مستقیم / `create_table` ندارد
- همه CRUD به `TemplateService` + `TemplateRepository` delegate می‌شود
- متد جدید: `get_templates_for_product()` برای چند پالت (فاز ۲)
- API عمومی قبلی حفظ شده → AJAX / MetaBox / Pricing بدون شکست

### ۳. `ProductSlotsService` (meta جدید)
- کلید: `_ezlens_option_slots` (JSON)
- مهاجرت خودکار از `_ezlens_option_template_id` هنگام خواندن
- همگام‌سازی legacy key هنگام ذخیره (برای سازگاری با کد قدیم)
- placement / display / order در هر slot آماده فاز ۱

### ۴. `TemplateRepository` سخت‌تر
- `table_exists()` case-insensitive
- `list()` با فیلترهای structured + prepare
- `count_connected_products` هم legacy و هم slots را می‌شمارد
- `set_product_template` از طریق slots می‌نویسد

### ۵. Helpers
- اعداد فارسی / غربی
- `icon_url` / `icon_svg` از `assets/icons` (+ modern)
- قرارداد `_code_*` : فیلدهای داخلی که باید از UI/validate/price رد شوند
- whitelist placement و display mode

### ۶. آپلود AJAX
- rate limit: حداکثر ۱۰ آپلود / ۱۰ دقیقه برای هر IP+user
- mime / size / is_uploaded_file از قبل برقرار بود

## نصب روی سایت

1. پوشه `modules/product-options/` را با محتویات این پکیج جایگزین کنید (یا فایل‌های تغییر‌یافته را کپی کنید).
2. در فایل اصلی `ezlens-secure-login.php` لود را ساده کنید (پیشنهاد پایین).
3. یک‌بار صفحه ادمین یا فرانت را باز کنید تا `maybe_install` جدول را تأیید کند.

### پیشنهاد تغییر در `ezlens-secure-login.php`

در آرایه `$core_files` فقط این خط برای Product Options کافی است:

```php
// Modules - Product Options (bootstrap loads the rest)
EZLAUTH_MODULES_DIR . 'product-options/class-install.php',
```

خطوط تکراری زیر را حذف کنید تا double-load و نام کلاس اشتباه رخ ندهد:

```php
// حذف شود:
EZLAUTH_MODULES_DIR . 'product-options/class-template-manager.php',
EZLAUTH_MODULES_DIR . 'product-options/class-ajax-handler.php',
EZLAUTH_MODULES_DIR . 'product-options/admin/meta-box.php',
EZLAUTH_MODULES_DIR . 'product-options/class-field-renderer.php',
EZLAUTH_MODULES_DIR . 'product-options/class-order-display.php',
```

و این بلوک boot با نام کلاس اشتباه را حذف/اصلاح کنید:

```php
// این کلاس‌ها با این نام‌ها وجود ندارند — bootstrap خودش get_instance می‌زند
if ( class_exists( 'EzLens_Product_Options' ) ) { ... }
if ( class_exists( 'EzLens_Template_Manager' ) ) { ... }
if ( class_exists( 'EzLens_Product_Options_Ajax_Handler' ) ) { ... }
if ( class_exists( 'EzLens_Field_Renderer' ) ) { ... }
if ( class_exists( 'EzLens_Order_Display' ) ) { ... }
```

### فعال‌سازی install روی activate (اختیاری)

داخل `EzLens_Auth::activate()`:

```php
if ( class_exists( 'EzLens_Product_Options_Install' ) ) {
    EzLens_Product_Options_Install::install();
}
```

(بعد از require شدن `class-install.php`)

## آنچه عمداً در فاز ۰ نیست
- UI متاباکس چند اسلات (فاز ۱)
- placement / accordion / modal در فرانت (فاز ۲)
- Dropzone آپلود (فاز ۳)
- شکستن فایل‌های ۲۰۰۰ خطی ادیتور (فاز ۵)

## تست سریع بعد از استقرار
1. لیست پالت‌ها در ادمین باز شود و CRUD کار کند
2. محصولی که قبلاً یک قالب داشت هنوز فیلدها را روی صفحه محصول نشان دهد
3. meta محصول: بعد از اولین load باید `_ezlens_option_slots` ساخته شده باشد
4. آپلود فایل روی محصول (در صورت وجود فیلد upload) با محدودیت حجم/نوع

