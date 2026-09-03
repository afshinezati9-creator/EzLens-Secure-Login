باشه. متن زیر نسخهٔ به‌روز شده‌ی `README.md` است که با ساختار جدید (ماژولار) پلاگین هماهنگ شده. می‌توانی آن را کپی کرده و در فایل جایگزین کنی.

---

```markdown
## 📘 مستندات کامل پلاگین EzLens Secure Login

**نسخه:** 5.4.0 (ساختار ماژولار بازسازیشده)  
**نویسنده:** افشین عزتی  
**وب‌سایت:** https://ezlens.ir  
**تاریخ انتشار:** ۱۴۰۴/۰۶/۰۲

---

## 📌 فهرست مطالب

1. [معرفی پلاگین](#۱-معرفی-پلاگین)
2. [معماری و ساختار کلی](#۲-معماری-و-ساختار-کلی)
3. [ساختار فایل‌ها و پوشه‌ها](#۳-ساختار-فایل‌ها-و-پوشه‌ها)
4. [شرح کامل کلاس‌ها](#۴-شرح-کامل-کلاس‌ها)
5. [جداول دیتابیس](#۵-جداول-دیتابیس)
6. [شورت‌کدها](#۶-شورت‌کدها)
7. [هوک‌ها و فیلترها](#۷-هوک‌ها-و-فیلترها)
8. [نکات امنیتی](#۸-نکات-امنیتی)
9. [راهنمای توسعه](#۹-راهنمای-توسعه)
10. [عیب‌یابی](#۱۰-عیب‌یابی)

---

## ۱. معرفی پلاگین

### ۱.۱. چیستی پلاگین

**EzLens Secure Login** یک پلاگین جامع و یکپارچه برای مدیریت **احراز هویت، پنل کاربری، سیستم تیکت پشتیبانی، کمپینگ تبلیغاتی و داشبورد مدیریت** در سایت‌های وردپرسی است. این پلاگین به‌صورت ویژه برای **فروشگاه‌های حوزه سلامت بینایی (عینک، لنز، خدمات چشم‌پزشکی)** طراحی شده اما قابلیت استفاده در هر نوع سایت وردپرسی را دارد.

> **توجه:** نسخه‌ی فعلی با ساختار **ماژولار** بازنویسی شده است تا امکان توسعه و نگهداری آسان‌تر فراهم شود.

### ۱.۲. ویژگی‌های کلیدی

| # | ویژگی | توضیح |
|---|-------|--------|
| ۱ | **تغییر مسیر ورود** | جایگزینی `wp-login.php` و `wp-admin` با آدرس‌های سفارشی |
| ۲ | **سه روش ورود** | ورود با OTP (پیامک)، ورود دستی (شماره موبایل + رمز)، ثبت‌نام |
| ۳ | **پنل کاربری کامل** | پیشخوان، سبد خرید، سفارش‌ها، آدرس، پروفایل، نسخه پزشکی، امنیت، پشتیبانی |
| ۴ | **سیستم تیکت پشتیبانی** | چت آنلاین، ارسال فایل، مدیریت تیکت‌ها با صفحه‌بندی و فیلتر |
| ۵ | **کمپینگ تبلیغاتی** | ارسال ایمیل/پیامک گروهی، گروه‌های مخاطبان، Import/Export CSV |
| ۶ | **داشبورد مدیریت** | آمار کاربران، لاگ‌ها، لیست کاربران با شماره تماس |
| ۷ | **سیستم OTP** | کدهای یکبارمصرف ۶ رقمی با انقضا و محدودیت تلاش |
| ۸ | **اتصال به SMS.ir** | ارسال کد تأیید از طریق پیامک |
| ۹ | **ویرایشگر صفحات** | ویرایش HTML/CSS/JS صفحات به‌صورت ایزوله |
| ۱۰ | **بکاپ و API** | خروجی/ورودی JSON، کلید API برای اتصال برنامه‌های خارجی |

### ۱.۳. مخاطبان هدف

- **مدیران فروشگاه‌های آنلاین** (ویژه حوزه سلامت بینایی)
- **توسعه‌دهندگان وردپرس** که به دنبال یک راه‌حل جامع احراز هویت هستند
- **صاحبان کسب‌وکارهای آنلاین** که نیاز به سیستم پشتیبانی و کمپینگ دارند

---

## ۲. معماری و ساختار کلی

### ۲.۱. الگوی طراحی

پلاگین از **الگوی Singleton** برای کلاس‌های اصلی استفاده می‌کند تا از ایجاد چندین نمونه جلوگیری شود.

```php
private static $instance = null;

