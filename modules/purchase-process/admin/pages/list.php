<?php
/**
 * صفحه مدیریت فایل‌های فرآیند خرید
 *
 * @package EzLens_Secure_Login
 * @subpackage Purchase_Process
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$table    = $wpdb->prefix . 'ezlens_purchase_scripts';
$per_page = 10;
$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$offset   = ( $paged - 1 ) * $per_page;

/**
 * ---------------------------------------------------------
 * آمار
 * ---------------------------------------------------------
 */
$total_all = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$table}"
);

$total_active = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$table} WHERE status = 1"
);

$total_inactive = max( 0, $total_all - $total_active );

/**
 * ---------------------------------------------------------
 * لیست فایل‌ها
 * ---------------------------------------------------------
 */
$items = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$table}
         ORDER BY id DESC
         LIMIT %d OFFSET %d",
        $per_page,
        $offset
    )
);

$total_pages = $total_all > 0
    ? (int) ceil( $total_all / $per_page )
    : 0;

/**
 * ---------------------------------------------------------
 * آیکون‌ها
 * ---------------------------------------------------------
 */
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

$ico_add = ezp_get_svg_icon(
    'plus',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>'
);

$ico_edit = ezp_get_svg_icon(
    'edit',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>'
);

$ico_delete = ezp_get_svg_icon(
    'trash',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 15H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>'
);

$ico_check = ezp_get_svg_icon(
    'check',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>'
);

$ico_x = ezp_get_svg_icon(
    'x',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>'
);

$ico_file = ezp_get_svg_icon(
    'file',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>'
);

$ico_folder = ezp_get_svg_icon(
    'folder',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg>'
);

$ico_database = ezp_get_svg_icon(
    'database',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>'
);

/**
 * URL افزودن فایل
 */
$add_url = admin_url(
    'admin.php?page=ezlens-purchase-process-add'
);
?>

<style>
/* =========================================================
   EzLens Purchase Process
   Minimal Admin UI
   ========================================================= */

.ezp-page {
    --ezp-primary: #031f8a;
    --ezp-primary-hover: #02176a;

    --ezp-text: #172033;
    --ezp-muted: #7b8496;

    --ezp-border: #e7eaf0;
    --ezp-border-soft: #f0f2f6;

    --ezp-bg: #f7f8fa;
    --ezp-card: #ffffff;

    --ezp-success: #159a6b;
    --ezp-success-bg: #eaf8f2;

    --ezp-danger: #dc4b4b;
    --ezp-danger-bg: #fff0f0;

    --ezp-radius: 10px;
    --ezp-radius-sm: 7px;

    --ezp-shadow: 0 1px 2px rgba(16, 24, 40, .03);

    direction: rtl;
    max-width: 1380px;
    margin: 0 auto;
    padding: 20px 10px 40px;

    color: var(--ezp-text);
    font-family:
        IRANYekan,
        Vazirmatn,
        Tahoma,
        Arial,
        sans-serif;
}

/* =========================================================
   Header
   ========================================================= */

.ezp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}

.ezp-header-main {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ezp-header-icon {
    width: 42px;
    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #eef2ff;
    color: var(--ezp-primary);

    border-radius: 10px;
}

.ezp-header-icon svg {
    width: 21px;
    height: 21px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.ezp-header-title h1 {
    margin: 0;

    font-size: 18px;
    line-height: 1.4;
    font-weight: 700;

    color: var(--ezp-text);
}

.ezp-header-title p {
    margin: 3px 0 0;

    font-size: 12px;
    color: var(--ezp-muted);
}

.ezp-header-count {
    display: inline-flex;
    align-items: center;

    margin-right: 8px;
    padding: 3px 8px;

    border-radius: 999px;

    background: #f1f3f7;
    color: #667085;

    font-size: 11px;
    font-weight: 600;
}

/* =========================================================
   Add button
   ========================================================= */

.ezp-primary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    min-height: 38px;
    padding: 0 15px;

    border: 1px solid var(--ezp-primary);
    border-radius: var(--ezp-radius-sm);

    background: var(--ezp-primary);
    color: #fff !important;

    font-size: 12px;
    font-weight: 600;
    text-decoration: none;

    transition:
        background .18s ease,
        border-color .18s ease,
        transform .18s ease;
}

.ezp-primary-btn:hover {
    background: var(--ezp-primary-hover);
    border-color: var(--ezp-primary-hover);
    color: #fff !important;
    transform: translateY(-1px);
}

.ezp-primary-btn svg {
    width: 16px;
    height: 16px;

    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
}

/* =========================================================
   Stats
   ========================================================= */

.ezp-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;

    margin-bottom: 18px;
}

