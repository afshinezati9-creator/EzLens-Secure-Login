## 📘 مستندات فنی ماژول ویژگی‌های محصول (Product Options Module)

**نسخه:** 1.0.0  
**تاریخ:** ۱۴۰۴/۰۶/۱۳  
**نویسنده:** تیم توسعه EzLens  
**حالت:** تکمیل‌شده (فاز ۱ تا ۷)

---

## 🧭 هدف کلی ماژول

ماژول **ویژگی‌های محصول (Product Options)** یک لایه‌ی مدیریتی و نمایشی بر روی ووکامرس است که به مدیر فروشگاه اجازه می‌دهد **پالت‌های سفارشی از فیلدها** را تعریف کرده و آن‌ها را به محصولات خاص متصل کند. مشتری در صفحه‌ی محصول، فیلدهای مرتبط را مشاهده کرده، اطلاعات موردنیاز (متن، انتخاب، آپلود فایل، رنگ و ...) را وارد می‌کند و این اطلاعات در طول مسیر خرید (سبد خرید، تسویه‌حساب، سفارش، پنل کاربری و ادمین) حفظ و نمایش داده می‌شوند.

این ماژول **جایگزین پلاگین‌های شخص‌ثالث** مانند Extra Product Options نیست، بلکه با رویکردی **ماژولار، سبک و کاملاً سازگار با معماری EzLens** توسعه یافته است.

---

## 🧱 معماری و هسته‌ی فنی

### ۱. ساختار دیتابیس

| نام جدول | شرح |
|----------|------|
| `wp_ezlens_option_templates` | ذخیره‌ی پالت‌های ساخته‌شده توسط مدیر. شامل `title`, `description`, `fields` (JSON), `status`, `created_at`, `updated_at`. |
| `wp_postmeta` | برای اتصال پالت به محصول از کلید `_ezlens_option_template_id` استفاده می‌شود. |

### ۲. جریان داده (Data Flow)

```
مدیر → ایجاد پالت در داشبورد
     ↓
ذخیره در جدول `wp_ezlens_option_templates`
     ↓
اتصال پالت به محصول (از طریق postmeta)
     ↓
مشتری → صفحه محصول → نمایش فیلدها
     ↓
انتخاب/ورود اطلاعات → افزودن به سبد خرید
     ↓
ذخیره‌سازی اطلاعات در `cart_item_data`
     ↓
تبدیل به سفارش → ذخیره در `order_item_meta`
     ↓
نمایش در پنل کاربری، ادمین و ایمیل‌ها
```

---

## 📂 ساختار فایل‌های ماژول (بر اساس آخرین وضعیت)

```
modules/product-options/
│
├── class-install.php                      # ایجاد جدول دیتابیس (یک بار)
├── class-template-manager.php             # CRUD پالت‌ها (مدیریت)
├── class-ajax-handler.php                 # هندلرهای AJAX (ذخیره، کپی، حذف، آپلود، پیش‌نمایش)
├── class-field-renderer.php               # رندر فیلدها در صفحه محصول + ذخیره در سبد خرید و سفارش
├── class-order-display.php                # نمایش ویژگی‌ها در پنل کاربری، ادمین و ایمیل‌ها
│
├── admin/
│   ├── class-field-renderer.php           # (تکراری – احتمالاً منسوخ) یک نسخه از رندر فیلدها برای ادمین
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
└── frontend/
    ├── assets/
    │   ├── css/
    │   │   └── product-options-frontend.css  # استایل‌های صفحات محصول و سبد خرید
    │   └── js/
    │       ├── product-options.js            # منطق قیمت‌گذاری پویا، اعتبارسنجی، آپلود
    │       └── upload-progress.js           # (اختیاری) نوار پیشرفت آپلود
    └── (هیچ قالب جداگانه‌ای نیاز نیست؛ همه فیلدها توسط کلاس FieldRenderer رندر می‌شوند)
```

