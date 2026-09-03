<?php
/**
 * قالب صفحه بکاپ و API
 *
 * Version: 3.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

$api_key = '';

if (class_exists('EzLens_Auth_Settings')) {
    $api_key = EzLens_Auth_Settings::get('api_key');
}

$is_api_enabled = '1';

if (class_exists('EzLens_Auth_Settings')) {
    $is_api_enabled =
        EzLens_Auth_Settings::get('enable_api');

    if ($is_api_enabled === null || $is_api_enabled === '') {
        $is_api_enabled = '1';
    }
}

$site_url = home_url();

$rest_url = rest_url('ezlens/v1/');


$settings_count = 0;

if (class_exists('EzLens_Auth_Settings')) {

    $all_settings =
        EzLens_Auth_Settings::get_all();

    if (is_array($all_settings)) {
        $settings_count = count($all_settings);
    }
}


$codes_count = 4;

$logs_count = 0;

if (class_exists('EzLens_Auth_Logger')) {

    $logs_count =
        (int) EzLens_Auth_Logger::get_stats('login') +
        (int) EzLens_Auth_Logger::get_stats('logout') +
        (int) EzLens_Auth_Logger::get_stats('failed_login');
}


/*
|--------------------------------------------------------------------------
| Icons
|--------------------------------------------------------------------------
*/

$icon_export = '📤';
$icon_import = '📥';
$icon_copy   = '📋';
$icon_refresh = '🔄';


$icon_path =
    EZLAUTH_PLUGIN_DIR .
    'assets/icons/20/solid/';


$icon_file =
    $icon_path . 'arrow-down-tray.svg';

if (file_exists($icon_file)) {

    $content = file_get_contents($icon_file);

    if ($content !== false) {
        $icon_export = $content;
    }
}


$icon_file =
    $icon_path . 'arrow-up-tray.svg';

if (file_exists($icon_file)) {

    $content = file_get_contents($icon_file);

    if ($content !== false) {
        $icon_import = $content;
    }
}


$icon_file =
    $icon_path . 'document-duplicate.svg';

if (file_exists($icon_file)) {

    $content = file_get_contents($icon_file);

    if ($content !== false) {
        $icon_copy = $content;
    }
}


$icon_file =
    $icon_path . 'arrow-path-rounded-square.svg';

if (file_exists($icon_file)) {

    $content = file_get_contents($icon_file);

    if ($content !== false) {
        $icon_refresh = $content;
    }
}

?>

