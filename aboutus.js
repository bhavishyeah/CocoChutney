// Mobile Navigation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const mobileNav = document.getElementById('mobile-nav');
    const closeMenu = document.getElementById('close-menu');
    const openMenu = document.getElementById('open-menu');
    
    // Check if elements exist to avoid errors
    if (!hamburgerMenu || !mobileNav || !closeMenu) {
        console.error('Mobile navigation elements not found');
        return;
    }

    // Open mobile menu function
    function openMobileMenu() {
        mobileNav.classList.add('open');
        document.body.style.overflow = 'hidden'; // Prevent body scrolling
        console.log('Mobile menu opened'); // Debug log
    }

    // Close mobile menu function
    function closeMobileMenu() {
        mobileNav.classList.remove('open');
        document.body.style.overflow = 'auto'; // Allow body scrolling
        console.log('Mobile menu closed'); // Debug log
    }

    // Event listeners
    hamburgerMenu.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Hamburger clicked'); // Debug log
        openMobileMenu();
    });

    closeMenu.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Close button clicked'); // Debug log
        closeMobileMenu();
    });

    // Close menu when clicking on navigation links
    const mobileNavLinks = document.querySelectorAll('#mobile-nav a');
    mobileNavLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            closeMobileMenu();
        });
    });

    // Close menu with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileNav.classList.contains('open')) {
            closeMobileMenu();
        }
    });

    // Close menu when window is resized to desktop size
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768 && mobileNav.classList.contains('open')) {
            closeMobileMenu();
        }
    });

    // Optional: Add scroll effect to navbar
    const nav = document.getElementById('nav');
    if (nav) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    }
});