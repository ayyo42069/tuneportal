<?php
include 'config.php';

if (isset($_GET['username'])) {
    $username = sanitize($_GET['username']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    echo json_encode(['available' => $stmt->num_rows === 0]);
    $stmt->close();
}
?>