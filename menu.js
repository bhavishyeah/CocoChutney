// ─────────────────────────────────────────────────────
// Constants
// ─────────────────────────────────────────────────────
const CART_BUTTON_ID = 'cart-button';
const CART_STORAGE_KEY = 'cart';


// ─────────────────────────────────────────────────────
// Cart Management Functions
// ─────────────────────────────────────────────────────

// Retrieve cart from localStorage
function getCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_STORAGE_KEY)) || {};
    } catch (error) {
        console.error('Error getting cart from localStorage:', error);
        return {};
    }
}

// Save cart to localStorage
function saveCart(cart) {
    try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    } catch (error) {
        console.error('Error saving cart to localStorage:', error);
    }
}

// Update/Add an item to the cart
function updateCart(itemName, itemPrice, itemImage, quantityElement) {
    try {
        const cart = getCart();
        const priceNumber = parseFloat(itemPrice.replace(/[^\d.]/g, ''));

        if (isNaN(priceNumber)) {
            console.error('Invalid price format:', itemPrice);
            return;
        }

        if (cart[itemName]) {
            cart[itemName].quantity++;
        } else {
            cart[itemName] = {
                price: priceNumber,
                quantity: 1,
                image: itemImage,
            };
        }

        if (quantityElement) {
            quantityElement.textContent = cart[itemName].quantity;
        }

        saveCart(cart);
        updateCartButton();
        console.log(`Item added to cart: ${itemName}`);
    } catch (error) {
        console.error('Error updating cart:', error);
    }
}

// Decrease quantity or remove item from cart
function decreaseCartQuantity(itemName, quantityElement) {
    try {
        const cart = getCart();

        if (cart[itemName]) {
            cart[itemName].quantity--;

            if (cart[itemName].quantity === 0) {
                delete cart[itemName];
            }

            if (quantityElement) {
                quantityElement.textContent = cart[itemName]?.quantity || 0;
            }

            saveCart(cart);
            updateCartButton();
            console.log(`Quantity decreased for: ${itemName}`);
        } else {
            console.warn(`Item not found in cart: ${itemName}`);
        }
    } catch (error) {
        console.error('Error decreasing cart quantity:', error);
    }
}

// Update cart button with total item count
function updateCartButton() {
    try {
        const cart = getCart();
        const cartCount = Object.values(cart).reduce((acc, item) => acc + item.quantity, 0);
        const cartButton = document.getElementById(CART_BUTTON_ID);

        if (cartButton) {
            cartButton.textContent = `Cart (${cartCount})`;
        }
    } catch (error) {
        console.error('Error updating cart button:', error);
    }
}

// Redirect to cart page
function handleCartButtonClick() {
    console.log('Cart button clicked. Redirecting to cart.html');

    fetch('cart.html', { method: 'HEAD' })
        .then(response => {
            if (response.ok) {
                window.location.href = 'cart.html';
            } else {
                console.warn('cart.html not found. Cannot redirect.');
            }
        })
        .catch(error => {
            console.error('Error checking for cart.html:', error);
        });
}


// ─────────────────────────────────────────────────────
// Event Handlers for Cart (+/- buttons)
// ─────────────────────────────────────────────────────

// "+" button
function handleAddButtonClick(event) {
    try {
        const menuItem = event.target.closest('.menu-item');
        if (!menuItem) throw new Error('Add button has no valid parent menu-item');

        const itemName = menuItem.dataset.name;
        const itemPrice = menuItem.dataset.price;
        const itemImageElement = menuItem.querySelector('img');
        const quantityElement = menuItem.querySelector('.quantity');

        if (itemName && itemPrice && itemImageElement && quantityElement) {
            const itemImage = itemImageElement.getAttribute('src');
            updateCart(itemName, itemPrice, itemImage, quantityElement);
        } else {
            console.error('Missing item details for add button.');
        }
    } catch (error) {
        console.error('Error handling add button click:', error);
    }
}

// "−" button
function handleRemoveButtonClick(event) {
    try {
        const menuItem = event.target.closest('.menu-item');
        if (!menuItem) throw new Error('Remove button has no valid parent menu-item');

        const itemName = menuItem.dataset.name;
        const quantityElement = menuItem.querySelector('.quantity');

        if (itemName && quantityElement) {
            decreaseCartQuantity(itemName, quantityElement);
        } else {
            console.error('Missing item name or quantity element.');
        }
    } catch (error) {
        console.error('Error handling remove button click:', error);
    }
}


// ─────────────────────────────────────────────────────
// Custom Cursor Logic (Only for non-touch devices)
// ─────────────────────────────────────────────────────
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


// ─────────────────────────────────────────────────────
// Event Binding on DOM Load
// ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded and parsed');

    // Attach "+" and "−" button listeners
    document.querySelectorAll('.add-btn').forEach(button => {
        button.addEventListener('click', handleAddButtonClick);
    });

    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', handleRemoveButtonClick);
    });

    // Attach cart button listener
    const cartButton = document.getElementById(CART_BUTTON_ID);
    if (cartButton) {
        cartButton.addEventListener('click', handleCartButtonClick);
    }

    // Initial cart button update
    updateCartButton();
});

// Mobile Shine Effects JavaScript
// Add this to your existing JavaScript file or create a new one

