/**
 * EzLens Secure Login - Admin Login Scripts (فاز ۵)
 * بخش‌های: ورود مدیر، فراموشی رمز مدیر
 * @version 2.3.3
 */
(function($) {
    'use strict';

    // ============================================================
    // ۱. نمایش پیام
    // ============================================================
    function showAdminMessage(message, type, containerId) {
        var container = document.getElementById(containerId || 'admin-login-msg');
        if (!container) return;
        container.textContent = message;
        container.className = 'admin-auth-msg ' + type;
        container.style.display = 'block';
        container.setAttribute('role', 'alert');
        container.setAttribute('aria-live', 'polite');
        if (type === 'error') {
            container.classList.add('shake');
            setTimeout(function() { container.classList.remove('shake'); }, 500);
        }
    }

    // ============================================================
    // ۲. رفرش کپچا (مدیر)
    // ============================================================
    document.querySelectorAll('.admin-auth-captcha .captcha-reload').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var container = this.closest('.admin-auth-captcha');
            var num1 = container.querySelector('.captcha-numbers span:first-child');
            var num2 = container.querySelector('.captcha-numbers span:nth-child(3)');
            var input = container.querySelector('.captcha-input');
            var n1 = Math.floor(Math.random() * 9) + 1;
            var n2 = Math.floor(Math.random() * 9) + 1;
            if (num1) num1.textContent = n1;
            if (num2) num2.textContent = n2;
            if (input) input.value = '';
            var hidden1 = container.querySelector('input[name="captcha_num1"]');
            var hidden2 = container.querySelector('input[name="captcha_num2"]');
            if (hidden1) hidden1.value = n1;
            if (hidden2) hidden2.value = n2;
        });
    });

    // ============================================================
    // ۳. چشم نمایش رمز (مدیر)
    // ============================================================
    document.querySelectorAll('.admin-auth-field .toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var wrapper = this.closest('.password-wrap');
            var input = wrapper ? wrapper.querySelector('input[type="password"], input[type="text"]') : null;
            if (!input) return;
            var eyeOpen = this.querySelector('.eye-open');
            var eyeClosed = this.querySelector('.eye-closed');
            if (input.getAttribute('type') === 'password') {
                input.setAttribute('type', 'text');
                if (eyeOpen) eyeOpen.style.display = 'none';
                if (eyeClosed) eyeClosed.style.display = 'inline';
                this.setAttribute('aria-label', 'مخفی کردن رمز عبور');
            } else {
                input.setAttribute('type', 'password');
                if (eyeOpen) eyeOpen.style.display = 'inline';
                if (eyeClosed) eyeClosed.style.display = 'none';
                this.setAttribute('aria-label', 'نمایش رمز عبور');
            }
        });
    });

    // ============================================================
    // ۴. ارسال فرم ورود مدیر
    // ============================================================
    var adminLoginForm = document.getElementById('admin-login-form');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('adminLoginBtn');
            var msg = document.getElementById('admin-login-msg');
            var username = document.getElementById('admin_username').value.trim();
            var password = document.getElementById('admin_password').value;
            var captcha = this.querySelector('.admin-auth-captcha .captcha-input');
            var captchaVal = captcha ? captcha.value.trim() : '';

            if (!username || !password) {
                showAdminMessage('لطفاً نام کاربری و رمز عبور را وارد کنید.', 'error', 'admin-login-msg');
                return;
            }
            if (!captchaVal) {
                showAdminMessage('لطفاً کد امنیتی را وارد کنید.', 'error', 'admin-login-msg');
                return;
            }

            msg.style.display = 'none';
            btn.disabled = true;
            btn.textContent = '⏳ در حال ورود...';

            var formData = new FormData(this);
            formData.append('action', 'min_auth_login');
            formData.append('security', ezlens_frontend.nonce);

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = 'ورود به مدیریت';

                if (data.success) {
                    showAdminMessage(data.data.message || 'ورود موفقیت‌آمیز.', 'success', 'admin-login-msg');
                    if (data.data.redirect) {
                        setTimeout(function() {
                            window.location.href = data.data.redirect;
                        }, 1200);
                    }
                } else {
                    showAdminMessage(data.data.message || 'نام کاربری یا رمز عبور اشتباه است.', 'error', 'admin-login-msg');
                    var reloadBtn = document.querySelector('.admin-auth-captcha .captcha-reload');
                    if (reloadBtn) reloadBtn.click();
                    document.getElementById('admin_password').value = '';
                    document.getElementById('admin_password').focus();
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = 'ورود به مدیریت';
                showAdminMessage('خطای ارتباط با سرور. لطفاً مجدداً تلاش کنید.', 'error', 'admin-login-msg');
            });
        });
    }

    // ============================================================
    // ۵. فراموشی رمز مدیر (اورلی)
    // ============================================================
    var adminForgotOverlay = document.getElementById('adminForgotOverlay');
    if (adminForgotOverlay) {
        var showAdminForgot = document.getElementById('showAdminForgot');
        var closeAdminForgot = document.getElementById('closeAdminForgot');
        var backToAdminLogin = document.getElementById('backToAdminLogin');
        var adminForgotTabs = adminForgotOverlay.querySelectorAll('.admin-auth-forgot-tab');
        var adminForgotForms = adminForgotOverlay.querySelectorAll('.admin-auth-forgot-form');

        if (showAdminForgot) {
            showAdminForgot.addEventListener('click', function() {
                adminForgotOverlay.classList.add('active');
                adminForgotTabs.forEach(function(t) { t.classList.remove('active'); });
                adminForgotForms.forEach(function(f) { f.classList.remove('active'); });
                if (adminForgotTabs.length) adminForgotTabs[0].classList.add('active');
                if (adminForgotForms.length) adminForgotForms[0].classList.add('active');
                adminForgotOverlay.querySelectorAll('.forgot-msg').forEach(function(el) {
                    el.style.display = 'none';
                    el.textContent = '';
                });
                document.getElementById('adminForgotVerifySection').style.display = 'none';
            });
        }

        if (closeAdminForgot) {
            closeAdminForgot.addEventListener('click', function() {
                adminForgotOverlay.classList.remove('active');
            });
        }

        if (backToAdminLogin) {
            backToAdminLogin.addEventListener('click', function() {
                adminForgotOverlay.classList.remove('active');
            });
        }

        adminForgotOverlay.addEventListener('click', function(e) {
            if (e.target === this) adminForgotOverlay.classList.remove('active');
        });

        adminForgotTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var target = this.getAttribute('data-target');
                adminForgotTabs.forEach(function(t) { t.classList.remove('active'); });
                adminForgotForms.forEach(function(f) { f.classList.remove('active'); });
                this.classList.add('active');
                adminForgotForms.forEach(function(f) {
                    if (f.id === target + 'Form') f.classList.add('active');
                });
                adminForgotOverlay.querySelectorAll('.forgot-msg').forEach(function(el) {
                    el.style.display = 'none';
                    el.textContent = '';
                });
                document.getElementById('adminForgotVerifySection').style.display = 'none';
            });
        });

        // ارسال فرم‌های فراموشی مدیر
        var adminForgotEmailForm = document.getElementById('adminForgotEmailForm');
        var adminForgotPhoneForm = document.getElementById('adminForgotPhoneForm');

        function handleAdminForgotSubmit(e) {
            e.preventDefault();
            var form = e.target;
            var btn = form.querySelector('.admin-auth-forgot-btn');
            var originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = '⏳ در حال ارسال...';
            var msgBox = form.querySelector('.forgot-msg');
            msgBox.style.display = 'none';
            msgBox.textContent = '';

            var formData = new FormData(form);
            formData.append('security', ezlens_frontend.nonce);

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = originalText;
                if (data.success) {
                    msgBox.className = 'forgot-msg success';
                    msgBox.textContent = '✅ ' + data.data.message;
                    msgBox.style.display = 'block';
                    msgBox.setAttribute('role', 'alert');
                    var verifySection = document.getElementById('adminForgotVerifySection');
                    if (verifySection) {
                        verifySection.style.display = 'block';
                        document.getElementById('adminForgotUserId').value = data.data.user_id || '';
                        document.getElementById('adminForgotType').value = data.data.type || 'email';
                        document.getElementById('adminForgotCode').value = '';
                        document.getElementById('adminForgotCode').focus();
                    }
                } else {
                    msgBox.className = 'forgot-msg error';
                    msgBox.textContent = '❌ ' + (data.data.message || 'خطا رخ داد.');
                    msgBox.style.display = 'block';
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = originalText;
                msgBox.className = 'forgot-msg error';
                msgBox.textContent = '❌ خطا در ارتباط با سرور.';
                msgBox.style.display = 'block';
            });
        }

        if (adminForgotEmailForm) adminForgotEmailForm.addEventListener('submit', handleAdminForgotSubmit);
        if (adminForgotPhoneForm) adminForgotPhoneForm.addEventListener('submit', handleAdminForgotSubmit);

        // تأیید کد فراموشی مدیر
        var adminForgotVerifyBtn = document.getElementById('adminForgotVerifyBtn');
        if (adminForgotVerifyBtn) {
            adminForgotVerifyBtn.addEventListener('click', function() {
                var code = document.getElementById('adminForgotCode').value.trim();
                if (code.length !== 6 || !/^\d{6}$/.test(code)) {
                    var msgBox = adminForgotOverlay.querySelector('.forgot-msg');
                    if (msgBox) {
                        msgBox.className = 'forgot-msg error';
                        msgBox.textContent = '❌ کد باید ۶ رقم باشد.';
                        msgBox.style.display = 'block';
                    }
                    return;
                }

                adminForgotVerifyBtn.disabled = true;
                adminForgotVerifyBtn.textContent = '⏳ در حال تأیید...';

                var formData = new FormData();
                formData.append('action', 'ezlens_admin_forgot_verify');
                formData.append('security', ezlens_frontend.nonce);
                formData.append('user_id', document.getElementById('adminForgotUserId').value);
                formData.append('code', code);
                formData.append('type', document.getElementById('adminForgotType').value);

                fetch(ezlens_frontend.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    adminForgotVerifyBtn.disabled = false;
                    adminForgotVerifyBtn.textContent = '✅ تأیید کد';
                    var msgBox = adminForgotOverlay.querySelector('.forgot-msg');
                    if (msgBox) {
                        if (data.success) {
                            msgBox.className = 'forgot-msg success';
                            msgBox.textContent = '✅ ' + data.data.message;
                            msgBox.style.display = 'block';
                            if (data.data.redirect) {
                                setTimeout(function() {
                                    window.location.href = data.data.redirect;
                                }, 1500);
                            }
                        } else {
                            msgBox.className = 'forgot-msg error';
                            msgBox.textContent = '❌ ' + (data.data.message || 'خطا رخ داد.');
                            msgBox.style.display = 'block';
                        }
                    }
                })
                .catch(function() {
                    adminForgotVerifyBtn.disabled = false;
                    adminForgotVerifyBtn.textContent = '✅ تأیید کد';
                    var msgBox = adminForgotOverlay.querySelector('.forgot-msg');
                    if (msgBox) {
                        msgBox.className = 'forgot-msg error';
                        msgBox.textContent = '❌ خطا در ارتباط با سرور.';
                        msgBox.style.display = 'block';
                    }
                });
            });
        }
    }

    // ============================================================
    // ۶. فوکوس خودکار روی فیلد نام کاربری
    // ============================================================
    var usernameField = document.getElementById('admin_username');
    if (usernameField && !usernameField.value) {
        usernameField.focus();
    }

    console.log('✅ EzLens Admin Login Scripts Loaded (admin-login.js)');
})(jQuery);