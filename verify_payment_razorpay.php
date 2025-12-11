<?php
// --- Development Error Reporting (TURN OFF IN PRODUCTION) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------------------------

session_start(); // Start session

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
    error_log("Database Connection Error in Verification: " . $e->getMessage());
    // Send JSON error response back to frontend
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}


// --- Razorpay Configuration (Must match initiate_payment_razorpay.php) ---
$keySecret = "YOUR_RAZORPAY_KEY_SECRET"; // Replace with your Razorpay Key Secret

// --- 1. Receive Payment Response Data from Frontend ---
// Data is sent as JSON in the fetch body
$json_data = file_get_contents('php://input');
$paymentResponse = json_decode($json_data, true); // Decode JSON into associative array

$razorpay_payment_id = isset($paymentResponse['razorpay_payment_id']) ? $paymentResponse['razorpay_payment_id'] : null;
$razorpay_order_id = isset($paymentResponse['razorpay_order_id']) ? $paymentResponse['razorpay_order_id'] : null;
$razorpay_signature = isset($paymentResponse['razorpay_signature']) ? $paymentResponse['razorpay_signature'] : null;


// Set JSON header for the response
header('Content-Type: application/json');

// Check if essential parameters are received
if (!$razorpay_payment_id || !$razorpay_order_id || !$razorpay_signature) {
     error_log("Verification failed: Missing parameters in response.");
     echo json_encode(['status' => 'error', 'message' => 'Missing payment parameters.']);
     $conn = null; // Close DB connection
     exit();
}


// --- 2. Verify Signature ---
// The string to sign is order_id + "|" + payment_id
$stringToSign = $razorpay_order_id . '|' . $razorpay_payment_id;

// Calculate expected signature using HMAC-SHA256
$expectedSignature = hash_hmac('sha256', $stringToSign, $keySecret);