.ezp-stat {
    display: flex;
    align-items: center;
    gap: 12px;

    min-height: 68px;
    padding: 12px 14px;

    background: var(--ezp-card);
    border: 1px solid var(--ezp-border);
    border-radius: var(--ezp-radius);

    box-shadow: var(--ezp-shadow);
}

.ezp-stat-icon {
    width: 34px;
    height: 34px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 8px;

    background: #f3f5f8;
    color: #667085;

    flex: 0 0 auto;
}

.ezp-stat-icon svg {
    width: 17px;
    height: 17px;

    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.ezp-stat.success .ezp-stat-icon {
    background: var(--ezp-success-bg);
    color: var(--ezp-success);
}

.ezp-stat.primary .ezp-stat-icon {
    background: #eef2ff;
    color: var(--ezp-primary);
}

.ezp-stat.danger .ezp-stat-icon {
    background: var(--ezp-danger-bg);
    color: var(--ezp-danger);
}

.ezp-stat-content {
    min-width: 0;
}

.ezp-stat-number {
    display: block;

    font-size: 18px;
    line-height: 1.2;
    font-weight: 700;

    color: var(--ezp-text);
}

.ezp-stat-label {
    display: block;

    margin-top: 3px;

    font-size: 11px;
    color: var(--ezp-muted);
}

/* =========================================================
   Table card
   ========================================================= */

.ezp-table-card {
    overflow: hidden;

    background: var(--ezp-card);
    border: 1px solid var(--ezp-border);
    border-radius: var(--ezp-radius);

    box-shadow: var(--ezp-shadow);
}

.ezp-table-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;

    min-height: 48px;
    padding: 0 15px;

    border-bottom: 1px solid var(--ezp-border-soft);
}

.ezp-table-toolbar-title {
    display: flex;
    align-items: center;
    gap: 7px;

    font-size: 12px;
    font-weight: 600;
}

.ezp-table-toolbar-title svg {
    width: 15px;
    height: 15px;

    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
}

.ezp-table-toolbar-info {
    color: var(--ezp-muted);
    font-size: 11px;
}

/* =========================================================
   Table
   ========================================================= */

.ezp-table-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.ezp-table {
    width: 100%;
    min-width: 720px;

    border-collapse: collapse;
    border-spacing: 0;

    font-size: 12px;
}

.ezp-table th {
    height: 42px;
    padding: 0 15px;

    background: #fafbfc;

    border-bottom: 1px solid var(--ezp-border);

    color: #8991a1;

    font-size: 10px;
    font-weight: 600;

    text-align: right;
    white-space: nowrap;
}

.ezp-table td {
    padding: 13px 15px;

    border-bottom: 1px solid var(--ezp-border-soft);

    vertical-align: middle;

    color: var(--ezp-text);
}

.ezp-table tbody tr {
    transition: background .15s ease;
}

.ezp-table tbody tr:hover {
    background: #fafbfe;
}

.ezp-table tbody tr:last-child td {
    border-bottom: 0;
}

/* =========================================================
   Number
   ========================================================= */

