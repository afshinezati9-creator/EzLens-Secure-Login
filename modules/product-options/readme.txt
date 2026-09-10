## 📘 مستندات فنی ماژول ویژگی‌های محصول (Product Options Module)

**نسخه:** 2.0.0  
**تاریخ:** ۱۴۰۴/۰۶/۱۴  
**نویسنده:** تیم توسعه EzLens  
**وضعیت:** پایدار – قابل توسعه

---

## 🧭 هدف کلی ماژول

ماژول **ویژگی‌های محصول (Product Options)** یک لایه‌ی مدیریتی و نمایشی بر روی ووکامرس است که به مدیر فروشگاه اجازه می‌دهد **پالت‌های سفارشی از فیلدها** را تعریف کرده و آن‌ها را به محصولات خاص متصل کند. مشتری در صفحه‌ی محصول، فیلدهای مرتبط را مشاهده کرده، اطلاعات موردنیاز (متن، انتخاب، آپلود فایل، رنگ و ...) را وارد می‌کند و این اطلاعات در طول مسیر خرید (سبد خرید، تسویه‌حساب، سفارش، پنل کاربری و ادمین) حفظ و نمایش داده می‌شوند.

این ماژول با رویکردی **ماژولار، لایه‌ای و کاملاً سازگار با معماری EzLens** توسعه یافته است و از الگوهای **Repository**، **Service** و **Event-Driven** برای جداسازی مسئولیت‌ها استفاده می‌کند.

---

## 🧱 معماری و هسته‌ی فنی

### ۱. لایه‌های معماری

| لایه | مسیر | شرح |
|------|------|------|
| **لایه داده (Repository)** | `src/Repositories/` | ارتباط با دیتابیس و کوئری‌نویسی |
| **لایه سرویس (Service)** | `src/Services/` | منطق کسب‌وکار، اعتبارسنجی، پیش‌پردازش |
| **لایه کنترلر (Handler)** | `modules/product-options/` | مدیریت درخواست‌ها (AJAX، هوک‌ها) |
| **لایه نمایش (UI)** | `admin/pages/` و `frontend/` | رندر صفحات و فیلدها |
| **لایه یکپارچه‌سازی (Integration)** | `class-woocommerce-integration.php` | اتصال به ووکامرس از طریق هوک‌ها |

### ۲. ساختار دیتابیس

| نام جدول | شرح |
|----------|------|
| `wp_ezlens_option_templates` | ذخیره‌ی پالت‌های ساخته‌شده توسط مدیر. شامل `title`, `description`, `fields` (JSON), `status`, `created_at`, `updated_at`. |
| `wp_postmeta` | برای اتصال پالت به محصول از کلید `_ezlens_option_template_id` استفاده می‌شود. |

### ۳. جریان داده (Data Flow)

```
مدیر → ایجاد پالت در داشبورد
     ↓
TemplateService → TemplateRepository → ذخیره در دیتابیس
     ↓
اتصال پالت به محصول (از طریق postmeta)
     ↓
مشتری → صفحه محصول → FieldRenderer → نمایش فیلدها
     ↓
انتخاب/ورود اطلاعات → افزودن به سبد خرید
     ↓
WooCommerce Integration → ذخیره‌سازی در `cart_item_data`
     ↓
تبدیل به سفارش → ذخیره در `order_item_meta`
     ↓
OrderDisplay → نمایش در پنل کاربری، ادمین و ایمیل‌ها
```

---

## 📂 ساختار فایل‌های ماژول (نسخه ۲.۰.۰)

