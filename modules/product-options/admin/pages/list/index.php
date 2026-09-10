<?php
/**
 * Template list page (modular) — Phase 5.
 * Path: admin/pages/list/index.php
 *
 * @package EzLens_Secure_Login
 */
if (!defined('ABSPATH')) {
    exit;
}

// Route: add / edit → editor (menu callback often always loads list)
$_ez_action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
if ($_ez_action === 'add' || $_ez_action === 'edit') {
    $editor = dirname(__DIR__) . '/editor/index.php';
    if (is_readable($editor)) {
        require $editor;
        return;
    }
}
if ($_ez_action === 'export' || $_ez_action === 'import') {
    $ei = dirname(__DIR__) . '/export-import/index.php';
    if (is_readable($ei)) {
        require $ei;
        return;
    }
}

$manager = EzLens_Product_Options_Template_Manager::get_instance();

$status = isset($_GET['status']) ? sanitize_key($_GET['status']) : 'all';
$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$paged  = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
$limit  = 20;
$offset = ($paged - 1) * $limit;

$list = $manager->get_list(array(
    'status' => $status,
    'search' => $search,
    'limit'  => $limit,
    'offset' => $offset,
));

$templates = isset($list['items']) && is_array($list['items']) ? $list['items'] : array();
$total     = isset($list['total']) ? (int) $list['total'] : count($templates);
$total_pages = max(1, (int) ceil($total / $limit));

foreach ($templates as &$template) {
    if (!is_array($template)) {
        continue;
    }
    $template['product_count'] = $manager->get_connected_products_count($template['id'] ?? 0);
}
unset($template);

$active_count   = $manager->count_templates('active');
$inactive_count = $manager->count_templates('inactive');

// Assets
$css_path = __DIR__ . '/assets/list.css';
$js_path  = __DIR__ . '/assets/list.js';
$css_url  = EZLAUTH_MODULES_URL . 'product-options/admin/pages/list/assets/list.css';
$js_url   = EZLAUTH_MODULES_URL . 'product-options/admin/pages/list/assets/list.js';

