<?php
if (!defined('ABSPATH')) exit;
?>

<div class="card ezlens-css-editor-card">

    <div class="ezlens-card-header">
        <div>
            <h3>CSS اختصاصی پالت</h3>
            <p>استایل اختصاصی خود را برای پالت محصول بنویسید.</p>
        </div>
    </div>

    <div class="ezlens-css-editor">

        <div class="ezlens-css-line-numbers" id="custom-css-line-numbers"></div>

        <textarea
            id="custom-css-editor"
            name="custom_css"
            class="code-editor-input"
            rows="8"
            spellcheck="false"
            autocomplete="off"
            autocorrect="off"
            autocapitalize="off"
        ><?php echo esc_textarea($custom_css); ?></textarea>

    </div>

    <div class="ezlens-css-editor-footer">

        <span class="ezlens-css-status" id="custom-css-status">
            آماده ویرایش
        </span>

        <button
            type="button"
            class="button button-primary"
            id="apply-custom-css"
        >
            اعمال CSS
        </button>

    </div>

</div>


<style>

/* =========================================================
   CSS EDITOR CARD
========================================================= */

.ezlens-css-editor-card {
    padding: 0 !important;
    overflow: hidden;
}

.ezlens-css-editor-card .ezlens-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.ezlens-css-editor-card .ezlens-card-header h3 {
    margin: 0;
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
}

.ezlens-css-editor-card .ezlens-card-header p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 12px;
}


/* =========================================================
   EDITOR
========================================================= */

.ezlens-css-editor {
    display: flex;
    width: 100%;
    min-height: 260px;
    max-height: 600px;
    overflow: hidden;

    background: #0f172a;
}


/* =========================================================
   LINE NUMBERS
========================================================= */

.ezlens-css-line-numbers {
    flex: 0 0 48px;
    width: 48px;

    padding: 14px 10px 14px 0;

    background: #111827;
    border-right: 1px solid #1e293b;

    color: #64748b;

    font-family:
        Consolas,
        Monaco,
        "Courier New",
        monospace;

    font-size: 12px;
    line-height: 20px;

    text-align: right;

    user-select: none;
    pointer-events: none;

    overflow: hidden;
}

.ezlens-css-line-number {
    height: 20px;
    line-height: 20px;
}


/* =========================================================
   TEXTAREA
========================================================= */

#custom-css-editor.code-editor-input {
    flex: 1;

    width: calc(100% - 48px);
    min-width: 0;
    min-height: 260px;
    max-height: 600px;

    margin: 0;
    padding: 14px 18px;

    border: 0 !important;
    border-radius: 0 !important;
    outline: none !important;

    resize: vertical;

    background: #0f172a !important;
    color: #e2e8f0 !important;

    font-family:
        Consolas,
        Monaco,
        "Courier New",
        monospace;

    font-size: 13px;
    line-height: 20px;

    direction: ltr;
    text-align: left;

    tab-size: 4;

    white-space: pre;

    caret-color: #60a5fa;

    box-shadow: none !important;
}

#custom-css-editor.code-editor-input::selection {
    background: rgba(59, 130, 246, .25);
}


/* =========================================================
   SCROLLBAR
========================================================= */

#custom-css-editor.code-editor-input::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

#custom-css-editor.code-editor-input::-webkit-scrollbar-track {
    background: #0f172a;
}

#custom-css-editor.code-editor-input::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 8px;
}

#custom-css-editor.code-editor-input::-webkit-scrollbar-thumb:hover {
    background: #475569;
}


/* =========================================================
   FOOTER
========================================================= */

.ezlens-css-editor-footer {
    display: flex;
    align-items: center;
    gap: 10px;

    min-height: 52px;

    padding: 8px 12px;

    background: #ffffff;
    border-top: 1px solid #e2e8f0;
}

.ezlens-css-status {
    margin-right: auto;

    color: #94a3b8;
    font-size: 11px;
}

#apply-custom-css {
    min-height: 34px;
    padding: 0 16px;

    border-radius: 7px;

    font-size: 12px;
    font-weight: 600;

    transition:
        background .15s ease,
        box-shadow .15s ease,
        transform .15s ease;
}

#apply-custom-css:hover {
    transform: translateY(-1px);
}


/* =========================================================
   FOCUS
========================================================= */

.ezlens-css-editor:focus-within {
    box-shadow: inset 0 0 0 1px rgba(59, 130, 246, .18);
}

.ezlens-css-editor:focus-within .ezlens-css-line-numbers {
    color: #718096;
    background: #111c31;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .ezlens-css-line-numbers {
        flex-basis: 40px;
        width: 40px;
        padding-right: 7px;
    }

    #custom-css-editor.code-editor-input {
        width: calc(100% - 40px);
        padding-left: 12px;
        padding-right: 12px;
        font-size: 12px;
    }

    .ezlens-css-editor-footer {
        flex-wrap: wrap;
    }

    .ezlens-css-status {
        width: 100%;
        margin-right: 0;
    }

}

</style>


<script>
jQuery(document).ready(function($) {

    'use strict';

    var $editor = $('#custom-css-editor');
    var $lineNumbers = $('#custom-css-line-numbers');
    var $status = $('#custom-css-status');


    /* =====================================================
       LINE NUMBERS
    ===================================================== */

    function updateCssLineNumbers() {

        if (!$editor.length || !$lineNumbers.length) {
            return;
        }

        var value = $editor.val() || '';

        var lineCount = value.split('\n').length;

        var html = '';

        for (var i = 1; i <= lineCount; i++) {

            html +=
                '<div class="ezlens-css-line-number">' +
                i +
                '</div>';

        }

        $lineNumbers.html(html);

        $lineNumbers.scrollTop(
            $editor.scrollTop()
        );
    }


    /* =====================================================
       INITIALIZE
    ===================================================== */

    updateCssLineNumbers();


    /* =====================================================
       INPUT
    ===================================================== */

    $editor.on('input', function() {

        updateCssLineNumbers();

        $status.text('تغییرات اعمال نشده');

    });


    /* =====================================================
       SCROLL
    ===================================================== */

    $editor.on('scroll', function() {

        $lineNumbers.scrollTop(
            $editor.scrollTop()
        );

    });


    /* =====================================================
       TAB
    ===================================================== */

    $editor.on('keydown', function(e) {

        if (e.key !== 'Tab') {
            return;
        }

        e.preventDefault();

        var textarea = this;

        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;

        var value = textarea.value;

        textarea.value =
            value.substring(0, start) +
            '    ' +
            value.substring(end);

        textarea.selectionStart = start + 4;
        textarea.selectionEnd = start + 4;

        updateCssLineNumbers();

    });


    /* =====================================================
       APPLY CSS TO MAIN PALETTE
    ===================================================== */

    $('#apply-custom-css').on('click', function() {

        var css = $editor.val() || '';

        /*
         * اگر Preview اصلی وجود داشته باشد،
         * CSS اختصاصی را روی آن اعمال می‌کنیم.
         */

        if (typeof window.updatePreview === 'function') {

            window.updatePreview();

        }

        /*
         * اطلاع‌رسانی به سیستم Preview
         */

        $(document).trigger(
            'ezlens:custom-css-updated',
            {
                css: css
            }
        );

        $status.text('CSS اعمال شد');

    });


    /* =====================================================
       REFRESH
    ===================================================== */

    window.EzLensCustomCssEditor = {

        refresh: function() {
            updateCssLineNumbers();
        }

    };


});
</script>