```
modules/product-options/
│
├── class-install.php                      # ایجاد جدول دیتابیس (یک بار)
├── class-template-manager.php             # (منسوخ) مدیریت پالت‌ها – به TemplateService منتقل شده
├── class-ajax-handler.php                 # هندلرهای AJAX (ذخیره، کپی، حذف، آپلود، پیش‌نمایش)
├── class-field-renderer.php               # رندر فیلدها در صفحه محصول + قیمت‌گذاری پویا
├── class-order-display.php                # نمایش ویژگی‌ها در پنل کاربری، ادمین و ایمیل‌ها
├── class-pricing-engine.php               # موتور محاسبه قیمت پویا بر اساس انتخاب‌ها
├── class-woocommerce-integration.php      # یکپارچه‌سازی با ووکامرس (هوک‌ها، فیلترها، ذخیره‌سازی)
├── readme.txt                             # اطلاعات اولیه ماژول
│
├── admin/
│   ├── class-field-renderer.php           # (تکراری – احتمالاً منسوخ) یک نسخه از رندر فیلدها برای ادمین
│   ├── class-preset-library-page.php      # صفحه کتابخانه قالب‌های آماده (Presets)
│   ├── meta-box.php                       # متاباکس انتخاب پالت در صفحه ویرایش محصول
│   │
│   └── pages/
│       ├── template-list.php              # لیست پالت‌ها (با فیلتر، صفحه‌بندی، حذف/کپی گروهی)
│       ├── template-editor.php            # بارگذار اصلی ویرایشگر (۳ ستون)
│       ├── template-editor-builder.php    # سازنده بصری (Visual Builder)
│       ├── template-editor-code.php       # ویرایشگر کد یکپارچه (HTML/CSS/JS)
│       ├── template-editor-preview.php    # پیش‌نمایش زنده (وسط صفحه)
│       ├── template-editor-settings.php   # تنظیمات CSS اختصاصی و تم‌ها
│       └── template-form.php              # (نمونه فرم تست – برای نمایش یا توسعه)
│
├── src/                                   # لایه‌های سرویس و ریپازیتوری (جدید)
│   ├── Repositories/
│   │   └── TemplateRepository.php         # کوئری‌های دیتابیس برای پالت‌ها
│   │
│   └── Services/
│       ├── TemplateService.php            # منطق کسب‌وکار پالت‌ها (CRUD، اعتبارسنجی، کش)
│       ├── FieldSchemaValidator.php       # اعتبارسنجی ساختار JSON فیلدها
│       ├── ConditionEvaluator.php         # ارزیابی شرایط شرطی (Conditional Logic)
│       └── PresetLibrary.php              # کتابخانه قالب‌های آماده (Presets) برای شروع سریع
│
└── frontend/
    ├── assets/
    │   ├── css/
    │   │   └── product-options-frontend.css  # استایل‌های صفحات محصول و سبد خرید
    │   └── js/
    │       ├── product-options.js            # منطق قیمت‌گذاری پویا، اعتبارسنجی، آپلود
    │       └── upload-progress.js           # (اختیاری) نوار پیشرفت آپلود
    └── (هیچ قالب جداگانه‌ای نیاز نیست؛ همه فیلدها توسط کلاس FieldRenderer رندر می‌شوند)
```

---

## 🔧 شرح کلاس‌های اصلی (نسخه ۲.۰.۰)

### لایه Repository

#### `EzLens_Product_Options_Repositories_TemplateRepository`
- **مسئولیت:** انجام عملیات دیتابیسی مربوط به پالت‌ها (جدا از منطق کسب‌وکار)
- **متدهای کلیدی:**
  - `find($id)` – دریافت یک پالت با شناسه
  - `findAll($filters, $pagination)` – دریافت لیست با فیلتر و صفحه‌بندی
  - `create($data)` – ثبت پالت جدید
  - `update($id, $data)` – به‌روزرسانی پالت
  - `delete($id)` – حذف فیزیکی پالت
  - `countConnectedProducts($id)` – تعداد محصولات متصل به پالت

---

### لایه Services

