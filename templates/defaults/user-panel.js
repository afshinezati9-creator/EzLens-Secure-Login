// ============================================================
// تعاملات پیش‌فرض پنل کاربری (ایزوله با IIFE)
// ============================================================
(function() {
    var root = document.querySelector('.ezu');
    if (!root) return;

    var tabs = root.querySelectorAll('.ezu-tab');
    var navBtns = root.querySelectorAll('.ezu-nav-d button[data-tab], .ezu-bottom-nav button[data-tab]');

    function openTab(tabId) {
        tabs.forEach(function(t) { t.classList.remove('active'); });
        tabs.forEach(function(t) {
            if (t.id === 'tab-' + tabId) t.classList.add('active');
        });
        navBtns.forEach(function(b) { b.classList.remove('active'); });
        navBtns.forEach(function(b) {
            if (b.dataset.tab === tabId) b.classList.add('active');
        });
    }

    navBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            openTab(this.dataset.tab);
        });
    });

    root.querySelectorAll('.ezu-quick a[data-go]').forEach(function(a) {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            openTab(this.dataset.go);
        });
    });

    console.log('✅ پنل کاربری بارگذاری شد.');
})();