.ezp-row-number {
    color: #a1a8b5;
    font-size: 11px;
    font-variant-numeric: tabular-nums;
}

/* =========================================================
   File identity
   ========================================================= */

.ezp-file {
    display: flex;
    align-items: center;
    gap: 10px;

    min-width: 210px;
}

.ezp-file-icon {
    width: 34px;
    height: 34px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 auto;

    border-radius: 8px;

    background: #f3f5f8;
    color: #667085;
}

.ezp-file-icon svg {
    width: 16px;
    height: 16px;

    fill: none;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.ezp-file-info {
    min-width: 0;
}

.ezp-file-title {
    overflow: hidden;

    color: var(--ezp-text);

    font-size: 12px;
    font-weight: 600;

    white-space: nowrap;
    text-overflow: ellipsis;
}

.ezp-file-name {
    margin-top: 3px;

    direction: ltr;
    text-align: right;

    overflow: hidden;

    color: #9aa1ad;

    font-family: Consolas, Monaco, monospace;
    font-size: 9px;

    white-space: nowrap;
    text-overflow: ellipsis;
}

/* =========================================================
   Description
   ========================================================= */

.ezp-description {
    max-width: 280px;

    overflow: hidden;

    color: #737c8c;

    font-size: 11px;

    white-space: nowrap;
    text-overflow: ellipsis;
}

/* =========================================================
   Status
   ========================================================= */

.ezp-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;

    min-height: 26px;
    padding: 0 9px;

    border: 1px solid transparent;
    border-radius: 999px;

    font-family: inherit;
    font-size: 10px;
    font-weight: 600;

    cursor: pointer;

    transition:
        background .15s ease,
        border-color .15s ease;
}

