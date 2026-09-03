<?php
if (!defined('ABSPATH')) exit;
?>

<div class="preview-card">
    <h3>
        📱 پیش‌نمایش زنده
        <button type="button" id="refresh-preview" class="button button-small" style="float:left;">🔄</button>
    </h3>

    <div id="preview-container" class="preview-box preview-desktop">
        <div class="preview-placeholder">فیلدها در اینجا نمایش داده می‌شوند.</div>
    </div>

    <div class="preview-tools">
        <select id="preview-device">
            <option value="desktop">💻 دسکتاپ</option>
            <option value="tablet">📱 تبلت</option>
            <option value="mobile">📱 موبایل</option>
        </select>
        <span class="preview-count" id="preview-field-count">۰ فیلد</span>
    </div>
</div>

<style>
.preview-card {
    background: #fff;
    border-radius: 10px;
    padding: 16px 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.preview-card h3 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: #0f172a;
}
.preview-box {
    flex: 1;
    min-height: 250px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    margin-bottom: 10px;
    transition: all 0.3s;
    overflow: auto;
}
.preview-box.preview-desktop { max-width: 100%; }
.preview-box.preview-tablet { max-width: 768px; margin: 0 auto; }
.preview-box.preview-mobile { max-width: 375px; margin: 0 auto; }

