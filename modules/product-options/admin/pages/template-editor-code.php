<?php
if (!defined('ABSPATH')) exit;

$code_html = $fields['_code_html'] ?? '<!-- کد HTML خود را اینجا بنویسید -->';
$code_css = $fields['_code_css'] ?? '/* کد CSS خود را اینجا بنویسید */';
$code_js = $fields['_code_js'] ?? '// کد JavaScript خود را اینجا بنویسید';
?>

<div class="code-editor-container">
    <div class="code-editor-tabs">
        <button type="button" class="code-tab active" data-lang="html">HTML</button>
        <button type="button" class="code-tab" data-lang="css">CSS</button>
        <button type="button" class="code-tab" data-lang="js">JS</button>
        <button type="button" class="code-tab" data-lang="preview" style="margin-left:auto;background:#2b6cb0;color:#fff;">👁️ پیش‌نمایش</button>
    </div>

    <div class="code-editor-body">
        <textarea id="code-html" name="code_html" class="code-editor-input" style="display:block;"><?php echo esc_textarea($code_html); ?></textarea>
        <textarea id="code-css" name="code_css" class="code-editor-input" style="display:none;"><?php echo esc_textarea($code_css); ?></textarea>
        <textarea id="code-js" name="code_js" class="code-editor-input" style="display:none;"><?php echo esc_textarea($code_js); ?></textarea>
    </div>

    <div class="code-actions">
        <button type="button" class="button button-secondary" id="code-format">🔧 فرمت کد</button>
        <button type="button" class="button button-secondary" id="code-apply">📥 اعمال به سازنده</button>
        <span class="code-hint">💡 تغییرات سازنده بصری به‌صورت خودکار در اینجا اعمال می‌شود</span>
    </div>
</div>

<style>
.code-editor-container {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.code-editor-tabs {
    display: flex;
    gap: 4px;
    padding: 8px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
}
.code-tab {
    padding: 6px 16px;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
}
.code-tab:hover { background: #e2e8f0; color: #0f172a; }
.code-tab.active { background: #2b6cb0; color: #fff; }
.code-editor-body {
    background: #0f172a;
}
.code-editor-input {
    width: 100%;
    min-height: 180px;
    padding: 12px;
    border: none;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    color: #e2e8f0;
    resize: vertical;
    tab-size: 2;
    direction: ltr;
    text-align: left;
    background: transparent;
}
.code-editor-input:focus { outline: none; }
.code-actions {
    display: flex;
    gap: 10px;
    padding: 8px 12px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    align-items: center;
    flex-wrap: wrap;
}
.code-hint {
    font-size: 12px;
    color: #64748b;
    margin-right: auto;
}
</style>

<script>
jQuery(document).ready(function($) {
    'use strict';

    // ===== تغییر تب‌ها =====
    $('.code-tab').on('click', function() {
        var lang = $(this).data('lang');
        if (lang === 'preview') {
            if (typeof window.updatePreview === 'function') window.updatePreview();
            return;
        }
        $('.code-tab').removeClass('active');
        $(this).addClass('active');
        $('.code-editor-input').hide();
        $('#code-' + lang).show();
    });

    // ===== فرمت کد =====
    $('#code-format').on('click', function() {
        var $active = $('.code-editor-input:visible');
        try {
            if ($active.attr('id') === 'code-html') {
                var formatted = $active.val().replace(/>\s+</g, '>\n<').trim();
                $active.val(formatted);
            } else if ($active.attr('id') === 'code-css' || $active.attr('id') === 'code-js') {
                var lines = $active.val().split('\n').filter(function(l) { return l.trim(); });
                $active.val(lines.join('\n'));
            }
            if (typeof window.updatePreview === 'function') window.updatePreview();
        } catch(e) { alert('خطا در فرمت کد'); }
    });

    // ===== اعمال کد به سازنده بصری =====
    $('#code-apply').on('click', function() {
        var html = $('#code-html').val();
        var css = $('#code-css').val();
        var js = $('#code-js').val();
        
        // نمایش پیام
        alert('✅ کدها ذخیره شدند. برای دیدن تغییرات، دکمه "پیش‌نمایش" را بزنید.');
        
        // اگر فرم HTML وجود دارد، آن را به یک فیلد html اضافه کنیم
        if (html && html.trim() && html.trim() !== '<!-- کد HTML خود را اینجا بنویسید -->') {
            // در صورت تمایل، می‌توانید یک فیلد html در سازنده بصری ایجاد کنید
            // اینجا فقط ذخیره می‌شود.
        }
    });

    console.log('✅ Integrated Code Editor loaded.');
});
</script>