<?php
/**
 * صفحه افزودن / ویرایش فایل‌های ماژول فرآیند خرید
 *
 * EzLens Secure Login
 * Modern PHP Code Editor
 *
 * @package EzLens_Secure_Login
 * @subpackage Purchase_Process
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================================================
 * آماده‌سازی Code Editor رسمی وردپرس
 * ========================================================= */

if ( function_exists( 'wp_enqueue_code_editor' ) ) {
    wp_enqueue_code_editor(
        array(
            'type' => 'application/x-php',
        )
    );
}

/* =========================================================
 * دریافت اطلاعات فایل
 * ========================================================= */

$edit_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$is_edit = $edit_id > 0;
$item    = null;

if ( $is_edit ) {

    global $wpdb;

    $table = $wpdb->prefix . 'ezlens_purchase_scripts';

    $item = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $edit_id
        )
    );

    if ( ! $item ) {
        ?>

        <div class="ezp-error-state">

            <div class="ezp-error-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>

            <h2>فایل مورد نظر یافت نشد</h2>

            <p>
                فایلی که به دنبال آن هستید وجود ندارد یا حذف شده است.
            </p>

            <a
                href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-purchase-process-list' ) ); ?>"
                class="ezp-btn ezp-btn-primary"
            >
                بازگشت به لیست
            </a>

        </div>

        <?php

        return;
    }
}

$page_title  = $is_edit ? 'ویرایش فایل' : 'افزودن فایل جدید';
$submit_text = $is_edit ? 'به‌روزرسانی فایل' : 'ذخیره فایل';

/* =========================================================
 * آیکون‌ها
 * ========================================================= */

if ( ! function_exists( 'ezp_get_svg_icon' ) ) {

    function ezp_get_svg_icon( $name, $fallback = '' ) {

        $path = EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg';

        if ( file_exists( $path ) ) {

            $content = file_get_contents( $path );

            if ( false !== $content ) {
                return $content;
            }
        }

        return $fallback;
    }
}

$ico_back = ezp_get_svg_icon(
    'arrow-right',
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>'
);

$ico_save = ezp_get_svg_icon(
    'save',
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>'
);

$ico_check = ezp_get_svg_icon(
    'check',
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
);

$ico_validate = ezp_get_svg_icon(
    'shield',
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>'
);

$ico_fullscreen = '
<svg width="17" height="17" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="1.8"
    stroke-linecap="round" stroke-linejoin="round">
    <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
    <path d="M16 3h3a2 2 0 0 1 2 2v3"/>
    <path d="M8 21H5a2 2 0 0 1-2-2v-3"/>
    <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
</svg>';

$ico_wrap = '
<svg width="17" height="17" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="1.8"
    stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 6h18"/>
    <path d="M3 12h12"/>
    <path d="M3 18h18"/>
    <path d="m17 9 3 3-3 3"/>
</svg>';

$ico_top = '
<svg width="17" height="17" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="1.8"
    stroke-linecap="round" stroke-linejoin="round">
    <path d="M6 15l6-6 6 6"/>
</svg>';

$ico_bottom = '
<svg width="17" height="17" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="1.8"
    stroke-linecap="round" stroke-linejoin="round">
    <path d="M6 9l6 6 6-6"/>
</svg>';

$ico_close = '
<svg width="17" height="17" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="1.8"
    stroke-linecap="round">
    <path d="M6 6l12 12"/>
    <path d="M18 6L6 18"/>
</svg>';

/* =========================================================
 * دریافت کد فایل
 * ========================================================= */

$code_content = '';

if ( $is_edit ) {

    $file_path = EZLAUTH_MODULES_DIR . 'purchase-process/storage/' . $item->filename;

    if ( file_exists( $file_path ) && is_readable( $file_path ) ) {

        $file_content = file_get_contents( $file_path );

        if ( false !== $file_content ) {
            $code_content = $file_content;
        }

    } else {

        $code_content =
            "<?php\n\n" .
            "// فایل یافت نشد.\n" .
            "// لطفاً کد جدید وارد کنید.\n";

    }

} else {

    $code_content =
        "<?php\n\n" .
        "// کد سفارشی خود را اینجا بنویسید...\n" .
        "// از توابع وردپرس و ووکامرس می‌توانید استفاده کنید.\n";

}
?>

<style>
/* =========================================================
   EzLens Purchase Process Editor
   Modern / Minimal / RTL Admin
   ========================================================= */

:root {

    --ezp-primary: #173ea5;
    --ezp-primary-hover: #123386;
    --ezp-primary-soft: rgba(23, 62, 165, .08);

    --ezp-success: #059669;
    --ezp-success-soft: rgba(5, 150, 105, .10);

    --ezp-danger: #dc2626;
    --ezp-danger-soft: rgba(220, 38, 38, .10);

    --ezp-warning: #d97706;
    --ezp-warning-soft: rgba(217, 119, 6, .10);

    --ezp-purple: #635bdb;
    --ezp-purple-hover: #5149c7;

    --ezp-text: #172033;
    --ezp-muted: #6b7280;
    --ezp-border: #e6eaf0;
    --ezp-bg: #f6f8fb;
    --ezp-card: #ffffff;

    --ezp-editor: #111827;
    --ezp-editor-top: #171e2d;
    --ezp-editor-gutter: #0d1420;

    --ezp-radius: 16px;
    --ezp-radius-sm: 11px;

    --ezp-shadow:
        0 1px 2px rgba(15, 23, 42, .03),
        0 8px 30px rgba(15, 23, 42, .06);

    --ezp-transition:
        180ms cubic-bezier(.4, 0, .2, 1);
}


/* =========================================================
   Base
   ========================================================= */

.ezp-edit-wrap {

    width: 100%;
    max-width: 1180px;

    margin: 0 auto;

    padding:
        18px
        0
        40px;

    direction: rtl;

    font-family:
        IRANYekan,
        Vazirmatn,
        Tahoma,
        Arial,
        sans-serif;

    color: var(--ezp-text);
}


/* =========================================================
   Header
   ========================================================= */

.ezp-edit-header {

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    margin-bottom: 22px;
}

.ezp-header-main {

    display: flex;

    align-items: center;

    gap: 13px;

    min-width: 0;
}

.ezp-header-icon {

    width: 44px;
    height: 44px;

    display: flex;

    align-items: center;
    justify-content: center;

    flex: 0 0 44px;

    border-radius: 13px;

    background: var(--ezp-primary-soft);

    color: var(--ezp-primary);
}

.ezp-header-icon svg {

    width: 21px;
    height: 21px;
}

.ezp-header-text {

    min-width: 0;
}

.ezp-header-text h1 {

    margin: 0 0 4px;

    font-size: 20px;
    line-height: 1.4;

    font-weight: 750;

    color: var(--ezp-text);
}

.ezp-header-meta {

    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 7px;
}

