document.addEventListener("DOMContentLoaded", function () {
  // Register GSAP plugin
  gsap.registerPlugin(ScrollTrigger);

  // Navigation menu items animation
  gsap.from('.nav-item', {
    duration: 0.5,
    opacity: 0,
    y: 20,
    stagger: 0.2,
    delay: 0.5,
    ease: 'power1.inOut',
  });

  // Hamburger Menu Toggle
  const openMenu = document.getElementById("open-menu");
  const closeMenu = document.getElementById("close-menu");
  const mobileNav = document.getElementById("mobile-nav");

  openMenu.addEventListener("click", () => {
    mobileNav.style.display = "flex";
  });

  closeMenu.addEventListener("click", () => {
    mobileNav.style.display = "none";
  });

  document.querySelectorAll("#mobile-nav a").forEach(link => {
    link.addEventListener("click", () => {
      mobileNav.style.display = "none";
    });
  });

 const crsr = document.querySelector("#cursor");
const blur = document.querySelector("#cursor-blur");

function isTouchDevice() {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
}

if (crsr && blur && !isTouchDevice()) {
    document.addEventListener("mousemove", function (e) {
        crsr.style.left = `${e.clientX}px`;
        crsr.style.top = `${e.clientY}px`;
        blur.style.left = `${e.clientX - 250}px`;
        blur.style.top = `${e.clientY - 250}px`;
    });

    document.querySelectorAll('a, button, .menu-item, #scroller h4').forEach(element => {
        element.addEventListener('mouseenter', () => {
            crsr.style.scale = 3;
            crsr.style.border = "1px solid #fff";
            crsr.style.backgroundColor = "transparent";
        });
        element.addEventListener('mouseleave', () => {
            crsr.style.scale = 1;
            crsr.style.border = "0px solid #ff9900";
            crsr.style.backgroundColor = "#ff9900";
        });
    });
} else {
    if (crsr) crsr.style.display = 'none';
    if (blur) blur.style.display = 'none';
}

  // Custom cursor on nav hover
  document.querySelectorAll("#nav h4").forEach(elem => {
    elem.addEventListener("mouseenter", () => {
      crsr.style.scale = 3;
      crsr.style.border = "1px solid #fff";
      crsr.style.backgroundColor = "transparent";
    });

    elem.addEventListener("mouseleave", () => {
      crsr.style.scale = 1;
      crsr.style.border = "0px solid #ff9900";
      crsr.style.backgroundColor = "#ff9900";
    });
  });

  // About Us Section - image reveal on scroll
  gsap.from(".about-img-left", {
    scrollTrigger: {
      trigger: "#about-us",
      start: "top 80%",
      toggleActions: "play none none none",
    },
    opacity: 0,
    x: -100,
    scale: 0.8,
    duration: 0.8,
    ease: "power2.out"
  });

  gsap.from(".about-img-right", {
    scrollTrigger: {
      trigger: "#about-us",
      start: "top 80%",
      toggleActions: "play none none none",
    },
    opacity: 0,
    x: 100,
    scale: 0.8,
    duration: 0.8,
    ease: "power2.out"
  });

  // Intersection Observer for fade-in
  const aboutUs = document.getElementById("about-us");

  new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        aboutUs.classList.add("active");
      }
    });
  }, { threshold: 0.2 }).observe(aboutUs);

  // Intro text animations
  gsap.from('.hero-text', {
    duration: 0.5,
    opacity: 0,
    y: 20,
    stagger: 0.2,
    delay: 1,
    ease: 'power1.inOut',
  });

  gsap.from('.scroller-item', {
    duration: 0.5,
    opacity: 0,
    y: 20,
    stagger: 0.2,
    delay: 1.5,
    ease: 'power1.inOut',
  });

  // Elements that animate in from left on scroll
  const scrollAnimations = [
    { selector: '.scroller-item', trigger: '.scroller' },
    { selector: '.card', trigger: '.cards-container' },
    { selector: '.testimonial-text', trigger: '.testimonial' },
    { selector: '.call-to-action-text', trigger: '.call-to-action' },
    { selector: '.footer-item', trigger: '.footer' },
    { selector: '#hero', trigger: '#hero', axis: 'y' },
    { selector: '#page1', trigger: '#page1', axis: 'y' },
    { selector: '#page2', trigger: '#page2', axis: 'y' },
    { selector: '#page3', trigger: '#page3', axis: 'y' },
    { selector: '#page4', trigger: '#page4', axis: 'y' },
  ];

  scrollAnimations.forEach(({ selector, trigger, axis = 'x' }) => {
    const animationProps = {
      duration: 1,
      ease: 'power1.inOut',
      scrollTrigger: {
        trigger: trigger,
        start: 'top 50%',
        end: 'bottom 50%',
        toggleActions: 'play none none reset',
      }
    };

    animationProps[axis] = 100;

    gsap.to(selector, animationProps);
  });
});
// Page 4 animation on scroll
gsap.to('#page4', {
  duration: 1,
  y: 100,
  ease: 'power1.inOut',
  scrollTrigger: {
    trigger: '#page4',
    start: 'top 50%',
    end: 'bottom 50%',
    toggleActions: 'play none none reset',
  },
}); function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = (Math.random() * 20 + 10) + 's';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.opacity = Math.random() * 0.5 + 0.1;
                particlesContainer.appendChild(particle);
            }
        }

        // Scroll reveal animation
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.scroll-reveal');
            
            reveals.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('revealed');
                }
            });
        }

        // About us activation
        function activateAboutUs() {
            const aboutUs = document.getElementById('about-us');
            const aboutUsTop = aboutUs.getBoundingClientRect().top;
            
            if (aboutUsTop < window.innerHeight - 100) {
                aboutUs.classList.add('active');
            }
        }

        // Enhanced mouse movement parallax
        document.addEventListener('mousemove', (e) => {
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            // Parallax effect for testimonial container
            const testimonial = document.querySelector('.testimonial-container');
            if (testimonial) {
                testimonial.style.transform = `translate(${mouseX * 10}px, ${mouseY * 10}px)`;
            }
            
            // Parallax effect for menu items
            const elems = document.querySelectorAll('.elem');
            elems.forEach((elem, index) => {
                const speed = (index % 2 === 0) ? 5 : -5;
                elem.style.transform = `translate(${mouseX * speed}px, ${mouseY * speed}px)`;
            });
        });

        // Smooth scrolling and enhanced interactions
        document.addEventListener('scroll', () => {
            revealOnScroll();
            activateAboutUs();
            
            // Parallax background effect
            const scrolled = window.pageYOffset;
            const page2 = document.getElementById('page2');
            const page3 = document.getElementById('page3');
            
            if (page2) {
                page2.style.transform = `translateY(${scrolled * 0.1}px)`;
            }
            if (page3) {
                page3.style.transform = `translateY(${scrolled * 0.05}px)`;
            }
        });

        // Enhanced hover effects for menu items
        document.querySelectorAll('.elem').forEach(elem => {
            elem.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.03) rotateX(5deg)';
                this.style.boxShadow = '0 25px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(212, 175, 55, 0.3)';
            });
            
            elem.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1) rotateX(0deg)';
                this.style.boxShadow = '0 10px 40px rgba(0, 0, 0, 0.4)';
            });
        });

        // Newsletter form enhancement
        const emailInput = document.querySelector('input[type="email"]');
        if (emailInput) {
            emailInput.addEventListener('focus', function() {
                this.parentElement.parentElement.style.transform = 'scale(1.02)';
            });
            
            emailInput.addEventListener('blur', function() {
                this.parentElement.parentElement.style.transform = 'scale(1)';
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            createParticles();
            revealOnScroll();
            
            // Add stagger animation to elements
            const elements = document.querySelectorAll('.elem');
            elements.forEach((elem, index) => {
                elem.style.animationDelay = `${index * 0.2}s`;
                elem.classList.add('fade-in');
            });
        });

        // Intersection Observer for better performance
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, observerOptions);

        // Observe all scroll-reveal elements
        document.querySelectorAll('.scroll-reveal').forEach(el => {
            observer.observe(el);
        });

        // Premium loading animation
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 1s ease';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

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
