<?php 
include 'header.php';
include 'config.php';

// Get dynamic data
try {
    // Total tuned vehicles
    $stmt = $pdo->query("SELECT COUNT(*) FROM files");
    $totalTuned = $stmt->fetchColumn();

    // Total certified tuners (admins)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $totalTuners = $stmt->fetchColumn();

    // Recent expert tunes
    $stmt = $pdo->query("SELECT f.*, u.username 
                        FROM files f
                        JOIN users u ON f.user_id = u.id
                        WHERE u.role = 'admin'
                        ORDER BY f.created_at DESC 
                        LIMIT 3");
    $recentTunes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Testimonials
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

<main class="flex-grow mt-16">
    <!-- Hero Section with Live Stats -->
    <section class="relative h-[600px] bg-gradient-to-r from-red-600 to-red-700">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
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
                            <div class="text-sm">Expert Tuners</div>
                        </div>
                    </div>
                </div>
                
                <h1 class="text-5xl md:text-6xl font-bold mb-6 animate-fade-in-up">
                    Precision ECU Tuning<br>Perfected
                </h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto">
                    Unleash your vehicle's true potential with professional-grade tuning solutions
                </p>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" 
                       class="inline-block bg-white text-red-600 px-8 py-3 rounded-full text-lg font-semibold 
                              hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                        Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Dynamic Tuning Calculator -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-3xl font-bold text-red-600 mb-8">Performance Calculator</h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Vehicle Type</label>
                            <select class="w-full p-3 border rounded-lg">
                                <option>Sedan</option>
                                <option>SUV</option>
                                <option>Sports Car</option>
                                <option>Pickup Truck</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Current HP</label>
                            <input type="number" class="w-full p-3 border rounded-lg" placeholder="Enter current horsepower">
                        </div>
                    </div>
                    <div class="bg-gray-100 p-6 rounded-lg">
                        <h3 class="text-xl font-semibold mb-4">Estimated Gains</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Horsepower:</span>
                                <span class="font-bold text-red-600">+35 HP</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Torque:</span>
                                <span class="font-bold text-red-600">+42 lb-ft</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Fuel Efficiency:</span>
                                <span class="font-bold text-green-600">+12%</span>
                            </div>
                        </div>
                        <button class="mt-6 w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 transition-colors">
                            Simulate Tune
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Brands Showcase -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h3 class="text-center text-gray-500 mb-8">Trusted by automotive brands worldwide</h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-8 items-center opacity-75">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/90/Mercedes-Logo.svg" alt="Mercedes" class="h-12 mx-auto">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg" alt="BMW" class="h-12 mx-auto">
                <img src="https://upload.wikimedia.org/wikipedia/commons/f/f4/Ford_Motor_Company_Logo.svg" alt="Ford" class="h-12 mx-auto">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5a/Honda_Logo.svg" alt="Honda" class="h-12 mx-auto">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/48/Toyota_emblem.svg" alt="Toyota" class="h-12 mx-auto">
                <img src="https://upload.wikimedia.org/wikipedia/commons/d/df/Subaru_Logo.svg" alt="Subaru" class="h-12 mx-auto">
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-16 text-red-600">Why Choose TunePortal?</h2>
            
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Feature 1 -->
                <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Advanced File Management</h3>
                    <p class="text-gray-600">Secure cloud storage with version control and full history tracking</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Flexible Credit System</h3>
                    <p class="text-gray-600">Pay-per-tune model with transparent pricing and real-time tracking</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h3m-3 4h3m-6 4h3M9 7h3m-3 4h3m-6 4h3M6 17v-4m9 4V7m3 10v-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Expert Tools</h3>
                    <p class="text-gray-600">Professional-grade tuning utilities and analytics</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Carousel -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-red-600">Success Stories</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <?php foreach($testimonials as $testimonial): ?>
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white">
                            <?= strtoupper(substr($testimonial['username'], 0, 1)) ?>
                        </div>
                        <div class="ml-4">
                            <div class="font-semibold"><?= htmlspecialchars($testimonial['username']) ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($testimonial['car_model']) ?></div>
                        </div>
                    </div>
                    <p class="text-gray-600">"<?= htmlspecialchars($testimonial['description']) ?>"</p>
                    <div class="mt-4 flex items-center">
                        <div class="text-sm bg-red-100 text-red-600 px-3 py-1 rounded-full">Dyno Verified</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Latest Tunes Feed -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-red-600">Recently Tuned</h2>
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
                            <span>Tuner:</span>
                            <span class="font-semibold"><?= htmlspecialchars($tune['username']) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Date:</span>
                            <span class="font-semibold">
                                <?= date('M j, Y', strtotime($tune['created_at'])) ?>
                            </span>
                        </div>
                        <div class="pt-2">
                            <div class="h-1 bg-gray-200 rounded-full">
                                <div class="h-1 bg-red-600 rounded-full w-3/4"></div>
                            </div>
                            <div class="text-right text-sm text-gray-500 mt-1">75% Complete</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6">Start Tuning Today</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto opacity-90">
                Join hundreds of professional tuners and enthusiasts already optimizing their vehicles
            </p>
            <div class="flex justify-center space-x-4">
                <a href="register.php" 
                   class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-lg font-semibold transition-colors">
                    Create Free Account
                </a>
                <a href="#features" 
                   class="border border-white hover:bg-white hover:text-gray-900 px-8 py-3 rounded-lg font-semibold transition-colors">
                    Learn More
                </a>
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