<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunePortal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen">
<header class="bg-red-600 text-white fixed w-full top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <h1 class="text-2xl font-bold">TunePortal</h1>
        <nav>
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="text-red-200"><?= $_SESSION['username'] ?></span>
                <a href="dashboard.php" class="px-2 hover:text-red-200">Dashboard</a>
                <a href="logout.php" class="px-2 hover:text-red-200">Logout</a>
            <?php else: ?>
                <a href="register.php" class="px-2 hover:text-red-200">Register</a>
                <a href="login.php" class="px-2 hover:text-red-200">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>