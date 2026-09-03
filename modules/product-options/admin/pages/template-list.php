<?php
if (!defined('ABSPATH')) exit;

$manager = EzLens_Product_Options_Template_Manager::get_instance();

// گرفتن پارامترهای فیلتر و صفحه‌بندی
$status = isset($_GET['status']) ? sanitize_key($_GET['status']) : 'all';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
$limit = 20;
$offset = ($paged - 1) * $limit;

// دریافت داده‌ها
$list = $manager->get_list([
    'status' => $status,
    'search' => $search,
    'limit'  => $limit,
    'offset' => $offset
]);

$templates = $list['items'];
$total = $list['total'];
$total_pages = ceil($total / $limit);

// اضافه کردن تعداد محصولات متصل (اگر متد exist باشد)
foreach ($templates as &$template) {
    if (method_exists($manager, 'get_connected_products_count')) {
        $template['product_count'] = $manager->get_connected_products_count($template['id']);
    } else {
        $template['product_count'] = 0;
    }
}
?>

<div class="wrap ezlens-template-list">
    <!-- هدر -->
    <div class="ezlens-list-header">
        <h1 class="wp-heading-inline">🧩 ویژگی‌های محصول</h1>
        <a href="<?php echo admin_url('admin.php?page=ezlens-product-options&action=add'); ?>" class="page-title-action">➕ افزودن پالت جدید</a>
        <button type="button" class="page-title-action" id="refresh-list" style="background:#f0fdf4;border-color:#86efac;color:#166534;">🔄 به‌روزرسانی</button>
    </div>

    <!-- پیام‌ها -->
    <div id="ezlens-messages" style="margin: 10px 0;"></div>

    <!-- فرم فیلتر و Bulk Actions -->
    <div class="ezlens-filter-bar">
        <form method="get" id="filter-form" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
            <input type="hidden" name="page" value="ezlens-product-options">
            <input type="search" name="s" placeholder="جستجوی پالت..." value="<?php echo esc_attr($search); ?>" class="ezlens-search-input">
            <select name="status" class="ezlens-filter-select">
                <option value="all" <?php selected($status, 'all'); ?>>همه</option>
                <option value="active" <?php selected($status, 'active'); ?>>فعال</option>
                <option value="inactive" <?php selected($status, 'inactive'); ?>>غیرفعال</option>
            </select>
            <button type="submit" class="button">🔍 فیلتر</button>
            <a href="<?php echo admin_url('admin.php?page=ezlens-product-options'); ?>" class="button">🔄 بازنشانی</a>
        </form>
        <div class="ezlens-bulk-actions">
            <select id="bulk-action-select" style="padding:4px 8px;border:1px solid #ddd;border-radius:4px;">
                <option value="">اقدام گروهی</option>
                <option value="delete">🗑️ حذف انتخاب‌شده</option>
                <option value="duplicate">📋 کپی انتخاب‌شده</option>
            </select>
            <button type="button" id="bulk-apply" class="button">اعمال</button>
        </div>
    </div>

    <!-- جدول -->
    <table class="wp-list-table widefat fixed striped ezlens-table">
        <thead>
            <tr>
                <th style="width:30px;"><input type="checkbox" id="select-all"></th>
                <th style="width:60px;">شناسه</th>
                <th>عنوان</th>
                <th style="width:100px;">وضعیت</th>
                <th style="width:70px;">فیلدها</th>
                <th style="width:90px;">محصولات</th>
                <th style="width:140px;">آخرین ویرایش</th>
                <th style="width:180px;">عملیات</th>
            </tr>
        </thead>
        <tbody id="ezlens-table-body">
            <?php if ($templates): foreach ($templates as $template): ?>
            <tr data-id="<?php echo esc_attr($template['id']); ?>">
                <td><input type="checkbox" class="template-checkbox" value="<?php echo esc_attr($template['id']); ?>"></td>
                <td>#<?php echo esc_html($template['id']); ?></td>
                <td><strong><?php echo esc_html($template['title']); ?></strong>
                    <?php if (!empty($template['description'])): ?>
                        <br><span style="font-size:11px;color:#94a3b8;"><?php echo esc_html(substr($template['description'], 0, 50)) . (strlen($template['description']) > 50 ? '...' : ''); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge <?php echo $template['status'] === 'active' ? 'active' : 'inactive'; ?>">
                        <?php echo $template['status'] === 'active' ? 'فعال' : 'غیرفعال'; ?>
                    </span>
                </td>
                <td><?php echo count($template['fields'] ?? []); ?></td>
                <td><?php echo (int)($template['product_count'] ?? 0); ?></td>
                <td><?php echo esc_html($template['updated_at'] ?? $template['created_at']); ?></td>
                <td class="actions">
                    <a href="<?php echo admin_url('admin.php?page=ezlens-product-options&action=edit&id=' . $template['id']); ?>" class="button button-small button-edit" title="ویرایش">✏️</a>
                    <button type="button" class="button button-small button-duplicate" data-id="<?php echo esc_attr($template['id']); ?>" title="کپی">📋</button>
                    <button type="button" class="button button-small button-delete" data-id="<?php echo esc_attr($template['id']); ?>" title="حذف">🗑️</button>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:30px 0;">هیچ پالتی ساخته نشده است. دکمه «افزودن پالت جدید» را بزنید.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- صفحه‌بندی -->
    <?php if ($total_pages > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo number_format($total); ?> مورد</span>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=ezlens-product-options&paged=<?php echo $i; ?>&status=<?php echo esc_attr($status); ?>&s=<?php echo esc_attr($search); ?>" 
                   class="button <?php echo $i == $paged ? 'button-primary' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ===== استایل‌های مینیمال و مدرن ===== -->
<style>
.ezlens-template-list {
    max-width: 100%;
    margin: 0 auto;
}

/* هدر */
.ezlens-list-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

/* نوار فیلتر */
.ezlens-filter-bar {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    margin-bottom: 16px;
}

.ezlens-search-input {
    padding: 6px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    min-width: 200px;
    background: #fff;
}

.ezlens-filter-select {
    padding: 6px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
}

.ezlens-bulk-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

/* جدول */
.ezlens-table {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.ezlens-table th {
    background: #f1f5f9;
    font-weight: 600;
    color: #0f172a;
    padding: 10px 12px;
}

.ezlens-table td {
    padding: 10px 12px;
    vertical-align: middle;
}

/* وضعیت */
.status-badge {
    padding: 2px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}
.status-badge.active {
    background: #d4edda;
    color: #155724;
}
.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

/* دکمه‌های عملیات */
.actions .button {
    padding: 2px 8px;
    font-size: 13px;
    border-radius: 4px;
    margin: 0 2px;
}
.button-edit { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }
.button-edit:hover { background: #dbeafe; }
.button-duplicate { border-color: #fcd34d; color: #92400e; background: #fffbeb; }
.button-duplicate:hover { background: #fef3c7; }
.button-delete { border-color: #fca5a5; color: #991b1b; background: #fef2f2; }
.button-delete:hover { background: #fee2e2; }

/* پیام‌ها */
.ezlens-toast {
    position: fixed;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    z-index: 99999;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    display: none;
    max-width: 90%;
    text-align: center;
}
.ezlens-toast.success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #86efac;
}
.ezlens-toast.error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
.ezlens-toast.info {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #93c5fd;
}
</style>

<!-- ===== اسکریپت‌ها ===== -->
<script>
jQuery(document).ready(function($) {
    'use strict';

    // ===== نمایش پیام =====
    function showToast(message, type) {
        var $toast = $('#ezlens-toast');
        if (!$toast.length) {
            $toast = $('<div id="ezlens-toast" class="ezlens-toast"></div>');
            $('body').append($toast);
        }
        $toast.removeClass('success error info').addClass(type).text(message).fadeIn(200);
        setTimeout(function() {
            $toast.fadeOut(300);
        }, 3000);
    }

    // ===== تابع حذف با AJAX =====
    function deleteTemplate(id, callback) {
        $.post(ajaxurl, {
            action: 'ezlens_delete_template',
            nonce: '<?php echo wp_create_nonce("ezlens_template_delete_nonce"); ?>',
            template_id: id
        }, function(response) {
            if (response.success) {
                showToast('✅ ' + response.data.message, 'success');
                if (typeof callback === 'function') callback(true);
            } else {
                showToast('❌ ' + response.data.message, 'error');
                if (typeof callback === 'function') callback(false);
            }
        }).fail(function() {
            showToast('❌ خطا در ارتباط با سرور', 'error');
            if (typeof callback === 'function') callback(false);
        });
    }

    // ===== تابع کپی با AJAX =====
    function duplicateTemplate(id, callback) {
        $.post(ajaxurl, {
            action: 'ezlens_duplicate_template',
            nonce: '<?php echo wp_create_nonce("ezlens_template_editor_nonce"); ?>',
            template_id: id
        }, function(response) {
            if (response.success) {
                showToast('✅ ' + response.data.message, 'success');
                if (typeof callback === 'function') callback(true, response.data.id);
            } else {
                showToast('❌ ' + response.data.message, 'error');
                if (typeof callback === 'function') callback(false);
            }
        }).fail(function() {
            showToast('❌ خطا در ارتباط با سرور', 'error');
            if (typeof callback === 'function') callback(false);
        });
    }

    // ===== دکمه حذف (با SweetAlert2) =====
    $(document).on('click', '.button-delete', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var id = $btn.data('id');
        var $row = $btn.closest('tr');

        Swal.fire({
            title: 'آیا مطمئن هستید؟',
            text: 'پالت با شناسه #' + id + ' حذف خواهد شد. این عمل قابل بازگشت نیست.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.text('⏳').prop('disabled', true);
                deleteTemplate(id, function(success) {
                    $btn.text('🗑️').prop('disabled', false);
                    if (success) {
                        $row.fadeOut(300, function() { $(this).remove(); });
                        // به‌روزرسانی تعداد
                        var count = parseInt($('#field-count').text()) || 0;
                        $('#field-count').text(count - 1);
                        if ($('#ezlens-table-body tr:visible').length === 0) {
                            $('#ezlens-table-body').html('<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:30px 0;">هیچ پالتی وجود ندارد.</td></tr>');
                        }
                    }
                });
            }
        });
    });

    // ===== دکمه کپی (با AJAX) =====
    $(document).on('click', '.button-duplicate', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var id = $btn.data('id');
        var $row = $btn.closest('tr');

        $btn.text('⏳').prop('disabled', true);
        duplicateTemplate(id, function(success, newId) {
            $btn.text('📋').prop('disabled', false);
            if (success && newId) {
                showToast('✅ پالت کپی شد. برای ویرایش کلیک کنید.', 'success');
                // بازآوری صفحه یا اضافه کردن ردیف جدید
                location.reload();
            }
        });
    });

    // ===== انتخاب همه =====
    $('#select-all').on('change', function() {
        $('.template-checkbox').prop('checked', $(this).prop('checked'));
    });

    // ===== Bulk Actions =====
    $('#bulk-apply').on('click', function() {
        var action = $('#bulk-action-select').val();
        var ids = [];
        $('.template-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (!action) {
            showToast('لطفاً یک اقدام گروهی انتخاب کنید.', 'info');
            return;
        }
        if (ids.length === 0) {
            showToast('هیچ پالتی انتخاب نشده است.', 'info');
            return;
        }

        if (action === 'delete') {
            Swal.fire({
                title: 'حذف گروهی',
                text: 'آیا از حذف ' + ids.length + ' پالت انتخاب‌شده مطمئن هستید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'بله، همه را حذف کن',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    var deleted = 0;
                    var total = ids.length;
                    ids.forEach(function(id) {
                        deleteTemplate(id, function(success) {
                            if (success) deleted++;
                            if (deleted === total) {
                                showToast('✅ ' + deleted + ' پالت با موفقیت حذف شد.', 'success');
                                location.reload();
                            }
                        });
                    });
                }
            });
        } else if (action === 'duplicate') {
            var duplicated = 0;
            var total = ids.length;
            ids.forEach(function(id) {
                duplicateTemplate(id, function(success) {
                    if (success) duplicated++;
                    if (duplicated === total) {
                        showToast('✅ ' + duplicated + ' پالت با موفقیت کپی شد.', 'success');
                        location.reload();
                    }
                });
            });
        }
    });

    // ===== دکمه رفرش =====
    $('#refresh-list').on('click', function() {
        location.reload();
    });

    // ===== فعال‌سازی دکمه‌های حذف و کپی در صورت اضافه شدن دینامیک =====
    console.log('✅ EzLens Template List loaded.');
});
</script>

<!-- ===== SweetAlert2 ===== -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>