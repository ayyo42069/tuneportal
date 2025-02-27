<?php
include 'config.php';
require_auth(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if ($data && isset($data['action']) && $data['action'] === 'view_user_details') {
        log_error("Admin viewed user details", "INFO", [
            'admin_id' => $_SESSION['user_id'],
            'viewed_user_id' => $data['user_id']
        ]);
    }
}
?>