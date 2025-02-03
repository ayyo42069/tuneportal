<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

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
<main class="container mx-auto px-4 py-6">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4 text-red-600">Login</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
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
