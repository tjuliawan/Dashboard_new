/**
 * ==========================================
 * RESPONSIVE UTILITIES & MOBILE INTERACTIONS
 * ==========================================
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ──────────────────────────────────────────
    // MOBILE SIDEBAR TOGGLE
    // ──────────────────────────────────────────
    const iconSidenav = document.getElementById('iconSidenav');
    const sidenavMain = document.getElementById('sidenav-main');
    const navbarBlur = document.getElementById('navbarBlur');
    
    if (iconSidenav && sidenavMain) {
        iconSidenav.addEventListener('click', function() {
            sidenavMain.classList.toggle('show');
            document.body.classList.toggle('sidenav-open');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            const isClickInsideSidebar = sidenavMain.contains(e.target);
            const isClickOnToggle = iconSidenav.contains(e.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle && sidenavMain.classList.contains('show')) {
                sidenavMain.classList.remove('show');
                document.body.classList.remove('sidenav-open');
            }
        });
    }
    
    // Close sidebar when clicking on menu item
    const menuItems = document.querySelectorAll('.sidenav .nav-link');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth < 1200) {
                sidenavMain.classList.remove('show');
                document.body.classList.remove('sidenav-open');
            }
        });
    });
    
    // ──────────────────────────────────────────
    // RESPONSIVE NAVBAR MENU TOGGLE
    // ──────────────────────────────────────────
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            this.classList.toggle('collapsed');
            if (navbarCollapse) {
                navbarCollapse.classList.toggle('show');
            }
        });
    }
    
    // ──────────────────────────────────────────
    // RESPONSIVE TABLE WRAPPER
    // ──────────────────────────────────────────
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    // ──────────────────────────────────────────
    // TOUCH-FRIENDLY INTERACTIONS
    // ──────────────────────────────────────────
    
    // Add touch support for dropdowns
    const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('touchstart', function(e) {
            const dropdown = this.nextElementSibling;
            if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // ──────────────────────────────────────────
    // HANDLE RESIZE FOR RESPONSIVE BEHAVIOR
    // ──────────────────────────────────────────
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            handleResponsiveResize();
        }, 100);
    });
    
    function handleResponsiveResize() {
        const width = window.innerWidth;
        
        if (width >= 1200) {
            // Desktop: close sidebar if it was open
            if (sidenavMain && sidenavMain.classList.contains('show')) {
                sidenavMain.classList.remove('show');
                document.body.classList.remove('sidenav-open');
            }
        }

        applyQuickAccessViewportClass();
    }

    function applyQuickAccessViewportClass() {
        const vw = Math.round(window.innerWidth);
        const vh = Math.round(window.innerHeight);
        const vvW = window.visualViewport ? Math.round(window.visualViewport.width) : vw;
        const vvH = window.visualViewport ? Math.round(window.visualViewport.height) : vh;
        const ua = navigator.userAgent || '';
        const isAndroid = /Android/i.test(ua);

        const matchInner = (vw >= 607 && vw <= 609) && (vh >= 684 && vh <= 688);
        const matchVisual = (vvW >= 607 && vvW <= 609) && (vvH >= 684 && vvH <= 688);
        const androidInner = isAndroid && (vw >= 390 && vw <= 430) && (vh >= 760 && vh <= 980) && (vh > vw);
        const androidVisual = isAndroid && (vvW >= 390 && vvW <= 430) && (vvH >= 760 && vvH <= 980) && (vvH > vvW);

        document.body.classList.toggle('qa-608x686', matchInner || matchVisual);
        document.body.classList.toggle('qa-android-2x4', androidInner || androidVisual);
    }
    
    // ──────────────────────────────────────────
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ──────────────────────────────────────────
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                const target = document.querySelector(href);
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // ──────────────────────────────────────────
    // PREVENT BODY SCROLL WHEN SIDEBAR OPEN
    // ──────────────────────────────────────────
    if (sidenavMain) {
        const observer = new MutationObserver(function() {
            if (sidenavMain.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
        
        observer.observe(sidenavMain, { attributes: true, attributeFilter: ['class'] });
    }
    
    // ──────────────────────────────────────────
    // DEBOUNCE FOR SCROLL EVENTS
    // ──────────────────────────────────────────
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // ──────────────────────────────────────────
    // MODAL RESPONSIVE BEHAVIOR
    // ──────────────────────────────────────────
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            if (window.innerWidth < 576) {
                this.classList.add('modal-fullscreen-sm-down');
            }
        });
    });
    
    // ──────────────────────────────────────────
    // DETECT TOUCH DEVICE
    // ──────────────────────────────────────────
    function isTouchDevice() {
        return (('ontouchstart' in window) ||
                (navigator.maxTouchPoints > 0) ||
                (navigator.msMaxTouchPoints > 0));
    }
    
    if (isTouchDevice()) {
        document.body.classList.add('touch-device');
    }
    
    // ──────────────────────────────────────────
    // HIDE NAVBAR ON SCROLL DOWN, SHOW ON SCROLL UP
    // ──────────────────────────────────────────
    let lastScrollTop = 0;
    const navbar = document.querySelector('.navbar-main');
    
    if (navbar) {
        window.addEventListener('scroll', debounce(function() {
            let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
            
            if (currentScroll > 100) {
                if (currentScroll > lastScrollTop) {
                    // SCROLLING DOWN
                    navbar.style.transform = 'translateY(-100%)';
                    navbar.style.transition = 'transform 0.3s ease-in-out';
                } else {
                    // SCROLLING UP
                    navbar.style.transform = 'translateY(0)';
                    navbar.style.transition = 'transform 0.3s ease-in-out';
                }
            }
            lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
        }, 100));
    }

    applyQuickAccessViewportClass();
});

// ──────────────────────────────────────────
// ORIENTATION CHANGE HANDLER
// ──────────────────────────────────────────
window.addEventListener('orientationchange', function() {
    // Adjust layout on orientation change
    setTimeout(function() {
        window.dispatchEvent(new Event('resize'));
    }, 100);
});

// ──────────────────────────────────────────
// HANDLE FORM FOCUS ON MOBILE
// ──────────────────────────────────────────
document.addEventListener('focusin', function(e) {
    if ((e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT')) {
        if (window.innerWidth < 768) {
            e.target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
