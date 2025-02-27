<?php
class FileHandler {
    private $conn;
    private $uploadDir;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->uploadDir = dirname(__DIR__) . '/uploads/files/';
        
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function handleUpload($file, $userId, $data) {
        try {
            $this->conn->begin_transaction();

            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Upload failed with error code: " . $file['error']);
            }

            if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
                throw new Exception("File size exceeds limit (10MB)");
            }

            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.bin';
            $filepath = $this->uploadDir . $filename;

            // Insert file record
            $stmt = $this->conn->prepare("
                INSERT INTO files (user_id, title, description, manufacturer_id, model_id, year, ecu_type_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");

            $stmt->bind_param("issiiii", 
                $userId,
                $data['title'],
                $data['description'],
                $data['manufacturer_id'],
                $data['model_id'],
                $data['year'],
                $data['ecu_type_id']
            );
            $stmt->execute();
            $fileId = $this->conn->insert_id;

            // Insert file version
            $stmt = $this->conn->prepare("
                INSERT INTO file_versions (file_id, version, file_path, file_hash)
                VALUES (?, 1, ?, ?)
            ");

            $fileHash = hash_file('sha256', $file['tmp_name']);
            $stmt->bind_param("iss", $fileId, $filename, $fileHash);
            $stmt->execute();

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception("Failed to move uploaded file");
            }

            // Insert tuning options
            if (!empty($data['tuning_options'])) {
                $stmt = $this->conn->prepare("
                    INSERT INTO file_tuning_options (file_id, option_id)
                    VALUES (?, ?)
                ");

                foreach ($data['tuning_options'] as $optionId) {
                    $stmt->bind_param("ii", $fileId, $optionId);
                    $stmt->execute();
                }
            }

            // Notify admins
            $this->notifyAdmins($fileId, $userId);

            $this->conn->commit();
            return $fileId;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function notifyAdmins($fileId, $userId) {
        $stmt = $this->conn->prepare("
            SELECT username FROM users WHERE id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $message = "New file upload from {$user['username']} requires review";
        $link = "admin_files.php?file_id=" . $fileId;

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