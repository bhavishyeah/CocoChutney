// Define constants
const CART_STORAGE_KEY = "cart";

// --- Cart Functions ---
// Retrieves the cart data from localStorage.
function getCart() {
    try {
        // Parse the JSON string from localStorage or return an empty object if null/invalid.
        return JSON.parse(localStorage.getItem(CART_STORAGE_KEY)) || {};
    } catch (error) {
        // Log any errors during retrieval or parsing.
        console.error('Error getting cart from localStorage:', error);
        return {}; // Return empty cart on error to prevent further issues.
    }
}

// Saves the cart data to localStorage.
function saveCart(cart) {
    try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    } catch (error) {
        console.error('Error saving cart to localStorage:', error);
    }
}

// Renders the cart items and updates the total price.
function renderCart() {
    const cartItemsContainer = document.getElementById("cart-items");
    const cartTotalElement = document.getElementById("cart-total");
    const cart = getCart(); // Get the latest cart data

    if (!cartItemsContainer || !cartTotalElement) {
        console.error("Cart container or total element not found.");
        return; // Exit if necessary elements are missing
    }

    cartItemsContainer.innerHTML = ""; // Clear current items
    let totalPrice = 0;

    const itemNames = Object.keys(cart);

    if (itemNames.length === 0) {
        cartItemsContainer.innerHTML = `<p class="empty-cart-message">Your cart is empty</p>`;
    } else {
        itemNames.forEach(itemName => {
            const item = cart[itemName];
            // Ensure item and its properties exist before using them
            if (item && typeof item.price === 'number' && typeof item.quantity === 'number') {
                 totalPrice += item.price * item.quantity;

                 const cartItem = document.createElement("div");
                 cartItem.classList.add("cart-item");
                 // Use consistent class names for elements within the item
                 cartItem.innerHTML = `
                     <img src="${item.image}" alt="${itemName}">
                     <div class="item-details">
                         <p class="item-name">${itemName}</p>
                         <p class="item-price">₹${item.price.toFixed(2)}</p> <div class="quantity-control">
                             <button class="decrease" data-name="${itemName}">-</button>
                             <span class="quantity">${item.quantity}</span>
                             <button class="increase" data-name="${itemName}">+</button>
                         </div>
                     </div>
                     <button class="remove-btn" data-name="${itemName}">Remove</button>
                 `;
                 cartItemsContainer.appendChild(cartItem);
            } else {
                 console.error(`Invalid item data in cart for "${itemName}":`, item);
                 // Optionally, remove this invalid item from the cart
                 // delete cart[itemName];
                 // saveCart(cart);
            }
        });
    }

    // Update the total price display
    cartTotalElement.innerText = totalPrice.toFixed(2); // Format total price

    // Re-attach event listeners after rendering
    attachEventListeners();
}

// Attaches event listeners to quantity change and remove buttons.
function attachEventListeners() {
    // Add listeners for increase buttons
    document.querySelectorAll(".increase").forEach(button => {
        button.removeEventListener("click", handleIncreaseQuantity); // Prevent duplicate listeners
        button.addEventListener("click", handleIncreaseQuantity);
    });

    // Add listeners for decrease buttons
    document.querySelectorAll(".decrease").forEach(button => {
        button.removeEventListener("click", handleDecreaseQuantity); // Prevent duplicate listeners
        button.addEventListener("click", handleDecreaseQuantity);
    });

    // Add listeners for remove buttons
    document.querySelectorAll(".remove-btn").forEach(button => {
        button.removeEventListener("click", handleRemoveItem); // Prevent duplicate listeners
        button.addEventListener("click", handleRemoveItem);
    });
}

// Handles increasing item quantity.
function handleIncreaseQuantity(event) {
    const itemName = event.target.getAttribute("data-name");
    const cart = getCart();
    if (cart[itemName]) {
        cart[itemName].quantity++;
        saveCart(cart);
        renderCart(); // Re-render the cart to update display
    }
}

