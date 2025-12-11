document.addEventListener('DOMContentLoaded', function() {
    // This function will be called when the checkout page loads
    displayCheckoutData();

    // Event listener for "Back to Cart" button
    const backToCartBtn = document.getElementById('back-to-cart-btn');
    if (backToCartBtn) {
        backToCartBtn.addEventListener('click', () => {
            window.location.href = 'cart.html'; // Adjust this URL if your cart page is different
        });
    }

    // Event listener for "Place Order" button
    const placeOrderBtn = document.getElementById('place-order-btn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', () => {
            handlePlaceOrder();
        });
    }

    // --- Address Modal Logic ---
    const addressModal = document.getElementById('addressModal');
    const addNewAddressCard = document.getElementById('addNewAddressCard');
    const closeButton = addressModal.querySelector('.close-button');
    const newAddressForm = document.getElementById('newAddressForm');

    // Open modal when "Add New Address" card is clicked
    if (addNewAddressCard) {
        addNewAddressCard.addEventListener('click', () => {
            addressModal.style.display = 'flex'; // Use flex to center
        });
    }

    // Close modal when close button is clicked
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            addressModal.style.display = 'none';
        });
    }

    // Close modal when clicking outside of it
    window.addEventListener('click', (event) => {
        if (event.target === addressModal) {
            addressModal.style.display = 'none';
        }
    });

    // Handle new address form submission
    if (newAddressForm) {
        newAddressForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Get form data
            const formData = new FormData(newAddressForm);
            const newAddress = {};
            for (let [key, value] of formData.entries()) {
                newAddress[key] = value.trim(); // Trim whitespace
            }

            console.log("New Address Captured (before sending to server):", newAddress);

            // --- IMPORTANT: Send the data to your backend database ---
            fetch('api/save_address.php', { // <--- TARGET YOUR PHP API ENDPOINT HERE
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                    // 'Authorization': 'Bearer YOUR_USER_TOKEN' // If you have user authentication (handled by PHP sessions now)
                },
                body: JSON.stringify(newAddress)
            })
            .then(response => {
                // Check if response is OK (200-299 status codes)
                if (!response.ok) {
                    // If not OK, parse the error response and throw an error
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Server error occurred.');
                    });
                }
                return response.json(); // Parse the JSON response
            })
            .then(data => {
                if (data.success) {
                    alert('Address saved successfully!');
                    addressModal.style.display = 'none'; // Close modal
                    newAddressForm.reset(); // Clear form

                    // --- NEXT STEP: After saving, we'll want to display the newly saved addresses ---
                    // For now, we'll just log it. In Part 5, we'll call a function here to refresh the list.
                    console.log("Address saved response:", data);
                    // displaySavedAddresses(); // This function will be created in Part 5
                } else {
                    alert('Failed to save address: ' + (data.message || 'Unknown error'));
                    console.error("Server reported failure:", data);
                }
            })
            .catch(error => {
                console.error('Error saving address:', error);
                alert('An error occurred while saving address: ' + error.message);
            });
        });
    }
});

/**
 * Retrieves checkout data from sessionStorage and calls display functions.
 */
function displayCheckoutData() {
    const checkoutDataString = sessionStorage.getItem('checkoutData');

    if (!checkoutDataString) {
        // If no checkout data, it means the user might have accessed checkout directly
        // or refreshed the page. Redirect them back to the cart.
        alert("No checkout data found. Please add items before proceeding to checkout.");
        window.location.href = 'cart.html'; // Adjust this URL if your cart page is different
        return;
    }

    try {
        const checkoutData = JSON.parse(checkoutDataString);
        console.log("Checkout data loaded:", checkoutData);

        // Display individual cart items
        displayCheckoutItems(checkoutData.cartItems);

        // Display the price breakdown (subtotal, packaging, GST, final total)
        displayPriceBreakdown(checkoutData);

    } catch (error) {
        console.error('Error parsing checkout data from sessionStorage:', error);
        alert('There was an error processing your checkout. Please try again.');
        window.location.href = 'cart.html'; // Redirect to cart on error
    }
}

/**
 * Renders the list of items from the cart on the checkout page.
 * @param {object} cartItems - The object containing items from the cart.
 */
function displayCheckoutItems(cartItems) {
    const itemsContainer = document.getElementById('checkout-items');

    if (!itemsContainer) {
        console.error('Error: "checkout-items" container not found in checkout.html. Please ensure your HTML has <div id="checkout-items"></div>');
        return;
    }

    itemsContainer.innerHTML = ''; // Clear existing content

    // Check if cartItems is an object and has keys
    const itemNames = Object.keys(cartItems);
    if (itemNames.length === 0) {
        itemsContainer.innerHTML = '<p class="empty-message">No items in checkout.</p>';
        return;
    }

    itemNames.forEach(itemName => {
        const item = cartItems[itemName];
        // Basic validation for item properties
        if (item && typeof item.price === 'number' && typeof item.quantity === 'number') {
            const itemTotal = item.price * item.quantity;

            const itemElement = document.createElement('div');
            itemElement.classList.add('checkout-item'); // Add a class for styling
            itemElement.innerHTML = `
                <div class="item-info">
                    <img src="${item.image || 'placeholder-food.jpg'}" alt="${itemName}" class="item-image">
                    <div class="details">
                        <h4>${itemName}</h4>
                        <p>Price: ₹${item.price.toFixed(2)}</p>
                        <p>Quantity: ${item.quantity}</p>
                    </div>
                </div>
                <div class="item-subtotal">
                    <p>₹${itemTotal.toFixed(2)}</p>
                </div>
            `;
            itemsContainer.appendChild(itemElement);
        } else {
            console.warn(`Invalid item data encountered for "${itemName}" during checkout display:`, item);
        }
    });
}

