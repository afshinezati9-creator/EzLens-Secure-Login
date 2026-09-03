<?php
/**
 * قالب صفحه ویرایشگر – مدرن‌سازی شده (فاز ۸)
 * @version 2.3.3
 */

$sections = [
    'customer-login' => 'ورود مشتری',
    'lost-password'  => 'فراموشی رمز',
    'user-panel'     => 'پنل کاربری',
    'admin-login'    => 'ورود مدیر',
];

$shortcode_map = [
    'customer-login' => '[minimal_auth]',
    'lost-password'  => '[ezlens_lost_password]',
    'user-panel'     => '[modern_user_panel]',
    'admin-login'    => '[admin_login_page]',
];

$current_section = isset($_GET['section']) ? sanitize_key($_GET['section']) : 'customer-login';
$is_enabled = EzLens_Auth_Settings::is_page_enabled($current_section);

$codes = get_option('ezlens_auth_codes_' . $current_section, []);
$defaults = EzLens_Auth_Admin::get_instance()->get_default_codes($current_section);
$codes = wp_parse_args($codes, $defaults);

$page_title = EzLens_Auth_Settings::get_page_title($current_section);
$page_subtitle = EzLens_Auth_Settings::get_page_subtitle($current_section);
$page_primary_color = EzLens_Auth_Settings::get_page_setting($current_section, 'primary_color') ?: EzLens_Auth_Settings::get('primary_color');
$page_redirect_url = EzLens_Auth_Settings::get_page_setting($current_section, 'redirect_url') ?: home_url();

// آیکون‌ها
$icon_save = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/archive-box-arrow-down.svg');
if (!$icon_save) $icon_save = '💾';
$icon_preview = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/eye.svg');
if (!$icon_preview) $icon_preview = '👁️';
$icon_reset = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/arrow-path-rounded-square.svg');
if (!$icon_reset) $icon_reset = '↩️';
$icon_copy = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/document-duplicate.svg');
if (!$icon_copy) $icon_copy = '📋';

