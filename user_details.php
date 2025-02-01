<?php
include 'config.php';
require_auth(true);

// Check if the user ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400); // Bad request
    die(json_encode(['error' => 'User ID is required']));
}

$user_id = (int)$_GET['id'];

try {
    // Fetch user details
    $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
    if (!$user) {
        http_response_code(404); // Not found
        die(json_encode(['error' => 'User not found']));
    }

    // Fetch login history (last 10 entries)
    $login_history = $conn->query("
        SELECT * FROM login_history 
        WHERE user_id = $user_id 
        ORDER BY attempted_at DESC 
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);

    // Fetch user files (last 10 entries)
    $files = $conn->query("
        SELECT * FROM files 
        WHERE user_id = $user_id 
        ORDER BY created_at DESC 
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);

    // Fetch credit transactions (last 10 entries)
    $transactions = $conn->query("
        SELECT * FROM credit_transactions 
        WHERE user_id = $user_id 
        ORDER BY created_at DESC 
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);

    // Fetch statistics
    $stats = $conn->query("
        SELECT 
            (SELECT COUNT(*) FROM files WHERE user_id = $user_id) AS files,
            (SELECT MAX(attempted_at) FROM login_history WHERE user_id = $user_id) AS last_activity
    ")->fetch_assoc();

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'user' => $user,
        'login_history' => $login_history,
        'files' => $files,
        'transactions' => $transactions,
        'stats' => $stats
    ]);
} catch (Exception $e) {
    http_response_code(500); // Internal server error
    die(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
}