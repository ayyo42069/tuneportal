<?php include 'header.php'; ?>

<main class="flex-grow">
    <!-- Hero Section with Geometric Background -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gray-900">
        <div class="absolute inset-0 bg-gradient-to-br from-red-500/[0.15] via-transparent to-blue-500/[0.15] blur-3xl"></div>

        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute left-[-10%] md:left-[-5%] top-[15%] md:top-[20%] w-[600px] h-[140px] rotate-12 float">
                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-red-500/[0.15] to-transparent backdrop-blur-[2px] border-2 border-white/[0.15] shadow-[0_8px_32px_0_rgba(255,255,255,0.1)] after:absolute after:inset-0 after:rounded-full after:bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.2),transparent_70%)]"></div>
            </div>

            <div class="absolute right-[-5%] md:right-[0%] top-[70%] md:top-[75%] w-[500px] h-[120px] -rotate-15 float">
                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-500/[0.15] to-transparent backdrop-blur-[2px] border-2 border-white/[0.15] shadow-[0_8px_32px_0_rgba(255,255,255,0.1)] after:absolute after:inset-0 after:rounded-full after:bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.2),transparent_70%)]"></div>
            </div>

            <div class="absolute left-[5%] md:left-[10%] bottom-[5%] md:bottom-[10%] w-[300px] h-[80px] -rotate-8 float">
                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-500/[0.15] to-transparent backdrop-blur-[2px] border-2 border-white/[0.15] shadow-[0_8px_32px_0_rgba(255,255,255,0.1)] after:absolute after:inset-0 after:rounded-full after:bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.2),transparent_70%)]"></div>
            </div>
        </div>

        <div class="relative z-10 container mx-auto px-4 text-center">
            <div class="live-stats absolute top-4 right-4 bg-black/30 p-4 rounded-lg backdrop-blur-sm fade-up" style="animation-delay: 0.5s;">
                <div class="flex space-x-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold count-up" data-count="2543">0</div>
                        <div class="text-sm">Tuned Vehicles</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold count-up" data-count="178">0</div>
                        <div class="text-sm">Expert Tuners</div>
                    </div>
                </div>
            </div>
            
            <h1 class="text-5xl md:text-6xl font-bold mb-6 fade-up" style="animation-delay: 0.7s;">
                Precision ECU Tuning<br>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-red-400 via-white to-blue-400">Perfected</span>
            </h1>
            <p class="text-xl mb-8 max-w-2xl mx-auto fade-up" style="animation-delay: 0.9s;">
                Unleash your vehicle's true potential with professional-grade tuning solutions
            </p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" 
                   class="inline-block bg-gradient-to-r from-red-600 to-blue-600 text-white px-8 py-3 rounded-full text-lg font-semibold 
                          hover:from-red-700 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 fade-up"
                   style="animation-delay: 1.1s;">
                    Get Started
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Brands Showcase -->
    <section class="py-12 bg-gray-800">
        <div class="container mx-auto px-4">
            <h3 class="text-center text-gray-400 mb-8">Trusted by automotive brands worldwide</h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-8 items-center opacity-75">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/90/Mercedes-Logo.svg" alt="Mercedes" class="h-12 mx-auto invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg" alt="BMW" class="h-12 mx-auto invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/f/f4/Ford_Motor_Company_Logo.svg" alt="Ford" class="h-12 mx-auto invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5a/Honda_Logo.svg" alt="Honda" class="h-12 mx-auto invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/48/Toyota_emblem.svg" alt="Toyota" class="h-12 mx-auto invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/d/df/Subaru_Logo.svg" alt="Subaru" class="h-12 mx-auto invert">
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-20 bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-16 text-red-400">Why Choose TunePortal?</h2>
            
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Feature 1 -->
                <div class="p-6 bg-gray-800 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-white">Advanced File Management</h3>
                    <p class="text-gray-400">Secure cloud storage with version control and full history tracking</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 bg-gray-800 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-blue-500/20 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-white">Flexible Credit System</h3>
                    <p class="text-gray-400">Pay-per-tune model with transparent pricing and real-time tracking</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 bg-gray-800 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h3m-3 4h3m-6 4h3M9 7h3m-3 4h3m-6 4h3M6 17v-4m9 4V7m3 10v-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-white">Expert Tools</h3>
                    <p class="text-gray-400">Professional-grade tuning utilities and analytics</p>
                </div>
            </div>
        </div>
    </section>
    

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-red-600 to-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6">Start Tuning Today</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto opacity-90">
                Join hundreds of professional tuners and enthusiasts already optimizing their vehicles
            </p>
            <div class="flex justify-center space-x-4">
                <a href="register.php" 
                   class="bg-white text-gray-900 hover:bg-gray-100 px-8 py-3 rounded-lg font-semibold transition-colors">
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

