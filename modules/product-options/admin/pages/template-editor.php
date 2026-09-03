<?php
if (!defined('ABSPATH')) exit;

$manager = EzLens_Product_Options_Template_Manager::get_instance();

// تشخیص حالت (افزودن یا ویرایش)
$template_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $template_id > 0;

// اگر ویرایش است، داده‌ها را از دیتابیس بگیر
$template_data = null;
if ($is_edit) {
    $template_data = $manager->get($template_id);
    if (!$template_data) {
        echo '<div class="wrap"><h1>🧩 ویرایش پالت</h1><p style="color:#dc2626;">پالت یافت نشد.</p></div>';
        return;
    }
}

// مقداردهی پیش‌فرض
$title = $template_data['title'] ?? '';
$description = $template_data['description'] ?? '';
$fields = $template_data['fields'] ?? [];
$status = $template_data['status'] ?? 'active';
$custom_css = $template_data['custom_css'] ?? '';

// دریافت کدهای ذخیره‌شده از فیلدها (با پیشوند _code_)
$code_html = $fields['_code_html'] ?? '';
$code_css  = $fields['_code_css'] ?? '';
$code_js   = $fields['_code_js'] ?? '';

// عملگرهای AJAX
$ajax_url = admin_url('admin-ajax.php');
$nonce = wp_create_nonce('ezlens_template_editor_nonce');
?>

<div class="wrap ezlens-template-editor">
    <!-- هدر با دکمه ذخیره -->
    <div class="ezlens-editor-header">
        <h1 class="wp-heading-inline">
            <?php echo $is_edit ? '✏️ ویرایش پالت' : '➕ افزودن پالت جدید'; ?>
        </h1>
        <a href="<?php echo admin_url('admin.php?page=ezlens-product-options'); ?>" class="page-title-action">← بازگشت به لیست</a>
        <button type="submit" form="template-editor-form" class="button button-primary" style="margin-right:10px;">💾 ذخیره پالت</button>
    </div>
    <hr class="wp-header-end">

    <form id="template-editor-form" method="post" data-ajaxurl="<?php echo esc_url($ajax_url); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
        <input type="hidden" name="template_id" value="<?php echo $template_id; ?>">
        <input type="hidden" name="action" value="ezlens_save_template">

        <!-- چیدمان جدید: ۳ ستون -->
        <div class="editor-grid-3">
            <!-- ستون چپ: سازنده بصری + ویرایشگر کد -->
            <div class="editor-left">
                <!-- عنوان و توضیحات -->
                <div class="form-group">
                    <label for="template_title">عنوان پالت <span class="required">*</span></label>
                    <input type="text" id="template_title" name="title" value="<?php echo esc_attr($title); ?>" placeholder="مثلاً: ویژگی‌های عینک آفتابی" required>
                </div>
                <div class="form-group">
                    <label for="template_description">توضیحات</label>
                    <textarea id="template_description" name="description" rows="2" placeholder="توضیح مختصری درباره این پالت"><?php echo esc_textarea($description); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label>وضعیت</label>
                        <div class="status-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="status" value="active" <?php checked($status, 'active'); ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="status-label"><?php echo $status === 'active' ? 'فعال' : 'غیرفعال'; ?></span>
                        </div>
                    </div>
                    <div class="form-group half">
                        <label>تم CSS</label>
                        <select id="css-theme-select">
                            <option value="default">پیش‌فرض</option>
                            <option value="dark">تیره</option>
                            <option value="glass">شیشه‌ای</option>
                            <option value="minimal">مینیمال</option>
                        </select>
                    </div>
                </div>

                <!-- ===== بخش سازنده بصری ===== -->
                <div class="editor-section">
                    <div class="section-header">
                        <h2>🎨 سازنده بصری</h2>
                        <span class="badge">بدون کد</span>
                        <button type="button" class="toggle-section" data-target="visual-builder">▾</button>
                    </div>
                    <div id="visual-builder" class="section-body">
                        <?php include __DIR__ . '/template-editor-builder.php'; ?>
                    </div>
                </div>

                <!-- ===== بخش ویرایشگر کد یکپارچه (با فیلدهای مخفی) ===== -->
                <div class="editor-section">
                    <div class="section-header">
                        <h2>💻 ویرایشگر کد یکپارچه</h2>
                        <span class="badge">HTML + CSS + JS</span>
                        <button type="button" class="toggle-section" data-target="code-editor">▾</button>
                    </div>
                    <div id="code-editor" class="section-body">
                        <!-- ===== تب‌های ویرایشگر کد ===== -->
                        <div class="code-tabs">
                            <button type="button" class="code-tab active" data-lang="html">HTML</button>
                            <button type="button" class="code-tab" data-lang="css">CSS</button>
                            <button type="button" class="code-tab" data-lang="js">JS</button>
                        </div>

                        <!-- ===== ویرایشگرهای کد ===== -->
                        <div class="code-editors">
                            <textarea id="code-html" name="code_html" class="code-editor-input" style="display:block;"><?php echo esc_textarea($code_html); ?></textarea>
                            <textarea id="code-css" name="code_css" class="code-editor-input" style="display:none;"><?php echo esc_textarea($code_css); ?></textarea>
                            <textarea id="code-js" name="code_js" class="code-editor-input" style="display:none;"><?php echo esc_textarea($code_js); ?></textarea>
                        </div>

                        <div class="code-actions">
                            <button type="button" class="button button-secondary" id="code-format">🔧 فرمت کد</button>
                            <button type="button" class="button button-secondary" id="code-apply">⬇️ اعمال به سازنده</button>
                            <span class="code-hint">💡 تغییرات در سازنده بصری به‌صورت خودکار به اینجا اضافه می‌شود</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ستون وسط: پیش‌نمایش -->
            <div class="editor-center">
                <?php include __DIR__ . '/template-editor-preview.php'; ?>
            </div>

            <!-- ستون راست: تنظیمات و دکمه‌ها -->
            <div class="editor-right">
                <?php include __DIR__ . '/template-editor-settings.php'; ?>
                <div class="card actions">
                    <button type="submit" class="button button-primary btn-save" style="width:100%;padding:12px;font-size:16px;font-weight:600;">💾 ذخیره پالت</button>
                    <button type="button" id="btn-duplicate" class="button button-secondary" style="width:100%;display:<?php echo $is_edit ? 'block' : 'none'; ?>;">📋 کپی پالت</button>
                    <div id="save-status" class="save-status"></div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ===== استایل‌های اصلی ===== -->
