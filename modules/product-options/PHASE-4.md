# Product Options — فاز ۴ (فیلدهای حرفه‌ای + renderer جدا)

**تاریخ:** 2026-09-08

## ساختار

```
frontend/renderers/
  class-field-dispatcher.php      # مسیریابی بر اساس type
  class-field-render-helpers.php  # id/name/label/wrapper مشترک
  class-text-renderer.php         # text, email, phone, number, textarea
  class-choice-renderer.php       # select, radio, checkbox
  class-image-select-renderer.php # کارت‌های تصویری
  class-color-datetime-renderer.php
  class-structural-renderer.php   # heading, divider, spacer, html
  class-group-renderer.php
  class-upload-renderer.php       # از فاز ۳
```

`FieldRenderer::render_field()` فقط به Dispatcher می‌سپارد.

## UI

- Input با آیکون (email / phone / date / time)
- Select سفارشی (بدون ظاهر پیش‌فرض OS)
- Radio/Checkbox به‌صورت کارت انتخاب‌شونده
- Image select: گرید کارت + تیک انتخاب
- Color: picker + فیلد hex همگام
- Group: باکس و grid ستونی
- تم مینیمال آبی/خاکستری هم‌راستا با آپلود و مودال

## تست

1. پالت با انواع فیلد (متن، سلکت، رادیو، چک‌باکس، image_select، رنگ، تاریخ، گروه، آپلود)
2. انتخاب گزینه دارای قیمت → راهنمای قیمت
3. image_select با/بدون URL تصویر
4. همگام بودن color picker و hex
