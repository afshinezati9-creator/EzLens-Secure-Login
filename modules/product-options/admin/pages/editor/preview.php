<?php
if (!defined('ABSPATH')) exit;
?>

<div class="card preview-card">

    <!-- =====================================================
         PREVIEW HEADER
    ====================================================== -->

    <div class="preview-header">

        <div class="preview-header-info">

            <span
                class="ezlens-preview-icon"
                style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/eye.svg'); ?>');"
                aria-hidden="true"
            ></span>

            <div class="preview-title-wrap">
                <h3>پیش‌نمایش زنده</h3>

                <span class="preview-status">
                    <span class="preview-status-dot"></span>
                    همگام با سازنده
                </span>
            </div>

        </div>

        <button
            type="button"
            id="refresh-preview"
            class="preview-refresh"
            aria-label="بروزرسانی پیش‌نمایش"
            title="بروزرسانی پیش‌نمایش"
        >
            <span class="refresh-icon" aria-hidden="true"></span>
        </button>

    </div>


    <!-- =====================================================
         PREVIEW CANVAS
    ====================================================== -->

    <div class="preview-stage">

        <div
            id="preview-container"
            class="preview-box preview-desktop"
        >
            <div class="preview-placeholder">

                <div
                    class="preview-placeholder-icon"
                    aria-hidden="true"
                ></div>

                <strong>پیش‌نمایش خالی است</strong>

                <span>
                    فیلدها را از سازنده اضافه کنید تا نتیجه اینجا نمایش داده شود.
                </span>

            </div>
        </div>

    </div>


    <!-- =====================================================
         PREVIEW TOOLS
    ====================================================== -->

    <div class="preview-tools">

        <div
            class="preview-device-switcher"
            role="group"
            aria-label="اندازه پیش‌نمایش"
        >

            <button
                type="button"
                class="preview-device-btn active"
                data-device="desktop"
                aria-pressed="true"
            >
                دسکتاپ
            </button>

            <button
                type="button"
                class="preview-device-btn"
                data-device="tablet"
                aria-pressed="false"
            >
                تبلت
            </button>

            <button
                type="button"
                class="preview-device-btn"
                data-device="mobile"
                aria-pressed="false"
            >
                موبایل
            </button>

        </div>


        <div class="preview-meta">

            <span
                class="preview-count"
                id="preview-field-count"
            >
                ۰ فیلد
            </span>

            <span
                class="preview-divider"
                aria-hidden="true"
            ></span>

            <span class="preview-currency">
                قیمت‌ها: تومان
            </span>

        </div>

    </div>

</div>


<style>

/* =========================================================
   PREVIEW CARD
========================================================= */

.preview-card {
    position: relative;

    display: flex;
    flex-direction: column;

    width: 100%;
    height: 100%;
    min-height: 520px;

    padding: 0 !important;

    overflow: hidden;

    background: #ffffff;

    border: 1px solid #e2e8f0;
    border-radius: 12px;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, .03),
        0 8px 28px rgba(15, 23, 42, .045);

    box-sizing: border-box;
}


/* =========================================================
   HEADER
========================================================= */

.preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    min-height: 62px;

    padding: 0 16px 0 18px;

    background: #ffffff;

    border-bottom: 1px solid #e8edf3;

    box-sizing: border-box;
}

.preview-header-info {
    display: flex;
    align-items: center;

    gap: 10px;

    min-width: 0;
}

.ezlens-preview-icon {
    display: inline-flex;

    width: 18px;
    height: 18px;

    flex: 0 0 18px;

    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
}

.preview-title-wrap {
    display: flex;
    flex-direction: column;

    gap: 3px;

    min-width: 0;
}

.preview-title-wrap h3 {
    margin: 0;

    color: #0f172a;

    font-size: 14px;
    line-height: 18px;
    font-weight: 700;
}

.preview-status {
    display: inline-flex;
    align-items: center;

    gap: 5px;

    color: #94a3b8;

    font-size: 10px;
    line-height: 14px;
}

.preview-status-dot {
    width: 6px;
    height: 6px;

    flex: 0 0 6px;

    border-radius: 50%;

    background: #22c55e;

    box-shadow:
        0 0 0 3px rgba(34, 197, 94, .08);
}


/* =========================================================
   REFRESH BUTTON
========================================================= */

