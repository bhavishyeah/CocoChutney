<?php
// --- Development Error Reporting (TURN OFF IN PRODUCTION) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------------------------

session_start(); // Start session to store temporary data

// --- Database Configuration (Optional - include if you have a config file) ---
// require_once 'db_config.php'; // If you have a separate config file

// --- Database Connection (If not using a config file) ---
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$database = "cocochutney_db"; // The name of your database
try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database Connection Error in Initiate Payment: " . $e->getMessage());
    die("Sorry, there was a problem processing your request.");
}

// --- Razorpay Configuration (Replace with your actual test/live keys) ---
$keyId = "YOUR_RAZORPAY_KEY_ID"; // Replace with your Razorpay Key ID (starts with rzp_test_ or rzp_live_)
$keySecret = "YOUR_RAZORPAY_KEY_SECRET"; // Replace with your Razorpay Key Secret
$apiEndpoint = "https://api.razorpay.com/v1/orders"; // Razorpay API endpoint for creating orders

// --- 2. Receive and Validate Form Data ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Basic validation (add more robust validation here as in previous PHP)
    $guest_name = trim(filter_input(INPUT_POST, 'guest_name', FILTER_SANITIZE_STRING));
    $guest_phone = trim(filter_input(INPUT_POST, 'guest_phone', FILTER_SANITIZE_STRING));
    $guest_email = trim(filter_input(INPUT_POST, 'guest_email', FILTER_SANITIZE_EMAIL));
    $reservation_date = filter_input(INPUT_POST, 'reservation_date', FILTER_SANITIZE_STRING);
    $reservation_time = filter_input(INPUT_POST, 'reservation_time', FILTER_SANITIZE_STRING);
    $number_guests = filter_input(INPUT_POST, 'number_guests', FILTER_VALIDATE_INT);
    $occasion = trim(filter_input(INPUT_POST, 'occasion', FILTER_SANITIZE_STRING));
    $special_requests = trim(filter_input(INPUT_POST, 'special_requests', FILTER_SANITIZE_STRING));

    // --- Store Reservation Data Temporarily (using session) ---
    // Use a unique identifier, e.g., a generated booking ID or a combination
    $internalBookingId = uniqid('book_', true); // Generate a unique ID for this booking attempt

    $_SESSION['booking_in_progress_' . $internalBookingId] = [
        'guest_name' => $guest_name,
        'guest_phone' => $guest_phone,
        'guest_email' => $guest_email,
        'reservation_date' => $reservation_date,
        'reservation_time' => $reservation_time,
        'number_guests' => $number_guests,
        'occasion' => $occasion,
        'special_requests' => $special_requests,
        'internal_booking_id' => $internalBookingId // Store the internal ID
        // Add any other necessary data
    ];

    // --- Prepare Razorpay Order Data ---
    // Amount must be in smallest currency unit (Paisa for INR)
    $amountInPaisa = 100 * 100; // ₹100 is 10000 paisa

    $orderData = [
        'receipt'         => $internalBookingId, // Use your internal ID as receipt
        'amount'          => $amountInPaisa,
        'currency'        => 'INR',
        'payment_capture' => 1 // Auto capture payment
    ];

    // --- Create Razorpay Order via API (Server-to-Server) ---
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
    // Set basic auth header with Key ID and Secret Key
    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
    curl_close($ch);

    $order = json_decode($response, true);

    // Check if Order creation was successful
    if ($httpCode !== 200 || !isset($order['id'])) {
        // Handle error - Order creation failed
        error_log("Razorpay Order Creation Failed: " . $response);
        header("Location: payment_failed.html?reason=order_creation_failed");
        exit();
    }

    // Store Razorpay Order ID in session as well, linked to internal ID
    $_SESSION['booking_in_progress_' . $internalBookingId]['razorpay_order_id'] = $order['id'];


    // --- Render the Payment Page with Checkout.js ---
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceed to Payment - CocoChutney</title>
    <link rel="stylesheet" href="style.css"> <style>
        /* Inherit basic styles from your theme */
        body {
            background-color: #121212;
            color: #f0f0f0;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        #nav {
  height: 13vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 5vw;
  padding: 0 12vw;
  position: fixed;
  z-index: 999;
  background-color: rgba(0, 0, 0, 0.5);
}
#nav img {
  height: 4.5vw;
}
#nav h4 {
  text-transform: uppercase;
  font-weight: 500;
  font-size: 1.15vw;
  cursor: pointer;
}
/* Default nav link styles */
#nav h4 a {
  color: white;
  text-decoration: none;
}

/* On hover */
#nav h4 a:hover {
  color: orange;
}

@media (max-width: 480px) {
  #nav {
    padding: 0 2vw;
    height: 60px;
  }
  #nav img {
    height: 2vw;
  }
  #nav h4 {
    font-size: 0.8vw;
  }
}

