<?php
include 'config.php';
require_auth();

// Get current credits using a prepared statement
$stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$_SESSION['credits'] = $user['credits'];
$stmt->close();

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-50 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <!-- Credit Balance Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Credit Balance</h3>
                        <p class="text-3xl font-bold text-red-600">
                            <?= number_format($_SESSION['credits']) ?> Credits
                        </p>
                    </div>
                    <a href="credits.php" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Transaction History
                    </a>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Welcome Back, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
                
                <!-- Grid Layout for Cards -->
                <div class="grid gap-8 grid-cols-1 lg:grid-cols-2">
                    <!-- Notifications Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Notifications</h3>
                            <span class="bg-red-100 text-red-600 text-sm py-1 px-3 rounded-full">New</span>
                        </div>
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC LIMIT 5");
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $notifications = $stmt->get_result();
                        
                        if ($notifications->num_rows > 0):
                        ?>
                        <div class="space-y-3">
                            <?php while ($note = $notifications->fetch_assoc()): ?>
                            <div class="p-4 border rounded-lg <?= $note['is_read'] ? 'bg-gray-50 dark:bg-gray-700' : 'bg-blue-50 dark:bg-blue-900 border-blue-100 dark:border-blue-800' ?>">
                                <div class="flex items-start justify-between">
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        <?= htmlspecialchars($note['message']) ?>
                                    </p>
                                    <?php if (!$note['is_read']): ?>
                                        <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full ml-2 mt-2"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        <?= date('M j, Y H:i', strtotime($note['created_at'])) ?>
                                    </span>
                                    <?php if ($note['link']): ?>
                                    <a href="<?= htmlspecialchars($note['link']) ?>" 
                                       class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">
                                        View Details →
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No new notifications</p>
                        <?php endif; ?>
                        <?php $stmt->close(); ?>
                    </div>

                    <!-- File Upload Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Upload New File</h3>
                        <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <?php echo csrf_input_field(); ?>
                            
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File Title</label>
                                    <input type="text" name="title" required 
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Car Model</label>
                                    <input type="text" name="car_model" required 
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea name="description" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"></textarea>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tuning Options</label>
                                    <div class="space-y-2 max-h-40 overflow-y-auto p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <?php
                                        $options = $conn->query("SELECT * FROM tuning_options");
                                        while ($opt = $options->fetch_assoc()) :
                                        ?>
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="tuning_options[]" value="<?= $opt['id'] ?>"
                                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <?= htmlspecialchars($opt['name']) ?> 
                                                <span class="text-gray-500 dark:text-gray-400">(<?= $opt['credit_cost'] ?> credits)</span>
                                            </span>
                                        </label>
                                        <?php
                                        endwhile;
                                        ?>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select .bin File</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-red-500 transition-colors">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                                <label class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-red-600 dark:text-red-400 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                                    <span>Upload a file</span>
                                                    <input type="file" name="bin_file" accept=".bin" class="sr-only" required>
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">.bin files only</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Upload File
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recent Files Table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm mt-8">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Files</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Car Model</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Version</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php
                                $stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                                $stmt->bind_param("i", $_SESSION['user_id']);
                                $stmt->execute();
                                $files = $stmt->get_result();
                                while ($file = $files->fetch_assoc()) :
                                ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($file['title']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <?= htmlspecialchars($file['car_model']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' ?>">
                                            <?= ucfirst($file['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        v<?= htmlspecialchars($file['current_version']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="file_details.php?id=<?= htmlspecialchars($file['id']) ?>" 
                                           class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">View Details</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php $stmt->close(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
