// ============================================================
// تعاملات پیش‌فرض فراموشی رمز (ایزوله با IIFE)
// ============================================================
(function() {
    // رفرش کپچا
    document.querySelectorAll('.lp-captcha .reload').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var container = this.closest('.lp-captcha');
            var num1 = container.querySelector('.numbers span:first-child');
            var num2 = container.querySelector('.numbers span:nth-child(3)');
            var input = container.querySelector('.captcha-input');
            var n1 = Math.floor(Math.random() * 9) + 1;
            var n2 = Math.floor(Math.random() * 9) + 1;
            if (num1) num1.textContent = n1;
            if (num2) num2.textContent = n2;
            if (input) input.value = '';
        });
    });

    // شبیه‌سازی ارسال فرم
    document.querySelector('.lp-card form').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = this.querySelector('.lp-btn');
        var orig = btn.textContent;
        btn.textContent = '⏳ در حال ارسال...';
        btn.style.opacity = '0.7';
        setTimeout(function() {
            btn.textContent = '✅ لینک ارسال شد!';
            btn.style.background = '#38a169';
            setTimeout(function() {
                btn.textContent = orig;
                btn.style.background = '';
                btn.style.opacity = '1';
            }, 1500);
        }, 800);
    });

    console.log('✅ صفحه فراموشی رمز بارگذاری شد.');
})();