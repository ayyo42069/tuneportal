<?php
include 'config.php';
require_auth();

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$fileId = (int)$_GET['id'];

// Use prepared statements for security
// After fetching the file, add ownership check
$stmt = $conn->prepare("SELECT f.*, u.username FROM files f JOIN users u ON f.user_id = u.id WHERE f.id = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Add security check
if (!$file || ($file['user_id'] !== $_SESSION['user_id'] && $_SESSION['role'] !== 'admin')) {
    log_error("Unauthorized file access attempt", "WARNING", [
        'file_id' => $fileId,
        'user_id' => $_SESSION['user_id']
    ]);
    $_SESSION['error'] = "You don't have permission to view this file";
    header("Location: files.php");
    exit();
}
// Add these new queries for enhanced details
$stmt = $conn->prepare("
    SELECT COUNT(*) as download_count 
    FROM file_download_log 
    WHERE file_id = ?
");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$downloads = $stmt->get_result()->fetch_assoc()['download_count'];
// Update the query to include proper timestamp fields and execute it
$stmt = $conn->prepare("
    SELECT 
        f.*,
        COALESCE(f.updated_at, f.created_at) as last_modified,
        u.username 
    FROM files f 
    JOIN users u ON f.user_id = u.id 
    WHERE f.id = ?
");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$file_details = $stmt->get_result()->fetch_assoc();
$stmt->close();
// Update the file array with the new details
$file = array_merge($file, $file_details);
$stmt = $conn->prepare("
    SELECT ft.*, u.username 
    FROM file_transactions ft 
    JOIN users u ON ft.user_id = u.id 
    WHERE ft.file_id = ? 
    ORDER BY ft.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$transactions = $stmt->get_result();
$stmt->close();
// Add this after the initial file query
$stmt = $conn->prepare("
    SELECT to.name
    FROM file_tuning_options fto
    JOIN tuning_options `to` ON fto.option_id = to.id
    WHERE fto.file_id = ?
");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$tuning_options = $stmt->get_result();
$stmt->close();
if (!$file) {
    log_error("File not found", "WARNING", ['file_id' => $fileId]);
    $_SESSION['error'] = "File not found";
    header("Location: dashboard.php");
    exit();
}
// Update the query to include vehicle information
// Update the file query to include pending update information
$stmt = $conn->prepare("
    SELECT f.*, u.username,
           m.name AS manufacturer_name,
           cm.name AS model_name,
           et.name AS ecu_name,
           COALESCE(f.updated_at, f.created_at) as last_modified,
           (SELECT COUNT(*) FROM file_transactions 
            WHERE file_id = f.id 
            AND action_type = 'update_requested' 
            AND status != 'completed') as has_pending_update
    FROM files f 
    JOIN users u ON f.user_id = u.id 
    JOIN car_manufacturers m ON f.manufacturer_id = m.id
    JOIN car_models cm ON f.model_id = cm.id
    JOIN ecu_types et ON f.ecu_type_id = et.id
    WHERE f.id = ?
");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();
$stmt = $conn->prepare("SELECT * FROM file_versions WHERE file_id = ? ORDER BY version DESC");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$versions = $stmt->get_result();
$stmt->close();
// Handle file download requests
if (isset($_GET['download'])) {
    $version_id = isset($_GET['version']) ? (int)$_GET['version'] : $file['current_version'];
    
    // Get file path for specific version
    $stmt = $conn->prepare("SELECT * FROM file_versions WHERE file_id = ? AND version = ?");
    $stmt->bind_param("ii", $fileId, $version_id);
    $stmt->execute();
    $version_file = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($version_file) {
        $file_path = __DIR__ . '/uploads/' . $version_file['file_path'];
        
        // Log download attempt
        $stmt = $conn->prepare("INSERT INTO file_download_log (file_id, version_id, user_id, user_ip) VALUES (?, ?, ?, ?)");
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("iiis", $fileId, $version_file['id'], $user_id, $user_ip);
        $stmt->execute();
        $stmt->close();

        if (!serve_file($file_path, basename($version_file['file_path']))) {
            log_error("File download failed", "ERROR", [
                'file_id' => $fileId,
                'version_id' => $version_id,
                'path' => $file_path
            ]);
            $_SESSION['error'] = "Download failed";
        }
    } else {
        log_error("Version not found", "WARNING", [
            'file_id' => $fileId,
            'version' => $version_id
        ]);
        $_SESSION['error'] = "Version not found";
    }
    header("Location: file_details.php?id=" . $fileId);
    exit();
}

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start mb-6">
    <div>
        <h2 class="text-2xl font-bold text-red-600 dark:text-red-400"><?= htmlspecialchars($file['title']) ?></h2>
        <div class="text-gray-600 dark:text-gray-400">
            <p><?= htmlspecialchars($file['manufacturer_name']) ?> <?= htmlspecialchars($file['model_name']) ?></p>
            <p>Year: <?= htmlspecialchars($file['year']) ?></p>
            <p>ECU: <?= htmlspecialchars($file['ecu_name']) ?></p>
        </div>
    </div>
    <span class="px-3 py-1 rounded-full <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' ?>">
        <?= ucfirst($file['status']) ?>
    </span>
</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-200">Description</h3>
                        <p class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($file['description']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-200">File Actions</h3>
                        <div class="space-y-2">
                            <?php if($file['status'] === 'processed'): ?>
                                <a href="?id=<?= $fileId ?>&download=true" 
                                   class="block w-full bg-red-600 text-white text-center py-2 rounded hover:bg-red-700 transition-colors">
                                    Download Processed File
                                </a>
                            <?php endif; ?>
                            <button class="w-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                    onclick="toggleVersionHistory()">
                                View Version History
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Version History (Hidden by Default) -->
                <div id="versionHistory" class="hidden">
                    <!-- File Statistics -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">File Statistics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Downloads</p>
                                <p class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= $downloads ?></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                                <p class="text-2xl font-bold text-gray-800 dark:text-gray-200"><?= date('M j, Y', strtotime($file['created_at'])) ?></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Last Modified</p>
    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">
        <?= $file['last_modified'] ? date('M j, Y', strtotime($file['last_modified'])) : 'N/A' ?>
    </p>
</div>
<!-- Add this after the ECU information -->
<div class="mt-4">
    <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-200">Requested Tuning Options</h3>
    <div class="space-y-1">
        <?php if ($tuning_options->num_rows > 0): ?>
            <?php while ($option = $tuning_options->fetch_assoc()): ?>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($option['name']) ?></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500 dark:text-gray-400">No tuning options requested</p>
        <?php endif; ?>
    </div>
</div>
                        </div>
                    </div>
                    <!-- Recent Activity -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Recent Activity</h3>
                        <div class="space-y-3">
                            <?php while($transaction = $transactions->fetch_assoc()): ?>
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                                <?= htmlspecialchars($transaction['action_type']) ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                by <?= htmlspecialchars($transaction['username']) ?>
                                            </p>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <?= date('M j, Y H:i', strtotime($transaction['created_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <?php while($version = $versions->fetch_assoc()): ?>
                        <div class="border rounded p-3 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">Version <?= $version['version'] ?></span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-2"><?= $version['uploaded_at'] ?></span>
                                </div>
                            
                                <div class="space-x-2">
                                    <a href="?id=<?= $fileId ?>&download=true&version=<?= $version['version'] ?>" 
                                       class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        Download
                                    </a>
                                </div>
                            </div>
                            <?php if($version['notes']): ?>
                                <p class="mt-2 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($version['notes']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Request File Update Section -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Request File Update</h3>
                    <?php if($file['status'] === 'processed' && !$file['has_pending_update']): ?>
                        <button onclick="toggleRequestModal()" 
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                            Request Update
                        </button>
                        
                        <!-- Request Modal -->
                        <!-- Replace the existing request modal with this updated version -->
                        <div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
                            <div class="flex items-center justify-center min-h-screen">
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
                                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Request File Update</h3>
                                    <form action="request_update.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="file_id" value="<?= $fileId ?>">
                                        <div class="mb-4">
                                            <label class="block mb-2 text-gray-700 dark:text-gray-300">Update Instructions</label>
                                            <textarea name="message" required 
                                                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                                    rows="4"
                                                    placeholder="Please describe what changes you need..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-4">
                                            <button type="button" onclick="toggleRequestModal()" 
                                                    class="bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                                Cancel
                                            </button>
                                            <button type="submit" 
                                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                                Submit Request
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400">
                            <?php if($file['has_pending_update']): ?>
                                An update request is already pending
                            <?php else: ?>
                                Available for processed files only
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleVersionHistory() {
    const history = document.getElementById('versionHistory');
    history.classList.toggle('hidden');
}

function toggleRequestModal() {
    const modal = document.getElementById('requestModal');
    modal.classList.toggle('hidden');
}
</script>

<?php include 'footer.php'; ?>