/**
 * Displays the price breakdown including subtotal, packaging fee, GST, and final total.
 * @param {object} checkoutData - The object containing cartTotal, gstAmount, finalTotal, gstPercentage, totalPackagingFee, etc.
 */
function displayPriceBreakdown(checkoutData) {
    // Get references to the HTML elements
    const cartSubtotalElement = document.getElementById('cart-subtotal');
    const packagingFeeElement = document.getElementById('packaging-fee'); // New element
    const deliveryFeeElement = document.getElementById('delivery-fee');
    const discountElement = document.getElementById('discount');
    const gstAmountElement = document.getElementById('gst-amount');
    const gstPercentageElement = document.getElementById('gst-percentage');
    const finalTotalElement = document.getElementById('final-total');
    const totalSavingsElement = document.getElementById('total-savings'); // Assuming this is for discounts/savings

    // Update the text content of the elements, with appropriate checks
    if (cartSubtotalElement) {
        cartSubtotalElement.textContent = `${checkoutData.cartTotal.toFixed(2)}`;
    }

    // New: Update packaging fee
    if (packagingFeeElement) {
        packagingFeeElement.textContent = `${checkoutData.totalPackagingFee.toFixed(2)}`;
    }

    if (deliveryFeeElement) {
        deliveryFeeElement.textContent = `${checkoutData.deliveryFee.toFixed(2)}`;
    }

    if (discountElement) {
        discountElement.textContent = `${checkoutData.discountAmount.toFixed(2)}`;
    }

    if (gstAmountElement) {
        gstAmountElement.textContent = `${checkoutData.gstAmount.toFixed(2)}`;
    }

    if (gstPercentageElement) {
        gstPercentageElement.textContent = `${checkoutData.gstPercentage}%`; // Display 5%
    }

    if (finalTotalElement) {
        finalTotalElement.textContent = `${checkoutData.finalTotal.toFixed(2)}`;
    }

    if (totalSavingsElement) {
        // You'll need to define how total savings are calculated.
        // For now, let's assume it's just the discountAmount.
        totalSavingsElement.textContent = `${checkoutData.discountAmount.toFixed(2)}`;
    }
}

/**
 * Handles the "Place Order" or "Pay Now" logic.
 * You'd typically send this data to a backend server here.
 */
function handlePlaceOrder() {
    const checkoutData = JSON.parse(sessionStorage.getItem('checkoutData'));

    if (!checkoutData) {
        alert("No order details found. Please go back to cart and try again.");
        return;
    }

    // Get selected address (you'll need to implement logic to select an address in Part 5)
    // For now, it's just a placeholder. When you fetch addresses from DB, you'd mark one as selected.
    const selectedAddress = {
        type: "Home",
        details: "Selected Address (Implement selection logic)"
    };

    // Get selected payment method
    const selectedPaymentInput = document.querySelector('input[name="payment"]:checked');
    const selectedPaymentMethod = selectedPaymentInput ? selectedPaymentInput.value : 'N/A';

    console.log("Placing order with data:", {
        ...checkoutData,
        selectedAddress: selectedAddress,
        selectedPaymentMethod: selectedPaymentMethod
    });

    // In a real application, you would send `checkoutData` and selected details to your server
    // for payment processing and order fulfillment.
    // Example:
    // fetch('/api/place-order', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         // 'Authorization': 'Bearer YOUR_USER_TOKEN' // If you have user authentication
    //     },
    //     body: JSON.stringify({
    //         order: checkoutData,
    //         address: selectedAddress,
    //         paymentMethod: selectedPaymentMethod
    //     })
    // })
    // .then(response => response.json())
    // .then(data => {
    //     if (data.success) {
    //         alert('Order Placed Successfully! Order ID: ' + data.orderId);
    //         localStorage.removeItem('cart'); // Clear cart from localStorage after successful order
    //         sessionStorage.removeItem('checkoutData'); // Clear checkout data
    //         window.location.href = 'order-confirmation.html'; // Redirect to confirmation page
    //     } else {
    //         alert('Order failed: ' + (data.message || 'Unknown error'));
    //     }
    // })
    // .catch(error => {
    //     console.error('Error placing order:', error);
    //     alert('An error occurred while placing your order.');
    // });

    alert(`Order Placed!\nTotal Amount: ₹${checkoutData.finalTotal.toFixed(2)}\nPayment Method: ${selectedPaymentMethod}\n(This is a simulation. No real payment processed.)`);

    // After successful order, you might want to clear the cart and session storage
    // localStorage.removeItem('cart'); // Clear cart from localStorage
    // sessionStorage.removeItem('checkoutData'); // Clear checkout data from sessionStorage

    // Redirect to a confirmation page or home page
    // window.location.href = 'order-confirmation.html';
}

// These are helper functions for UI interaction, potentially moved or consolidated.
// They are still in checkout.html's <script> block as per previous step.
// For a cleaner approach, these could be integrated into checkout.js event listeners.
/*
function selectAddress(element) {
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('selected');
    });
    element.classList.add('selected');
}

function selectPayment(element, type) {
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('selected');
        option.querySelector('.radio-input').checked = false;
    });
    element.classList.add('selected');
    element.querySelector('.radio-input').checked = true;
    console.log('Selected payment method:', type);
}

function applyCoupon() {
    alert('Apply Coupon functionality would go here!');
    // Implement coupon application logic
}
*/