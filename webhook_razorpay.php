<?php
// --- Development Error Reporting (TURN OFF IN PRODUCTION) ---
// In production, DISABLE display_errors and log errors to a file instead
ini_set('display_errors', 0); // Set to 0 in production
ini_set('display_startup_errors', 0); // Set to 0 in production
error_reporting(E_ALL); // Keep logging level high
ini_set('log_errors', 1); // Enable error logging
ini_set('error_log', '/path/to/your/php_error.log'); // Set a path for your error log file
// ---------------------------------------------------------

// Database Configuration (Optional - include if you have a config file)
// require_once 'db_config.php'; // If you have a separate config file

// Database Connection (If not using a config file)
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$database = "cocochutney_db"; // The name of your database

// --- Razorpay Webhook Secret ---
// You MUST get this secret from your Razorpay Dashboard -> Webhooks settings.
// This is DIFFERENT from your API Key Secret.
$webhookSecret = "YOUR_RAZORPAY_WEBHOOK_SECRET"; // Replace with your actual Webhook Secret

// --- 1. Get the raw POST data from Razorpay ---
$webhookBody = file_get_contents('php://input');
$webhookHeaders = getallheaders(); // Get all request headers

// Ensure we got some data
if (empty($webhookBody)) {
    error_log("Razorpay Webhook Error: Empty request body received.");
    http_response_code(400); // Bad Request
    exit();
}

// Decode the JSON payload
$data = json_decode($webhookBody, true);

// Ensure JSON decoding was successful and basic structure exists
if ($data === null || !isset($data['event'], $data['payload'])) {
     error_log("Razorpay Webhook Error: Invalid JSON payload or structure.");
     http_response_code(400); // Bad Request
     exit();
}

// --- 2. Get the Razorpay Signature from the headers ---
// Header name can vary in casing, check common variations
$razorpaySignature = null;
if (isset($webhookHeaders['X-Razorpay-Signature'])) {
    $razorpaySignature = $webhookHeaders['X-Razorpay-Signature'];
} elseif (isset($webhookHeaders['x-razorpay-signature'])) { // Check lowercase
     $razorpaySignature = $webhookHeaders['x-razorpay-signature'];
}

if (!$razorpaySignature) {
    error_log("Razorpay Webhook Error: Signature header missing.");
    http_response_code(400); // Bad Request
    exit();
}


// --- 3. Verify the Signature ---
// Calculate the expected signature
$expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);

// Compare the calculated signature with the signature received from Razorpay
if ($expectedSignature !== $razorpaySignature) {
    // Signature mismatch! This request is NOT from Razorpay or has been tampered with.
    error_log("Razorpay Webhook Error: Signature verification failed!");
    http_response_code(400); // Bad Request (or 403 Forbidden)
    exit();
}

// --- Signature Verified! Process the event ---
error_log("Razorpay Webhook: Signature verified for event '" . $data['event'] . "'"); // Log successful verification

// --- 4. Process the Event Type ---
$event = $data['event'];