.ezp-file-badge,
.ezp-status-badge {

    display: inline-flex;

    align-items: center;

    min-height: 24px;

    padding: 3px 9px;

    border-radius: 7px;

    font-size: 11px;

    font-weight: 650;
}

.ezp-file-badge {

    max-width: 320px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    direction: ltr;

    background: var(--ezp-bg);

    border: 1px solid var(--ezp-border);

    color: var(--ezp-muted);

    font-family:
        JetBrains Mono,
        Fira Code,
        Consolas,
        monospace;
}

.ezp-status-badge.active {

    background: var(--ezp-success-soft);

    color: var(--ezp-success);
}

.ezp-status-badge.inactive {

    background: var(--ezp-danger-soft);

    color: var(--ezp-danger);
}


/* =========================================================
   Back Button
   ========================================================= */

.ezp-back-link {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 9px 13px;

    border-radius: 10px;

    border: 1px solid var(--ezp-border);

    background: var(--ezp-card);

    color: var(--ezp-text);

    text-decoration: none;

    font-size: 12px;

    font-weight: 650;

    transition: var(--ezp-transition);
}

.ezp-back-link:hover {

    color: var(--ezp-primary);

    border-color: rgba(23, 62, 165, .25);

    background: var(--ezp-primary-soft);

}

.ezp-back-link svg {

    width: 17px;
    height: 17px;

    flex: 0 0 auto;
}


/* =========================================================
   Main Card
   ========================================================= */

.ezp-edit-card {

    background: var(--ezp-card);

    border:
        1px solid
        var(--ezp-border);

    border-radius: var(--ezp-radius);

    box-shadow: var(--ezp-shadow);

    overflow: hidden;
}

.ezp-card-body {

    padding:
        28px;
}


/* =========================================================
   Fields
   ========================================================= */

.ezp-field-group {

    margin-bottom: 22px;
}

.ezp-field-group:last-child {

    margin-bottom: 0;
}

.ezp-field-label {

    display: flex;

    align-items: baseline;

    gap: 7px;

    margin-bottom: 8px;
}

.ezp-field-label label {

    font-size: 13px;

    font-weight: 720;

    color: var(--ezp-text);
}

.ezp-required {

    color: var(--ezp-danger);

    font-weight: 800;
}

.ezp-field-hint {

    color: var(--ezp-muted);

    font-size: 11px;

    font-weight: 400;
}

.ezp-field-description {

    margin-top: 6px;

    color: var(--ezp-muted);

    font-size: 11px;

    line-height: 1.8;
}


/* =========================================================
   Inputs
   ========================================================= */

.ezp-input,
.ezp-textarea {

    display: block;

    width: 100%;

    box-sizing: border-box;

    border:
        1px solid
        var(--ezp-border);

    border-radius: 10px;

    background: #fbfcfe;

    color: var(--ezp-text);

    font-family: inherit;

    font-size: 13px;

    outline: none;

    transition:
        border-color var(--ezp-transition),
        box-shadow var(--ezp-transition),
        background var(--ezp-transition);
}

.ezp-input {

    height: 42px;

    padding: 0 13px;
}

.ezp-textarea {

    min-height: 86px;

    padding: 11px 13px;

    line-height: 1.8;

    resize: vertical;
}

.ezp-input:focus,
.ezp-textarea:focus {

    background: #fff;

    border-color: var(--ezp-primary);

    box-shadow:
        0 0 0 3px
        var(--ezp-primary-soft);
}


/* =========================================================
   CODE EDITOR
   ========================================================= */

.ezp-code-section {

    position: relative;
}


/* Editor container */

.ezp-code-editor {

    overflow: hidden;

    border:
        1px solid
        #242d3d;

    border-radius: 13px;

    background: var(--ezp-editor);

    box-shadow:
        0 12px 30px
        rgba(15, 23, 42, .14);

    transition:
        border-color var(--ezp-transition),
        box-shadow var(--ezp-transition);
}

.ezp-code-editor:focus-within {

    border-color:
        rgba(99, 91, 219, .65);

    box-shadow:
        0 0 0 3px
        rgba(99, 91, 219, .10),
        0 16px 35px
        rgba(15, 23, 42, .16);
}


/* =========================================================
   Editor Toolbar
   ========================================================= */

.ezp-editor-toolbar {

    height: 44px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;

    padding:
        0 10px;

    background:
        var(--ezp-editor-top);

    border-bottom:
        1px solid
        rgba(255, 255, 255, .055);

    direction: ltr;
}

.ezp-editor-toolbar-left,
.ezp-editor-toolbar-right {

    display: flex;

    align-items: center;

    gap: 5px;
}

.ezp-editor-language {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    height: 27px;

    padding:
        0 9px;

    border-radius: 7px;

    background:
        rgba(255, 255, 255, .055);

    color: #aeb9cc;

    font-family:
        JetBrains Mono,
        Fira Code,
        Consolas,
        monospace;

    font-size: 10px;

    letter-spacing: .2px;
}

.ezp-editor-language-dot {

    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: #8be9fd;

    box-shadow:
        0 0 8px
        rgba(139, 233, 253, .55);
}

.ezp-editor-tool {

    width: 30px;
    height: 30px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 0;

    border: 0;

    border-radius: 7px;

    background: transparent;

    color: #7f8ba0;

    cursor: pointer;

    transition:
        background var(--ezp-transition),
        color var(--ezp-transition);
}

.ezp-editor-tool:hover {

    color: #dce4f2;

    background:
        rgba(255, 255, 255, .07);
}

.ezp-editor-tool.active {

    color: #a5b4fc;

    background:
        rgba(99, 91, 219, .16);
}

.ezp-editor-tool svg {

    width: 16px;
    height: 16px;
}


/* =========================================================
   CodeMirror
   ========================================================= */

.ezp-code-editor .CodeMirror {

    height: 510px !important;

    direction: ltr !important;

    text-align: left !important;

    background:
        var(--ezp-editor) !important;

    color:
        #d8dee9 !important;

    font-family:
        JetBrains Mono,
        Fira Code,
        Consolas,
        "Courier New",
        monospace !important;

    font-size:
        13px !important;

    line-height:
        1.72 !important;

    letter-spacing:
        0 !important;
}

.ezp-code-editor .CodeMirror-scroll {

    background:
        var(--ezp-editor) !important;

    padding-bottom:
        20px;
}


/* Gutters */

.ezp-code-editor .CodeMirror-gutters {

    min-width: 46px !important;

    background:
        var(--ezp-editor-gutter) !important;

    border-right:
        1px solid
        rgba(255, 255, 255, .045) !important;

    border-left: 0 !important;
}

.ezp-code-editor .CodeMirror-linenumber {

    min-width: 32px !important;

    padding:
        0 8px 0 6px !important;

    color:
        #4f5c70 !important;

    font-family:
        JetBrains Mono,
        Fira Code,
        Consolas,
        monospace !important;

    font-size:
        11px !important;

    text-align:
        right !important;

    direction:
        ltr !important;
}


