<?php
include 'header.php';
include 'config.php';

try {
    // Get total tuned vehicles
    $stmt = $pdo->query("SELECT COUNT(*) FROM files");
    $totalTuned = $stmt->fetchColumn();

    // Get total certified tuners (admins)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $totalTuners = $stmt->fetchColumn();

    // Get recent expert tunes
    $stmt = $pdo->query("SELECT f.*, u.username 
                        FROM files f
                        JOIN users u ON f.user_id = u.id
                        WHERE u.role = 'admin'
                        ORDER BY f.created_at DESC 
                        LIMIT 3");
    $recentTunes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get tuning packages
    $stmt = $pdo->query("SELECT * FROM tuning_options ORDER BY credit_cost ASC");
    $tuningPackages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get expert testimonials
    $stmt = $pdo->query("SELECT u.username, f.car_model, f.description 
                       FROM files f
                       JOIN users u ON f.user_id = u.id
                       WHERE f.description IS NOT NULL
                       AND u.role = 'admin'
                       ORDER BY f.created_at DESC 
                       LIMIT 2");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunePortal - Professional Automotive Tuning</title>
</head>
<body class="flex flex-col min-h-screen">
<?php include 'header.php'; ?>

<main class="flex-grow mt-16">
    <!-- Hero Section -->
    <section class="relative h-[600px] bg-gradient-to-r from-red-600 to-red-700">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70" 
                 alt="Performance Car" 
                 class="w-full h-full object-cover opacity-40">
        </div>
        
        <div class="relative z-10 h-full flex items-center">
            <div class="container mx-auto px-4 text-center text-white">
                <div class="live-stats absolute top-4 right-4 bg-black/30 p-4 rounded-lg backdrop-blur-sm">
                    <div class="flex space-x-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold count-up" data-count="<?= $totalTuned ?>">0</div>
                            <div class="text-sm">Tuned Vehicles</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold count-up" data-count="<?= $totalTuners ?>">0</div>
                            <div class="text-sm">Certified Tuners</div>
                        </div>
                    </div>
                </div>
                
                <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in-up">
                    Professional ECU Tuning Solutions
                </h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto">
                    Maximize performance with certified tuning experts and industry-leading tools
                </p>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="flex justify-center gap-4">
                        <a href="register.php" 
                           class="inline-block bg-white text-red-600 px-8 py-3 rounded-full text-lg font-semibold 
                                  hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                            Get Started
                        </a>
                        <a href="#packages" 
                           class="inline-block border-2 border-white text-white px-8 py-3 rounded-full text-lg font-semibold 
                                  hover:bg-white hover:text-red-600 transition-all duration-300">
                            View Packages
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mt-4">
                        <a href="dashboard.php" 
                           class="inline-block bg-black/30 text-white px-8 py-3 rounded-full text-lg font-semibold 
                                  hover:bg-black/40 transition-all duration-300">
                            Go to Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Tuning Packages -->
    <section id="packages" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-red-600">Tuning Packages</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($tuningPackages as $package): ?>
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                    <div class="text-center mb-4">
                        <span class="text-4xl font-bold text-red-600"><?= $package['credit_cost'] ?></span>
                        <span class="text-gray-600">Credits</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-center"><?= htmlspecialchars($package['name']) ?></h3>
                    <p class="text-gray-600 mb-6"><?= htmlspecialchars($package['description']) ?></p>
                    <div class="text-center">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php?purchase=<?= $package['id'] ?>" 
                               class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                Purchase Now
                            </a>
                        <?php else: ?>
                            <a href="register.php" 
                               class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                Sign Up to Purchase
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Recent Expert Tunes -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-red-600">Recent Expert Tunes</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($recentTunes as $tune): ?>
                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <div class="font-semibold"><?= htmlspecialchars($tune['car_model']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($tune['title']) ?></div>
                            </div>
                        </div>
                        <div class="text-red-600 font-bold">v<?= $tune['current_version'] ?></div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Certified Tuner:</span>
                            <span class="font-semibold"><?= htmlspecialchars($tune['username']) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Tuned On:</span>
                            <span class="font-semibold">
                                <?= date('M j, Y', strtotime($tune['created_at'])) ?>
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Status:</span>
                            <span class="font-semibold capitalize <?= $tune['status'] === 'processed' ? 'text-green-600' : 'text-yellow-600' ?>">
                                <?= $tune['status'] ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Expert Testimonials -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-red-600">Expert Insights</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <?php foreach($testimonials as $testimonial): ?>
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white">
                            <?= strtoupper(substr($testimonial['username'], 0, 1)) ?>
                        </div>
                        <div class="ml-4">
                            <div class="font-semibold"><?= htmlspecialchars($testimonial['username']) ?></div>
                            <div class="text-sm text-gray-500">
                                <span class="bg-red-100 text-red-600 px-2 py-1 rounded-full text-xs">
                                    Certified Tuner
                                </span>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600">"<?= htmlspecialchars($testimonial['description']) ?>"</p>
                    <div class="mt-4 text-sm text-gray-500">
                        Vehicle: <?= htmlspecialchars($testimonial['car_model']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<script>
// Animated counter
document.addEventListener('DOMContentLoaded', () => {
    const animateValue = (obj, start, end, duration) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.textContent = Math.floor(progress * (end - start) + start);
            if (progress < 1) window.requestAnimationFrame(step);
        };
        window.requestAnimationFrame(step);
    };

    document.querySelectorAll('.count-up').forEach(element => {
        const target = parseInt(element.getAttribute('data-count'));
        animateValue(element, 0, target, 2000);
    });
});
</script>
</body>
</html>