#### `EzLens_Product_Options_Services_TemplateService`
- **مسئولیت:** منطق کسب‌وکار پالت‌ها (اعتبارسنجی، پیش‌پردازش، کش، اتصال به محصول)
- **متدهای کلیدی:**
  - `createTemplate($data)` – ایجاد پالت با اعتبارسنجی کامل
  - `updateTemplate($id, $data)` – ویرایش پالت با بررسی وابستگی‌ها
  - `getTemplate($id)` – دریافت پالت با کش
  - `getTemplates($args)` – دریافت لیست با فیلتر
  - `deleteTemplate($id)` – حذف با بررسی محصولات متصل
  - `duplicateTemplate($id)` – کپی پالت و فیلدها
  - `attachToProduct($product_id, $template_id)` – اتصال پالت به محصول
  - `detachFromProduct($product_id)` – جدا کردن پالت از محصول
  - `getTemplateForProduct($product_id)` – دریافت پالت متصل به محصول

#### `EzLens_Product_Options_Services_FieldSchemaValidator`
- **مسئولیت:** اعتبارسنجی ساختار JSON هر فیلد قبل از ذخیره‌سازی
- **متدهای کلیدی:**
  - `validate($fields)` – بررسی صحت تمام فیلدها
  - `validateField($key, $field)` – بررسی یک فیلد بر اساس نوع آن
  - `getErrors()` – دریافت لیست خطاهای اعتبارسنجی

#### `EzLens_Product_Options_Services_ConditionEvaluator`
- **مسئولیت:** ارزیابی شرایط شرطی برای نمایش/مخفی کردن فیلدها
- **متدهای کلیدی:**
  - `evaluate($condition, $values)` – بررسی یک شرط بر اساس مقادیر ورودی
  - `shouldRender($field, $values)` – تصمیم‌گیری برای رندر یک فیلد

#### `EzLens_Product_Options_Services_PresetLibrary`
- **مسئولیت:** ارائه قالب‌های آماده برای شروع سریع (لنز، عینک، عینک آفتابی و ...)
- **متدهای کلیدی:**
  - `getPresets()` – دریافت لیست قالب‌های آماده
  - `getPreset($slug)` – دریافت یک قالب خاص
  - `applyPreset($slug, $template_data)` – اعمال قالب روی داده‌های پالت

---

### لایه کنترلر (Handlers)

#### `EzLens_Product_Options_Ajax`
- **مسئولیت:** پردازش درخواست‌های AJAX از صفحه‌ی ویرایشگر و متاباکس
- **متدهای کلیدی:**
  - `save_template()` – ذخیره‌سازی پالت (با پشتیبانی از فیلدهای بصری و کدها)
  - `duplicate_template()` – کپی پالت
  - `delete_template()` – حذف پالت (با تأیید)
  - `get_template_preview()` – بارگذاری پیش‌نمایش فیلدها در متاباکس
  - `upload_file()` – آپلود فایل با نوار پیشرفت

#### `EzLens_Product_Options_FieldRenderer`
- **مسئولیت:** رندر فیلدها در صفحه محصول، قیمت‌گذاری پویا
- **متدهای کلیدی:**
  - `render_fields()` – نقطه‌ی ورود (هوک `woocommerce_before_add_to_cart_button`)
  - `render_field($key, $field)` – رندر یک فیلد بر اساس نوع آن

#### `EzLens_Product_Options_WooCommerce_Integration`
- **مسئولیت:** یکپارچه‌سازی با ووکامرس (ذخیره‌سازی در سبد خرید، سفارش، نمایش)
- **متدهای کلیدی:**
  - `add_cart_item_data()` – افزودن اطلاعات به سبد خرید
  - `display_cart_item_data()` – نمایش اطلاعات در سبد خرید
  - `add_order_item_meta()` – ذخیره در سفارش
  - `display_order_item_meta()` – نمایش در پنل کاربری و ادمین
  - `calculate_cart_item_price()` – محاسبه قیمت نهایی آیتم سبد خرید

#### `EzLens_Product_Options_OrderDisplay`
- **مسئولیت:** نمایش ویژگی‌ها در پنل کاربری، ادمین و ایمیل‌ها
- **متدهای کلیدی:**
  - `display_in_user_panel()` – نمایش در سفارش‌های مشتری
  - `display_in_admin()` – نمایش در صفحه ویرایش سفارش (ادمین)
  - `display_in_emails()` – نمایش در ایمیل‌های تأیید سفارش
  - `add_order_meta_box()` – افزودن متاباکس در صفحه سفارش ادمین

