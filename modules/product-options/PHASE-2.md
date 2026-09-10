# Product Options — فاز ۲ (رندر فرانت)

**تاریخ:** 2026-09-08  
**وابستگی:** فاز ۰ و ۱

## قابلیت‌ها

1. **چند پالت روی صفحه محصول** با مکان جدا:
   - `gallery_side` → `woocommerce_before_single_product_summary` (اولویت ۳۵)
   - `below_price` → `woocommerce_single_product_summary` (۱۵)
   - `below_summary` → `woocommerce_single_product_summary` (۴۵)
   - `full_width` → `woocommerce_after_single_product_summary` (۸)
   - فیلتر: `ezlens_po_placement_hooks`
   - Fallback نزدیک دکمه افزودن به سبد اگر هوک تم اجرا نشد

2. **حالت نمایش**
   - `inline` — مستقیم
   - `accordion` — کشویی
   - `ajax_modal` — دکمه + مودال (فیلدها در فرم می‌مانند برای submit)

3. **کلید فیلد چندقالبی**  
   `ezlens_options[{template_id}][{field_key}]` → ذخیره cart به‌صورت `tid:field`

4. **قیمت**  
   Pricing Engine جمع extra همه پالت‌ها را حساب می‌کند.

5. **اعتبارسنجی**  
   Required per-template با برچسب پالت.

6. **اعداد فارسی** در برچسب قیمت فرانت (JS + Helpers)

## فایل‌های جدید/تغییر‌یافته

```
frontend/placements/class-placement-registry.php
frontend/display-modes/class-display-modes.php
frontend/assets/product-options-phase2.css
frontend/assets/product-options-phase2.js
class-field-renderer.php   (بازنویسی)
class-pricing-engine.php   (multi-slot)
class-woocommerce-integration.php (multi-slot validate)
```

## تست

1. محصول با دو اسلات: یکی below_price + inline، یکی full_width + accordion
2. صفحه محصول: هر دو بلوک در جای تقریبی درست
3. آکاردئون باز/بسته شود؛ مودال باز و تأیید شود و مقدار در cart بماند
4. فیلد اجباری خالی → خطای افزودن به سبد با نام پالت
5. گزینه دارای قیمت → جمع سبد درست
