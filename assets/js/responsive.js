/**
 * Hungry Food - Responsive UI Enhancements
 * Mobile-first responsive functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    initNavbarScroll();
    initMobileMenu();
    initScrollAnimations();
    initTouchFeedback();
    initFloatingButtons();
    initSmoothScroll();
    initAutoHideAlerts();
});

/**
 * Navbar scroll effect
 */
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }, { passive: true });
}

/**
 * Mobile menu enhancements — slide-out drawer with backdrop
 */
function initMobileMenu() {
    var navbarToggler = document.querySelector('.navbar-toggler');
    var navbarCollapse = document.querySelector('.navbar-collapse');
    var backdrop = document.getElementById('mobileNavBackdrop');

    if (!navbarToggler || !navbarCollapse) return;

    // Toggle backdrop visibility with menu
    navbarCollapse.addEventListener('show.bs.collapse', function() {
        if (backdrop && window.innerWidth < 992) {
            backdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    });

    navbarCollapse.addEventListener('hide.bs.collapse', function() {
        if (backdrop) {
            backdrop.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Close menu when clicking backdrop
    if (backdrop) {
        backdrop.addEventListener('click', function() {
            var bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
            if (bsCollapse) bsCollapse.hide();
        });
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        var isClickInside = navbarCollapse.contains(e.target) || navbarToggler.contains(e.target);
        if (!isClickInside && navbarCollapse.classList.contains('show')) {
            var bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
            if (bsCollapse) bsCollapse.hide();
        }
    });

    // Close menu when clicking a nav link (mobile)
    var navLinks = navbarCollapse.querySelectorAll('.nav-link');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                var bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) bsCollapse.hide();
            }
        });
    });

    // Reset body overflow on resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            document.body.style.overflow = '';
            if (backdrop) backdrop.classList.remove('active');
        }
    });
}

/**
 * Scroll animations using Intersection Observer
 */
function initScrollAnimations() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    
    const animatedElements = document.querySelectorAll(
        '.category-card, .step-card, .menu-card, .featured-item-card, .testimonial-card, .animate-on-scroll'
    );
    
    if (!animatedElements.length) return;
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(function(el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

/**
 * Touch feedback for mobile devices
 */
function initTouchFeedback() {
    const touchElements = document.querySelectorAll('.btn, .card, .category-card, .nav-link');
    
    touchElements.forEach(function(el) {
        el.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        }, { passive: true });
        
        el.addEventListener('touchend', function() {
            this.classList.remove('touch-active');
        }, { passive: true });
        
        el.addEventListener('touchcancel', function() {
            this.classList.remove('touch-active');
        }, { passive: true });
    });
}

/**
 * Floating buttons behavior
 */
function initFloatingButtons() {
    const backToTop = document.getElementById('backToTop');
    
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        }, { passive: true });
        
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
}

/**
 * Smooth scroll for anchor links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const offsetTop = targetElement.offsetTop - 80;
                window.scrollTo({ top: offsetTop, behavior: 'smooth' });
            }
        });
    });
}

/**
 * Auto-hide alerts after timeout
 */
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            } else {
                alert.style.opacity = '0';
                setTimeout(function() { alert.style.display = 'none'; }, 300);
            }
        }, 5000);
    });
}

/**
 * Loading state for buttons
 */
function setButtonLoading(button, isLoading) {
    if (isLoading) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
        button.disabled = true;
    } else {
        button.innerHTML = button.dataset.originalText;
        button.disabled = false;
    }
}

/**
 * Fix iOS input zoom prevention
 */
if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
    document.querySelectorAll('input, select, textarea').forEach(function(input) {
        if (!input.style.fontSize || parseInt(input.style.fontSize) < 16) {
            input.style.fontSize = '16px';
        }
    });
}

console.log('Hungry Food - Responsive UI loaded successfully');

