<?php
include 'config.php';
require_auth(true);

$files = $conn->query("
    SELECT 
        f.*, 
        u.username,
        (SELECT COUNT(*) FROM file_versions WHERE file_id = f.id) as version_count,
        (SELECT COUNT(*) FROM file_download_log WHERE file_id = f.id) as download_count
    FROM files f
    LEFT JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC
");

$result = [];
while ($file = $files->fetch_assoc()) {
    $result[] = $file;
}

header('Content-Type: application/json');
echo json_encode($result);