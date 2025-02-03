<?php include 'header.php'; ?>

<main class="flex-grow mt-16">
    <!-- Hero Section with Hexagonal Tech Overlay -->
    <section class="relative h-[700px] bg-gradient-to-br from-gray-900 via-gray-800 to-red-900 overflow-hidden">
        <!-- Hexagonal Background Pattern -->
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute -top-20 -left-10 w-full h-full hexagon-bg"></div>
        </div>
        
        <!-- Blurred Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
                 alt="Performance Car" 
                 class="w-full h-full object-cover opacity-20 blur-sm">
        </div>
        
        <div class="relative z-10 h-full flex items-center">
            <div class="container mx-auto px-4 text-center text-white">
                <!-- Tech-inspired Live Stats -->
                <div class="live-stats absolute top-4 right-4 bg-black/40 p-6 rounded-xl backdrop-blur-md border border-gray-700">
                    <div class="flex space-x-8">
                        <div class="text-center border-r border-gray-600 pr-6">
                            <div class="text-3xl font-bold text-red-400 count-up" data-count="2543">0</div>
                            <div class="text-xs uppercase tracking-wider text-gray-300">Tuned Vehicles</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-400 count-up" data-count="178">0</div>
                            <div class="text-xs uppercase tracking-wider text-gray-300">Expert Tuners</div>
                        </div>
                    </div>
                </div>
                
                <h1 class="text-5xl md:text-6xl font-bold mb-6 animate-fade-in-up bg-clip-text text-transparent bg-gradient-to-r from-red-400 to-blue-500">
                    Precision ECU Tuning<br>Perfected
                </h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto text-gray-200">
                    Unleash your vehicle's true potential with next-generation tuning solutions
                </p>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" 
                       class="inline-block bg-gradient-to-r from-red-500 to-blue-600 text-white px-10 py-4 rounded-full text-lg font-semibold 
                              hover:from-red-600 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl">
                        Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Brands Showcase with Hexagonal Overlay -->
    <section class="py-16 bg-gray-50 relative">
        <div class="absolute top-0 left-0 w-full h-full opacity-5 pointer-events-none">
            <div class="hexagon-overlay"></div>
        </div>
        <div class="container mx-auto px-4 relative z-10">
            <h3 class="text-center text-gray-600 mb-12 text-xl">Trusted by Automotive Innovators</h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-8 items-center opacity-80">
                <div class="hexagon-brand-container"><img src="https://upload.wikimedia.org/wikipedia/commons/9/90/Mercedes-Logo.svg" alt="Mercedes" class="hexagon-brand-logo"></div>
                <div class="hexagon-brand-container"><img src="https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg" alt="BMW" class="hexagon-brand-logo"></div>
                <div class="hexagon-brand-container"><img src="https://upload.wikimedia.org/wikipedia/commons/f/f4/Ford_Motor_Company_Logo.svg" alt="Ford" class="hexagon-brand-logo"></div>
                <div class="hexagon-brand-container"><img src="https://upload.wikimedia.org/wikipedia/commons/5/5a/Honda_Logo.svg" alt="Honda" class="hexagon-brand-logo"></div>
                <div class="hexagon-brand-container"><img src="https://upload.wikimedia.org/wikipedia/commons/4/48/Toyota_emblem.svg" alt="Toyota" class="hexagon-brand-logo"></div>
                <div class="hexagon-brand-container"><img src="https://upload.wikimedia.org/wikipedia/commons/d/df/Subaru_Logo.svg" alt="Subaru" class="hexagon-brand-logo"></div>
            </div>
        </div>
    </section>

    <!-- Features Grid with Hexagonal Icons -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-blue-600">Why Choose TunePortal?</h2>
            
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Feature 1 -->
                <div class="p-8 bg-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl transition-all group">
                    <div class="hexagon-icon-container mb-6">
                        <svg class="w-10 h-10 text-red-500 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Advanced File Management</h3>
                    <p class="text-gray-600">Secure cloud storage with intelligent version control and comprehensive history tracking</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-8 bg-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl transition-all group">
                    <div class="hexagon-icon-container mb-6">
                        <svg class="w-10 h-10 text-blue-500 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Flexible Credit System</h3>
                    <p class="text-gray-600">Advanced pay-per-tune model with transparent pricing and real-time financial tracking</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-8 bg-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl transition-all group">
                    <div class="hexagon-icon-container mb-6">
                        <svg class="w-10 h-10 text-green-500 group-hover:text-green-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h3m-3 4h3m-6 4h3M9 7h3m-3 4h3m-6 4h3M6 17v-4m9 4V7m3 10v-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Expert Tools</h3>
                    <p class="text-gray-600">Professional-grade tuning utilities with advanced analytics and precision diagnostics</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section with Gradient and Hexagonal Overlay -->
    <section class="bg-gradient-to-br from-gray-900 to-red-900 text-white py-20 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute -bottom-20 -right-10 w-full h-full hexagon-bg"></div>
        </div>
        <div class="container mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl font-bold mb-6 text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-blue-500">Start Tuning Today</h2>
            <p class="text-xl mb-10 max-w-2xl mx-auto opacity-90 text-gray-200">
                Join a cutting-edge community of professionals transforming automotive performance
            </p>
            <div class="flex justify-center space-x-6">
                <a href="register.php" 
                   class="bg-gradient-to-r from-red-500 to-blue-600 text-white px-10 py-4 rounded-full font-semibold 
                          hover:from-red-600 hover:to-blue-700 transition-all transform hover:scale-105 hover:shadow-2xl">
                    Create Free Account
                </a>
                <a href="#features" 
                   class="border-2 border-white/30 text-white px-10 py-4 rounded-full font-semibold 
                          hover:bg-white/10 transition-all transform hover:scale-105">
                    Learn More
                </a>
            </div>
        </div>
    </section>
</main>

<!-- Additional Styling for Hexagonal Elements -->
<style>
    .hexagon-bg {
        background-image: 
            linear-gradient(30deg, rgba(255,255,255,0.05) 12%, transparent 12%, transparent 88%, rgba(255,255,255,0.05) 88%),
            linear-gradient(150deg, rgba(255,255,255,0.05) 12%, transparent 12%, transparent 88%, rgba(255,255,255,0.05) 88%);
        background-size: 50px 100px;
        transform: rotate(-30deg);
    }

    .hexagon-overlay {
        background-image: 
            linear-gradient(30deg, rgba(0,0,0,0.03) 12%, transparent 12%, transparent 88%, rgba(0,0,0,0.03) 88%),
            linear-gradient(150deg, rgba(0,0,0,0.03) 12%, transparent 12%, transparent 88%, rgba(0,0,0,0.03) 88%);
        background-size: 50px 100px;
        height: 100%;
        width: 100%;
    }

    .hexagon-icon-container {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
        display: flex;
        align-items: center;
        justify-content: center;
        clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
        transition: all 0.3s ease;
    }

    .hexagon-brand-container {
        display: flex;
        justify-content: center;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .hexagon-brand-container:hover {
        transform: scale(1.1) rotate(5deg);
    }

    .hexagon-brand-logo {
        max-height: 60px;
        max-width: 100px;
        filter: grayscale(100%) opacity(70%);
        transition: all 0.3s ease;
    }

    .hexagon-brand-container:hover .hexagon-brand-logo {
        filter: grayscale(0%) opacity(100%);
    }
</style>

<?php include 'footer.php'; ?>