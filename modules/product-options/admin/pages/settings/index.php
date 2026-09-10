<?php
/**
 * Product Options settings page.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render settings UI (menu callback).
 */
function ezlens_po_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('شما دسترسی لازم را ندارید.', 'ezlens-auth'));
    }

    $tab_css = EZLAUTH_MODULES_DIR . 'product-options/admin/pages/list/assets/list.css';
    if (is_readable($tab_css)) {
        echo '<link rel="stylesheet" href="' . esc_url(EZLAUTH_MODULES_URL . 'product-options/admin/pages/list/assets/list.css') . '?v=' . esc_attr((string) filemtime($tab_css)) . '">';
    }


    // Ensure settings class.
    $settings_file = EZLAUTH_MODULES_DIR . 'product-options/includes/class-po-settings.php';
    if (is_readable($settings_file)) {
        require_once $settings_file;
    }

    $saved = false;
    if (
        isset($_POST['ezlens_po_settings_save'])
        && check_admin_referer('ezlens_po_settings_save', 'ezlens_po_settings_nonce')
        && class_exists('EzLens_PO_Settings')
    ) {
        EzLens_PO_Settings::save(wp_unslash($_POST));
        $saved = true;
    }

    $s = class_exists('EzLens_PO_Settings') ? EzLens_PO_Settings::all() : array();
    $get = function ($k, $d = '') use ($s) {
        return isset($s[ $k ]) ? $s[ $k ] : $d;
    };
    ?>
    <div class="wrap" style="max-width:820px;">
        <h1>تنظیمات ویژگی‌های محصول</h1>
        <nav class="ezlens-po-tabs" aria-label="بخش‌ها" style="margin:12px 0 16px;">
            <a class="ezlens-po-tab" href="<?php echo esc_url(admin_url('admin.php?page=ezlens-product-options')); ?>">پالت‌ها</a>
            <a class="ezlens-po-tab is-active" href="<?php echo esc_url(admin_url('admin.php?page=ezlens-product-options-settings')); ?>">تنظیمات</a>
        </nav>
        <p class="description">مدیریت نمایش پالت‌ها در محصول، سبد خرید و سفارش.</p>

        <?php if ($saved) : ?>
            <div class="notice notice-success is-dismissible"><p>تنظیمات ذخیره شد.</p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('ezlens_po_settings_save', 'ezlens_po_settings_nonce'); ?>
            <input type="hidden" name="ezlens_po_settings_save" value="1">

            <div class="card" style="padding:16px 20px;margin-top:16px;">
                <h2 style="margin-top:0;">عمومی</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th>فعال‌سازی ماژول</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" <?php checked($get('enabled', '1'), '1'); ?>>
                                فعال کردن ویژگی‌های محصول
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>فیلدهای اجباری</th>
                        <td>
                            <label>
                                <input type="checkbox" name="required_fields" value="1" <?php checked($get('required_fields', '1'), '1'); ?>>
                                اعتبارسنجی فیلدهای required در افزودن به سبد
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>نمایش عنوان پالت</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_title" value="1" <?php checked($get('show_title', '1'), '1'); ?>>
                                نمایش عنوان پالت در صفحه محصول
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="card" style="padding:16px 20px;margin-top:16px;">
                <h2 style="margin-top:0;">آپلود فایل</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th><label for="max_upload_size">حداکثر حجم (مگابایت)</label></th>
                        <td>
                            <input type="number" id="max_upload_size" name="max_upload_size" value="<?php echo esc_attr((string) $get('max_upload_size', 5)); ?>" min="1" max="100" style="width:80px;">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="allowed_extensions">پسوندهای مجاز</label></th>
                        <td>
                            <input type="text" id="allowed_extensions" name="allowed_extensions" value="<?php echo esc_attr($get('allowed_extensions', 'jpg,jpeg,png,pdf')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <div class="card" style="padding:16px 20px;margin-top:16px;border-color:#bfdbfe;">
                <h2 style="margin-top:0;">نمایش در سبد خرید</h2>
                <p class="description" style="margin-top:0;">به‌جای لیست ساده، می‌توانید دکمهٔ بازشونده یا پالت کشویی نشان دهید.</p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th>حالت نمایش</th>
                        <td>
                            <?php $mode = $get('cart_display_mode', 'button'); ?>
                            <label style="display:block;margin-bottom:6px;">
                                <input type="radio" name="cart_display_mode" value="button" <?php checked($mode, 'button'); ?>>
                                <strong>دکمه + باکس</strong> — مثلاً «نسخه لنز»؛ با کلیک جزئیات باز می‌شود
                            </label>
                            <label style="display:block;margin-bottom:6px;">
                                <input type="radio" name="cart_display_mode" value="accordion" <?php checked($mode, 'accordion'); ?>>
                                <strong>کشویی (آکاردئون)</strong> — هر پالت جدا باز/بسته می‌شود
                            </label>
                            <label style="display:block;">
                                <input type="radio" name="cart_display_mode" value="inline" <?php checked($mode, 'inline'); ?>>
                                <strong>مستقیم</strong> — همه مقادیر زیر نام محصول
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cart_button_label">متن دکمه</label></th>
                        <td>
                            <input type="text" id="cart_button_label" name="cart_button_label" value="<?php echo esc_attr($get('cart_button_label', '')); ?>" class="regular-text" placeholder="خالی = نام پالت (مثلاً نسخه لنز)">
                            <p class="description">اگر خالی باشد از عنوان پالت استفاده می‌شود.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>گروه‌بندی</th>
                        <td>
                            <label>
                                <input type="checkbox" name="cart_group_by_template" value="1" <?php checked($get('cart_group_by_template', '1'), '1'); ?>>
                                گروه‌بندی مقادیر بر اساس پالت
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>هزینه اضافی</th>
                        <td>
                            <label>
                                <input type="checkbox" name="cart_show_extra_price" value="1" <?php checked($get('cart_show_extra_price', '1'), '1'); ?>>
                                نمایش هزینه اضافی گزینه‌ها در سبد
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>تسویه حساب</th>
                        <td>
                            <label>
                                <input type="checkbox" name="checkout_same_as_cart" value="1" <?php checked($get('checkout_same_as_cart', '1'), '1'); ?>>
                                همان حالت نمایش در صفحه تسویه حساب
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="card" style="padding:16px 20px;margin-top:16px;">
                <h2 style="margin-top:0;">نمایش در سفارش</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th>حالت</th>
                        <td>
                            <?php $om = $get('order_display_mode', 'inline'); ?>
                            <select name="order_display_mode">
                                <option value="inline" <?php selected($om, 'inline'); ?>>مستقیم</option>
                                <option value="accordion" <?php selected($om, 'accordion'); ?>>کشویی</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <p style="margin-top:20px;">
                <button type="submit" class="button button-primary button-large">ذخیره تنظیمات</button>
            </p>
        </form>
    </div>
    <?php
}

/**
 * Callable for add_submenu_page callback.
 */
class EzLens_PO_Settings_Page {
    public static function render() {
        ezlens_po_render_settings_page();
    }
}
