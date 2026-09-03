jQuery(document).ready(function($) {
    'use strict';

    var $wrapper = $('.ezlens-product-options-wrapper');
    if (!$wrapper.length) return;

    var productId = ezlens_po.product_id;
    var basePrice = parseFloat(ezlens_po.price) || 0;
    var currency = ezlens_po.currency || 'تومان';

    // ===== ۱. محاسبه قیمت نهایی =====
    function calculateTotalPrice() {
        var total = basePrice;
        var fields = {};

        // جمع‌آوری مقادیر فیلدها
        $wrapper.find('.ezlens-field-input, .ezlens-field-select, .ezlens-field-color, .ezlens-field-date, .ezlens-radio-label input, .ezlens-checkbox-label input, .ezlens-image-select-label input').each(function() {
            var $input = $(this);
            var key = $input.data('field-key');
            if (!key) return;

            var price = parseFloat($input.data('price')) || 0;
            var value = $input.val();

            if ($input.is(':checkbox')) {
                if ($input.is(':checked')) {
                    total += price;
                    if (!fields[key]) fields[key] = [];
                    fields[key].push($input.val());
                }
                return;
            }

            if ($input.is(':radio')) {
                if ($input.is(':checked')) {
                    total += price;
                    fields[key] = $input.val();
                }
                return;
            }

            if ($input.is('select')) {
                var $selected = $input.find('option:selected');
                var optPrice = parseFloat($selected.data('price')) || 0;
                total += optPrice;
                fields[key] = $input.val();
                return;
            }

            // فیلدهای معمولی
            if (value && value.trim() !== '') {
                var fieldPrice = parseFloat($input.data('price')) || 0;
                total += fieldPrice;
                fields[key] = value;
            }
        });

        // نمایش قیمت
        var $priceDisplay = $('#ezlens-total-price');
        if ($priceDisplay.length) {
            $priceDisplay.text(numberFormat(total) + ' ' + currency);
            $wrapper.find('.ezlens-total-price').show();
        }

        return { total: total, fields: fields };
    }

    // ===== ۲. تغییرات فیلدها =====
    $wrapper.on('change', '.ezlens-field-input, .ezlens-field-select, .ezlens-field-color, .ezlens-field-date, .ezlens-radio-label input, .ezlens-checkbox-label input, .ezlens-image-select-label input', function() {
        calculateTotalPrice();
    });

    $wrapper.on('input', '.ezlens-field-input, .ezlens-field-select, .ezlens-field-color, .ezlens-field-date', function() {
        calculateTotalPrice();
    });

    // ===== ۳. آپلود فایل با نوار پیشرفت =====
    $wrapper.on('change', '.ezlens-field-upload', function() {
        var $input = $(this);
        var file = this.files[0];
        if (!file) return;

        var $wrapperUpload = $input.closest('.ezlens-upload-wrapper');
        var $progress = $wrapperUpload.find('.ezlens-upload-progress');
        var $preview = $wrapperUpload.find('.ezlens-upload-preview');

        var formData = new FormData();
        formData.append('action', 'ezlens_upload_file');
        formData.append('nonce', ezlens_po.nonce);
        formData.append('file', file);
        formData.append('product_id', productId);

        $progress.show();
        $preview.text('در حال آپلود...');

        $.ajax({
            url: ezlens_po.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percent = Math.round((e.loaded / e.total) * 100);
                        $progress.find('.progress-bar').css('width', percent + '%');
                        if (percent === 100) {
                            $preview.text('آپلود کامل شد!');
                        }
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    $preview.html('✅ <a href="' + response.data.url + '" target="_blank">' + response.data.filename + '</a>');
                    $input.data('uploaded-url', response.data.url);
                    calculateTotalPrice();
                } else {
                    $preview.text('❌ ' + response.data.message);
                }
                setTimeout(function() {
                    $progress.fadeOut();
                }, 1000);
            },
            error: function() {
                $preview.text('❌ خطا در آپلود فایل');
                $progress.fadeOut();
            }
        });
    });

    // ===== ۴. اعتبارسنجی قبل از افزودن به سبد خرید =====
    $('form.cart').on('submit', function(e) {
        var hasError = false;

        $wrapper.find('.ezlens-field-wrapper').each(function() {
            var $wrapperField = $(this);
            var $input = $wrapperField.find('input, select, textarea');
            var required = $input.prop('required');

            if (required) {
                var value = $input.val();
                if (!value || (Array.isArray(value) && value.length === 0)) {
                    $input.addClass('ezlens-field-error');
                    if (!$wrapperField.find('.ezlens-error-message').length) {
                        $wrapperField.append('<div class="ezlens-error-message">این فیلد اجباری است.</div>');
                    }
                    hasError = true;
                } else {
                    $input.removeClass('ezlens-error-message');
                    $wrapperField.find('.ezlens-error-message').remove();
                }
            }
        });

        if (hasError) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: $wrapper.offset().top - 100 }, 300);
            return false;
        }
    });

    // ===== ۵. حذف خطا هنگام تایپ =====
    $wrapper.on('input change', '.ezlens-field-input, .ezlens-field-select, .ezlens-radio-label input, .ezlens-checkbox-label input, .ezlens-image-select-label input', function() {
        $(this).removeClass('ezlens-field-error');
        $(this).closest('.ezlens-field-wrapper').find('.ezlens-error-message').remove();
    });

    // ===== ۶. محاسبه اولیه =====
    calculateTotalPrice();

    // ===== ۷. تابع کمکی فرمت اعداد =====
    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    console.log('✅ EzLens Product Options loaded.');
});