.ezp-status svg {
    width: 12px;
    height: 12px;

    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.ezp-status.active {
    background: var(--ezp-success-bg);
    border-color: #d5f0e4;
    color: var(--ezp-success);
}

.ezp-status.active:hover {
    background: #e1f5ec;
}

.ezp-status.inactive {
    background: var(--ezp-danger-bg);
    border-color: #f8dada;
    color: var(--ezp-danger);
}

.ezp-status.inactive:hover {
    background: #ffe7e7;
}

.ezp-status:disabled {
    opacity: .55;
    cursor: wait;
}

/* =========================================================
   Actions
   ========================================================= */

.ezp-actions {
    display: flex;
    align-items: center;
    gap: 5px;
}

.ezp-action {
    position: relative;

    width: 30px;
    height: 30px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0;

    border: 1px solid transparent;
    border-radius: 7px;

    background: transparent;
    color: #8a93a3;

    cursor: pointer;

    text-decoration: none;

    transition:
        color .15s ease,
        background .15s ease,
        border-color .15s ease;
}

.ezp-action svg {
    width: 15px;
    height: 15px;

    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.ezp-action:hover {
    color: var(--ezp-primary);
    background: #f2f4ff;
    border-color: #e2e6fb;
}

.ezp-action.delete:hover {
    color: var(--ezp-danger);
    background: var(--ezp-danger-bg);
    border-color: #f8dada;
}

/* Tooltip */

.ezp-action[data-tooltip]::after {
    content: attr(data-tooltip);

    position: absolute;
    bottom: calc(100% + 7px);
    right: 50%;

    transform:
        translateX(50%)
        translateY(3px);

    padding: 5px 8px;

    border-radius: 5px;

    background: #1e293b;
    color: #fff;

    font-size: 9px;
    font-weight: 500;

    white-space: nowrap;

    opacity: 0;
    visibility: hidden;

    pointer-events: none;

    transition:
        opacity .15s ease,
        transform .15s ease;
}

.ezp-action[data-tooltip]:hover::after {
    opacity: 1;
    visibility: visible;

    transform:
        translateX(50%)
        translateY(0);
}

/* =========================================================
   Pagination
   ========================================================= */

.ezp-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;

    min-height: 54px;
    padding: 0 15px;

    border-top: 1px solid var(--ezp-border);
    background: #fafbfc;
}

.ezp-pagination-info {
    color: var(--ezp-muted);
    font-size: 10px;
}

.ezp-pagination-info strong {
    color: var(--ezp-text);
    font-weight: 600;
}

.ezp-pagination-links {
    display: flex;
    align-items: center;
    gap: 3px;
}

.ezp-pagination-links .page-numbers {
    min-width: 29px;
    height: 29px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0 7px;

    border: 1px solid transparent;
    border-radius: 6px;

    background: transparent;
    color: #687386;

    font-size: 10px;
    font-weight: 600;

    text-decoration: none;

    transition: all .15s ease;
}

.ezp-pagination-links .page-numbers:hover {
    background: #fff;
    border-color: var(--ezp-border);
    color: var(--ezp-primary);
}

.ezp-pagination-links .page-numbers.current {
    background: var(--ezp-primary);
    border-color: var(--ezp-primary);
    color: #fff;
}

.ezp-pagination-links .page-numbers.dots {
    pointer-events: none;
}

/* =========================================================
   Empty state
   ========================================================= */

.ezp-empty {
    padding: 70px 20px;

    text-align: center;
}

.ezp-empty-icon {
    width: 52px;
    height: 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin: 0 auto 14px;

    border-radius: 12px;

    background: #f3f5f8;
    color: #8c95a5;
}

.ezp-empty-icon svg {
    width: 23px;
    height: 23px;

    fill: none;
    stroke: currentColor;
    stroke-width: 1.6;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.ezp-empty h3 {
    margin: 0 0 5px;

    font-size: 14px;
    font-weight: 700;

    color: var(--ezp-text);
}

.ezp-empty p {
    margin: 0 0 18px;

    color: var(--ezp-muted);

    font-size: 11px;
}

/* =========================================================
   Toast
   ========================================================= */

#ezpurchase-toast {
    position: fixed;

    left: 24px;
    bottom: 24px;

    z-index: 999999;

    min-width: 280px;
    max-width: 380px;

    display: flex;
    align-items: center;
    gap: 10px;

    padding: 12px 14px;

    background: #fff;

    border: 1px solid var(--ezp-border);
    border-right: 3px solid var(--ezp-primary);

    border-radius: 9px;

    box-shadow:
        0 10px 30px rgba(16, 24, 40, .12);

    opacity: 0;
    visibility: hidden;

    transform: translateY(8px);

    transition:
        opacity .2s ease,
        transform .2s ease,
        visibility .2s ease;

    font-family:
        IRANYekan,
        Vazirmatn,
        Tahoma,
        Arial,
        sans-serif;
}

#ezpurchase-toast.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

#ezpurchase-toast.success {
    border-right-color: var(--ezp-success);
}

#ezpurchase-toast.error {
    border-right-color: var(--ezp-danger);
}

#ezpurchase-toast .msg {
    flex: 1;

    color: var(--ezp-text);

    font-size: 11px;
    line-height: 1.7;
}

#ezpurchase-toast .close {
    width: 24px;
    height: 24px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 auto;

    border-radius: 5px;

    color: #98a1b1;

    cursor: pointer;

    font-size: 13px;

    transition:
        background .15s ease,
        color .15s ease;
}

#ezpurchase-toast .close:hover {
    background: #f3f4f6;
    color: var(--ezp-text);
}

/* =========================================================
   Responsive
   ========================================================= */

@media (max-width: 900px) {

    .ezp-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .ezp-header {
        align-items: flex-start;
    }
}

