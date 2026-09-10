/**
 * Product Options — template list interactions (Phase 5, pure JS).
 * Expects window.ezlensPoList = { ajaxUrl, nonces, presets, i18n }
 */
(function ($) {
  'use strict';

  function cfg() {
    return window.ezlensPoList || {};
  }

  function showToast(msg, type) {
    if (window.Swal) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type === 'error' ? 'error' : 'success',
        title: msg,
        showConfirmButton: false,
        timer: 2800,
      });
      return;
    }
    // fallback
    // eslint-disable-next-line no-alert
    alert(msg);
  }

  function ajax(action, data) {
    var c = cfg();
    data = data || {};
    data.action = action;
    return $.post(c.ajaxUrl || window.ajaxurl, data);
  }

  $(function () {
    if (!$('.ezlens-template-list').length) {
      return;
    }

    var c = cfg();

    // Select all
    $(document).on('change', '#select-all', function () {
      $('.ezlens-template-list .template-checkbox').prop('checked', this.checked);
    });

    // Export
    $('#export-templates').on('click', function () {
      var nonce = c.nonces && c.nonces.exportImport ? c.nonces.exportImport : '';
      ajax('ezlens_export_templates', { nonce: nonce }).done(function (res) {
        if (!res || !res.success) {
          showToast((res && res.data && res.data.message) || 'خطا در خروجی', 'error');
          return;
        }
        var blob = new Blob([JSON.stringify(res.data, null, 2)], { type: 'application/json' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'ezlens-templates-export.json';
        a.click();
        URL.revokeObjectURL(url);
        showToast('خروجی آماده شد', 'success');
      });
    });

    // Toggle import form
    $('#show-import-form').on('click', function () {
      $('#import-form-container').slideToggle(150);
    });

    // Import submit
    $('#import-submit-btn').on('click', function (e) {
      e.preventDefault();
      var $form = $('#import-form-container').find('form');
      if (!$form.length) {
        $form = $(this).closest('form');
      }
      var fileInput = $form.find('input[type="file"]')[0];
      if (!fileInput || !fileInput.files || !fileInput.files[0]) {
        showToast('فایل JSON را انتخاب کنید', 'error');
        return;
      }
      var fd = new FormData($form[0]);
      $.ajax({
        url: c.ajaxUrl || window.ajaxurl,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
      }).done(function (res) {
        if (res && res.success) {
          showToast((res.data && res.data.message) || 'وارد شد', 'success');
          setTimeout(function () {
            location.reload();
          }, 800);
        } else {
          showToast((res && res.data && res.data.message) || 'خطا در وارد کردن', 'error');
        }
      });
    });

    // Bulk apply
    $('#bulk-apply').on('click', function () {
      var action = $('#bulk-action-select').val();
      var ids = [];
      $('.ezlens-template-list .template-checkbox:checked').each(function () {
        ids.push($(this).val());
      });
      if (!action || action === '-1') {
        showToast('عملیات را انتخاب کنید', 'error');
        return;
      }
      if (!ids.length) {
        showToast('هیچ موردی انتخاب نشده', 'error');
        return;
      }

      if (action === 'delete') {
        if (!window.Swal) {
          if (!window.confirm('حذف موارد انتخاب‌شده؟')) return;
          ids.forEach(function (id) {
            ajax('ezlens_delete_template', {
              nonce: c.nonces && c.nonces.delete ? c.nonces.delete : '',
              template_id: id,
            });
          });
          location.reload();
          return;
        }
        Swal.fire({
          title: 'حذف؟',
          text: ids.length + ' مورد حذف می‌شود',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'حذف',
          cancelButtonText: 'انصراف',
        }).then(function (r) {
          if (!r.isConfirmed) return;
          var pending = ids.length;
          ids.forEach(function (id) {
            ajax('ezlens_delete_template', {
              nonce: c.nonces && c.nonces.delete ? c.nonces.delete : '',
              template_id: id,
            }).always(function () {
              pending -= 1;
              if (pending <= 0) location.reload();
            });
          });
        });
        return;
      }

      if (action === 'duplicate') {
        var left = ids.length;
        ids.forEach(function (id) {
          ajax('ezlens_duplicate_template', {
            nonce: c.nonces && c.nonces.duplicate ? c.nonces.duplicate : '',
            template_id: id,
          }).always(function () {
            left -= 1;
            if (left <= 0) location.reload();
          });
        });
      }
    });

    // Single delete
    $(document).on('click', '.action-delete', function (e) {
      e.preventDefault();
      var id = $(this).data('id');
      var run = function () {
        ajax('ezlens_delete_template', {
          nonce: c.nonces && c.nonces.delete ? c.nonces.delete : '',
          template_id: id,
        }).done(function (res) {
          if (res && res.success) {
            showToast('حذف شد', 'success');
            location.reload();
          } else {
            showToast((res && res.data && res.data.message) || 'خطا', 'error');
          }
        });
      };
      if (window.Swal) {
        Swal.fire({
          title: 'حذف پالت؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'حذف',
          cancelButtonText: 'انصراف',
        }).then(function (r) {
          if (r.isConfirmed) run();
        });
      } else if (window.confirm('حذف پالت؟')) {
        run();
      }
    });

    // Duplicate
    $(document).on('click', '.action-duplicate', function (e) {
      e.preventDefault();
      var id = $(this).data('id');
      ajax('ezlens_duplicate_template', {
        nonce: c.nonces && c.nonces.duplicate ? c.nonces.duplicate : '',
        template_id: id,
      }).done(function (res) {
        if (res && res.success) {
          showToast('کپی ایجاد شد', 'success');
          location.reload();
        } else {
          showToast((res && res.data && res.data.message) || 'خطا', 'error');
        }
      });
    });

    // Preset create
    $('#create-preset-btn').on('click', function () {
      var presets = c.presets || [];
      if (!presets.length) {
        showToast('پالت آماده‌ای تعریف نشده', 'error');
        return;
      }
      var options = presets
        .map(function (p) {
          return '<option value="' + p.id + '">' + (p.title || p.id) + '</option>';
        })
        .join('');

      if (!window.Swal) {
        showToast('SweetAlert در دسترس نیست', 'error');
        return;
      }

      Swal.fire({
        title: 'ایجاد از پالت آماده',
        html:
          '<div style="text-align:right;">' +
          '<label style="display:block;margin-bottom:6px;font-weight:600;">انتخاب پالت</label>' +
          '<select id="preset-select" style="width:100%;padding:8px;">' +
          options +
          '</select>' +
          '<label style="display:block;margin:12px 0 6px;font-weight:600;">عنوان سفارشی</label>' +
          '<input type="text" id="preset-title" style="width:100%;padding:8px;" placeholder="عنوان جدید">' +
          '</div>',
        showCancelButton: true,
        confirmButtonText: 'ایجاد',
        cancelButtonText: 'انصراف',
        preConfirm: function () {
          return {
            preset_id: document.getElementById('preset-select').value,
            title: document.getElementById('preset-title').value,
          };
        },
      }).then(function (r) {
        if (!r.isConfirmed || !r.value) return;
        ajax('ezlens_create_preset', {
          nonce: c.nonces && c.nonces.preset ? c.nonces.preset : '',
          preset_id: r.value.preset_id,
          title: r.value.title,
        }).done(function (res) {
          if (res && res.success) {
            showToast((res.data && res.data.message) || 'ایجاد شد', 'success');
            if (res.data && res.data.id) {
              window.location.href = '?page=ezlens-product-options&action=edit&id=' + res.data.id;
            } else {
              location.reload();
            }
          } else {
            showToast((res && res.data && res.data.message) || 'خطا', 'error');
          }
        });
      });
    });

    $('#refresh-list').on('click', function () {
      location.reload();
    });
  });
})(jQuery);
