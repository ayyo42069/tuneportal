<?php
include 'config.php';
require_auth(true);

if (!isset($_GET['id'])) {
    http_response_code(400);
    die("Invalid request");
}

$request_id = (int)$_GET['id'];

$request = $conn->query("
    SELECT ur.*, f.title AS file_title, u.username, u.email,
           fv.file_path, fv.uploaded_at AS file_updated
    FROM update_requests ur
    JOIN files f ON ur.file_id = f.id
    JOIN users u ON ur.user_id = u.id
    LEFT JOIN file_versions fv ON f.id = fv.file_id AND f.current_version = fv.version
    WHERE ur.id = $request_id
")->fetch_assoc();

if (!$request) {
    http_response_code(404);
    die("Request not found");
}

header('Content-Type: application/json');
echo json_encode($request);