<div class="wrap ezlens-backup">

    <h1 class="wp-heading-inline">
        💾 بکاپ و API
    </h1>

    <p class="description">
        مدیریت بکاپ، ایمپورت/اکسپورت و دسترسی خارجی به داده‌های پلاگین
    </p>


    <?php if (isset($_GET['exported'])): ?>

        <div class="ezlens-notice success"
             role="alert"
             aria-live="polite">

            ✅ خروجی با موفقیت ایجاد شد.

        </div>

    <?php endif; ?>


    <?php if (isset($_GET['imported'])): ?>

        <div class="ezlens-notice success"
             role="alert"
             aria-live="polite">

            ✅ داده‌ها با موفقیت وارد شدند.

        </div>

    <?php endif; ?>


    <?php if (isset($_GET['error'])): ?>

        <div class="ezlens-notice error"
             role="alert"
             aria-live="polite">

            ❌ خطا:
            <?php
            echo esc_html(
                wp_unslash($_GET['error'])
            );
            ?>

        </div>

    <?php endif; ?>


    <!-- ==========================================================
         EXPORT
    =========================================================== -->

    <div class="card">

        <h2>
            <?php echo $icon_export; ?>
            خروجی‌گیری (Export)
        </h2>

        <p class="ezlens-description">
            خروجی شامل تنظیمات عمومی، کدهای سفارشی صفحات و لاگ‌ها
        </p>

        <div class="ezlens-action-row">

            <button
                type="button"
                id="export-data"
                class="button button-primary">

                <?php echo $icon_export; ?>

                دریافت خروجی JSON

            </button>


            <span class="ezlens-small-text">

                حجم داده:

                <strong>
                    <?php
                    echo number_format(
                        $settings_count + $codes_count
                    );
                    ?>
                </strong>

                آیتم تنظیمات +

                <strong>
                    <?php
                    echo number_format($logs_count);
                    ?>
                </strong>

                لاگ

            </span>

        </div>


        <div
            id="export-status"
            class="ezlens-status"
            style="display:none;">
        </div>

    </div>


    <!-- ==========================================================
         IMPORT
    =========================================================== -->

    <div class="card">

        <h2>
            <?php echo $icon_import; ?>
            وارد کردن (Import)
        </h2>

        <p class="ezlens-description">
            فایل JSON خروجی را آپلود کنید تا تنظیمات و کدها بازیابی شوند.
        </p>


        <form
            method="post"
            enctype="multipart/form-data"
            id="import-form">

            <?php
            wp_nonce_field(
                'ezlens_import_data',
                'import_nonce'
            );
            ?>


            <input
                type="hidden"
                name="action"
                value="ezlens_import_data">


            <div class="ezlens-action-row">

                <div class="file-wrap">

                    <span class="fake-btn">
                        📂 انتخاب فایل
                    </span>

                    <input
                        type="file"
                        name="import_file"
                        accept=".json"
                        required>

                </div>


                <button
                    type="submit"
                    class="button button-primary">

                    <?php echo $icon_import; ?>

                    وارد کردن

                </button>


                <span class="ezlens-small-text">
                    فقط فایل‌های JSON
                </span>

            </div>


            <div
                id="import-status"
                class="ezlens-status"
                style="display:none;">
            </div>

        </form>

    </div>


    <!-- ==========================================================
         API
    =========================================================== -->

    <div class="card">

        <h2>
            🔑 کلید دسترسی API
        </h2>

        <p class="ezlens-description">
            از این کلید برای اتصال برنامه‌های خارجی به داده‌های پلاگین استفاده کنید.
        </p>


        <!-- API STATUS -->

        <div class="api-status-box">

            <label>
                وضعیت API:
            </label>


            <div class="toggle-box">

                <input
                    type="checkbox"
                    id="enable_api"
                    <?php checked(
                        $is_api_enabled,
                        '1'
                    ); ?>>

                <span>
                    فعال
                </span>

            </div>


            <span class="ezlens-small-text api-status-description">

                با غیرفعال کردن، تمام درخواست‌های API مسدود می‌شوند.

            </span>

        </div>


        <!-- CURRENT KEY -->

        <div class="api-key-box">

            <div class="api-key-title">

                🔑
                <strong>
                    کلید فعلی
                </strong>

            </div>


            <div
                id="api-key-display"
                class="api-key-value"
                data-api-key="<?php echo esc_attr($api_key); ?>">

                <?php
                echo esc_html($api_key);
                ?>

            </div>


            <div class="api-key-actions">

                <button
                    type="button"
                    id="copy-api-key"
                    class="button button-secondary">

                    <?php echo $icon_copy; ?>

                    کپی

                </button>


                <button
                    type="button"
                    id="regenerate-api-key"
                    class="button button-secondary regenerate-button">

                    <?php echo $icon_refresh; ?>

                    بازسازی

                </button>

            </div>

        </div>


        <!-- ======================================================
             NEW KEY BOX
        ======================================================= -->

        <div
            id="api-key-result-box"
            class="api-key-result-box">

            <div class="api-key-result-title">

                🔐
                کلید API برای استفاده

            </div>


            <div class="api-key-result-description">

                کلید API در این قسمت نیز نمایش داده می‌شود تا بتوانید
                به راحتی آن را مشاهده و کپی کنید.

            </div>


            <div class="api-key-result-row">

                <input
                    type="text"
                    id="api-key-copy-field"
                    readonly
                    value="<?php echo esc_attr($api_key); ?>"
                    autocomplete="off"
                    spellcheck="false">


                <button
                    type="button"
                    id="copy-api-key-bottom"
                    class="button button-primary">

                    📋 کپی کلید

                </button>

            </div>

        </div>


        <!-- API USAGE -->

        <div class="api-info-box">

            <strong>
                📌 نحوه استفاده از API:
            </strong>


            <div class="api-endpoints">

                <div>
                    <code>
                        GET <?php echo esc_html($rest_url); ?>stats
                    </code>

                    <span>
                        دریافت آمار
                    </span>
                </div>


                <div>
                    <code>
                        GET <?php echo esc_html($rest_url); ?>logs?limit=10
                    </code>

                    <span>
                        دریافت لاگ‌ها
                    </span>
                </div>


                <div>
                    <code>
                        GET <?php echo esc_html($rest_url); ?>settings
                    </code>

                    <span>
                        دریافت تنظیمات
                    </span>
                </div>


                <div>
                    <code>
                        POST <?php echo esc_html($rest_url); ?>settings
                    </code>

                    <span>
                        به‌روزرسانی تنظیمات
                    </span>
                </div>

            </div>


            <div class="api-header-box">

                <strong>
                    Header:
                </strong>

                <code id="api-header-code">
                    X-API-Key:
                    <?php
                    echo esc_html($api_key);
                    ?>
                </code>

            </div>

        </div>

    </div>


    <!-- ==========================================================
         STATS
    =========================================================== -->

    <div class="card">

        <h2>
            📊 اطلاعات داده‌ها
        </h2>


        <div class="ezlens-backup-stats">


            <div class="stat-item">

                <div class="number">

                    <?php
                    echo number_format(
                        $settings_count
                    );
                    ?>

                </div>

                <div class="label">
                    تنظیمات
                </div>

            </div>


            <div class="stat-item">

                <div class="number">

                    <?php
                    echo number_format(
                        $codes_count
                    );
                    ?>

                </div>

                <div class="label">
                    کدهای صفحات
                </div>

            </div>


            <div class="stat-item">

                <div class="number">

                    <?php
                    echo number_format(
                        $logs_count
                    );
                    ?>

                </div>

                <div class="label">
                    لاگ‌ها
                </div>

            </div>


            <div class="stat-item">

                <div class="number">

                    <?php
                    echo esc_html(
                        date_i18n('Y/m/d')
                    );
                    ?>

                </div>

                <div class="label">
                    آخرین به‌روزرسانی
                </div>

            </div>


        </div>

    </div>

