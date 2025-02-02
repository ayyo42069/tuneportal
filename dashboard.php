<?php
include 'config.php';
require_auth();

try {
    // Get current credits using prepared statement
    $stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $_SESSION['credits'] = $user['credits'] ?? 0;
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    die("Error loading dashboard data");
}

include 'header.php';
?>

<!-- Main Container -->
<div class="flex min-h-screen">
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 mt-16 ml-64 p-8">
        <!-- Credit Balance Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">Credit Balance</h3>
                    <p class="text-3xl font-bold text-red-600">
                        <?= number_format($_SESSION['credits']) ?> Credits
                    </p>
                </div>
                <a href="credits.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Transaction History
                </a>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-red-600 mb-4">Welcome Back, <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES) ?>!</h2>
            
            <!-- Notifications -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">Notifications</h3>
                <?php
                try {
                    $stmt = $conn->prepare("
                        SELECT * FROM notifications 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $notifications = $stmt->get_result();
                    
                    if ($notifications->num_rows > 0):
                ?>
                <div class="space-y-2">
                    <?php while ($note = $notifications->fetch_assoc()): ?>
                    <div class="p-3 border rounded <?= $note['is_read'] ? 'bg-gray-50' : 'bg-blue-50' ?>">
                        <p class="text-sm">
                            <?= htmlspecialchars($note['message'], ENT_QUOTES) ?>
                            <?php if ($note['link']): ?>
                            <a href="<?= htmlspecialchars($note['link'], ENT_QUOTES) ?>" class="text-red-600 hover:text-red-800 ml-2">
                                View →
                            </a>
                            <?php endif; ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <?= date('M j, Y H:i', strtotime($note['created_at'])) ?>
                        </p>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500">No new notifications</p>
                <?php endif; 
                    $stmt->close();
                } catch (Exception $e) {
                    error_log("Notifications error: " . $e->getMessage());
                    echo '<p class="text-red-500">Error loading notifications</p>';
                }
                ?>
            </div>

            <!-- File Upload Card -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">Upload New File</h3>
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Form fields remain the same but add validation later -->
                        <?php /* Existing form fields */ ?>
                    </div>
                    
                    <button type="submit" class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Upload File
                    </button>
                </form>
            </div>

            <!-- File List -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Files</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-red-50">
                                <?php /* Table headers */ ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                                $stmt->bind_param("i", $_SESSION['user_id']);
                                $stmt->execute();
                                $files = $stmt->get_result();
                                
                                while ($file = $files->fetch_assoc()) :
                            ?>
                                <tr class="border-b">
                                    <td class="p-2"><?= htmlspecialchars($file['title'], ENT_QUOTES) ?></td>
                                    <td class="p-2"><?= htmlspecialchars($file['car_model'], ENT_QUOTES) ?></td>
                                    <td class="p-2">
                                        <span class="px-2 py-1 rounded <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= ucfirst(htmlspecialchars($file['status'], ENT_QUOTES)) ?>
                                        </span>
                                    </td>
                                    <td class="p-2">v<?= htmlspecialchars($file['current_version'], ENT_QUOTES) ?></td>
                                    <td class="p-2">
                                        <a href="file_details.php?id=<?= $file['id'] ?>" class="text-red-600 hover:text-red-800">View</a>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                                $stmt->close();
                            } catch (Exception $e) {
                                error_log("Files error: " . $e->getMessage());
                                echo '<tr><td colspan="5" class="p-2 text-red-500">Error loading files</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>