/* ========== Hamburger Menu ========== */
#hamburger-menu {
  display: none;
  position: fixed;
  top: 2vh;
  right: 5vw;
  z-index: 1001;
  cursor: pointer;
}

#hamburger-menu i {
  font-size: 2.5rem;
  color: white;
}

/* ========== Mobile Navigation Drawer ========== */
#mobile-nav {
  display: none;
  flex-direction: column;
  position: fixed;
  top: 0;
  right: 0;
  width: 60vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.95);
  padding: 4vh 2vw;
  z-index: 1000;
  animation: slideIn 0.3s ease forwards;
}

#mobile-nav i#close-menu {
  font-size: 2rem;
  color: white;
  align-self: flex-end;
  cursor: pointer;
}

#mobile-nav ul {
  list-style: none;
  margin-top: 5vh;
}

#mobile-nav ul li {
  margin: 3vh 0;
}

#mobile-nav ul li a {
  text-decoration: none;
  color: white;
  font-size: 1.2rem;
  transition: color 0.3s ease;
}

#mobile-nav ul li a:hover {
  color: #ff9900;
}

/* Slide-in animation */
@keyframes slideIn {
  from {
    right: -100%;
  }
  to {
    right: 0;
  }
}

/* Show hamburger only on small screens */
@media (max-width: 768px) {
  #hamburger-menu {
    display: block;
  }

#nav h4 {
  display: none; 
  }

}

