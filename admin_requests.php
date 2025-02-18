<?php
include 'config.php';
require_auth(true);

// Handle request completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_request'])) {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    $request_id = (int)$_POST['request_id'];
    $notes = sanitize($_POST['admin_notes']);
    // Update the processing status when admin starts working
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_processing'])) {
        if (verify_csrf_token($_POST['csrf_token'])) {
            $request_id = (int)$_POST['request_id'];
            $stmt = $conn->prepare("UPDATE update_requests SET status = 'processing' WHERE id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            
            // Add to transactions log
            $stmt = $conn->prepare("INSERT INTO file_transactions (file_id, user_id, action_type, details) VALUES (?, ?, 'processing_started', 'Admin started processing update request')");
            $stmt->bind_param("ii", $file_id, $_SESSION['user_id']);
            $stmt->execute();
        }
    }
    
    // When completing the request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_request'])) {
        // Handle file upload
        if (!empty($_FILES['updated_file']['name'])) {
            $file = $_FILES['updated_file'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            
            if ($ext === 'bin') {
                // Get request details using prepared statement
                $stmt = $conn->prepare("SELECT * FROM update_requests WHERE id = ?");
                $stmt->bind_param("i", $request_id);
                $stmt->execute();
                $request = $stmt->get_result()->fetch_assoc();
                
                // Update file version with encryption
                $stmt = $conn->prepare("SELECT current_version FROM files WHERE id = ?");
                $stmt->bind_param("i", $request['file_id']);
                $stmt->execute();
                $new_version = $stmt->get_result()->fetch_assoc()['current_version'] + 1;
                
                $filename = "processed_{$request['file_id']}_v{$new_version}.bin";
                $upload_path = __DIR__ . "/uploads/$filename";
                
                if (!encrypt_file($_FILES['updated_file']['tmp_name'], $upload_path)) {
                    throw new Exception("Failed to encrypt file");
                }
                
                // Calculate file hash
                $file_hash = hash_file('sha256', $upload_path);
                
                // Update database with prepared statements
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
}

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-red-600 dark:text-red-400 mb-6">Update Requests</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-red-50 dark:bg-red-900">
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Request ID</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">File</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">User</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Status</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Actions</th>
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
                            <tr class="border-b dark:border-gray-700">
                                <td class="p-3 text-gray-700 dark:text-gray-300">#<?= $request['id'] ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= $request['title'] ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= $request['username'] ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded 
                                        <?= $request['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                           ($request['status'] === 'processing' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') ?>">
                                        <?= ucfirst($request['status']) ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <?php if($request['status'] === 'pending'): ?>
                                        <button onclick="toggleProcessingModal(<?= $request['id'] ?>)" 
                                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                            Process
                                        </button>
                                        <button onclick="rejectRequest(<?= $request['id'] ?>)" 
                                                class="bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700 ml-2">
                                            Reject
                                        </button>
                                    <?php elseif($request['status'] === 'processing'): ?>
                                        <button onclick="completeRequest(<?= $request['id'] ?>)" 
                                                class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                                            Complete
                                        </button>
                                    <?php endif; ?>
                                    <a href="javascript:void(0)" 
                                       onclick="showRequestDetails(<?= $request['id'] ?>)" 
                                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 ml-2">
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
    </div>
</div>

<!-- Processing Modal -->
<div id="processingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
            <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Process Update Request</h3>
            <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_input_field(); ?>
                <input type="hidden" name="request_id" id="modalRequestId">
                <input type="hidden" name="complete_request" value="1">
                
                <div class="mb-4">
                    <label class="block mb-2 text-gray-700 dark:text-gray-300">Upload Updated File</label>
                    <input type="file" name="updated_file" required 
                           class="w-full p-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" accept=".bin">
                </div>
                
                <div class="mb-4">
                    <label class="block mb-2 text-gray-700 dark:text-gray-300">Admin Notes</label>
                    <textarea name="admin_notes" 
                              class="w-full p-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="3"
                              placeholder="Add any notes for the user"></textarea>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="toggleProcessingModal()" 
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        Complete Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-11/12 max-w-2xl">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Request Details - #<span id="requestId"></span></h3>
                <button onclick="toggleDetailsModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    ✕
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">File:</label>
                        <p id="fileTitle" class="text-gray-600 dark:text-gray-400"></p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">User:</label>
                        <p id="userInfo" class="text-gray-600 dark:text-gray-400"></p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">Status:</label>
                        <p id="requestStatus" class="text-gray-600 dark:text-gray-400"></p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">Created:</label>
                        <p id="createdAt" class="text-gray-600 dark:text-gray-400"></p>
                    </div>
                </div>
                
                <div class="border-t pt-4 dark:border-gray-700">
                    <label class="font-semibold text-gray-700 dark:text-gray-300">User's Message:</label>
                    <p id="userMessage" class="text-gray-600 dark:text-gray-400 whitespace-pre-line"></p>
                </div>
                
                <div class="border-t pt-4 dark:border-gray-700">
                    <label class="font-semibold text-gray-700 dark:text-gray-300">Admin Notes:</label>
                    <p id="adminNotes" class="text-gray-600 dark:text-gray-400 whitespace-pre-line"></p>
                </div>
                
                <div class="border-t pt-4 dark:border-gray-700">
                    <label class="font-semibold text-gray-700 dark:text-gray-300">Current File Version:</label>
                    <p id="fileVersion" class="text-gray-600 dark:text-gray-400"></p>
                    <a id="fileDownload" href="#" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" download>
                        Download Latest Version
                    </a>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button onclick="toggleDetailsModal()" 
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                    Close
                </button>
            </div>
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

async function showRequestDetails(requestId) {
    try {
        const response = await fetch(`get_request_details.php?id=${requestId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();

        // Populate the modal with request details
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
            document.getElementById('fileDownload').classList.remove('hidden');
        } else {
            document.getElementById('fileVersion').textContent = 'No file available';
            document.getElementById('fileDownload').classList.add('hidden');
        }
        
        toggleDetailsModal();
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load request details');
    }
}

function toggleDetailsModal() {
    const modal = document.getElementById('detailsModal');
    modal.classList.toggle('hidden');
}
</script>

<?php include 'footer.php'; ?>

