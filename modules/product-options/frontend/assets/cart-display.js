(function () {
  'use strict';

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.ezlens-cart-btn');
    if (btn) {
      e.preventDefault();
      var id = btn.getAttribute('data-panel');
      var panel = id ? document.getElementById(id) : null;
      if (!panel) return;
      var open = panel.hasAttribute('hidden');
      // close others in same cart line
      var root = btn.closest('.ezlens-cart-opts');
      if (root) {
        root.querySelectorAll('.ezlens-cart-panel').forEach(function (p) {
          p.setAttribute('hidden', 'hidden');
        });
      }
      if (open) {
        panel.removeAttribute('hidden');
      }
      return;
    }

    if (e.target.closest('.ezlens-cart-panel-close')) {
      e.preventDefault();
      var p = e.target.closest('.ezlens-cart-panel');
      if (p) p.setAttribute('hidden', 'hidden');
      return;
    }

    var acc = e.target.closest('.ezlens-cart-acc-toggle');
    if (acc) {
      e.preventDefault();
      var expanded = acc.getAttribute('aria-expanded') === 'true';
      var tid = acc.getAttribute('data-target');
      var body = tid ? document.getElementById(tid) : acc.nextElementSibling;
      acc.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      if (body) {
        if (expanded) body.setAttribute('hidden', 'hidden');
        else body.removeAttribute('hidden');
      }
    }
  });
})();
