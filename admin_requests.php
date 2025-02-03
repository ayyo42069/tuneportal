<?php
include 'config.php';
require_auth(true);

// Handle request completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_request'])) {
    $request_id = (int)$_POST['request_id'];
    $notes = sanitize($_POST['admin_notes']);
    
    // Handle file upload
    if (!empty($_FILES['updated_file']['name'])) {
        $file = $_FILES['updated_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if ($ext === 'bin') {
            // Get request details
            $request = $conn->query("SELECT * FROM update_requests WHERE id = $request_id")->fetch_assoc();
            
            // Update file version
            $new_version = $conn->query("SELECT current_version FROM files WHERE id = {$request['file_id']}")->fetch_assoc()['current_version'] + 1;
            $filename = "processed_{$request['file_id']}_v{$new_version}.bin";
            move_uploaded_file($file['tmp_name'], __DIR__ . "/uploads/$filename");
            
            // Update database
            $conn->query("UPDATE files SET current_version = $new_version WHERE id = {$request['file_id']}");
            $conn->query("INSERT INTO file_versions (file_id, version, file_path, notes) 
                         VALUES ({$request['file_id']}, $new_version, '$filename', '$notes')");
            
            // Update request status
            $conn->query("UPDATE update_requests SET status = 'completed', admin_notes = '$notes' WHERE id = $request_id");
            
            // Notify user
            $user_msg = "Your update request #$request_id has been completed";
            $conn->query("INSERT INTO notifications (user_id, message, link) 
                         VALUES ({$request['user_id']}, '$user_msg', 'file_details.php?id={$request['file_id']}')");
            
            $_SESSION['success'] = "Request completed successfully";
        } else {
            $_SESSION['error'] = "Only .bin files accepted";
        }
    }
}

include 'header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-red-600 mb-6">Update Requests</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-red-50">
                        <th class="p-3 text-left">Request ID</th>
                        <th class="p-3 text-left">File</th>
                        <th class="p-3 text-left">User</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $requests = $conn->query("
                        SELECT ur.*, f.title, u.username 
                        FROM update_requests ur
                        JOIN files f ON ur.file_id = f.id
                        JOIN users u ON ur.user_id = u.id
                        ORDER BY ur.created_at DESC
                    ");
                    
                    while ($request = $requests->fetch_assoc()):
                    ?>
                    <tr class="border-b">
                        <td class="p-3">#<?= $request['id'] ?></td>
                        <td class="p-3"><?= $request['title'] ?></td>
                        <td class="p-3"><?= $request['username'] ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded 
                                <?= $request['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                   ($request['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                                <?= ucfirst($request['status']) ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <?php if($request['status'] !== 'completed'): ?>
                            <button onclick="toggleProcessingModal(<?= $request['id'] ?>)" 
                                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                Process
                            </button>
                            <?php endif; ?>
                            <a href="javascript:void(0)" 
                               onclick="showRequestDetails(<?= $request['id'] ?>)" 
                               class="text-blue-600 hover:text-blue-800 ml-2">
                                Details
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Processing Modal -->
<div id="processingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Process Update Request</h3>
            <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_input_field(); ?>
                <input type="hidden" name="request_id" id="modalRequestId">
                <input type="hidden" name="complete_request" value="1">
                
                <div class="mb-4">
                    <label class="block mb-2">Upload Updated File</label>
                    <input type="file" name="updated_file" required 
                           class="w-full p-2 border rounded" accept=".bin">
                </div>
                
                <div class="mb-4">
                    <label class="block mb-2">Admin Notes</label>
                    <textarea name="admin_notes" 
                              class="w-full p-2 border rounded" rows="3"
                              placeholder="Add any notes for the user"></textarea>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="toggleProcessingModal()" 
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Complete Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleProcessingModal(requestId = null) {
    const modal = document.getElementById('processingModal');
    if(requestId) {
        document.getElementById('modalRequestId').value = requestId;
    }
    modal.classList.toggle('hidden');
}

function showRequestDetails(requestId) {
    // AJAX implementation for details would go here
    alert('Detailed view implementation would go here');
}
</script>
<!-- Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-11/12 max-w-2xl">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold">Request Details - #<span id="requestId"></span></h3>
                <button onclick="toggleDetailsModal()" class="text-gray-500 hover:text-gray-700">
                    ✕
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="font-semibold">File:</label>
                        <p id="fileTitle" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">User:</label>
                        <p id="userInfo" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">Status:</label>
                        <p id="requestStatus" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">Created:</label>
                        <p id="createdAt" class="text-gray-600"></p>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <label class="font-semibold">User's Message:</label>
                    <p id="userMessage" class="text-gray-600 whitespace-pre-line"></p>
                </div>
                
                <div class="border-t pt-4">
                    <label class="font-semibold">Admin Notes:</label>
                    <p id="adminNotes" class="text-gray-600 whitespace-pre-line"></p>
                </div>
                
                <div class="border-t pt-4">
                    <label class="font-semibold">Current File Version:</label>
                    <p id="fileVersion" class="text-gray-600"></p>
                    <a id="fileDownload" href="#" class="text-red-600 hover:text-red-800" download>
                        Download Latest Version
                    </a>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button onclick="toggleDetailsModal()" 
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showRequestDetails(requestId) {
    fetch(`get_request_details.php?id=${requestId}`)
        .then(response => {
            if (!response.ok) throw new Error('Request failed');
            return response.json();
        })
        .then(data => {
            // Populate modal
            document.getElementById('requestId').textContent = data.id;
            document.getElementById('fileTitle').textContent = data.file_title;
            document.getElementById('userInfo').textContent = `${data.username} (${data.email})`;
            document.getElementById('requestStatus').textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
            document.getElementById('createdAt').textContent = new Date(data.created_at).toLocaleString();
            document.getElementById('userMessage').textContent = data.message;
            document.getElementById('adminNotes').textContent = data.admin_notes || 'No admin notes';
            
            if(data.file_path) {
                document.getElementById('fileVersion').textContent = new Date(data.file_updated).toLocaleString();
                document.getElementById('fileDownload').href = `uploads/${data.file_path}`;
            } else {
                document.getElementById('fileVersion').textContent = 'No file available';
                document.getElementById('fileDownload').classList.add('hidden');
            }
            
            toggleDetailsModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load request details');
        });
}

function toggleDetailsModal() {
    const modal = document.getElementById('detailsModal');
    modal.classList.toggle('hidden');
}
</script>

<?php include 'footer.php'; ?>