document.addEventListener('DOMContentLoaded', function() {
    // Function to detect if device is mobile/touch
    function isMobileDevice() {
        return (('ontouchstart' in window) || 
                (navigator.maxTouchPoints > 0) || 
                (navigator.msMaxTouchPoints > 0) ||
                (window.innerWidth <= 768));
    }

    // Function to add shine effect
    function addShineEffect(element, shineClass, duration = 800) {
        element.classList.add(shineClass);
        setTimeout(() => {
            element.classList.remove(shineClass);
        }, duration);
    }

    // Function to add ripple effect
    function addRippleEffect(element, event) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple-effect');
        
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    if (isMobileDevice()) {
        // Mobile shine for menu items
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            // Touch start for immediate feedback
            item.addEventListener('touchstart', function(e) {
                addShineEffect(this, 'mobile-shine', 800);
            }, { passive: true });

            // Alternative: tap event for better UX
            item.addEventListener('click', function(e) {
                // Only trigger if it's a touch event (not mouse on mobile)
                if (e.pointerType === 'touch' || window.TouchEvent && e instanceof TouchEvent) {
                    addShineEffect(this, 'mobile-shine', 800);
                }
            });
        });

        // Mobile shine for quantity buttons
        const quantityButtons = document.querySelectorAll('.quantity-control button');
        quantityButtons.forEach(button => {
            button.addEventListener('touchstart', function(e) {
                // Add shine effect
                addShineEffect(this, 'mobile-button-shine', 500);
                // Add pulse effect
                addShineEffect(this, 'mobile-button-pulse', 300);
            }, { passive: true });

            button.addEventListener('click', function(e) {
                // For mouse clicks on mobile browsers
                if (e.pointerType === 'touch' || window.TouchEvent && e instanceof TouchEvent) {
                    addShineEffect(this, 'mobile-button-shine', 500);
                    addShineEffect(this, 'mobile-button-pulse', 300);
                }
                
                // Handle quantity logic here
                // Example:
                // updateQuantity(this);
            });

            // Enhanced touch feedback
            button.addEventListener('touchend', function(e) {
                // Brief scale animation
                this.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }, { passive: true });
        });

        // Mobile shine for cart button
        const cartButton = document.getElementById('cart-button');
        if (cartButton) {
            cartButton.addEventListener('touchstart', function(e) {
                addShineEffect(this, 'mobile-cart-shine', 700);
            }, { passive: true });

            cartButton.addEventListener('click', function(e) {
                if (e.pointerType === 'touch' || window.TouchEvent && e instanceof TouchEvent) {
                    addShineEffect(this, 'mobile-cart-shine', 700);
                }
            });
        }

        // Optional: Add haptic feedback for supported devices
        function addHapticFeedback() {
            if (navigator.vibrate) {
                navigator.vibrate(50); // Very brief vibration
            }
        }

        // Add haptic feedback to quantity buttons
        quantityButtons.forEach(button => {
            button.addEventListener('touchstart', addHapticFeedback, { passive: true });
        });
    }

    // Enhanced quantity control functionality with shine
    function updateQuantity(button) {
        const isIncrement = button.textContent.includes('+');
        const quantityElement = isIncrement ? 
            button.nextElementSibling : 
            button.previousElementSibling;
        
        let currentQuantity = parseInt(quantityElement.textContent) || 0;
        
        if (isIncrement) {
            currentQuantity++;
        } else if (currentQuantity > 0) {
            currentQuantity--;
        }
        
        quantityElement.textContent = currentQuantity;
        
        // Add shine to the quantity display
        if (isMobileDevice()) {
            quantityElement.style.animation = 'none';
            setTimeout(() => {
                quantityElement.style.animation = 'fadeInUp 0.3s ease-out';
            }, 10);
        }
        
        // Update cart count, total, etc.
        // updateCartDisplay();
    }

    // Bind quantity buttons to update function
    document.querySelectorAll('.quantity-control button').forEach(button => {
        button.addEventListener('click', function() {
            updateQuantity(this);
        });
    });

    // Optional: Long press detection for continuous increment/decrement
    let longPressTimer;
    let isLongPress = false;

    document.querySelectorAll('.quantity-control button').forEach(button => {
        button.addEventListener('touchstart', function(e) {
            isLongPress = false;
            longPressTimer = setTimeout(() => {
                isLongPress = true;
                // Start continuous update
                const interval = setInterval(() => {
                    if (isLongPress) {
                        updateQuantity(this);
                        addShineEffect(this, 'mobile-button-shine', 300);
                    } else {
                        clearInterval(interval);
                    }
                }, 200);
            }, 500);
        }, { passive: true });

        button.addEventListener('touchend', function() {
            clearTimeout(longPressTimer);
            isLongPress = false;
        }, { passive: true });
    });
});

// Utility function to add custom shine to any element
function triggerCustomShine(element, duration = 600) {
    const shine = document.createElement('div');
    shine.style.cssText = `
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        pointer-events: none;
        z-index: 1000;
        animation: customShine ${duration}ms ease-out forwards;
    `;
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(shine);
    
    setTimeout(() => {
        shine.remove();
    }, duration);
}

// Add the custom shine keyframe
const style = document.createElement('style');
style.textContent = `
    @keyframes customShine {
        0% { left: -100%; }
        100% { left: 100%; }
    }
`;
document.head.appendChild(style);
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