$guide_content = [
    'customer-login' => '
        <div class="guide-section">
            <h5>🎯 هدف صفحه</h5>
            <p>ورود و ثبت‌نام کاربران عادی سایت. این صفحه شامل دو تب <strong>ورود</strong> و <strong>ثبت‌نام</strong> است.</p>
        </div>
        <div class="guide-section">
            <h5>📌 کلاس‌های اصلی</h5>
            <ul>
                <li><code>.min-auth-wrapper</code> – کانتینر اصلی</li>
                <li><code>.min-auth-container</code> – باکس شیشه‌ای اصلی</li>
                <li><code>.min-auth-tabs</code> – تب‌های ورود/ثبت‌نام</li>
                <li><code>.min-auth-form</code> – فرم‌ها (با کلاس <code>active</code> برای نمایش)</li>
            </ul>
        </div>
    ',
    'lost-password' => '
        <div class="guide-section">
            <h5>🎯 هدف صفحه</h5>
            <p>بازیابی رمز عبور کاربران از طریق ایمیل.</p>
        </div>
        <div class="guide-section">
            <h5>📌 کلاس‌های اصلی</h5>
            <ul>
                <li><code>.lp-wrapper</code> – کانتینر اصلی</li>
                <li><code>.lp-card</code> – باکس شیشه‌ای</li>
                <li><code>.lp-header</code> – بخش بالایی با آیکون و عنوان</li>
            </ul>
        </div>
    ',
    'user-panel' => '
        <div class="guide-section">
            <h5>🎯 هدف صفحه</h5>
            <p>داشبورد مدیریت کاربری مشتری. شامل پیشخوان، سبد خرید، سفارش‌ها، آدرس، پروفایل، نسخه پزشکی، امنیت و پشتیبانی.</p>
        </div>
        <div class="guide-section">
            <h5>📌 کلاس‌های اصلی</h5>
            <ul>
                <li><code>.ezu</code> – کانتینر اصلی</li>
                <li><code>.ezu-side</code> – سایدبار منو</li>
                <li><code>.ezu-main</code> – محتوای اصلی</li>
                <li><code>.ezu-tab</code> – هر تب</li>
            </ul>
        </div>
    ',
    'admin-login' => '
        <div class="guide-section">
            <h5>🎯 هدف صفحه</h5>
            <p>ورود اختصاصی مدیران با آدرس جدید. آدرس‌های <code>wp-admin</code> و <code>wp-login.php</code> برای کاربران عادی مسدود شده‌اند.</p>
        </div>
        <div class="guide-section">
            <h5>📌 کلاس‌های اصلی</h5>
            <ul>
                <li><code>.admin-auth-wrap</code> – کانتینر اصلی</li>
                <li><code>.admin-auth-card</code> – باکس شیشه‌ای</li>
                <li><code>.admin-auth-badge</code> – برچسب "ورود مدیران"</li>
            </ul>
        </div>
    ',
];
?>
<div class="wrap ezlens-editor">
    <h1 class="wp-heading-inline">✏️ ویرایش صفحات</h1>
    <p class="description">ویرایش HTML، CSS و JS صفحات (بدون هدر/فوتر) – کدها به‌صورت ایزوله اجرا می‌شوند</p>

    <?php if (isset($_GET['saved'])): ?>
        <div class="ezlens-notice success" role="alert" aria-live="polite">✅ کدها با موفقیت ذخیره شدند.</div>
    <?php endif; ?>
    <?php if (isset($_GET['reset'])): ?>
        <div class="ezlens-notice info" role="alert" aria-live="polite">↩️ کدها به حالت پیش‌فرض بازگشتند.</div>
    <?php endif; ?>

    <ul class="ezlens-editor-tabs">
        <?php foreach ($sections as $key => $label): ?>
            <li class="<?php echo $current_section === $key ? 'active' : ''; ?>">
                <a href="<?php echo admin_url('admin.php?page=ezlens-auth-editor&section=' . $key); ?>"><?php echo esc_html($label); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="ezlens-editor-container" data-section="<?php echo esc_attr($current_section); ?>">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="editor-form">
            <input type="hidden" name="action" value="ezlens_auth_save_code">
            <input type="hidden" name="section" value="<?php echo esc_attr($current_section); ?>">
            <?php wp_nonce_field('ezlens_auth_save_code'); ?>

            <div class="editor-status-bar">
                <div class="status-left">
                    <span class="status-label">وضعیت صفحه:</span>
                    <span class="status-badge <?php echo $is_enabled ? 'on' : 'off'; ?>" id="page-status-badge">
                        <?php echo $is_enabled ? '✅ فعال' : '❌ غیرفعال'; ?>
                    </span>
                </div>
                <div class="status-right">
                    <button type="button" class="btn-toggle-page" data-section="<?php echo esc_attr($current_section); ?>" data-current="<?php echo $is_enabled ? '1' : '0'; ?>">
                        <?php echo $is_enabled ? '🔴 غیرفعال‌سازی صفحه' : '🟢 فعال‌سازی صفحه'; ?>
                    </button>
                    <span class="shortcode-hint">
                        شورت‌کد: <code><?php echo esc_html($shortcode_map[$current_section] ?? ''); ?></code>
                        <button type="button" class="copy-shortcode" data-code="<?php echo esc_attr($shortcode_map[$current_section] ?? ''); ?>" title="کپی شورت‌کد" aria-label="کپی شورت‌کد"><?php echo $icon_copy; ?></button>
                    </span>
                </div>
            </div>

            <div class="editor-settings-box">
                <h4>⚙️ تنظیمات اختصاصی صفحه</h4>
                <div class="setting-row">
                    <label for="page_title">عنوان صفحه</label>
                    <input type="text" id="page_title" value="<?php echo esc_attr($page_title); ?>" placeholder="عنوان صفحه را وارد کنید" autofocus>
                </div>
                <div class="setting-row">
                    <label for="page_subtitle">توضیحات (زیر عنوان)</label>
                    <input type="text" id="page_subtitle" value="<?php echo esc_attr($page_subtitle); ?>" placeholder="توضیحات کوتاه">
                </div>
                <div class="setting-row">
                    <label for="page_primary_color">رنگ اصلی</label>
                    <input type="color" id="page_primary_color" value="<?php echo esc_attr($page_primary_color); ?>">
                </div>
                <div class="setting-row" style="border-bottom:none;padding-bottom:0;">
                    <label for="page_redirect_url">آدرس بازگشت (بعد از لاگین)</label>
                    <input type="text" id="page_redirect_url" value="<?php echo esc_url($page_redirect_url); ?>" placeholder="https://example.com">
                </div>
                <div class="setting-row" style="border-bottom:none;padding-bottom:0;border-top:1px solid var(--ezlens-border);padding-top:10px;">
                    <span id="page-settings-status" style="font-size:0.75rem;color:var(--ezlens-success);"></span>
                </div>
            </div>

            <div class="editor-toolbar">
                <button type="submit" class="btn-save" data-section="<?php echo esc_attr($current_section); ?>"><?php echo $icon_save; ?> ذخیره</button>
                <button type="button" class="btn-preview" data-section="<?php echo esc_attr($current_section); ?>"><?php echo $icon_preview; ?> پیش‌نمایش</button>
                <button type="button" class="btn-reset" data-section="<?php echo esc_attr($current_section); ?>"><?php echo $icon_reset; ?> بازنشانی</button>
                <span class="ajax-status" id="ajax-status" style="margin-right:auto;font-size:0.75rem;color:var(--ezlens-success);"></span>
            </div>

            <div class="triple-editor">
                <?php foreach (['html' => '📄 HTML', 'css' => '🎨 CSS', 'js' => '⚡ JS'] as $type => $label): ?>
                <div class="editor-box">
                    <div class="editor-head">
                        <span class="lang <?php echo $type; ?>"><?php echo $label; ?></span>
                        <span class="count"><?php echo count(explode("\n", $codes[$type] ?? '')); ?> خط</span>
                    </div>
                    <div class="editor-body">
                        <div class="line-numbers" id="lines-<?php echo $type; ?>"></div>
                        <textarea id="code-<?php echo $type; ?>" class="code-editor" name="<?php echo $type; ?>" spellcheck="false"><?php echo esc_textarea($codes[$type] ?? ''); ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </form>

        <div class="ezlens-guide-full" id="page-guide">
            <h4>📘 راهنمای کامل ساخت صفحه: <strong><?php echo esc_html($sections[$current_section]); ?></strong></h4>
            <div class="guide-content">
                <?php echo $guide_content[$current_section] ?? '<p>راهنمایی برای این صفحه تنظیم نشده است.</p>'; ?>
            </div>
            <div class="guide-tips">
                <strong>💡 نکات مهم:</strong>
                <ul>
                    <li>کدهای <strong>HTML، CSS و JS</strong> به‌صورت ایزوله اجرا می‌شوند (JS در IIFE).</li>
                    <li>صفحات <strong>بدون هدر و فوتر</strong> وردپرس نمایش داده می‌شوند.</li>
                    <li>برای تست، از دکمه <strong>پیش‌نمایش</strong> استفاده کنید.</li>
                    <li>پس از هر تغییر، دکمه <strong>ذخیره</strong> را بزنید.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    'use strict';

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
            $btn.html('<?php echo $icon_save; ?> ذخیره').prop('disabled', false);
            setTimeout(function() {
                container.find('#ajax-status').text('');
            }, 3000);
        });
    });

    $('.ezlens-editor .btn-reset').on('click', function(e) {
        e.preventDefault();
        if (!confirm('آیا مطمئن هستید؟ کدها به حالت پیش‌فرض بازنشانی می‌شوند.')) return;

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
            $btn.html('<?php echo $icon_reset; ?> بازنشانی').prop('disabled', false);
        });
    });

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

    function showNotice(message, type) {
        $('.ezlens-notice').remove();
        var $notice = $('<div class="ezlens-notice ' + type + '" role="alert" aria-live="polite">' + message + '</div>');
        $('.wrap.ezlens-editor').prepend($notice);
        $('html, body').animate({ scrollTop: $notice.offset().top - 40 }, 300);
        setTimeout(function() {
            $notice.fadeOut(300, function() { $(this).remove(); });
        }, 5000);
    }

    if (window.location.search.indexOf('saved=1') !== -1) {
        showNotice('✅ کدها با موفقیت ذخیره شدند.', 'success');
        if (window.history && window.history.replaceState) {
            var url = window.location.href.replace(/(\?|&)saved=1/, '');
            window.history.replaceState(null, '', url);
        }
    }

    if (window.location.search.indexOf('reset=1') !== -1) {
        showNotice('↩️ کدها به حالت پیش‌فرض بازگشتند.', 'info');
        if (window.history && window.history.replaceState) {
            var url = window.location.href.replace(/(\?|&)reset=1/, '');
            window.history.replaceState(null, '', url);
        }
    }

    console.log('✅ EzLens Editor Loaded');
});
</script>