<?php
include 'config.php';
require_auth(true);

// Handle ban/unban actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $action = sanitize($_POST['action']);
    
    if ($_SESSION['user_id'] === $user_id) {
        $_SESSION['error'] = "You cannot modify your own status";
    } else {
        switch ($action) {
            case 'ban':
                $ban_reason = sanitize($_POST['ban_reason']); // Get the ban reason
                if (empty($ban_reason)) {
                    $_SESSION['error'] = "Ban reason is required.";
                } else {
                    // Update the user's banned status and ban reason
                    $stmt = $conn->prepare("UPDATE users SET banned = TRUE, ban_reason = ? WHERE id = ?");
                    $stmt->bind_param("si", $ban_reason, $user_id);
                    $stmt->execute();
                    $_SESSION['success'] = "User banned successfully.";
                }
                break;
            case 'unban':
                // Unban the user and clear the ban reason
                $stmt = $conn->prepare("UPDATE users SET banned = FALSE, ban_reason = NULL WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $_SESSION['success'] = "User unbanned successfully.";
                break;
        }
    }
    header("Location: admin_users.php");
    exit();
}

include 'header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-red-600 mb-6">Manage Users</h2>
        
        <div class="mb-4">
            <input type="text" id="searchInput" placeholder="Search users..." 
                   class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-600">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="usersTable">
                <thead>
                    <tr class="bg-red-50">
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Username</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Registered</th>
                        <th class="p-3 text-left">Last Login</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = $conn->query("
                        SELECT u.*, 
                        (SELECT MAX(attempted_at) FROM login_history WHERE user_id = u.id AND success = 1) AS last_login
                        FROM users u
                        ORDER BY u.created_at DESC
                    ");
                    
                    while ($user = $users->fetch_assoc()):
                    ?>
                    <tr class="border-b" data-id="<?= $user['id'] ?>">
                        <td class="p-3"><?= $user['id'] ?></td>
                        <td class="p-3"><?= $user['username'] ?></td>
                        <td class="p-3"><?= $user['email'] ?></td>
                        <td class="p-3"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td class="p-3"><?= $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never' ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded <?= $user['banned'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                <?= $user['banned'] ? 'Banned' : 'Active' ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <?php if($user['banned']): ?>
                                    <form method="POST">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <input type="hidden" name="action" value="unban">
                                        <button type="submit" class="text-green-600 hover:text-green-800">
                                            Unban
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button onclick="openBanModal(<?= $user['id'] ?>)" 
                                            class="text-red-600 hover:text-red-800">
                                        Ban
                                    </button>
                                <?php endif; ?>
                                <button onclick="showUserDetails(<?= $user['id'] ?>)" 
                                        class="text-blue-600 hover:text-blue-800">
                                    Details
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Ban Modal -->
<div id="banModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-11/12 max-w-md">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold">Ban User</h3>
                <button onclick="closeBanModal()" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <form method="POST" id="banForm">
                <input type="hidden" name="action" value="ban">
                <input type="hidden" name="user_id" id="banUserId">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Ban Reason:</label>
                    <textarea name="ban_reason" id="banReason" 
                              class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-600" 
                              required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeBanModal()" 
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 mr-2">
                        Cancel
                    </button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Ban User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-11/12 max-w-4xl">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold">User Details - #<span id="userId"></span></h3>
                <button onclick="toggleUserModal()" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            
            <div class="grid grid-cols-2 gap-6">
                <!-- Column 1: User Info -->
                <div class="space-y-4">
                    <div>
                        <label class="font-semibold">Username:</label>
                        <p id="detailUsername" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">Email:</label>
                        <p id="detailEmail" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">Registered:</label>
                        <p id="detailRegistered" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">IP Address:</label>
                        <p id="detailIP" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">Ban Reason:</label>
                        <p id="detailBanReason" class="text-gray-600"></p>
                    </div>
                </div>

                <!-- Column 2: Statistics -->
                <div class="space-y-4">
                    <div>
                        <label class="font-semibold">Total Files:</label>
                        <p id="detailFiles" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">Credits Balance:</label>
                        <p id="detailCredits" class="text-gray-600"></p>
                    </div>
                    <div>
                        <label class="font-semibold">Last Activity:</label>
                        <p id="detailLastActivity" class="text-gray-600"></p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
           <!-- Tabs -->
<div class="mt-6 border-b">
    <button onclick="showTab('loginHistory', event)" class="tab-link px-4 py-2 font-semibold text-red-600 border-b-2 border-red-600">
        Login History
    </button>
    <button onclick="showTab('files', event)" class="tab-link px-4 py-2 font-semibold text-gray-500">
        Files
    </button>
    <button onclick="showTab('transactions', event)" class="tab-link px-4 py-2 font-semibold text-gray-500">
        Transactions
    </button>
</div>

            <!-- Tab Content -->
            <div id="loginHistory" class="tab-content py-4">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-2 text-left">Date</th>
                            <th class="p-2 text-left">IP Address</th>
                            <th class="p-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody id="loginHistoryBody"></tbody>
                </table>
            </div>

            <div id="files" class="tab-content py-4 hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-2 text-left">File</th>
                            <th class="p-2 text-left">Status</th>
                            <th class="p-2 text-left">Last Modified</th>
                        </tr>
                    </thead>
                    <tbody id="userFilesBody"></tbody>
                </table>
            </div>

            <div id="transactions" class="tab-content py-4 hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-2 text-left">Date</th>
                            <th class="p-2 text-left">Amount</th>
                            <th class="p-2 text-left">Description</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsBody"></tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="toggleUserModal()" 
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});

