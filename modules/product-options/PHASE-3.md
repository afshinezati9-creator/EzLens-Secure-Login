# Product Options — فاز ۳ (آپلود حرفه‌ای)

**تاریخ:** 2026-09-08

## قابلیت‌ها

- حذف ظاهر پیش‌فرض Choose File
- Dropzone: کلیک + کشیدن و رها کردن
- نوار پیشرفت واقعی (XHR `upload.onprogress`) با درصد فارسی
- پیش‌نمایش تصویر / برچسب پسوند برای PDF/DOC
- وضعیت‌ها: idle / progress / success / error
- حذف فایل و تلاش دوباره
- اعتبارسنجی سمت کلاینت: نوع + حداکثر ۵ مگابایت
- سمت سرور (از قبل + تقویت):
  - nonce
  - rate limit ۱۰ / ۱۰ دقیقه
  - mime واقعی با `wp_check_filetype_and_ext`
  - `is_uploaded_file`
  - پیام خطای فارسی برای کدهای UPLOAD_ERR_*
  - پاسخ شامل `url`, `filename`, `attachment_id`, `mime`, `size`

## فایل‌ها

```
frontend/renderers/class-upload-renderer.php
frontend/assets/product-options-phase2.css  (+ بخش آپلود)
frontend/assets/product-options-phase2.js   (+ XHR آپلود)
class-field-renderer.php  (case upload → Upload_Renderer)
class-ajax-handler.php    (پیام خطا و payload غنی‌تر)
```

## تست

1. صفحه محصول با فیلد upload
2. انتخاب تصویر → progress → پیش‌نمایش
3. PDF → پیش‌نمایش با برچسب PDF
4. فایل بزرگ‌تر از ۵MB → خطای فارسی
5. افزودن به سبد → URL در meta سفارش
6. حذف فایل قبل از افزودن به سبد → مقدار خالی