---

## 🧩 ساختار داده‌های فیلد (JSON Schema)

هر فیلد در پالت به‌صورت یک آرایه‌ی JSON با کلیدهای زیر ذخیره می‌شود:

```json
{
    "type": "text|select|radio|checkbox|upload|color|date|time|number|heading|divider|spacer|group|html|image_select",
    "label": "عنوان نمایشی",
    "name": "شناسه یکتا (اختیاری)",
    "placeholder": "متن راهنما",
    "required": true/false,
    "price": 0,
    "width": "full|half|third|quarter",
    "options": [
        { "label": "گزینه ۱", "value": "opt1", "image": "url", "price": 0 }
    ],
    "settings": {
        "min": 0, "max": 10, "step": 0.5,
        "extensions": ["jpg","pdf"],
        "max_size": 5,
        "rows": 3,
        "level": "h3",
        "thickness": 1,
        "color": "#e2e8f0",
        "height": 20,
        "columns": 2,
        "gap": "medium",
        "code": "<!-- HTML -->"
    },
    "children": { /* برای گروه‌ها */ }
}
```

---

## 🔗 اتصال به ووکامرس (هوک‌ها)

| هوک | کاربرد | متد مرتبط |
|-----|--------|-----------|
| `woocommerce_before_add_to_cart_button` | نمایش فیلدها در صفحه محصول | `FieldRenderer::render_fields()` |
| `woocommerce_add_cart_item_data` | افزودن اطلاعات انتخاب‌شده به سبد خرید | `WooCommerceIntegration::add_cart_item_data()` |
| `woocommerce_get_item_data` | نمایش اطلاعات در سبد خرید | `WooCommerceIntegration::display_cart_item_data()` |
| `woocommerce_checkout_create_order_line_item` | ذخیره اطلاعات در سفارش | `WooCommerceIntegration::add_order_item_meta()` |
| `woocommerce_order_item_meta_end` | نمایش در پنل کاربری و ادمین | `OrderDisplay::display_in_*()` |
| `add_meta_boxes` | افزودن متاباکس انتخاب پالت در محصول و سفارش | `meta-box.php` |
| `woocommerce_cart_item_price` | محاسبه قیمت پویا در سبد خرید | `PricingEngine::calculate_cart_item_price()` |

---

## 🧠 راهنمای توسعه برای هوش مصنوعی و توسعه‌دهندگان آینده

### ۱. افزودن نوع فیلد جدید

1. در `template-editor-builder.php`، یک دکمه به نوار ابزار اضافه کن:
   ```html
   <button class="btn-add-field" data-type="new_type">🆕 نوع جدید</button>
   ```
2. در تابع `getFieldHTML`، یک `case` جدید برای `type` اضافه کن.
3. در متد `render_field` در `class-field-renderer.php`، یک `case` جدید برای رندر در فرانت‌اند اضافه کن.
4. در `FieldSchemaValidator`، قانون اعتبارسنجی برای نوع جدید اضافه کن.
5. در صورت نیاز، تنظیمات اختصاصی را به `field.settings` در `template-editor-settings.php` اضافه کن.

### ۲. افزودن قابلیت شرطی (Conditional Logic)

- از کلاس `ConditionEvaluator` استفاده کن.
- یک کلید جدید به `field` اضافه کن مانند `condition: { field: "other_field", operator: "==", value: "something" }`.
- در `product-options.js`، هنگام تغییر فیلدها، شرایط را با AJAX یا محلی بررسی کن و فیلدهای مرتبط را نمایش/مخفی کن.
- در `FieldRenderer`، متد `should_render_field` را توسعه بده.

### ۳. افزودن قالب آماده جدید (Preset)

1. در `PresetLibrary`، یک متد جدید اضافه کن:
   ```php
   public function getPresetNew() {
       return [
           'title' => 'عنوان قالب',
           'fields' => [ /* تعریف فیلدها */ ]
       ];
   }
   ```