@media (max-width: 650px) {

    .ezp-page {
        padding: 15px 5px 30px;
    }

    .ezp-header {
        flex-direction: column;
    }

    .ezp-header-main {
        width: 100%;
    }

    .ezp-primary-btn {
        width: 100%;
    }

    .ezp-stats {
        grid-template-columns: 1fr 1fr;
        gap: 7px;
    }

    .ezp-stat {
        min-height: 60px;
        padding: 10px;
    }

    .ezp-stat-icon {
        width: 30px;
        height: 30px;
    }

    .ezp-stat-number {
        font-size: 16px;
    }

    .ezp-pagination {
        flex-direction: column;
        justify-content: center;
        padding: 12px;
    }

    .ezp-pagination-links {
        justify-content: center;
    }
}

@media (max-width: 420px) {

    .ezp-stats {
        grid-template-columns: 1fr;
    }

    .ezp-header-title h1 {
        font-size: 16px;
    }

    #ezpurchase-toast {
        left: 12px;
        right: 12px;
        bottom: 12px;

        min-width: 0;
        max-width: none;
    }
}
</style>

<div class="ezp-page">

<!-- =====================================================
     Header
     ===================================================== -->

<div class="ezp-header">

    <div class="ezp-header-main">

        <div class="ezp-header-icon">
            <?php echo $ico_file; ?>
        </div>

        <div class="ezp-header-title">

            <h1>
                مدیریت فایل‌های سفارشی

                <span class="ezp-header-count">
                    <?php echo esc_html( number_format_i18n( $total_all ) ); ?>
                </span>
            </h1>

            <p>
                مدیریت فایل‌های PHP مورد استفاده در فرآیند خرید
            </p>

        </div>

    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;">

        <a
            href="<?php echo esc_url( $add_url ); ?>"
            class="ezp-primary-btn"
        >
            <?php echo $ico_add; ?>
            <span>افزودن فایل</span>
        </a>

        <!-- ===== دکمه بازسازی جدول ===== -->
        <button
            type="button"
            id="ezp-force-install-btn"
            class="ezp-primary-btn"
            style="background:#dc2626;border-color:#dc2626;"
        >
            <?php echo $ico_database; ?>
            <span>بازسازی جدول</span>
        </button>

    </div>

</div>


<!-- =====================================================
     Statistics
     ===================================================== -->

<div class="ezp-stats">

    <div class="ezp-stat primary">

        <div class="ezp-stat-icon">
            <?php echo $ico_file; ?>
        </div>

        <div class="ezp-stat-content">

            <span class="ezp-stat-number">
                <?php echo esc_html( number_format_i18n( $total_all ) ); ?>
            </span>

            <span class="ezp-stat-label">
                کل فایل‌ها
            </span>

        </div>

    </div>


    <div class="ezp-stat success">

        <div class="ezp-stat-icon">
            <?php echo $ico_check; ?>
        </div>

        <div class="ezp-stat-content">

            <span class="ezp-stat-number">
                <?php echo esc_html( number_format_i18n( $total_active ) ); ?>
            </span>

            <span class="ezp-stat-label">
                فعال
            </span>

        </div>

    </div>


    <div class="ezp-stat danger">

        <div class="ezp-stat-icon">
            <?php echo $ico_x; ?>
        </div>

        <div class="ezp-stat-content">

            <span class="ezp-stat-number">
                <?php echo esc_html( number_format_i18n( $total_inactive ) ); ?>
            </span>

            <span class="ezp-stat-label">
                غیرفعال
            </span>

        </div>

    </div>


    <div class="ezp-stat">

        <div class="ezp-stat-icon">
            <?php echo $ico_folder; ?>
        </div>

        <div class="ezp-stat-content">

            <span class="ezp-stat-number">
                <?php echo esc_html( wp_date( 'Y/m/d' ) ); ?>
            </span>

            <span class="ezp-stat-label">
                تاریخ امروز
            </span>

        </div>

    </div>

</div>


<!-- =====================================================
     Table
     ===================================================== -->

