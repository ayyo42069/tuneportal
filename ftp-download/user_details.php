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
    // Fetch user details using a prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        http_response_code(404); // Not found
        die(json_encode(['error' => 'User not found']));
    }

    // Fetch login history (last 10 entries) using a prepared statement
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

    // Fetch user files (last 10 entries) using a prepared statement
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

    // Fetch credit transactions (last 10 entries) using a prepared statement
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

    // Fetch statistics using a prepared statement
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM files WHERE user_id = ?) AS files,
            (SELECT MAX(attempted_at) FROM login_history WHERE user_id = ?) AS last_activity
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

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
    die(json_encode(['error' => 'An error occurred: ' . htmlspecialchars($e->getMessage())]));
}
