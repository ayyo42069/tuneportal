<?php
session_start();

// Database credentials
$host = "localhost";
$user = "tuneadmin";
$pass = "Bajnok123$";
$db = "tuneportaldb";

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to require authentication (and optionally admin access)
function require_auth($admin = false) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    if ($admin && $_SESSION['role'] !== 'admin') {
        header("HTTP/1.1 403 Forbidden");
        exit("Admin access required");
    }
}
?>
