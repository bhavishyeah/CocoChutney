<?php
// api/save_address.php

// Temporarily disable display errors for production API behavior
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); // Still log all errors, just don't display them

// Rest of your file...

// Start the session to access $_SESSION['user_id']
session_start();

// --- IMPORTANT: Session Check ---
// We need to ensure the user is logged in before saving an address.
// If you created includes/check_login.php, you can include it here:
// include_once '../includes/check_login.php'; // Adjust path based on your file structure

// Manual check if not using check_login.php
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    // User is not logged in. Send an error response.
    http_response_code(401); // Unauthorized
    echo json_encode(["success" => false, "message" => "Unauthorized: Please log in to save addresses."]);
    exit();
}

// Include database connection
// Adjust path if db.php is in a different directory (e.g., '../db.php' if db.php is in parent folder)
include 'db.php'; // <--- IMPORTANT: Adjust this path as needed

// Set content type header to JSON for API response
header('Content-Type: application/json');

// Initialize response array
$response = ["success" => false, "message" => "An unknown error occurred."];

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the raw POST data (JSON from fetch API)
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true); // Decode JSON into an associative array

    // Validate input data
    if ($data === null) {
        $response['message'] = "Invalid JSON data received.";
        http_response_code(400); // Bad Request
    } else {
        // Assign data to variables, with null coalescing for optional fields
        $user_id = $_SESSION['user_id']; // Get user ID from session
        $address_type = $data['addressType'] ?? '';
        $full_name = $data['fullName'] ?? '';
        $flat_house_no = $data['flatHouseNo'] ?? '';
        $building_name = $data['buildingName'] ?? '';
        $street_area = $data['streetArea'] ?? '';
        $city = $data['city'] ?? '';
        $state = $data['state'] ?? '';
        $pincode = $data['pincode'] ?? '';
        $landmark = $data['landmark'] ?? null; // Optional, can be null
        $contact_number = $data['contactNumber'] ?? '';

        // Basic server-side validation (add more as needed)
        if (empty($address_type) || empty($full_name) || empty($flat_house_no) ||
            empty($building_name) || empty($street_area) || empty($city) ||
            empty($state) || empty($pincode) || empty($contact_number)) {
            
            $response['message'] = "All required address fields must be filled.";
            http_response_code(400); // Bad Request
        } else {
            // Prepare an SQL INSERT statement
            $stmt = null; // Initialize statement for finally block
// ... (previous code) ...

            // --- TEMPORARY DEBUGGING LINE ---
            if ($conn === null) {
                error_log("DEBUG: \$conn is NULL right before prepare() call in api/save_address.php");
                $response['message'] = "DEBUG: Database connection is null. Check db.php or its include.";
                http_response_code(500); // Internal Server Error
                echo json_encode($response);
                exit(); // Stop execution here to see the debug message
            }
            try {
                $sql = "INSERT INTO user_addresses (user_id, address_type, full_name, flat_house_no, building_name, street_area, city, state, pincode, landmark, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }

                $stmt->bind_param("issssssssss", // 'i' for integer (user_id), 's' for string
                    $user_id,
                    $address_type,
                    $full_name,
                    $flat_house_no,
                    $building_name,
                    $street_area,
                    $city,
                    $state,
                    $pincode,
                    $landmark,
                    $contact_number
                );

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "Address saved successfully!";
                    $response['address_id'] = $conn->insert_id; // Get the ID of the newly inserted address
                    http_response_code(201); // Created
                } else {
                    throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                }
            } catch (Exception $e) {
                // Log the error
                error_log("Error saving address: " . $e->getMessage());
                $response['message'] = "Failed to save address: " . $e->getMessage(); // More detailed for debugging
                http_response_code(500); // Internal Server Error
            } finally {
                if ($stmt) {
                    $stmt->close();
                }
                if ($conn) {
                    $conn->close();
                }
            }
        }
    }
} else {
    // Not a POST request
    $response['message'] = "Invalid request method. Only POST is allowed.";
    http_response_code(405); // Method Not Allowed
}

echo json_encode($response);
?>