<?php
include 'config.php';
require_auth(true);

if (!isset($_GET['id'])) {
    http_response_code(400);
    die("Invalid request");
}

$user_id = (int)$_GET['id'];

// Get user basic info (including ban_reason)
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Get login history
$login_history = $conn->query("
    SELECT * FROM login_history 
    WHERE user_id = $user_id 
    ORDER BY attempted_at DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get user files
$files = $conn->query("
    SELECT * FROM files 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get transactions
$transactions = $conn->query("
    SELECT * FROM credit_transactions 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM files WHERE user_id = $user_id) AS files,
        (SELECT MAX(attempted_at) FROM login_history WHERE user_id = $user_id) AS last_activity
")->fetch_assoc();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'user' => $user, // Includes ban_reason
    'login_history' => $login_history,
    'files' => $files,
    'transactions' => $transactions,
    'stats' => $stats
]);