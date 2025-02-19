<?php
include 'config.php';

$password_requirements = [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_number' => true,
    'require_special' => true
];

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

<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Create your account</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Join our community of tuning enthusiasts
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
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                    <input type="text" id="username" name="username" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm dark:bg-gray-700"
                           onkeyup="checkUsername(this.value)">
                    <span id="username-availability" class="text-sm"></span>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" id="email" name="email" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm dark:bg-gray-700">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm dark:bg-gray-700"
                           onkeyup="checkPasswordStrength(this.value)">
                    <div id="password-strength" class="mt-2">
                        <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                            <div id="strength-bar" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="strength-text" class="text-sm mt-1 text-gray-600 dark:text-gray-400"></p>
                    </div>
                </div>

                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm dark:bg-gray-700">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="terms" name="terms" required
                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                    I agree to the <a href="terms.php" class="text-red-600 hover:text-red-500">Terms and Conditions</a>
                </label>
            </div>

            <button type="submit" 
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Create Account
            </button>
        </form>

        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            Already have an account? 
            <a href="login.php" class="font-medium text-red-600 hover:text-red-500">Sign in</a>
        </p>
    </div>
</div>

<script>
function checkPasswordStrength(password) {
    let strength = 0;
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    if (password.length >= 8) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 25;
    if (password.match(/[^A-Za-z0-9]/)) strength += 25;

    strengthBar.style.width = strength + '%';
    
    if (strength <= 25) {
        strengthBar.className = 'h-2 rounded-full bg-red-500 transition-all duration-300';
        strengthText.textContent = 'Weak password';
    } else if (strength <= 50) {
        strengthBar.className = 'h-2 rounded-full bg-yellow-500 transition-all duration-300';
        strengthText.textContent = 'Fair password';
    } else if (strength <= 75) {
        strengthBar.className = 'h-2 rounded-full bg-blue-500 transition-all duration-300';
        strengthText.textContent = 'Good password';
    } else {
        strengthBar.className = 'h-2 rounded-full bg-green-500 transition-all duration-300';
        strengthText.textContent = 'Strong password';
    }
}

async function checkUsername(username) {
    if (username.length < 3) return;
    
    try {
        const response = await fetch('check_username.php?username=' + encodeURIComponent(username));
        const data = await response.json();
        const availabilitySpan = document.getElementById('username-availability');
        
        if (data.available) {
            availabilitySpan.textContent = '✓ Username available';
            availabilitySpan.className = 'text-sm text-green-600 dark:text-green-400';
        } else {
            availabilitySpan.textContent = '✗ Username taken';
            availabilitySpan.className = 'text-sm text-red-600 dark:text-red-400';
        }
    } catch (error) {
        console.error('Error checking username:', error);
    }
}
</script>

<?php include 'footer.php'; ?>
