# پچ پیشنهادی ezlens-secure-login.php (فاز ۰)

## در `ezlens_auth_load_core_classes()` — بخش Product Options

**قبل:** چند فایل جدا + نام کلاس اشتباه در boot

**بعد:**

```php
        // Modules - Product Options (class-install bootstrap loads manager/ajax/renderer/...)
        EZLAUTH_MODULES_DIR . 'product-options/class-install.php',
```

حذف از `$core_files`:
- `product-options/class-template-manager.php`
- `product-options/class-ajax-handler.php`
- `product-options/admin/meta-box.php`
- `product-options/class-field-renderer.php`
- `product-options/class-order-display.php`

حذف بلوک:

```php
    if ( class_exists( 'EzLens_Product_Options' ) ) {
        EzLens_Product_Options::get_instance();
    }
    if ( class_exists( 'EzLens_Template_Manager' ) ) {
        EzLens_Template_Manager::get_instance();
    }
    if ( class_exists( 'EzLens_Product_Options_Ajax_Handler' ) ) {
        EzLens_Product_Options_Ajax_Handler::get_instance();
    }
    if ( class_exists( 'EzLens_Field_Renderer' ) ) {
        EzLens_Field_Renderer::get_instance();
    }
    if ( class_exists( 'EzLens_Order_Display' ) ) {
        EzLens_Order_Display::get_instance();
    }
```

## در `activate()` (اختیاری)

```php
        if ( class_exists( 'EzLens_Product_Options_Install' ) ) {
            EzLens_Product_Options_Install::install();
        }
```

