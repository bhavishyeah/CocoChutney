<?php
// --- Development Error Reporting (TURN OFF IN PRODUCTION) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------------------------

// --- 1. Database Configuration ---
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password (often empty for root in local dev)
$database = "cocochutney_db"; // The name of your database

// Create connection using PDO (generally preferred)
try {
    // Correctly use the $database variable here
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // Set the PDO error mode to exception for better error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode (optional, but often useful)
    // $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Handle database connection errors
    // Log the error for debugging (replace with actual logging mechanism)
    error_log("Database Connection Error: " . $e->getMessage());
    // Show a user-friendly error message
    die("Sorry, there was a problem connecting to the database.");
}

// --- 2. Receive and Validate Form Data ---
// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Initialize an array to store validation errors
    $errors = [];

    // Retrieve and sanitize/validate data
    // Using filter_input is good, but we'll add more specific validation
    $guest_name = trim(filter_input(INPUT_POST, 'guest_name', FILTER_SANITIZE_STRING)); // Use trim to remove leading/trailing whitespace
    $guest_phone = trim(filter_input(INPUT_POST, 'guest_phone', FILTER_SANITIZE_STRING)); // Sanitize, then validate format
    $guest_email = trim(filter_input(INPUT_POST, 'guest_email', FILTER_SANITIZE_EMAIL)); // Sanitize and basic format check
    $reservation_date = filter_input(INPUT_POST, 'reservation_date', FILTER_SANITIZE_STRING); // Sanitize, then validate date format/future
    $reservation_time = filter_input(INPUT_POST, 'reservation_time', FILTER_SANITIZE_STRING); // Sanitize, then validate against allowed times
    // FILTER_VALIDATE_INT returns false on failure or if not an integer
    $number_guests = filter_input(INPUT_POST, 'number_guests', FILTER_VALIDATE_INT);
    $occasion = trim(filter_input(INPUT_POST, 'occasion', FILTER_SANITIZE_STRING));
    $special_requests = trim(filter_input(INPUT_POST, 'special_requests', FILTER_SANITIZE_STRING));

    // Server-side Validation Checks (more robust than just required in HTML)
    if (empty($guest_name)) { $errors[] = "Full Name is required."; }
    // Basic phone format validation - adjust regex if needed for other formats
    if (empty($guest_phone) || !preg_match("/^[0-9]{10}$/", $guest_phone)) { $errors[] = "A valid 10-digit Phone Number is required."; }
    if (empty($guest_email) || !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) { $errors[] = "A valid Email Address is required."; }
    if (empty($reservation_date)) {
        $errors[] = "Reservation Date is required.";
    } else {
        // Optional: Validate date format and ensure it's today or in the future
        $date_obj = DateTime::createFromFormat('Y-m-d', $reservation_date);
        $today = new DateTime('today');
        if (!$date_obj || $date_obj < $today) {
            $errors[] = "Please select a valid future date.";
        }
    }
    if (empty($reservation_time)) {
        $errors[] = "Reservation Time slot is required.";
    } else {
         // Optional: Validate if the selected time is within allowed slots or format
         // For a real system, you'd validate against your available slots.
         // Basic format check:
         if (!preg_match("/^([01]\d|2[0-3]):([0-5]\d)$/", $reservation_time)) {
             $errors[] = "Invalid time format selected.";
         }
    }

    // Check if number_guests is a valid integer within the range
    if ($number_guests === false || $number_guests < 1 || $number_guests > 10) {
        $errors[] = "Number of Guests must be between 1 and 10.";
    }

    // --- Add Availability Check Here (This is a more advanced step) ---
    // Before inserting, you would query the 'bookings' table to see if a table
    // is available for the given date, time, and number of guests.
    // This is complex and depends on your table/seating logic.
    // For now, we'll skip this and just insert the booking request.
    // If ($is_available == false) { $errors[] = "Sorry, no tables available for that time/date."; }


    // --- 3. Insert into Database (if no errors) ---
    if (empty($errors)) {
        try {
            // Prepare SQL statement to prevent SQL injection
            $sql = "INSERT INTO bookings (guest_name, guest_phone, guest_email, reservation_date, reservation_time, number_guests, occasion, special_requests, booking_timestamp, booking_status, booking_fee_status)
                    VALUES (:guest_name, :guest_phone, :guest_email, :reservation_date, :reservation_time, :number_guests, :occasion, :special_requests, :booking_timestamp, :booking_status, :booking_fee_status)";

            $stmt = $conn->prepare($sql);

            // Define default values for timestamp and statuses
            $booking_timestamp = date("Y-m-d H:i:s"); // Record when the booking was made
            $booking_status = "Pending"; // Default status
            $booking_fee_status = "Pending"; // Assuming fee payment happens later

            // Bind parameters to the prepared statement
            $stmt->bindParam(':guest_name', $guest_name);
            $stmt->bindParam(':guest_phone', $guest_phone);
            $stmt->bindParam(':guest_email', $guest_email);
            $stmt->bindParam(':reservation_date', $reservation_date);
            $stmt->bindParam(':reservation_time', $reservation_time);
            $stmt->bindParam(':number_guests', $number_guests, PDO::PARAM_INT); // Specify type
            // Handle empty occasion/special_requests by setting to NULL if they are empty after trim
            // Handle 'occasion': If empty, bind as NULL; otherwise, bind as string
$occasionToBind = !empty($occasion) ? $occasion : null;
$occasionType = ($occasionToBind === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;
$stmt->bindParam(':occasion', $occasionToBind, $occasionType);

// Handle 'special_requests': If empty, bind as NULL; otherwise, bind as string
$specialRequestsToBind = !empty($special_requests) ? $special_requests : null;
$specialRequestsType = ($specialRequestsToBind === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;
$stmt->bindParam(':special_requests', $specialRequestsToBind, $specialRequestsType);
            $stmt->bindParam(':booking_timestamp', $booking_timestamp);
            $stmt->bindParam(':booking_status', $booking_status);
            $stmt->bindParam(':booking_fee_status', $booking_fee_status);


            // Execute the statement
            $stmt->execute();

            // --- 4. Provide Feedback (Success) ---
            // Redirect to a thank you page
            // Make sure you have a thankyou.html page in the same directory
            header("Location: thankyou.html");
            exit(); // Important to prevent further script execution after redirect

        } catch(PDOException $e) {
             // Handle database errors during insertion
             // Log the error (replace with actual logging)
             error_log("Booking Insert Error: " . $e->getMessage());
             // Show a user-friendly generic message
             echo "Sorry, an error occurred while processing your booking. Please try again later.";
             // In development, you might echo $e->getMessage() for debugging, but not in production.
             // echo "Error: " . $e->getMessage();
        }

    } else {
        // --- 4. Provide Feedback (Validation Errors) ---
        // Display validation errors directly on this page
        echo "<h2>Booking Failed:</h2>";
        echo "<p>Please fix the following errors:</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>"; // Sanitize error output before displaying
        }
        echo "</ul>";
        // Provide a link back to the form
        echo "<p><a href='RESERVATION.HTML'>Go back to the form</a></p>";
        // Ideally, you would redirect back to RESERVATION.HTML and pass the errors
        // and submitted data using sessions or GET parameters, then display them
        // on the form itself. This is more advanced.
    }

} else {
    // Not a POST request, redirect to the form or show an error
    header("Location: RESERVATION.HTML");
    exit();
}

// --- 5. Close Connection ---
$conn = null; // Close PDO connection

?>