<?php
// Add error reporting for debugging (optional, remove for production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php'; // your connection file

    $name = trim($_POST['name']); // Use trim to remove whitespace
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $cpass = $_POST['confirm_password'];

    // Basic Validation (Add more as needed)
    if (empty($name) || empty($email) || empty($pass)) {
         echo "Please fill in all required fields.";
         exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    if ($pass !== $cpass) {
        echo "Passwords do not match!";
        exit;
    }

    // Optional: Add password strength check here

    // Check if email already exists (assuming you added this as recommended before)
    $checkSql = "SELECT id FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        die("Prepare failed (email check): " . $conn->error); // More robust error handling
    }
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "Email address already registered!";
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close(); // Close the check statement


    // --- Proceed with the INSERT statement ---
    $hashed = password_hash($pass, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
     if (!$stmt) {
        die("Prepare failed (insert): " . $conn->error); // More robust error handling
    }
    $stmt->bind_param("sss", $name, $email, $hashed);

    if ($stmt->execute()) {
        // SUCCESS! Redirect to index.html
        header("Location: index.html");
        exit; // IMPORTANT: Always exit after a header redirect
    } else {
        // Provide more specific error for debugging if possible
        error_log("Signup Error: " . $stmt->error); // Log the error
        echo "An error occurred during signup. Please try again.";
        // For debugging only: echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect non-POST requests or show error
    header("Location: signup.html");
    // echo "Only POST method is allowed.";
    exit;
}
?>