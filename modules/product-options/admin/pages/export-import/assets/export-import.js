jQuery(document).ready(function($) {
        'use strict';

        // ===== Toast =====
        function showToast(message, type) {
            var $toast = $('#ezlens-toast');
            if (!$toast.length) {
                $toast = $('<div id="ezlens-toast" class="ezlens-toast"></div>');
                $('body').append($toast);
            }
            $toast.removeClass('success error info').addClass(type).text(message).fadeIn(200);
            clearTimeout($toast.data('timeout'));
            $toast.data('timeout', setTimeout(function() { $toast.fadeOut(300); }, 3000));
        }

        // ===== 1. Export =====
        $('#export-btn').on('click', function() {
            var $btn = $(this);
            var status = $('#export-status-filter').val();
            var $result = $('#export-result');

            $btn.text('⏳ در حال آماده‌سازی...').prop('disabled', true);
            $result.hide();

            $.post(ajaxurl, {
                action: 'ezlens_export_templates',
                nonce: ''+(window.ezlensPoExport && ezlensPoExport.nonce ? ezlensPoExport.nonce : '')+'',
                status: status
            }, function(response) {
                if (response.success) {
                    var data = response.data.data;
                    var filename = response.data.filename || 'ezlens-templates-backup.json';
                    var json = JSON.stringify(data, null, 2);
                    var blob = new Blob([json], {type: 'application/json'});
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);

                    $result.html('<span class="success">✅ خروجی با موفقیت ایجاد شد. (' + data.templates.length + ' پالت)</span>').show();
                    showToast('✅ خروجی با موفقیت ایجاد شد.', 'success');

                    // به‌روزرسانی تاریخ آخرین بکاپ
                    $('#info-backup').text(new Date().toLocaleDateString('fa-IR'));
                } else {
                    showToast('❌ ' + (response.data.message || 'خطا در ایجاد خروجی'), 'error');
                    $result.html('<span style="color:#dc2626;">❌ ' + (response.data.message || 'خطا') + '</span>').show();
                }
            }).fail(function() {
                showToast('❌ خطا در ارتباط با سرور', 'error');
            }).always(function() {
                $btn.text('📥 دانلود خروجی JSON').prop('disabled', false);
            });
        });

        // ===== 2. Import - Drag & Drop =====
        var dropZone = document.getElementById('drop-zone');

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                var input = document.getElementById('import-file-input');
                input.files = e.dataTransfer.files;
                var event = new Event('change');
                input.dispatchEvent(event);
            }
        });

        // ===== 3. Import - File selection =====
        $('#import-file-input').on('change', function() {
            if (this.files && this.files[0]) {
                var file = this.files[0];
                var validTypes = ['application/json', 'text/plain'];
                if (!validTypes.includes(file.type) && !file.name.endsWith('.json')) {
                    showToast('❌ لطفاً یک فایل JSON معتبر انتخاب کنید.', 'error');
                    this.value = '';
                    $('#import-file-name').text('');
                    return;
                }
                $('#import-file-name').text('📎 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)');
            } else {
                $('#import-file-name').text('');
            }
        });

        // ===== 4. Import - Submit =====
        $('#import-form').on('submit', function(e) {
            e.preventDefault();

            var fileInput = document.getElementById('import-file-input');
            if (!fileInput.files || !fileInput.files[0]) {
                showToast('❌ لطفاً یک فایل JSON انتخاب کنید.', 'error');
                return;
            }

            var file = fileInput.files[0];
            var reader = new FileReader();
            var $status = $('#import-status');
            var $btn = $('#import-submit-btn');

            reader.onload = function(e) {
                var jsonData = e.target.result;

                $btn.text('⏳ در حال وارد کردن...').prop('disabled', true);
                $status.removeClass('success error info').addClass('info').text('⏳ در حال پردازش...').show();

                $.post(ajaxurl, {
                    action: 'ezlens_import_templates',
                    nonce: ''+(window.ezlensPoExport && ezlensPoExport.nonce ? ezlensPoExport.nonce : '')+'',
                    json_data: jsonData,
                    overwrite: $('input[name="overwrite"]').prop('checked') ? '1' : '0'
                }, function(response) {
                    if (response.success) {
                        var data = response.data;
                        var msg = '✅ ' + data.message;
                        if (data.errors && data.errors.length > 0) {
                            msg += '\n⚠️ خطاها: ' + data.errors.join(', ');
                        }
                        $status.removeClass('info error').addClass('success').text(msg);
                        showToast('✅ وارد کردن با موفقیت انجام شد.', 'success');

                        // به‌روزرسانی اطلاعات
                        setTimeout(function() { location.reload(); }, 2000);
                    } else {
                        $status.removeClass('info success').addClass('error').text('❌ ' + (response.data.message || 'خطا در وارد کردن'));
                        showToast('❌ ' + (response.data.message || 'خطا در وارد کردن'), 'error');
                    }
                }).fail(function() {
                    $status.removeClass('info success').addClass('error').text('❌ خطا در ارتباط با سرور');
                    showToast('❌ خطا در ارتباط با سرور', 'error');
                }).always(function() {
                    $btn.text('⬆️ شروع وارد کردن').prop('disabled', false);
                });
            };

            reader.onerror = function() {
                showToast('❌ خطا در خواندن فایل', 'error');
            };

            reader.readAsText(file);
        });

        // ===== 5. Refresh Info =====
        $('#refresh-info').on('click', function() {
            var $btn = $(this);
            $btn.text('⏳ در حال بروزرسانی...').prop('disabled', true);

            $.post(ajaxurl, {
                action: 'ezlens_get_templates_count',
                nonce: ''+(window.ezlensPoExport && ezlensPoExport.nonce ? ezlensPoExport.nonce : '')+''
            }, function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#info-total').text(data.total);
                    $('#info-active').text(data.active);
                    $('#info-inactive').text(data.inactive);
                    $('#info-products').text(data.products);
                    showToast('✅ اطلاعات بروزرسانی شد.', 'success');
                }
            }).fail(function() {
                showToast('❌ خطا در بروزرسانی اطلاعات', 'error');
            }).always(function() {
                $btn.text('🔄 بروزرسانی اطلاعات').prop('disabled', false);
            });
        });

        console.log('✅ EzLens Export/Import page loaded.');
    });
