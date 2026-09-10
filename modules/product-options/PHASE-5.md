# Product Options — فاز ۵ (ادمین ماژولار)

**تاریخ:** 2026-09-08

## ساختار جدید

```
admin/pages/
  list/
    index.php
    assets/list.css
    assets/list.js
  editor/
    index.php          # orchestrator
    builder.php
    code.php
    preview.php
    settings.php
    form.php
    assets/editor.css
    assets/editor.js
  export-import/
    index.php
    assets/export-import.css
    assets/export-import.js
  settings/
    index.php
  # سازگاری عقب‌رو (فقط لودر):
  template-list.php → list/index.php
  template-editor.php → editor/index.php
  template-editor-*.php → editor/*
  template-export-import.php → export-import/index.php
  template-settings.php → settings/index.php
  template-form.php → editor/form.php
```

## تغییرات دیگر

- `Template_Manager::get_list()` همیشه `{ items, total }` برمی‌گرداند (باگ لیست بعد از فاز ۰ برطرف شد).
- CSS/JS از داخل PHP جدا شد تا شخصی‌سازی بدون دست زدن به منطق آسان‌تر باشد.

## نکته برای شخصی‌سازی

- استایل لیست پالت‌ها: `admin/pages/list/assets/list.css`
- رفتار JS لیست (bulk/export UI): `list/assets/list.js`
- ادیتور: `editor/assets/editor.css` و `editor.js`
- سازنده فیلد: فقط `editor/builder.php`

مسیرهای قدیمی همچنان کار می‌کنند؛ هر `include` قبلی به همان فایل‌های لودر می‌رسد.

## تست

1. صفحه لیست پالت‌ها باز شود (فیلتر، صفحه‌بندی، آمار)
2. ویرایش پالت → builder / preview / settings
3. Export / Import JSON
4. تنظیمات ماژول
