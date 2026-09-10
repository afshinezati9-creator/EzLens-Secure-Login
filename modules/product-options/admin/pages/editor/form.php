<?php
if (!defined('ABSPATH')) exit;

// بررسی وجود کلاس مدیریت
$manager = EzLens_Product_Options_Template_Manager::get_instance();

// تشخیص حالت (add یا edit)
$action = isset($_GET['action']) ? $_GET['action'] : 'add';
$template_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$template = null;

if ($action === 'edit' && $template_id > 0) {
    $template = $manager->get($template_id);
    if (!$template) {
        echo '<div class="wrap"><h1>🧩 ویرایش پالت</h1><p style="color:#dc2626;">پالت یافت نشد.</p></div>';
        return;
    }
}

// عنوان صفحه
$page_title = ($action === 'edit') ? 'ویرایش پالت' : 'افزودن پالت جدید';
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    <hr class="wp-header-end">

    <form id="ezlens-template-form" method="post" action="">
        <?php wp_nonce_field('ezlens_template_form_action', 'ezlens_template_nonce'); ?>
        <input type="hidden" name="template_id" value="<?php echo esc_attr($template_id); ?>">
        <input type="hidden" name="action_type" value="<?php echo esc_attr($action); ?>">

        <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:16px;">
            <!-- ستون اصلی فرم -->
            <div style="flex:3;min-width:300px;">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="template_title">عنوان پالت <span style="color:#dc2626;">*</span></label></th>
                        <td>
                            <input type="text" id="template_title" name="template_title" 
                                   value="<?php echo esc_attr($template ? $template['title'] : ''); ?>" 
                                   class="regular-text" required>
                            <p class="description">یک عنوان مشخص برای این پالت انتخاب کنید.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="template_description">توضیحات</label></th>
                        <td>
                            <textarea id="template_description" name="template_description" 
                                      rows="3" class="large-text"><?php echo esc_textarea($template ? $template['description'] : ''); ?></textarea>
                            <p class="description">توضیح کوتاهی درباره کاربرد این پالت.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="template_status">وضعیت</label></th>
                        <td>
                            <select id="template_status" name="template_status">
                                <option value="active" <?php selected($template ? $template['status'] : 'active', 'active'); ?>>فعال</option>
                                <option value="inactive" <?php selected($template ? $template['status'] : '', 'inactive'); ?>>غیرفعال</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>فیلدها (JSON)</label></th>
                        <td>
                            <textarea id="template_fields_json" name="template_fields_json" 
                                      rows="10" class="large-text code"><?php 
                                $fields = $template ? json_encode($template['fields'], JSON_PRETTY_PRINT) : '[]';
                                echo esc_textarea($fields); 
                            ?></textarea>
                            <p class="description">
                                فیلدها را به صورت JSON وارد کنید. برای ساخت بصری از بخش زیر استفاده کنید.
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary" id="ezlens-save-template">
                        💾 ذخیره پالت
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=ezlens-product-options'); ?>" class="button">
                        ↩️ بازگشت به لیست
                    </a>
                </p>
            </div>

            <!-- ستون جانبی: سازنده بصری ساده -->
            <div style="flex:2;min-width:250px;background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0;">
                <h3 style="margin-top:0;">🧰 افزودن فیلد</h3>
                <p style="color:#64748b;font-size:13px;">برای ساخت سریع فیلد، نوع را انتخاب کنید و روی «افزودن فیلد» کلیک کنید.</p>

                <div style="margin-bottom:12px;">
                    <label style="display:block;font-weight:600;font-size:13px;">نوع فیلد</label>
                    <select id="field-type-selector" style="width:100%;padding:6px 8px;">
                        <option value="text">متن (Text)</option>
                        <option value="number">عدد (Number)</option>
                        <option value="select">انتخاب (Select)</option>
                        <option value="radio">رادیو (Radio)</option>
                        <option value="checkbox">چک‌باکس (Checkbox)</option>
                        <option value="upload">آپلود فایل (Upload)</option>
                        <option value="color">رنگ (Color)</option>
                        <option value="date">تاریخ (Date)</option>
                        <option value="time">زمان (Time)</option>
                    </select>
                </div>

                <button type="button" class="button" id="add-field-btn" style="width:100%;text-align:center;">
                    ➕ افزودن فیلد
                </button>

                <hr style="margin:16px 0;">

                <div id="fields-preview" style="max-height:400px;overflow-y:auto;">
                    <?php if ($template && !empty($template['fields'])): ?>
                        <p style="font-size:12px;color:#64748b;"><?php echo count($template['fields']); ?> فیلد موجود است.</p>
                        <ul style="list-style:none;padding:0;">
                        <?php foreach ($template['fields'] as $index => $field): ?>
                            <li style="padding:4px 8px;background:#fff;border-radius:4px;border:1px solid #e2e8f0;margin-bottom:4px;display:flex;justify-content:space-between;align-items:center;">
                                <span><strong><?php echo esc_html($field['label'] ?? 'بدون عنوان'); ?></strong> (<?php echo esc_html($field['type'] ?? ''); ?>)</span>
                                <button type="button" class="button button-small remove-field" data-index="<?php echo $index; ?>">🗑️</button>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="color:#94a3b8;font-size:13px;">هنوز فیلدی اضافه نشده است.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
