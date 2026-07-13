import './bootstrap';

(function () {
    'use strict';

    const themeToggle = document.getElementById('themeToggle');
    const rootElement = document.documentElement;
    const themeStorageKey = 'pho_theme_preference';
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarStorageKey = 'pho_sidebar_collapsed';
    const DESKTOP_BREAKPOINT = 1024;

    function isMobile() {
        return window.matchMedia('(max-width: ' + (DESKTOP_BREAKPOINT - 1) + 'px)').matches;
    }

    function applyTheme(theme) {
        rootElement.setAttribute('data-theme', theme);
        if (themeToggle) {
            themeToggle.setAttribute('aria-pressed', String(theme === 'dark'));
            themeToggle.setAttribute('aria-label', theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme');
        }
        localStorage.setItem(themeStorageKey, theme);
    }

    function initTheme() {
        const storedTheme = localStorage.getItem(themeStorageKey);
        const initialTheme = storedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(initialTheme);

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const nextTheme = rootElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                applyTheme(nextTheme);
            });
        }
    }

    function setSidebarExpanded(expanded) {
        if (!sidebar) return;

        if (isMobile()) {
            sidebar.classList.toggle('is-open', expanded);
            sidebar.classList.toggle('is-collapsed', !expanded);
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('is-open', expanded);
                sidebarOverlay.setAttribute('aria-hidden', expanded ? 'false' : 'true');
            }
        } else {
            sidebar.classList.toggle('is-collapsed', !expanded);
            sidebar.classList.remove('is-open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('is-open');
                sidebarOverlay.setAttribute('aria-hidden', 'true');
            }
            localStorage.setItem(sidebarStorageKey, expanded ? 'false' : 'true');
        }

        if (sidebarToggle) {
            sidebarToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            sidebarToggle.setAttribute('aria-label', expanded ? 'Close navigation menu' : 'Open navigation menu');
        }
        if (sidebarCollapseBtn) {
            sidebarCollapseBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function initSidebar() {
        if (!sidebar) return;

        const saved = localStorage.getItem(sidebarStorageKey);
        const expanded = isMobile() ? false : saved !== 'true';
        setSidebarExpanded(expanded);

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                if (isMobile()) {
                    setSidebarExpanded(!sidebar.classList.contains('is-open'));
                } else {
                    setSidebarExpanded(sidebar.classList.contains('is-collapsed'));
                }
            });
        }

        if (sidebarCollapseBtn) {
            sidebarCollapseBtn.addEventListener('click', function () {
                if (isMobile()) return;
                setSidebarExpanded(sidebar.classList.contains('is-collapsed'));
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                setSidebarExpanded(false);
            });
        }

        window.addEventListener('resize', function () {
            if (isMobile()) {
                sidebar.classList.remove('is-collapsed');
                if (!sidebar.classList.contains('is-open')) {
                    setSidebarExpanded(false);
                }
            } else {
                sidebar.classList.remove('is-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('is-open');
                }
                const savedCollapsed = localStorage.getItem(sidebarStorageKey) === 'true';
                sidebar.classList.toggle('is-collapsed', savedCollapsed);
            }
        });
    }

    function initDropdowns() {
        document.querySelectorAll('[data-dropdown]').forEach(function (dropdown) {
            const trigger = dropdown.querySelector('[data-dropdown-trigger]');
            const panel = dropdown.querySelector('.topbar-dropdown-panel');
            if (!trigger || !panel) return;

            trigger.addEventListener('click', function (event) {
                event.stopPropagation();
                const isOpen = !panel.hasAttribute('hidden');

                document.querySelectorAll('.topbar-dropdown-panel').forEach(function (other) {
                    other.setAttribute('hidden', '');
                });
                document.querySelectorAll('[data-dropdown-trigger]').forEach(function (btn) {
                    btn.setAttribute('aria-expanded', 'false');
                });

                if (!isOpen) {
                    panel.removeAttribute('hidden');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.addEventListener('click', function () {
            document.querySelectorAll('.topbar-dropdown-panel').forEach(function (panel) {
                panel.setAttribute('hidden', '');
            });
            document.querySelectorAll('[data-dropdown-trigger]').forEach(function (btn) {
                btn.setAttribute('aria-expanded', 'false');
            });
        });

        document.querySelectorAll('.topbar-dropdown-panel').forEach(function (panel) {
            panel.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });
    }

    function initFlashDismiss() {
        document.querySelectorAll('.flash .alert').forEach(function (alert) {
            alert.setAttribute('role', 'status');
        });
    }

    function init() {
        initTheme();
        initSidebar();
        initDropdowns();
        initFlashDismiss();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