.preview-refresh {
    position: relative;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 32px;
    height: 32px;

    flex: 0 0 32px;

    padding: 0;

    border: 1px solid #e2e8f0;
    border-radius: 7px;

    background: #ffffff;

    cursor: pointer;

    transition:
        background .18s ease,
        border-color .18s ease,
        transform .18s ease,
        box-shadow .18s ease;
}

.preview-refresh:hover {
    background: #f8fafc;

    border-color: #cbd5e1;

    transform: translateY(-1px);

    box-shadow:
        0 3px 8px rgba(15, 23, 42, .07);
}

.preview-refresh:active {
    transform: translateY(0);
}

.preview-refresh:focus-visible,
.preview-device-btn:focus-visible {
    outline: 2px solid #93c5fd;
    outline-offset: 2px;
}

.refresh-icon {
    position: relative;

    display: block;

    width: 14px;
    height: 14px;

    border: 1.7px solid #64748b;
    border-left-color: transparent;

    border-radius: 50%;

    transition: transform .35s ease;

    box-sizing: border-box;
}

.refresh-icon::after {
    content: "";

    position: absolute;

    top: -2px;
    right: -2px;

    width: 0;
    height: 0;

    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
    border-right: 5px solid #64748b;

    transform: rotate(18deg);
}


/* =========================================================
   PREVIEW STAGE
========================================================= */

.preview-stage {
    flex: 1;

    min-height: 0;

    padding: 18px;

    overflow: auto;

    background:
        linear-gradient(
            135deg,
            #f8fafc 25%,
            transparent 25%
        ) 0 0 / 12px 12px,
        linear-gradient(
            225deg,
            #f8fafc 25%,
            transparent 25%
        ) 0 0 / 12px 12px,
        linear-gradient(
            315deg,
            #f8fafc 25%,
            transparent 25%
        ) 6px 0 / 12px 12px,
        linear-gradient(
            45deg,
            #f8fafc 25%,
            #ffffff 25%
        ) 6px 0 / 12px 12px;

    box-sizing: border-box;
}


/* =========================================================
   PREVIEW BOX
========================================================= */

.preview-box {
    position: relative;

    width: 100%;
    min-height: 360px;

    margin: 0 auto;
    padding: 20px;

    background: #ffffff;

    border: 1px solid #dbe3ec;
    border-radius: 10px;

    overflow: auto;

    box-shadow:
        0 2px 5px rgba(15, 23, 42, .025),
        0 8px 20px rgba(15, 23, 42, .035);

    transition:
        max-width .3s ease,
        min-height .3s ease,
        box-shadow .2s ease;

    box-sizing: border-box;
}

.preview-box.preview-desktop {
    max-width: 100%;
}

.preview-box.preview-tablet {
    max-width: 768px;
}

.preview-box.preview-mobile {
    max-width: 375px;
}


/* =========================================================
   PLACEHOLDER
========================================================= */

.preview-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    min-height: 280px;

    padding: 40px 20px;

    text-align: center;

    color: #94a3b8;

    box-sizing: border-box;
}

.preview-placeholder-icon {
    position: relative;

    width: 42px;
    height: 42px;

    margin-bottom: 14px;

    border: 1px solid #dbe3ec;
    border-radius: 10px;

    background: #f8fafc;

    box-sizing: border-box;
}

.preview-placeholder-icon::before {
    content: "";

    position: absolute;

    top: 11px;
    left: 9px;

    width: 22px;
    height: 15px;

    border: 1.5px solid #94a3b8;
    border-radius: 4px;

    box-sizing: border-box;
}

.preview-placeholder-icon::after {
    content: "";

    position: absolute;

    top: 15px;
    left: 15px;

    width: 7px;
    height: 7px;

    border: 1.5px solid #94a3b8;
    border-radius: 50%;

    box-sizing: border-box;
}

.preview-placeholder strong {
    margin-bottom: 5px;

    color: #475569;

    font-size: 13px;
    font-weight: 600;
}

.preview-placeholder span {
    max-width: 280px;

    color: #94a3b8;

    font-size: 11px;
    line-height: 18px;
}


/* =========================================================
   PREVIEW FIELDS
========================================================= */

.preview-fields {
    display: flex;

    width: 100%;

    flex-wrap: wrap;

    gap: 4px;

    box-sizing: border-box;
}

