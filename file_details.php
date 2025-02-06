<?php
include 'config.php';
require_auth();

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$fileId = (int)$_GET['id'];
$file = $conn->query("SELECT * FROM files WHERE id = $fileId")->fetch_assoc();
$versions = $conn->query("SELECT * FROM file_versions WHERE file_id = $fileId ORDER BY version DESC");

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
                        <p class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($file['car_model']) ?></p>
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
                                <a href="#" class="block w-full bg-red-600 text-white text-center py-2 rounded hover:bg-red-700 transition-colors">
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
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Version History</h3>
                    <div class="space-y-3">
                        <?php while($version = $versions->fetch_assoc()): ?>
                        <div class="border rounded p-3 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">Version <?= $version['version'] ?></span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-2"><?= $version['uploaded_at'] ?></span>
                                </div>
                                <div class="space-x-2">
                                    <a href="#" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Download</a>
                                    <?php if($version['version'] !== $file['current_version']): ?>
                                        <a href="revert_version.php?file_id=<?= $fileId ?>&version=<?= $version['version'] ?>" 
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Revert</a>
                                    <?php endif; ?>
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
                    <?php if($file['status'] === 'processed'): ?>
                        <button onclick="toggleRequestModal()" 
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                            Request Update
                        </button>
                        
                        <!-- Request Modal -->
                        <div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
                            <div class="flex items-center justify-center min-h-screen">
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
                                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Request File Update</h3>
                                    <form action="request_update.php" method="POST">
                                    <?php echo csrf_input_field(); ?>
                                        <input type="hidden" name="file_id" value="<?= $fileId ?>">
                                        <div class="mb-4">
                                            <label class="block mb-2 text-gray-700 dark:text-gray-300">Update Instructions</label>
                                            <textarea name="message" required 
                                                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="4"
                                                    placeholder="What needs to be updated?"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-4">
                                            <button type="button" onclick="toggleRequestModal()" 
                                                    class="bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                                                Cancel
                                            </button>
                                            <button type="submit" 
                                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                                                Submit Request
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400">Available for processed files only</p>
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
