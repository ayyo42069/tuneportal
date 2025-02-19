<?php
class RequestHandler {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createUpdateRequest($fileId, $userId, $message) {
        try {
            $this->conn->begin_transaction();

            $stmt = $this->conn->prepare("
                INSERT INTO update_requests (file_id, user_id, message, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->bind_param("iis", $fileId, $userId, $message);
            $stmt->execute();
            $requestId = $this->conn->insert_id;

            // Notify admins
            $this->notifyAdmins($requestId, $fileId, $userId);

            $this->conn->commit();
            return $requestId;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function notifyAdmins($requestId, $fileId, $userId) {
        $stmt = $this->conn->prepare("
            SELECT u.username, f.title 
            FROM users u 
            JOIN files f ON f.id = ?
            WHERE u.id = ?
        ");
        $stmt->bind_param("ii", $fileId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $message = "New update request from {$data['username']} for file: {$data['title']}";
        $link = "admin_requests.php?request_id=" . $requestId;

        $stmt = $this->conn->prepare("
            INSERT INTO notifications (user_id, message, link)
            SELECT id, ?, ?
            FROM users
            WHERE role = 'admin'
        ");
        $stmt->bind_param("ss", $message, $link);
        $stmt->execute();
    }
}