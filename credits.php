<?php
include 'config.php';
require_auth();

$stmt = $conn->prepare("SELECT * FROM credit_transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$transactions = $stmt->get_result();

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-red-600 dark:text-red-400 mb-6">Credit Transactions</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-red-50 dark:bg-red-900">
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Date</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Description</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Amount</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $balance = $_SESSION['credits'];
                            while ($transaction = $transactions->fetch_assoc()):
                                $balance -= $transaction['amount']; // Subtract because amounts are stored as changes
                            ?>
                            <tr class="border-b dark:border-gray-700">
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= date('M j, Y H:i', strtotime($transaction['created_at'])) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($transaction['description']) ?></td>
                                <td class="p-3 <?= $transaction['amount'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                    <?= $transaction['amount'] > 0 ? '+' : '' ?><?= $transaction['amount'] ?>
                                </td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= $balance + $transaction['amount'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