2. در متد `getPresets()`، قالب جدید را به لیست اضافه کن.
3. در صفحه `class-preset-library-page.php`، یک دکمه برای اعمال قالب جدید اضافه کن.

### ۴. تغییر در ذخیره‌سازی یا نمایش

- ذخیره‌سازی در سبد خرید: `WooCommerceIntegration::add_cart_item_data()`.
- نمایش در سبد خرید: `WooCommerceIntegration::display_cart_item_data()`.
- ذخیره در سفارش: `WooCommerceIntegration::add_order_item_meta()`.
- نمایش در پنل کاربری/ادمین: `OrderDisplay` کلاس.

### ۵. بکاپ و Import/Export

- از متدهای `export_json` و `import_json` در `template-editor.php` استفاده کن.
- داده‌های خروجی شامل تمام پالت‌ها و فیلدهای آنهاست.

---

## 🧪 تست و اشکال‌زدایی

- **خطاهای رایج:** `getFieldsData is not defined` → فایل‌های JS را به‌درستی بارگذاری کن و از `window.getFieldsData` استفاده کن.
- **ذخیره‌سازی نشدن کدها:** مطمئن شو `name`های textarea در `template-editor.php` به `code_html`, `code_css`, `code_js` تنظیم شده و در `class-ajax-handler.php` دریافت می‌شوند.
- **نمایش فیلدها در صفحه محصول:** محصول را ویرایش کن و یک پالت به آن متصل کن. در غیر این صورت فیلدی نمایش داده نمی‌شود.
- **قیمت پویا محاسبه نمی‌شود:** مطمئن شو `PricingEngine` در `WooCommerceIntegration` به‌درستی فراخوانی شده است.
- **خطای دیتابیس:** جدول `wp_ezlens_option_templates` را بررسی کن و مطمئن شو `class-install.php` اجرا شده است.

---

## 📌 نکات امنیتی

- تمام ورودی‌ها با `sanitize_text_field`, `wp_kses_post`, `sanitize_email` و ... پاک‌سازی می‌شوند.
- Nonce برای تمام درخواست‌های AJAX استفاده شده است.
- دسترسی به صفحات مدیریتی با `current_user_can('manage_options')` محافظت می‌شود.
- فایل‌های آپلودشده با `media_handle_upload` مدیریت می‌شوند.
- `FieldSchemaValidator` از ورود داده‌های مخرب JSON جلوگیری می‌کند.
- کوئری‌های دیتابیس در `TemplateRepository` با `$wpdb->prepare` پارامتریک شده‌اند.

---

## 🧾 جمع‌بندی

ماژول **ویژگی‌های محصول** یک سیستم کامل، لایه‌ای و انعطاف‌پذیر برای مدیریت فیلدهای سفارشی محصولات در فروشگاه ایزی‌لنز است. این ماژول با معماری **Repository-Service-Controller**، جدا کردن مسئولیت‌ها و استفاده از هوک‌های استاندارد ووکامرس، به‌راحتی قابل توسعه و نگهداری است. تمام کدها در پوشه‌ی `modules/product-options/` قرار دارند و برای افزودن قابلیت‌های جدید، نیازی به تغییر در هسته‌ی اصلی پلاگین نیست.

### مسیرهای کلیدی برای توسعه‌دهندگان جدید

- **افزودن فیلد جدید:** `template-editor-builder.php` ← `FieldSchemaValidator` ← `FieldRenderer`
- **تغییر منطق قیمت:** `PricingEngine` ← `WooCommerceIntegration`
- **افزودن قالب آماده:** `PresetLibrary` ← `class-preset-library-page.php`
- **تغییر نمایش در فرانت‌اند:** `product-options.js` ← `product-options-frontend.css`
- **تغییر نمایش در ادمین:** `template-list.php` ← `template-editor-*.php`

---

**تاریخ بروزرسانی سند:** ۱۴۰۴/۰۶/۱۴  
**نسخه سند:** 2.0.0  
**وضعیت:** هم‌راستا با کد (Code-Aligned)