.preview-field {
    box-sizing: border-box;

    padding: 12px 0;

    border-bottom: 1px solid #f1f5f9;

    display: flex;
    flex-wrap: wrap;
    align-items: center;

    gap: 8px;

    transition:
        background .15s ease;
}

.preview-field:last-child {
    border-bottom: none;
}

.preview-field .field-label {
    min-width: 130px;

    flex-shrink: 0;

    color: #1e293b;

    font-size: 12px;
    font-weight: 600;
}

.preview-field .field-label .required {
    margin-right: 3px;

    color: #ef4444;
}

.preview-field .field-value {
    flex: 1;

    min-width: 100px;

    padding: 7px 10px;

    color: #475569;

    background: #f8fafc;

    border: 1px solid #e2e8f0;
    border-radius: 6px;

    font-size: 12px;

    box-sizing: border-box;
}

.preview-field .field-price {
    padding: 3px 8px;

    color: #2563eb;

    background: #eff6ff;

    border: 1px solid #dbeafe;
    border-radius: 999px;

    font-size: 10px;
    font-weight: 600;

    white-space: nowrap;
}

.preview-field .field-type-badge {
    display: inline-flex;

    margin-right: 5px;
    padding: 2px 6px;

    color: #64748b;

    background: #f1f5f9;

    border: 1px solid #e2e8f0;
    border-radius: 999px;

    font-size: 8px;
    font-weight: 500;

    line-height: 12px;
}


/* =========================================================
   SPECIAL FIELDS
========================================================= */

.preview-field.heading {
    padding: 14px 0 7px;

    border-bottom: none;
}

.preview-field.heading .field-label {
    min-width: auto;

    color: #0f172a;

    font-size: 19px;
    font-weight: 700;
}

.preview-field.heading h1,
.preview-field.heading h2,
.preview-field.heading h3,
.preview-field.heading h4,
.preview-field.heading h5,
.preview-field.heading h6 {
    width: 100%;

    margin: 0;

    color: #0f172a;
}

.preview-field.divider {
    padding: 8px 0;

    border-bottom: none;
}

.preview-field.spacer {
    padding: 8px 0;

    border-bottom: none;
}

.preview-field.spacer .spacer-placeholder {
    width: 100%;

    background: #f8fafc;

    border-radius: 4px;
}

.preview-field.html {
    border-bottom: none;
}

.preview-field.html .html-placeholder {
    width: 100%;

    padding: 10px 12px;

    color: #64748b;

    background: #f8fafc;

    border: 1px dashed #cbd5e1;
    border-radius: 6px;

    font-family:
        Consolas,
        Monaco,
        monospace;

    font-size: 11px;

    line-height: 18px;

    white-space: pre-wrap;

    overflow-wrap: anywhere;

    box-sizing: border-box;
}


/* =========================================================
   TOTAL
========================================================= */

.preview-total {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-top: 16px;
    padding-top: 14px;

    border-top: 1px solid #dbe3ec;

    color: #0f172a;

    font-size: 13px;
    font-weight: 700;
}

.preview-total .total-price {
    color: #1d4ed8;

    font-size: 15px;
}


/* =========================================================
   TOOLS
========================================================= */

.preview-tools {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 12px;

    min-height: 58px;

    padding: 10px 14px;

    background: #ffffff;

    border-top: 1px solid #e8edf3;

    box-sizing: border-box;
}


/* =========================================================
   DEVICE SWITCHER
========================================================= */

.preview-device-switcher {
    display: inline-flex;

    padding: 3px;

    background: #f1f5f9;

    border: 1px solid #e2e8f0;
    border-radius: 8px;

    box-sizing: border-box;
}

.preview-device-btn {
    height: 29px;

    padding: 0 12px;

    border: 0;
    border-radius: 6px;

    background: transparent;

    color: #64748b;

    font-size: 11px;
    font-weight: 600;

    cursor: pointer;

    transition:
        background .15s ease,
        color .15s ease,
        box-shadow .15s ease;
}

.preview-device-btn:hover {
    color: #334155;
}

.preview-device-btn.active {
    background: #ffffff;

    color: #1e293b;

    box-shadow:
        0 1px 3px rgba(15, 23, 42, .08);
}


/* =========================================================
   META
========================================================= */