/* Cursor */

.ezp-code-editor .CodeMirror-cursor {

    border-left:
        2px solid
        #c7d2fe !important;
}


/* Selection */

.ezp-code-editor .CodeMirror-selected {

    background:
        rgba(99, 102, 241, .24) !important;
}


/* Active line */

.ezp-code-editor .CodeMirror-activeline-background {

    background:
        rgba(255, 255, 255, .028) !important;
}


/* Matching brackets */

.ezp-code-editor .CodeMirror-matchingbracket {

    color:
        #86efac !important;

    background:
        rgba(134, 239, 172, .08) !important;

    outline:
        1px solid
        rgba(134, 239, 172, .25);
}


/* =========================================================
   Syntax Highlighting
   ========================================================= */

.ezp-code-editor .cm-s-default .cm-keyword {
    color: #ff79c6 !important;
}

.ezp-code-editor .cm-s-default .cm-operator {
    color: #ff79c6 !important;
}

.ezp-code-editor .cm-s-default .cm-variable {
    color: #f8f8f2 !important;
}

.ezp-code-editor .cm-s-default .cm-variable-2 {
    color: #50fa7b !important;
}

.ezp-code-editor .cm-s-default .cm-variable-3 {
    color: #ffb86c !important;
}

.ezp-code-editor .cm-s-default .cm-builtin {
    color: #8be9fd !important;
}

.ezp-code-editor .cm-s-default .cm-atom {
    color: #bd93f9 !important;
}

.ezp-code-editor .cm-s-default .cm-number {
    color: #bd93f9 !important;
}

.ezp-code-editor .cm-s-default .cm-def {
    color: #50fa7b !important;
}

.ezp-code-editor .cm-s-default .cm-string {
    color: #f1fa8c !important;
}

.ezp-code-editor .cm-s-default .cm-string-2 {
    color: #f1fa8c !important;
}

.ezp-code-editor .cm-s-default .cm-comment {
    color: #6272a4 !important;
    font-style: italic !important;
}

.ezp-code-editor .cm-s-default .cm-tag {
    color: #ff79c6 !important;
}

.ezp-code-editor .cm-s-default .cm-attribute {
    color: #50fa7b !important;
}

.ezp-code-editor .cm-s-default .cm-property {
    color: #8be9fd !important;
}

.ezp-code-editor .cm-s-default .cm-qualifier {
    color: #8be9fd !important;
}

.ezp-code-editor .cm-s-default .cm-error {

    color: #ff5555 !important;

    background:
        rgba(255, 85, 85, .10) !important;
}


/* =========================================================
   Editor Status Bar
   ========================================================= */

.ezp-editor-status {

    min-height: 31px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 12px;

    padding:
        0 11px;

    background:
        #0c121d;

    border-top:
        1px solid
        rgba(255, 255, 255, .045);

    color:
        #66748a;

    font-family:
        JetBrains Mono,
        Fira Code,
        Consolas,
        monospace;

    font-size:
        10px;

    direction: ltr;
}

.ezp-editor-status-left,
.ezp-editor-status-right {

    display: flex;

    align-items: center;

    gap: 13px;

    min-width: 0;
}

.ezp-editor-stat {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    white-space: nowrap;
}

.ezp-editor-stat strong {

    color:
        #8c9ab0;

    font-weight:
        500;
}

.ezp-editor-modified {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    color:
        #64748b;
}

.ezp-editor-modified.is-modified {

    color:
        #fbbf24;
}

.ezp-editor-modified-dot {

    width: 5px;
    height: 5px;

    border-radius: 50%;

    background:
        currentColor;
}


/* =========================================================
   Tips
   ========================================================= */

.ezp-code-footer {

    display: flex;

    align-items: center;

    justify-content: space-between;

    flex-wrap: wrap;

    gap: 12px;

    margin-top: 9px;
}

.ezp-code-tip {

    display: flex;

    align-items: center;

    gap: 6px;

    color:
        var(--ezp-muted);

    font-size:
        11px;

    line-height:
        1.7;
}

.ezp-code-tip code {

    direction: ltr;

    padding:
        2px 6px;

    border:
        1px solid
        var(--ezp-border);

    border-radius: 5px;

    background:
        var(--ezp-bg);

    color:
        var(--ezp-text);

    font-family:
        JetBrains Mono,
        Consolas,
        monospace;

    font-size:
        10px;
}

.ezp-shortcuts {

    display: flex;

    align-items: center;

    gap: 5px;

    direction: ltr;

    color:
        var(--ezp-muted);

    font-size:
        10px;
}

.ezp-shortcuts kbd {

    padding:
        2px 6px;

    border:
        1px solid
        var(--ezp-border);

    border-bottom-width:
        2px;

    border-radius:
        5px;

    background:
        var(--ezp-card);

    color:
        var(--ezp-muted);

    font-family:
        JetBrains Mono,
        monospace;
}


/* =========================================================
   Validation
   ========================================================= */

.ezp-validation-row {

    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 13px;
}

.ezp-validation-help {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    color:
        var(--ezp-muted);

    font-size:
        11px;
}

.ezp-validation-result {

    display: none;

    margin-top: 10px;

    padding:
        11px 13px;

    border-radius:
        9px;

    font-size:
        12px;

    line-height:
        1.8;

    direction:
        ltr;

    text-align:
        left;

    font-family:
        JetBrains Mono,
        Fira Code,
        Consolas,
        monospace;

    white-space:
        pre-wrap;

    word-break:
        break-word;

    max-height:
        180px;

    overflow:
        auto;
}

.ezp-validation-result.valid {

    display: block;

    background:
        var(--ezp-success-soft);

    border:
        1px solid
        rgba(5, 150, 105, .25);

    color:
        #047857;
}

.ezp-validation-result.invalid {

    display: block;

    background:
        var(--ezp-danger-soft);

    border:
        1px solid
        rgba(220, 38, 38, .22);

    color:
        #b91c1c;
}

.ezp-validation-result.loading {

    display: block;

    background:
        var(--ezp-warning-soft);

    border:
        1px solid
        rgba(217, 119, 6, .22);

    color:
        #b45309;
}


/* =========================================================
   Buttons
   ========================================================= */

.ezp-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    min-height: 39px;

    padding:
        0 15px;

    border:
        1px solid transparent;

    border-radius:
        9px;

    font-family:
        inherit;

    font-size:
        12px;

    font-weight:
        700;

    line-height:
        1;

    text-decoration:
        none;

    cursor:
        pointer;

    transition:
        transform var(--ezp-transition),
        background var(--ezp-transition),
        border-color var(--ezp-transition),
        box-shadow var(--ezp-transition),
        color var(--ezp-transition);
}