// Ban Modal
function openBanModal(userId) {
    document.getElementById('banUserId').value = userId;
    document.getElementById('banModal').classList.remove('hidden');
}

function closeBanModal() {
    document.getElementById('banModal').classList.add('hidden');
    document.getElementById('banReason').value = ''; // Clear the textarea
}

// User details modal
async function showUserDetails(userId) {
    try {
        const response = await fetch(`user_details.php?id=${userId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();

        // Populate the modal with user details
        document.getElementById('userId').textContent = userId;
        document.getElementById('detailUsername').textContent = data.user.username;
        document.getElementById('detailEmail').textContent = data.user.email;
        document.getElementById('detailRegistered').textContent = new Date(data.user.created_at).toLocaleString();
        document.getElementById('detailIP').textContent = data.user.ip;
        document.getElementById('detailFiles').textContent = data.stats.files;
        document.getElementById('detailCredits').textContent = data.user.credits;
        document.getElementById('detailLastActivity').textContent = data.stats.last_activity || 'Never';
        document.getElementById('detailBanReason').textContent = data.user.ban_reason || 'Not banned';

        // Populate login history
        const loginBody = document.getElementById('loginHistoryBody');
        loginBody.innerHTML = data.login_history.map(login => `
            <tr class="border-b">
                <td class="p-2">${new Date(login.attempted_at).toLocaleString()}</td>
                <td class="p-2">${login.ip_address}</td>
                <td class="p-2">
                    <span class="px-2 py-1 rounded ${login.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${login.success ? 'Success' : 'Failed'}
                    </span>
                </td>
            </tr>
        `).join('');

        // Populate files
        const filesBody = document.getElementById('userFilesBody');
        filesBody.innerHTML = data.files.map(file => `
            <tr class="border-b">
                <td class="p-2">${file.title}</td>
                <td class="p-2">
                    <span class="px-2 py-1 rounded ${file.status === 'processed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                        ${file.status}
                    </span>
                </td>
                <td class="p-2">${new Date(file.created_at).toLocaleDateString()}</td>
            </tr>
        `).join('');

        // Populate transactions
        const transactionsBody = document.getElementById('transactionsBody');
        transactionsBody.innerHTML = data.transactions.map(tx => `
            <tr class="border-b">
                <td class="p-2">${new Date(tx.created_at).toLocaleString()}</td>
                <td class="p-2 ${tx.amount > 0 ? 'text-green-600' : 'text-red-600'}">
                    ${tx.amount > 0 ? '+' : ''}${tx.amount}
                </td>
                <td class="p-2">${tx.description}</td>
            </tr>
        `).join('');

        // Show the modal
        toggleUserModal();
        showTab('loginHistory'); // Call showTab without the event object
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load user details. Check the console for more information.');
    }
}

function toggleUserModal() {
    document.getElementById('userModal').classList.toggle('hidden');
}

function showTab(tabName, event = null) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));

    // Remove active styles from all tab links
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('text-red-600', 'border-red-600');
        link.classList.add('text-gray-500');
    });

    // Show the selected tab content
    document.getElementById(tabName).classList.remove('hidden');

    // Add active styles to the clicked tab link (if event is provided)
    if (event) {
        event.target.classList.add('text-red-600', 'border-red-600');
        event.target.classList.remove('text-gray-500');
    }
}
</script>

<?php include 'footer.php'; ?>