.preview-meta {
    display: flex;
    align-items: center;

    gap: 9px;

    color: #94a3b8;

    font-size: 10px;
}

.preview-count {
    color: #64748b;

    font-weight: 600;
}

.preview-divider {
    width: 1px;
    height: 13px;

    background: #e2e8f0;
}

.preview-currency {
    color: #94a3b8;
}


/* =========================================================
   GENERATED PREVIEW CONTROLS
========================================================= */

.preview-box input,
.preview-box select,
.preview-box textarea,
.preview-box button {
    font-family: inherit;
}

.preview-box input[type="text"],
.preview-box input[type="email"],
.preview-box input[type="tel"],
.preview-box input[type="number"],
.preview-box input[type="date"],
.preview-box input[type="time"],
.preview-box input[type="color"],
.preview-box select,
.preview-box textarea {
    box-sizing: border-box;
}

.preview-box input[type="checkbox"],
.preview-box input[type="radio"] {
    accent-color: #2563eb;
}


/* =========================================================
   INTERACTION FIX
========================================================= */

.ezlens-preview-box *,
.preview-box * {
    pointer-events: auto !important;
}

.ezlens-preview-field input,
.ezlens-preview-field select,
.ezlens-preview-field textarea,
.ezlens-preview-field button,
.ezlens-preview-field label,
.ezlens-preview-field .ezlens-preview-options label {
    pointer-events: auto !important;

    cursor: default;
}

.ezlens-preview-field input[type="checkbox"],
.ezlens-preview-field input[type="radio"] {
    cursor: pointer;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .preview-tools {
        align-items: flex-start;
        flex-direction: column;
    }

    .preview-meta {
        width: 100%;
    }

}

@media (max-width: 600px) {

    .preview-card {
        min-height: 460px;
    }

    .preview-stage {
        padding: 10px;
    }

    .preview-box {
        padding: 14px;
    }

    .preview-header {
        padding: 0 12px;
    }

    .preview-device-btn {
        padding: 0 9px;
    }

    .preview-field .field-label {
        width: 100%;
        min-width: 100%;
    }

    .preview-field .field-value {
        width: 100%;
        min-width: 100%;
        flex: 1 1 100%;
    }

}


/* =========================================================
   REFRESH ANIMATION
========================================================= */

@keyframes ezlens-preview-spin {

    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }

}

.preview-refresh.is-refreshing .refresh-icon {
    animation:
        ezlens-preview-spin .55s ease;
}

</style>


