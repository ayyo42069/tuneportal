<?php
include 'config.php';
require_auth(true); // Admin only

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        log_error("Invalid CSRF token", "WARNING", ['admin_id' => $_SESSION['user_id']]);
        die(json_encode(['error' => 'Invalid CSRF token.']));
    }

    // Handle file processing
    if (isset($_FILES['processed_file'])) {
        $file_id = (int)$_POST['file_id'];
        $user_id = (int)$_POST['user_id'];
        
        try {
            if (empty($_FILES['processed_file']['name'])) {
                throw new Exception("No file selected");
            }

            if ($_FILES['processed_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File upload failed with error code: " . $_FILES['processed_file']['error']);
            }

            list($valid, $message) = validate_file($_FILES['processed_file']);
            if (!$valid) {
                throw new Exception($message);
            }

            $conn->begin_transaction();

            // Get current version
            $stmt = $conn->prepare("SELECT current_version, title FROM files WHERE id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $file_info = $stmt->get_result()->fetch_assoc();
            
            if (!$file_info) {
                throw new Exception("File not found");
            }

            $new_version = $file_info['current_version'] + 1;
            $uploadDir = __DIR__ . '/uploads/';
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
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

            // Update file status and version
            $stmt = $conn->prepare("UPDATE files SET status = 'processed', current_version = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_version, $file_id);
            $stmt->execute();

            // Notify user
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, link)
                VALUES (?, ?, ?)
            ");
            $message = "Your file '{$file_info['title']}' has been processed";
            $link = "file_details.php?id=" . $file_id;
            $stmt->bind_param("iss", $user_id, $message, $link);
            $stmt->execute();

            $conn->commit();
            $_SESSION['success'] = "File processed successfully";

        } catch (Exception $e) {
            $conn->rollback();
            log_error("File processing failed", "ERROR", [
                'file_id' => $file_id,
                'error' => $e->getMessage()
            ]);
            $_SESSION['error'] = "Error processing file: " . htmlspecialchars($e->getMessage());
            
            // Clean up uploaded file if it exists
            if (isset($uploadDir, $filename) && file_exists($uploadDir . $filename)) {
                unlink($uploadDir . $filename);
            }
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
   // Handle deletion
   if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $file_id = (int)$_POST['file_id'];
    
    $conn->begin_transaction();
    
    try {
        // Delete in correct order to handle foreign key constraints
        // First, delete download logs
        $stmt = $conn->prepare("DELETE FROM file_download_log WHERE file_id = ?");
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();

        // Delete file tuning options
        $stmt = $conn->prepare("DELETE FROM file_tuning_options WHERE file_id = ?");
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();

        // Delete update requests
        $stmt = $conn->prepare("DELETE FROM update_requests WHERE file_id = ?");
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();

        // Get file paths before deleting versions
        $stmt = $conn->prepare("SELECT file_path FROM file_versions WHERE file_id = ?");
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $versions = $stmt->get_result();
        $filesToDelete = [];
        while ($version = $versions->fetch_assoc()) {
            $filesToDelete[] = __DIR__ . '/uploads/' . $version['file_path'];
        }
        $stmt->close();

        // Delete file versions
        $stmt = $conn->prepare("DELETE FROM file_versions WHERE file_id = ?");
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();

        // Finally delete the file record
        $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();

        // Delete physical files
        foreach ($filesToDelete as $filepath) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
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
                // Replace hardcoded messages with translations
                $_SESSION['success'] = __('file_uploaded', 'notifications');
                $_SESSION['error'] = __('error', 'notifications') . ": " . htmlspecialchars($e->getMessage());
                
                if (file_exists($uploadDir . $filename)) {
                    unlink($uploadDir . $filename);
                }
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
            <!-- Stats Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <?php
                $stats = $conn->query("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END) as processed
                    FROM files
                ")->fetch_assoc();
                ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Total Files</h3>
                            <p class="text-2xl font-bold text-blue-600"><?= $stats['total'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Pending Files</h3>
                            <p class="text-2xl font-bold text-yellow-600"><?= $stats['pending'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Processed Files</h3>
                            <p class="text-2xl font-bold text-green-600"><?= $stats['processed'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Manage Files</h2>
                    <div class="flex gap-4">
                        <select id="statusFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processed">Processed</option>
                        </select>
                        <input type="text" id="searchInput" placeholder="Search files..." 
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

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

                <!-- Files Table -->
                <div class="overflow-x-auto">
                    <table class="w-full" id="filesTable">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">ID</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">User</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Title</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Vehicle Info</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Status</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Version</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Actions</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Tuning Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                           $stmt = $conn->prepare("
                           SELECT f.*, u.username, 
                                  m.name as manufacturer,
                                  cm.name as model,
                                  et.name as ecu_type,
                                  fv.file_path,
                                  GROUP_CONCAT(DISTINCT to.name) as tuning_options
                           FROM files f
                           JOIN users u ON f.user_id = u.id
                           JOIN car_manufacturers m ON f.manufacturer_id = m.id
                           JOIN car_models cm ON f.model_id = cm.id
                           JOIN ecu_types et ON f.ecu_type_id = et.id
                           JOIN file_versions fv ON f.id = fv.file_id AND f.current_version = fv.version
                           LEFT JOIN file_tuning_options fto ON f.id = fto.file_id
                           LEFT JOIN tuning_options `to` ON fto.option_id = to.id
                           GROUP BY f.id, f.user_id, f.title, f.status, f.current_version, f.created_at,
                                    u.username, m.name, cm.name, et.name, fv.file_path
                           ORDER BY f.created_at DESC
                       ");
                            $stmt->execute();
                            $files = $stmt->get_result();
                            
                            while ($file = $files->fetch_assoc()):
                            ?>
                           
                            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="p-3 text-gray-700 dark:text-gray-300">#<?= htmlspecialchars($file['id']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($file['username']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($file['title']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($file['manufacturer']) ?> <?= htmlspecialchars($file['model']) ?><br>
                                    <span class="text-sm text-gray-500">ECU: <?= htmlspecialchars($file['ecu_type']) ?></span>
                                </td>
                                <td class="p-3 text-gray-700 dark:text-gray-300">
    <?= $file['tuning_options'] ? htmlspecialchars($file['tuning_options']) : '<span class="text-gray-400">None</span>' ?>
</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded text-sm font-medium
                                        <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= ucfirst(htmlspecialchars($file['status'])) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-gray-700 dark:text-gray-300">v<?= htmlspecialchars($file['current_version']) ?></td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <a href="file_details.php?id=<?= $file['id'] ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            View
                                        </a>
                                        
                                        <?php if ($file['status'] === 'pending'): ?>
<button type="button" 
        onclick="showProcessModal(<?= $file['id'] ?>, <?= $file['user_id'] ?>)"
        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
    Process
</button>
<?php endif; ?>
                                        
                                        <form method="POST" class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to delete this file?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                            <?php echo csrf_input_field(); ?>
                                            <button type="submit" 
                                                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <script>
document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const fileInput = this.querySelector('input[type="file"]');
        if (fileInput && !fileInput.files.length) {
            e.preventDefault();
            alert('Please select a file first');
        }
    });
});
</script>
<script>
function showProcessModal(fileId, userId) {
    document.getElementById('modal_file_id').value = fileId;
    document.getElementById('modal_user_id').value = userId;
    document.getElementById('processModal').classList.remove('hidden');
    document.getElementById('processModal').classList.add('flex');
}

function closeProcessModal() {
    document.getElementById('processModal').classList.add('hidden');
    document.getElementById('processModal').classList.remove('flex');
    document.getElementById('processForm').reset();
}

// Close modal when clicking outside
document.getElementById('processModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProcessModal();
    }
});

// Form validation
document.getElementById('processForm').addEventListener('submit', function(e) {
    const fileInput = this.querySelector('input[type="file"]');
    if (!fileInput.files.length) {
        e.preventDefault();
        alert('Please select a file first');
    }
});
</script>
<script>
function showProcessModal(fileId, userId) {
    document.getElementById('modal_file_id').value = fileId;
    document.getElementById('modal_user_id').value = userId;
    
    // Fetch tuning options for this file
    fetch(`get_file_tuning_options.php?file_id=${fileId}`)
        .then(response => response.json())
        .then(data => {
            const optionsList = document.getElementById('tuningOptionsList');
            optionsList.innerHTML = data.options.length ? 
                data.options.map(opt => `<div class="text-sm mb-1">• ${opt}</div>`).join('') :
                '<div class="text-gray-500">No tuning options requested</div>';
        });
    
    document.getElementById('processModal').classList.remove('hidden');
    document.getElementById('processModal').classList.add('flex');
}
</script>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div id="processModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Process File</h3>
            <button type="button" onclick="closeProcessModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="processForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="file_id" id="modal_file_id">
            <input type="hidden" name="user_id" id="modal_user_id">
            <div class="space-y-2 mb-4">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Required Tuning Options
    </label>
    <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
        <div id="tuningOptionsList"></div>
    </div>
</div>
            <?php echo csrf_input_field(); ?>
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Select Processed File (.bin)
                </label>
                <input type="file" name="processed_file" 
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0 file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100
                              dark:file:bg-gray-700 dark:file:text-gray-300"
                       accept=".bin" required>
            </div>
            
            <div class="mt-4 flex justify-end space-x-3">
                <button type="button" onclick="closeProcessModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200
                               dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Process File
                </button>
            </div>
        </form>
    </div>
</div>

            </div>
        </div> <!-- Close the bg-white div -->
    </div> <!-- Close the flex-1 div -->
</div> <!--


