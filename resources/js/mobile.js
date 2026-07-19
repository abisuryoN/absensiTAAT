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

    // ─── Scrollbar width compensation ─────────────────────────
    // Cache scrollbar width once on load to ensure consistent compensation
    // across multiple open/close cycles
    var cachedScrollbarWidth = null;

    function getScrollbarWidth() {
        if (cachedScrollbarWidth !== null) {
            return cachedScrollbarWidth;
        }
        cachedScrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        // Guard: clamp to 0 if negative
        if (cachedScrollbarWidth < 0) {
            cachedScrollbarWidth = 0;
        }
        return cachedScrollbarWidth;
    }

    function openDrawer() {
        if (!drawer || !drawerOverlay) return;
        // Compensate for scrollbar disappearing to prevent layout shift
        // Use cached scrollbar width so the padding is always the same
        // every time the drawer opens
        var scrollbarWidth = getScrollbarWidth();
        if (scrollbarWidth > 0) {
            document.body.style.paddingRight = scrollbarWidth + 'px';
        }

        // Reset drawer scroll to top so it always opens from the beginning,
        // no auto-scrolling / jumping to active item
        var nav = drawer.querySelector('.drawer-nav');
        if (nav) {
            nav.scrollTop = 0;
        }

        drawer.classList.add('open');
        drawerOverlay.classList.add('open');
        document.body.classList.add('drawer-open');
        // Hide bottom nav when drawer opens
        if (bottomNav) {
            bottomNav.style.display = 'none';
        }
    }

    function closeDrawer() {
        if (!drawer || !drawerOverlay) return;
        drawer.classList.remove('open');
        drawerOverlay.classList.remove('open');
        document.body.classList.remove('drawer-open');
        // Restore padding compensation
        document.body.style.paddingRight = '';
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