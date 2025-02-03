<?php
include 'config.php';

// Unset all session variables
$_SESSION = [];

// Destroy the session
if (session_id() !== '' || isset($_COOKIE[session_name()])) {
    session_destroy();
    // Optionally, clear the session cookie
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to the index page
header("Location: index.php?logout=success");
exit();
?>
