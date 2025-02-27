<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    $login = sanitize($_POST['login']);
    $password = sanitize($_POST['password']);

    // Fetch user with role, banned status, and ban reason
    $stmt = $conn->prepare("SELECT id, username, email, password, ip, user_agent, role, banned, ban_reason, verified FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if the user is banned
        if ($user['banned'] == 1) {
            $ban_reason = $user['ban_reason'] ? " Reason: " . htmlspecialchars($user['ban_reason']) : "";
            $error = "Your account has been banned." . $ban_reason . " Please contact support.";
        } else if ($user['verified'] == 0) {
            $error = "Your email has not been verified. Please check your email and follow the verification link.";
        } else {
            if (password_verify($password, $user['password'])) {
                // Log the login attempt
                $success = 1;
                $ip = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $user_id = $user['id'];

                // Check for IP or User-Agent mismatch
                $security_mismatch = ($user['ip'] !== $ip || $user['user_agent'] !== $user_agent) ? 1 : 0;

                // Update user's IP and User-Agent in the database
                $update_stmt = $conn->prepare("UPDATE users SET ip = ?, user_agent = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $ip, $user_agent, $user_id);
                
                if ($update_stmt->execute()) {
                    // Log login attempt with security mismatch flag
                    $stmt = $conn->prepare("
                        INSERT INTO login_history (user_id, ip_address, user_agent, success, security_mismatch)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("issii", $user_id, $ip, $user_agent, $success, $security_mismatch);
                    $stmt->execute();
                    $stmt->close();

                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Set session with role
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Add active session tracking
                    $session_id = session_id();
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    $device_type = get_device_type($user_agent); // Add this function to config.php
                    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    $stmt = $conn->prepare("
                        INSERT INTO active_sessions (user_id, session_id, ip_address, user_agent, device_type, expires, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->bind_param("isssss", $user['id'], $session_id, $ip, $user_agent, $device_type, $expires);
                    $stmt->execute();
                    $stmt->close();
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Failed to update user IP and User-Agent.";
                }
            } else {
                $error = "Invalid credentials.";
            }
        }
    } else {
        $error = "Invalid credentials.";
    }

    $stmt->close();
}
?>

<?php include 'header.php'; ?>

<!-- Replace the main container div -->
<div class="min-h-screen flex items-center justify-center bg-gradient-hero py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="glass-card animate-fade-in-up">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gradient mb-2">Welcome back</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Sign in to access your tuning dashboard
                </p>
            </div>

            <?php if (isset($error)): ?>
                <div class="mt-4 glass-feature p-4 text-red-500 rounded-xl" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST">
                <?php echo csrf_input_field(); ?>

                <div class="space-y-4">
                    <div>
                        <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username or Email</label>
                        <input type="text" id="login" name="login" required 
                               class="glass-input">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input type="password" id="password" name="password" required 
                               class="glass-input">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember_me" name="remember_me"
                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="forgot_password.php" class="font-medium text-red-500 hover:text-red-600">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <button type="submit" class="glass-button-primary w-full py-2 px-4 rounded-xl">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                Don't have an account? 
                <a href="register.php" class="font-medium text-red-500 hover:text-red-600">Register now</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>