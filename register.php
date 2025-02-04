<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $ip = sanitize($_SERVER['REMOTE_ADDR']);
    $user_agent = sanitize($_SERVER['HTTP_USER_AGENT']);

    // IP-based rate limiting
    $stmt = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempt_time > NOW() - INTERVAL 1 HOUR");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->bind_result($attempts);
    $stmt->fetch();
    $stmt->close();

    if ($attempts > 3) {
        $error = "Too many registration attempts. Try again later.";
    } else {
        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email or username already exists. Please use different credentials.";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, ip, user_agent, role) VALUES (?, ?, ?, ?, ?, 'user')");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $ip, $user_agent);

            if ($stmt->execute()) {
                // Automatically log in the user
                $userId = $stmt->insert_id;
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user'; // Set the role to 'user' by default

                // Log the registration as a successful login attempt
                $stmt = $conn->prepare("
                    INSERT INTO login_history (user_id, ip_address, user_agent, success, security_mismatch)
                    VALUES (?, ?, ?, 1, 0)
                ");
                $stmt->bind_param("iss", $userId, $ip, $user_agent);
                $stmt->execute();

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<main class="flex-grow mt-16 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Register</h2>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <?php echo csrf_input_field(); ?>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                    <input type="text" id="username" name="username" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" id="email" name="email" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <button type="submit" 
                        class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition-colors">
                    Register
                </button>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                Already have an account? <a href="login.php" class="text-red-600 hover:underline">Login here</a>
            </p>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
