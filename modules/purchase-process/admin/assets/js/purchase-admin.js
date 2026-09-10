(function($) {
    'use strict';

    var ajaxurl = ezpurchase.ajax_url;
    var nonce = ezpurchase.nonce;
    var i18n = ezpurchase.i18n;

    // نمایش پیام (Toast)
    function showToast(message, type) {
        var toast = $('#ezpurchase-toast');
        if (toast.length === 0) {
            toast = $('<div id="ezpurchase-toast" class="ezpurchase-toast"><span class="close">✕</span><span class="msg"></span></div>');
            $('body').append(toast);
            toast.find('.close').on('click', function() {
                toast.removeClass('show');
            });
        }
        toast.removeClass('success error').addClass(type);
        toast.find('.msg').text(message);
        toast.addClass('show');

        clearTimeout(toast.data('timer'));
        var timer = setTimeout(function() {
            toast.removeClass('show');
        }, 4000);
        toast.data('timer', timer);
    }

    // تغییر وضعیت (فعال/غیرفعال)
    $(document).on('click', '.ezpurchase-toggle-status', function(e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var currentStatus = parseInt(btn.data('status'), 10);
        var newStatus = currentStatus ? 0 : 1;

        btn.prop('disabled', true);

        $.post(ajaxurl, {
            action: 'ezpurchase_toggle_status',
            id: id,
            status: newStatus,
            nonce: nonce
        }, function(response) {
            btn.prop('disabled', false);
            if (response.success) {
                btn.data('status', newStatus);
                var statusText = newStatus ? 'فعال' : 'غیرفعال';
                var icon = newStatus ? '<img src="' + ezpurchase.icons.check + '" width="18" height="18" alt="فعال" style="display:inline-block;vertical-align:middle;">' :
                                        '<img src="' + ezpurchase.icons.x + '" width="18" height="18" alt="غیرفعال" style="display:inline-block;vertical-align:middle;">';
                btn.html(icon + ' ' + statusText);
                btn.removeClass('button-primary button-secondary').addClass(newStatus ? 'button-primary' : 'button-secondary');
                showToast(response.data.message, 'success');
            } else {
                showToast(response.data.message || i18n.error, 'error');
            }
        }).fail(function() {
            btn.prop('disabled', false);
            showToast(i18n.error, 'error');
        });
    });

    // حذف فایل
    $(document).on('click', '.ezpurchase-delete', function(e) {
        e.preventDefault();
        if (!confirm(i18n.confirm_delete)) {
            return;
        }

        var btn = $(this);
        var id = btn.data('id');
        var row = btn.closest('tr');

        btn.prop('disabled', true);

        $.post(ajaxurl, {
            action: 'ezpurchase_delete_file',
            id: id,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                row.fadeOut(300, function() {
                    $(this).remove();
                    if ($('#ezpurchase-list-body tr').length === 0) {
                        location.reload();
                    }
                });
                showToast(response.data.message, 'success');
            } else {
                showToast(response.data.message || i18n.error, 'error');
            }
            btn.prop('disabled', false);
        }).fail(function() {
            btn.prop('disabled', false);
            showToast(i18n.error, 'error');
        });
    });

})(jQuery);