<script>
jQuery(function($) {

    'use strict';


    /* =====================================================
       INTERNAL STATE
    ===================================================== */

    var previewUpdateTimer = null;
    var refreshTimer = null;


    /* =====================================================
       GET FIELDS DATA
    ===================================================== */

    window.getFieldsData = function() {

        var fields = [];

        $('.ezlens-field-card:not(.ezlens-child-card)').each(function() {

            var $field = $(this);

            var type =
                $field.data('type') ||
                'text';

            var label =
                $field.find('.field-label').val() ||
                'فیلد';

            var placeholder =
                $field.find('.field-placeholder').val() ||
                '';

            var required =
                $field.find('.field-required').is(':checked');

            var price =
                parseFloat(
                    $field.find('.field-price').val()
                ) || 0;

            var width =
                $field.find('.field-width').val() ||
                'full';


            /* =================================================
               OPTIONS
            ================================================= */

            var options = [];

            $field.find(
                '.ezlens-options .ezlens-option'
            ).each(function() {

                var $option = $(this);

                var optionLabel =
                    $option.find('.option-label').val() ||
                    '';

                var optionValue =
                    $option.find('.option-value').val() ||
                    '';

                var optionPrice =
                    parseFloat(
                        $option.find('.option-price').val()
                    ) || 0;

                if (
                    optionLabel ||
                    optionValue
                ) {

                    options.push({

                        label: optionLabel,

                        value: optionValue,

                        price: optionPrice

                    });

                }

            });


            /* =================================================
               SETTINGS
            ================================================= */

            var settings = {};


            $field.find('.field-maxlength').each(function() {

                settings.maxlength =
                    parseInt(
                        $(this).val(),
                        10
                    ) || 0;

            });


            $field.find('.field-rows').each(function() {

                settings.rows =
                    parseInt(
                        $(this).val(),
                        10
                    ) || 3;

            });


            $field.find('.field-heading-level').each(function() {

                settings.level =
                    $(this).val() ||
                    'h3';

            });


            $field.find('.field-html-code').each(function() {

                settings.code =
                    $(this).val() ||
                    '';

            });


            $field.find('.field-spacer-height').each(function() {

                settings.height =
                    parseInt(
                        $(this).val(),
                        10
                    ) || 20;

            });


            $field.find('.field-divider-thickness').each(function() {

                settings.thickness =
                    parseInt(
                        $(this).val(),
                        10
                    ) || 1;

            });


            $field.find('.field-divider-color').each(function() {

                settings.color =
                    $(this).val() ||
                    '#e2e8f0';

            });


            /* =================================================
               FIELD
            ================================================= */

            fields.push({

                type: type,

                label: label,

                placeholder: placeholder,

                required: required,

                price: price,

                width: width,

                options: options,

                settings: settings

            });

        });


        return fields;

    };


    /* =====================================================
       NUMBER FORMAT
    ===================================================== */

    function formatPrice(value) {

        value =
            parseFloat(value) || 0;

        return value.toLocaleString(
            'fa-IR'
        );

    }


    /* =====================================================
       SAMPLE OPTION TEXT
    ===================================================== */

    function formatOption(option) {

        if (!option) {
            return '';
        }

        var text =
            option.label ||
            option.value ||
            '';

        if (
            parseFloat(option.price) > 0
        ) {

            text +=
                ' (+' +
                formatPrice(option.price) +
                ')';

        }

        return text;

    }


    /* =====================================================
       SAMPLE VALUE
    ===================================================== */

    function getSampleValue(field) {

        var type =
            field.type || 'text';

        var placeholder =
            field.placeholder || '';


        switch (type) {

            case 'text':

            case 'email':

            case 'phone':

                return escapeHTML(
                    placeholder ||
                    'متن نمونه'
                );


            case 'number':

                return '۱۲۳';


            case 'textarea':

                return escapeHTML(
                    placeholder ||
                    'متن بلند نمونه...'
                );


            case 'select':

                if (
                    field.options &&
                    field.options.length
                ) {

                    return field.options
                        .map(formatOption)
                        .map(escapeHTML)
                        .join(' | ');

                }

                return 'انتخاب کنید...';


            case 'radio':

                if (
                    field.options &&
                    field.options.length
                ) {

                    return field.options
                        .map(formatOption)
                        .map(escapeHTML)
                        .join('  /  ');

                }

                return 'گزینه‌های رادیویی';


            case 'checkbox':

                if (
                    field.options &&
                    field.options.length
                ) {

                    return field.options
                        .map(formatOption)
                        .map(escapeHTML)
                        .join('  /  ');

                }

                return 'چک‌باکس‌ها';


            case 'upload':

                return 'فایل انتخاب نشده';


            case 'color':

                return (
                    '<span class="preview-color-sample" ' +
                    'style="' +
                    'display:inline-block;' +
                    'width:28px;' +
                    'height:28px;' +
                    'border-radius:5px;' +
                    'background:#2b6cb0;' +
                    'border:1px solid #e2e8f0;' +
                    'vertical-align:middle;' +
                    '"></span>'
                );


            case 'date':

                return '۱۴۰۴/۰۱/۰۱';


            case 'time':

                return '۱۲:۰۰';


            case 'image_select':

                if (
                    field.options &&
                    field.options.length
                ) {

                    return field.options
                        .map(formatOption)
                        .map(escapeHTML)
                        .join(' | ');

                }

                return 'انتخاب تصویر';


            default:

                return escapeHTML(
                    placeholder ||
                    'مقدار نمونه'
                );

        }

    }


    /* =====================================================
       ESCAPE HTML
    ===================================================== */

    function escapeHTML(value) {

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    /* =====================================================
       SAFE COLOR
    ===================================================== */

    function safeColor(value) {

        value =
            String(value || '')
                .trim();

        if (
            /^#[0-9a-fA-F]{3,8}$/.test(value)
        ) {

            return value;

        }

        if (
            /^(rgb|rgba|hsl|hsla)\([\d\s,.%+-]+\)$/.test(value)
        ) {

            return value;

        }

        return '#e2e8f0';

    }


    /* =====================================================
       SAFE NUMBER
    ===================================================== */

    function safeNumber(value, fallback) {

        var number =
            parseInt(
                value,
                10
            );

        if (
            isNaN(number) ||
            number < 0
        ) {

            return fallback;

        }

        return number;

    }


    /* =====================================================
       UPDATE PREVIEW
    ===================================================== */

    window.updatePreview = function() {

        var $preview =
            $('#preview-container');

        if (
            !$preview.length
        ) {

            return;

        }


        var fields =
            typeof window.getFieldsData ===
            'function'
                ? window.getFieldsData()
                : [];


        /* =================================================
           EMPTY
        ================================================= */

        if (
            !fields ||
            fields.length === 0
        ) {

            $preview.html(

                '<div class="preview-placeholder">' +

                    '<div class="preview-placeholder-icon"></div>' +

                    '<strong>پیش‌نمایش خالی است</strong>' +

                    '<span>' +
                        'فیلدها را از سازنده اضافه کنید تا نتیجه اینجا نمایش داده شود.' +
                    '</span>' +

                '</div>'

            );


            $('#preview-field-count')
                .text('۰ فیلد');


            return;

        }


        /* =================================================
           BUILD
        ================================================= */

        var html =
            '<div class="preview-fields">';

        var totalPrice = 0;


        fields.forEach(function(field) {

            var f =
                field || {};


            var width =
                f.width === 'half'
                    ? 'calc(50% - 4px)'
                    : f.width === 'third'
                        ? 'calc(33.33% - 6px)'
                        : f.width === 'quarter'
                            ? 'calc(25% - 6px)'
                            : '100%';


            var fieldClass =
                'preview-field';


            var specialContent =
                '';


            var labelText =
                escapeHTML(
                    f.label ||
                    'فیلد'
                );


            var sampleValue =
                getSampleValue(f);


            var price =
                parseFloat(
                    f.price
                ) || 0;


            if (price > 0) {

                totalPrice += price;

            }


            var requiredStar =
                f.required
                    ? ' <span class="required">*</span>'
                    : '';


            var badge =
                ' <span class="field-type-badge">' +
                    escapeHTML(
                        f.type ||
                        'text'
                    ) +
                '</span>';


            /* =================================================
               HEADING
            ================================================= */

            if (
                f.type === 'heading'
            ) {

                fieldClass +=
                    ' heading';


                var level =
                    f.settings &&
                    f.settings.level
                        ? String(
                            f.settings.level
                        )
                        : 'h3';


                if (
                    !/^h[1-6]$/.test(level)
                ) {

                    level = 'h3';

                }


                specialContent =
                    '<' +
                        level +
                        ' class="field-label">' +
                        labelText +
                    '</' +
                        level +
                    '>';

            }


            /* =================================================
               DIVIDER
            ================================================= */

            else if (
                f.type === 'divider'
            ) {

                fieldClass +=
                    ' divider';


                var dividerColor =
                    safeColor(
                        f.settings &&
                        f.settings.color
                            ? f.settings.color
                            : '#e2e8f0'
                    );


                var dividerThickness =
                    safeNumber(
                        f.settings &&
                        f.settings.thickness,
                        1
                    );


                specialContent =
                    '<div ' +
                        'class="divider-line" ' +
                        'style="' +
                            'width:100%;' +
                            'height:' +
                                dividerThickness +
                            'px;' +
                            'background:' +
                                dividerColor +
                            ';' +
                        '">' +
                    '</div>';

            }


            /* =================================================
               SPACER
            ================================================= */

            else if (
                f.type === 'spacer'
            ) {

                fieldClass +=
                    ' spacer';


                var spacerHeight =
                    safeNumber(
                        f.settings &&
                        f.settings.height,
                        20
                    );


                specialContent =
                    '<div ' +
                        'class="spacer-placeholder" ' +
                        'style="' +
                            'height:' +
                                spacerHeight +
                            'px;' +
                        '">' +
                    '</div>';

            }


            /* =================================================
               HTML
            ================================================= */

            else if (
                f.type === 'html'
            ) {

                fieldClass +=
                    ' html';


                var customCode =
                    f.settings &&
                    f.settings.code
                        ? String(
                            f.settings.code
                        )
                        : '<!-- کد HTML سفارشی -->';


                specialContent =
                    '<div class="html-placeholder">' +
                        escapeHTML(
                            customCode
                        ) +
                    '</div>';

            }


            /* =================================================
               NORMAL FIELD
            ================================================= */

            else {

                specialContent =

                    '<span class="field-label">' +
                        labelText +
                        requiredStar +
                        badge +
                    '</span>' +

                    '<span class="field-value">' +
                        sampleValue +
                    '</span>' +

                    (
                        price > 0
                            ? '<span class="field-price">' +
                                '+' +
                                formatPrice(price) +
                                ' تومان' +
                              '</span>'
                            : ''
                    );

            }


            html +=

                '<div ' +
                    'class="' +
                        fieldClass +
                    '" ' +
                    'style="width:' +
                        width +
                    ';">' +

                    specialContent +

                '</div>';

        });


        html +=
            '</div>';


        /* =================================================
           TOTAL
        ================================================= */

        if (
            totalPrice > 0
        ) {

            html +=

                '<div class="preview-total">' +

                    '<span>قیمت نهایی</span>' +

                    '<span class="total-price">' +
                        formatPrice(
                            totalPrice
                        ) +
                        ' تومان' +
                    '</span>' +

                '</div>';

        }


        $preview.html(
            html
        );


        /* =================================================
           COUNT
        ================================================= */

        $('#preview-field-count')
            .text(
                formatPrice(
                    fields.length
                ) +
                ' فیلد'
            );


        /* =================================================
           BUILDER SYNC
        ================================================= */

        if (
            typeof window.syncBuilderToCode ===
            'function'
        ) {

            try {

                window.syncBuilderToCode();

            } catch (error) {

                if (
                    window.console &&
                    typeof console.warn ===
                    'function'
                ) {

                    console.warn(
                        'EzLens Preview: Builder sync failed.',
                        error
                    );

                }

            }

        }

    };


    /* =====================================================
       DEVICE SWITCHER
    ===================================================== */

    $(document).on(
        'click',
        '.preview-device-btn',
        function() {

            var $button =
                $(this);

            var device =
                $button.data('device');


            if (
                !device
            ) {

                return;

            }


            $('.preview-device-btn')
                .removeClass('active')
                .attr(
                    'aria-pressed',
                    'false'
                );


            $button
                .addClass('active')
                .attr(
                    'aria-pressed',
                    'true'
                );


            var $box =
                $('#preview-container');


            if (
                !$box.length
            ) {

                return;

            }


            $box.removeClass(
                'preview-desktop ' +
                'preview-tablet ' +
                'preview-mobile'
            );


            $box.addClass(
                'preview-' +
                device
            );

        }
    );


    /* =====================================================
       REFRESH
    ===================================================== */

    $(document).on(
        'click',
        '#refresh-preview',
        function(event) {

            event.preventDefault();


            var $button =
                $(this);


            if (
                refreshTimer
            ) {

                clearTimeout(
                    refreshTimer
                );

            }


            $button
                .removeClass(
                    'is-refreshing'
                );


            /*
             * Force reflow so the animation
             * can restart on repeated clicks.
             */

            void $button[0].offsetWidth;


            $button
                .addClass(
                    'is-refreshing'
                );


            if (
                typeof window.updatePreview ===
                'function'
            ) {

                window.updatePreview();

            }


            refreshTimer =
                setTimeout(
                    function() {

                        $button
                            .removeClass(
                                'is-refreshing'
                            );

                    },
                    550
                );

        }
    );


    /* =====================================================
       AUTO UPDATE
    ===================================================== */

    $(document).on(
        'input change keyup',
        '.ezlens-field-card input, ' +
        '.ezlens-field-card select, ' +
        '.ezlens-field-card textarea',
        function() {

            if (
                previewUpdateTimer
            ) {

                clearTimeout(
                    previewUpdateTimer
                );

            }


            previewUpdateTimer =
                setTimeout(
                    function() {

                        if (
                            typeof window.updatePreview ===
                            'function'
                        ) {

                            window.updatePreview();

                        }

                    },
                    200
                );

        }
    );


    /* =====================================================
       INITIAL LOAD
    ===================================================== */

    setTimeout(
        function() {

            if (
                typeof window.updatePreview ===
                'function'
            ) {

                window.updatePreview();

            }

        },
        300
    );


});
</script>