switch ($event) {
    case 'payment.captured':
        // A payment has been successfully captured.
        // This is typically the event you want to listen for to confirm a booking.

        $payment = $data['payload']['payment']['entity']; // Get the payment entity details
        $order = $data['payload']['order']['entity'];     // Get the order entity details (linked to payment)

        $razorpay_payment_id = $payment['id'];
        $razorpay_order_id = $order['id']; // This should match the order_id you created
        $payment_amount = $payment['amount']; // Amount in paisa
        $payment_currency = $payment['currency'];
        $payment_status = $payment['status']; // Should be 'captured'
        $internal_booking_id = isset($order['receipt']) ? $order['receipt'] : null; // Retrieve your internal ID from the order receipt

        error_log("Razorpay Webhook: Processing payment.captured for Order ID: $razorpay_order_id, Payment ID: $razorpay_payment_id, Internal ID: $internal_booking_id");

        // --- 5. Connect to Database and Update Booking Status ---
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Implement Idempotency: Check if this booking is already confirmed via this order ID
            $checkSql = "SELECT COUNT(*) FROM bookings WHERE razorpay_order_id = :razorpay_order_id AND booking_status = 'Confirmed'";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(':razorpay_order_id', $razorpay_order_id);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                // Booking already confirmed, likely a duplicate webhook - do nothing further
                error_log("Razorpay Webhook: Duplicate payment.captured received for Order ID $razorpay_order_id. Booking already confirmed.");
            } else {
                 // Booking not yet confirmed for this order - proceed with update
                 $updateSql = "UPDATE bookings
                               SET booking_status = 'Confirmed',
                                   booking_fee_status = 'Paid',
                                   razorpay_payment_id = :razorpay_payment_id,
                                   razorpay_status = :razorpay_status,
                                   razorpay_response = :razorpay_response -- Store the full payload or relevant parts
                               WHERE razorpay_order_id = :razorpay_order_id
                               AND (booking_status IS NULL OR booking_status = 'Pending Payment')"; // Ensure you only update if not already confirmed/failed

                 $updateStmt = $conn->prepare($updateSql);

                 $updateStmt->bindParam(':razorpay_payment_id', $razorpay_payment_id);
                 $updateStmt->bindParam(':razorpay_status', $payment_status);
                 $fullPayload = json_encode($data); // Store the full webhook payload
                 $updateStmt->bindParam(':razorpay_response', $fullPayload);
                 $updateStmt->bindParam(':razorpay_order_id', $razorpay_order_id);

                 $updateStmt->execute();

                 if ($updateStmt->rowCount() > 0) {
                     error_log("Razorpay Webhook: Booking successfully updated to Confirmed for Order ID: $razorpay_order_id");
                     // Trigger internal processes if any (e.g., send confirmation email)
                 } else {
                     // RowCount is 0 - means no booking found with that razorpay_order_id
                     // Or it was already in a state like Failed/Cancelled
                     error_log("Razorpay Webhook Warning: No booking found or updated for Order ID: $razorpay_order_id. Check if order ID exists or status was already non-pending.");
                 }
            }

        } catch(PDOException $e) {
            // Handle database errors during the update
            error_log("Razorpay Webhook Database Error for Order ID $razorpay_order_id: " . $e->getMessage());
            // Don't exit with error code immediately, just log. Razorpay expects 200 if signature verified.
        } finally {
            // Close database connection
            $conn = null;
        }

        break;

    case 'payment.failed':
        // A payment has failed.
        $payment = $data['payload']['payment']['entity'];
        $order = $data['payload']['order']['entity'];

        $razorpay_payment_id = $payment['id']; // May be null for some failures
        $razorpay_order_id = $order['id'];
        $payment_status = $payment['status']; // Should be 'failed'

        error_log("Razorpay Webhook: Processing payment.failed for Order ID: $razorpay_order_id, Payment ID: $razorpay_payment_id");

        // Optional: Update booking status to 'Failed' in DB using razorpay_order_id
        // This is important for tracking failed attempts
        try {
             $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
             $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

             $updateSql = "UPDATE bookings
                           SET booking_status = 'Payment Failed',
                               booking_fee_status = 'Failed',
                               razorpay_payment_id = :razorpay_payment_id,
                               razorpay_status = :razorpay_status,
                               razorpay_response = :razorpay_response
                           WHERE razorpay_order_id = :razorpay_order_id
                           AND (booking_status IS NULL OR booking_status = 'Pending Payment')"; // Only update if still pending

             $updateStmt = $conn->prepare($updateSql);

             $updateStmt->bindParam(':razorpay_payment_id', $razorpay_payment_id);
             $updateStmt->bindParam(':razorpay_status', $payment_status);
             $fullPayload = json_encode($data);
             $updateStmt->bindParam(':razorpay_response', $fullPayload);
             $updateStmt->bindParam(':razorpay_order_id', $razorpay_order_id);

             $updateStmt->execute();

             if ($updateStmt->rowCount() > 0) {
                  error_log("Razorpay Webhook: Booking status updated to 'Payment Failed' for Order ID: $razorpay_order_id");
             } else {
                  error_log("Razorpay Webhook Warning: No pending booking found or updated for failed payment Order ID: $razorpay_order_id");
             }

        } catch(PDOException $e) {
             error_log("Razorpay Webhook DB Error for failed payment Order ID $razorpay_order_id: " . $e->getMessage());
        } finally {
             $conn = null;
        }

        break;

    // Add more cases for other events if needed (e.g., 'refund.processed', 'payment.authorized')
    default:
        // Handle other events you might have subscribed to but don't need specific actions for
        error_log("Razorpay Webhook: Received unhandled event type: " . $event);
        break;
}

// --- 6. Send Success Response to Razorpay ---
// Razorpay expects a 200 OK response if the webhook is successfully received and processed.
// This indicates to Razorpay that they don't need to retry sending this event.
// Only send 200 if signature verification passed, otherwise send 400.
http_response_code(200);
exit();

?>