<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'light';
    $_SESSION['dark_mode'] = ($theme === 'dark') ? 1 : 0;

    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE user_preferences SET dark_mode = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $_SESSION['dark_mode'], $_SESSION['user_id']);
        $stmt->execute();
    }

    echo json_encode(['success' => true]);
}