public static function get_instance() {
    if (null === self::$instance) {
        self::$instance = new self();
    }
    return self::$instance;
}
```

### ۲.۲. ساختار MVC-like

| لایه | مسیر | توضیح |
|------|------|--------|
| **Core** | `core/` | کلاس‌های هسته (تنظیمات، ارتقا، کرون، Container) |
| **Modules** | `modules/` | هر قابلیت در یک ماژول مستقل (auth, campaign, customer, ...) |
| **Admin** | `admin/` | صفحات مدیریتی و Assets مربوطه |
| **Frontend** | `frontend/` | صفحات جلوی سایت و Assets مربوطه |
| **API** | `api/` | REST API و Webhook |
| **Shared** | `shared/` | کدهای مشترک بین ماژول‌ها (Helper, Interfaces) |
| **Includes** | `includes/` | بارگذارهای AJAX و شورت‌کدها (در حال انتقال به ماژول‌ها) |

### ۲.۳. جریان بارگذاری

```
ezlens-secure-login.php
    ↓
تعریف Constants و مسیرهای جدید
    ↓
plugins_loaded → ezlens_auth_load_core_classes()
    ↓
بارگذاری کلاس‌های هسته از core/ و modules/
    ↓
is_admin() → بارگذاری admin/pages/dashboard.php
    ↓
init → بارگذاری شورت‌کدها و AJAX
    ↓
