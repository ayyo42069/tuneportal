<?php
include 'config.php';
require_auth();

// Get filter parameters
$filter_type = $_GET['type'] ?? 'all';
$date_range = $_GET['date_range'] ?? '30';

// Prepare base query
$query = "SELECT ct.*, u.credits as current_balance 
          FROM credit_transactions ct 
          JOIN users u ON ct.user_id = u.id 
          WHERE ct.user_id = ?";

// Apply filters
if ($filter_type !== 'all') {
    $query .= " AND ct.type = ?";
}

if ($date_range !== 'all') {
    $query .= " AND ct.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
}

$query .= " ORDER BY ct.created_at DESC";

// Prepare and execute statement
$stmt = $conn->prepare($query);

if ($filter_type !== 'all' && $date_range !== 'all') {
    $stmt->bind_param("isi", $_SESSION['user_id'], $filter_type, $date_range);
} elseif ($filter_type !== 'all') {
    $stmt->bind_param("is", $_SESSION['user_id'], $filter_type);
} elseif ($date_range !== 'all') {
    $stmt->bind_param("ii", $_SESSION['user_id'], $date_range);
} else {
    $stmt->bind_param("i", $_SESSION['user_id']);
}

$stmt->execute();
$transactions = $stmt->get_result();
$first_row = $transactions->fetch_assoc();
$current_balance = $first_row ? $first_row['current_balance'] : 0;
$transactions->data_seek(0);

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <!-- Credit Balance Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Current Balance</h2>
                        <p class="text-4xl font-bold text-red-600 dark:text-red-400 mt-2">
                            <?= number_format($current_balance) ?> Credits
                        </p>
                    </div>
                    <button onclick="showCreditPackages()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Buy Credits
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <form class="flex flex-wrap gap-4" method="GET">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transaction Type</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="all" <?= $filter_type === 'all' ? 'selected' : '' ?>>All Transactions</option>
                            <option value="purchase" <?= $filter_type === 'purchase' ? 'selected' : '' ?>>Purchases</option>
                            <option value="file_upload" <?= $filter_type === 'file_upload' ? 'selected' : '' ?>>File Uploads</option>
                            <option value="admin_adjust" <?= $filter_type === 'admin_adjust' ? 'selected' : '' ?>>Admin Adjustments</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                        <select name="date_range" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="7" <?= $date_range === '7' ? 'selected' : '' ?>>Last 7 days</option>
                            <option value="30" <?= $date_range === '30' ? 'selected' : '' ?>>Last 30 days</option>
                            <option value="90" <?= $date_range === '90' ? 'selected' : '' ?>>Last 90 days</option>
                            <option value="all" <?= $date_range === 'all' ? 'selected' : '' ?>>All time</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Transaction History</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Date</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Type</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Description</th>
                                <th class="p-3 text-right text-gray-800 dark:text-gray-200">Amount</th>
                                <th class="p-3 text-right text-gray-800 dark:text-gray-200">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $balance = $current_balance;
                            if ($transactions->num_rows > 0):
                                while ($transaction = $transactions->fetch_assoc()):
                            ?>
                            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="p-3 text-gray-700 dark:text-gray-300">
                                    <?= date('M j, Y H:i', strtotime($transaction['created_at'])) ?>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        <?php
                                        switch($transaction['type']) {
                                            case 'purchase':
                                                echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                                break;
                                            case 'file_upload':
                                                echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
                                        }
                                        ?>">
                                        <?= ucfirst($transaction['type']) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($transaction['description']) ?>
                                </td>
                                <td class="p-3 text-right <?= $transaction['amount'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                    <?= $transaction['amount'] > 0 ? '+' : '' ?><?= number_format($transaction['amount']) ?>
                                </td>
                                <td class="p-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                    <?= number_format($balance) ?>
                                    <?php $balance -= $transaction['amount']; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-500 dark:text-gray-400">
                                    No transactions found for the selected filters.
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