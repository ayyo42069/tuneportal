<?php
include 'config.php';
require_auth(true);

// Check if the user ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400); // Bad request
    die(json_encode(['error' => 'Invalid request']));
}

$user_id = (int)$_GET['id'];

try {
    // Get user basic info (including ban_reason) using a prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        http_response_code(404); // Not found
        die(json_encode(['error' => 'User not found']));
    }

    // Get login history using a prepared statement
    $stmt = $conn->prepare("
        SELECT * FROM login_history 
        WHERE user_id = ? 
        ORDER BY attempted_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $login_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get user files using a prepared statement
    $stmt = $conn->prepare("
        SELECT * FROM files 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get transactions using a prepared statement
    $stmt = $conn->prepare("
        SELECT * FROM credit_transactions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get statistics using a prepared statement
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM files WHERE user_id = ?) AS files,
            (SELECT MAX(attempted_at) FROM login_history WHERE user_id = ?) AS last_activity
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'user' => $user, // Includes ban_reason
        'login_history' => $login_history,
        'files' => $files,
        'transactions' => $transactions,
        'stats' => $stats
    ]);
} catch (Exception $e) {
    http_response_code(500); // Internal server error
    die(json_encode(['error' => 'An error occurred: ' . htmlspecialchars($e->getMessage())]));
}
