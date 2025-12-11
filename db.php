<?php
// db.php
$servername = "localhost";
$username = "root"; // Your MySQL username (e.g., "root" for XAMPP default)
$password = "";     // Your MySQL password (e.g., "" for XAMPP default)
$dbname = "cocochutney_db"; // <--- IMPORTANT: CHANGE THIS TO YOUR ACTUAL DATABASE NAME

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // For debugging, you might die with error:
    // die("Connection failed: " . $conn->connect_error);
    // In production, log error and show generic message:
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "message" => "Database connection error."]);
    exit();
}

// Set charset to utf8mb4 (recommended for modern applications)
$conn->set_charset("utf8mb4");

// Enable MySQLi error reporting if you want stricter debugging
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>