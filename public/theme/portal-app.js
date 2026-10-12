/**
 * JS portail — extrait de Vuesy app.js, sans panneau latéral / i18n / preloader.
 */
(function () {
    'use strict';

    function initHoriMenuActive() {
        document.querySelectorAll('.navbar-nav a').forEach(function (item) {
            var pageUrl = window.location.href.split(/[?#]/)[0];
            if (item.href !== pageUrl) {
                return;
            }

            item.classList.add('active');

            var parent = item.parentElement;
            if (!parent) {
                return;
            }

            parent.classList.add('active');

            var parent2 = parent.parentElement;
            if (parent2) {
                parent2.classList.add('active');
            }

            var parent3 = parent2 && parent2.parentElement;
            if (parent3) {
                parent3.classList.add('active');
                var parent4 = parent3.parentElement;
                if (parent4 && parent4.closest('li')) {
                    parent4.closest('li').classList.add('active');
                }
            }
        });
    }

    function initDropdownMenu() {
        var container = document.getElementById('topnav-menu-content');
        if (!container) {
            return;
        }

        container.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function (event) {
                if (event.target.getAttribute('href') === '#') {
                    event.target.parentElement.classList.toggle('active');
                    event.target.nextElementSibling.classList.toggle('show');
                }
            });
        });

        window.addEventListener('resize', function () {
            container.querySelectorAll('a').forEach(function (link) {
                if (link.parentElement.classList.contains('nav-item') &&
                    link.parentElement.classList.contains('dropdown') &&
                    link.parentElement.classList.contains('active')) {
                    link.parentElement.classList.remove('active');
                    if (link.nextElementSibling) {
                        link.nextElementSibling.classList.remove('show');
                    }
                }
            });
        });
    }

    function initComponents() {
        if (typeof bootstrap === 'undefined') {
            return;
        }

        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(function (el) {
            new bootstrap.Tooltip(el);
        });

        [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]')).forEach(function (el) {
            new bootstrap.Popover(el);
        });

        [].slice.call(document.querySelectorAll('.toast')).forEach(function (el) {
            new bootstrap.Toast(el);
        });
    }

    function initHorizontalMenu() {
        var horimenuBtn = document.getElementById('horimenu-btn');
        var topnav = document.getElementById('topnav-menu-content');
        var overlay = document.querySelector('.hori-overlay');

        if (horimenuBtn) {
            horimenuBtn.addEventListener('click', function () {
                document.body.classList.toggle('horimenu-enabled');
            });
        }

        if (topnav && typeof bootstrap !== 'undefined') {
            var collapse = new bootstrap.Collapse(topnav, { toggle: false });

            if (overlay) {
                overlay.addEventListener('click', function () {
                    collapse.hide();
                    document.body.classList.remove('horimenu-enabled');
                });
            }

            window.addEventListener('resize', function () {
                collapse.hide();
                if (window.innerWidth < 991) {
                    document.body.classList.remove('horimenu-enabled');
                }
            });
        }

    }

    function initCheckAll() {
        var checkAll = document.getElementById('checkAll');
        if (!checkAll) {
            return;
        }

        checkAll.addEventListener('click', function () {
            document.querySelectorAll('.table-check input[type="checkbox"]').forEach(function (checkbox) {
                checkbox.checked = checkAll.checked;
            });
        });
    }

    function init() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        initHoriMenuActive();
        initDropdownMenu();
        initComponents();
        initHorizontalMenu();
        initCheckAll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
