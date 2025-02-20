<?php
include 'config.php';
require_auth();

$userId = $_SESSION['user_id'];

// Enhanced query to include manufacturer and model information
$stmt = $conn->prepare("
    SELECT 
        f.*, 
        fv.uploaded_at, 
        fv.file_path,
        u.username,
        m.name AS manufacturer_name,
        cm.name AS model_name,
        et.name AS ecu_name,
        (SELECT COUNT(*) FROM file_download_log WHERE file_id = f.id) as download_count,
        (SELECT COUNT(*) FROM file_versions WHERE file_id = f.id) as version_count,
        (SELECT COUNT(*) 
         FROM file_transactions 
         WHERE file_id = f.id 
         AND action_type = 'update_requested'
         AND created_at = (
             SELECT MAX(created_at) 
             FROM file_transactions 
             WHERE file_id = f.id
         )
         AND NOT EXISTS (
             SELECT 1 
             FROM file_transactions 
             WHERE file_id = f.id 
             AND created_at > (
                 SELECT MAX(created_at) 
                 FROM file_transactions 
                 WHERE file_id = f.id 
                 AND action_type = 'update_requested'
             )
         )
        ) as has_pending_update
    FROM files f
    LEFT JOIN file_versions fv ON f.id = fv.file_id AND f.current_version = fv.version
    LEFT JOIN users u ON f.user_id = u.id
    LEFT JOIN car_manufacturers m ON f.manufacturer_id = m.id
    LEFT JOIN car_models cm ON f.model_id = cm.id
    LEFT JOIN ecu_types et ON f.ecu_type_id = et.id
    WHERE f.user_id = ? " . 
    ($_SESSION['role'] === 'admin' ? "OR 1=1 " : "") .
    "ORDER BY f.created_at DESC"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$files = $stmt->get_result();

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-red-600 dark:text-red-400">My Files</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Manage and monitor your tune files
                        </p>
                    </div>
                    <a href="dashboard.php" 
                       class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Upload New File
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <th class="p-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">File Details</th>
                                <th class="p-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Vehicle Info</th>
                                <th class="p-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Status</th>
                                <th class="p-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Statistics</th>
                                <th class="p-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            <?php if ($files->num_rows > 0): ?>
                                <?php while ($file = $files->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-800 dark:text-gray-200">
                                                    <?= htmlspecialchars($file['title']) ?>
                                                </span>
                                                <?php if($_SESSION['role'] === 'admin'): ?>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        by <?= htmlspecialchars($file['username'] ?? 'Unknown User') ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                                    Created: <?= date('M j, Y', strtotime($file['created_at'])) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-gray-800 dark:text-gray-200">
                                                    <?= htmlspecialchars($file['manufacturer_name']) ?> <?= htmlspecialchars($file['model_name']) ?>
                                                </span>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                                    Year: <?= htmlspecialchars($file['year']) ?>
                                                </span>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                                    ECU: <?= htmlspecialchars($file['ecu_name']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <?php
                                            $statusClass = '';
                                            $statusText = '';
                                            // Update the status check in the table
                                            if ($file['has_pending_update'] && $file['status'] === 'pending') {
                                                $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                                $statusText = 'Update Requested';
                                            } elseif ($file['status'] === 'pending') {
                                                $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                                $statusText = 'Processing';
                                            } else {
                                                $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                                $statusText = 'Processed';
                                            }
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-sm font-medium <?= $statusClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex gap-4">
                                                <div class="flex flex-col items-center">
                                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Versions</span>
                                                    <span class="text-lg font-bold text-gray-800 dark:text-gray-200"><?= $file['version_count'] ?></span>
                                                </div>
                                                <div class="flex flex-col items-center">
                                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Downloads</span>
                                                    <span class="text-lg font-bold text-gray-800 dark:text-gray-200"><?= $file['download_count'] ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-2">
                                                <a href="file_details.php?id=<?= $file['id'] ?>" 
                                                   class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Details
                                                </a>
                                                <?php if ($file['status'] === 'processed' && !empty($file['file_path'])): ?>
                                                    <a href="file_details.php?id=<?= $file['id'] ?>&download=true" 
                                                       class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                        </svg>
                                                        Download
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-4 text-center">
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-gray-600 dark:text-gray-400 mb-4">No files found</p>
                                            <a href="dashboard.php" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                                Upload Your First File
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>