<style>
.ezlens-template-editor {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 10px;
}
.ezlens-template-editor .required { color: #dc2626; }

/* هدر */
.ezlens-editor-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}
.ezlens-editor-header .button-primary {
    margin-right: auto;
}

/* چیدمان ۳ ستون */
.editor-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 280px;
    gap: 20px;
    margin-top: 16px;
}
@media (max-width: 1200px) {
    .editor-grid-3 {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 780px) {
    .editor-grid-3 {
        grid-template-columns: 1fr;
    }
}

.editor-left {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
}
.editor-center {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    min-height: 400px;
}
.editor-right {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #334155;
    margin-bottom: 4px;
}
.form-group input[type="text"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: #2b6cb0;
    outline: none;
    box-shadow: 0 0 0 3px rgba(43,108,176,0.1);
    background: #fff;
}

.form-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.form-row .half { flex: 1; min-width: 150px; }

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    cursor: pointer;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-switch .slider {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: #cbd5e1;
    border-radius: 24px;
    transition: 0.3s;
}
.toggle-switch .slider::before {
    content: '';
    position: absolute;
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: 0.3s;
}
.toggle-switch input:checked + .slider { background: #2b6cb0; }
.toggle-switch input:checked + .slider::before { transform: translateX(20px); }

.status-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
}
.status-label { font-size: 14px; color: #334155; }

.editor-section {
    margin-top: 20px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}
.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    cursor: pointer;
}
.section-header h2 { font-size: 15px; font-weight: 600; margin: 0; color: #0f172a; }
.section-header .badge {
    font-size: 10px;
    padding: 2px 10px;
    border-radius: 999px;
    background: #2b6cb0;
    color: #fff;
    font-weight: 600;
}
.section-header .toggle-section {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #64748b;
}
.section-body { padding: 16px; display: block; }
.section-body.collapsed { display: none; }

/* سایدبار راست */
.editor-right .card {
    background: #fff;
    border-radius: 10px;
    padding: 16px 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
}
.editor-right .card h3 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: #0f172a;
}

.actions { display: flex; flex-direction: column; gap: 10px; }
.btn-save { width: 100%; padding: 12px; font-size: 16px; font-weight: 600; }
.save-status {
    font-size: 13px;
    text-align: center;
    min-height: 24px;
}
.save-status.success { color: #16a34a; }
.save-status.error { color: #dc2626; }

/* ===== استایل‌های ویرایشگر کد ===== */
.code-tabs {
    display: flex;
    gap: 4px;
    padding: 6px 0;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 8px;
    flex-wrap: wrap;
}
.code-tab {
    padding: 4px 14px;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.code-tab:hover { background: #e2e8f0; color: #0f172a; }
.code-tab.active { background: #2b6cb0; color: #fff; }

.code-editor-input {
    width: 100%;
    min-height: 180px;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    background: #0f172a;
    color: #e2e8f0;
    resize: vertical;
    direction: ltr;
    text-align: left;
}
.code-editor-input:focus { outline: none; border-color: #2b6cb0; }

.code-actions {
    display: flex;
    gap: 10px;
    padding-top: 10px;
    flex-wrap: wrap;
    align-items: center;
}
.code-hint {
    font-size: 12px;
    color: #64748b;
    margin-right: auto;
}
</style>

<script>
jQuery(document).ready(function($) {
    // ===== دکمه‌های تاگل بخش‌ها =====
    $('.toggle-section').on('click', function() {
        var target = $(this).data('target');
        $('#' + target).toggleClass('collapsed');
        $(this).text($('#' + target).hasClass('collapsed') ? '▸' : '▾');
    });

    // ===== وضعیت toggle =====
    $('.toggle-switch input[type="checkbox"]').on('change', function() {
        var label = $(this).closest('.status-toggle').find('.status-label');
        label.text($(this).prop('checked') ? 'فعال' : 'غیرفعال');
    });

    // ===== انتخاب تم =====
    $('#css-theme-select').on('change', function() {
        var theme = $(this).val();
        var themes = {
            'default': '/* استایل پیش‌فرض */',
            'dark': '.ezlens-product-options-wrapper { background: #1a202c; color: #e2e8f0; } .ezlens-options-title { border-color: #2d3748; color: #f1f5f9; }',
            'glass': '.ezlens-product-options-wrapper { background: rgba(255,255,255,0.6); backdrop-filter: blur(10px); }',
            'minimal': '.ezlens-product-options-wrapper { border: none; box-shadow: none; padding: 0; }'
        };
        $('#custom-css-editor').val(themes[theme] || '');
        if (typeof updatePreview === 'function') updatePreview();
    });

    // ===== کپی پالت =====
    $('#btn-duplicate').on('click', function() {
        if (!confirm('آیا از این پالت کپی تهیه شود؟')) return;
        var templateId = $('input[name="template_id"]').val();
        $.post(ajaxurl, {
            action: 'ezlens_duplicate_template',
            nonce: '<?php echo wp_create_nonce("ezlens_template_editor_nonce"); ?>',
            template_id: templateId
        }, function(response) {
            if (response.success) {
                window.location.href = '?page=ezlens-product-options&action=edit&id=' + response.data.id;
            } else {
                alert('خطا: ' + response.data.message);
            }
        });
    });

    // ===== تغییر تب‌های ویرایشگر کد =====
    $('.code-tab').on('click', function() {
        var lang = $(this).data('lang');
        $('.code-tab').removeClass('active');
        $(this).addClass('active');
        $('.code-editor-input').hide();
        $('#code-' + lang).show();
    });

    // ===== فرمت کد =====
    $('#code-format').on('click', function() {
        var $active = $('.code-editor-input:visible');
        try {
            var val = $active.val();
            if ($active.attr('id') === 'code-html') {
                var formatted = val.replace(/>\s+</g, '>\n<').trim();
                $active.val(formatted);
            } else if ($active.attr('id') === 'code-css' || $active.attr('id') === 'code-js') {
                var lines = val.split('\n').filter(function(l) { return l.trim(); });
                $active.val(lines.join('\n'));
            }
            if (typeof updatePreview === 'function') updatePreview();
        } catch(e) { alert('خطا در فرمت کد'); }
    });

    // ===== اعمال به سازنده (همگام‌سازی معکوس) =====
    $('#code-apply').on('click', function() {
        var html = $('#code-html').val();
        var css = $('#code-css').val();
        var js = $('#code-js').val();
        // اینجا می‌توانید کدها را به سازنده بصری اعمال کنید
        alert('✅ کدها به سازنده اعمال شدند (در نسخه کامل این بخش پیاده‌سازی می‌شود)');
        if (typeof updatePreview === 'function') updatePreview();
    });

    console.log('✅ EzLens Template Editor (3-column) loaded.');
});
</script>