@media (max-width: 480px) {
  #hamburger-menu {
    top: 1vh;
    right: 2vw;
  }
  #hamburger-menu i {
    font-size: 2rem;
  }
  #nav h4 {
    display: none; 
  }

  #nav img {
    height: 5vh;
  
  }
  .nav {
    height: 10px;
  }
}

         .main-content-area {
             padding-top: 12vh;
             width: 100%;
             flex-grow: 1;
             display: flex;
             flex-direction: column;
             align-items: center;
             padding-bottom: 5vh;
             box-sizing: border-box;
         }
         .payment-init-container { /* Similar styling to thankyou/status container */
             background-color: #1e1e1e;
             padding: 5vh 6vw;
             border-radius: 15px;
             box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
             width: 80%;
             max-width: 700px;
             text-align: center;
             color: #f0f0f0;
         }
         .payment-init-container h1 {
             color: #ff9900;
             font-size: clamp(2rem, 5vw, 3rem);
             font-weight: 700;
             margin-bottom: 0.8em;
         }
         .payment-init-container p {
             font-size: 1.05em;
             color: #ccc;
             margin-bottom: 1.5em;
             line-height: 1.6;
         }
          .payment-button {
              background: linear-gradient(135deg, #ffcc00, #ff9800);
              color: #121212;
              border: none;
              padding: 15px 30px;
              border-radius: 8px;
              font-size: 1.2em;
              font-weight: 600;
              cursor: pointer;
              transition: background 0.3s ease, transform 0.1s ease;
              margin-top: 2em;
              text-transform: uppercase;
          }
          .payment-button:hover {
              background: linear-gradient(135deg, #ffb300, #ff6f00);
              transform: translateY(-2px);
          }
          .payment-button:active {
              transform: translateY(0px);
          }
          #footer {
  background-color: #0d0d0d;
  padding: 5vh 10vw;
  display: flex;
  flex-direction: column;
  gap: 4vh;
  position: relative;
}

.footer-top {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5vh;
}

.footer-logo {
  width: 160px;
  object-fit: contain;
}

.footer-decor {
  width: 100%;
  max-width: 300px;
}

.footer-links {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 3vw;
  margin-top: 2vh;
}

.footer-column {
  flex: 1;
  min-width: 200px;
}

.footer-column h3 {
  font-size: 1.3vw;
  margin-bottom: 1vh;
  color: #ff9900;
}

.footer-column a {
  display: block;
  color: #ccc;
  font-size: 1vw;
  margin-bottom: 0.8vh;
  text-decoration: none;
  transition: color 0.3s;
}

.footer-column a:hover {
  color: #ff9900;
}

.footer-column p {
  font-size: 1vw;
  color: #ccc;
  line-height: 1.5;
}

.footer-bottom {
  text-align: center;
  font-size: 0.9vw;
  color: #aaa;
  border-top: 1px solid #333;
  padding-top: 2vh;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .footer-column h3 {
    font-size: 4vw;
  }

  .footer-column a,
  .footer-column p {
    font-size: 3vw;
  }

  .footer-bottom {
    font-size: 2.5vw;
  }
}
     </style>
</head>
<body>
<div id="nav">
        <img src="logo.png" alt="Coco Chutney Logo" />
        <h4>Our Story</h4>
        <h4><a href="menu.html" class="active">Menu</a></h4>
        <h4>About Us</h4>
        <h4>Reservations</h4>
        <h4>Contact</h4>
    </div>
    <!-- Hamburger Menu for Mobile -->
<div id="hamburger-menu">
  <i class="ri-menu-3-line" id="open-menu"></i>
</div>

<!-- Mobile Navigation Drawer -->
<div id="mobile-nav">
  <i class="ri-close-line" id="close-menu"></i>
  <ul>
    <li><a href="#">Our Story</a></li>
    <li><a href="menu.html" class="active-link">Menu</a></li>
    <li><a href="#">About Us</a></li>
    <li><a href="#">Reservations</a></li>
    <li><a href="#">Contact</a></li>
  </ul>
</div>

    <div class="main-content-area">
        <div class="payment-init-container">
            <h1>Payment Required</h1>
            <p>A booking fee of ₹100 is required to confirm your reservation.</p>
            <p>Click the button below to proceed to secure payment.</p>

            <button class="payment-button" id="rzp-button1">Pay ₹100</button>

        </div>
    </div>

    <footer id="footer">  
  <div class="footer-links">
    <div class="footer-column">
      <h3>Menu</h3>
      <a href="#">Appetizers</a>
      <a href="#">Main Courses</a>
      <a href="#">Desserts</a>
      <a href="#">Beverages</a>
    </div>
    
    <div class="footer-column">
      <h3>Explore</h3>
      <a href="#">Reservations</a>
      <a href="#">Contact Us</a>
      <a href="#">About Us</a>
    </div>
    
    <div class="footer-column">
      <h3>Visit Us</h3>
      <p>
        Satya The Hive,<br />
        Dwarka ExpressWay, Sector 102,<br />
        Gurgaon, Haryana<br />
        <a href="mailto:info@cocochutney.com">info@cocochutney.com</a>
      </p>
    </div>
  </div>
  
  <div class="footer-bottom">
    <p>&copy; 2025 Coco Chutney. All rights reserved.</p>
  </div>
</footer>



    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <script>
        // JavaScript to handle the Razorpay modal
        var options = {
            "key": "<?php echo $keyId; ?>", // Your Razorpay Key ID
            "amount": "<?php echo $orderData['amount']; ?>", // Amount in Paisa
            "currency": "<?php echo $orderData['currency']; ?>",
            "name": "CocoChutney Booking Fee", // Your business name
            "description": "Reservation Booking Fee", // Transaction description
            "image": "logo.png", // Your business logo (optional)
            "order_id": "<?php echo $order['id']; ?>", // Razorpay Order ID created in PHP
            "handler": function (response){
                // This function is called when payment is successful
                // Send the response data to your server for verification
                console.log("Payment successful. Verifying...");
                console.log(response); // Log response for debugging

                // Make an AJAX call to your verification script
                fetch('verify_payment_razorpay.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json' // Indicate sending JSON
                    },
                    body: JSON.stringify(response) // Send response data as JSON
                })
                .then(response => response.json()) // Expect JSON response from verification script
                .then(data => {
                    console.log("Verification response:", data);
                    if (data.status === 'success') {
                        // Verification successful, booking is saved in DB
                        window.location.href = 'thankyou.html'; // Redirect to success page
                    } else {
                        // Verification failed
                        window.location.href = 'payment_failed.html?reason=verification_failed'; // Redirect to failure page
                    }
                })
                .catch((error) => {
                    console.error('Error during verification:', error);
                     window.location.href = 'payment_failed.html?reason=verification_error'; // Redirect on network/server error
                });
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($guest_name); ?>", // Pre-fill name
                "email": "<?php echo htmlspecialchars($guest_email); ?>", // Pre-fill email
                "contact": "<?php echo htmlspecialchars($guest_phone); ?>" // Pre-fill phone
            },
            "notes": {
                "internal_booking_id": "<?php echo $internalBookingId; ?>" // Pass your internal ID to Razorpay (optional, for reference)
            },
            "theme": {
                "color": "#ff9900" // Your theme color
            }
        };

        // Create a new Razorpay instance
        var rzp1 = new Razorpay(options);

        // Trigger the modal when the button is clicked
        document.getElementById('rzp-button1').onclick = function(e){
            rzp1.open();
            e.preventDefault(); // Prevent the button's default submit behavior
        }

        // Optional: Handle modal close/dismissal
        rzp1.on('payment.failed', function (response){
             console.error('Payment failed:', response); // Log failure details
             // You can redirect immediately or show a message
             window.location.href = 'payment_failed.html?reason=' + (response.error.reason || 'unknown');
        });


    </script>
    <?php
    // Close database connection
    $conn = null;
?>
    </body>
</html>
<?php
} // This closes the if ($_SERVER["REQUEST_METHOD"] == "POST") block
?>