<div class="ezp-table-card">

    <div class="ezp-table-toolbar">

        <div class="ezp-table-toolbar-title">
            <?php echo $ico_file; ?>
            فایل‌های فرآیند خرید
        </div>

        <div class="ezp-table-toolbar-info">
            <?php echo esc_html( number_format_i18n( $total_all ) ); ?> مورد
        </div>

    </div>


    <?php if ( empty( $items ) ) : ?>

        <!-- Empty -->

        <div class="ezp-empty">

            <div class="ezp-empty-icon">
                <?php echo $ico_folder; ?>
            </div>

            <h3>
                هنوز فایلی ایجاد نشده است
            </h3>

            <p>
                اولین فایل سفارشی فرآیند خرید را ایجاد کنید.
            </p>

            <a
                href="<?php echo esc_url( $add_url ); ?>"
                class="ezp-primary-btn"
            >
                <?php echo $ico_add; ?>
                ایجاد اولین فایل
            </a>

        </div>

    <?php else : ?>

        <div class="ezp-table-scroll">

            <table class="ezp-table">

                <thead>

                    <tr>

                        <th style="width:45px;">
                            #
                        </th>

                        <th>
                            فایل
                        </th>

                        <th>
                            توضیحات
                        </th>

                        <th style="width:110px;">
                            وضعیت
                        </th>

                        <th style="width:90px;">
                            عملیات
                        </th>

                    </tr>

                </thead>

                <tbody id="ezpurchase-list-body">

                <?php foreach ( $items as $index => $item ) : ?>

                    <?php

                    // از همان صفحه add برای ویرایش استفاده می‌شود (slug ثبت‌شده در منو)
                    $edit_url = admin_url(
                        'admin.php?page=ezlens-purchase-process-add&id=' .
                        absint( $item->id )
                    );

                    $description = ! empty( $item->description )
                        ? wp_trim_words(
                            $item->description,
                            12,
                            '...'
                        )
                        : 'بدون توضیحات';

                    ?>

                    <tr
                        data-id="<?php echo esc_attr( $item->id ); ?>"
                        data-filename="<?php echo esc_attr( $item->filename ); ?>"
                    >

                        <!-- Number -->

                        <td>

                            <span class="ezp-row-number">
                                <?php
                                echo esc_html(
                                    $index + 1 + $offset
                                );
                                ?>
                            </span>

                        </td>


                        <!-- File -->

                        <td>

                            <div class="ezp-file">

                                <div class="ezp-file-icon">
                                    <?php echo $ico_file; ?>
                                </div>

                                <div class="ezp-file-info">

                                    <div class="ezp-file-title">
                                        <?php
                                        echo esc_html(
                                            $item->title
                                        );
                                        ?>
                                    </div>

                                    <div class="ezp-file-name">
                                        <?php
                                        echo esc_html(
                                            $item->filename
                                        );
                                        ?>
                                    </div>

                                </div>

                            </div>

                        </td>


                        <!-- Description -->

                        <td>

                            <div
                                class="ezp-description"
                                title="<?php echo esc_attr( $item->description ); ?>"
                            >
                                <?php echo esc_html( $description ); ?>
                            </div>

                        </td>


                        <!-- Status -->

                        <td>

                            <button
                                type="button"
                                class="ezp-status <?php echo $item->status ? 'active' : 'inactive'; ?> ezpurchase-toggle-status"
                                data-id="<?php echo esc_attr( $item->id ); ?>"
                                data-status="<?php echo esc_attr( $item->status ); ?>"
                            >

                                <?php
                                echo $item->status
                                    ? $ico_check
                                    : $ico_x;
                                ?>

                                <span>
                                    <?php
                                    echo $item->status
                                        ? 'فعال'
                                        : 'غیرفعال';
                                    ?>
                                </span>

                            </button>

                        </td>


                        <!-- Actions -->

                        <td>

                            <div class="ezp-actions">

                                <a
                                    href="<?php echo esc_url( $edit_url ); ?>"
                                    class="ezp-action"
                                    data-tooltip="ویرایش"
                                    aria-label="ویرایش"
                                >
                                    <?php echo $ico_edit; ?>
                                </a>

                                <button
                                    type="button"
                                    class="ezp-action delete ezpurchase-delete"
                                    data-id="<?php echo esc_attr( $item->id ); ?>"
                                    data-tooltip="حذف"
                                    aria-label="حذف"
                                >
                                    <?php echo $ico_delete; ?>
                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


        <!-- =================================================
             Pagination
             ================================================= -->

        <?php if ( $total_pages > 1 ) : ?>

            <div class="ezp-pagination">

                <div class="ezp-pagination-info">

                    نمایش

                    <strong>
                        <?php
                        echo esc_html(
                            number_format_i18n(
                                $offset + 1
                            )
                        );
                        ?>
                    </strong>

                    تا

                    <strong>
                        <?php
                        echo esc_html(
                            number_format_i18n(
                                min(
                                    $offset + $per_page,
                                    $total_all
                                )
                            )
                        );
                        ?>
                    </strong>

                    از

                    <strong>
                        <?php
                        echo esc_html(
                            number_format_i18n(
                                $total_all
                            )
                        );
                        ?>
                    </strong>

                </div>


                <div class="ezp-pagination-links">

                    <?php

                    $page_links = paginate_links(
                        array(
                            'base'      => add_query_arg(
                                'paged',
                                '%#%'
                            ),
                            'format'    => '',
                            'prev_text' => '‹',
                            'next_text' => '›',
                            'total'     => $total_pages,
                            'current'   => $paged,
                            'type'      => 'array',
                        )
                    );

                    if ( is_array( $page_links ) ) {

                        foreach ( $page_links as $link ) {

                            echo wp_kses_post( $link );

                        }

                    }

                    ?>

                </div>

            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

