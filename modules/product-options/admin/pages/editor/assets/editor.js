jQuery(document).ready(function($) {

    'use strict';


    /* ============================================================
       تا کردن / باز کردن بخش‌ها
       ============================================================ */

    $('.toggle-section').on('click', function() {

        var target = $(this).data('target');
        var $target = $('#' + target);

        $target.toggleClass('collapsed');

        $(this).text(
            $target.hasClass('collapsed')
                ? '▸'
                : '▾'
        );
    });


    /* ============================================================
       تغییر وضعیت
       ============================================================ */

    $('.toggle-switch input[type="checkbox"]').on('change', function() {

        var label = $(this)
            .closest('.status-toggle')
            .find('.status-label');

        label.text(
            $(this).prop('checked')
                ? 'فعال'
                : 'غیرفعال'
        );
    });


    /* ============================================================
       انتخاب تم CSS
       ============================================================ */

    $('#css-theme-select').on('change', function() {

        var theme = $(this).val();

        var themes = {

            'default':
                '/* استایل پیش‌فرض */',

            'dark':
                '.ezlens-product-options-wrapper { background: #1a202c; color: #e2e8f0; } .ezlens-options-title { border-color: #2d3748; color: #f1f5f9; }',

            'glass':
                '.ezlens-product-options-wrapper { background: rgba(255,255,255,0.6); backdrop-filter: blur(10px); }',

            'minimal':
                '.ezlens-product-options-wrapper { border: none; box-shadow: none; padding: 0; }'
        };

        $('#custom-css-editor').val(
            themes[theme] || ''
        );

        if (typeof updatePreview === 'function') {
            updatePreview();
        }
    });


    /* ============================================================
       کپی پالت
       ============================================================ */

    $('#btn-duplicate').on('click', function() {

        if (!confirm('آیا از این پالت کپی تهیه شود؟')) {
            return;
        }

        var templateId = $('input[name="template_id"]').val();

        $.post(
            ajaxurl,
            {
                action: 'ezlens_duplicate_template',

                nonce: (window.ezlensPoEditor && ezlensPoEditor.nonce) || '',

                template_id: templateId
            },
            function(response) {

                if (response.success) {

                    window.location.href =
                        '?page=ezlens-product-options&action=edit&id=' +
                        response.data.id;

                } else {

                    alert(
                        'خطا: ' +
                        (
                            response.data.message ||
                            'خطای نامشخص'
                        )
                    );
                }
            }
        );
    });


    /* ============================================================
       شمارشگر خطوط
       ============================================================ */

    var $textarea = $('#code-editor-input');
    var $lineNumbers = $('#code-line-numbers');


    function updateLineNumbers() {

        if (!$textarea.length || !$lineNumbers.length) {
            return;
        }

        var lines = $textarea.val().split('\n');

        var count = lines.length;

        var numbers = [];

        for (var i = 1; i <= count; i++) {
            numbers.push(i);
        }

        $lineNumbers.text(
            numbers.join('\n')
        );

        syncScroll();
    }


    function syncScroll() {

        if (!$textarea.length || !$lineNumbers.length) {
            return;
        }

        $lineNumbers.scrollTop(
            $textarea.scrollTop()
        );
    }


    $textarea.on('input', function() {

        updateLineNumbers();

        if (
            typeof window.updatePreview === 'function'
        ) {

            clearTimeout(
                window._codeEditorTimeout
            );

            window._codeEditorTimeout =
                setTimeout(function() {

                    window.updatePreview();

                }, 400);
        }
    });


    $textarea.on('scroll', syncScroll);

    $(window).on('resize', syncScroll);


    /* ============================================================
       فرمت کد
       ============================================================ */

    $('#code-format').on('click', function() {

        try {

            var val = $textarea.val();

            var lines = val
                .split('\n')
                .map(function(line) {
                    return line.trim();
                });

            var formatted = [];

            var lastEmpty = false;

            lines.forEach(function(line) {

                if (line === '') {

                    if (!lastEmpty) {
                        formatted.push('');
                        lastEmpty = true;
                    }

                } else {

                    formatted.push(line);

                    lastEmpty = false;
                }
            });

            $textarea.val(
                formatted.join('\n')
            );

            updateLineNumbers();

            if (
                typeof window.updatePreview === 'function'
            ) {
                window.updatePreview();
            }

        } catch (e) {

            alert('خطا در فرمت کد');
        }
    });


    /* ============================================================
       داده‌های سازنده
       ============================================================ */

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

            var required =
                $field.find('.field-required').is(':checked');

            var price =
                parseFloat(
                    $field.find('.field-price').val()
                ) || 0;

            var width =
                $field.find('.field-width').val() ||
                'full';

            fields.push({
                label: label,
                type: type,
                required: required,
                price: price,
                width: width
            });
        });

        return fields;
    };


    /* ============================================================
       پیش‌نمایش
       ============================================================ */

    window.updatePreview = function() {

        var preview =
            $('#preview-container');

        var fields =
            window.getFieldsData
                ? window.getFieldsData()
                : [];

        if (
            !fields ||
            fields.length === 0
        ) {

            preview.html(
                '<div class="preview-placeholder">هنوز فیلدی اضافه نشده است.</div>'
            );

            $('#preview-field-count').text(
                '۰ فیلد'
            );

            return;
        }


        var html =
            '<div style="display:flex;flex-wrap:wrap;gap:6px;">';


        fields.forEach(function(f) {

            var width =
                f.width === 'half'
                    ? 'calc(50% - 4px)'
                    : f.width === 'third'
                        ? 'calc(33.33% - 6px)'
                        : f.width === 'quarter'
                            ? 'calc(25% - 6px)'
                            : '100%';


            html +=
                '<div class="preview-field" style="width:' +
                width +
                ';">';


            html +=
                '<span class="label">' +
                (f.label || 'فیلد') +
                (
                    f.required
                        ? ' <span style="color:#dc2626;">*</span>'
                        : ''
                ) +
                '</span>';


            if (f.price > 0) {

                html +=
                    '<span class="value" style="color:#2b6cb0;font-weight:600;">+' +
                    f.price +
                    ' تومان</span>';
            }


            html += '</div>';
        });


        html += '</div>';

        preview.html(html);


        $('#preview-field-count').text(
            fields.length + ' فیلد'
        );


        if (
            typeof window.syncBuilderToCode === 'function'
        ) {
            window.syncBuilderToCode();
        }
    };


    /* ============================================================
       دستگاه پیش‌نمایش
       ============================================================ */

    $('#preview-device').on('change', function() {

        var device = $(this).val();

        var $box =
            $('#preview-container');

        $box.removeClass(
            'preview-desktop preview-tablet preview-mobile'
        );

        $box.addClass(
            'preview-' + device
        );

        if (
            typeof updatePreview === 'function'
        ) {
            updatePreview();
        }
    });


    /* ============================================================
       رفرش پیش‌نمایش
       ============================================================ */

    $('#refresh-preview').on('click', function() {

        if (
            typeof updatePreview === 'function'
        ) {
            updatePreview();
        }

        $(this).text('✓');

        var $button = $(this);

        setTimeout(function() {
            $button.text('↻');
        }, 1000);
    });


    /* ============================================================
       تغییرات سازنده
       ============================================================ */

    $(document).on(
        'change keyup',
        '.ezlens-field-card input, .ezlens-field-card select',
        function() {

            if (
                typeof updatePreview === 'function'
            ) {
                updatePreview();
            }
        }
    );


    /* ============================================================
       ذخیره AJAX
       ============================================================ */

    function saveTemplate() {

        var $form =
            $('#template-editor-form');

        var $status =
            $('#ezlens-save-status, #save-status');


        $status.html(
            '<span style="color:#2b6cb0;">در حال ذخیره...</span>'
        );


        var formData =
            new FormData($form[0]);


        var codeEditor =
            $('#code-editor-input').val();


        formData.append(
            'code_editor',
            codeEditor
        );


        var fieldsJson =
            $('#ezlens-builder-fields-json').val();


        if (fieldsJson) {

            formData.append(
                'fields',
                fieldsJson
            );
        }


        $.ajax({

            url: '''',

            type: 'POST',

            data: formData,

            processData: false,

            contentType: false,


            success: function(response) {

                if (response.success) {

                    $status.html(
                        '<span style="color:#16a34a;">' +
                        response.data.message +
                        '</span>'
                    );


                    if (
                        response.data.id &&
                        response.data.id > 0
                    ) {

                        setTimeout(function() {

                            window.location.href =
                                '?page=ezlens-product-options&action=edit&id=' +
                                response.data.id;

                        }, 1000);

                    } else {

                        setTimeout(function() {
                            location.reload();
                        }, 800);
                    }

                } else {

                    $status.html(
                        '<span style="color:#dc2626;">' +
                        response.data.message +
                        '</span>'
                    );
                }
            },


            error: function(
                xhr,
                status,
                error
            ) {

                $status.html(
                    '<span style="color:#dc2626;">' +
                    'خطا در ارتباط با سرور: ' +
                    error +
                    '</span>'
                );

                console.log(
                    'AJAX Error:',
                    xhr.responseText
                );
            }
        });
    }


    /* ============================================================
       دکمه‌های ذخیره
       ============================================================ */

    $(
        '#ezlens-save-template-btn, #ezlens-save-template-btn2'
    ).on('click', function(e) {

        e.preventDefault();

        saveTemplate();
    });


    /* ============================================================
       جلوگیری از Submit با Enter
       ============================================================ */

    $('#template-editor-form').on(
        'keydown',
        function(e) {

            if (
                e.key === 'Enter' &&
                $(e.target).is(
                    'input, textarea'
                )
            ) {

                e.preventDefault();
            }
        }
    );


    /* ============================================================
       بارگذاری اولیه
       ============================================================ */

    setTimeout(function() {

        updateLineNumbers();


        if (
            typeof window.syncBuilderToCode === 'function'
        ) {
            window.syncBuilderToCode();
        }


        if (
            typeof window.updatePreview === 'function'
        ) {
            window.updatePreview();
        }

    }, 300);

});
