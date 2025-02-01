<?php
include 'config.php';
require_auth();

include 'header.php';
include 'includes/sidebar.php';

$stmt = $conn->prepare("SELECT * FROM credit_transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$transactions = $stmt->get_result();
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-red-600 mb-6">Credit Transactions</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-red-50">
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-left">Amount</th>
                        <th class="p-3 text-left">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $balance = $_SESSION['credits'];
                    while ($transaction = $transactions->fetch_assoc()):
                        $balance -= $transaction['amount']; // Subtract because amounts are stored as changes
                    ?>
                    <tr class="border-b">
                        <td class="p-3"><?= date('M j, Y H:i', strtotime($transaction['created_at'])) ?></td>
                        <td class="p-3"><?= $transaction['description'] ?></td>
                        <td class="p-3 <?= $transaction['amount'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $transaction['amount'] > 0 ? '+' : '' ?><?= $transaction['amount'] ?>
                        </td>
                        <td class="p-3"><?= $balance + $transaction['amount'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>