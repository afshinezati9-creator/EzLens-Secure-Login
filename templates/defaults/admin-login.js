// ============================================================
// تعاملات پیش‌فرض ورود مدیر (ایزوله با IIFE)
// ============================================================
(function() {
    // رفرش کپچا
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
        });
    });

    // شبیه‌سازی ارسال فرم
    document.querySelector('.admin-auth-card form').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = this.querySelector('.admin-auth-btn');
        var orig = btn.textContent;
        btn.textContent = '⏳ در حال ورود...';
        btn.style.opacity = '0.7';
        setTimeout(function() {
            btn.textContent = '✅ ورود موفق!';
            btn.style.background = '#38a169';
            setTimeout(function() {
                btn.textContent = orig;
                btn.style.background = '';
                btn.style.opacity = '1';
                alert('🚀 به پنل مدیریت خوش آمدید!');
            }, 1200);
        }, 600);
    });

    console.log('✅ صفحه ورود مدیر بارگذاری شد.');
})();