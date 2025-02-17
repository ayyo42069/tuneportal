<?php
include 'config.php';
require_auth(true); // Admin only

// Handle log clearing if requested
if (isset($_POST['clear_logs']) && verify_csrf_token($_POST['csrf_token'])) {
    $stmt = $conn->prepare("DELETE FROM error_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $_SESSION['success'] = "Logs older than 30 days have been cleared.";
    header("Location: admin_logs.php");
    exit();
}

// Fetch logs with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM error_log");
$stmt->execute();
$total_logs = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_logs / $per_page);

$stmt = $conn->prepare("
    SELECT el.*, u.username 
    FROM error_log el 
    LEFT JOIN users u ON el.user_id = u.id 
    ORDER BY el.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$logs = $stmt->get_result();

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">System Logs</h2>
                    <form method="POST" class="inline">
                        <?php echo csrf_input_field(); ?>
                        <button type="submit" name="clear_logs" 
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors"
                                onclick="return confirm('Are you sure you want to clear old logs?')">
                            Clear Old Logs
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</span>
                                </th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Severity</span>
                                </th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Message</span>
                                </th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</span>
                                </th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($log['created_at']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch ($log['severity']) {
                                            case 'ERROR':
                                            case 'CRITICAL':
                                                echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                                break;
                                            case 'WARNING':
                                                echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                                break;
                                            default:
                                                echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                        }
                                        ?>">
                                        <?= htmlspecialchars($log['severity']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($log['message']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?= $log['username'] ? htmlspecialchars($log['username']) : 'Guest' ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <button onclick="showContext('<?= htmlspecialchars(json_encode($log['context']), ENT_QUOTES) ?>')"
                                            class="text-blue-600 dark:text-blue-400 hover:underline">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="mt-4 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 
                                      <?= $i === $page ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200' ?> 
                                      text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Context Modal -->
<div id="contextModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Log Details</h3>
                <button onclick="hideContext()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="contextContent" class="space-y-4"></div>
        </div>
    </div>
</div>

<script>
function formatValue(value) {
    if (typeof value === 'object' && value !== null) {
        return `<div class="pl-4 border-l-2 border-gray-300 dark:border-gray-600">
            ${Object.entries(value).map(([k, v]) => formatKeyValue(k, v)).join('')}
        </div>`;
    }
    return `<span class="text-gray-600 dark:text-gray-300">${value}</span>`;
}

function formatKeyValue(key, value) {
    return `<div class="py-2">
        <span class="font-semibold text-gray-700 dark:text-gray-200">${key}:</span> 
        ${formatValue(value)}
    </div>`;
}

function showContext(context) {
    const modal = document.getElementById('contextModal');
    const content = document.getElementById('contextContent');
    try {
        const parsed = JSON.parse(context);
        content.innerHTML = `
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 space-y-3">
                ${Object.entries(parsed).map(([key, value]) => {
                    return `<div class="border-b border-gray-200 dark:border-gray-700 last:border-0 pb-3 last:pb-0">
                        ${formatKeyValue(key, value)}
                    </div>`;
                }).join('')}
            </div>
        `;
    } catch (e) {
        content.innerHTML = `
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <pre class="text-sm text-gray-600 dark:text-gray-300">${context}</pre>
            </div>
        `;
    }
    modal.classList.remove('hidden');
}

function hideContext() {
    document.getElementById('contextModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('contextModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideContext();
    }
});

// Prevent modal close when clicking inside
document.querySelector('#contextModal > div').addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>

<?php include 'footer.php'; ?>