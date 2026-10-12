(function () {
    'use strict';

    document.querySelectorAll('[data-copy], [data-copy-value]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var copyTarget = btn.getAttribute('data-copy');
            var text = btn.getAttribute('data-copy-value') || '';
            if (!text && copyTarget) {
                var target = document.querySelector(copyTarget);
                text = target ? (target.textContent || target.value || '') : '';
            }
            if (!text) return;

            navigator.clipboard.writeText(text.trim()).then(function () {
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-check"></i> Copié';
                setTimeout(function () {
                    btn.innerHTML = original;
                }, 2000);
            });
        });
    });

    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (!input) return;

            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.querySelector('i').className = isPassword ? 'bx bx-hide' : 'bx bx-show';
        });
    });
})();
