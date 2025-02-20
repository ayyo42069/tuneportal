<?php
include 'config.php';

// Add rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
$attempts_window = 15 * 60; // 15 minutes
$max_attempts = 5;

// Clean up old attempts
$stmt = $conn->prepare("DELETE FROM login_attempts WHERE attempt_time < NOW() - INTERVAL ? SECOND");
$stmt->bind_param("i", $attempts_window);
$stmt->execute();

// Check attempts count
$stmt = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempt_time > NOW() - INTERVAL ? SECOND");
$stmt->bind_param("si", $ip, $attempts_window);
$stmt->execute();
$stmt->bind_result($attempts);
$stmt->fetch();
$stmt->close();

if ($attempts >= $max_attempts) {
    $remaining_time = ceil($attempts_window / 60);
    $error = "Too many login attempts. Please try again in {$remaining_time} minutes.";
}
// Add IP-based rate limiting with exponential backoff
function getBackoffTime($attempts) {
    return min(pow(2, $attempts), 30) * 60; // Max 30 minutes
}

$backoff_time = getBackoffTime($attempts);
if ($attempts > 0) {
    $last_attempt = $conn->prepare("SELECT MAX(attempt_time) FROM login_attempts WHERE ip = ?");
    $last_attempt->bind_param("s", $ip);
    $last_attempt->execute();
    $last_attempt->bind_result($last_time);
    $last_attempt->fetch();
    
    if (strtotime($last_time) + $backoff_time > time()) {
        $wait_time = ceil((strtotime($last_time) + $backoff_time - time()) / 60);
        $error = "Please wait {$wait_time} minutes before trying again.";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        // Debug information
        error_log('CSRF Token Mismatch - Received: ' . $token . ', Session Token: ' . ($_SESSION['csrf_token'] ?? 'not set'));
        $error = "Security token validation failed. Please try again.";
    }

    $login = sanitize($_POST['login']);
    $password = sanitize($_POST['password']);

    // Fetch user with role, banned status, and ban reason
    $stmt = $conn->prepare("SELECT id, username, email, password, ip, user_agent, role, banned, ban_reason FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if the user is banned
        if ($user['banned'] == 1) {
            $ban_reason = $user['ban_reason'] ? " Reason: " . htmlspecialchars($user['ban_reason']) : "";
            $error = "Your account has been banned." . $ban_reason . " Please contact support.";
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
                $update_stmt->execute();
                $update_stmt->close();

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
                // After setting the session variables and before the redirect
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

<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Welcome back</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Sign in to access your tuning dashboard
            </p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <?php echo csrf_input_field(); ?>

            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username or Email</label>
                    <input type="text" id="login" name="login" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm dark:bg-gray-700">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm dark:bg-gray-700">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember_me" name="remember_me"
                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="forgot_password.php" class="font-medium text-red-600 hover:text-red-500">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <button type="submit" 
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Sign in
            </button>
        </form>

        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            Don't have an account? 
            <a href="register.php" class="font-medium text-red-600 hover:text-red-500">Register now</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>
