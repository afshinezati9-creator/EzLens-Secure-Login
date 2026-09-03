/**
 * EzLens Secure Login - Auth Scripts (فاز ۵)
 * بخش‌های: OTP، ورود/ثبت‌نام، فراموشی رمز مشتری، کپچا، تب‌ها
 * @version 2.3.3
 */
(function($) {
    'use strict';

    // ============================================================
    // ۱. تایمر OTP
    // ============================================================
    var otpTimerInterval = null;
    var otpRemainingSeconds = 0;

    function startOtpTimer(seconds, elementId) {
        var display = document.getElementById(elementId || 'otpTimer');
        if (!display) return;

        if (otpTimerInterval) {
            clearInterval(otpTimerInterval);
            otpTimerInterval = null;
        }

        otpRemainingSeconds = seconds;
        updateTimerDisplay(display);

        otpTimerInterval = setInterval(function() {
            otpRemainingSeconds--;
            if (otpRemainingSeconds <= 0) {
                clearInterval(otpTimerInterval);
                otpTimerInterval = null;
                display.textContent = '⏱️ منقضی شد';
                display.style.color = '#dc2626';
                var resendBtn = document.getElementById('otpResendBtn');
                if (resendBtn) {
                    resendBtn.disabled = false;
                    resendBtn.style.opacity = '1';
                    resendBtn.style.cursor = 'pointer';
                }
                var verifyBtn = document.getElementById('otpVerifyBtn');
                if (verifyBtn) {
                    verifyBtn.disabled = true;
                    verifyBtn.style.opacity = '0.5';
                }
            } else {
                updateTimerDisplay(display);
            }
        }, 1000);
    }

    function updateTimerDisplay(display) {
        var mins = Math.floor(otpRemainingSeconds / 60);
        var secs = otpRemainingSeconds % 60;
        display.textContent = '⏱️ ' + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        display.style.color = otpRemainingSeconds < 30 ? '#dc2626' : '#2b6cb0';
    }

    function stopOtpTimer() {
        if (otpTimerInterval) {
            clearInterval(otpTimerInterval);
            otpTimerInterval = null;
        }
        var display = document.getElementById('otpTimer');
        if (display) {
            display.textContent = '⏱️ 00:00';
            display.style.color = '#94a3b8';
        }
    }

    // ============================================================
    // ۲. نمایش پیام
    // ============================================================
    function showMessage(message, type, containerId) {
        var container = document.getElementById(containerId || 'minAuthMessage');
        if (!container) return;
        container.textContent = message;
        container.className = 'min-auth-message ' + type;
        container.style.display = 'block';
        container.setAttribute('role', 'alert');
        container.setAttribute('aria-live', 'polite');
        setTimeout(function() {
            container.style.display = 'none';
        }, 5000);
    }

    // ============================================================
    // ۳. رفرش کپچا (همه)
    // ============================================================
    function refreshAllCaptchas() {
        var n1 = Math.floor(Math.random() * 9) + 1;
        var n2 = Math.floor(Math.random() * 9) + 1;
        var maps = [
            ['otpNum1', 'otpNum2', 'otpCaptcha', 'otpCaptchaNum1', 'otpCaptchaNum2'],
            ['loginNum1', 'loginNum2', 'loginCaptcha', 'loginCaptchaNum1', 'loginCaptchaNum2'],
            ['regNum1', 'regNum2', 'regCaptcha', 'regCaptchaNum1', 'regCaptchaNum2']
        ];
        maps.forEach(function(ids) {
            var e1 = document.getElementById(ids[0]);
            var e2 = document.getElementById(ids[1]);
            var inp = document.getElementById(ids[2]);
            var h1 = document.getElementById(ids[3]);
            var h2 = document.getElementById(ids[4]);
            if (e1) e1.textContent = n1;
            if (e2) e2.textContent = n2;
            if (inp) inp.value = '';
            if (h1) h1.value = n1;
            if (h2) h2.value = n2;
        });
    }

    // ============================================================
    // ۴. مدیریت OTP
    // ============================================================
    var otpForm = document.getElementById('otpForm');
    var otpSendBtn = document.getElementById('otpSendBtn');
    var otpVerifySection = document.getElementById('otpVerifySection');
    var otpCode = document.getElementById('otpCode');
    var otpVerifyBtn = document.getElementById('otpVerifyBtn');
    var otpResendBtn = document.getElementById('otpResendBtn');
    var storedMobile = '';

    if (otpForm) {
        otpForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var mobile = this.querySelector('input[name="mobile"]').value.trim();
            if (!/^09\d{9}$/.test(mobile) && !/^9\d{9}$/.test(mobile)) {
                showMessage('❌ شماره موبایل نامعتبر است.', 'error', 'minAuthMessage');
                return;
            }

            var otpCap = document.querySelector('.min-auth-captcha.otp-captcha');
            if (otpCap && otpCap.style.display !== 'none') {
                var captchaInput = otpCap.querySelector('.captcha-input');
                var num1 = parseInt(document.getElementById('otpNum1').textContent);
                var num2 = parseInt(document.getElementById('otpNum2').textContent);
                var answer = parseInt(captchaInput.value.trim());
                if (isNaN(answer) || answer !== (num1 + num2)) {
                    showMessage('❌ کد امنیتی اشتباه است.', 'error', 'minAuthMessage');
                    refreshAllCaptchas();
                    return;
                }
            }

            var formData = new FormData();
            formData.append('action', 'ezlens_otp_send');
            formData.append('security', document.querySelector('#otpForm input[name="security"]').value);
            formData.append('mobile', mobile);

            if (otpCap && otpCap.style.display !== 'none') {
                var captchaInput2 = otpCap.querySelector('.captcha-input');
                formData.append('captcha_answer', captchaInput2.value.trim());
                formData.append('captcha_num1', document.getElementById('otpCaptchaNum1').value);
                formData.append('captcha_num2', document.getElementById('otpCaptchaNum2').value);
            }

            otpSendBtn.disabled = true;
            otpSendBtn.textContent = '⏳ در حال ارسال...';

            fetch(ezlens_frontend.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                otpSendBtn.disabled = false;
                otpSendBtn.textContent = 'ارسال کد تأیید';

                if (data.success) {
                    showMessage('✅ ' + data.data.message, 'success', 'minAuthMessage');
                    storedMobile = mobile;
                    otpVerifySection.style.display = 'block';
                    if (otpCode) {
                        otpCode.value = '';
                        otpCode.focus();
                    }
                    var expiryMinutes = parseInt(ezlens_frontend.otp_expiry) || 2;
                    startOtpTimer(expiryMinutes * 60, 'otpTimer');
                    if (otpResendBtn) {
                        otpResendBtn.disabled = true;
                        otpResendBtn.style.opacity = '0.5';
                        otpResendBtn.style.cursor = 'not-allowed';
                    }
                    if (otpVerifyBtn) {
                        otpVerifyBtn.disabled = false;
                        otpVerifyBtn.style.opacity = '1';
                    }
                } else {
                    showMessage('❌ ' + (data.data.message || 'خطا رخ داد.'), 'error', 'minAuthMessage');
                    refreshAllCaptchas();
                }
            })
            .catch(function() {
                otpSendBtn.disabled = false;
                otpSendBtn.textContent = 'ارسال کد تأیید';
                showMessage('❌ خطا در ارتباط با سرور.', 'error', 'minAuthMessage');
            });
        });

        if (otpVerifyBtn) {
            otpVerifyBtn.addEventListener('click', function() {
                var code = otpCode ? otpCode.value.trim() : '';
                if (code.length !== 6 || !/^\d{6}$/.test(code)) {
                    showMessage('❌ کد باید ۶ رقم باشد.', 'error', 'minAuthMessage');
                    return;
                }

                otpVerifyBtn.disabled = true;
                otpVerifyBtn.textContent = '⏳ در حال تأیید...';

                var formData = new FormData();
                formData.append('action', 'ezlens_otp_verify');
                formData.append('security', document.querySelector('#otpForm input[name="security"]').value);
                formData.append('mobile', storedMobile);
                formData.append('code', code);

                fetch(ezlens_frontend.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    otpVerifyBtn.disabled = false;
                    otpVerifyBtn.textContent = '✅ تأیید و ورود';

                    if (data.success) {
                        showMessage('✅ ' + data.data.message, 'success', 'minAuthMessage');
                        stopOtpTimer();
                        if (data.data.redirect) {
                            setTimeout(function() {
                                window.location.href = data.data.redirect;
                            }, 1000);
                        }
                    } else {
                        showMessage('❌ ' + (data.data.message || 'خطا رخ داد.'), 'error', 'minAuthMessage');
                    }
                })
                .catch(function() {
                    otpVerifyBtn.disabled = false;
                    otpVerifyBtn.textContent = '✅ تأیید و ورود';
                    showMessage('❌ خطا در ارتباط با سرور.', 'error', 'minAuthMessage');
                });
            });
        }

        if (otpResendBtn) {
            otpResendBtn.addEventListener('click', function() {
                if (this.disabled) return;
                if (otpSendBtn) otpSendBtn.click();
            });
        }
    }

    // ============================================================
    // ۵. مدیریت تب‌ها (ورود مشتری)
    // ============================================================
    var tabWrappers = document.querySelectorAll('.min-auth-tabs');
    tabWrappers.forEach(function(wrapper) {
        var tabs = wrapper.querySelectorAll('.min-auth-tab');
        var forms = wrapper.closest('.min-auth-wrapper').querySelectorAll('.min-auth-form');

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var target = this.getAttribute('data-target');
                tabs.forEach(function(t) { t.classList.remove('active'); });
                forms.forEach(function(f) { f.classList.remove('active'); });
                this.classList.add('active');
                forms.forEach(function(f) {
                    if (f.id === target + 'Form') f.classList.add('active');
                });
                var msgBox = document.getElementById('minAuthMessage');
                if (msgBox) {
                    msgBox.style.display = 'none';
                    msgBox.className = 'min-auth-message';
                    msgBox.textContent = '';
                }
                refreshAllCaptchas();
                stopOtpTimer();
                var ovs = document.getElementById('otpVerifySection');
                if (ovs) ovs.style.display = 'none';
                if (otpSendBtn) {
                    otpSendBtn.disabled = false;
                    otpSendBtn.textContent = 'ارسال کد تأیید';
                }
            });
        });
    });

    // ============================================================
    // ۶. رفرش کپچا (دکمه‌ها)
    // ============================================================
    document.querySelectorAll('.min-auth-captcha .captcha-reload, .admin-auth-captcha .captcha-reload, .lp-captcha .reload').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var container = this.closest('.min-auth-captcha, .admin-auth-captcha, .lp-captcha');
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
    // ۷. چشم نمایش رمز عبور
    // ============================================================
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var wrapper = this.closest('.password-wrap, .admin-auth-field .password-wrap, .admin-auth-field, .min-auth-input-group');
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
    // ۸. فراموشی رمز مشتری (اورلی)
    // ============================================================
    var forgotOverlay = document.getElementById('forgotOverlay');
    if (forgotOverlay) {
        var showForgotBtn = document.getElementById('showForgot');
        var closeForgotBtn = document.getElementById('closeForgot');
        var backToLoginBtn = document.getElementById('backToLogin');
        var forgotTabs = forgotOverlay.querySelectorAll('.min-auth-forgot-tab');
        var forgotForms = forgotOverlay.querySelectorAll('.min-auth-forgot-form');

        if (showForgotBtn) {
            showForgotBtn.addEventListener('click', function() {
                forgotOverlay.classList.add('active');
                forgotTabs.forEach(function(t) { t.classList.remove('active'); });
                forgotForms.forEach(function(f) { f.classList.remove('active'); });
                if (forgotTabs.length) forgotTabs[0].classList.add('active');
                if (forgotForms.length) forgotForms[0].classList.add('active');
                forgotOverlay.querySelectorAll('.forgot-msg').forEach(function(el) {
                    el.style.display = 'none';
                    el.textContent = '';
                });
            });
        }

        if (closeForgotBtn) {
            closeForgotBtn.addEventListener('click', function() {
                forgotOverlay.classList.remove('active');
            });
        }

        if (backToLoginBtn) {
            backToLoginBtn.addEventListener('click', function() {
                forgotOverlay.classList.remove('active');
            });
        }

        forgotOverlay.addEventListener('click', function(e) {
            if (e.target === this) forgotOverlay.classList.remove('active');
        });

        forgotTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var target = this.getAttribute('data-target');
                forgotTabs.forEach(function(t) { t.classList.remove('active'); });
                forgotForms.forEach(function(f) { f.classList.remove('active'); });
                this.classList.add('active');
                forgotForms.forEach(function(f) {
                    if (f.id === target + 'Form') f.classList.add('active');
                });
                forgotOverlay.querySelectorAll('.forgot-msg').forEach(function(el) {
                    el.style.display = 'none';
                    el.textContent = '';
                });
            });
        });

        // ارسال فرم‌های فراموشی با AJAX
        var forgotSmsForm = document.getElementById('forgotSmsForm');
        var forgotEmailForm = document.getElementById('forgotEmailForm');

        function handleForgotSubmit(e) {
            e.preventDefault();
            var form = e.target;
            var btn = form.querySelector('.min-auth-forgot-btn');
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

        if (forgotSmsForm) forgotSmsForm.addEventListener('submit', handleForgotSubmit);
        if (forgotEmailForm) forgotEmailForm.addEventListener('submit', handleForgotSubmit);
    }

    // ============================================================
    // ۹. مقداردهی اولیه
    // ============================================================
    refreshAllCaptchas();

    console.log('✅ EzLens Auth Scripts Loaded (auth.js)');
})(jQuery);