#ezlens-template-form .code{
    font-family: monospace;
    font-size: 13px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // ===== افزودن فیلد (ساده) =====
    $('#add-field-btn').on('click', function() {
        var type = $('#field-type-selector').val();
        var label = prompt('عنوان فیلد را وارد کنید:', '');
        if (label === null) return;
        if (label.trim() === '') {
            alert('عنوان نمی‌تواند خالی باشد.');
            return;
        }
        
        // ساخت فیلد پیش‌فرض
        var field = {
            type: type,
            label: label.trim(),
            required: false,
            placeholder: '',
            options: [],
            price: 0,
            settings: {}
        };

        // برای select/radio/checkbox نیاز به گزینه‌ها داریم
        if (['select', 'radio', 'checkbox'].includes(type)) {
            var options_str = prompt('گزینه‌ها را با کاما وارد کنید (مثلاً: آبی, قرمز, سبز):', '');
            if (options_str !== null && options_str.trim() !== '') {
                field.options = options_str.split(',').map(function(s) { 
                    return { value: s.trim(), label: s.trim(), price: 0 };
                });
            }
        }

        // دریافت JSON فعلی
        var current_json = $('#template_fields_json').val();
        var fields = [];
        try {
            fields = JSON.parse(current_json);
            if (!Array.isArray(fields)) fields = [];
        } catch(e) {
            fields = [];
        }

        fields.push(field);
        var new_json = JSON.stringify(fields, null, 4);
        $('#template_fields_json').val(new_json);
        
        // به‌روزرسانی پیش‌نمایش
        updatePreview(fields);
    });

    // ===== حذف فیلد =====
    $(document).on('click', '.remove-field', function() {
        var index = $(this).data('index');
        var current_json = $('#template_fields_json').val();
        var fields = [];
        try {
            fields = JSON.parse(current_json);
            if (!Array.isArray(fields)) fields = [];
        } catch(e) {
            fields = [];
        }
        if (index >= 0 && index < fields.length) {
            fields.splice(index, 1);
            var new_json = JSON.stringify(fields, null, 4);
            $('#template_fields_json').val(new_json);
            updatePreview(fields);
        }
    });

    // ===== به‌روزرسانی پیش‌نمایش =====
    function updatePreview(fields) {
        var html = '';
        if (fields.length === 0) {
            html = '<p style="color:#94a3b8;font-size:13px;">هنوز فیلدی اضافه نشده است.</p>';
        } else {
            html = '<p style="font-size:12px;color:#64748b;">' + fields.length + ' فیلد موجود است.</p>';
            html += '<ul style="list-style:none;padding:0;">';
            fields.forEach(function(field, idx) {
                html += '<li style="padding:4px 8px;background:#fff;border-radius:4px;border:1px solid #e2e8f0;margin-bottom:4px;display:flex;justify-content:space-between;align-items:center;">';
                html += '<span><strong>' + escHtml(field.label || 'بدون عنوان') + '</strong> (' + escHtml(field.type || '') + ')</span>';
                html += '<button type="button" class="button button-small remove-field" data-index="' + idx + '">🗑️</button>';
                html += '</li>';
            });
            html += '</ul>';
        }
        $('#fields-preview').html(html);
    }

    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ===== ارسال فرم با AJAX (برای ذخیره) =====
    $('#ezlens-template-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submit = $('#ezlens-save-template');
        $submit.prop('disabled', true).text('⏳ در حال ذخیره...');

        var formData = new FormData(this);
        formData.append('action', 'ezlens_save_template');
        formData.append('nonce', '<?php echo wp_create_nonce('ezlens_template_ajax'); ?>');
        formData.append('fields_json', $('#template_fields_json').val());

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('✅ ' + response.data.message);
                    window.location.href = '<?php echo admin_url('admin.php?page=ezlens-product-options'); ?>';
                } else {
                    alert('❌ ' + response.data.message);
                }
            },
            error: function() {
                alert('❌ خطا در ارتباط با سرور.');
            },
            complete: function() {
                $submit.prop('disabled', false).text('💾 ذخیره پالت');
            }
        });
    });
});
</script>