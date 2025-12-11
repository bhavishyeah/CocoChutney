<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Correctly placed at the very beginning

// Enable MySQLi error reporting (keep this for debugging)
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Stricter reporting can be helpful

// Include database connection
include 'db.php'; // Assuming db.php establishes $conn

// Initialize $stmt to null to avoid errors on close if not set
$stmt = null;

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input data
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    // Store the actual submitted password
    $submitted_password = $_POST['password'];

    // Optional: Validate password format if needed (but don't overwrite the variable)
    // $is_password_format_valid = preg_match('/^[a-zA-Z0-9]+$/', $submitted_password);
    // Note: You might want a more robust password policy than just letters/numbers

    // Proceed if email is valid (add password format check if desired)
    // if ($email && $is_password_format_valid) { // Uncomment validation if you keep it
    if ($email) { // Proceeding only with email validation for now
        try {
            // Prepare SQL query
            $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }

            $stmt->bind_param("s", $email);

            // Execute SQL query
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            }
            $result = $stmt->get_result();

            // Check if user exists
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify password using the SUBMITTED password and the HASHED password from DB
                if (password_verify($submitted_password, $user['password'])) {
                    // Password is correct!
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['logged_in'] = true; // <--- ADDED THIS FLAG

                    // Redirect to dashboard.php
                    header('Location: index.html');
                    exit; // Important to exit after redirection
                } else {
                    // Use a generic message for security - don't reveal if email exists but password is wrong
                    echo "Invalid email or password.";
                    // For debugging: echo "Incorrect password.";
                }
            } else {
                // User not found, or more than one user with the same email (shouldn't happen if email is unique)
                echo "Invalid email or password.";
                // For debugging: echo "No user found with that email address.";
            }
        } catch (Exception $e) {
            // Log error (good practice for production)
            error_log("Login Error: " . $e->getMessage());

            // Display generic error message to user
            echo "An error occurred during login. Please try again later.";
            // For debugging: echo "An error occurred: " . $e->getMessage();
        } finally {
            // Close statement and connection if they were opened
            if ($stmt) {
                $stmt->close();
            }
            if ($conn) {
                $conn->close();
            }
        }
    } else {
        echo "Invalid input data provided."; // More specific message if needed
        if ($conn) $conn->close(); // Close connection if opened
    }
} else {
    // If not POST, redirect back to login form
    header('Location: login.php');
    exit;
}
// No need to close connection here if using finally block or closing within conditions
?>