wp_enqueue_scripts → بارگذاری Assets (شرطی)
```

---

## ۳. ساختار فایل‌ها و پوشه‌ها (نسخه ماژولار)

```
ezlens-secure-login/
│
├── ezlens-secure-login.php          # فایل اصلی پلاگین (ورودی)
│
├── core/                            # هسته اصلی (قابل تغییر نیست)
│   ├── class-settings.php           # مدیریت تنظیمات با کش
│   ├── class-upgrader.php           # مدیریت ارتقا و Migration
│   └── class-cron.php               # کرون جاب‌ها
│
├── modules/                         # هر قابلیت یک ماژول مستقل
│   ├── auth/                        # احراز هویت
│   │   ├── class-login.php          # تغییر مسیر ورود و صفحات سفارشی
│   │   ├── class-otp.php            # مدیریت کدهای یکبارمصرف
│   │   └── class-app-auth.php       # احراز هویت اپلیکیشن موبایل
│   │
│   ├── customer/                    # مشتری (داشبورد، پروفایل، سفارش)
│   │   ├── class-notifications.php  # اعلان‌های کاربر
│   │   ├── class-order-meta.php     # متاباکس کد رهگیری سفارش
│   │   ├── class-order-tracking.php # رهگیری سفارشات
│   │   └── class-user-customizations.php # سفارشی‌سازی کاربر
│   │
│   ├── campaign/                    # کمپین‌های تبلیغاتی
│   │   ├── class-campaign.php       # مدیریت کمپین‌ها و گروه‌ها
│   │   ├── class-campaign-queue.php # صف و پردازش کمپین
│   │   └── class-campaign-compliance.php # لغو عضویت (Unsubscribe)
│   │
│   ├── messaging/                   # پیام‌رسانی
│   │   ├── class-messaging.php      # لایه اصلی پیام‌رسانی
│   │   ├── class-sms.php            # مدیریت SMS
│   │   └── providers/               # سرویس‌های پیامکی
│   │       ├── class-provider-interface.php
│   │       ├── class-sms-ir-provider.php
│   │       ├── class-kavenegar-provider.php
│   │       └── class-custom-provider.php
│   │
│   ├── support/                     # پشتیبانی (تیکت‌ها)
│   │   └── class-support.php        # مدیریت تیکت‌ها و پیام‌ها
│   │
│   └── analytics/                   # آمار و گزارش‌ها
│       ├── class-logger.php         # لاگ‌گیری ورود/خروج
│       ├── class-audit.php          # لاگ امنیتی (Audit Trail)
│       └── class-health.php         # سلامت سیستم
│
├── admin/                           # بخش مدیریتی (ادمین)
│   ├── pages/                       # صفحات ادمین (هر صفحه یک فایل)
│   │   ├── dashboard.php            # داشبورد مدیریت (کلاس EzLens_Auth_Admin)
│   │   ├── settings.php             # تنظیمات عمومی
│   │   ├── editor.php               # ویرایشگر صفحات
│   │   ├── logs.php                 # گزارش‌های لاگ
│   │   ├── backup.php               # بکاپ و API
│   │   ├── campaign.php             # مدیریت کمپینگ
│   │   └── support.php              # مدیریت پشتیبانی
│   │
│   └── assets/                      # فایل‌های مدیریتی
│       ├── css/
│       │   ├── admin.css            # استایل اصلی ادمین
│       │   └── admin-campaign.css   # استایل کمپینگ
│       └── js/
│           ├── admin.js             # اسکریپت اصلی ادمین
│           └── admin-campaign.js    # اسکریپت کمپینگ
│
├── frontend/                        # بخش جلوی سایت
│   ├── pages/                       # صفحات فرانت‌اند
│   │   ├── login.php                # ورود/ثبت‌نام مشتری ([minimal_auth])
│   │   ├── forgot-password.php      # فراموشی رمز
│   │   ├── user-panel.php           # پنل کاربری ([modern_user_panel])
│   │   └── support-chat.php         # ویجت چت پشتیبانی
│   │
│   ├── templates/                   # قالب‌های HTML (در صورت نیاز)
│   │
│   └── assets/                      # فایل‌های فرانت‌اند
│       ├── css/
│       │   ├── frontend-core.css    # استایل‌های پایه
│       │   ├── frontend-auth.css    # استایل صفحات ورود
│       │   ├── frontend-panel.css   # استایل پنل کاربری
│       │   ├── frontend-admin.css   # استایل ورود مدیر
│       │   ├── frontend-responsive.css # استایل ریسپانسیو
│       │   └── support.css          # استایل ویجت چت
│       └── js/
│           ├── auth.js              # اسکریپت احراز هویت
│           ├── panel.js             # اسکریپت پنل کاربری
│           ├── admin-login.js       # اسکریپت ورود مدیر
│           └── support.js           # اسکریپت ویجت چت
│
├── api/                             # API و Webhook
│   ├── class-api.php                # REST API نسخه ۱
│   ├── class-api-v2.php             # REST API نسخه ۲ (برای اپلیکیشن)
│   └── class-webhooks.php           # ارسال رویدادها به سرورهای خارجی
│
├── shared/                          # کدهای مشترک
│   └── helpers/
│       └── class-helper.php         # توابع کمکی (نرمال‌سازی، رمزگذاری، ایمیل)
│
├── includes/                        # (در حال انتقال به ماژول‌ها)
│   ├── ajax/                        # هندلرهای AJAX
│   │   ├── class-ajax-loader.php    # بارگذار AJAX
│   │   ├── class-ajax-auth.php
│   │   ├── class-ajax-editor.php
│   │   ├── class-ajax-settings.php
│   │   ├── class-ajax-support.php
│   │   ├── class-ajax-tests.php
│   │   └── class-campaign-ajax.php
│   │
│   └── shortcodes/                  # شورت‌کدها
│       ├── class-shortcodes-loader.php
│       ├── class-shortcodes-auth.php
│       ├── class-shortcodes-forgot.php
│       └── class-shortcodes-panel.php
│
├── templates/                       # قالب‌های قدیمی (برای سازگاری)
│   ├── admin-dashboard.php
│   ├── admin-login.php
│   ├── defaults/                    # قالب‌های پیش‌فرض ویرایشگر
│   ├── admin-support-tabs/
│   ├── campaign-tabs/
│   └── settings-tabs/
│
├── assets/                          # فایل‌های عمومی (فقط ضروری‌ها)
│   └── fonts/
│       └── vazirmatn/               # فونت وزیرمتن (داخلی)
│
└── bootstrap/                       # بارگذار اولیه
    ├── autoloader.php
    └── container.php
