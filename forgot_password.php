<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    $login = sanitize($_POST['login']);
    
    // Fetch user by email or username
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $reset_token = bin2hex(random_bytes(32)); // Generate reset token
    
    // Store reset token in database using MySQL's DATE_ADD function
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
    $stmt->bind_param("si", $reset_token, $user['id']);
    
    if ($stmt->execute()) {
        // Send password reset email with the new template
        $reset_link = "https://tuning-portal.eu/reset_password.php?token=" . urlencode($reset_token);
        $subject = "Password Reset Request";
        
        // Load the email template
        $email_template = file_get_contents('password_reset_template.html');
        
        // Replace the placeholder with the actual reset link
        $message = str_replace('%%RESET_LINK%%', $reset_link, $email_template);
        
        $headers = "From: noreply@tuning-portal.eu\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        if (mail($user['email'], $subject, $message, $headers)) {
            $_SESSION['success'] = "Password reset email sent. Please check your email.";
        } else {
            $error = "Failed to send password reset email.";
        }
    } else {
        $error = "Failed to generate reset token.";
    }
} else {
    $error = "No account found with that email or username.";
}
$stmt->close();
}
?>

<?php include 'header.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-hero py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="glass-card animate-fade-in-up">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gradient mb-2">Forgot Password</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Enter your email or username to receive a password reset link.
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

                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username or Email</label>
                    <input type="text" id="login" name="login" required class="glass-input">
                </div>

                <button type="submit" class="glass-button-primary w-full py-2 px-4 rounded-xl">
                    Send Reset Link
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                Remember your password? <a href="login.php" class="font-medium text-red-500 hover:text-red-600">Sign in</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>