# منوی وردپرس — ویژگی‌های محصول

منوی فعلی زیر **EzLens Auth** ثبت شده (parent slug: `ezlens-auth`):

```
admin.php?page=ezlens-auth
admin.php?page=ezlens-product-options
admin.php?page=ezlens-purchase-process
...
```

## تنظیمات پالت‌ها
ماژول خودش این زیرمنو را ثبت می‌کند:

- Parent: `ezlens-auth`
- slug: `ezlens-product-options-settings`
- callback: `ezlens_po_render_settings_page`

همچنین تب **پالت‌ها | تنظیمات** روی صفحه لیست و صفحه تنظیمات هست.

## اگر بخواهی منوی تو درختی‌تر شود
وردپرس سطح سوم منو ندارد. برای ظاهر «زیرِ ویژگی‌های محصول» معمولاً:

1. همان parent=`ezlens-auth` با عنوان «— تنظیمات پالت‌ها» (الان)
2. یا فقط تب داخل صفحه `ezlens-product-options`

## فایلی که برای تغییر منوی اصلی لازم است
ثبت منوی `ezlens-product-options` در **پلاگین اصلی** است، نه فقط این ماژول؛ معمولاً یکی از:

- `ezlens-secure-login.php`
- `admin/pages/dashboard.php` یا کلاس Admin Menu
- جستجو: `ezlens-product-options` و `add_submenu_page`

اگر همان فایل را بفرستی می‌توان عنوان/ترتیب منو را دقیق‌تر چید.
