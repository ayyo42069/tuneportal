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
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

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

<main class="flex-grow mt-16 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Login</h2>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <?php echo csrf_input_field(); ?>
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email or Username</label>
                    <input type="text" id="login" name="login" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <button type="submit" 
                        class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition-colors">
                    Login
                </button>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                Don't have an account? <a href="register.php" class="text-red-600 dark:text-red-400 hover:underline">Register here</a>
            </p>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
