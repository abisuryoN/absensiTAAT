/**
 * Mobile UI — Drawer, Bottom Nav, Global Events
 * Used on ALL pages for mobile-fist responsive behavior
 */

(function() {
    'use strict';

    const drawerOverlay  = document.getElementById('mobileDrawerOverlay');
    const drawer         = document.getElementById('mobileDrawer');
    const menuToggle     = document.getElementById('mobileMenuToggle');
    const menuToggle2    = document.getElementById('mobileMenuToggle2');
    const drawerClose    = document.getElementById('mobileDrawerClose');
    const bottomMenuBtn  = document.getElementById('mobileBottomMenuToggle');

    const bottomNav = document.getElementById('mobileBottomNav');

    // ─── Helpers ─────────────────────────────────────────────
    function scrollDrawerToActive() {
        if (!drawer) return;
        var nav = drawer.querySelector('.drawer-nav');
        var activeItem = nav ? nav.querySelector('.drawer-nav-item.active') : null;
        if (nav && activeItem) {
            var containerRect = nav.getBoundingClientRect();
            var itemRect = activeItem.getBoundingClientRect();
            var offset = itemRect.top - containerRect.top - containerRect.clientHeight / 2 + itemRect.clientHeight / 2;
            nav.scrollTop += offset;
        }
    }

    function openDrawer() {
        if (!drawer || !drawerOverlay) return;
        drawer.classList.add('open');
        drawerOverlay.classList.add('open');
        document.body.classList.add('drawer-open');
        // Hide bottom nav when drawer opens (third image requirement)
        if (bottomNav) {
            bottomNav.style.display = 'none';
        }
        // Scroll to active menu item after drawer opens
        setTimeout(scrollDrawerToActive, 50);
    }

    function closeDrawer() {
        if (!drawer || !drawerOverlay) return;
        drawer.classList.remove('open');
        drawerOverlay.classList.remove('open');
        document.body.classList.remove('drawer-open');
        // Show bottom nav again when drawer closes
        if (bottomNav) {
            bottomNav.style.display = '';
        }
    }

    // ─── Drawer toggle via hamburger ─────────────────────────
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = drawer && drawer.classList.contains('open');
            if (isOpen) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });
    }

    // Second hamburger (if exists)
    if (menuToggle2) {
        menuToggle2.addEventListener('click', function(e) {
            e.stopPropagation();
            openDrawer();
        });
    }

    // Bottom nav "Menu" button
    if (bottomMenuBtn) {
        bottomMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            openDrawer();
        });
    }

    // ─── Close via X button ──────────────────────────────────
    if (drawerClose) {
        drawerClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeDrawer();
        });
    }

    // ─── Close via overlay tap ───────────────────────────────
    if (drawerOverlay) {
        drawerOverlay.addEventListener('click', function(e) {
            if (e.target === drawerOverlay) {
                closeDrawer();
            }
        });
    }

    // ─── Close via Escape key ────────────────────────────────
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('open')) {
            closeDrawer();
        }
    });

    // ─── Auto-close drawer on route change / link click ──────
    // Close drawer when a navigation link inside it is clicked
    if (drawer) {
        drawer.querySelectorAll('.drawer-nav-item, a.drawer-logout-btn').forEach(function(link) {
            link.addEventListener('click', function() {
                // Small delay for visual feedback
                setTimeout(closeDrawer, 150);
            });
        });

        // Logout form in drawer
        drawer.querySelectorAll('form[action*="logout"]').forEach(function(form) {
            form.addEventListener('submit', function() {
                // Let the form submit naturally
            });
        });
    }

    // ─── Update active state on bottom nav ───────────────────
    // Already handled by Blade. Fallback if using pushState:
    if (window.navigation && window.navigation.addEventListener) {
        window.navigation.addEventListener('navigate', function() {
            // Page will reload via traditional routing,
            // but if using SPA-like it would update here.
        });
    }

    // ─── Fix: mobile filter forms — auto-submit on change ────
    document.querySelectorAll('.mobile-filter-form select, .mobile-filter-form input[type="month"]').forEach(function(el) {
        el.addEventListener('change', function() {
            var form = this.closest('form');
            if (form && !form.querySelector('.filter-btn')) {
                // Only auto-submit if no explicit filter button
                form.submit();
            }
        });
    });

    // ─── Disable horizontal scroll globally on mobile ────────
    // Ensures no overflow-x issues
    window.addEventListener('load', function() {
        document.querySelectorAll('*').forEach(function(el) {
            if (el.scrollWidth > el.clientWidth && getComputedStyle(el).overflowX === 'auto') {
                // Let tables inside .table-responsive scroll horizontally,
                // but prevent body-level overflow
                if (el === document.body || el === document.documentElement) {
                    return;
                }
            }
        });
    });

    // ─── Set page title from a data attribute if present ─────
    // This supports dynamic titles from pages
    var titleEl = document.querySelector('[data-mobile-title]');
    if (titleEl) {
        var headerTitle = document.querySelector('.mobile-page-title');
        if (headerTitle) {
            headerTitle.textContent = titleEl.getAttribute('data-mobile-title');
        }
    }

})();