<?php
ob_start(); // Start output buffering

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize the login field.
    $login = sanitize($_POST['login']);
    // Use raw password input (optionally trim whitespace)
    $password = trim($_POST['password']);

    error_log("Login attempt for: $login");

    // Fetch user with role, banned status, and ban reason
    $stmt = $conn->prepare("SELECT id, username, email, password, ip, user_agent, role, banned, ban_reason FROM users WHERE email = ? OR username = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die("An error occurred. Please try again later.");
    }

    $stmt->bind_param("ss", $login, $login);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        die("An error occurred. Please try again later.");
    }
    
    $result = $stmt->get_result();
    if ($result === false) {
        error_log("Get result failed: " . $stmt->error);
        die("An error occurred. Please try again later.");
    }

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['banned'] == 1) {
            $ban_reason = $user['ban_reason'] ? " Reason: " . $user['ban_reason'] : "";
            $error = "Your account has been banned." . $ban_reason . " Please contact support.";
            error_log("Banned user login attempt: " . $user['email'] . " $ban_reason");
        } else {
            if (password_verify($password, $user['password'])) {
                error_log("Password verified. Redirecting user: " . $user['email']);

                $success    = 1;
                $ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                $user_id    = $user['id'];

                $security_mismatch = 0;
                if ($user['ip'] !== $ip || $user['user_agent'] !== $user_agent) {
                    $security_mismatch = 1;
                    error_log("Security mismatch detected for user: " . $user['email']);
                }

                $update_stmt = $conn->prepare("UPDATE users SET ip = ?, user_agent = ? WHERE id = ?");
                if (!$update_stmt) {
                    error_log("Prepare update failed: " . $conn->error);
                } else {
                    $update_stmt->bind_param("ssi", $ip, $user_agent, $user_id);
                    if (!$update_stmt->execute()) {
                        error_log("Update execute failed: " . $update_stmt->error);
                    }
                }

                $log_stmt = $conn->prepare("
                    INSERT INTO login_history (user_id, ip_address, user_agent, success, security_mismatch)
                    VALUES (?, ?, ?, ?, ?)
                ");
                if (!$log_stmt) {
                    error_log("Prepare login_history failed: " . $conn->error);
                } else {
                    $log_stmt->bind_param("issii", $user_id, $ip, $user_agent, $success, $security_mismatch);
                    if (!$log_stmt->execute()) {
                        error_log("Execute login_history failed: " . $log_stmt->error);
                    }
                }

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['role']     = $user['role'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid credentials.";
                error_log("Invalid password for user: " . $login);
            }
        }
    } else {
        $error = "Invalid credentials.";
        error_log("No matching user found for login: " . $login);
    }
}

// Flush the buffer (if any)
ob_end_flush();
?>

<?php include 'header.php'; ?>
<main class="container mx-auto px-4 py-6">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4 text-red-600">Login</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email or Username</label>
                <input type="text" name="login" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-600">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-600">
            </div>
            <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600">
                Login
            </button>
            <p class="mt-4 text-center text-gray-600">
                Don't have an account? <a href="register.php" class="text-red-600 hover:underline">Register here</a>
            </p>
        </form>
    </div>
</main>
<?php include 'footer.php'; ?>
