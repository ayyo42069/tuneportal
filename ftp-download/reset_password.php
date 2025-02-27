<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    $new_password = sanitize($_POST['new_password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    $reset_token = sanitize($_POST['token']);

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if the reset token is valid
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->bind_param("s", $reset_token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the user's password and clear the reset token
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user['id']);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Your password has been reset successfully. You can now log in.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Failed to reset password. Please try again.";
            }
        } else {
            $error = "Invalid or expired reset token.";
        }
        $stmt->close();
    }
} else {
    $reset_token = isset($_GET['token']) ? $_GET['token'] : '';
    if (empty($reset_token)) {
        die("Error: No reset token provided.");
    }
}
?>

<?php include 'header.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-hero py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="glass-card animate-fade-in-up">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gradient mb-2">Reset Password</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Enter your new password below.
                </p>
            </div>

            <?php if (isset($error)): ?>
                <div class="mt-4 glass-feature p-4 text-red-500 rounded-xl" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mt-4 glass-feature p-4 text-green-500 rounded-xl" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST">
                <?php echo csrf_input_field(); ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($reset_token) ?>">

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                    <input type="password" id="new_password" name="new_password" required class="glass-input">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="glass-input">
                </div>

                <button type="submit" class="glass-button-primary w-full py-2 px-4 rounded-xl">
                    Reset Password
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                Remember your password? <a href="login.php" class="font-medium text-red-500 hover:text-red-600">Sign in</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
