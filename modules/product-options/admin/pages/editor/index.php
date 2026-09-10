<?php
if (!defined('ABSPATH')) exit;

// Phase 5 modular assets
$_ezlens_editor_css = __DIR__ . '/assets/editor.css';
$_ezlens_editor_js  = __DIR__ . '/assets/editor.js';
$_ezlens_editor_css_url = EZLAUTH_MODULES_URL . 'product-options/admin/pages/editor/assets/editor.css';
$_ezlens_editor_js_url  = EZLAUTH_MODULES_URL . 'product-options/admin/pages/editor/assets/editor.js';
if (is_readable($_ezlens_editor_css)) {
    echo '<link rel="stylesheet" href="' . esc_url($_ezlens_editor_css_url) . '?v=' . esc_attr((string) filemtime($_ezlens_editor_css)) . '">';
}


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

// دریافت کدهای ذخیره‌شده
$code_html = $fields['_code_html'] ?? '';
$code_css  = $fields['_code_css'] ?? '';
$code_js   = $fields['_code_js'] ?? '';

// عملگرهای AJAX
$ajax_url = admin_url('admin-ajax.php');
$nonce = wp_create_nonce('ezlens_template_editor_nonce');
?>

<div class="wrap ezlens-template-editor">

    <!-- ===== هدر ===== -->
    <div class="ezlens-editor-header">
        <div class="ezlens-header-left">
            <h1 class="ezlens-page-title">
                <span
                    class="ezlens-icon-title"
                    style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/edit.svg'); ?>');">
                </span>

                <?php echo $is_edit ? 'ویرایش پالت' : 'افزودن پالت جدید'; ?>
            </h1>

            <span class="ezlens-badge-status">
                <?php echo $is_edit ? 'ویرایش' : 'جدید'; ?>
            </span>
        </div>

        <div class="ezlens-header-right">
            <a
                href="<?php echo esc_url(admin_url('admin.php?page=ezlens-product-options')); ?>"
                class="ezlens-btn ezlens-btn-outline">

                <span
                    class="ezlens-icon-btn"
                    style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/arrow-left.svg'); ?>');">
                </span>

                <span class="ezlens-btn-text">بازگشت به لیست</span>
            </a>

            <button
                type="button"
                class="ezlens-btn ezlens-btn-primary"
                id="ezlens-save-template-btn">

                <span
                    class="ezlens-icon-btn"
                    style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/save.svg'); ?>');">
                </span>

                <span class="ezlens-btn-text">ذخیره پالت</span>
            </button>

            <span id="ezlens-save-status" class="ezlens-save-status"></span>
        </div>
    </div>

    <hr class="wp-header-end">

    <form id="template-editor-form" method="post">

        <input type="hidden" name="template_id" value="<?php echo esc_attr($template_id); ?>">
        <input type="hidden" name="action" value="ezlens_save_template">
        <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

        <div class="editor-grid-3">

            <!-- ستون چپ -->
            <div class="editor-left">

                <div class="form-group">
                    <label for="template_title">
                        عنوان پالت <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="template_title"
                        name="title"
                        value="<?php echo esc_attr($title); ?>"
                        placeholder="مثلاً: ویژگی‌های عینک آفتابی"
                        required>
                </div>

                <div class="form-group">
                    <label for="template_description">توضیحات</label>

                    <textarea
                        id="template_description"
                        name="description"
                        rows="2"
                        placeholder="توضیح مختصری درباره این پالت"><?php echo esc_textarea($description); ?></textarea>
                </div>

                <div class="form-row">

                    <div class="form-group half">
                        <label>وضعیت</label>

                        <div class="status-toggle">
                            <label class="toggle-switch">
                                <input
                                    type="checkbox"
                                    name="status"
                                    value="active"
                                    <?php checked($status, 'active'); ?>>

                                <span class="slider"></span>
                            </label>

                            <span class="status-label">
                                <?php echo $status === 'active' ? 'فعال' : 'غیرفعال'; ?>
                            </span>
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

                <!-- بخش سازنده بصری -->
                <div class="editor-section">

                    <div class="section-header">

                        <h2>
                            <span
                                class="ezlens-icon-section"
                                style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/layers.svg'); ?>');">
                            </span>

                            سازنده بصری
                        </h2>

                        <span class="badge">بدون کد</span>

                        <button
                            type="button"
                            class="toggle-section"
                            data-target="visual-builder">
                            ▾
                        </button>

                    </div>

                    <div id="visual-builder" class="section-body">
                        <?php include __DIR__ . '/builder.php'; ?>
                    </div>

                </div>

                <!-- ویرایشگر کد -->
                <div class="editor-section">

                    <div class="section-header">

                        <h2>
                            <span
                                class="ezlens-icon-section"
                                style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/code.svg'); ?>');">
                            </span>

                            ویرایشگر کد
                        </h2>

                        <span class="badge">HTML + CSS + JS</span>

                        <button
                            type="button"
                            class="toggle-section"
                            data-target="code-editor">
                            ▾
                        </button>

                    </div>

                    <div id="code-editor" class="section-body">

                        <div class="code-hint-box">

                            <span
                                class="ezlens-icon-hint"
                                style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/help.svg'); ?>');">
                            </span>

                            <span>
                                کدهای خود را به صورت یکجا وارد کنید. از جداکننده‌های زیر استفاده کنید:
                            </span>

                            <ul class="code-tags">
                                <li><code>/*HTML*/</code> برای کد HTML</li>
                                <li><code>/*CSS*/</code> برای کد CSS</li>
                                <li><code>/*JS*/</code> برای کد JavaScript</li>
                            </ul>

                        </div>

                        <div class="code-editor-pro">

                            <textarea
                                id="code-editor-input"
                                name="code_editor"
                                class="code-editor-input"
                                spellcheck="false"><?php
                                echo esc_textarea(
                                    "/*HTML*/\n$code_html\n\n/*CSS*/\n$code_css\n\n/*JS*/\n$code_js"
                                );
                                ?></textarea>

                            <div
                                class="code-editor-line-numbers"
                                id="code-line-numbers"></div>

                        </div>

                        <div class="code-actions">

                            <button
                                type="button"
                                class="code-format-btn"
                                id="code-format">

                                <span
                                    class="ezlens-icon-btn-small"
                                    style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/check.svg'); ?>');">
                                </span>

                                <span>فرمت کد</span>
                            </button>

                            <span class="code-hint">

                                <span
                                    class="ezlens-icon-hint-small"
                                    style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/refresh.svg'); ?>');">
                                </span>

                                تغییرات در سازنده بصری به‌صورت خودکار به اینجا اضافه می‌شود

                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ستون وسط: پیش‌نمایش -->
            <div class="editor-center">
                <?php include __DIR__ . '/preview.php'; ?>
            </div>

            <!-- ستون راست -->
            <div class="editor-right">

                <?php include __DIR__ . '/settings.php'; ?>

                <div class="card actions">

                    <button
                        type="button"
                        id="ezlens-save-template-btn2"
                        class="ezlens-btn ezlens-btn-primary btn-save">

                        <span
                            class="ezlens-icon-btn"
                            style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/save.svg'); ?>');">
                        </span>

                        <span class="ezlens-btn-text">ذخیره پالت</span>

                    </button>

                    <button
                        type="button"
                        id="btn-duplicate"
                        class="ezlens-btn ezlens-btn-secondary"
                        style="display:<?php echo $is_edit ? 'flex' : 'none'; ?>;">

                        <span
                            class="ezlens-icon-btn"
                            style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/copy.svg'); ?>');">
                        </span>

                        <span class="ezlens-btn-text">کپی پالت</span>

                    </button>

                    <div id="save-status" class="save-status"></div>

                </div>

            </div>

        </div>
    </form>
</div>


<script>
window.ezlensPoEditor = window.ezlensPoEditor || {
  ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
  nonce: <?php echo wp_json_encode(wp_create_nonce('ezlens_template_editor_nonce')); ?>
};
</script>
<?php if (is_readable($_ezlens_editor_js)) : ?>
<script src="<?php echo esc_url($_ezlens_editor_js_url); ?>?v=<?php echo esc_attr((string) filemtime($_ezlens_editor_js)); ?>"></script>
<?php endif; ?>
