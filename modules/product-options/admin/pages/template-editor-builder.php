<?php
if (!defined('ABSPATH')) exit;
?>

<div class="builder-container">
    <div class="builder-toolbar">
        <div class="toolbar-group">
            <span class="group-label">ورودی</span>
            <button type="button" class="btn-add-field" data-type="text">📝 متن</button>
            <button type="button" class="btn-add-field" data-type="number">🔢 عددی</button>
            <button type="button" class="btn-add-field" data-type="textarea">📄 متن بلند</button>
            <button type="button" class="btn-add-field" data-type="email">✉️ ایمیل</button>
            <button type="button" class="btn-add-field" data-type="phone">📞 تلفن</button>
        </div>
        <div class="toolbar-group">
            <span class="group-label">انتخاب</span>
            <button type="button" class="btn-add-field" data-type="select">📋 سلکت</button>
            <button type="button" class="btn-add-field" data-type="radio">⭕ رادیو</button>
            <button type="button" class="btn-add-field" data-type="checkbox">✅ چک‌باکس</button>
            <button type="button" class="btn-add-field" data-type="image_select">🖼️ تصویری</button>
        </div>
        <div class="toolbar-group">
            <span class="group-label">ویژه</span>
            <button type="button" class="btn-add-field" data-type="upload">📤 آپلود</button>
            <button type="button" class="btn-add-field" data-type="color">🎨 رنگ</button>
            <button type="button" class="btn-add-field" data-type="date">📅 تاریخ</button>
            <button type="button" class="btn-add-field" data-type="time">⏰ زمان</button>
        </div>
        <div class="toolbar-group">
            <span class="group-label">ساختار</span>
            <button type="button" class="btn-add-field" data-type="heading">📌 عنوان</button>
            <button type="button" class="btn-add-field" data-type="divider">➖ جداکننده</button>
            <button type="button" class="btn-add-field" data-type="spacer">⬜ فاصله</button>
            <button type="button" class="btn-add-field" data-type="group">📦 گروه</button>
            <button type="button" class="btn-add-field" data-type="html">🌐 HTML</button>
        </div>
    </div>

    <div class="layout-toolbar">
        <span class="layout-label">چیدمان:</span>
        <button type="button" class="layout-btn active" data-layout="1">1 ستون</button>
        <button type="button" class="layout-btn" data-layout="2">2 ستون</button>
        <button type="button" class="layout-btn" data-layout="3">3 ستون</button>
        <button type="button" class="layout-btn" data-layout="horizontal">افقی</button>
        <span class="layout-label" style="margin-left:16px;">فاصله:</span>
        <select id="builder-gap">
            <option value="small">کوچک</option>
            <option value="medium" selected>متوسط</option>
            <option value="large">بزرگ</option>
        </select>
    </div>

    <div class="builder-fields" id="builder-fields">
        <?php if (!empty($fields)): ?>
            <?php foreach ($fields as $key => $field): ?>
                <?php if (strpos($key, '_code_') === 0) continue; ?>
                <?php if (isset($field['_meta']) && $field['_meta'] === true) continue; ?>
                <?php echo render_field_editor($key, $field); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="builder-empty" style="display:<?php echo empty($fields) ? 'block' : 'none'; ?>;">
        <p>هنوز فیلدی اضافه نشده است. از دکمه‌های بالا یک فیلد اضافه کنید.</p>
    </div>
</div>

