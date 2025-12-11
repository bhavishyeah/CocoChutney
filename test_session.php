<?php
session_start();
echo "<h1>Session Test</h1>";
if (isset($_SESSION['user_id'])) {
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>User Name: " . $_SESSION['user_name'] . "</p>";
    echo "<p>Logged In: " . ($_SESSION['logged_in'] ? 'Yes' : 'No') . "</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
} else {
    echo "<p>No user session found.</p>";
}
echo "<p><a href='login.php'>Go to Login</a></p>";
echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
echo "<p><a href='logout.php'>Logout</a></p>"; // We'll create this later
?>