.ezp-btn svg {

    width:
        17px;

    height:
        17px;

    flex:
        0 0 auto;
}

.ezp-btn-primary {

    background:
        var(--ezp-primary);

    color:
        #fff !important;

    box-shadow:
        0 4px 12px
        rgba(23, 62, 165, .18);
}

.ezp-btn-primary:hover {

    background:
        var(--ezp-primary-hover);

    color:
        #fff !important;

    transform:
        translateY(-1px);

    box-shadow:
        0 7px 18px
        rgba(23, 62, 165, .25);
}

.ezp-btn-secondary {

    background:
        var(--ezp-card);

    border-color:
        var(--ezp-border);

    color:
        var(--ezp-text);
}

.ezp-btn-secondary:hover {

    background:
        var(--ezp-bg);

    border-color:
        #cbd5e1;

    color:
        var(--ezp-text);
}

.ezp-btn-success {

    background:
        var(--ezp-success);

    color:
        #fff !important;
}

.ezp-btn-success:hover {

    background:
        #047857;

    color:
        #fff !important;

    transform:
        translateY(-1px);
}

.ezp-btn-validate {

    background:
        var(--ezp-purple);

    color:
        #fff !important;
}

.ezp-btn-validate:hover {

    background:
        var(--ezp-purple-hover);

    color:
        #fff !important;

    transform:
        translateY(-1px);
}

.ezp-btn:disabled {

    opacity:
        .55;

    cursor:
        not-allowed;

    transform:
        none !important;

    box-shadow:
        none !important;
}


/* =========================================================
   Spinner
   ========================================================= */

.ezp-spinner {

    width:
        14px;

    height:
        14px;

    display:
        none;

    border:
        2px solid
        rgba(255,255,255,.3);

    border-top-color:
        #fff;

    border-radius:
        50%;

    animation:
        ezp-spin .65s linear infinite;
}

.ezp-spinner.active {

    display:
        inline-block;
}

@keyframes ezp-spin {

    to {
        transform:
            rotate(360deg);
    }
}


/* =========================================================
   Activate Toggle
   ========================================================= */

.ezp-toggle {

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    padding:
        13px 14px;

    border:
        1px solid
        var(--ezp-border);

    border-radius:
        11px;

    background:
        var(--ezp-bg);

    transition:
        border-color var(--ezp-transition),
        background var(--ezp-transition);
}

.ezp-toggle:hover {

    border-color:
        #d4dbe6;
}

.ezp-toggle input {

    position:
        absolute;

    opacity:
        0;

    pointer-events:
        none;
}

.ezp-toggle-switch {

    position:
        relative;

    width:
        39px;

    height:
        22px;

    flex:
        0 0 39px;

    border-radius:
        999px;

    background:
        #cbd5e1;

    cursor:
        pointer;

    transition:
        background var(--ezp-transition);
}

.ezp-toggle-switch::after {

    content:
        "";

    position:
        absolute;

    top:
        3px;

    right:
        3px;

    width:
        16px;

    height:
        16px;

    border-radius:
        50%;

    background:
        #fff;

    box-shadow:
        0 1px 3px
        rgba(0,0,0,.18);

    transition:
        transform var(--ezp-transition);
}

.ezp-toggle input:checked + .ezp-toggle-switch {

    background:
        var(--ezp-success);
}

.ezp-toggle input:checked + .ezp-toggle-switch::after {

    transform:
        translateX(-17px);
}

.ezp-toggle-content {

    min-width:
        0;
}

.ezp-toggle-title {

    display:
        block;

    margin-bottom:
        2px;

    color:
        var(--ezp-text);

    font-size:
        12px;

    font-weight:
        700;
}

.ezp-toggle-description {

    color:
        var(--ezp-muted);

    font-size:
        10px;

    line-height:
        1.7;
}


/* =========================================================
   Actions
   ========================================================= */

.ezp-actions {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        9px;

    margin-top:
        25px;

    padding-top:
        19px;

    border-top:
        1px solid
        var(--ezp-border);
}

.ezp-actions-spacer {

    flex:
        1;
}


/* =========================================================
   Toast
   ========================================================= */

#ezp-toast {

    position:
        fixed;

    z-index:
        999999;

    left:
        25px;

    bottom:
        25px;

    min-width:
        260px;

    max-width:
        420px;

    padding:
        12px 14px;

    display:
        none;

    align-items:
        center;

    gap:
        10px;

    border:
        1px solid
        var(--ezp-border);

    border-radius:
        11px;

    background:
        var(--ezp-card);

    box-shadow:
        0 14px 40px
        rgba(15,23,42,.15);

    color:
        var(--ezp-text);

    font-size:
        12px;

    direction:
        rtl;

    opacity:
        0;

    transform:
        translateY(10px);

    transition:
        opacity .2s ease,
        transform .2s ease;
}

#ezp-toast.show {

    display:
        flex;

    opacity:
        1;

    transform:
        translateY(0);
}

#ezp-toast.success {

    border-right:
        3px solid
        var(--ezp-success);
}

#ezp-toast.error {

    border-right:
        3px solid
        var(--ezp-danger);
}

.ezp-toast-close {

    margin-right:
        auto;

    padding:
        0;

    border:
        0;

    background:
        transparent;

    color:
        var(--ezp-muted);

    cursor:
        pointer;
}

.ezp-toast-close:hover {

    color:
        var(--ezp-text);
}


/* =========================================================
   Error State
   ========================================================= */

.ezp-error-state {

    max-width:
        480px;

    margin:
        70px auto;

    padding:
        35px;

    text-align:
        center;

    border:
        1px solid
        var(--ezp-border);

    border-radius:
        16px;

    background:
        var(--ezp-card);

    box-shadow:
        var(--ezp-shadow);
}

.ezp-error-icon {

    width:
        58px;

    height:
        58px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    margin:
        0 auto 15px;

    border-radius:
        16px;

    background:
        var(--ezp-danger-soft);

    color:
        var(--ezp-danger);
}

.ezp-error-icon .dashicons {

    font-size:
        28px;

    width:
        28px;

    height:
        28px;
}

.ezp-error-state h2 {

    margin:
        0 0 7px;

    font-size:
        17px;
}

.ezp-error-state p {

    margin:
        0 0 20px;

    color:
        var(--ezp-muted);

    font-size:
        12px;

    line-height:
        1.9;
}


/* =========================================================
   Fullscreen Mode
   ========================================================= */

body.ezp-editor-fullscreen {

    overflow:
        hidden !important;
}

.ezp-code-editor.is-fullscreen {

    position:
        fixed;

    z-index:
        999998;

    top:
        20px;

    right:
        20px;

    bottom:
        20px;

    left:
        20px;

    border-radius:
        14px;

    display:
        flex;

    flex-direction:
        column;
}

