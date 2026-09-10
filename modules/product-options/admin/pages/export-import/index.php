<?php
/**
 * صفحه مدیریت Export/Import پالت‌ها
 * این فایل به‌عنوان یک صفحه مستقل برای مدیریت بکاپ و انتقال پالت‌ها استفاده می‌شود
 * 
 * @package EzLens_Product_Options
 */

if (!defined('ABSPATH')) exit;

$_ez_ei_css = __DIR__ . '/assets/export-import.css';
$_ez_ei_js  = __DIR__ . '/assets/export-import.js';
$_ez_ei_css_url = EZLAUTH_MODULES_URL . 'product-options/admin/pages/export-import/assets/export-import.css';
$_ez_ei_js_url  = EZLAUTH_MODULES_URL . 'product-options/admin/pages/export-import/assets/export-import.js';
if (is_readable($_ez_ei_css)) {
    echo '<link rel="stylesheet" href="' . esc_url($_ez_ei_css_url) . '?v=' . esc_attr((string) filemtime($_ez_ei_css)) . '">';
}

?>

<div class="wrap ezlens-export-import">
    <h1 class="wp-heading-inline">💾 بکاپ و انتقال پالت‌ها</h1>
    <a href="<?php echo admin_url('admin.php?page=ezlens-product-options'); ?>" class="page-title-action">← بازگشت به لیست</a>
    <hr class="wp-header-end">

    <div class="ezlens-export-import-grid">

        <!-- ===== بخش Export ===== -->
        <div class="card export-card">
            <div class="card-header">
                <span class="card-icon">📤</span>
                <h2>خروجی گرفتن (Export)</h2>
            </div>
            <p class="card-desc">خروجی JSON از تمام پالت‌ها با فیلدهایشان. مناسب برای بکاپ یا انتقال به سایت دیگر.</p>

            <div class="export-options">
                <div class="filter-group">
                    <label>فیلتر وضعیت:</label>
                    <select id="export-status-filter">
                        <option value="all">همه</option>
                        <option value="active">فعال</option>
                        <option value="inactive">غیرفعال</option>
                    </select>
                </div>
            </div>

            <button type="button" class="button button-primary" id="export-btn" style="width:100%;padding:10px;font-size:15px;font-weight:600;">
                📥 دانلود خروجی JSON
            </button>

            <div id="export-result" class="export-result" style="margin-top:12px;display:none;">
                <span class="success">✅ خروجی با موفقیت ایجاد شد.</span>
            </div>
        </div>

        <!-- ===== بخش Import ===== -->
        <div class="card import-card">
            <div class="card-header">
                <span class="card-icon">📥</span>
                <h2>وارد کردن (Import)</h2>
            </div>
            <p class="card-desc">فایل JSON خروجی را انتخاب کنید تا پالت‌ها به سیستم اضافه شوند.</p>

            <form id="import-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="ezlens_import_templates">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('ezlens_export_import_nonce'); ?>">

                <div class="import-file-area" id="drop-zone">
                    <span class="upload-icon">📤</span>
                    <p>فایل JSON را اینجا بکشید یا کلیک کنید</p>
                    <span class="file-hint">فایل‌های JSON</span>
                    <input type="file" id="import-file-input" name="json_file" accept=".json">
                    <div id="import-file-name" class="file-name"></div>
                </div>

                <div class="import-options">
                    <label class="toggle-label">
                        <input type="checkbox" name="overwrite" value="1">
                        <span>بازنویسی پالت‌های تکراری</span>
                    </label>
                    <span class="toggle-hint">در صورت فعال بودن، پالت‌های با عنوان مشابه به‌روزرسانی می‌شوند.</span>
                </div>

                <button type="submit" class="button button-primary" id="import-submit-btn" style="width:100%;padding:10px;font-size:15px;font-weight:600;">
                    ⬆️ شروع وارد کردن
                </button>
            </form>

            <div id="import-status" class="import-status" style="margin-top:12px;display:none;"></div>
        </div>

        <!-- ===== بخش اطلاعات ===== -->
        <div class="card info-card">
            <div class="card-header">
                <span class="card-icon">ℹ️</span>
                <h2>اطلاعات</h2>
            </div>
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">تعداد پالت‌ها:</span>
                    <span class="info-value" id="info-total"><?php echo number_format($total ?? 0); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">فعال:</span>
                    <span class="info-value" id="info-active"><?php echo number_format($active_count ?? 0); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">غیرفعال:</span>
                    <span class="info-value" id="info-inactive"><?php echo number_format($inactive_count ?? 0); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">محصولات متصل:</span>
                    <span class="info-value" id="info-products"><?php echo number_format($products_count ?? 0); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">آخرین بکاپ:</span>
                    <span class="info-value" id="info-backup"><?php echo get_option('ezlens_last_export_date', '—'); ?></span>
                </div>
            </div>
            <button type="button" class="button button-secondary" id="refresh-info" style="width:100%;margin-top:8px;">🔄 بروزرسانی اطلاعات</button>
        </div>
    </div>

    <!-- ===== استایل‌ها ===== -->
    

    <!-- ===== اسکریپت‌ها ===== -->
    
</div>

<script>
window.ezlensPoExport = window.ezlensPoExport || {
  ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
  nonce: <?php echo wp_json_encode(wp_create_nonce('ezlens_export_import_nonce')); ?>
};
</script>
<?php if (is_readable($_ez_ei_js)) : ?>
<script src="<?php echo esc_url($_ez_ei_js_url); ?>?v=<?php echo esc_attr((string) filemtime($_ez_ei_js)); ?>"></script>
<?php endif; ?>