</div>


<style>

/* ============================================================
   Base
============================================================ */

.ezlens-backup .card {
    background: var(--ezlens-card, #ffffff);
    border: 1px solid var(--ezlens-border, #e5e7eb);
    border-radius: var(--ezlens-radius, 12px);
    padding: 20px 22px;
    box-shadow: var(--ezlens-shadow, 0 2px 10px rgba(0,0,0,.05));
    margin-bottom: 16px;
}


.ezlens-backup .card h2 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 6px;
    color: var(--ezlens-text, #1f2937);
}


.ezlens-description {
    color: var(--ezlens-muted, #6b7280);
    font-size: .85rem;
    margin: 0 0 12px;
}


.ezlens-small-text {
    color: var(--ezlens-muted, #6b7280);
    font-size: .75rem;
}


/* ============================================================
   Action Row
============================================================ */

.ezlens-action-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
}


/* ============================================================
   File
============================================================ */

.ezlens-backup .file-wrap {
    position: relative;
    display: inline-block;
}


.ezlens-backup .file-wrap .fake-btn {
    display: inline-block;
    padding: 8px 18px;
    background: var(--ezlens-bg, #f8fafc);
    border: 1px solid var(--ezlens-border, #e5e7eb);
    border-radius: 7px;
    cursor: pointer;
    font-weight: 600;
    font-size: .8rem;
}


.ezlens-backup .file-wrap input[type="file"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}


/* ============================================================
   API Status
============================================================ */

.api-status-box {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    margin: 14px 0;
    padding: 14px 16px;
    background: var(--ezlens-bg, #f8fafc);
    border-radius: 8px;
    border: 1px solid var(--ezlens-border, #e5e7eb);
}


.api-status-box > label {
    font-weight: 700;
    color: var(--ezlens-text, #1f2937);
}


.toggle-box {
    display: flex;
    align-items: center;
    gap: 8px;
}


.api-status-description {
    margin-right: auto;
}


/* ============================================================
   API Key
============================================================ */

.api-key-box {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--ezlens-success-bg, #f0fdf4);
    border-radius: 8px;
    border: 1px solid rgba(34,197,94,.2);
}


.api-key-title {
    font-size: .85rem;
    color: var(--ezlens-text, #1f2937);
    white-space: nowrap;
}


.api-key-value {
    flex: 1 1 300px;
    min-width: 200px;
    background: #ffffff;
    padding: 10px 14px;
    border-radius: 7px;
    border: 1px solid var(--ezlens-border, #e5e7eb);
    direction: ltr;
    text-align: left;
    word-break: break-all;
    font-family: monospace;
    font-size: .86rem;
    color: #111827;
    user-select: text;
    -webkit-user-select: text;
}


.api-key-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}


.regenerate-button {
    background: #fff7ed !important;
    border-color: #fed7aa !important;
    color: #9a3412 !important;
}


/* ============================================================
   Bottom Key Box
============================================================ */

.api-key-result-box {
    margin-top: 16px;
    padding: 18px;
    background: #f8fafc;
    border: 2px solid #dbeafe;
    border-radius: 10px;
}


.api-key-result-title {
    font-size: .95rem;
    font-weight: 700;
    color: #1e3a8a;
    margin-bottom: 5px;
}


.api-key-result-description {
    color: #64748b;
    font-size: .78rem;
    margin-bottom: 12px;
}


.api-key-result-row {
    display: flex;
    gap: 10px;
    align-items: stretch;
}


#api-key-copy-field {
    flex: 1;
    min-width: 0;
    height: 40px;
    direction: ltr;
    text-align: left;
    font-family: monospace;
    font-size: .85rem;
    padding: 7px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #111827;
    user-select: text;
    -webkit-user-select: text;
}


/* ============================================================
   API Info
============================================================ */

.api-info-box {
    margin-top: 14px;
    padding: 14px 16px;
    background: var(--ezlens-info-bg, #eff6ff);
    border-radius: 8px;
    border: 1px solid rgba(59,130,246,.2);
    font-size: .83rem;
    color: #2563eb;
}


.api-endpoints {
    margin-top: 10px;
    display: grid;
    gap: 7px;
}


.api-endpoints div {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}


.api-endpoints code {
    direction: ltr;
    text-align: left;
    font-size: .74rem;
    background: rgba(255,255,255,.8);
    padding: 4px 7px;
    border-radius: 5px;
}


.api-endpoints span {
    font-size: .74rem;
}


.api-header-box {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid rgba(59,130,246,.15);
}


.api-header-box code {
    direction: ltr;
    display: block;
    margin-top: 6px;
    word-break: break-all;
    background: #ffffff;
    padding: 8px;
    border-radius: 6px;
}


/* ============================================================
   Stats
============================================================ */

.ezlens-backup-stats {
    display: grid;
    grid-template-columns: repeat(
        auto-fit,
        minmax(150px, 1fr)
    );
    gap: 12px;
    margin-top: 12px;
}


.ezlens-backup-stats .stat-item {
    padding: 14px;
    background: var(--ezlens-bg, #f8fafc);
    border-radius: 8px;
    text-align: center;
    border: 1px solid var(--ezlens-border, #e5e7eb);
}


.ezlens-backup-stats .number {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--ezlens-primary, #4f46e5);
}


.ezlens-backup-stats .label {
    font-size: .75rem;
    color: var(--ezlens-muted, #6b7280);
}


/* ============================================================
   Status
============================================================ */

.ezlens-status {
    margin-top: 10px;
    font-size: .85rem;
}


/* ============================================================
   Responsive
============================================================ */

@media (max-width: 700px) {

    .api-key-result-row {
        flex-direction: column;
    }

    .api-key-result-row .button {
        width: 100%;
    }

    .api-key-actions {
        width: 100%;
    }

    .api-key-actions .button {
        flex: 1;
    }

    .api-status-description {
        width: 100%;
        margin-right: 0;
    }

}

</style>


<script>
(function () {

    'use strict';


    /*
    |--------------------------------------------------------------------------
    | Safe initialization
    |--------------------------------------------------------------------------
    */

    function initEzLensBackup() {

        if (typeof window.jQuery === 'undefined') {
            return;
        }

        var $ = window.jQuery;


        /*
        |--------------------------------------------------------------------------
        | Check AJAX object
        |--------------------------------------------------------------------------
        */

        if (
            typeof window.ezlens_auth_ajax === 'undefined'
        ) {

            console.error(
                'EzLens: ezlens_auth_ajax is not defined.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Toast
        |--------------------------------------------------------------------------
        */

        function showToast(message, type) {

            var $toast =
                $('#ezlens-toast');

            if (!$toast.length) {

                $toast =
                    $('<div id="ezlens-toast"></div>');

                $('body').append($toast);
            }


            $toast
                .stop(true, true)
                .removeClass(
                    'success error warning'
                )
                .addClass(type || 'success')
                .text(message)
                .fadeIn(200)
                .delay(2500)
                .fadeOut(300);
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback copy
        |--------------------------------------------------------------------------
        */

        function fallbackCopy(text) {

            var textarea =
                document.createElement('textarea');


            textarea.value = text;

            textarea.setAttribute(
                'readonly',
                ''
            );

            textarea.style.position =
                'fixed';

            textarea.style.left =
                '-9999px';

            textarea.style.top =
                '0';

            textarea.style.opacity =
                '0';


            document.body.appendChild(
                textarea
            );


            textarea.focus();

            textarea.select();

            textarea.setSelectionRange(
                0,
                textarea.value.length
            );


            var success = false;


            try {

                success =
                    document.execCommand(
                        'copy'
                    );

            } catch (error) {

                success = false;
            }


            document.body.removeChild(
                textarea
            );


            return success;
        }


        /*
        |--------------------------------------------------------------------------
        | Copy API Key
        |--------------------------------------------------------------------------
        */

        function copyApiKey(text) {

            if (!text) {

                showToast(
                    '⚠️ کلید API یافت نشد.',
                    'warning'
                );

                return;
            }


            /*
             * Modern Clipboard
             */
            if (
                navigator.clipboard &&
                typeof navigator.clipboard.writeText === 'function'
            ) {

                navigator.clipboard
                    .writeText(text)
                    .then(function () {

                        showToast(
                            '✅ کلید API کپی شد.',
                            'success'
                        );

                    })
                    .catch(function () {

                        if (
                            fallbackCopy(text)
                        ) {

                            showToast(
                                '✅ کلید API کپی شد.',
                                'success'
                            );

                        } else {

                            showToast(
                                '❌ کپی خودکار انجام نشد. کلید را دستی انتخاب کنید.',
                                'error'
                            );
                        }
                    });

                return;
            }


            /*
             * Old browser / WordPress fallback
             */
            if (fallbackCopy(text)) {

                showToast(
                    '✅ کلید API کپی شد.',
                    'success'
                );

            } else {

                showToast(
                    '❌ کپی خودکار انجام نشد. کلید را دستی انتخاب کنید.',
                    'error'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Get API key
        |--------------------------------------------------------------------------
        */

        function getApiKey() {

            var value =
                $('#api-key-display').attr(
                    'data-api-key'
                );


            if (!value) {

                value =
                    $('#api-key-display')
                        .text()
                        .trim();
            }


            return value || '';
        }


        /*
        |--------------------------------------------------------------------------
        | Sync API Key UI
        |--------------------------------------------------------------------------
        */

        function syncApiKey(key) {

            key = key || '';


            $('#api-key-display')
                .text(key)
                .attr(
                    'data-api-key',
                    key
                );


            $('#api-key-copy-field')
                .val(key);


            $('#api-header-code')
                .text(
                    'X-API-Key: ' + key
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Export
        |--------------------------------------------------------------------------
        */

        $('#export-data')
            .off('click.ezlens')
            .on(
                'click.ezlens',
                function (e) {

                    e.preventDefault();


                    var $btn =
                        $(this);

                    var $status =
                        $('#export-status');


                    $btn
                        .prop(
                            'disabled',
                            true
                        )
                        .text(
                            '⏳ در حال آماده‌سازی...'
                        );


                    $status
                        .show()
                        .css(
                            'color',
                            '#d97706'
                        )
                        .text(
                            '⏳ در حال ساخت خروجی...'
                        );


                    $.post(
                        window.ezlens_auth_ajax.ajax_url,
                        {
                            action:
                                'ezlens_export_data',

                            nonce:
                                window.ezlens_auth_ajax.nonce
                        }
                    )
                    .done(function (response) {

                        if (
                            response &&
                            response.success &&
                            response.data
                        ) {

                            var blob =
                                new Blob(
                                    [
                                        response.data.content
                                    ],
                                    {
                                        type:
                                            'application/json;charset=utf-8'
                                    }
                                );


                            var url =
                                URL.createObjectURL(
                                    blob
                                );


                            var a =
                                document.createElement(
                                    'a'
                                );


                            a.href =
                                url;

                            a.download =
                                response.data.filename ||
                                'ezlens-backup.json';


                            document.body.appendChild(
                                a
                            );

                            a.click();

                            document.body.removeChild(
                                a
                            );


                            setTimeout(
                                function () {

                                    URL.revokeObjectURL(
                                        url
                                    );

                                },
                                1000
                            );


                            $status
                                .css(
                                    'color',
                                    '#16a34a'
                                )
                                .text(
                                    '✅ خروجی با موفقیت ایجاد شد.'
                                );


                        } else {

                            var message =
                                response &&
                                response.data
                                    ? response.data
                                    : 'خطای نامشخص';

                            $status
                                .css(
                                    'color',
                                    '#dc2626'
                                )
                                .text(
                                    '❌ ' + message
                                );
                        }

                    })
                    .fail(function () {

                        $status
                            .css(
                                'color',
                                '#dc2626'
                            )
                            .text(
                                '❌ خطا در ارتباط با سرور.'
                            );

                    })
                    .always(function () {

                        $btn
                            .prop(
                                'disabled',
                                false
                            )
                            .html(
                                <?php
                                echo wp_json_encode(
                                    $icon_export .
                                    ' دریافت خروجی JSON'
                                );
                                ?>
                            );

                    });

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Copy button - top
        |--------------------------------------------------------------------------
        */

        $('#copy-api-key')
            .off('click.ezlens')
            .on(
                'click.ezlens',
                function (e) {

                    e.preventDefault();

                    copyApiKey(
                        getApiKey()
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Copy button - bottom
        |--------------------------------------------------------------------------
        */

        $('#copy-api-key-bottom')
            .off('click.ezlens')
            .on(
                'click.ezlens',
                function (e) {

                    e.preventDefault();

                    var key =
                        $('#api-key-copy-field')
                            .val()
                            .trim();


                    copyApiKey(key);

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Regenerate API Key
        |--------------------------------------------------------------------------
        */

        $('#regenerate-api-key')
            .off('click.ezlens')
            .on(
                'click.ezlens',
                function (e) {

                    e.preventDefault();


                    var confirmed =
                        window.confirm(
                            '⚠️ با بازسازی کلید، کلید قبلی منقضی می‌شود. آیا مطمئن هستید؟'
                        );


                    if (!confirmed) {
                        return;
                    }


                    var $btn =
                        $(this);


                    $btn
                        .prop(
                            'disabled',
                            true
                        )
                        .text(
                            '⏳ در حال بازسازی...'
                        );


                    $.post(
                        window.ezlens_auth_ajax.ajax_url,
                        {
                            action:
                                'ezlens_regenerate_api_key',

                            nonce:
                                window.ezlens_auth_ajax.nonce
                        }
                    )
                    .done(function (response) {

                        if (
                            response &&
                            response.success &&
                            response.data &&
                            response.data.api_key
                        ) {

                            syncApiKey(
                                response.data.api_key
                            );


                            showToast(
                                '✅ کلید API با موفقیت بازسازی شد.',
                                'success'
                            );

                        } else {

                            showToast(
                                '❌ ' +
                                (
                                    response &&
                                    response.data
                                        ? response.data
                                        : 'خطا در بازسازی کلید'
                                ),
                                'error'
                            );
                        }

                    })
                    .fail(function () {

                        showToast(
                            '❌ خطا در ارتباط با سرور.',
                            'error'
                        );

                    })
                    .always(function () {

                        $btn
                            .prop(
                                'disabled',
                                false
                            )
                            .html(
                                <?php
                                echo wp_json_encode(
                                    $icon_refresh .
                                    ' بازسازی'
                                );
                                ?>
                            );

                    });

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Toggle API
        |--------------------------------------------------------------------------
        */

        $('#enable_api')
            .off('change.ezlens')
            .on(
                'change.ezlens',
                function () {

                    var $checkbox =
                        $(this);


                    var status =
                        $checkbox.is(':checked')
                            ? '1'
                            : '0';


                    $checkbox.prop(
                        'disabled',
                        true
                    );


                    $.post(
                        window.ezlens_auth_ajax.ajax_url,
                        {
                            action:
                                'ezlens_toggle_api',

                            nonce:
                                window.ezlens_auth_ajax.nonce,

                            status:
                                status
                        }
                    )
                    .done(function (response) {

                        if (
                            response &&
                            response.success
                        ) {

                            showToast(
                                '✅ وضعیت API تغییر کرد: ' +
                                (
                                    status === '1'
                                        ? 'فعال'
                                        : 'غیرفعال'
                                ),
                                'success'
                            );

                        } else {

                            $checkbox.prop(
                                'checked',
                                status !== '1'
                            );


                            showToast(
                                '❌ ' +
                                (
                                    response &&
                                    response.data
                                        ? response.data
                                        : 'خطا در تغییر وضعیت API'
                                ),
                                'error'
                            );
                        }

                    })
                    .fail(function () {

                        $checkbox.prop(
                            'checked',
                            status !== '1'
                        );


                        showToast(
                            '❌ خطا در ارتباط با سرور.',
                            'error'
                        );

                    })
                    .always(function () {

                        $checkbox.prop(
                            'disabled',
                            false
                        );

                    });

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Toast CSS
        |--------------------------------------------------------------------------
        */

        if (
            !document.getElementById(
                'ezlens-backup-toast-style'
            )
        ) {

            var style =
                document.createElement(
                    'style'
                );


            style.id =
                'ezlens-backup-toast-style';


            style.textContent =
                '#ezlens-toast {' +
                'position:fixed;' +
                'top:80px;' +
                'left:50%;' +
                'transform:translateX(-50%);' +
                'padding:10px 24px;' +
                'border-radius:8px;' +
                'font-weight:600;' +
                'z-index:999999;' +
                'box-shadow:0 8px 30px rgba(0,0,0,.15);' +
                'display:none;' +
                'font-size:.85rem;' +
                'max-width:90%;' +
                'text-align:center;' +
                'background:#fff;' +
                'border:1px solid #e5e7eb;' +
                '}' +

                '#ezlens-toast.success {' +
                'background:#f0fdf4;' +
                'color:#15803d;' +
                'border-color:#bbf7d0;' +
                '}' +

                '#ezlens-toast.error {' +
                'background:#fef2f2;' +
                'color:#b91c1c;' +
                'border-color:#fecaca;' +
                '}' +

                '#ezlens-toast.warning {' +
                'background:#fffbeb;' +
                'color:#a16207;' +
                'border-color:#fde68a;' +
                '}';


            document.head.appendChild(
                style
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Initial sync
        |--------------------------------------------------------------------------
        */

        syncApiKey(
            getApiKey()
        );


        console.log(
            '✅ EzLens Backup & API loaded - 3.0.1'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DOM Ready
    |--------------------------------------------------------------------------
    */

    if (
        document.readyState === 'loading'
    ) {

        document.addEventListener(
            'DOMContentLoaded',
            initEzLensBackup
        );

    } else {

        initEzLensBackup();
    }

})();
</script>