// Handles decreasing item quantity.
function handleDecreaseQuantity(event) {
    const itemName = event.target.getAttribute("data-name");
    const cart = getCart();
    if (cart[itemName] && cart[itemName].quantity > 1) {
        cart[itemName].quantity--;
        saveCart(cart);
        renderCart(); // Re-render the cart
    } else if (cart[itemName] && cart[itemName].quantity === 1) {
        // If quantity is 1, remove the item
        handleRemoveItem(event); // Call the remove function
    }
}

// Handles removing an item from the cart.
function handleRemoveItem(event) {
    const itemName = event.target.getAttribute("data-name");
    const cart = getCart();
    if (cart[itemName]) {
        delete cart[itemName];
        saveCart(cart);
        renderCart(); // Re-render the cart
    }
}



// Clears the entire cart.
function clearCart() {
    if (confirm("Are you sure you want to clear your cart?")) {
        localStorage.removeItem(CART_STORAGE_KEY);
        renderCart(); // Re-render to show empty cart
        // Optionally, reload the page: location.reload();
    }
}

// --- Custom Cursor ---
var crsr = document.querySelector("#cursor");
var blur = document.querySelector("#cursor-blur");

// Function to detect if the device is a touch device.
function isTouchDevice() {
    return ('ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0);
}

// Check if cursor elements exist AND if it's NOT a touch device before enabling cursor effects.
if (crsr && blur && !isTouchDevice()) {
    // Add mousemove listener to update cursor position.
    document.addEventListener("mousemove", function (dets) {
        crsr.style.left = dets.clientX + "px"; // Use clientX/Y for more reliable positioning.
        crsr.style.top = dets.clientY + "px";
        blur.style.left = dets.clientX - 250 + "px"; // Adjust blur position relative to cursor.
        blur.style.top = dets.clientY - 250 + "px";
    });

    // Optional: Add hover effects for interactive elements.
    // Select elements that cursor should interact with
    document.querySelectorAll('a, button, .cart-item, #scroller h4').forEach(element => { // Added #scroller h4
        element.addEventListener('mouseenter', () => {
            crsr.style.scale = 3; // Make cursor larger.
            crsr.style.border = "1px solid #fff"; // Add border.
            crsr.style.backgroundColor = "transparent"; // Make background transparent.
        });
        element.addEventListener('mouseleave', () => {
            crsr.style.scale = 1; // Reset cursor size.
            crsr.style.border = "0px solid #ff9900"; // Reset border.
            crsr.style.backgroundColor = "#ff9900"; // Reset background color.
        });
    });
} else if (crsr || blur) {
    // If it is a touch device, hide the custom cursor elements.
    if (crsr) crsr.style.display = 'none';
    if (blur) blur.style.display = 'none';
}
// --- End Custom Cursor ---


// --- Mobile Navigation Toggle ---
const openMenu = document.getElementById("open-menu");
const closeMenu = document.getElementById("close-menu");
const mobileNav = document.getElementById("mobile-nav");

// Check if mobile nav elements exist before adding listeners.
if (openMenu && closeMenu && mobileNav) {
     // Add click listener to open the mobile nav.
     openMenu.addEventListener("click", () => {
       mobileNav.classList.add('open'); // Add 'open' class to trigger CSS animation.
     });

     // Add click listener to close the mobile nav.
     closeMenu.addEventListener("click", () => {
        mobileNav.classList.remove('open'); // Remove 'open' class to trigger CSS animation.
     });

     // Close mobile nav when a link inside it is clicked.
     document.querySelectorAll("#mobile-nav ul li a").forEach(link => {
       link.addEventListener("click", () => {
         mobileNav.classList.remove('open'); // Close nav when a link is selected.
       });
     });

     // Optional: Close mobile nav if clicking outside it.
     document.addEventListener('click', (event) => {
         // Check if the click is outside the mobile nav AND outside the open button,
         // AND if the mobile nav is currently open.
         if (!mobileNav.contains(event.target) && !openMenu.contains(event.target) && mobileNav.classList.contains('open')) {
             mobileNav.classList.remove('open'); // Close the nav.
         }
     });
}
// --- End Mobile Navigation Toggle ---


// --- Event Listeners ---
document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM content loaded!");

    // Initial render of the cart
    renderCart();

    // Expose functions globally if needed for inline onclick (less recommended)
    // window.proceedToPayment = proceedToPayment; // Already in the provided JS
    // window.clearCart = clearCart; // Already in the provided JS
});
// --- End Event Listeners ---
document.addEventListener('DOMContentLoaded', () => {
    // Smooth scrolling for navigation links
    document.querySelectorAll('#nav .nav-links a, #mobile-nav ul li a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).scrollIntoView({
                behavior: 'smooth'
            });

            // Close mobile nav after clicking a link
            const mobileNav = document.getElementById('mobile-nav');
            if (mobileNav.classList.contains('open')) {
                mobileNav.classList.remove('open');
                document.getElementById('hamburger-menu').querySelector('i').classList.remove('fa-times');
                document.getElementById('hamburger-menu').querySelector('i').classList.add('fa-bars');
            }
        });
    });

    // Mobile navigation toggle
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const closeMenu = document.getElementById('close-menu');
    const mobileNav = document.getElementById('mobile-nav');

    hamburgerMenu.addEventListener('click', () => {
        mobileNav.classList.add('open');
        hamburgerMenu.querySelector('i').classList.remove('fa-bars');
        hamburgerMenu.querySelector('i').classList.add('fa-times');
        // Animate mobile nav links
        mobileNav.querySelectorAll('ul li').forEach((item, index) => {
            item.style.animationDelay = `${0.05 * index}s`; // Stagger delay
            item.style.opacity = 1;
            item.style.transform = 'translateX(0)';
        });
    });

    closeMenu.addEventListener('click', () => {
        mobileNav.classList.remove('open');
        hamburgerMenu.querySelector('i').classList.remove('fa-times');
        hamburgerMenu.querySelector('i').classList.add('fa-bars');
    });

    // Cursor functionality (assuming you have this)
    const cursor = document.getElementById('cursor');
    const cursorBlur = document.getElementById('cursor-blur');

    document.addEventListener('mousemove', (e) => {
        if (cursor) {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
            cursor.style.display = 'block';
        }
        if (cursorBlur) {
            cursorBlur.style.left = e.clientX - 200 + 'px'; // Center blur around cursor
            cursorBlur.style.top = e.clientY - 200 + 'px';
            cursorBlur.style.display = 'block';
        }
    });

    document.addEventListener('mouseleave', () => {
        if (cursor) cursor.style.display = 'none';
        if (cursorBlur) cursorBlur.style.display = 'none';
    });

    // Hover effects for nav links and scroller h4 (if you want cursor to change)
    document.querySelectorAll('#nav .nav-links h4, #scroller h4').forEach(element => {
        element.addEventListener('mouseenter', () => {
            if (cursor) cursor.style.transform = 'scale(3)'; // Bigger cursor
            if (cursorBlur) cursorBlur.style.transform = 'scale(0.5)'; // Smaller blur or different effect
        });
        element.addEventListener('mouseleave', () => {
            if (cursor) cursor.style.transform = 'scale(1)';
            if (cursorBlur) cursorBlur.style.transform = 'scale(1)';
        });
    });


    // ============ NEW: Cart Item and Quantity Button Shine Logic ============

    const cartItems = document.querySelectorAll('.cart-item');

    // Function to trigger shine on a given element
    function triggerShine(element, animationDuration = 500) { // Default 500ms for cart-item shine
        // Ensure the element is valid and has the shine-active class logic
        if (!element || !element.classList) {
            return;
        }

        // Remove and re-add the class to re-trigger the animation
        element.classList.remove('shine-active');
        // Small delay to ensure the browser registers the class removal before re-adding
        // This forces the animation to restart from its beginning
        void element.offsetWidth; // Force reflow/repaint to ensure animation resets
        element.classList.add('shine-active');

        // Remove the class after the animation duration
        setTimeout(() => {
            element.classList.remove('shine-active');
        }, animationDuration);
    }


    // 1. Handle '+' button clicks to shine the parent cart-item
    document.querySelectorAll('.increase-qty').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent the click from bubbling up to the cart-item wrapper

            // Find the closest parent .cart-item
            const parentCartItem = this.closest('.cart-item');
            if (parentCartItem) {
                // Trigger the shine on the parent cart item (using 0.5s shineTap animation)
                triggerShine(parentCartItem, 500); // Pass the duration of shineTap animation
            }

            // You can add your quantity update logic here
            const quantitySpan = this.previousElementSibling;
            let currentQuantity = parseInt(quantitySpan.textContent);
            quantitySpan.textContent = currentQuantity + 1;
            // You might also want to update total price here or via a global state management
        });
    });

    // 2. Handle '-' button clicks (optional shine)
    document.querySelectorAll('.decrease-qty').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent the click from bubbling up to the cart-item wrapper

            // Optional: Trigger shine on parent cart item even for decrease
            // const parentCartItem = this.closest('.cart-item');
            // if (parentCartItem) {
            //     triggerShine(parentCartItem, 500);
            // }

            // Quantity update logic
            const quantitySpan = this.nextElementSibling; // Changed to nextElementSibling
            let currentQuantity = parseInt(quantitySpan.textContent);
            if (currentQuantity > 1) { // Prevent quantity from going below 1
                quantitySpan.textContent = currentQuantity - 1;
            } else {
                // If quantity is 1 and decreased, perhaps remove the item or just keep it at 1
                console.log("Cannot decrease quantity below 1. Consider removing item.");
            }
            // Update total price here
        });
    });


    // 3. Handle 'Remove' button clicks (optional shine)
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent the click from bubbling up to the cart-item wrapper

            // Optional: Trigger shine on the remove button itself if desired,
            // though the button's own :active::before handles it via CSS.
            // triggerShine(this, 300); // 300ms for buttonShine animation

            // Find the closest parent .cart-item and remove it
            const parentCartItem = this.closest('.cart-item');
            if (parentCartItem) {
                parentCartItem.remove();
                // Update total price and check if cart is empty here
            }
        });
    });

    // 4. Handle shine on main action buttons in cart-footer
    document.querySelectorAll('.checkout-btn, .clear-cart, .back-to-menu-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            // No need for event.stopPropagation() here as they are top-level buttons
            // The shine is handled by CSS :active::before for these buttons,
            // but if you wanted a JS-controlled shine for specific logic,
            // you could uncomment the line below.
            // triggerShine(this, 300); // Trigger shine on the button itself (using buttonShine duration)

            // Add your button specific logic here (e.g., redirect to checkout, clear cart, go back)
            if (this.classList.contains('clear-cart')) {
                // Example: Clear all items (visual removal)
                const cartItemsContainer = document.getElementById('cart-items');
                cartItemsContainer.innerHTML = '<p class="empty-cart-message">Your cart is empty.</p>';
                // Logic to update backend/storage if applicable
            } else if (this.classList.contains('back-to-menu-btn')) {
                window.location.href = 'index.html'; // Example redirect
            }
            // For checkout, you'd typically send data to a server
            console.log(this.textContent + ' clicked!');
        });
    });

    // Initial check for empty cart
    if (cartItems.length === 0) {
        document.getElementById('cart-items').innerHTML = '<p class="empty-cart-message">Your cart is empty.</p>';
    }

});

