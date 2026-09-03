/**
 * EzLens Secure Login - Panel Scripts (فاز ۵)
 * بخش پنل کاربری: تب‌ها، آپلود عکس، پروفایل، آدرس، رمز، نسخه
 * به همراه تبدیل خودکار اعداد به فارسی
 * @version 2.3.5
 */
(function($) {
    'use strict';

    // ============================================================
    // تبدیل اعداد به فارسی در کل پنل (قیمت‌ها، تاریخ‌ها و...)
    // ============================================================
    function convertToPersianDigits() {
        // تبدیل اعداد در متن‌های اصلی ووکامرس (قیمت‌ها، تعداد و جمع)
        $('.woocommerce-Price-amount, .amount, .product-price, .product-subtotal, .quantity input.qty, .product-quantity').each(function() {
            var text = $(this).text();
            var newText = text.replace(/[0-9]/g, function(d) {
                return ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'][d];
            });
            $(this).text(newText);
        });

        // تبدیل اعداد در کل متن‌های داخل پنل (مثل پیام‌ها و خطاها)
        $('.ezu, .woocommerce-message, .woocommerce-info, .woocommerce-orders-table__message').each(function() {
            var walker = document.createTreeWalker(this, NodeFilter.SHOW_TEXT, null, false);
            var node;
            while (node = walker.nextNode()) {
                if (node.nodeValue && /[0-9]/.test(node.nodeValue)) {
                    node.nodeValue = node.nodeValue.replace(/[0-9]/g, function(d) {
                        return ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'][d];
                    });
                }
            }
        });
    }

    // اجرای تبدیل اعداد بعد از بارگذاری اولیه صفحه
    $(document).ready(function() {
        convertToPersianDigits();
    });

    // ============================================================
    // مدیریت تب‌های پنل
    // ============================================================
    var panelRoot = document.querySelector('.ezu');
    if (panelRoot) {
        var panelTabs = panelRoot.querySelectorAll('.ezu-tab');
        var panelBtns = panelRoot.querySelectorAll('.ezu-nav-d button[data-tab], .ezu-bottom-nav button[data-tab]');

        function openPanelTab(tabId) {
            panelTabs.forEach(function(t) {
                t.classList.toggle('active', t.id === 'tab-' + tabId);
            });
            panelBtns.forEach(function(b) {
                b.classList.toggle('active', b.getAttribute('data-tab') === tabId);
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        panelBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                openPanelTab(this.getAttribute('data-tab'));
            });
        });

        panelRoot.querySelectorAll('[data-go]').forEach(function(a) {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                openPanelTab(this.getAttribute('data-go'));
            });
        });
    }

    // نمایش پیام (Toast) در پنل
    function showPanelMessage(message, type, containerId) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.textContent = message;
        container.className = type;
        container.style.display = 'block';
        container.setAttribute('role', 'alert');
        container.setAttribute('aria-live', 'polite');
        setTimeout(function() {
            container.style.display = 'none';
        }, 5000);
    }

    // ذخیره پروفایل
    var profileForm = document.getElementById('ezu-profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('ezu-save-profile');
            var status = document.getElementById('ezu-profile-status');
            btn.disabled = true;
            btn.textContent = '⏳ در حال ذخیره...';

            var formData = new FormData(this);
            formData.append('action', 'ezlens_save_profile');
            formData.append('security', document.querySelector('#ezu-profile-form input[name="profile_nonce"]').value);

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = '💾 ذخیره پروفایل';
                if (data.success) {
                    showPanelMessage('✅ ' + data.data.message, 'success', 'ezu-profile-status');
                } else {
                    showPanelMessage('❌ ' + (data.data.message || 'خطا رخ داد.'), 'error', 'ezu-profile-status');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = '💾 ذخیره پروفایل';
                showPanelMessage('❌ خطا در ارتباط با سرور.', 'error', 'ezu-profile-status');
            });
        });
    }

    // ذخیره آدرس
    var addressForm = document.getElementById('ezu-address-form');
    if (addressForm) {
        addressForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('ezu-save-address');
            var status = document.getElementById('ezu-address-status');
            btn.disabled = true;
            btn.textContent = '⏳ در حال ذخیره...';

            var formData = new FormData(this);
            formData.append('action', 'ezlens_save_address');
            formData.append('security', document.querySelector('#ezu-address-form input[name="address_nonce"]').value);

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = '💾 ذخیره آدرس';
                if (data.success) {
                    showPanelMessage('✅ ' + data.data.message, 'success', 'ezu-address-status');
                } else {
                    showPanelMessage('❌ ' + (data.data.message || 'خطا رخ داد.'), 'error', 'ezu-address-status');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = '💾 ذخیره آدرس';
                showPanelMessage('❌ خطا در ارتباط با سرور.', 'error', 'ezu-address-status');
            });
        });
    }

    // تغییر رمز عبور
    var passwordForm = document.getElementById('ezu-password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('ezu-change-password');
            var status = document.getElementById('ezu-password-status');
            var newPass = document.getElementById('new_pass').value;
            var newPass2 = document.getElementById('new_pass2').value;

            if (newPass.length < 8) {
                showPanelMessage('❌ رمز جدید حداقل ۸ کاراکتر باشد.', 'error', 'ezu-password-status');
                return;
            }
            if (newPass !== newPass2) {
                showPanelMessage('❌ تکرار رمز یکسان نیست.', 'error', 'ezu-password-status');
                return;
            }

            btn.disabled = true;
            btn.textContent = '⏳ در حال تغییر...';

            var formData = new FormData(this);
            formData.append('action', 'ezlens_change_password');
            formData.append('security', document.querySelector('#ezu-password-form input[name="password_nonce"]').value);

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = '🔑 تغییر رمز عبور';
                if (data.success) {
                    showPanelMessage('✅ ' + data.data.message, 'success', 'ezu-password-status');
                    document.getElementById('current_pass').value = '';
                    document.getElementById('new_pass').value = '';
                    document.getElementById('new_pass2').value = '';
                } else {
                    showPanelMessage('❌ ' + (data.data.message || 'خطا رخ داد.'), 'error', 'ezu-password-status');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = '🔑 تغییر رمز عبور';
                showPanelMessage('❌ خطا در ارتباط با سرور.', 'error', 'ezu-password-status');
            });
        });
    }

    // آپلود عکس پروفایل
    var avatarUpload = document.getElementById('ezu-avatar-upload');
    if (avatarUpload) {
        avatarUpload.addEventListener('change', function() {
            var file = this.files[0];
            if (!file) return;

            var formData = new FormData();
            formData.append('action', 'ezlens_upload_avatar');
            formData.append('ezu_avatar', file);
            formData.append('security', ezlens_frontend.nonce);

            var preview = document.getElementById('ezu-avatar-preview');
            var status = document.querySelector('.ezu-avatar-box .hint');
            if (status) status.textContent = '⏳ در حال آپلود...';

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    if (preview) preview.src = data.data.avatar_url;
                    if (status) status.textContent = '✅ عکس با موفقیت آپلود شد.';
                    location.reload();
                } else {
                    if (status) status.textContent = '❌ ' + (data.data.message || 'خطا');
                }
            })
            .catch(function() {
                if (status) status.textContent = '❌ خطا در ارتباط با سرور.';
            });
        });
    }

    // حذف عکس پروفایل
    var avatarRemove = document.getElementById('ezu-avatar-remove');
    if (avatarRemove) {
        avatarRemove.addEventListener('click', function() {
            if (!confirm('آیا از حذف عکس پروفایل اطمینان دارید؟')) return;

            var formData = new FormData();
            formData.append('action', 'ezlens_remove_avatar');
            formData.append('security', ezlens_frontend.nonce);

            var status = document.querySelector('.ezu-avatar-box .hint');
            if (status) status.textContent = '⏳ در حال حذف...';

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    if (status) status.textContent = '✅ عکس حذف شد.';
                    location.reload();
                } else {
                    if (status) status.textContent = '❌ ' + (data.data.message || 'خطا');
                }
            })
            .catch(function() {
                if (status) status.textContent = '❌ خطا در ارتباط با سرور.';
            });
        });
    }

    // آپلود نسخه پزشکی
    var prescriptionUpload = document.getElementById('ezu-prescription-upload');
    if (prescriptionUpload) {
        prescriptionUpload.addEventListener('change', function() {
            var file = this.files[0];
            if (!file) return;

            var formData = new FormData();
            formData.append('action', 'ezlens_upload_prescription');
            formData.append('ezu_prescription', file);
            formData.append('security', document.querySelector('#ezu-prescription-form input[name="prescription_nonce"]').value);

            var status = document.getElementById('ezu-prescription-status');
            if (status) {
                status.textContent = '⏳ در حال آپلود...';
                status.style.display = 'block';
            }

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    if (status) {
                        status.textContent = '✅ ' + data.data.message;
                        status.className = 'success';
                    }
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    if (status) {
                        status.textContent = '❌ ' + (data.data.message || 'خطا');
                        status.className = 'error';
                    }
                }
            })
            .catch(function() {
                if (status) {
                    status.textContent = '❌ خطا در ارتباط با سرور.';
                    status.className = 'error';
                }
            });
        });
    }

    // حذف نسخه پزشکی (تابع گلوبال)
    window.deletePrescription = function(btn) {
        if (!confirm('آیا از حذف این نسخه اطمینان دارید؟')) return;

        var index = btn.getAttribute('data-index');
        var formData = new FormData();
        formData.append('action', 'ezlens_delete_prescription');
        formData.append('prescription_index', index);
        formData.append('security', ezlens_frontend.nonce);

        var item = btn.closest('.ezu-prescription-item');
        if (item) item.style.opacity = '0.5';

        fetch(ezlens_frontend.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                if (item) item.remove();
                var list = document.getElementById('ezu-prescription-list');
                if (list && list.children.length === 0) {
                    list.innerHTML = '<p style="color:var(--ezlens-muted);">هیچ نسخه‌ای آپلود نشده است.</p>';
                }
            } else {
                if (item) item.style.opacity = '1';
                alert('❌ ' + (data.data.message || 'خطا در حذف.'));
            }
        })
        .catch(function() {
            if (item) item.style.opacity = '1';
            alert('❌ خطا در ارتباط با سرور.');
        });
    };

    // فراخوانی مجدد تبدیل اعداد بعد از هر تغییر (مثلاً بعد از ذخیره پروفایل یا آپلود)
    $(document).ajaxComplete(function() {
        convertToPersianDigits();
    });

    console.log('✅ EzLens Panel Scripts Loaded (panel.js)');
})(jQuery);