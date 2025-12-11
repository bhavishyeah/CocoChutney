<?php
// --- Development Error Reporting (TURN OFF IN PRODUCTION) ---
ini_set('display_errors', 1); // Set to 0 in production
ini_set('display_startup_errors', 1); // Set to 0 in production
error_reporting(E_ALL); // Keep logging level high
ini_set('log_errors', 1); // Enable error logging
ini_set('error_log', '/path/to/your/php_contact_error.log'); // Set a path for your error log file
// ---------------------------------------------------------

// --- Database Configuration ---
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password (often empty for root in local dev)
$database = "cocochutney_db"; // The name of your database

// --- Email Recipient Configuration ---
$recipientEmail = "your_email@example.com"; // <--- REPLACE WITH YOUR EMAIL ADDRESS

// --- Database Connection ---
try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set charset to utf8mb4 for broader character support
    $conn->exec("SET NAMES utf8mb4");

} catch(PDOException $e) {
    // Handle database connection errors
    error_log("Contact Form DB Connection Error: " . $e->getMessage());
    // Show a user-friendly error message
    // In production, you might show a generic error page
    die("Sorry, there was a problem connecting to the database.");
}


// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Initialize an array to store validation errors
    $errors = [];

    // --- 1. Receive and Sanitize Form Data ---
    // Use filter_input for basic sanitization
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
    // Use FILTER_UNSAFE_RAW for message body to retain formatting, but be very careful displaying this later
    // For simple storage, FILTER_SANITIZE_STRING is safer if you don't need HTML
    // Let's stick to SANITIZE_STRING for safety unless specific formatting is needed
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));


    // --- 2. Server-side Validation Checks ---
    if (empty($name)) { $errors[] = "Name is required."; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "A valid Email Address is required."; }
    if (empty($subject)) { $errors[] = "Subject is required."; }
    if (empty($message)) { $errors[] = "Message is required."; }

    // --- 3. Process Data (Save to DB and Send Email) if no errors ---
    if (empty($errors)) {

        // --- Save to Database ---
        try {
            // Prepare SQL statement to prevent SQL injection
            $sql = "INSERT INTO contact_messages (name, email, subject, message)
                    VALUES (:name, :email, :subject, :message)";

            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);

            // Execute the statement
            $stmt->execute();

            // Get the ID of the newly inserted message (optional)
            $lastInsertId = $conn->lastInsertId();
            error_log("Contact form message saved to DB with ID: " . $lastInsertId); // Log success

        } catch(PDOException $e) {
             // Handle database errors during insertion
             error_log("Contact Form DB Insert Error: " . $e->getMessage());
             // Decide how to handle this:
             // Option A: Stop processing and show DB error (less user friendly)
             // die("Sorry, a database error occurred while saving your message.");
             // Option B: Log the error and *attempt* to send the email anyway (more robust)
             // We will proceed with sending email in this example.
             $dbErrorOccurred = true; // Flag that a DB error happened
        }


        // --- Send the email ---
        // Prepare the email details
        $emailSubject = "New Contact Form Submission: " . $subject;
        $emailBody = "Name: " . $name . "\n";
        $emailBody .= "Email: " . $email . "\n";
        $emailBody .= "Subject: " . $subject . "\n\n";
        $emailBody .= "Message:\n" . $message;

        // Email headers
        $headers = "From: " . $name . " <" . $email . ">\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n"; // Ensure correct content type

        // Using PHP's built-in mail() function
        $mailSent = mail($recipientEmail, $emailSubject, $emailBody, $headers);

        // --- 4. Provide Feedback ---
        // Redirect based on whether email was sent AND DB save was successful (or attempted)
        if ($mailSent && !isset($dbErrorOccurred)) {
            // Email sent and DB save successful
            header("Location: thankyou_contact.html"); // Create a new thank you page for contact form
            exit();
        } elseif ($mailSent && isset($dbErrorOccurred)) {
             // Email sent, but DB save failed - notify admin via log, maybe show slightly different message?
             // For simplicity, still redirect to thank you, but the log will show the DB error.
             header("Location: thankyou_contact.html");
             exit();
        }
        else {
            // Email sending failed (regardless of DB save success/failure)
            error_log("Contact Form Email Failed to Send. Recipient: $recipientEmail, Subject: $emailSubject");
            // Show a user-friendly message indicating email failure
            echo "<h2>Message Sending Failed:</h2>";
            echo "<p>Sorry, there was a problem sending your message via email. Your message may have been saved internally.</p>";
            echo "<p>Please try again later or contact us via phone.</p>";
            // In development, you might add: echo "<p>PHP mail() failed. Check server logs.</p>";
            echo "<p><a href='contact.html'>Go back to the form</a></p>";
        }

    } else {
        // --- 4. Provide Feedback (Validation Errors) ---
        echo "<h2>Message Sending Failed:</h2>";
        echo "<p>Please fix the following errors:</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>"; // Sanitize error output
        }
        echo "</ul>";
        // Provide a link back to the form
        echo "<p><a href='contact.html'>Go back to the form</a></p>";
        // Ideally, you'd redirect back to contact.html and repopulate fields
        // with the submitted data and display errors there.
    }

} else {
    // Not a POST request, redirect to the form
    header("Location: contact.html");
    exit();
}

// --- Close Database Connection ---
// This will be reached if the script doesn't exit early
$conn = null;

?>