```

---

## ۴. شرح کامل کلاس‌ها

### ۴.۱. کلاس‌های Core (هسته)

| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Settings` | `core/class-settings.php` | مدیریت تنظیمات با کش (`wp_cache`) |
| `EzLens_Auth_Upgrader` | `core/class-upgrader.php` | مدیریت ارتقا و ایجاد/به‌روزرسانی جداول |
| `EzLens_Auth_Cron` | `core/class-cron.php` | کرون جاب برای پاک‌سازی خودکار (لاگ‌ها، OTP) |

---

### ۴.۲. کلاس‌های Modules (ماژول‌ها)

#### احراز هویت (Auth)
| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Login` | `modules/auth/class-login.php` | تغییر مسیر `wp-login.php`، نمایش صفحات سفارشی و هدایت پس از لاگین |
| `EzLens_Auth_OTP` | `modules/auth/class-otp.php` | مدیریت کدهای یکبارمصرف (ذخیره، تأیید، اعتبارسنجی) |
| `EzLens_Auth_App_Auth` | `modules/auth/class-app-auth.php` | احراز هویت بدون رمز برای اپلیکیشن موبایل (Bearer Token) |

#### مشتری (Customer)
| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Notifications` | `modules/customer/class-notifications.php` | اعلان‌های کاربر |
| `EzLens_Auth_Order_Meta` | `modules/customer/class-order-meta.php` | متاباکس کد رهگیری سفارش در ادمین |
| `EzLens_Order_Tracking` | `modules/customer/class-order-tracking.php` | رهگیری سفارشات (مراحل و کد رهگیری) |
| `EzLens_Auth_User_Customizations` | `modules/customer/class-user-customizations.php` | سفارشی‌سازی‌های کاربر (پروفایل، لیست کاربران) |

#### کمپینگ (Campaign)
| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Campaign` | `modules/campaign/class-campaign.php` | مدیریت کمپین‌ها، گروه‌ها و مخاطبان |
| `EzLens_Auth_Campaign_Queue` | `modules/campaign/class-campaign-queue.php` | صف و پردازش کمپین‌ها (با WP-Cron) |
| `EzLens_Auth_Campaign_Compliance` | `modules/campaign/class-campaign-compliance.php` | مدیریت لغو عضویت (Unsubscribe) |

#### پیام‌رسانی (Messaging)
| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Messaging` | `modules/messaging/class-messaging.php` | لایه اصلی پیام‌رسانی (Email, SMS) |
| `EzLens_Auth_SMS` | `modules/messaging/class-sms.php` | مدیریت SMS و سازگاری با نسخه‌های قدیمی |
| Providerها | `modules/messaging/providers/` | پیاده‌سازی سرویس‌های SMS.ir، Kavenegar و Custom HTTP |

#### پشتیبانی (Support)
| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Support` | `modules/support/class-support.php` | مدیریت تیکت‌ها و پیام‌ها |

#### آمار (Analytics)
| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Logger` | `modules/analytics/class-logger.php` | لاگ‌گیری ورود/خروج |
| `EzLens_Auth_Audit` | `modules/analytics/class-audit.php` | لاگ امنیتی (Audit Trail) |
| `EzLens_Auth_Health` | `modules/analytics/class-health.php` | بررسی سلامت سیستم |

---

### ۴.۳. کلاس‌های مدیریت (Admin)

| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Admin` | `admin/pages/dashboard.php` | مدیریت منوی ادمین، رندر صفحات و ذخیره تنظیمات |

---

### ۴.۴. کلاس‌های AJAX

| کلاس | مسیر | وظیفه |
|------|------|--------|
| `EzLens_Auth_Ajax_Loader` | `includes/ajax/class-ajax-loader.php` | بارگذار درخواست‌های AJAX |
| `EzLens_Auth_Ajax_Auth` | `includes/ajax/class-ajax-auth.php` | OTP، ورود، ثبت‌نام، فراموشی رمز |
| `EzLens_Auth_Ajax_Editor` | `includes/ajax/class-ajax-editor.php` | ذخیره/بازنشانی کدها، پیش‌نمایش، فعال‌سازی صفحات |
| `EzLens_Auth_Ajax_Settings` | `includes/ajax/class-ajax-settings.php` | ذخیره تنظیمات، بکاپ، API Key |
| `EzLens_Auth_Campaign_Ajax` | `includes/ajax/class-campaign-ajax.php` | ایجاد/ارسال/حذف کمپین، گروه‌ها، مخاطبان |
| `EzLens_Auth_Ajax_Support` | `includes/ajax/class-ajax-support.php` | ایجاد تیکت، پاسخ، تغییر وضعیت، جستجوی کاربران |
| `EzLens_Auth_Ajax_Tests` | `includes/ajax/class-ajax-tests.php` | تست SMS و SMTP |

---

### ۴.۵. کلاس‌های کمکی

| کلاس | مسیر | توضیح |
|------|------|--------|
| `EzLens_Auth_Helper` | `shared/helpers/class-helper.php` | توابع کمکی عمومی (نرمال‌سازی موبایل، ایمیل، رمزگذاری، Rate Limiting) |

---

## ۵. جداول دیتابیس

### ۵.۱. جدول لاگ‌ها (`wp_ezlens_logs`)

```sql
CREATE TABLE wp_ezlens_logs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    username varchar(60) NOT NULL,
    action varchar(20) NOT NULL,
    ip varchar(45) NOT NULL,
    user_agent text,
    timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY action (action),
    KEY timestamp (timestamp)
);
```

### ۵.۲. جدول OTP (`wp_ezlens_otp`)

```sql
CREATE TABLE wp_ezlens_otp (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    mobile varchar(15) NOT NULL,
    code varchar(6) NOT NULL,
    action varchar(20) NOT NULL DEFAULT 'login',
    expires_at datetime NOT NULL,
    is_used tinyint(1) NOT NULL DEFAULT 0,
    attempts int(11) NOT NULL DEFAULT 0,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY mobile (mobile),
    KEY code (code),
    KEY expires_at (expires_at)
);
```

### ۵.۳. جداول کمپینگ

#### `wp_ezlens_campaigns`
```sql
CREATE TABLE wp_ezlens_campaigns (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    type varchar(20) NOT NULL DEFAULT 'email',
    subject varchar(255) DEFAULT '',
    message longtext NOT NULL,
    file_attachment varchar(255) DEFAULT '',
    status varchar(20) NOT NULL DEFAULT 'draft',
    scheduled_at datetime DEFAULT NULL,
    sent_at datetime DEFAULT NULL,
    stats longtext,
    created_by bigint(20) NOT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY status (status),
    KEY created_at (created_at)
);
```

#### `wp_ezlens_campaign_contacts`
```sql
CREATE TABLE wp_ezlens_campaign_contacts (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20) DEFAULT '',
    category varchar(100) DEFAULT 'عمومی',
    extra_fields longtext,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY email (email),
    KEY category (category)
);
```

#### `wp_ezlens_campaign_groups`
```sql
CREATE TABLE wp_ezlens_campaign_groups (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text,
    type varchar(20) NOT NULL DEFAULT 'custom',
    user_filters longtext,
    contact_ids longtext,
    created_by bigint(20) NOT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY created_by (created_by)
);
```

#### `wp_ezlens_campaign_group_relations`
```sql
CREATE TABLE wp_ezlens_campaign_group_relations (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    campaign_id bigint(20) NOT NULL,
    group_id bigint(20) NOT NULL,
    PRIMARY KEY (id),
    KEY campaign_id (campaign_id),
    KEY group_id (group_id)
);
```

#### `wp_ezlens_campaign_tracks`
```sql
CREATE TABLE wp_ezlens_campaign_tracks (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    campaign_id bigint(20) NOT NULL,
    recipient_email varchar(100) NOT NULL,
    opened_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip varchar(45) DEFAULT '',
    user_agent text,
    PRIMARY KEY (id),
    KEY campaign_id (campaign_id),
    KEY recipient_email (recipient_email)
);
```

### ۵.۴. جداول پشتیبانی

#### `wp_ezlens_support_tickets`
```sql
CREATE TABLE wp_ezlens_support_tickets (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'open',
    subject varchar(255) DEFAULT '',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY status (status)
);
```

#### `wp_ezlens_support_messages`
```sql
CREATE TABLE wp_ezlens_support_messages (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    ticket_id bigint(20) NOT NULL,
    sender_id bigint(20) NOT NULL,
    sender_type varchar(10) NOT NULL DEFAULT 'user',
    message text NOT NULL,
    file_attachment varchar(255) DEFAULT '',
    is_read tinyint(1) NOT NULL DEFAULT 0,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ticket_id (ticket_id),
    KEY sender_id (sender_id)
);
```

### ۵.۵. جداول اضافی (Audit, Notifications, App Tokens, ...)

جداول زیر توسط `core/class-upgrader.php` ایجاد می‌شوند:

- `wp_ezlens_audit_log` (لاگ امنیتی)
- `wp_ezlens_notifications` (اعلان‌های کاربر)
- `wp_ezlens_app_tokens` (توکن‌های اپلیکیشن)
- `wp_ezlens_campaign_unsubscribes` (لغو عضویت)
- `wp_ezlens_email_log` (لاگ ایمیل‌ها)
- `wp_ezlens_campaign_recipients` (گیرندگان کمپین)

---

## ۶. شورت‌کدها

| شورت‌کد | کاربرد | فایل قالب |
|---------|--------|-----------|
| `[minimal_auth]` | صفحه ورود/ثبت‌نام مشتری | `frontend/pages/login.php` |
| `[ezlens_lost_password]` | صفحه فراموشی رمز (غیرفعال) | `frontend/pages/forgot-password.php` |
| `[modern_user_panel]` | پنل کاربری کامل | `frontend/pages/user-panel.php` |
| `[admin_login_page]` | صفحه ورود مدیر | `templates/admin-login.php` |
| `[ezlens_support_chat]` | ویجت چت پشتیبانی | `frontend/pages/support-chat.php` |

---

## ۷. هوک‌ها و فیلترها

### ۷.۱. فیلترها (Filters)

| فیلتر | توضیح | نمونه |
|-------|-------|--------|
| `ezlens_auth_login_redirect` | تغییر مسیر پس از ورود | `add_filter('ezlens_auth_login_redirect', function($url, $user) { return home_url('/custom-page/'); }, 10, 2);` |
| `ezlens_auth_register_redirect` | تغییر مسیر پس از ثبت‌نام | `add_filter('ezlens_auth_register_redirect', function($url, $user_id) { return home_url('/welcome/'); }, 10, 2);` |
| `ezlens_auth_customer_redirect` | تغییر مسیر مشتری پس از ورود | `add_filter('ezlens_auth_customer_redirect', function($url, $user) { return home_url('/my-account/'); }, 10, 2);` |

### ۷.۲. اکشن‌ها (Actions)

| اکشن | توضیح | نمونه |
|------|-------|--------|
| `ezlens_auth_user_registered` | پس از ثبت‌نام کاربر جدید | `add_action('ezlens_auth_user_registered', function($user_id, $data) { error_log('User registered: ' . $data['email']); }, 10, 2);` |
| `ezlens_support_ticket_created` | پس از ایجاد تیکت جدید | `add_action('ezlens_support_ticket_created', function($ticket_id) { // ... }, 10, 1);` |
| `ezlens_campaign_sent` | پس از ارسال کامل کمپین | `add_action('ezlens_campaign_sent', function($campaign_id, $stats) { // ... }, 10, 2);` |

---

## ۸. نکات امنیتی

### ۸.۱. Nonce برای تمام درخواست‌ها

```php
// در JS
'nonce' => wp_create_nonce('ezlens_auth_nonce')

// در PHP
check_ajax_referer('ezlens_auth_nonce', 'nonce');
```

### ۸.۲. رمزگذاری کلیدهای حساس

کلیدهای زیر با `openssl_encrypt` رمزگذاری می‌شوند:
- `sms_api_key`
- `smtp_password`
- `captcha_secret_key`
- `otp_sms_api_key`
- `campaign_sms_api_key`

### ۸.۳. Sanitization و Validation

تمام ورودی‌ها با توابع استاندارد وردپرس پاک‌سازی می‌شوند:
- `sanitize_text_field()`
- `sanitize_email()`
- `sanitize_textarea_field()`
- `wp_kses_post()`
- `esc_url_raw()`

### ۸.۴. مسدودسازی `wp-admin` برای کاربران غیرمدیر

```php
if (is_admin() && !wp_doing_ajax() && !defined('DOING_AJAX')) {
    $current_user = wp_get_current_user();
    if (!is_user_logged_in() || !in_array('administrator', (array) $current_user->roles)) {
        $this->show_404();
        exit;
    }
}
```

### ۸.۵. محدودیت تلاش OTP

هر شماره موبایل حداکثر **۵ بار** تلاش ناموفق می‌تواند داشته باشد (`otp_max_attempts`).

### ۸.۶. Rate Limiting برای ورود

با استفاده از `transient`، تلاش‌های ناموفق ورود محدود می‌شوند (پیش‌فرض: ۵ تلاش در ۱۵ دقیقه).

---

## ۹. راهنمای توسعه

### ۹.۱. افزودن یک ماژول جدید

برای افزودن قابلیت جدید به‌صورت ماژولار:

1. پوشه‌ی جدید در `modules/` ایجاد کنید، مثلاً `modules/loyalty/`.
2. کلاس اصلی را با نام `class-loyalty.php` ایجاد کنید.
3. از الگوی Singleton استفاده کنید.
4. هوک‌های موردنیاز را در `__construct()` ثبت کنید.
5. اگر نیاز به جدول دیتابیس دارید، متد `create_table()` اضافه کنید و در `core/class-upgrader.php` فراخوانی کنید.
6. برای بارگذاری خودکار کلاس، نام آن را در Autoloader (که در `ezlens-secure-login.php` تعریف شده) قرار دهید (Autoloader به‌صورت خودکار ماژول‌ها را اسکن می‌کند).

### ۹.۲. افزودن یک شورت‌کد جدید

**مرحله ۱:** در `includes/shortcodes/class-shortcodes-loader.php` شورت‌کد را ثبت کنید:

```php
add_shortcode('my_custom_shortcode', [$this, 'render_my_shortcode']);

public function render_my_shortcode($atts) {
    return '<div>محتوای شورت‌کد</div>';
}
```

**مرحله ۲:** در `ezlens-secure-login.php` شورت‌کد را به لیست بررسی‌ها اضافه کنید (در تابع `enqueue_frontend_assets`):

```php
$shortcodes = array('minimal_auth', 'my_custom_shortcode', ...);
```

### ۹.۳. افزودن یک تب جدید به تنظیمات

**مرحله ۱:** ایجاد فایل `templates/settings-tabs/mytab.php`

**مرحله ۲:** در `includes/ajax/class-ajax-settings.php` کیس جدید اضافه کنید:

```php
case 'mytab':
    include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/mytab.php';
    break;
```

**مرحله ۳:** در `admin/pages/settings.php` (که در `templates/admin-settings.php` قرار دارد) دکمه تب جدید اضافه کنید.

### ۹.۴. افزودن یک متد AJAX جدید

**مرحله ۱:** در کلاس AJAX مناسب، متد جدید اضافه کنید:

```php
public static function my_ajax_action() {
    check_ajax_referer('ezlens_auth_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز');
        wp_die();
    }
    // منطق شما
    wp_send_json_success(['message' => 'انجام شد.']);
    wp_die();
}
```

**مرحله ۲:** در `__construct()` همان کلاس، اکشن را ثبت کنید:

```php
add_action('wp_ajax_ezlens_my_ajax_action', [__CLASS__, 'my_ajax_action']);
```

**مرحله ۳:** در JS درخواست ارسال کنید:

```javascript
$.post(ezlens_auth_ajax.ajax_url, {
    action: 'ezlens_my_ajax_action',
    nonce: ezlens_auth_ajax.nonce,
    // داده‌ها
}, function(response) {
    // پردازش پاسخ
});
```

### ۹.۵. افزودن یک متغیر قابل شخصی‌سازی در ایمیل‌ها

در متد `send_campaign` یا `send_email`، متغیر جدید را به جایگزین‌ها اضافه کنید:

```php
$message = str_replace(
    ['{name}', '{email}', '{my_custom_var}'],
    [$recipient['name'], $recipient['email'], $custom_value],
    $campaign->message
);
```

---

## ۱۰. عیب‌یابی

### ۱۰.۱. مشکلات رایج و راه‌حل‌ها

| مشکل | راه‌حل |
|------|--------|
| **خطای ۴۰۴ در wp-admin** | مطمئن شوید کاربر نقش مدیر دارد. در غیر این صورت به آدرس جدید ورود هدایت می‌شود. |
| **عدم دریافت کد OTP** | تنظیمات SMS را بررسی کنید (API Key, Line Number, Template ID). حالت Sandbox را غیرفعال کنید. |
| **خطای ۴۰۳ در AJAX** | `nonce` را بررسی کنید. مطمئن شوید `check_ajax_referer` با نام صحیح فراخوانی شده است. |
| **خطای ۵۰۰ در AJAX** | لاگ‌های PHP را بررسی کنید (`wp-content/debug.log`). |
| **تیکت ایجاد نمی‌شود** | جداول دیتابیس را بررسی کنید. متد `create_tables` را در `class-support.php` اجرا کنید. |
| **مخاطب دستی اضافه نمی‌شود** | ستون `category` را در جدول `wp_ezlens_campaign_contacts` بررسی کنید. |
| **کمپین ارسال نمی‌شود** | تنظیمات SMTP و SMS را بررسی کنید. مطمئن شوید گروه‌های مخاطبان انتخاب شده‌اند. |
| **خطای دیتابیس `Invalid default value for 'id'`** | مطمئن شوید `core/class-upgrader.php` به‌روز است و تابع `repair_legacy_id_defaults()` اجرا شده است. |
| **استایل‌ها نمایش داده نمی‌شوند** | بررسی کنید که فایل‌های CSS در مسیرهای جدید (`frontend/assets/css/` و `admin/assets/css/`) قرار دارند و با `Ctrl+F5` رفرش کنید. |

### ۱۰.۲. فعال‌سازی حالت دیباگ

برای مشاهده خطاها، در فایل `wp-config.php` کد زیر را اضافه کنید:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

سپس خطاها در `wp-content/debug.log` ذخیره می‌شوند.

### ۱۰.۳. بازنشانی تنظیمات

برای بازنشانی تمام تنظیمات پلاگین به حالت پیش‌فرض:
- به بخش **تنظیمات عمومی EzLens** بروید.
- روی دکمه **بازنشانی همه** کلیک کنید.

---

## 📌 اطلاعات تماس و پشتیبانی

- **وب‌سایت:** https://ezlens.ir
- **ایمیل:** info@ezlens.ir
- **تلفن:** 02144385667

---

**پایان مستندات**  
**نسخه:** 5.4.0 (ساختار ماژولار)  
**تاریخ:** ۱۴۰۴/۰۶/۰۲
```

---

حالا می‌توانی این متن را در فایل `README.md` کپی کرده و جایگزین کنی.