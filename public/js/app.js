/**
 * Anglish — interacciones de interfaz (JavaScript nativo).
 *
 * No se usa jQuery: Bootstrap 5 ya no lo necesita. Todo se ejecuta después
 * de cargar el DOM y con mejora progresiva (si el JS falla, la app sigue
 * funcionando).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initSidebar();
        initPasswordToggles();
        initFlashAlerts();
    });

    function initSidebar() {
        var toggle = document.getElementById('sidebarToggle');
        var sidebar = document.getElementById('appSidebar');
        var backdrop = document.getElementById('sidebarBackdrop');

        if (!toggle || !sidebar) {
            return;
        }

        function setOpen(open) {
            document.body.classList.toggle('sidebar-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        }

        toggle.addEventListener('click', function () {
            setOpen(!document.body.classList.contains('sidebar-open'));
        });

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                setOpen(false);
            });
        }

        sidebar.querySelectorAll('.nav-link:not(.disabled)').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) {
                    setOpen(false);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
                setOpen(false);
                toggle.focus();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                setOpen(false);
            }
        });
    }

    function initPasswordToggles() {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            var inputId = button.getAttribute('data-password-toggle');
            var input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            button.addEventListener('click', function () {
                var visible = input.getAttribute('type') === 'text';
                input.setAttribute('type', visible ? 'password' : 'text');
                button.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');

                var icon = button.querySelector('i');
                if (icon) {
                    icon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
                }

                input.focus();
            });
        });
    }

    function initFlashAlerts() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Alert) {
            return;
        }

        window.setTimeout(function () {
            document.querySelectorAll('.app-alert').forEach(function (alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 7000);
    }
})();
