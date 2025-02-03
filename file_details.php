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
include 'includes/sidebar.php';
?>

<div class="ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-red-600"><?= $file['title'] ?></h2>
                <p class="text-gray-600"><?= $file['car_model'] ?></p>
            </div>
            <span class="px-3 py-1 rounded-full <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                <?= ucfirst($file['status']) ?>
            </span>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="text-lg font-semibold mb-2">Description</h3>
                <p class="text-gray-600"><?= $file['description'] ?></p>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-2">File Actions</h3>
                <div class="space-y-2">
                    <?php if($file['status'] === 'processed'): ?>
                        <a href="#" class="block w-full bg-red-600 text-white text-center py-2 rounded hover:bg-red-700">
                            Download Processed File
                        </a>
                    <?php endif; ?>
                    <button class="w-full bg-gray-100 text-gray-700 py-2 rounded hover:bg-gray-200"
                            onclick="toggleVersionHistory()">
                        View Version History
                    </button>
                </div>
            </div>
        </div>

        <!-- Version History (Hidden by Default) -->
        <div id="versionHistory" class="hidden">
            <h3 class="text-lg font-semibold mb-4">Version History</h3>
            <div class="space-y-3">
                <?php while($version = $versions->fetch_assoc()): ?>
                <div class="border rounded p-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-semibold">Version <?= $version['version'] ?></span>
                            <span class="text-sm text-gray-500 ml-2"><?= $version['uploaded_at'] ?></span>
                        </div>
                        <div class="space-x-2">
                            <a href="#" class="text-red-600 hover:text-red-800">Download</a>
                            <?php if($version['version'] !== $file['current_version']): ?>
                                <a href="revert_version.php?file_id=<?= $fileId ?>&version=<?= $version['version'] ?>" 
                                   class="text-blue-600 hover:text-blue-800">Revert</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if($version['notes']): ?>
                        <p class="mt-2 text-gray-600"><?= $version['notes'] ?></p>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <!-- Add this after version history -->
<div class="mt-8">
    <h3 class="text-lg font-semibold mb-4">Request File Update</h3>
    <?php if($file['status'] === 'processed'): ?>
        <button onclick="toggleRequestModal()" 
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            Request Update
        </button>
        
        <!-- Request Modal -->
        <div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-lg p-6 w-96">
                    <h3 class="text-xl font-bold mb-4">Request File Update</h3>
                    <form action="request_update.php" method="POST">
                    <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="file_id" value="<?= $fileId ?>">
                        <div class="mb-4">
                            <label class="block mb-2">Update Instructions</label>
                            <textarea name="message" required 
                                    class="w-full p-2 border rounded" rows="4"
                                    placeholder="What needs to be updated?"></textarea>
                        </div>
                        <div class="flex justify-end gap-4">
                            <button type="button" onclick="toggleRequestModal()" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
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
        <p class="text-gray-500">Available for processed files only</p>
    <?php endif; ?>
</div>

<script>
function toggleRequestModal() {
    const modal = document.getElementById('requestModal');
    modal.classList.toggle('hidden');
}
</script>
    </div>
</div>

<script>
function toggleVersionHistory() {
    const history = document.getElementById('versionHistory');
    history.classList.toggle('hidden');
}
</script>

<?php include 'footer.php'; ?>