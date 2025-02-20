<?php
require_once 'config.php';

// Log the logout event if user is logged in
if (isset($_SESSION['user_id'])) {
    $context = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'unknown'
    ];
    log_error("User logged out", "INFO", $context);
    
    // Remove active session from database
    $session_id = session_id();
    if ($session_id) {
        $stmt = $conn->prepare("DELETE FROM active_sessions WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Clear any other cookies set by the application
setcookie('darkMode', '', time() - 3600, '/');
setcookie('PHPSESSID', '', time() - 3600, '/');

// Redirect to login page
header("Location: login.php");
exit();
?>