// Mobile touch/tap shine for cart-item image
document.querySelectorAll('.cart-item img').forEach((img) => {
    img.addEventListener('click', () => {
        const item = img.closest('.cart-item');
        item.classList.add('shine-active');
        setTimeout(() => {
            item.classList.remove('shine-active');
        }, 600); // Match shineTap animation duration
    });
});

// Mobile tap shine for Add buttons
document.querySelectorAll('.quantity-control button').forEach((btn) => {
    btn.addEventListener('click', () => {
        btn.classList.add('shine-tap');
        setTimeout(() => {
            btn.classList.remove('shine-tap');
        }, 300); // Match buttonShine duration
    });
});
if (window.navigator.vibrate) {
    window.navigator.vibrate(15); // Buzzes for 15ms on touch
}
setInterval(() => {
    document.querySelectorAll('.cart-item').forEach(item => {
        const shine = item.querySelector('::before');
        item.classList.add('shine-now');

        // Remove the class after 0.5 seconds
        setTimeout(() => {
            item.classList.remove('shine-now');
        }, 500);
    });
}, 100); // Every 10 seconds
// Add this function to your existing cart.js file
// (Keep all your existing cart-related functions like getCart, saveCart, renderCart, etc.)

// --- Cart Total and Checkout Logic (Ensure this is in cart.js) ---