.ezp-code-editor.is-fullscreen .CodeMirror {

    height:
        auto !important;

    flex:
        1;
}

.ezp-code-editor.is-fullscreen .CodeMirror-scroll {

    max-height:
        none !important;
}


/* =========================================================
   Responsive
   ========================================================= */

@media (max-width: 782px) {

    .ezp-edit-wrap {

        padding:
            12px 0 30px;
    }

    .ezp-edit-header {

        align-items:
            flex-start;

        flex-direction:
            column;
    }

    .ezp-back-link {

        width:
            100%;

        box-sizing:
            border-box;

        justify-content:
            center;
    }

    .ezp-card-body {

        padding:
            20px 15px;
    }

    .ezp-code-editor .CodeMirror {

        height:
            400px !important;

        font-size:
            12px !important;
    }

    .ezp-editor-status {

        min-height:
            36px;

        overflow:
            hidden;
    }

    .ezp-editor-status-left,
    .ezp-editor-status-right {

        gap:
            8px;
    }

    .ezp-editor-stat:nth-child(2) {

        display:
            none;
    }

    .ezp-code-footer {

        align-items:
            flex-start;

        flex-direction:
            column;
    }

    .ezp-actions {

        flex-direction:
            column;

        align-items:
            stretch;
    }

    .ezp-actions .ezp-btn {

        width:
            100%;
    }

    .ezp-actions-spacer {

        display:
            none;
    }
}

@media (max-width: 480px) {

    .ezp-header-text h1 {

        font-size:
            17px;
    }

    .ezp-code-editor .CodeMirror {

        height:
            320px !important;

        font-size:
            11px !important;
    }

    .ezp-editor-toolbar {

        height:
            42px;
    }

    .ezp-editor-tool {

        width:
            28px;

        height:
            28px;
    }

    .ezp-editor-status-right {

        display:
            none;
    }
}
</style>


