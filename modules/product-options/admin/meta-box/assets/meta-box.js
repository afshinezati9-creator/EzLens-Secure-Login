(function ($) {
  'use strict';

  function escHtml(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function reindex() {
    var $rows = $('#ezlens-slots-list .ezlens-slot-row');
    $rows.each(function (i) {
      var $row = $(this);
      $row.attr('data-index', i);
      $row.find('.ezlens-slot-badge').text(
        (i + 1).toString().replace(/\d/g, function (d) {
          return '۰۱۲۳۴۵۶۷۸۹'[d];
        })
      );
      $row.find('select, input').each(function () {
        var name = $(this).attr('name');
        if (!name || name.indexOf('ezlens_slots') === -1) return;
        name = name.replace(/ezlens_slots\[\d+\]/, 'ezlens_slots[' + i + ']');
        name = name.replace(/ezlens_slots\[__INDEX__\]/, 'ezlens_slots[' + i + ']');
        $(this).attr('name', name);
      });
      $row.find('.ezlens-slot-move-up').prop('disabled', i === 0);
      $row.find('.ezlens-slot-move-down').prop('disabled', i === $rows.length - 1);
    });
    $('#ezlens-slots-empty').toggle($rows.length === 0);
  }

  function loadPreview($row) {
    var tid = parseInt($row.find('.ezlens-slot-template').val(), 10) || 0;
    var $preview = $row.find('.ezlens-slot-preview');
    var $chips = $row.find('.ezlens-preview-fields');
    if (!tid) {
      $preview.removeClass('is-open');
      $chips.html('<span class="ezlens-preview-empty">پالتی انتخاب نشده است.</span>');
      $row.addClass('is-empty-template');
      return;
    }
    $row.removeClass('is-empty-template');
    $chips.html('<span class="ezlens-preview-empty">در حال بارگذاری…</span>');
    $preview.addClass('is-open');

    var nonce = (window.ezlensSlotsMeta && ezlensSlotsMeta.previewNonce) || '';
    $.post(ajaxurl, {
      action: 'ezlens_get_template_preview',
      nonce: nonce,
      template_id: tid
    })
      .done(function (res) {
        if (!res || !res.success) {
          $chips.html('<span class="ezlens-preview-empty">خطا در دریافت فیلدها.</span>');
          return;
        }
        var fields = (res.data && res.data.fields) || [];
        if (!fields.length) {
          $chips.html('<span class="ezlens-preview-empty">این پالت فیلدی ندارد.</span>');
          return;
        }
        var html = '';
        fields.forEach(function (f) {
          html += '<span class="ezlens-preview-chip">';
          html += '<span>' + escHtml(f.label || 'فیلد') + '</span>';
          if (f.required) html += '<span class="req">*</span>';
          html += '<span class="type">' + escHtml(f.type || '') + '</span>';
          html += '</span>';
        });
        $chips.html(html);
        var title = (res.data && res.data.title) || '';
        if (title) {
          $row.find('.ezlens-slot-label').attr('placeholder', title);
        }
      })
      .fail(function () {
        $chips.html('<span class="ezlens-preview-empty">خطا در ارتباط با سرور.</span>');
      });
  }

  function buildRow(index) {
    var tpl = $('#ezlens-slot-row-template').html();
    if (!tpl) return $();
    var $row = $(tpl);
    // template may wrap extra whitespace text nodes
    if (!$row.hasClass('ezlens-slot-row')) {
      $row = $row.filter('.ezlens-slot-row');
      if (!$row.length) {
        $row = $(tpl).find('.ezlens-slot-row').first();
      }
    }
    $row.attr('data-index', index);
    return $row;
  }

  $(function () {
    if (!$('#ezlens-slots-list').length) return;

    reindex();
    $('#ezlens-slots-list .ezlens-slot-row').each(function () {
      loadPreview($(this));
    });

    $('#ezlens-add-slot').on('click', function (e) {
      e.preventDefault();
      var i = $('#ezlens-slots-list .ezlens-slot-row').length;
      var $row = buildRow(i);
      if (!$row || !$row.length) return;
      $('#ezlens-slots-list').append($row);
      reindex();
    });

    $('#ezlens-slots-list').on('click', '.ezlens-slot-remove', function (e) {
      e.preventDefault();
      $(this).closest('.ezlens-slot-row').remove();
      reindex();
    });

    $('#ezlens-slots-list').on('click', '.ezlens-slot-move-up', function (e) {
      e.preventDefault();
      var $row = $(this).closest('.ezlens-slot-row');
      var $prev = $row.prev('.ezlens-slot-row');
      if ($prev.length) {
        $row.insertBefore($prev);
        reindex();
      }
    });

    $('#ezlens-slots-list').on('click', '.ezlens-slot-move-down', function (e) {
      e.preventDefault();
      var $row = $(this).closest('.ezlens-slot-row');
      var $next = $row.next('.ezlens-slot-row');
      if ($next.length) {
        $row.insertAfter($next);
        reindex();
      }
    });

    $('#ezlens-slots-list').on('change', '.ezlens-slot-template', function () {
      loadPreview($(this).closest('.ezlens-slot-row'));
    });
  });
})(jQuery);
