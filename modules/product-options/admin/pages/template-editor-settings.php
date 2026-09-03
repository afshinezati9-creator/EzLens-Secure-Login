<?php
if (!defined('ABSPATH')) exit;
?>

<div class="card">
    <h3>⚙️ تنظیمات CSS</h3>
    <div class="form-group">
        <label for="custom-css-editor">🎨 CSS اختصاصی پالت</label>
        <textarea id="custom-css-editor" name="custom_css" rows="6" class="code-editor-input"><?php echo esc_textarea($custom_css); ?></textarea>
        <button type="button" class="button button-secondary" id="apply-custom-css" style="margin-top:6px;">✅ اعمال CSS</button>
    </div>
</div>

<div class="card">
    <h3>🎭 تم‌های آماده</h3>
    <div class="theme-grid">
        <button type="button" class="theme-btn" data-theme="default">پیش‌فرض</button>
        <button type="button" class="theme-btn" data-theme="dark">🌙 تیره</button>
        <button type="button" class="theme-btn" data-theme="glass">🪟 شیشه‌ای</button>
        <button type="button" class="theme-btn" data-theme="minimal">✨ مینیمال</button>
    </div>
    <p class="description">با کلیک روی هر تم، CSS آن به ویرایشگر اضافه می‌شود.</p>
</div>

<div class="card">
    <h3>⚙️ تنظیمات پیشرفته</h3>
    <div class="settings-grid">
        <label><input type="checkbox" id="setting-rtl" checked> پشتیبانی RTL</label>
        <label><input type="checkbox" id="setting-animation"> انیمیشن ورود</label>
    </div>
</div>

<style>
.card {
    background: #fff;
    border-radius: 10px;
    padding: 16px 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 16px;
}
.card h3 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: #0f172a;
}
.card .form-group { margin-bottom: 0; }
.card .code-editor-input {
    width: 100%;
    min-height: 100px;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    background: #0f172a;
    color: #e2e8f0;
    resize: vertical;
    direction: ltr;
    text-align: left;
}
.card .code-editor-input:focus { outline: none; border-color: #2b6cb0; }
.card .description { font-size: 12px; color: #64748b; margin-top: 4px; }

.theme-grid {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin: 8px 0;
}
.theme-btn {
    padding: 6px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    color: #334155;
    font-size: 13px;
    cursor: pointer;
}
.theme-btn:hover { border-color: #2b6cb0; background: #f8fafc; }

.settings-grid {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    padding: 8px 0;
}
.settings-grid label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #334155;
    cursor: pointer;
}
</style>

<script>
jQuery(document).ready(function($) {
    // ===== اعمال CSS اختصاصی =====
    $('#apply-custom-css').on('click', function() {
        var css = $('#custom-css-editor').val();
        if (typeof updatePreview === 'function') {
            updatePreview();
            alert('✅ CSS اختصاصی ذخیره شد.');
        }
    });

    // ===== تم‌های آماده =====
    var themes = {
        'default': '/* استایل پیش‌فرض */',
        'dark': '.ezlens-product-options-wrapper { background: #1a202c; color: #e2e8f0; } .ezlens-options-title { border-color: #2d3748; color: #f1f5f9; }',
        'glass': '.ezlens-product-options-wrapper { background: rgba(255,255,255,0.6); backdrop-filter: blur(10px); }',
        'minimal': '.ezlens-product-options-wrapper { border: none; box-shadow: none; padding: 0; } .ezlens-options-title { font-size: 14px; color: #64748b; }'
    };
    $('.theme-btn').on('click', function() {
        var theme = $(this).data('theme');
        if (themes[theme]) {
            $('#custom-css-editor').val(themes[theme]);
            if (typeof updatePreview === 'function') updatePreview();
        }
    });

    // ===== تنظیم RTL =====
    $('#setting-rtl').on('change', function() {
        if (typeof updatePreview === 'function') updatePreview();
    });

    console.log('✅ Settings panel loaded.');
});
</script>