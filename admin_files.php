<?php
include 'config.php';
require_auth(true); // Admin only

// Generate CSRF token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle file processing and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        log_error("Invalid CSRF token", "WARNING", ['admin_id' => $_SESSION['user_id']]);
        die(json_encode(['error' => 'Invalid CSRF token.']));
    }

    // Handle deletion
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $file_id = (int)$_POST['file_id'];
        
        $conn->begin_transaction();
        
        try {
            // Get all file versions to delete physical files
            $stmt = $conn->prepare("SELECT file_path, file_hash FROM file_versions WHERE file_id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $versions = $stmt->get_result();
            
            while ($version = $versions->fetch_assoc()) {
                $filepath = __DIR__ . '/uploads/' . $version['file_path'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                    log_error("File deleted", "INFO", [
                        'file_id' => $file_id,
                        'path' => $filepath,
                        'hash' => $version['file_hash']
                    ]);
                }
            }
            $stmt->close();
            
            // Delete all associated records
            $stmt = $conn->prepare("DELETE FROM file_versions WHERE file_id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM update_requests WHERE file_id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $_SESSION['success'] = "File and all associated data deleted successfully";
            
        } catch (Exception $e) {
            $conn->rollback();
            log_error("File deletion failed", "ERROR", [
                'file_id' => $file_id,
                'error' => $e->getMessage()
            ]);
            $_SESSION['error'] = "Error deleting file: " . htmlspecialchars($e->getMessage());
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Handle file processing
    if (isset($_FILES['processed_file'])) {
        $file_id = (int)$_POST['file_id'];
        $user_id = (int)$_POST['user_id'];
        
        if (!empty($_FILES['processed_file']['name'])) {
            list($valid, $message) = validate_file($_FILES['processed_file']);
            if (!$valid) {
                $_SESSION['error'] = $message;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }

            $conn->begin_transaction();
            
            try {
                // Get current version
                $stmt = $conn->prepare("SELECT current_version FROM files WHERE id = ?");
                $stmt->bind_param("i", $file_id);
                $stmt->execute();
                $current = $stmt->get_result()->fetch_assoc();
                $new_version = $current['current_version'] + 1;
                
                $uploadDir = __DIR__ . '/uploads/';
                $filename = "processed_{$file_id}_v{$new_version}.bin";
                
                // Encrypt and store the file
                if (!encrypt_file($_FILES['processed_file']['tmp_name'], $uploadDir . $filename)) {
                    throw new Exception("Failed to encrypt file");
                }

                // Calculate file hash
                $file_hash = hash_file('sha256', $uploadDir . $filename);
                
                // Update database with new version and hash
                $stmt = $conn->prepare("INSERT INTO file_versions (file_id, version, file_path, file_hash) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $file_id, $new_version, $filename, $file_hash);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE files SET status = 'processed', current_version = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_version, $file_id);
                $stmt->execute();
                
                // Create notification
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
                $message = "Your file #$file_id has been processed!";
                $link = "file_details.php?id=$file_id";
                $stmt->bind_param("iss", $user_id, $message, $link);
                $stmt->execute();
                $stmt->close();
                
                $conn->commit();
                $_SESSION['success'] = "File processed successfully";
                
            } catch (Exception $e) {
                $conn->rollback();
                log_error("File processing failed", "ERROR", [
                    'file_id' => $file_id,
                    'error' => $e->getMessage()
                ]);
                $_SESSION['error'] = "Error processing file: " . htmlspecialchars($e->getMessage());
                
                if (file_exists($uploadDir . $filename)) {
                    unlink($uploadDir . $filename);
                }
            }
        }
    }
}

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-red-600 dark:text-red-400 mb-6">Manage User Files</h2>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-red-50 dark:bg-red-900">
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">File ID</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">User</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Title</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Status</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Version</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("
                                SELECT f.*, u.username, fv.file_path 
                                FROM files f
                                JOIN users u ON f.user_id = u.id
                                JOIN file_versions fv ON f.id = fv.file_id AND f.current_version = fv.version
                                ORDER BY f.created_at DESC
                            ");
                            $stmt->execute();
                            $files = $stmt->get_result();
                            
                            while ($file = $files->fetch_assoc()):
                            ?>
                            <tr class="border-b dark:border-gray-700">
                                <td class="p-3 text-gray-700 dark:text-gray-300">#<?= htmlspecialchars($file['id']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($file['username']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($file['title']) ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' ?>">
                                        <?= ucfirst(htmlspecialchars($file['status'])) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-gray-700 dark:text-gray-300">v<?= htmlspecialchars($file['current_version']) ?></td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <?php if(!empty($file['file_path'])): ?>
                                            <a href="uploads/<?= htmlspecialchars($file['file_path']) ?>" 
                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                               download>
                                                Download
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">No file</span>
                                        <?php endif; ?>
                                        
                                        <form method="POST" enctype="multipart/form-data" 
                                              class="flex items-center gap-2">
                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                            <input type="hidden" name="user_id" value="<?= $file['user_id'] ?>">
                                            <input type="file" name="processed_file" 
                                                   class="text-sm" accept=".bin">
                                                   <?php echo csrf_input_field(); ?>
                                            <button type="submit" 
                                                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                                Process
                                            </button>
                                        </form>
                                        
                                        <form method="POST" 
                                              class="ml-2"
                                              onsubmit="return confirm('Are you sure you want to permanently delete this file and all its versions? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                            <?php echo csrf_input_field(); ?>
                                            <button type="submit" 
                                                    class="bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