> **توجه:** فایل `admin/class-field-renderer.php` احتمالاً یک نسخه‌ی قدیمی یا تکراری است و باید با نسخه‌ی اصلی (موجود در ریشه‌ی ماژول) هماهنگ شود. در توسعه‌های آینده از آن استفاده نکنید.

---

## 🔧 شرح کلاس‌های اصلی

### `EzLens_Product_Options_Template_Manager`
- **مسئولیت:** مدیریت پالت‌ها (ایجاد، ویرایش، دریافت، لیست، حذف، کپی، اتصال به محصول)
- **متدهای کلیدی:**
  - `create($data)` – ثبت پالت جدید در دیتابیس
  - `update($id, $data)` – به‌روزرسانی پالت
  - `get($id)` – دریافت یک پالت با کش
  - `get_list($args)` – دریافت لیست با فیلتر و صفحه‌بندی
  - `delete($id)` – حذف پالت (با بررسی محصولات متصل)
  - `duplicate($id)` – کپی پالت
  - `get_template_for_product($product_id)` – دریافت پالت متصل به محصول
  - `attach_to_product($product_id, $template_id)` – اتصال/جداسازی پالت از محصول

### `EzLens_Product_Options_Ajax`
- **مسئولیت:** پردازش درخواست‌های AJAX از صفحه‌ی ویرایشگر و متاباکس
- **متدهای کلیدی:**
  - `save_template()` – ذخیره‌سازی پالت (با پشتیبانی از فیلدهای بصری و کدها)
  - `duplicate_template()` – کپی پالت
  - `delete_template()` – حذف پالت (با تأیید)
  - `get_template_preview()` – بارگذاری پیش‌نمایش فیلدها در متاباکس
  - `upload_file()` – آپلود فایل با نوار پیشرفت

### `EzLens_Product_Options_FieldRenderer`
- **مسئولیت:** رندر فیلدها در صفحه محصول، قیمت‌گذاری پویا، ذخیره‌سازی در سبد خرید و سفارش
- **متدهای کلیدی:**
  - `render_fields()` – نقطه‌ی ورود (هوک `woocommerce_before_add_to_cart_button`)
  - `render_field($key, $field)` – رندر یک فیلد بر اساس نوع آن
  - `add_cart_item_data()` – افزودن اطلاعات به سبد خرید
  - `display_cart_item_data()` – نمایش اطلاعات در سبد خرید
  - `add_order_item_meta()` – ذخیره در سفارش
  - `calculate_extra_price()` – محاسبه‌ی قیمت اضافی بر اساس انتخاب‌ها

### `EzLens_Product_Options_OrderDisplay`
- **مسئولیت:** نمایش ویژگی‌ها در پنل کاربری، ادمین و ایمیل‌ها
- **متدهای کلیدی:**
  - `display_in_user_panel()` – نمایش در سفارش‌های مشتری
  - `display_in_admin()` – نمایش در صفحه ویرایش سفارش (ادمین)
  - `display_in_emails()` – نمایش در ایمیل‌های تأیید سفارش
  - `add_order_meta_box()` – افزودن متاباکس در صفحه سفارش ادمین

### `EzLens_Product_Options_Install`
- **مسئولیت:** ایجاد جدول دیتابیس در زمان فعال‌سازی
- **متد کلیدی:** `create_table()` – با شرط یک‌بار اجرا شدن

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

| هوک | کاربرد |
|-----|--------|
| `woocommerce_before_add_to_cart_button` | نمایش فیلدها در صفحه محصول |
| `woocommerce_add_cart_item_data` | افزودن اطلاعات انتخاب‌شده به سبد خرید |
| `woocommerce_get_item_data` | نمایش اطلاعات در سبد خرید |
| `woocommerce_checkout_create_order_line_item` | ذخیره اطلاعات در سفارش |
| `woocommerce_order_item_meta_end` | نمایش در پنل کاربری و ادمین |
| `add_meta_boxes` | افزودن متاباکس انتخاب پالت در محصول و سفارش |