.preview-box .preview-field {
    padding: 6px 0;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
}
.preview-box .preview-field .label { font-weight: 500; color: #334155; }
.preview-box .preview-field .value { color: #64748b; font-size: 12px; }

.preview-placeholder {
    color: #64748b;
    text-align: center;
    padding: 30px 0;
    font-size: 13px;
}

.preview-tools {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-top: 8px;
    border-top: 1px solid #e2e8f0;
}
.preview-count { font-size: 12px; color: #64748b; margin-right: auto; }
</style>

<script>
jQuery(document).ready(function($) {
    'use strict';

    // ===== تعریف تابع getFieldsData (برای رفع خطا) =====
    window.getFieldsData = function() {
        var fields = [];
        $('.builder-field:not(.builder-field .builder-field)').each(function() {
            var $f = $(this);
            var type = $f.data('type') || 'text';
            var data = {
                type: type,
                label: $f.find('.field-label').val() || 'فیلد',
                name: $f.find('.field-name').val() || 'field_' + Date.now(),
                placeholder: $f.find('.field-placeholder').val() || '',
                required: $f.find('.field-required').prop('checked'),
                price: parseFloat($f.find('.field-price').val()) || 0,
                width: $f.find('.field-width-select').val() || 'full'
            };
            fields.push(data);
        });
        return fields;
    };

    // ===== تغییر دستگاه =====
    $('#preview-device').on('change', function() {
        var device = $(this).val();
        var $box = $('#preview-container');
        $box.removeClass('preview-desktop preview-tablet preview-mobile');
        $box.addClass('preview-' + device);
    });

    // ===== به‌روزرسانی پیش‌نمایش =====
    window.updatePreview = function() {
        var preview = $('#preview-container');
        var fields = window.getFieldsData ? window.getFieldsData() : [];
        
        if (!fields || fields.length === 0) {
            preview.html('<div class="preview-placeholder">هنوز فیلدی اضافه نشده است.</div>');
            $('#preview-field-count').text('۰ فیلد');
            return;
        }

        var html = '<div style="display:flex;flex-wrap:wrap;gap:8px;">';
        fields.forEach(function(f) {
            var width = f.width === 'half' ? 'calc(50% - 4px)' : 
                       f.width === 'third' ? 'calc(33.33% - 6px)' : 
                       f.width === 'quarter' ? 'calc(25% - 6px)' : '100%';
            html += '<div class="preview-field" style="width:' + width + ';">';
            html += '<span class="label">' + escHtml(f.label || 'فیلد') + (f.required ? ' <span style="color:#dc2626;">*</span>' : '') + '</span>';
            if (f.price > 0) {
                html += '<span class="value" style="color:#2b6cb0;font-weight:600;">+' + f.price + ' تومان</span>';
            }
            html += '</div>';
        });
        html += '</div>';
        preview.html(html);
        $('#preview-field-count').text(fields.length + ' فیلد');

        // همگام‌سازی با ویرایشگر کد
        if (typeof window.syncBuilderToCode === 'function') {
            window.syncBuilderToCode();
        }
    };

    // ===== دکمه رفرش =====
    $('#refresh-preview').on('click', function() {
        if (typeof window.updatePreview === 'function') window.updatePreview();
        $(this).text('✅');
        setTimeout(function() { $('#refresh-preview').text('🔄'); }, 1000);
    });

    // ===== همگام‌سازی با ویرایشگر کد (تولید HTML/CSS/JS) =====
    window.syncBuilderToCode = function() {
        var fields = window.getFieldsData ? window.getFieldsData() : [];
        var html = '', css = '', js = '';

        if (fields.length === 0) {
            // اگر فیلدی وجود نداشت، خالی نگذار
            return;
        }

        fields.forEach(function(f, i) {
            var name = 'field_' + (i+1);
            var type = f.type || 'text';
            html += '<div class="field-item" data-type="' + type + '">\n';
            html += '  <label for="' + name + '">' + (f.label || 'فیلد') + '</label>\n';
            
            switch (type) {
                case 'textarea':
                    html += '  <textarea id="' + name + '" name="' + name + '" placeholder="' + (f.placeholder || '') + '"></textarea>\n';
                    break;
                case 'select':
                    html += '  <select id="' + name + '" name="' + name + '">\n';
                    html += '    <option value="">انتخاب کنید...</option>\n';
                    html += '  </select>\n';
                    break;
                case 'radio':
                case 'checkbox':
                    html += '  <div class="options">\n';
                    html += '    <label><input type="' + type + '" name="' + name + '" value="1"> گزینه ۱</label>\n';
                    html += '  </div>\n';
                    break;
                case 'upload':
                    html += '  <input type="file" id="' + name + '" name="' + name + '">\n';
                    break;
                case 'color':
                    html += '  <input type="color" id="' + name + '" name="' + name + '" value="#2b6cb0">\n';
                    break;
                case 'date':
                    html += '  <input type="date" id="' + name + '" name="' + name + '">\n';
                    break;
                case 'time':
                    html += '  <input type="time" id="' + name + '" name="' + name + '">\n';
                    break;
                case 'number':
                    html += '  <input type="number" id="' + name + '" name="' + name + '" step="1" min="0">\n';
                    break;
                case 'heading':
                    html += '  <h3 class="field-heading">' + (f.label || 'عنوان') + '</h3>\n';
                    break;
                case 'divider':
                    html += '  <hr class="field-divider">\n';
                    break;
                case 'spacer':
                    html += '  <div class="field-spacer" style="height:20px;"></div>\n';
                    break;
                case 'html':
                    html += '  <div class="field-html">\n';
                    html += '    <p>کد HTML سفارشی</p>\n';
                    html += '  </div>\n';
                    break;
                default:
                    html += '  <input type="text" id="' + name + '" name="' + name + '" placeholder="' + (f.placeholder || '') + '">\n';
            }
            html += '</div>\n\n';

            css += '.field-item[data-type="' + type + '"] { margin-bottom: 12px; }\n';
            css += '.field-item label { display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; }\n';
            css += '.field-item input, .field-item textarea, .field-item select { width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px; background: #f8fafc; }\n';
            css += '.field-item .field-heading { margin: 12px 0 8px; color: #0f172a; }\n';
            css += '.field-item .field-divider { border: none; border-top: 1px solid #e2e8f0; margin: 8px 0; }\n';
            css += '.field-item .field-group { border: 1px dashed #cbd5e1; padding: 12px; border-radius: 8px; background: #f8fafc; }\n';
            css += '.field-item .options { display: flex; gap: 12px; flex-wrap: wrap; }\n';
            css += '.field-item .options label { display: flex; align-items: center; gap: 4px; font-weight: 400; cursor: pointer; }\n';

            js += '// اسکریپت برای فیلد ' + (i+1) + '\n';
            js += 'document.querySelectorAll("[name=\\"" + name + "\\"]").forEach(function(el) {\n';
            js += '  el.addEventListener("change", function() {\n';
            js += '    console.log("فیلد تغییر کرد:", this.value);\n';
            js += '  });\n';
            js += '});\n\n';
        });

        // اعمال به ویرایشگر کد (فقط اگر چیزی تولید شده باشد)
        if (html) $('#code-html').val(html);
        if (css) $('#code-css').val(css);
        if (js) $('#code-js').val(js);
    };

    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ===== بارگذاری اولیه =====
    setTimeout(function() {
        if (typeof window.updatePreview === 'function') window.updatePreview();
    }, 200);
});
</script>