if (is_readable($css_path)) {
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '?v=' . esc_attr((string) filemtime($css_path)) . '">';
}
?>
<div class="wrap ezlens-template-list">
    
    <!-- ===== هدر اصلی ===== -->
    <div class="ezlens-header">
        <div class="ezlens-header-left">
            <h1 class="wp-heading-inline">🧩 ویژگی‌های محصول</h1>
            <span class="ezlens-badge"><?php echo number_format($total); ?> پالت</span>
        </div>
        <div class="ezlens-header-right">
            <a href="<?php echo admin_url('admin.php?page=ezlens-product-options-add'); ?>" class="ezlens-btn ezlens-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                افزودن پالت جدید
            </a>
            <button type="button" class="ezlens-btn ezlens-btn-success" id="create-preset-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v16h16"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                پالت آماده
            </button>
            <button type="button" class="ezlens-btn ezlens-btn-outline" id="refresh-list">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                بروزرسانی
            </button>
        </div>
    </div>


    <!-- Tabs -->
    <nav class="ezlens-po-tabs" aria-label="بخش‌ها">
        <a class="ezlens-po-tab is-active" href="<?php echo esc_url(admin_url('admin.php?page=ezlens-product-options')); ?>">پالت‌ها</a>
        <a class="ezlens-po-tab" href="<?php echo esc_url(admin_url('admin.php?page=ezlens-product-options-settings')); ?>">تنظیمات</a>
    </nav>

    <!-- ===== کارت آمار ===== -->
    <div class="ezlens-stats-cards">
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($total); ?></span>
            <span class="stat-label">کل پالت‌ها</span>
        </div>
        <div class="stat-card stat-active">
            <span class="stat-number"><?php echo number_format($active_count); ?></span>
            <span class="stat-label">فعال</span>
        </div>
        <div class="stat-card stat-inactive">
            <span class="stat-number"><?php echo number_format($inactive_count); ?></span>
            <span class="stat-label">غیرفعال</span>
        </div>
        <div class="stat-card stat-products">
            <span class="stat-number"><?php echo number_format(array_sum(array_column($templates, 'product_count'))); ?></span>
            <span class="stat-label">محصولات متصل</span>
        </div>
    </div>

    <!-- ===== نوار ابزار Export/Import (گام ۹) ===== -->
    <div class="ezlens-toolbar">
        <div class="ezlens-toolbar-left">
            <span class="toolbar-label">💾 بکاپ و انتقال</span>
            <button type="button" class="ezlens-btn ezlens-btn-primary" id="export-templates">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                خروجی JSON
            </button>
            <button type="button" class="ezlens-btn ezlens-btn-outline" id="show-import-form">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                وارد کردن JSON
            </button>
        </div>
        <div class="ezlens-toolbar-right">
            <span class="toolbar-hint">خروجی شامل تمام پالت‌ها با فیلدهایشان است</span>
        </div>
    </div>

    <!-- ===== فرم Import (گام ۹) ===== -->
    <div id="import-form-container" class="ezlens-import-box" style="display:none;">
        <div class="import-header">
            <h3>📥 وارد کردن پالت‌ها از فایل JSON</h3>
            <button type="button" class="import-close" id="hide-import-form">✕</button>
        </div>
        <p class="import-desc">فایل JSON خروجی را انتخاب کنید تا پالت‌ها به سیستم اضافه شوند.</p>
        <form id="import-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="ezlens_import_templates">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('ezlens_export_import_nonce'); ?>">
            <div class="import-row">
                <div class="file-wrap">
                    <span class="file-btn">📂 انتخاب فایل</span>
                    <input type="file" id="import-file-input" name="json_file" accept=".json">
                </div>
                <span id="import-file-name" class="file-name">هیچ فایلی انتخاب نشده است.</span>
                <label class="import-checkbox">
                    <input type="checkbox" name="overwrite" value="1">
                    بازنویسی پالت‌های تکراری
                </label>
                <button type="submit" class="ezlens-btn ezlens-btn-primary" id="import-submit-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    شروع وارد کردن
                </button>
            </div>
            <div id="import-status" class="import-status"></div>
        </form>
    </div>

    <!-- ===== نوار فیلتر ===== -->
    <div class="ezlens-filter-bar">
        <form method="get" id="filter-form" class="filter-form">
            <input type="hidden" name="page" value="ezlens-product-options">
            <div class="filter-group">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" name="s" placeholder="جستجوی پالت..." value="<?php echo esc_attr($search); ?>" class="filter-search">
            </div>
            <select name="status" class="filter-select">
                <option value="all" <?php selected($status, 'all'); ?>>همه</option>
                <option value="active" <?php selected($status, 'active'); ?>>فعال</option>
                <option value="inactive" <?php selected($status, 'inactive'); ?>>غیرفعال</option>
            </select>
            <button type="submit" class="ezlens-btn ezlens-btn-secondary">🔍 فیلتر</button>
            <a href="<?php echo admin_url('admin.php?page=ezlens-product-options'); ?>" class="ezlens-btn ezlens-btn-ghost">🔄 بازنشانی</a>
        </form>
        <div class="bulk-actions">
            <select id="bulk-action-select" class="bulk-select">
                <option value="">اقدام گروهی</option>
                <option value="delete">🗑️ حذف انتخاب‌شده</option>
                <option value="duplicate">📋 کپی انتخاب‌شده</option>
            </select>
            <button type="button" id="bulk-apply" class="ezlens-btn ezlens-btn-secondary">اعمال</button>
        </div>
    </div>

    <!-- ===== جدول ===== -->
    <div class="ezlens-table-wrap">
        <table class="ezlens-table">
            <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" id="select-all"></th>
                    <th class="col-id">#</th>
                    <th class="col-title">عنوان</th>
                    <th class="col-status">وضعیت</th>
                    <th class="col-fields">فیلدها</th>
                    <th class="col-products">محصولات</th>
                    <th class="col-date">آخرین ویرایش</th>
                    <th class="col-actions">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($templates): foreach ($templates as $template): ?>
                <tr data-id="<?php echo esc_attr($template['id']); ?>">
                    <td class="col-check"><input type="checkbox" class="template-checkbox" value="<?php echo esc_attr($template['id']); ?>"></td>
                    <td class="col-id"><?php echo esc_html($template['id']); ?></td>
                    <td class="col-title">
                        <strong><?php echo esc_html($template['title']); ?></strong>
                        <?php if (!empty($template['description'])): ?>
                            <div class="row-desc"><?php echo esc_html(substr($template['description'], 0, 60)) . (strlen($template['description']) > 60 ? '...' : ''); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="col-status">
                        <span class="status-badge <?php echo $template['status'] === 'active' ? 'active' : 'inactive'; ?>">
                            <?php echo $template['status'] === 'active' ? 'فعال' : 'غیرفعال'; ?>
                        </span>
                    </td>
                    <td class="col-fields"><span class="field-count"><?php echo count($template['fields'] ?? []); ?></span></td>
                    <td class="col-products"><span class="product-count"><?php echo (int)($template['product_count'] ?? 0); ?></span></td>
                    <td class="col-date"><?php echo esc_html($template['updated_at'] ?? $template['created_at']); ?></td>
                    <td class="col-actions">
                        <a href="<?php echo admin_url('admin.php?page=ezlens-product-options&action=edit&id=' . $template['id']); ?>" class="action-btn action-edit" title="ویرایش">✏️</a>
                        <button type="button" class="action-btn action-duplicate" data-id="<?php echo esc_attr($template['id']); ?>" title="کپی">📋</button>
                        <button type="button" class="action-btn action-delete" data-id="<?php echo esc_attr($template['id']); ?>" title="حذف">🗑️</button>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr class="empty-row">
                    <td colspan="8">
                        <div class="empty-state">
                            <span class="empty-icon">📭</span>
                            <h3>هیچ پالتی ساخته نشده است</h3>
                            <p>برای شروع، روی دکمه «افزودن پالت جدید» کلیک کنید.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== صفحه‌بندی ===== -->
    <?php if ($total_pages > 1): ?>
    <div class="ezlens-pagination">
        <span class="pagination-info">نمایش <?php echo number_format($offset + 1); ?> تا <?php echo number_format(min($offset + $limit, $total)); ?> از <?php echo number_format($total); ?> مورد</span>
        <div class="pagination-links">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=ezlens-product-options&paged=<?php echo $i; ?>&status=<?php echo esc_attr($status); ?>&s=<?php echo esc_attr($search); ?>" 
                   class="page-link <?php echo $i == $paged ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ===== استایل‌های مینیمال و مدرن ===== -->

<?php
$presets = array();
if (method_exists($manager, 'get_presets')) {
    $presets = $manager->get_presets();
    if (!is_array($presets)) {
        $presets = array();
    }
}
$list_config = array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonces'  => array(
        'exportImport' => wp_create_nonce('ezlens_export_import_nonce'),
        'delete'       => wp_create_nonce('ezlens_template_delete_nonce'),
        'duplicate'    => wp_create_nonce('ezlens_template_editor_nonce'),
        'preset'       => wp_create_nonce('ezlens_template_editor_nonce'),
    ),
    'presets' => $presets,
);
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.ezlensPoList = <?php echo wp_json_encode($list_config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<?php if (is_readable($js_path)) : ?>
<script src="<?php echo esc_url($js_url); ?>?v=<?php echo esc_attr((string) filemtime($js_path)); ?>"></script>
<?php endif; ?>
