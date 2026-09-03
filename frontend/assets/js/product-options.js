jQuery(document).ready(function($) {
    'use strict';

    var $wrapper = $('.ezlens-product-options-wrapper');
    if (!$wrapper.length) return;

    var productId = ezlens_po.product_id;
    var basePrice = parseFloat(ezlens_po.price) || 0;
    var currency = ezlens_po.currency || 'تومان';

    function getFieldValue(key) {
        var $inputs = $wrapper.find('[data-field-key="' + key.replace(/"/g, '\\"') + '"]');
        if (!$inputs.length) return '';
        if ($inputs.first().is(':checkbox')) {
            var values = [];
            $inputs.filter(':checked').each(function() { values.push($(this).val()); });
            return values;
        }
        if ($inputs.first().is(':radio')) {
            var $checked = $inputs.filter(':checked');
            return $checked.length ? $checked.val() : '';
        }
        return $inputs.first().val() || '';
    }

    function compareValues(actual, expected, operator) {
        operator = operator || 'equals';
        if (Array.isArray(actual)) {
            if (operator === 'contains' || operator === 'equals') {
                return actual.map(String).indexOf(String(expected)) !== -1;
            }
            if (operator === 'not_contains') {
                return actual.map(String).indexOf(String(expected)) === -1;
            }
            actual = actual.join(',');
        }
        actual = String(actual == null ? '' : actual);
        expected = String(expected == null ? '' : expected);
        switch (operator) {
            case 'not_equals': return actual !== expected;
            case 'contains': return actual.indexOf(expected) !== -1;
            case 'not_contains': return actual.indexOf(expected) === -1;
            case 'greater_than': return parseFloat(actual) > parseFloat(expected);
            case 'less_than': return parseFloat(actual) < parseFloat(expected);
            case 'greater_or_equal': return parseFloat(actual) >= parseFloat(expected);
            case 'less_or_equal': return parseFloat(actual) <= parseFloat(expected);
            case 'empty': return actual === '';
            case 'not_empty': return actual !== '';
            case 'equals':
            default: return actual === expected;
        }
    }

    function conditionsMatch(conditions) {
        if (!conditions || !conditions.rules || !conditions.rules.length) return true;
        var results = $.map(conditions.rules, function(rule) {
            if (!rule || !rule.field) return null;
            return compareValues(getFieldValue(rule.field), rule.value, rule.operator);
        });
        if (!results.length) return true;
        return conditions.logic === 'any' ? results.some(Boolean) : results.every(Boolean);
    }

    function applyConditionalLogic() {
        $wrapper.find('.ezlens-field-wrapper[data-conditions]').each(function() {
            var $field = $(this);
            var raw = $field.attr('data-conditions');
            var conditions;
            try { conditions = JSON.parse(raw); } catch (e) { conditions = null; }
            var visible = conditionsMatch(conditions);
            $field.toggle(visible).attr('aria-hidden', visible ? 'false' : 'true');
            $field.find('input, select, textarea').prop('disabled', !visible);
        });
    }

    function calculateTotalPrice() {
        var total = basePrice;
        $wrapper.find('.ezlens-field-wrapper:visible').find('.ezlens-field-input, .ezlens-field-select, .ezlens-field-color, .ezlens-field-date, .ezlens-radio-label input, .ezlens-checkbox-label input, .ezlens-image-select-label input').each(function() {
            var $input = $(this);
            var price = parseFloat($input.data('price')) || 0;
            if ($input.is(':disabled')) return;
            if ($input.is(':checkbox')) {
                if ($input.is(':checked')) total += price;
            } else if ($input.is(':radio')) {
                if ($input.is(':checked')) total += price;
            } else if ($input.is('select')) {
                if ($input.val()) total += parseFloat($input.find('option:selected').data('price')) || 0;
                if ($input.val() && price) total += price;
            } else if ($input.val() && String($input.val()).trim() !== '') {
                total += price;
            }
        });
        var $priceDisplay = $('#ezlens-total-price');
        if ($priceDisplay.length) $priceDisplay.text(numberFormat(total) + ' ' + currency);
        return total;
    }

    function refresh() {
        applyConditionalLogic();
        calculateTotalPrice();
    }

    $wrapper.on('change input', '.ezlens-field-input, .ezlens-field-select, .ezlens-field-color, .ezlens-field-date, .ezlens-radio-label input, .ezlens-checkbox-label input, .ezlens-image-select-label input', refresh);

    $('form.cart').on('submit', function(e) {
        refresh();
        var hasError = false;
        $wrapper.find('.ezlens-field-wrapper:visible').each(function() {
            var $field = $(this);
            var $inputs = $field.find('input, select, textarea').filter(':enabled');
            var required = $inputs.first().prop('required');
            if (!required) return;
            var valid = false;
            $inputs.each(function() {
                var $input = $(this);
                if ($input.is(':checkbox,:radio')) { if ($input.is(':checked')) valid = true; }
                else if ($input.val()) valid = true;
            });
            if (!valid) {
                $field.addClass('ezlens-has-error');
                if (!$field.find('.ezlens-error-message').length) $field.append('<div class="ezlens-error-message">این فیلد اجباری است.</div>');
                hasError = true;
            }
        });
        if (hasError) {
            e.preventDefault();
            $('html, body').animate({scrollTop: $wrapper.offset().top - 100}, 300);
            return false;
        }
    });

    $wrapper.on('input change', '.ezlens-field-input, .ezlens-field-select, .ezlens-radio-label input, .ezlens-checkbox-label input, .ezlens-image-select-label input', function() {
        $(this).closest('.ezlens-field-wrapper').removeClass('ezlens-has-error').find('.ezlens-error-message').remove();
    });

    $wrapper.on('change', '.ezlens-field-upload', function() {
        var $input = $(this), file = this.files[0];
        if (!file) return;
        var $upload = $input.closest('.ezlens-upload-wrapper'), $progress = $upload.find('.ezlens-upload-progress'), $preview = $upload.find('.ezlens-upload-preview');
        var formData = new FormData();
        formData.append('action', 'ezlens_upload_file'); formData.append('nonce', ezlens_po.nonce); formData.append('file', file); formData.append('product_id', productId);
        $progress.show(); $preview.text('در حال آپلود...');
        $.ajax({url: ezlens_po.ajax_url, type: 'POST', data: formData, processData: false, contentType: false,
            xhr: function() { var xhr = new window.XMLHttpRequest(); xhr.upload.addEventListener('progress', function(e) { if (e.lengthComputable) $progress.find('.progress-bar').css('width', Math.round(e.loaded / e.total * 100) + '%'); }); return xhr; },
            success: function(response) { if (response.success) { $preview.html('آپلود کامل شد: <a href="' + response.data.url + '" target="_blank" rel="noopener">' + response.data.filename + '</a>'); $input.data('uploaded-url', response.data.url); } else $preview.text('خطا: ' + (response.data.message || 'آپلود ناموفق')); setTimeout(function() {$progress.fadeOut();}, 1000); },
            error: function() { $preview.text('خطا در آپلود فایل'); $progress.fadeOut(); }
        });
    });

    refresh();

    function numberFormat(num) { return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
});
