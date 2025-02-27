<?php
include 'config.php';
require_auth();
include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 text-center">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Payment Successful!</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Your credits have been added to your account.</p>
                <a href="dashboard.php" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors inline-block">
                    Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>