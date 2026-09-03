/**
 * EzLens Secure Login - Admin Scripts (نسخه نهایی اصلاح‌شده با پشتیبانی از چک‌باکس‌ها و خطایابی بهتر)
 * @version 2.3.6
 */
(function($) {
    'use strict';

    // ============================================================
    // ۱. شماره‌گذاری خطوط ویرایشگر
    // ============================================================
    function updateLineNumbers(textarea) {
        var container = textarea.closest('.editor-body');
        var lineNumbers = container.find('.line-numbers');
        if (!lineNumbers.length) return;

        var lines = textarea.val().split('\n').length;
        var html = '';
        for (var i = 1; i <= lines; i++) {
            html += i + '\n';
        }
        lineNumbers.text(html);

        textarea.off('scroll').on('scroll', function() {
            lineNumbers.scrollTop = this.scrollTop;
        });
    }

    $('.ezlens-editor .code-editor').each(function() {
        var $this = $(this);
        updateLineNumbers($this);
        $this.on('input', function() {
            updateLineNumbers($this);
            var box = $this.closest('.editor-box');
            var lines = this.value.split('\n').length;
            box.find('.count').text(lines + ' خط');
        });
    });

    // ============================================================
    // ۲. مدیریت تب‌های تنظیمات با AJAX - پایدار و بدون درخواست موازی
    // ============================================================
    var settingsTabs = document.querySelector('.ezlens-settings-tabs');
    var settingsContent = document.querySelector('.ezlens-settings-content');
    // jQuery wrapper is required for .find(); keep native node for innerHTML.
    var $settingsContent = $(settingsContent);
    var settingsTabXHR = null;
    var settingsSaveXHR = null;

    if (settingsTabs && settingsContent) {
        var tabs = settingsTabs.querySelectorAll('.settings-tab');

        function normalizeAjaxError(response, fallback) {
            if (response && response.data) {
                if (typeof response.data === 'string') return response.data;
                if (response.data.message) return response.data.message;
            }
            return fallback || 'خطای نامشخص';
        }

        function loadSettingsTab(tabId) {
            if (settingsSaveXHR) {
                return; // never replace the form while a save is in progress
            }
            if (settingsTabXHR && settingsTabXHR.readyState !== 4) {
                settingsTabXHR.abort();
            }

            tabs.forEach(function(t) { t.classList.remove('active'); });
            var activeTab = settingsTabs.querySelector('.settings-tab[data-tab="' + tabId + '"]');
            if (activeTab) activeTab.classList.add('active');

            settingsContent.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;">⏳ در حال بارگذاری...</div>';
            settingsTabXHR = $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_load_settings_tab',
                nonce: ezlens_auth_ajax.nonce,
                tab: tabId
            }, function(response) {
                if (response.success) {
                    settingsContent.innerHTML = response.data.html;
                } else {
                    settingsContent.innerHTML = '<div style="text-align:center;padding:40px;color:#dc2626;">❌ ' + normalizeAjaxError(response, 'خطا در بارگذاری') + '</div>';
                }
            }).fail(function(xhr, status) {
                if (status === 'abort') return;
                settingsContent.innerHTML = '<div style="text-align:center;padding:40px;color:#dc2626;">❌ خطا در ارتباط با سرور</div>';
            });
        }

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                if (settingsSaveXHR) return;
                var tabId = this.getAttribute('data-tab');
                loadSettingsTab(tabId);
                if (window.history && window.history.pushState) {
                    var url = new URL(window.location.href);
                    url.searchParams.set('tab', tabId);
                    window.history.pushState({}, '', url);
                }
            });
        });

        $('#saveAllSettings').off('click.ezlensSettings').on('click.ezlensSettings', function(e) {
            e.preventDefault();
            if (settingsSaveXHR) return;

            var $btn = $(this);
            var $status = $('#settings-save-status');
            if (!$status.length) {
                $status = $('<span id="settings-save-status" style="margin-right:12px;font-size:13px;display:inline-block;"></span>');
                $btn.after($status);
            }

            var activeTabId = $('.settings-tab.active').data('tab') || 'general';
            var formData = {
                action: 'ezlens_save_settings_ajax',
                nonce: ezlens_auth_ajax.nonce,
                settings_tab: activeTabId
            };
            var $form = $settingsContent.find('form').first();
            if ($form.length) {
                $form.find('input, select, textarea').each(function() {
                    var $el = $(this);
                    var name = $el.attr('name');
                    if (!name) return;
                    if ($el.is(':checkbox')) {
                        formData[name] = $el.is(':checked') ? '1' : '0';
                    } else {
                        formData[name] = $el.val();
                    }
                });
            } else {
                $settingsContent.find('input, select, textarea').each(function() {
                    var $el = $(this), name = $el.attr('name');
                    if (!name) return;
                    formData[name] = $el.is(':checkbox') ? ($el.is(':checked') ? '1' : '0') : $el.val();
                });
            }

            $btn.data('original-text', $btn.text()).text('⏳ در حال ذخیره...').prop('disabled', true);
            $status.text('در حال ذخیره...').css('color', '#d97706');

            settingsSaveXHR = $.post(ezlens_auth_ajax.ajax_url, formData, function(response) {
                if (response.success) {
                    var successText = (response.data && response.data.message) ? response.data.message : 'تنظیمات با موفقیت ذخیره شد.';
                    $status.text('✅ ' + successText).css('color', '#16a34a');
                    $btn.text('✅ ذخیره شد').css({'background':'#16a34a','border-color':'#16a34a','color':'#fff'});
                    showNotice('✅ ' + successText, 'success');
                    setTimeout(function() {
                        $btn.text('💾 ذخیره تنظیمات این بخش').css({'background':'','border-color':'','color':''});
                    }, 2200);
                } else {
                    var errorText = normalizeAjaxError(response, 'خطا در ذخیره تنظیمات');
                    $status.text('❌ ' + errorText).css('color', '#dc2626');
                    $btn.text('❌ ذخیره نشد').css({'background':'#dc2626','border-color':'#dc2626','color':'#fff'});
                    showNotice('❌ ' + errorText, 'error');
                    setTimeout(function() {
                        $btn.text('💾 ذخیره تنظیمات این بخش').css({'background':'','border-color':'','color':''});
                    }, 2500);
                }
            }).fail(function(xhr, status) {
                if (status === 'abort') return;
                var msg = xhr.status === 403 ? 'نشست یا دسترسی منقضی شده است. صفحه را رفرش کنید.' : 'خطا در ارتباط با سرور.';
                $status.text('❌ ' + msg).css('color', '#dc2626');
                $btn.text('❌ ذخیره نشد').css({'background':'#dc2626','border-color':'#dc2626','color':'#fff'});
                showNotice('❌ ' + msg, 'error');
                setTimeout(function() {
                    $btn.text('💾 ذخیره تنظیمات این بخش').css({'background':'','border-color':'','color':''});
                }, 2500);
            }).always(function() {
                settingsSaveXHR = null;
                if ($btn.text().indexOf('⏳') === 0) { $btn.text('💾 ذخیره تنظیمات این بخش'); }
                $btn.prop('disabled', false);
            });
        });

        var urlParams = new URLSearchParams(window.location.search);
        var activeTab = urlParams.get('tab') || 'general';
        loadSettingsTab(activeTab);
    }

    // ============================================================
    // ۴. ذخیره کدها (AJAX)
    // ============================================================
    $('.ezlens-editor .btn-save').on('click', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var section = $btn.data('section');
        var container = $btn.closest('.ezlens-editor-container');

        var data = {
            action: 'ezlens_save_code',
            nonce: ezlens_auth_ajax.nonce,
            section: section,
            html: container.find('#code-html').val(),
            css: container.find('#code-css').val(),
            js: container.find('#code-js').val()
        };

        $btn.text('⏳ در حال ذخیره...').prop('disabled', true);
        container.find('#ajax-status').text('⏳ در حال ذخیره...').css('color', '#d97706');

        $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
            if (response.success) {
                container.find('#ajax-status').text('✅ ذخیره شد').css('color', '#16a34a');
                showNotice('✅ ' + response.data, 'success');
            } else {
                container.find('#ajax-status').text('❌ خطا').css('color', '#dc2626');
                showNotice('❌ ' + response.data, 'error');
            }
        }).fail(function() {
            container.find('#ajax-status').text('❌ خطای ارتباط').css('color', '#dc2626');
            showNotice('❌ خطا در ارتباط با سرور', 'error');
        }).always(function() {
            $btn.text('💾 ذخیره').prop('disabled', false);
            setTimeout(function() {
                container.find('#ajax-status').text('');
            }, 3000);
        });
    });

    // ============================================================
    // ۵. بازنشانی کدها (AJAX)
    // ============================================================
    $('.ezlens-editor .btn-reset').on('click', function(e) {
        e.preventDefault();

        if (!confirm('آیا مطمئن هستید؟ کدها به حالت پیش‌فرض بازنشانی می‌شوند.')) {
            return;
        }

        var $btn = $(this);
        var section = $btn.data('section');
        var container = $btn.closest('.ezlens-editor-container');

        var data = {
            action: 'ezlens_reset_code',
            nonce: ezlens_auth_ajax.nonce,
            section: section
        };

        $btn.text('⏳ در حال بازنشانی...').prop('disabled', true);

        $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
            if (response.success) {
                showNotice('↩️ ' + response.data, 'success');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                showNotice('❌ ' + response.data, 'error');
            }
        }).fail(function() {
            showNotice('❌ خطا در ارتباط با سرور', 'error');
        }).always(function() {
            $btn.text('↩️ بازنشانی').prop('disabled', false);
        });
    });

    // ============================================================
    // ۶. پیش‌نمایش (AJAX)
    // ============================================================
    $('.ezlens-editor .btn-preview').on('click', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var section = $btn.data('section');
        var container = $btn.closest('.ezlens-editor-container');

        var data = {
            action: 'ezlens_preview_page',
            nonce: ezlens_auth_ajax.nonce,
            section: section,
            html: container.find('#code-html').val(),
            css: container.find('#code-css').val(),
            js: container.find('#code-js').val()
        };

        var win = window.open('', '_blank', 'width=1024,height=768,scrollbars=yes');
        if (!win) {
            showNotice('⚠️ لطفاً پاپ‌آپ را در مرورگر خود فعال کنید.', 'error');
            return;
        }

        win.document.write('<html><head><title>پیش‌نمایش - EzLens</title></head><body style="margin:0;padding:0;background:#fff;">');
        win.document.write('<div style="padding:10px;background:#f7fafc;border-bottom:1px solid #e2e8f0;font-family:Tahoma,sans-serif;font-size:12px;color:#718096;text-align:center;">');
        win.document.write('🔍 پیش‌نمایش صفحه <strong>' + section + '</strong> — کدها به‌صورت ایزوله اجرا می‌شوند');
        win.document.write('</div>');

        $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
            win.document.write(response);
            win.document.write('</body></html>');
            win.document.close();
        }).fail(function() {
            win.document.write('<div style="padding:40px;text-align:center;color:#e53e3e;font-family:Tahoma,sans-serif;">❌ خطا در بارگذاری پیش‌نمایش</div>');
            win.document.write('</body></html>');
            win.document.close();
            showNotice('❌ خطا در بارگذاری پیش‌نمایش', 'error');
        });
    });

    // ============================================================
    // ۷. فعال/غیرفعال‌سازی صفحه (AJAX)
    // ============================================================
    $('.ezlens-editor .btn-toggle-page').on('click', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var section = $btn.data('section');
        var current = $btn.data('current');
        var newStatus = current === '1' ? '0' : '1';

        var data = {
            action: 'ezlens_toggle_page',
            nonce: ezlens_auth_ajax.nonce,
            section: section,
            status: newStatus
        };

        $btn.text('⏳ در حال تغییر...').prop('disabled', true);

        $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
            if (response.success) {
                var statusText = newStatus === '1' ? 'فعال' : 'غیرفعال';
                $btn.data('current', newStatus);
                $btn.text(newStatus === '1' ? '🔴 غیرفعال‌سازی صفحه' : '🟢 فعال‌سازی صفحه');

                var badge = $('#page-status-badge');
                badge.text(newStatus === '1' ? '✅ فعال' : '❌ غیرفعال');
                badge.removeClass('on off').addClass(newStatus === '1' ? 'on' : 'off');

                showNotice('✅ وضعیت صفحه با موفقیت تغییر کرد: ' + statusText, 'success');
            } else {
                showNotice('❌ ' + response.data, 'error');
            }
        }).fail(function() {
            showNotice('❌ خطا در ارتباط با سرور', 'error');
        }).always(function() {
            setTimeout(function() {
                var currentStatus = $btn.data('current');
                $btn.text(currentStatus === '1' ? '🔴 غیرفعال‌سازی صفحه' : '🟢 فعال‌سازی صفحه');
                $btn.prop('disabled', false);
            }, 1000);
        });
    });

    // ============================================================
    // ۸. ذخیره تنظیمات اختصاصی صفحه (AJAX)
    // ============================================================
    var settingsTimeout;

    $('.ezlens-editor .editor-settings-box input').on('input change', function() {
        clearTimeout(settingsTimeout);
        var container = $('.ezlens-editor-container');
        var section = container.data('section');

        $('#page-settings-status').text('⏳ در حال ذخیره...').css('color', '#d97706');

        settingsTimeout = setTimeout(function() {
            var data = {
                action: 'ezlens_save_page_settings',
                nonce: ezlens_auth_ajax.nonce,
                section: section,
                page_title: $('#page_title').val(),
                page_subtitle: $('#page_subtitle').val(),
                primary_color: $('#page_primary_color').val(),
                redirect_url: $('#page_redirect_url').val()
            };

            $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    $('#page-settings-status').text('✅ ذخیره شد').css('color', '#16a34a');
                } else {
                    $('#page-settings-status').text('❌ خطا').css('color', '#dc2626');
                }
            }).fail(function() {
                $('#page-settings-status').text('❌ خطای ارتباط').css('color', '#dc2626');
            });
        }, 800);
    });

    // ============================================================
    // ۹. کپی شورت‌کد
    // ============================================================
    $('.copy-shortcode').on('click', function() {
        var code = $(this).data('code');
        if (code) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(function() {
                    showNotice('📋 شورت‌کد کپی شد: ' + code, 'success');
                }).catch(function() { fallbackCopy(code); });
            } else {
                fallbackCopy(code);
            }
        }
    });

    function fallbackCopy(text) {
        var temp = $('<input>');
        $('body').append(temp);
        temp.val(text).select();
        try {
            document.execCommand('copy');
            showNotice('📋 شورت‌کد کپی شد: ' + text, 'success');
        } catch(e) {
            showNotice('❌ کپی ناموفق', 'error');
        }
        temp.remove();
    }

    // ============================================================
    // ۱۰. بارگذاری آمار داشبورد
    // ============================================================
    function loadDashboardStats() {
        var $container = $('.ezlens-dashboard');
        if (!$container.length) return;

        var data = {
            action: 'ezlens_get_stats',
            nonce: ezlens_auth_ajax.nonce
        };

        $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
            if (response.success) {
                var stats = response.data;
                $container.find('.stat-users .stat-number').text(stats.users);
                $container.find('.stat-admins .stat-number').text(stats.admins);
                $container.find('.stat-logins .stat-number').text(stats.logins);
                $container.find('.stat-logouts .stat-number').text(stats.logouts);
                $container.find('.quick-item .quick-number').first().text(stats.users);
            }
        });
    }

    loadDashboardStats();

    // ============================================================
    // ۱۱. نمایش پیام
    // ============================================================
    function showNotice(message, type) {
        $('.ezlens-notice').remove();
        var $notice = $('<div class="ezlens-notice ' + type + '">' + message + '</div>');
        $('.wrap.ezlens-editor, .wrap.ezlens-dashboard, .wrap.ezlens-settings').first().prepend($notice);
        $('html, body').animate({ scrollTop: $notice.offset().top - 40 }, 300);
        setTimeout(function() {
            $notice.fadeOut(300, function() { $(this).remove(); });
        }, 5000);
    }

    // ============================================================
    // ۱۲. حذف پیام‌های Query String
    // ============================================================
    if (window.location.search.indexOf('saved=1') !== -1) {
        showNotice('✅ تنظیمات با موفقیت ذخیره شد.', 'success');
        if (window.history && window.history.replaceState) {
            var url = window.location.href.replace(/(\?|&)saved=1/, '');
            window.history.replaceState(null, '', url);
        }
    }

    if (window.location.search.indexOf('reset=1') !== -1) {
        showNotice('↩️ تنظیمات به حالت پیش‌فرض بازگشت.', 'info');
        if (window.history && window.history.replaceState) {
            var url = window.location.href.replace(/(\?|&)reset=1/, '');
            window.history.replaceState(null, '', url);
        }
    }

    // ============================================================
    // ۱۳. فیلتر لاگ‌ها
    // ============================================================
    function filterLogs() {
        var action = $('#log-filter-action').val();
        var search = $('#log-filter-search').val().toLowerCase().trim();

        $('#logs-tbody tr').each(function() {
            var $row = $(this);
            var show = true;

            if (action !== 'all' && $row.data('action') !== action) {
                show = false;
            }

            if (search && $row.data('username') && $row.data('username').indexOf(search) === -1) {
                show = false;
            }

            $row.toggle(show);
        });

        var count = $('#logs-tbody tr:visible').length;
        $('#log-count').text(count);
    }

    $('#log-filter-apply').on('click', filterLogs);
    $('#log-filter-reset').on('click', function() {
        $('#log-filter-action').val('all');
        $('#log-filter-search').val('');
        filterLogs();
    });
    $('#log-filter-search').on('keypress', function(e) {
        if (e.which === 13) filterLogs();
    });
    $('#log-filter-action').on('change', filterLogs);

    // ============================================================
    // Messaging tests (works for AJAX-loaded settings tabs)
    // ============================================================
    function ezlensRenderMessagingResult(result, ok) {
        result = result || {};
        var html = '<div style="margin-top:8px;padding:10px 12px;border-radius:8px;border:1px solid '+(ok?'#bbf7d0':'#fecaca')+';background:'+(ok?'#f0fdf4':'#fef2f2')+';line-height:1.9;max-width:680px;">';
        html += '<strong>' + (ok ? '🟢 تست موفق' : '🔴 تست ناموفق') + '</strong><br>';
        if (result.message) html += '<b>نتیجه:</b> ' + $('<div>').text(result.message).html() + '<br>';
        if (result.mailer) html += '<b>Mailer:</b> ' + $('<div>').text(result.mailer).html() + '<br>';
        if (result.provider) html += '<b>سرویس:</b> ' + $('<div>').text(result.provider).html() + '<br>';
        if (result.http_code) html += '<b>HTTP:</b> ' + $('<div>').text(result.http_code).html() + '<br>';
        if (result.latency_ms !== undefined) html += '<b>زمان پاسخ:</b> ' + $('<div>').text(result.latency_ms).html() + ' ms<br>';
        if (result.reason_code) html += '<b>کد علت:</b> ' + $('<div>').text(result.reason_code).html() + '<br>';
        if (result.error_code) html += '<b>کد خطا:</b> ' + $('<div>').text(result.error_code).html() + '<br>';
        if (result.smtp_enabled !== undefined) html += '<b>SMTP:</b> ' + (result.smtp_enabled ? 'فعال' : 'غیرفعال') + '<br>';
        html += '</div>';
        return html;
    }

    function ezlensMessagingTest(target, resultBox, button, channel, message) {
        target = $.trim(target || '');
        if (!target) {
            resultBox.html('<span style="color:#dc2626">ایمیل/شماره تست را وارد کنید.</span>');
            return;
        }
        var oldText = button.text();
        button.prop('disabled', true).text('⏳ در حال تست...');
        resultBox.html('<span style="color:#d97706">در حال اتصال به سرویس...</span>');

        $.ajax({
            url: (window.ezlens_auth_ajax && ezlens_auth_ajax.ajax_url) ? ezlens_auth_ajax.ajax_url : window.ajaxurl,
            type: 'POST',
            dataType: 'json',
            timeout: 45000,
            data: {
                action: 'ezlens_messaging_test',
                nonce: (window.ezlens_auth_ajax && ezlens_auth_ajax.nonce) ? ezlens_auth_ajax.nonce : '',
                channel: channel,
                target: target,
                message: message || ''
            }
        }).done(function(x) {
            var d = x && x.data ? x.data : {};
            var res = d.result || {};
            resultBox.html(ezlensRenderMessagingResult(res, !!(x && x.success)));
        }).fail(function(xhr, status) {
            var msg = status === 'timeout' ? 'زمان پاسخ سرور تمام شد (45 ثانیه).' : 'خطا در ارتباط AJAX.';
            try {
                var x = JSON.parse(xhr.responseText || '{}');
                var d = x.data || {};
                if (typeof d === 'string') msg = d;
                else if (d.message) msg = d.message;
            } catch(e) {}
            resultBox.html('<div style="margin-top:8px;color:#b91c1c">🔴 '+$('<div>').text(msg).html()+'</div>');
        }).always(function() {
            button.prop('disabled', false).text(oldText);
        });
    }

    // Delegated handlers are essential because settings tabs are injected with innerHTML.
    $(document).off('click.ezlensCoreEmailTest', '#ezlens-email-test, #ezlens-email-test-settings')
        .on('click.ezlensCoreEmailTest', '#ezlens-email-test, #ezlens-email-test-settings', function(e) {
            e.preventDefault();
            var b=$(this);
            var targetId=b.attr('id')==='ezlens-email-test-settings' ? '#ezlens-email-test-settings-target' : '#ezlens_email_test_target';
            var resultId=b.attr('id')==='ezlens-email-test-settings' ? '#ezlens-email-test-settings-result' : '#ezlens-email-test-result';
            ezlensMessagingTest($(targetId).val(), $(resultId), b, 'email', 'این یک ایمیل تست از EzLens Secure Login است.');
        });

    $(document).off('click.ezlensCoreMsgTest', '.ezlens-msg-test')
        .on('click.ezlensCoreMsgTest', '.ezlens-msg-test', function(e) {
            e.preventDefault();
            var b=$(this), ch=String(b.data('channel') || ''), target='', message='';
            if(ch==='otp') target=$('#ezlens_otp_test_target').val();
            if(ch==='campaign') { target=$('#ezlens_campaign_test_target').val(); message=$('#ezlens_campaign_test_message').val(); }
            var resultBox=b.siblings('.ezlens-msg-test-result');
            ezlensMessagingTest(target, resultBox, b, ch, message);
        });

    // ============================================================
    // ۱۴. مقداردهی اولیه
    // ============================================================
    console.log('✅ EzLens Secure Login - Admin Loaded');
    console.log('📌 نسخه: ' + (ezlens_auth_ajax.version || '2.3.3'));

})(jQuery);