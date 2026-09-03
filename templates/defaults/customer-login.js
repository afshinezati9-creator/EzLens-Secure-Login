// ============================================================
// تعاملات پیش‌فرض ورود مشتری (ایزوله با IIFE)
// ============================================================
(function() {
    // تب‌ها
    var tabs = document.querySelectorAll('.min-auth-tab');
    var forms = document.querySelectorAll('.min-auth-form');

    function switchTab(target) {
        tabs.forEach(function(t) { t.classList.remove('active'); });
        forms.forEach(function(f) { f.classList.remove('active'); });
        tabs.forEach(function(t) {
            if (t.dataset.target === target) t.classList.add('active');
        });
        forms.forEach(function(f) {
            if (f.id === target + '-form') f.classList.add('active');
        });
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            switchTab(this.dataset.target);
        });
    });

    // رفرش کپچا
    document.querySelectorAll('.min-auth-captcha .captcha-reload').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var container = this.closest('.min-auth-captcha');
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
    document.querySelectorAll('.min-auth-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = this.querySelector('.min-auth-btn');
            var orig = btn.textContent;
            btn.textContent = '⏳ در حال پردازش...';
            btn.style.opacity = '0.7';
            setTimeout(function() {
                btn.textContent = '✅ موفقیت!';
                btn.style.background = '#38a169';
                setTimeout(function() {
                    btn.textContent = orig;
                    btn.style.background = '';
                    btn.style.opacity = '1';
                }, 1200);
            }, 600);
        });
    });

    console.log('✅ صفحه ورود مشتری بارگذاری شد.');
})();