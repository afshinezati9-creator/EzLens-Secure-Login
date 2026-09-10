(function ($) {
  'use strict';

  function toFa(n) {
    return String(n).replace(/\d/g, function (d) {
      return '۰۱۲۳۴۵۶۷۸۹'[d];
    });
  }

  function formatMoney(amount) {
    var decimals = (window.ezlens_po && ezlens_po.price_decimals) || 0;
    var formatted = Number(amount).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    var out = toFa(formatted);
    if (window.ezlens_po && ezlens_po.currency_suffix) {
      out += ' ' + ezlens_po.currency_suffix;
    }
    return out;
  }

  function calcExtra($scope) {
    var extra = 0;
    $scope.find('input[type="checkbox"][data-price], input[type="radio"][data-price]').each(function () {
      var $el = $(this);
      if ($el.is(':checked')) {
        extra += parseFloat($el.attr('data-price')) || 0;
      }
    });
    $scope.find('select').each(function () {
      var $opt = $(this).find('option:selected');
      extra += parseFloat($opt.attr('data-price')) || 0;
      extra += parseFloat($(this).attr('data-price')) || 0;
    });
    $scope.find('input[type="text"][data-price], input[type="number"][data-price], input[type="email"][data-price], textarea[data-price]').each(function () {
      var $el = $(this);
      if ($el.val()) {
        extra += parseFloat($el.attr('data-price')) || 0;
      }
    });
    return extra;
  }

  function updatePriceHints() {
    var base = (window.ezlens_po && parseFloat(ezlens_po.price)) || 0;
    var $roots = $('.ezlens-po-root');
    if (!$roots.length) return;
    var extra = calcExtra($('.ezlens-po-root, #ezlens-po-modal'));
    $roots.each(function () {
      var $hint = $(this).find('.ezlens-po-price-hint').first();
      if (!$hint.length) {
        $hint = $('<div class="ezlens-po-price-hint"></div>').appendTo($(this));
      }
      if (extra > 0) {
        $hint
          .text(
            (ezlens_po.i18n && ezlens_po.i18n.extra ? ezlens_po.i18n.extra : 'هزینه اضافی') +
              ': ' +
              formatMoney(extra) +
              ' — ' +
              (ezlens_po.i18n && ezlens_po.i18n.total ? ezlens_po.i18n.total : 'جمع') +
              ': ' +
              formatMoney(base + extra)
          )
          .removeAttr('hidden')
          .addClass('is-visible')
          .show();
      } else {
        $hint.attr('hidden', true).removeClass('is-visible').hide();
      }
    });
  }

  function syncModalToSource($slot) {
    var $src = $slot.find('.ezlens-po-modal-fields');
    var $body = $('#ezlens-po-modal .ezlens-po-modal-body');
    $src.find('input, select, textarea').each(function () {
      var $s = $(this);
      var name = $s.attr('name');
      if (!name) return;
      var $m = $body.find('[name="' + name.replace(/"/g, '\\"') + '"]');
      if (!$m.length) return;
      if ($s.is(':checkbox') || $s.is(':radio')) {
        // handled per value
      } else {
        $s.val($m.val());
      }
    });
    $body.find('input[type="checkbox"], input[type="radio"]').each(function () {
      var $m = $(this);
      var name = $m.attr('name');
      var val = $m.val();
      $src
        .find('[name="' + name.replace(/"/g, '\\"') + '"]')
        .filter(function () {
          return $(this).val() === val;
        })
        .prop('checked', $m.is(':checked'));
    });
  }

  function openModal($slot) {
    var $src = $slot.find('.ezlens-po-modal-fields');
    var title =
      $slot.find('.ezlens-po-modal-trigger').data('slot-title') ||
      ($slot.find('.ezlens-po-slot-title').text()) ||
      (ezlens_po.i18n && ezlens_po.i18n.options) ||
      'گزینه‌ها';
    var $modal = $('#ezlens-po-modal');
    var $body = $modal.find('.ezlens-po-modal-body');
    $modal.data('source-slot', $slot);
    $modal.find('.ezlens-po-modal-title').text(title);
    // Clone fields into modal for editing (keep originals for form submit)
    $body.empty();
    var $clone = $src.children().clone(true, true);
    // Ensure unique ids in clone
    $clone.find('[id]').each(function () {
      var id = $(this).attr('id');
      $(this).attr('id', id + '_modal');
    });
    $clone.find('label[for]').each(function () {
      var f = $(this).attr('for');
      $(this).attr('for', f + '_modal');
    });
    // Copy current values into clone
    $src.find('input, select, textarea').each(function (i) {
      var $s = $(this);
      var $c = $clone.find('input, select, textarea').eq(i);
      if (!$c.length) return;
      if ($s.is(':checkbox') || $s.is(':radio')) {
        $c.prop('checked', $s.is(':checked'));
      } else {
        $c.val($s.val());
      }
    });
    $body.append($clone);
    $modal.prop('hidden', false);
    $('body').css('overflow', 'hidden');
  }

  function closeModal() {
    var $modal = $('#ezlens-po-modal');
    var $slot = $modal.data('source-slot');
    if ($slot && $slot.length) {
      syncModalToSource($slot);
    }
    $modal.prop('hidden', true);
    $modal.removeData('source-slot');
    $('body').css('overflow', '');
    updatePriceHints();
  }

  $(function () {
    // Accordion
    $(document).on('click', '.ezlens-po-acc-toggle', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var expanded = $btn.attr('aria-expanded') === 'true';
      var target = $btn.data('target');
      var $body = target ? $('#' + target) : $btn.next('.ezlens-po-acc-body');
      $btn.attr('aria-expanded', expanded ? 'false' : 'true');
      if (expanded) {
        $body.attr('hidden', true);
      } else {
        $body.removeAttr('hidden');
      }
    });

    // Modal open
    $(document).on('click', '.ezlens-po-modal-trigger', function (e) {
      e.preventDefault();
      openModal($(this).closest('.ezlens-po-slot'));
    });

    $(document).on('click', '#ezlens-po-modal [data-close]', function (e) {
      e.preventDefault();
      closeModal();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && !$('#ezlens-po-modal').prop('hidden')) {
        closeModal();
      }
    });

    // Price live update
    $(document).on(
      'change input',
      '.ezlens-po-root input, .ezlens-po-root select, .ezlens-po-root textarea, #ezlens-po-modal input, #ezlens-po-modal select, #ezlens-po-modal textarea',
      function () {
        updatePriceHints();
      }
    );

    updatePriceHints();
  });


  /* ===== Phase 3: Upload ===== */
  var UPLOAD_ACCEPT = ['image/jpeg','image/png','image/gif','image/webp','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
  var UPLOAD_EXT = /\.(jpe?g|png|gif|webp|pdf|docx?)$/i;

  function toFaPct(n) {
    return toFa(Math.round(n)) + '٪';
  }

  function setUploadState($box, state) {
    var $dz = $box.find('.ezlens-upload-dropzone');
    $dz.removeClass('is-dragover is-uploading is-success is-error');
    $box.find('.ezlens-upload-idle, .ezlens-upload-progressing, .ezlens-upload-done, .ezlens-upload-error').attr('hidden', true);
    if (state === 'idle') {
      $box.find('.ezlens-upload-idle').removeAttr('hidden');
    } else if (state === 'progress') {
      $dz.addClass('is-uploading');
      $box.find('.ezlens-upload-progressing').removeAttr('hidden');
    } else if (state === 'done') {
      $dz.addClass('is-success');
      $box.find('.ezlens-upload-done').removeAttr('hidden');
    } else if (state === 'error') {
      $dz.addClass('is-error');
      $box.find('.ezlens-upload-error').removeAttr('hidden');
    }
  }

  function validateFile(file, maxSize) {
    if (!file) return 'فایلی انتخاب نشده است.';
    if (file.size <= 0) return 'فایل خالی است.';
    if (file.size > maxSize) return 'حجم فایل بیشتر از ۵ مگابایت است.';
    var typeOk = UPLOAD_ACCEPT.indexOf(file.type) !== -1;
    var extOk = UPLOAD_EXT.test(file.name || '');
    if (!typeOk && !extOk) return 'نوع فایل مجاز نیست.';
    return null;
  }

  function showDone($box, data) {
    var url = data.url || '';
    var name = data.filename || 'فایل';
    var isImg = /\.(jpe?g|png|gif|webp)$/i.test(name) || (data.mime || '').indexOf('image/') === 0;
    var $thumb = $box.find('.ezlens-upload-thumb').empty();
    if (isImg && url) {
      $thumb.append($('<img>').attr({ src: url, alt: name }));
    } else {
      var ext = (name.split('.').pop() || 'FILE').toUpperCase();
      $thumb.text(ext);
    }
    $box.find('.ezlens-upload-filename').text(name);
    $box.find('.ezlens-upload-value').val(url);
    setUploadState($box, 'done');
    updatePriceHints();
  }

  function showError($box, msg) {
    $box.find('.ezlens-upload-error-msg').text(msg || 'خطا در آپلود');
    setUploadState($box, 'error');
  }

  function uploadFile($box, file) {
    var maxSize = parseInt($box.attr('data-max-size'), 10) || 5242880;
    var err = validateFile(file, maxSize);
    if (err) {
      showError($box, err);
      return;
    }

    setUploadState($box, 'progress');
    $box.find('.ezlens-upload-progress-bar').css('width', '0%');
    $box.find('.ezlens-upload-pct').text(toFaPct(0));

    var fd = new FormData();
    fd.append('action', 'ezlens_upload_file');
    fd.append('nonce', (window.ezlens_po && ezlens_po.nonce) || '');
    fd.append('file', file);
    if (window.ezlens_po && ezlens_po.product_id) {
      fd.append('product_id', ezlens_po.product_id);
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', (window.ezlens_po && ezlens_po.ajax_url) || (window.ajaxurl || '/wp-admin/admin-ajax.php'));
    xhr.responseType = 'json';

    xhr.upload.onprogress = function (e) {
      if (!e.lengthComputable) return;
      var pct = (e.loaded / e.total) * 100;
      $box.find('.ezlens-upload-progress-bar').css('width', pct + '%');
      $box.find('.ezlens-upload-pct').text(toFaPct(pct));
    };

    xhr.onload = function () {
      var res = xhr.response;
      if (typeof res === 'string') {
        try { res = JSON.parse(res); } catch (ex) { res = null; }
      }
      if (xhr.status >= 200 && xhr.status < 300 && res && res.success && res.data) {
        $box.find('.ezlens-upload-progress-bar').css('width', '100%');
        $box.find('.ezlens-upload-pct').text(toFaPct(100));
        showDone($box, res.data);
      } else {
        var msg = (res && res.data && (res.data.message || res.data)) || 'خطا در آپلود فایل.';
        if (typeof msg !== 'string') msg = 'خطا در آپلود فایل.';
        showError($box, msg);
      }
    };

    xhr.onerror = function () {
      showError($box, 'خطا در ارتباط با سرور.');
    };

    xhr.send(fd);
  }

  function resetUpload($box) {
    $box.find('.ezlens-upload-value').val('');
    $box.find('.ezlens-upload-input').val('');
    $box.find('.ezlens-upload-thumb').empty();
    $box.find('.ezlens-upload-filename').text('');
    setUploadState($box, 'idle');
    updatePriceHints();
  }

  $(function () {
    $(document).on('click', '.ezlens-upload-dropzone', function (e) {
      if ($(e.target).closest('.ezlens-upload-remove, .ezlens-upload-retry').length) return;
      var $box = $(this).closest('.ezlens-upload-box');
      if ($box.find('.ezlens-upload-dropzone').hasClass('is-uploading')) return;
      // If already done, allow replace via click
      $box.find('.ezlens-upload-input').trigger('click');
    });

    $(document).on('keydown', '.ezlens-upload-dropzone', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        $(this).trigger('click');
      }
    });

    $(document).on('change', '.ezlens-upload-input', function () {
      var $input = $(this);
      var $box = $input.closest('.ezlens-upload-box');
      var file = this.files && this.files[0];
      if (file) uploadFile($box, file);
    });

    $(document).on('dragenter dragover', '.ezlens-upload-dropzone', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).addClass('is-dragover');
    });

    $(document).on('dragleave dragend', '.ezlens-upload-dropzone', function (e) {
      e.preventDefault();
      $(this).removeClass('is-dragover');
    });

    $(document).on('drop', '.ezlens-upload-dropzone', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('is-dragover');
      var $box = $(this).closest('.ezlens-upload-box');
      var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
      if (files && files[0]) uploadFile($box, files[0]);
    });

    $(document).on('click', '.ezlens-upload-remove', function (e) {
      e.preventDefault();
      e.stopPropagation();
      resetUpload($(this).closest('.ezlens-upload-box'));
    });

    $(document).on('click', '.ezlens-upload-retry', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var $box = $(this).closest('.ezlens-upload-box');
      resetUpload($box);
      $box.find('.ezlens-upload-input').trigger('click');
    });
  });



  /* ===== Phase 4: field UI helpers ===== */
  $(function () {
    // Color picker ↔ hex text
    $(document).on('input change', '.ezlens-field-color', function () {
      var v = $(this).val();
      $(this).closest('.ezlens-color-shell').find('.ezlens-color-hex').val(v);
    });
    $(document).on('change blur', '.ezlens-color-hex', function () {
      var v = ($(this).val() || '').trim();
      if (/^#?[0-9a-fA-F]{6}$/.test(v)) {
        if (v.charAt(0) !== '#') v = '#' + v;
        $(this).val(v.toLowerCase());
        $(this).closest('.ezlens-color-shell').find('.ezlens-field-color').val(v.toLowerCase()).trigger('change');
      }
    });
  });

})(jQuery);