</div>

<!-- =========================================================
     Toast
     ========================================================= -->

<div id="ezpurchase-toast">

<span class="msg"></span>

<button
    type="button"
    class="close"
    aria-label="بستن"
>
    ×
</button>

</div>

<script>
(function($) {

    'use strict';

    var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

    var nonce = '<?php echo esc_js( wp_create_nonce( 'ezpurchase_nonce' ) ); ?>';

    var i18n = {
        confirm_delete: 'آیا از حذف این فایل مطمئن هستید؟',
        success: 'عملیات با موفقیت انجام شد.',
        error: 'خطایی در انجام عملیات رخ داد.'
    };


    /* =====================================================
       Toast
       ===================================================== */

    function showToast(message, type) {

        var toast = $('#ezpurchase-toast');

        toast
            .removeClass('success error')
            .addClass(type);

        toast
            .find('.msg')
            .text(message);

        toast.addClass('show');

        clearTimeout(
            toast.data('timer')
        );

        var timer = setTimeout(function() {

            toast.removeClass('show');

        }, 3500);

        toast.data('timer', timer);
    }


    $(document).on(
        'click',
        '#ezpurchase-toast .close',
        function() {

            $('#ezpurchase-toast')
                .removeClass('show');

        }
    );


    /* =====================================================
       Status Toggle
       ===================================================== */

    $(document).on(
        'click',
        '.ezpurchase-toggle-status',
        function(e) {

            e.preventDefault();

            var btn = $(this);

            var id = btn.data('id');

            var currentStatus =
                parseInt(
                    btn.attr('data-status'),
                    10
                );

            var newStatus =
                currentStatus ? 0 : 1;


            if (btn.prop('disabled')) {
                return;
            }


            btn.prop('disabled', true);


            $.post(
                ajaxurl,
                {
                    action: 'ezpurchase_toggle_status',
                    id: id,
                    status: newStatus,
                    nonce: nonce
                }
            )

            .done(function(response) {

                if (response.success) {

                    var statusText =
                        newStatus
                            ? 'فعال'
                            : 'غیرفعال';


                    var className =
                        newStatus
                            ? 'active'
                            : 'inactive';


                    var icon =
                        newStatus

                            ? '<?php echo wp_json_encode( $ico_check ); ?>'

                            : '<?php echo wp_json_encode( $ico_x ); ?>';


                    btn
                        .attr(
                            'data-status',
                            newStatus
                        )
                        .removeClass(
                            'active inactive'
                        )
                        .addClass(
                            className
                        )
                        .html(
                            icon +
                            '<span>' +
                            statusText +
                            '</span>'
                        );


                    showToast(
                        response.data &&
                        response.data.message
                            ? response.data.message
                            : i18n.success,
                        'success'
                    );

                } else {

                    showToast(
                        response.data &&
                        response.data.message
                            ? response.data.message
                            : i18n.error,
                        'error'
                    );
                }

            })

            .fail(function() {

                showToast(
                    i18n.error,
                    'error'
                );

            })

            .always(function() {

                btn.prop(
                    'disabled',
                    false
                );

            });

        }
    );


    /* =====================================================
       Delete
       ===================================================== */

    $(document).on(
        'click',
        '.ezpurchase-delete',
        function(e) {

            e.preventDefault();


            if (
                !window.confirm(
                    i18n.confirm_delete
                )
            ) {
                return;
            }


            var btn = $(this);

            var id = btn.data('id');

            var row = btn.closest('tr');


            if (btn.prop('disabled')) {
                return;
            }


            btn.prop(
                'disabled',
                true
            );


            $.post(
                ajaxurl,
                {
                    action: 'ezpurchase_delete_file',
                    id: id,
                    nonce: nonce
                }
            )

            .done(function(response) {

                if (response.success) {

                    row.fadeOut(
                        220,
                        function() {

                            $(this).remove();

                            if (
                                $('#ezpurchase-list-body tr')
                                    .length === 0
                            ) {

                                window.location.reload();

                            }

                        }
                    );


                    showToast(
                        response.data &&
                        response.data.message
                            ? response.data.message
                            : i18n.success,
                        'success'
                    );

                } else {

                    showToast(
                        response.data &&
                        response.data.message
                            ? response.data.message
                            : i18n.error,
                        'error'
                    );

                }

            })

            .fail(function() {

                showToast(
                    i18n.error,
                    'error'
                );

            })

            .always(function() {

                btn.prop(
                    'disabled',
                    false
                );

            });

        }
    );


    /* =====================================================
       ===== جدید: Force Install (ایجاد جدول) =====
       ===================================================== */

    $('#ezp-force-install-btn').on('click', function() {

        var btn = $(this);

        if (btn.prop('disabled')) {
            return;
        }

        if ( ! confirm('آیا مطمئن هستید؟ این عملیات جدول را در صورت عدم وجود ایجاد می‌کند.') ) {
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none;margin:0;"></span> در حال ایجاد...');

        $.post(
            ajaxurl,
            {
                action: 'ezpurchase_force_install',
                nonce: nonce
            }
        )
        .done(function(response) {
            if (response.success) {
                showToast(response.data.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                var errorMsg = response.data && response.data.message
                    ? response.data.message
                    : 'خطا در ایجاد جدول. لطفاً لاگ‌ها را بررسی کنید.';
                showToast('❌ ' + errorMsg, 'error');
                btn.prop('disabled', false).html('<?php echo $ico_database; ?> <span>بازسازی جدول</span>');
            }
        })
        .fail(function(xhr) {
            var msg = 'ارتباط با سرور برقرار نشد.';
            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                msg = xhr.responseJSON.data.message;
            } else if (xhr.status === 500) {
                msg = 'خطای ۵۰۰ در سرور. لطفاً لاگ‌های PHP را بررسی کنید.';
            }
            showToast('❌ ' + msg, 'error');
            btn.prop('disabled', false).html('<?php echo $ico_database; ?> <span>بازسازی جدول</span>');
        });

    });

})(jQuery);
</script>