// Compare expected signature with the received signature
if ($expectedSignature === $razorpay_signature) {
    // Signature is valid! Payment is confirmed by Razorpay.

    // --- 3. Retrieve Temporary Booking Data from Session ---
    // Find the internal booking ID using the razorpay_order_id stored in session
    $internalBookingId = null;
    $bookingData = null;

    // Iterate through session data to find the matching order_id
    foreach ($_SESSION as $key => $data) {
        if (strpos($key, 'booking_in_progress_') === 0 && isset($data['razorpay_order_id']) && $data['razorpay_order_id'] === $razorpay_order_id) {
            $internalBookingId = $data['internal_booking_id'];
            $bookingData = $data;
            break; // Found the data
        }
    }


    if ($internalBookingId && $bookingData) {
         // --- 4. Save Booking to Database ---
         try {
             // Check if a booking with this Razorpay Order ID already exists
             // This prevents duplicate entries if the callback is called multiple times
             $checkSql = "SELECT COUNT(*) FROM bookings WHERE razorpay_order_id = :razorpay_order_id";
             $checkStmt = $conn->prepare($checkSql);
             $checkStmt->bindParam(':razorpay_order_id', $razorpay_order_id);
             $checkStmt->execute();
             if ($checkStmt->fetchColumn() > 0) {
                 // Booking already exists, probably a duplicate callback - acknowledge success but don't re-insert
                 error_log("Duplicate callback received for Order ID: $razorpay_order_id. Booking already exists.");
                 echo json_encode(['status' => 'success', 'message' => 'Booking already processed']);
                 // Clean up session data
                 unset($_SESSION['booking_in_progress_' . $internalBookingId]);
                 $conn = null;
                 exit();
             }


             // If no existing booking, proceed with insert
             $sql = "INSERT INTO bookings (guest_name, guest_phone, guest_email, reservation_date, reservation_time, number_guests, occasion, special_requests, razorpay_order_id, razorpay_payment_id, razorpay_status, razorpay_response, booking_status, booking_fee_status)
                     VALUES (:guest_name, :guest_phone, :guest_email, :reservation_date, :reservation_time, :number_guests, :occasion, :special_requests, :razorpay_order_id, :razorpay_payment_id, :razorpay_status, :razorpay_response, :booking_status, :booking_fee_status)";

             $stmt = $conn->prepare($sql);

             // Bind parameters (using data from session and Razorpay response)
             $stmt->bindParam(':guest_name', $bookingData['guest_name']);
             $stmt->bindParam(':guest_phone', $bookingData['guest_phone']);
             $stmt->bindParam(':guest_email', $bookingData['guest_email']);
             $stmt->bindParam(':reservation_date', $bookingData['reservation_date']);
             $stmt->bindParam(':reservation_time', $bookingData['reservation_time']);
             $stmt->bindParam(':number_guests', $bookingData['number_guests'], PDO::PARAM_INT);
             // Handle empty occasion/special_requests
           // Handle 'occasion': If empty in session data, bind as NULL; otherwise, bind as string
            $occasionToBind = !empty($bookingData['occasion']) ? $bookingData['occasion'] : null;
            $occasionType = ($occasionToBind === null) ? PDO::PARAM_NULL : PDO::PARAM_STR; // Determine type based on value
            $stmt->bindParam(':occasion', $occasionToBind, $occasionType); // Use the variable and the correct type

// Handle 'special_requests': If empty in session data, bind as NULL; otherwise, bind as string
            $specialRequestsToBind = !empty($bookingData['special_requests']) ? $bookingData['special_requests'] : null;
            $specialRequestsType = ($specialRequestsToBind === null) ? PDO::PARAM_NULL : PDO::PARAM_STR; // Determine type based on value
            $stmt->bindParam(':special_requests', $specialRequestsToBind, $specialRequestsType); // Use the variable and the correct type

             // Razorpay Details
             $stmt->bindParam(':razorpay_order_id', $razorpay_order_id);
             $stmt->bindParam(':razorpay_payment_id', $razorpay_payment_id);
             $razorpay_status = "captured"; // Assuming auto-capture is enabled and successful
             $stmt->bindParam(':razorpay_status', $razorpay_status);
             $fullRazorpayResponse = json_encode($paymentResponse); // Store received response
             $stmt->bindParam(':razorpay_response', $fullRazorpayResponse);

             // Update booking statuses
             $booking_status = "Confirmed";
             $booking_fee_status = "Paid";
             $stmt->bindParam(':booking_status', $booking_status);
             $stmt->bindParam(':booking_fee_status', $booking_fee_status);


             // Execute the statement
             $stmt->execute();

             // --- Booking Saved Successfully ---
             // Clean up session data
             unset($_SESSION['booking_in_progress_' . $internalBookingId]);

             // Send success response back to frontend JavaScript
             echo json_encode(['status' => 'success', 'message' => 'Booking confirmed!']);
             $conn = null; // Close DB connection


         } catch(PDOException $e) {
              // Handle database errors during insert *after* successful payment verification
              error_log("Booking Save Error AFTER Verification (Order ID: $razorpay_order_id): " . $e->getMessage());
              // Send JSON error response back to frontend
              echo json_encode(['status' => 'error', 'message' => 'Error saving booking.']);
              $conn = null;
         }

    } else {
        // Session data not found for this Order ID - potential issue or expired session
        error_log("Session data missing for Order ID: $razorpay_order_id");
        echo json_encode(['status' => 'error', 'message' => 'Booking data not found.']);
        $conn = null;
    }


} else {
    // Signature verification failed! Potential tampering.
    error_log("Razorpay Signature verification failed for Order ID: $razorpay_order_id");
    echo json_encode(['status' => 'error', 'message' => 'Payment verification failed.']);
    $conn = null;
}

// Note: Razorpay Webhooks are recommended for robust status updates,
// but this basic flow uses the frontend callback and server-side verification.

?>