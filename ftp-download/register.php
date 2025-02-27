<?php
include 'config.php';

// Ensure CSRF functions are defined in config.php
if (!function_exists('verify_csrf_token')) {
    die("Error: CSRF token function is missing.");
}

$password_requirements = [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_number' => true,
    'require_special' => true
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    // Sanitize inputs
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $password_confirm = sanitize($_POST['password_confirm']);
    $ip = sanitize($_SERVER['REMOTE_ADDR']);
    $user_agent = sanitize($_SERVER['HTTP_USER_AGENT']);

    // Validate password strength
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        die("Error: Password must be at least 8 characters and include an uppercase letter, a number, and a special character.");
    }

    // Check if passwords match
    if ($password !== $password_confirm) {
        die("Error: Passwords do not match.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }

    // Rate limiting (prevent excessive registration attempts from the same IP)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempt_time > NOW() - INTERVAL 1 HOUR");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->bind_result($attempts);
    $stmt->fetch();
    $stmt->close();

    if ($attempts > 3) {
        die("Error: Too many registration attempts. Try again later.");
    }

    // Check if email or username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Error: Email or username already exists. Please use different credentials.");
    }

    $stmt->close();

    // Generate a unique verification token
    $verification_token = bin2hex(random_bytes(32));

    // Insert new user with unverified status
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, ip, user_agent, role, credits, banned, verification_token, verified) VALUES (?, ?, ?, ?, ?, 'user', 0, 0, ?, 0)");
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("ssssss", $username, $email, $hashed_password, $ip, $user_agent, $verification_token);


if ($stmt->execute()) {
    // Send verification email
    $verification_link = "https://tuning-portal.eu/verify_email.php?token=" . $verification_token;
    $subject = "Verify Your Email";
    
    // Load the email template
    $email_template = file_get_contents('email_verification_template.html');
    
    // Replace the placeholder with the actual verification link
    $message = str_replace('%%VERIFICATION_LINK%%', $verification_link, $email_template);
    
    $headers = "From: noreply@tuning-portal.eu\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($email, $subject, $message, $headers)) {
        $_SESSION['success'] = "Registration successful. Please check your email to verify your account.";
    } else {
        die("Error: Failed to send verification email.");
    }
} else {
    die("Error: Registration failed. Please try again.");
}
}
?>

<?php include 'header.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-hero py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="glass-card animate-fade-in-up">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gradient mb-2">Create your account</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Join our community of tuning enthusiasts</p>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mt-4 glass-feature p-4 text-green-500 rounded-xl" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="mt-4 glass-feature p-4 text-red-500 rounded-xl" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST">
                <?php echo csrf_input_field(); ?>
                
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                        <input type="text" id="username" name="username" required class="glass-input" onkeyup="checkUsername(this.value)">
                        <span id="username-availability" class="text-sm"></span>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" id="email" name="email" required class="glass-input">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input type="password" id="password" name="password" required class="glass-input" onkeyup="checkPasswordStrength(this.value)">
                        <div id="password-strength" class="mt-2">
                            <div class="h-2 rounded-full bg-gray-200/20 dark:bg-gray-700/20">
                                <div id="strength-bar" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <p id="strength-text" class="text-sm mt-1 text-gray-600 dark:text-gray-400"></p>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" required class="glass-input">
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="terms" name="terms" required class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        I agree to the <a href="terms.php" class="text-red-500 hover:text-red-600">Terms and Conditions</a>
                    </label>
                </div>

                <button type="submit" class="glass-button-primary w-full py-2 px-4 rounded-xl">Create Account</button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                Already have an account? 
                <a href="login.php" class="font-medium text-red-500 hover:text-red-600">Sign in</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>