<div class="ezp-edit-wrap">

    <!-- =====================================================
         Header
         ===================================================== -->

    <div class="ezp-edit-header">

        <div class="ezp-header-main">

            <div class="ezp-header-icon">
                <?php echo $ico_save; ?>
            </div>

            <div class="ezp-header-text">

                <h1>
                    <?php echo esc_html( $page_title ); ?>
                </h1>

                <?php if ( $is_edit && $item ) : ?>

                    <div class="ezp-header-meta">

                        <span class="ezp-file-badge">
                            <?php echo esc_html( $item->filename ); ?>
                        </span>

                        <span class="ezp-status-badge <?php echo $item->status ? 'active' : 'inactive'; ?>">

                            <?php if ( $item->status ) : ?>
                                ● فعال
                            <?php else : ?>
                                ○ غیرفعال
                            <?php endif; ?>

                        </span>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <a
            href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-purchase-process-list' ) ); ?>"
            class="ezp-back-link"
        >

            <?php echo $ico_back; ?>

            <span>بازگشت به لیست</span>

        </a>

    </div>


    <!-- =====================================================
         Main Card
         ===================================================== -->

    <div class="ezp-edit-card">

        <div class="ezp-card-body">

            <form
                id="ezp-edit-form"
                method="post"
                action=""
                autocomplete="off"
            >

                <?php
                wp_nonce_field(
                    'ezpurchase_edit_nonce',
                    'ezpurchase_edit_nonce'
                );
                ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo esc_attr( $edit_id ); ?>"
                >


                <!-- =================================================
                     Title
                     ================================================= -->

                <div class="ezp-field-group">

                    <div class="ezp-field-label">

                        <label for="ezp-title">
                            عنوان نمایشی
                            <span class="ezp-required">*</span>
                        </label>

                        <span class="ezp-field-hint">
                            فقط برای نمایش در لیست
                        </span>

                    </div>

                    <input
                        type="text"
                        id="ezp-title"
                        name="title"
                        class="ezp-input"
                        value="<?php echo $is_edit ? esc_attr( $item->title ) : ''; ?>"
                        placeholder="مثلاً: صفحه محصول"
                        required
                    >

                    <div class="ezp-field-description">
                        عنوان فارسی/دلخواه برای پیدا کردن راحت‌تر. روی نام فایل فیزیکی اثری ندارد.
                    </div>

                </div>


                <!-- =================================================
                     Filename (slug)
                     ================================================= -->

                <div class="ezp-field-group">

                    <div class="ezp-field-label">

                        <label for="ezp-filename">
                            نام فایل (انگلیسی)
                            <span class="ezp-required">*</span>
                        </label>

                        <span class="ezp-field-hint">
                            فقط a-z و 0-9 و - _
                        </span>

                    </div>

                    <input
                        type="text"
                        id="ezp-filename"
                        name="filename"
                        class="ezp-input"
                        value="<?php echo $is_edit ? esc_attr( pathinfo( $item->filename, PATHINFO_FILENAME ) ) : ''; ?>"
                        placeholder="مثلاً: product-page"
                        <?php echo $is_edit ? 'readonly' : 'required'; ?>
                        pattern="[A-Za-z0-9_-]+"
                        dir="ltr"
                        style="text-align:left;font-family:monospace;"
                    >

                    <div class="ezp-field-description">
                        <?php if ( $is_edit ) : ?>
                            نام فایل پس از ایجاد قابل تغییر نیست (برای جلوگیری از تداخل). پسوند .php خودکار اضافه می‌شود.
                        <?php else : ?>
                            این نام برای فایل فیزیکی استفاده می‌شود. اگر تکراری باشد خطا می‌گیرید و فایل ساخته نمی‌شود.
                        <?php endif; ?>
                    </div>

                </div>


                <!-- =================================================
                     Description
                     ================================================= -->

                <div class="ezp-field-group">

                    <div class="ezp-field-label">

                        <label for="ezp-desc">
                            توضیحات
                        </label>

                        <span class="ezp-field-hint">
                            اختیاری
                        </span>

                    </div>

                    <textarea
                        id="ezp-desc"
                        name="description"
                        class="ezp-textarea"
                        rows="3"
                        placeholder="توضیح مختصری درباره کاربرد این فایل..."
                    ><?php echo $is_edit ? esc_textarea( $item->description ) : ''; ?></textarea>

                    <div class="ezp-field-description">
                        هدف یا کاربرد این فایل را برای مدیریت بهتر مشخص کنید.
                    </div>

                </div>


                <!-- =================================================
                     Code
                     ================================================= -->

                <div class="ezp-field-group ezp-code-section">

                    <div class="ezp-field-label">

                        <label for="ezp-code">
                            کد PHP
                            <span class="ezp-required">*</span>
                        </label>

                        <span class="ezp-field-hint">
                            ویرایشگر حرفه‌ای
                        </span>

                    </div>


                    <div
                        class="ezp-code-editor"
                        id="ezp-code-editor"
                    >


                        <!-- Editor Toolbar -->

                        <div class="ezp-editor-toolbar">

                            <div class="ezp-editor-toolbar-left">

                                <div class="ezp-editor-language">

                                    <span class="ezp-editor-language-dot"></span>

                                    <span>PHP</span>

                                </div>

                            </div>


                            <div class="ezp-editor-toolbar-right">

                                <button
                                    type="button"
                                    class="ezp-editor-tool"
                                    id="ezp-wrap-btn"
                                    title="فعال/غیرفعال کردن Word Wrap"
                                    aria-label="Word Wrap"
                                >
                                    <?php echo $ico_wrap; ?>
                                </button>


                                <button
                                    type="button"
                                    class="ezp-editor-tool"
                                    id="ezp-top-btn"
                                    title="رفتن به ابتدای کد"
                                    aria-label="Go to top"
                                >
                                    <?php echo $ico_top; ?>
                                </button>


                                <button
                                    type="button"
                                    class="ezp-editor-tool"
                                    id="ezp-bottom-btn"
                                    title="رفتن به انتهای کد"
                                    aria-label="Go to bottom"
                                >
                                    <?php echo $ico_bottom; ?>
                                </button>


                                <button
                                    type="button"
                                    class="ezp-editor-tool"
                                    id="ezp-fullscreen-btn"
                                    title="تمام صفحه"
                                    aria-label="Fullscreen"
                                >
                                    <?php echo $ico_fullscreen; ?>
                                </button>

                            </div>

                        </div>


                        <!-- CodeMirror textarea -->

                        <textarea
                            id="ezp-code"
                            name="code"
                            required
                        ><?php echo esc_textarea( $code_content ); ?></textarea>


                        <!-- Editor Status -->

                        <div class="ezp-editor-status">

                            <div class="ezp-editor-status-left">

                                <span class="ezp-editor-stat">
                                    Ln
                                    <strong id="ezp-cursor-line">1</strong>
                                </span>

                                <span class="ezp-editor-stat">
                                    Col
                                    <strong id="ezp-cursor-column">1</strong>
                                </span>

                                <span class="ezp-editor-stat">
                                    Lines
                                    <strong id="ezp-line-count">1</strong>
                                </span>

                            </div>


                            <div class="ezp-editor-status-right">

                                <span class="ezp-editor-stat">
                                    Chars
                                    <strong id="ezp-char-count">0</strong>
                                </span>

                                <span
                                    class="ezp-editor-modified"
                                    id="ezp-modified-status"
                                >
                                    <span class="ezp-editor-modified-dot"></span>
                                    <span id="ezp-modified-text">Saved</span>
                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- Editor Footer -->

                    <div class="ezp-code-footer">

                        <div class="ezp-code-tip">

                            <span>راهنما:</span>

                            <code>error_log()</code>

                            <span>
                                برای دیباگ کد استفاده کنید.
                            </span>

                        </div>


                        <div class="ezp-shortcuts">

                            <kbd>Ctrl</kbd>
                            <span>+</span>
                            <kbd>S</kbd>

                            <span>ذخیره</span>

                            <span>•</span>

                            <kbd>Ctrl</kbd>
                            <span>+</span>
                            <kbd>Enter</kbd>

                            <span>بررسی</span>

                        </div>

                    </div>


                    <!-- Validation -->

                    <div class="ezp-validation-row">

                        <button
                            type="button"
                            id="ezp-validate-btn"
                            class="ezp-btn ezp-btn-validate"
                        >

                            <?php echo $ico_validate; ?>

                            <span>بررسی کد PHP</span>

                        </button>


                        <span class="ezp-validation-help">

                            <span class="dashicons dashicons-shield"></span>

                            بررسی خطاهای نحوی PHP

                        </span>

                    </div>


                    <div
                        id="ezp-validation-result"
                        class="ezp-validation-result"
                    ></div>

                </div>


                <!-- =================================================
                     Status
                     ================================================= -->

                <div class="ezp-field-group">

                    <label class="ezp-toggle">

                        <input
                            type="checkbox"
                            id="ezp-activate"
                            name="activate"
                            value="1"
                            <?php checked( $is_edit ? $item->status : 0, 1 ); ?>
                        >

                        <span class="ezp-toggle-switch"></span>

                        <span class="ezp-toggle-content">

                            <span class="ezp-toggle-title">
                                فعال‌سازی فایل
                            </span>

                            <span class="ezp-toggle-description">
                                در صورت فعال بودن، فایل در زمان اجرای وردپرس بارگذاری می‌شود.
                            </span>

                        </span>

                    </label>

                </div>


                <!-- =================================================
                     Actions
                     ================================================= -->

                <div class="ezp-actions">

                    <button
                        type="submit"
                        id="ezp-save-btn"
                        class="ezp-btn ezp-btn-primary"
                    >

                        <span
                            class="ezp-spinner"
                            id="ezp-spinner"
                        ></span>

                        <span
                            class="ezp-save-icon"
                        >
                            <?php echo $ico_save; ?>
                        </span>

                        <span id="ezp-save-text">
                            <?php echo esc_html( $submit_text ); ?>
                        </span>

                    </button>


                    <a
                        href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-purchase-process-list' ) ); ?>"
                        class="ezp-btn ezp-btn-secondary"
                    >
                        انصراف
                    </a>


                    <?php if ( $is_edit ) : ?>

                        <span class="ezp-actions-spacer"></span>

                        <button
                            type="button"
                            id="ezp-save-and-activate"
                            class="ezp-btn ezp-btn-success"
                        >

                            <?php echo $ico_check; ?>

                            ذخیره و فعال‌سازی

                        </button>

                    <?php endif; ?>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- =========================================================
     Toast
     ========================================================= -->

<div id="ezp-toast">

    <span class="toast-msg"></span>

    <button
        type="button"
        class="ezp-toast-close"
        aria-label="بستن"
    >
        <?php echo $ico_close; ?>
    </button>

</div>


