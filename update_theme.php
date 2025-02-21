<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['theme'])) {
    $_SESSION['dark_mode'] = ($data['theme'] === 'dark') ? 1 : 0;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid theme']);
}
?>