---

## 🧠 راهنمای توسعه برای هوش مصنوعی و توسعه‌دهندگان آینده

### ۱. افزودن نوع فیلد جدید

1. در `template-editor-builder.php`، یک دکمه به نوار ابزار اضافه کن:
   ```html
   <button class="btn-add-field" data-type="new_type">🆕 نوع جدید</button>
   ```
2. در تابع `getFieldHTML`، یک `case` جدید برای `type` اضافه کن.
3. در متد `render_field` در `class-field-renderer.php`، یک `case` جدید برای رندر در فرانت‌اند اضافه کن.
4. در صورت نیاز، تنظیمات اختصاصی را به `field.settings` در `template-editor-settings.php` اضافه کن.

### ۲. افزودن قابلیت شرطی (Conditional Logic)

- یک کلید جدید به `field` اضافه کن مانند `condition: { field: "other_field", operator: "==", value: "something" }`.
- در `product-options.js`، هنگام تغییر فیلدها، شرایط را بررسی کن و فیلدهای مرتبط را نمایش/مخفی کن.
- در `class-field-renderer.php`، یک متد `should_render_field($field, $values)` اضافه کن.

### ۳. تغییر در ذخیره‌سازی یا نمایش

- ذخیره‌سازی در سبد خرید: متد `add_cart_item_data` در `class-field-renderer.php`.
- نمایش در سبد خرید: متد `display_cart_item_data`.
- ذخیره در سفارش: متد `add_order_item_meta`.
- نمایش در پنل کاربری/ادمین: کلاس `class-order-display.php`.

### ۴. بکاپ و Import/Export

- از متدهای `export_json` و `import_json` در `template-editor.php` استفاده کن.
- داده‌های خروجی شامل تمام پالت‌ها و فیلدهای آنهاست.

---

## 🧪 تست و اشکال‌زدایی

- **خطاهای رایج:** `getFieldsData is not defined` → فایل‌های JS را به‌درستی بارگذاری کن و از `window.getFieldsData` استفاده کن.
- **ذخیره‌سازی نشدن کدها:** مطمئن شو `name`های textarea در `template-editor.php` به `code_html`, `code_css`, `code_js` تنظیم شده و در `class-ajax-handler.php` دریافت می‌شوند.
- **نمایش فیلدها در صفحه محصول:** محصول را ویرایش کن و یک پالت به آن متصل کن. در غیر این صورت فیلدی نمایش داده نمی‌شود.

---

## 📌 نکات امنیتی

- تمام ورودی‌ها با `sanitize_text_field`, `wp_kses_post`, `sanitize_email` و ... پاک‌سازی می‌شوند.
- Nonce برای تمام درخواست‌های AJAX استفاده شده است.
- دسترسی به صفحات مدیریتی با `current_user_can('manage_options')` محافظت می‌شود.
- فایل‌های آپلودشده با `media_handle_upload` مدیریت می‌شوند.

---

## 🧾 جمع‌بندی

ماژول **ویژگی‌های محصول** یک سیستم کامل و انعطاف‌پذیر برای مدیریت فیلدهای سفارشی محصولات در فروشگاه ایزی‌لنز است. این ماژول با معماری ماژولار، جدا کردن مسئولیت‌ها (Separation of Concerns) و استفاده از هوک‌های استاندارد ووکامرس، به‌راحتی قابل توسعه و نگهداری است. تمام کدها در پوشه‌ی `modules/product-options/` قرار دارند و برای افزودن قابلیت‌های جدید، نیازی به تغییر در هسته‌ی اصلی پلاگین نیست.

---

**تاریخ بروزرسانی سند:** ۱۴۰۴/۰۶/۱۳  
**نسخه سند:** 1.0.0