<script type="text/javascript">
(function($) {

    'use strict';


    /* =========================================================
       Configuration
       ========================================================= */

    var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

    var nonce = <?php echo wp_json_encode( wp_create_nonce( 'ezpurchase_nonce' ) ); ?>;

    var listUrl = <?php echo wp_json_encode( admin_url( 'admin.php?page=ezlens-purchase-process-list' ) ); ?>;

    var submitText = <?php echo wp_json_encode( $submit_text ); ?>;


    /* =========================================================
       Elements
       ========================================================= */

    var editForm       = $('#ezp-edit-form');
    var saveBtn        = $('#ezp-save-btn');
    var saveText       = $('#ezp-save-text');
    var spinner        = $('#ezp-spinner');

    var activate       = $('#ezp-activate');

    var validateBtn    = $('#ezp-validate-btn');
    var validateResult = $('#ezp-validation-result');

    var editorBox      = $('#ezp-code-editor');

    var wrapBtn        = $('#ezp-wrap-btn');
    var fullscreenBtn  = $('#ezp-fullscreen-btn');
    var topBtn         = $('#ezp-top-btn');
    var bottomBtn      = $('#ezp-bottom-btn');

    var editor         = null;
    var originalCode   = '';

    var isFullscreen   = false;
    var isSaving       = false;


    /* =========================================================
       Get Editor Value
       ========================================================= */

    function getEditorValue() {

        if (
            editor &&
            editor.codemirror
        ) {
            return editor.codemirror.getValue();
        }

        return $('#ezp-code').val() || '';
    }


    /* =========================================================
       Update Counters
       ========================================================= */

    function updateEditorStats() {

        if (
            !editor ||
            !editor.codemirror
        ) {
            return;
        }

        var cm = editor.codemirror;

        var value = cm.getValue();

        var cursor = cm.getCursor();

        var line = cursor.line + 1;

        var column = cursor.ch + 1;

        var lines = value.length
            ? value.split('\n').length
            : 1;

        var chars = value.length;

        $('#ezp-cursor-line').text(line);

        $('#ezp-cursor-column').text(column);

        $('#ezp-line-count').text(lines);

        $('#ezp-char-count').text(chars.toLocaleString('en-US'));


        /* Modified status */

        var modifiedStatus = $('#ezp-modified-status');

        var modifiedText = $('#ezp-modified-text');

        if (
            value !== originalCode
        ) {

            modifiedStatus.addClass('is-modified');

            modifiedText.text('Modified');

        } else {

            modifiedStatus.removeClass('is-modified');

            modifiedText.text('Saved');
        }
    }


    /* =========================================================
       Initialize CodeMirror
       ========================================================= */

    function initEditor() {

        if (
            typeof wp === 'undefined' ||
            !wp.codeEditor ||
            typeof wp.codeEditor.initialize !== 'function'
        ) {

            console.warn(
                'EzLens: wp.codeEditor در دسترس نیست.'
            );

            return;
        }


        var settings = {

            codemirror: {

                indentUnit: 4,

                tabSize: 4,

                mode: 'php',

                lineNumbers: true,

                lineWrapping: false,

                matchBrackets: true,

                autoCloseBrackets: true,

                styleActiveLine: true,

                viewportMargin: Infinity,

                direction: 'ltr',

                rtlMoveVisually: false,

                extraKeys: {

                    'Ctrl-S': function() {

                        editForm.trigger('submit');

                    },

                    'Cmd-S': function() {

                        editForm.trigger('submit');

                    },

                    'Ctrl-Enter': function() {

                        validateCode();

                    },

                    'Cmd-Enter': function() {

                        validateCode();

                    },

                    'F11': function(cm) {

                        toggleFullscreen();

                    },

                    'Esc': function() {

                        if (isFullscreen) {
                            toggleFullscreen();
                        }

                    }

                }

            }

        };


        try {

            editor =
                wp.codeEditor.initialize(
                    $('#ezp-code'),
                    settings
                );


            window.ezp_editor = editor;


            if (
                editor &&
                editor.codemirror
            ) {

                var cm = editor.codemirror;


                /* =================================================
                   Important: Code itself is LTR
                   ================================================= */

                cm.setOption(
                    'direction',
                    'ltr'
                );

                cm.setOption(
                    'lineNumbers',
                    true
                );

                cm.setOption(
                    'mode',
                    'php'
                );

                cm.setOption(
                    'lineWrapping',
                    false
                );


                originalCode =
                    cm.getValue();


                /* Events */

                cm.on(
                    'cursorActivity',
                    updateEditorStats
                );

                cm.on(
                    'change',
                    function() {

                        updateEditorStats();

                    }
                );


                setTimeout(
                    function() {

                        cm.refresh();

                        updateEditorStats();

                    },
                    150
                );
            }

        } catch (error) {

            console.error(
                'EzLens Code Editor:',
                error
            );

        }
    }


    /* =========================================================
       Word Wrap
       ========================================================= */

    wrapBtn.on(
        'click',
        function() {

            if (
                !editor ||
                !editor.codemirror
            ) {
                return;
            }

            var cm = editor.codemirror;

            var current =
                cm.getOption('lineWrapping');

            cm.setOption(
                'lineWrapping',
                !current
            );

            wrapBtn.toggleClass(
                'active',
                !current
            );

            cm.refresh();

        }
    );


    /* =========================================================
       Go Top
       ========================================================= */

    topBtn.on(
        'click',
        function() {

            if (
                !editor ||
                !editor.codemirror
            ) {
                return;
            }

            var cm = editor.codemirror;

            cm.scrollTo(
                0,
                0
            );

            cm.setCursor(
                0,
                0
            );

            cm.focus();

        }
    );


    /* =========================================================
       Go Bottom
       ========================================================= */

    bottomBtn.on(
        'click',
        function() {

            if (
                !editor ||
                !editor.codemirror
            ) {
                return;
            }

            var cm = editor.codemirror;

            var lastLine =
                cm.lastLine();

            var lastColumn =
                cm.getLine(lastLine).length;

            cm.setCursor(
                lastLine,
                lastColumn
            );

            cm.scrollIntoView(
                {
                    line: lastLine,
                    ch: lastColumn
                },
                100
            );

            cm.focus();

        }
    );


    /* =========================================================
       Fullscreen
       ========================================================= */

    function toggleFullscreen() {

        isFullscreen =
            !isFullscreen;


        if (isFullscreen) {

            editorBox.addClass(
                'is-fullscreen'
            );

            $('body').addClass(
                'ezp-editor-fullscreen'
            );

            fullscreenBtn.addClass(
                'active'
            );

        } else {

            editorBox.removeClass(
                'is-fullscreen'
            );

            $('body').removeClass(
                'ezp-editor-fullscreen'
            );

            fullscreenBtn.removeClass(
                'active'
            );
        }


        if (
            editor &&
            editor.codemirror
        ) {

            setTimeout(
                function() {

                    editor.codemirror.refresh();

                },
                100
            );
        }
    }


    fullscreenBtn.on(
        'click',
        function() {

            toggleFullscreen();

        }
    );


    /* =========================================================
       Validation
       ========================================================= */

    function validateCode() {

        var codeContent =
            getEditorValue();


        if (
            !codeContent.trim()
        ) {

            validateResult
                .removeClass(
                    'valid invalid loading'
                )
                .addClass(
                    'invalid'
                )
                .text(
                    'کد خالی است. لطفاً کد PHP را وارد کنید.'
                );

            return;
        }


        validateResult
            .removeClass(
                'valid invalid'
            )
            .addClass(
                'loading'
            )
            .text(
                'در حال بررسی کد PHP...'
            );


        validateBtn.prop(
            'disabled',
            true
        );


        $.post(
            ajaxurl,
            {

                action:
                    'ezpurchase_validate_code',

                nonce:
                    nonce,

                code:
                    codeContent

            }
        )
        .done(
            function(response) {

                if (
                    response &&
                    response.success
                ) {

                    validateResult
                        .removeClass(
                            'loading invalid'
                        )
                        .addClass(
                            'valid'
                        )
                        .text(
                            response.data &&
                            response.data.message
                                ? response.data.message
                                : 'کد PHP از نظر نحوی صحیح است.'
                        );

                } else {

                    validateResult
                        .removeClass(
                            'loading valid'
                        )
                        .addClass(
                            'invalid'
                        )
                        .text(
                            response &&
                            response.data &&
                            response.data.message
                                ? response.data.message
                                : 'خطا در بررسی کد.'
                        );
                }

            }
        )
        .fail(
            function() {

                validateResult
                    .removeClass(
                        'loading valid'
                    )
                    .addClass(
                        'invalid'
                    )
                    .text(
                        'ارتباط با سرور برقرار نشد.'
                    );

            }
        )
        .always(
            function() {

                validateBtn.prop(
                    'disabled',
                    false
                );

            }
        );
    }


    validateBtn.on(
        'click',
        validateCode
    );


    /* =========================================================
       Toast
       ========================================================= */

    function showToast(
        message,
        type
    ) {

        var toast =
            $('#ezp-toast');

        toast
            .removeClass(
                'success error'
            )
            .addClass(
                type
            );

        toast
            .find('.toast-msg')
            .text(
                message
            );

        toast.addClass(
            'show'
        );


        clearTimeout(
            toast.data('timer')
        );


        var timer =
            setTimeout(
                function() {

                    toast.removeClass(
                        'show'
                    );

                },
                4500
            );


        toast.data(
            'timer',
            timer
        );
    }


    $('.ezp-toast-close').on(
        'click',
        function() {

            $('#ezp-toast')
                .removeClass('show');

        }
    );


    /* =========================================================
       Save Button State
       ========================================================= */

    function setSavingState(
        saving
    ) {

        isSaving =
            saving;


        if (saving) {

            saveBtn.prop(
                'disabled',
                true
            );

            spinner.addClass(
                'active'
            );

            saveText.text(
                'در حال ذخیره...'
            );

            $('.ezp-save-icon').hide();

        } else {

            saveBtn.prop(
                'disabled',
                false
            );

            spinner.removeClass(
                'active'
            );

            saveText.text(
                submitText
            );

            $('.ezp-save-icon').show();
        }
    }


    /* =========================================================
       Submit Form
       ========================================================= */

    editForm.on(
        'submit',
        function(event) {

            event.preventDefault();


            if (isSaving) {
                return;
            }


            var codeContent =
                getEditorValue();


            var title =
                $.trim(
                    $('#ezp-title').val()
                );


            var filename =
                $.trim(
                    $('#ezp-filename').val()
                );


            var description =
                $('#ezp-desc').val();


            var id =
                $('input[name="id"]').val();


            if (!title) {

                showToast(
                    'لطفاً عنوان نمایشی را وارد کنید.',
                    'error'
                );

                $('#ezp-title').focus();

                return;
            }


            if (!filename) {

                showToast(
                    'لطفاً نام فایل انگلیسی را وارد کنید.',
                    'error'
                );

                $('#ezp-filename').focus();

                return;
            }


            if (!/^[A-Za-z0-9_-]+$/.test(filename)) {

                showToast(
                    'نام فایل فقط می‌تواند شامل حروف انگلیسی، عدد، - و _ باشد.',
                    'error'
                );

                $('#ezp-filename').focus();

                return;
            }


            if (!codeContent.trim()) {

                showToast(
                    'لطفاً کد PHP را وارد کنید.',
                    'error'
                );

                if (
                    editor &&
                    editor.codemirror
                ) {
                    editor.codemirror.focus();
                }

                return;
            }


            var formData = {

                action:
                    'ezpurchase_save_file',

                nonce:
                    nonce,

                id:
                    id,

                title:
                    title,

                filename:
                    filename,

                description:
                    description,

                code:
                    codeContent,

                activate:
                    activate.is(':checked')
                        ? 1
                        : 0
            };


            setSavingState(
                true
            );


            $.post(
                ajaxurl,
                formData
            )
            .done(
                function(response) {

                    if (
                        response &&
                        response.success
                    ) {

                        originalCode =
                            codeContent;

                        updateEditorStats();


                        showToast(
                            response.data &&
                            response.data.message
                                ? response.data.message
                                : 'فایل با موفقیت ذخیره شد.',
                            'success'
                        );


                        if (!id) {

                            setTimeout(
                                function() {

                                    window.location.href =
                                        listUrl;

                                },
                                1000
                            );
                        }

                    } else {

                        showToast(
                            response &&
                            response.data &&
                            response.data.message
                                ? response.data.message
                                : 'خطا در ذخیره فایل.',
                            'error'
                        );
                    }

                }
            )
            .fail(
                function() {

                    showToast(
                        'ارتباط با سرور برقرار نشد.',
                        'error'
                    );

                }
            )
            .always(
                function() {

                    setSavingState(
                        false
                    );

                }
            );
        }
    );


    /* =========================================================
       Save & Activate
       ========================================================= */

    $('#ezp-save-and-activate').on(
        'click',
        function() {

            activate.prop(
                'checked',
                true
            );

            editForm.trigger(
                'submit'
            );

        }
    );


    /* =========================================================
       Keyboard Shortcuts
       ========================================================= */

    $(document).on(
        'keydown',
        function(event) {

            var key =
                String(event.key).toLowerCase();


            /* Ctrl / Cmd + S */

            if (
                (event.ctrlKey || event.metaKey) &&
                key === 's'
            ) {

                if (
                    editForm.is(':visible')
                ) {

                    event.preventDefault();

                    editForm.trigger(
                        'submit'
                    );
                }
            }


            /* Ctrl / Cmd + Enter */

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key === 'Enter'
            ) {

                if (
                    editForm.is(':visible')
                ) {

                    event.preventDefault();

                    validateCode();
                }
            }


            /* Escape */

            if (
                event.key === 'Escape' &&
                isFullscreen
            ) {

                event.preventDefault();

                toggleFullscreen();
            }

        }
    );


    /* =========================================================
       Before Unload Warning
       ========================================================= */

    window.addEventListener(
        'beforeunload',
        function(event) {

            if (
                !editor ||
                !editor.codemirror ||
                isSaving
            ) {
                return;
            }


            var current =
                editor.codemirror.getValue();


            if (
                current !== originalCode
            ) {

                event.preventDefault();

                event.returnValue =
                    '';

                return '';
            }
        }
    );


    /* =========================================================
       Initial Load
       ========================================================= */

    $(document).ready(
        function() {

            initEditor();

        }
    );


})(jQuery);
</script>