// (Keep all your existing cart-related functions like getCart, saveCart, renderCart, etc.)

// --- Cart Total and Checkout Logic (Ensure this is in cart.js) ---

// Function to proceed to checkout with cart data
function proceedToCheckout() {
    console.log("Proceed to checkout clicked!");

    const cart = getCart(); // Uses your existing getCart function
    console.log("Current cart:", cart);

    const itemNames = Object.keys(cart);
    console.log("Item names:", itemNames);

    if (itemNames.length === 0) {
        alert("Your cart is empty. Please add items before proceeding to checkout.");
        return;
    }

    // Calculate cart total (same logic as in renderCart)
    let cartTotal = 0;
    let totalPackagingFee = 0; // Initialize packaging fee
    const PACKAGING_FEE_PER_ITEM = 5; // Define packaging fee per item

    itemNames.forEach(itemName => {
        const item = cart[itemName];
        if (item && typeof item.price === 'number' && typeof item.quantity === 'number') {
            cartTotal += item.price * item.quantity;
            totalPackagingFee += item.quantity * PACKAGING_FEE_PER_ITEM; // Calculate packaging for each item's quantity
        }
    });

    console.log("Cart total (before GST & packaging):", cartTotal);
    console.log("Total Packaging Fee:", totalPackagingFee);

    // Calculate GST (5%)
    const GST_PERCENTAGE = 0.05; // Changed from 0.18 to 0.05 (5%)
    // GST applies to the item total, not including packaging or delivery yet (standard practice)
    const gstAmount = cartTotal * GST_PERCENTAGE;
    
    // For simplicity, let's assume Delivery Fee and Discount will be handled later dynamically
    // For now, they will be 0 or hardcoded if you prefer in checkout.html
    const deliveryFee = 0; // Will be calculated dynamically later
    const discountAmount = 0; // Will be applied with coupons later

    // Final total calculation: Item Total + Packaging + GST + Delivery - Discount
    const finalTotal = cartTotal + totalPackagingFee + gstAmount + deliveryFee - discountAmount;

    console.log("GST amount (5%):", gstAmount);
    console.log("Final total:", finalTotal);

    // Store checkout data in sessionStorage
    const checkoutData = {
        cartItems: cart, // The actual items (object format)
        cartTotal: cartTotal, // Subtotal of items (before packaging, GST, delivery, discount)
        totalPackagingFee: totalPackagingFee, // New: Total packaging fee
        gstAmount: gstAmount,
        gstPercentage: GST_PERCENTAGE * 100, // Store as 5, not 0.05
        deliveryFee: deliveryFee, // Placeholder
        discountAmount: discountAmount, // Placeholder
        finalTotal: finalTotal,
    };

    console.log("Storing checkout data:", checkoutData);
    sessionStorage.setItem('checkoutData', JSON.stringify(checkoutData));

    // Redirect to checkout page
    console.log("Redirecting to checkout.html");
    window.location.href = 'checkout.html';
}
