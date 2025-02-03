<?php
include 'config.php';
require_auth(true);

// Check if the request ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400); // Bad request
    die(json_encode(['error' => 'Invalid request']));
}

$request_id = (int)$_GET['id'];

try {
    // Fetch request details using a prepared statement
    $stmt = $conn->prepare("
        SELECT ur.*, f.title AS file_title, u.username, u.email,
               fv.file_path, fv.uploaded_at AS file_updated
        FROM update_requests ur
        JOIN files f ON ur.file_id = f.id
        JOIN users u ON ur.user_id = u.id
        LEFT JOIN file_versions fv ON f.id = fv.file_id AND f.current_version = fv.version
        WHERE ur.id = ?
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$request) {
        http_response_code(404); // Not found
        die(json_encode(['error' => 'Request not found']));
    }

    // Return the request details as JSON
    header('Content-Type: application/json');
    echo json_encode($request);
} catch (Exception $e) {
    http_response_code(500); // Internal server error
    die(json_encode(['error' => 'An error occurred: ' . htmlspecialchars($e->getMessage())]));
}