<style>
.builder-container { position: relative; }
.builder-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 10px 12px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-bottom: 12px;
}
.toolbar-group {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-right: 1px solid #e2e8f0;
}
.toolbar-group:last-child { border-right: none; }
.group-label { font-size: 10px; font-weight: 700; color: #64748b; margin-right: 4px; }
.btn-add-field {
    padding: 4px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    background: #fff;
    color: #334155;
    font-size: 11px;
    cursor: pointer;
}
.btn-add-field:hover { border-color: #2b6cb0; color: #2b6cb0; }

.layout-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #fff;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    margin-bottom: 12px;
}
.layout-label { font-size: 12px; font-weight: 600; color: #64748b; }
.layout-btn {
    padding: 3px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    background: #fff;
    color: #334155;
    font-size: 12px;
    cursor: pointer;
}
.layout-btn:hover { border-color: #2b6cb0; }
.layout-btn.active { background: #2b6cb0; color: #fff; border-color: #2b6cb0; }

.builder-fields {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 100px;
}
.builder-field {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
}
.builder-field:hover { border-color: #2b6cb0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.builder-field[data-width="half"] { width: calc(50% - 5px); }
.builder-field[data-width="third"] { width: calc(33.33% - 7px); }
.builder-field[data-width="quarter"] { width: calc(25% - 8px); }

.field-header {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    background: #f8fafc;
    cursor: move;
    flex-wrap: wrap;
}
.drag-handle { color: #cbd5e1; cursor: grab; font-size: 14px; }
.field-title { flex: 1; font-weight: 600; font-size: 13px; color: #0f172a; }
.field-type { font-size: 10px; color: #64748b; background: #e2e8f0; padding: 1px 8px; border-radius: 999px; }
.field-width select { font-size: 10px; padding: 1px 4px; border: 1px solid #e2e8f0; border-radius: 3px; }
.field-duplicate { color: #2b6cb0; }
.field-delete { color: #dc2626; }
.field-duplicate, .field-delete { background: none; border: none; cursor: pointer; font-size: 14px; padding: 0 4px; }

.field-body {
    padding: 10px 12px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.field-body .field-row { display: flex; flex-direction: column; gap: 3px; }
.field-body .field-row:has(.field-options-container),
.field-body .field-row:has(.field-children) { grid-column: span 2; }
.field-body .field-row label { font-size: 11px; font-weight: 500; color: #64748b; }
.field-body .field-row input,
.field-body .field-row select,
.field-body .field-row textarea {
    padding: 5px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 12px;
    background: #f8fafc;
}
.field-body .field-row input:focus,
.field-body .field-row select:focus,
.field-body .field-row textarea:focus {
    border-color: #2b6cb0;
    outline: none;
    background: #fff;
}

.field-settings {
    grid-column: span 2;
    display: none;
    border-top: 1px solid #e2e8f0;
    padding-top: 8px;
    margin-top: 4px;
}
.field-settings.active { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

.option-row {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 4px;
    align-items: center;
}
.option-row input { flex: 1; min-width: 60px; padding: 4px 6px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 11px; }
.option-row .option-image { min-width: 100px; }
.option-row .option-remove { background: none; border: none; color: #dc2626; cursor: pointer; padding: 0 4px; }
.option-add {
    padding: 3px 12px;
    border: 1px dashed #2b6cb0;
    border-radius: 4px;
    background: transparent;
    color: #2b6cb0;
    cursor: pointer;
    font-size: 12px;
    margin-top: 4px;
}

.field-children {
    grid-column: span 2;
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    padding: 8px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.btn-add-child-field {
    padding: 4px 12px;
    border: 1px dashed #2b6cb0;
    border-radius: 4px;
    background: transparent;
    color: #2b6cb0;
    cursor: pointer;
    font-size: 12px;
    align-self: flex-start;
}

.builder-empty { text-align: center; padding: 30px; color: #64748b; }
</style>

<script>
function getFieldHTML(type, label, index) {
    var defaultLabel = label || 'فیلد';
    var html = '<div class="builder-field" data-index="' + index + '" data-type="' + type + '" data-width="full">';
    html += '<div class="field-header">';
    html += '<span class="drag-handle">☰</span>';
    html += '<span class="field-title">' + defaultLabel + '</span>';
    html += '<span class="field-type">' + type + '</span>';
    html += '<span class="field-width"><select class="field-width-select"><option value="full">کامل</option><option value="half">نصف</option><option value="third">یک‌سوم</option><option value="quarter">یک‌چهارم</option></select></span>';
    html += '<button type="button" class="field-duplicate">📋</button>';
    html += '<button type="button" class="field-delete">✕</button>';
    html += '</div>';
    html += '<div class="field-body">';
    html += '<div class="field-row"><label>نام (شناسه)</label><input type="text" class="field-name" value="' + index + '" placeholder="شناسه یکتا"></div>';
    html += '<div class="field-row"><label>برچسب</label><input type="text" class="field-label" value="' + defaultLabel + '"></div>';
    html += '<div class="field-row"><label>متن راهنما</label><input type="text" class="field-placeholder" value=""></div>';
    html += '<div class="field-row"><label>قیمت اضافه</label><input type="number" class="field-price" value="0"></div>';
    html += '<div class="field-row"><label><input type="checkbox" class="field-required"> اجباری</label></div>';
    html += '<div class="field-settings" data-type="' + type + '">';
    switch (type) {
        case 'text': case 'email': case 'phone':
            html += '<div class="field-row"><label>حداکثر طول</label><input type="number" class="field-maxlength" value=""></div>';
            break;
        case 'number':
            html += '<div class="field-row"><label>حداقل</label><input type="number" class="field-min" value=""></div>';
            html += '<div class="field-row"><label>حداکثر</label><input type="number" class="field-max" value=""></div>';
            html += '<div class="field-row"><label>گام</label><input type="number" class="field-step" value="1" step="0.1"></div>';
            break;
        case 'textarea':
            html += '<div class="field-row"><label>تعداد ردیف</label><input type="number" class="field-rows" value="3"></div>';
            break;
        case 'upload':
            html += '<div class="field-row"><label>حداکثر حجم (MB)</label><input type="number" class="field-maxsize" value="5"></div>';
            html += '<div class="field-row"><label>پسوندهای مجاز</label><input type="text" class="field-extensions" value="jpg, jpeg, png, pdf" placeholder="jpg, png, pdf"></div>';
            break;
        case 'select': case 'radio': case 'checkbox': case 'image_select':
            html += '<div class="field-row field-options-row">';
            html += '<label>گزینه‌ها (هر گزینه با قیمت)</label>';
            html += '<div class="field-options-container">';
            html += '<div class="option-row"><input type="text" class="option-label" placeholder="برچسب"><input type="text" class="option-value" placeholder="مقدار">';
            if (type === 'image_select') html += '<input type="text" class="option-image" placeholder="آدرس تصویر">';
            html += '<input type="number" class="option-price" placeholder="قیمت"><button type="button" class="option-remove">✕</button></div>';
            html += '<button type="button" class="option-add">➕ افزودن گزینه</button>';
            html += '</div></div>';
            break;
        case 'heading':
            html += '<div class="field-row"><label>سطح</label><select class="field-heading-level"><option value="h1">H1</option><option value="h2">H2</option><option value="h3" selected>H3</option><option value="h4">H4</option></select></div>';
            break;
        case 'divider':
            html += '<div class="field-row"><label>ضخامت (px)</label><input type="number" class="field-divider-thickness" value="1"></div>';
            html += '<div class="field-row"><label>رنگ</label><input type="color" class="field-divider-color" value="#e2e8f0"></div>';
            break;
        case 'spacer':
            html += '<div class="field-row"><label>ارتفاع (px)</label><input type="number" class="field-spacer-height" value="20"></div>';
            break;
        case 'group':
            html += '<div class="field-row"><label>تعداد ستون‌ها</label><select class="field-group-columns"><option value="1">1</option><option value="2" selected>2</option><option value="3">3</option></select></div>';
            html += '<div class="field-row"><label>فاصله</label><select class="field-group-gap"><option value="small">کوچک</option><option value="medium" selected>متوسط</option><option value="large">بزرگ</option></select></div>';
            html += '<div class="field-children"><button type="button" class="btn-add-child-field" data-parent="' + index + '">➕ افزودن فیلد</button></div>';
            break;
        case 'html':
            html += '<div class="field-row"><label>کد HTML</label><textarea class="field-html-code" rows="3"><!-- کد HTML --></textarea></div>';
            break;
    }
    html += '</div></div></div>';
    return html;
}

jQuery(document).ready(function($) {
    // ===== اضافه کردن فیلد =====
    $('.btn-add-field').on('click', function() {
        var type = $(this).data('type');
        var labelMap = {
            'text': 'متن', 'number': 'عددی', 'textarea': 'متن بلند', 'email': 'ایمیل',
            'phone': 'تلفن', 'select': 'سلکت', 'radio': 'رادیو', 'checkbox': 'چک‌باکس',
            'image_select': 'تصویری', 'upload': 'آپلود', 'color': 'رنگ',
            'date': 'تاریخ', 'time': 'زمان', 'heading': 'عنوان',
            'divider': 'جداکننده', 'spacer': 'فاصله', 'group': 'گروه', 'html': 'HTML'
        };
        var index = 'field_' + Date.now();
        var html = getFieldHTML(type, labelMap[type] || type, index);
        $('#builder-fields').append(html);
        $('.builder-empty').hide();
        attachBuilderEvents();
        if (typeof updatePreview === 'function') updatePreview();
        if (typeof syncBuilderToCode === 'function') syncBuilderToCode();
        if (typeof updateFieldCount === 'function') updateFieldCount();
    });

    // ===== رویدادهای فیلدها (با Event Delegation) =====
    function attachBuilderEvents() {
        $('#builder-fields').off('click', '.field-delete').on('click', '.field-delete', function(e) {
            e.preventDefault();
            if (!confirm('آیا این فیلد حذف شود؟')) return;
            $(this).closest('.builder-field').remove();
            if ($('.builder-field:not(.builder-field .builder-field)').length === 0) {
                $('.builder-empty').show();
            }
            if (typeof updatePreview === 'function') updatePreview();
            if (typeof syncBuilderToCode === 'function') syncBuilderToCode();
            if (typeof updateFieldCount === 'function') updateFieldCount();
        });

        $('#builder-fields').off('click', '.field-duplicate').on('click', '.field-duplicate', function(e) {
            e.preventDefault();
            var $original = $(this).closest('.builder-field');
            var $clone = $original.clone();
            var newIndex = 'field_' + Date.now();
            $clone.attr('data-index', newIndex);
            $clone.find('.field-name').val(newIndex);
            $clone.find('.field-title').text($original.find('.field-label').val() || 'کپی');
            $original.after($clone);
            if (typeof updatePreview === 'function') updatePreview();
            if (typeof syncBuilderToCode === 'function') syncBuilderToCode();
            if (typeof updateFieldCount === 'function') updateFieldCount();
        });

        $('#builder-fields').off('change keyup', '.field-label, .field-name, .field-placeholder, .field-price, .field-required, .field-type-select, .field-min, .field-max, .field-step, .field-rows, .field-maxsize, .field-extensions, .field-heading-level, .field-divider-thickness, .field-divider-color, .field-spacer-height, .field-group-columns, .field-group-gap, .field-html-code, .option-label, .option-value, .option-image, .option-price, .field-width-select').on('change keyup', function() {
            var $field = $(this).closest('.builder-field');
            var label = $field.find('.field-label').val() || 'فیلد';
            $field.find('.field-title').text(label);
            if (typeof updatePreview === 'function') updatePreview();
            if (typeof syncBuilderToCode === 'function') syncBuilderToCode();
        });

        $('#builder-fields').off('click', '.option-add').on('click', '.option-add', function() {
            var $container = $(this).closest('.field-options-container');
            var $first = $container.find('.option-row').first();
            var $new = $first.clone();
            $new.find('input').val('');
            $(this).before($new);
            if (typeof updatePreview === 'function') updatePreview();
            if (typeof syncBuilderToCode === 'function') syncBuilderToCode();
        });

        $('#builder-fields').off('click', '.option-remove').on('click', '.option-remove', function() {
            var $container = $(this).closest('.field-options-container');
            if ($container.find('.option-row').length > 1) {
                $(this).closest('.option-row').remove();
                if (typeof updatePreview === 'function') updatePreview();
                if (typeof syncBuilderToCode === 'function') syncBuilderToCode();
            }
        });

        $('#builder-fields').off('click', '.btn-add-child-field').on('click', '.btn-add-child-field', function() {
            var parentIndex = $(this).data('parent');
            var index = 'child_' + Date.now();
            var html = getFieldHTML('text', 'فیلد فرزند', index);
            $(this).before(html);
            if (typeof updatePreview === 'function') updatePreview();
            if (typeof syncBuilderToCode === 'function') syncBuilderToCode();
            if (typeof updateFieldCount === 'function') updateFieldCount();
        });

        $('#builder-fields').off('dblclick', '.field-header').on('dblclick', '.field-header', function() {
            $(this).closest('.builder-field').find('.field-settings').toggleClass('active');
        });
    }

    attachBuilderEvents();
});
</script>