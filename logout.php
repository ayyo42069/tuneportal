<?php
include 'config.php';

// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

// Unset all session variables
$_SESSION = [];

// Destroy the session
if (session_id() !== '' || isset($_COOKIE[session_name()])) {
    session_destroy();
    // Optionally, clear the session cookie securely
    setcookie(session_name(), '', time() - 3600, '/', '', true, true); // Secure and HttpOnly flags
}

// Redirect to the index page
header("Location: index.php?logout=success");
exit();
?>
