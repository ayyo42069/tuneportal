<?php
include 'config.php'; // Database connection

// Fetch statistics
try {
    $stats = $conn->query("
    SELECT 
        COALESCE((SELECT COUNT(*) FROM files WHERE status = 'processed'), 0) as tuned_files,
        COALESCE((SELECT COUNT(DISTINCT user_id) FROM files WHERE status = 'processed'), 0) as active_tuners,
        COALESCE((SELECT COUNT(*) FROM file_versions), 0) as total_tunes,
        COALESCE((SELECT COUNT(DISTINCT car_model) FROM files), 0) as unique_models
");
$stats = $stats->fetch_assoc();

    // Fetch latest successful tunes
    $latest_tunes = $conn->query("
        SELECT f.title, f.car_model, u.username, fv.uploaded_at 
        FROM files f 
        JOIN users u ON f.user_id = u.id 
        JOIN file_versions fv ON f.id = fv.file_id 
        WHERE f.status = 'processed' 
        ORDER BY fv.uploaded_at DESC 
        LIMIT 5
    ");

    // Fetch top tuners
    $top_tuners = $conn->query("
        SELECT u.username, u.id, COUNT(f.id) as tune_count 
        FROM users u 
        JOIN files f ON u.id = f.user_id 
        WHERE f.status = 'processed' 
        GROUP BY u.id 
        ORDER BY tune_count DESC 
        LIMIT 3
    ");
} catch (Exception $e) {
    $stats = [
        'tuned_files' => 0,
        'active_tuners' => 0,
        'total_tunes' => 0,
        'unique_models' => 0
    ];
    error_log("Database error: " . $e->getMessage());
}
?>
<?php include 'header.php'; ?>

<!-- Custom styles for the landing page -->
<style>
    .parallax-bg {
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }
    
    .text-glow {
        text-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
    }
    
    .card-hover-effect {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .card-hover-effect:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(239, 68, 68, 0.2);
    }
    
    .stats-counter {
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .animated-gradient {
        background-size: 200% 200%;
        animation: gradientShift 5s ease infinite;
    }
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .reveal-text {
        position: relative;
        overflow: hidden;
    }
    
    .reveal-text::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        background: #EF4444;
        animation: revealText 1.5s cubic-bezier(0.77, 0, 0.175, 1) forwards;
    }
    
    @keyframes revealText {
        0% { transform: translateX(0); }
        100% { transform: translateX(101%); }
    }
    
    .floating {
        animation: floating 3s ease-in-out infinite;
    }
    
    @keyframes floating {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
    
    .pulse-ring {
        position: relative;
    }
    
    .pulse-ring::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: inherit;
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    
    .gallery-item {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .gallery-item:hover {
        transform: scale(1.05);
        z-index: 10;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 100;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
    }
    
    .modal-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90%;
    }
    
    .close-modal {
        position: absolute;
        top: 20px;
        right: 30px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
        cursor: pointer;
    }
    
    .close-modal:hover {
        color: #EF4444;
    }
</style>

<main class="flex-grow pt-0">
    <!-- Hero Section with Dynamic Background -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Video Background with Enhanced Overlay -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-r from-black/95 via-black/85 to-black/80 z-10"></div>
            <video class="w-full h-full object-cover lazy-video" 
                   autoplay muted loop playsinline 
                   poster="/src/images/video-poster.jpg"
                   preload="metadata">
                <source data-src="/src/videos/car-tuning.mp4" type="video/mp4">
                <!-- Fallback message -->
                <p class="text-white">Your browser doesn't support HTML5 video.</p>
            </video>
            <!-- Loading spinner -->
            <div class="video-loading absolute inset-0 flex items-center justify-center bg-black/90 z-20">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-500"></div>
            </div>
        </div>

        <!-- Animated particles overlay -->
        <div class="absolute inset-0 z-15 opacity-30">
            <div id="particles-js"></div>
        </div>

        <!-- Hero Content Container -->
        <div class="relative z-20 container mx-auto px-4 py-16">
            <!-- Stats Counter Bar -->
            <div class="stats-counter rounded-2xl p-4 mb-12 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="p-3">
                    <div class="text-3xl font-bold text-red-500 count-up" data-count="<?= $stats['tuned_files'] ?>">0</div>
                    <div class="text-sm text-gray-300">Tuned Files</div>
                </div>
                <div class="p-3">
                    <div class="text-3xl font-bold text-red-500 count-up" data-count="<?= $stats['active_tuners'] ?>">0</div>
                    <div class="text-sm text-gray-300">Active Tuners</div>
                </div>
                <div class="p-3">
                    <div class="text-3xl font-bold text-red-500 count-up" data-count="<?= $stats['total_tunes'] ?>">0</div>
                    <div class="text-sm text-gray-300">Total Tunes</div>
                </div>
                <div class="p-3">
                    <div class="text-3xl font-bold text-red-500 count-up" data-count="<?= $stats['unique_models'] ?>">0</div>
                    <div class="text-sm text-gray-300">Unique Models</div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center w-full">
                <!-- Left Column - Content -->
                <div class="glass-hero p-8 sm:p-10 lg:p-12 rounded-3xl text-white space-y-8 backdrop-blur-lg 
                            bg-black/20 border border-white/10 order-2 lg:order-1 card-hover-effect">
                    <div class="inline-flex items-center space-x-2 px-4 py-2 bg-red-600/20 backdrop-blur-sm rounded-full pulse-ring">
                        <span class="animate-pulse w-2 h-2 bg-red-500 rounded-full"></span>
                        <span class="text-red-400 font-semibold">Professional ECU Remapping & Tuning</span>
                    </div>

                    <div class="space-y-6">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight reveal-text">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-200">Unleash</span>
                            <span class="relative inline-block">
                                <span class="bg-gradient-to-r from-red-500 via-orange-500 to-red-500 text-transparent bg-clip-text animated-gradient">Maximum</span>
                                <svg class="absolute -bottom-2 sm:-bottom-3 lg:-bottom-4 left-0 w-full" viewBox="0 0 100 12" preserveAspectRatio="none">
                                    <path d="M0,0 Q50,12 100,0" stroke="url(#gradient)" stroke-width="4" fill="none"/>
                                    <defs>
                                        <linearGradient id="gradient">
                                            <stop offset="0%" stop-color="#EF4444"/>
                                            <stop offset="50%" stop-color="#F97316"/>
                                            <stop offset="100%" stop-color="#EF4444"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </span>
                            <br class="hidden sm:block">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-200">Performance</span>
                        </h1>

                        <p class="text-lg sm:text-xl text-gray-300 max-w-xl">
                            Precision ECU tuning, turbo optimization, and custom mapping by certified automotive engineers. Experience the difference with our professional tuning solutions.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 pt-4">
                            <?php if(!isset($_SESSION['user_id'])): ?>
                                <a href="register.php" 
                                   class="group relative px-8 py-4 bg-gradient-to-r from-red-600 to-red-700 rounded-xl 
                                          font-semibold inline-flex items-center justify-center overflow-hidden">
                                    <span class="relative z-10 flex items-center text-white">
                                        Unlock Your Engine
                                        <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-orange-500 opacity-0 
                                              group-hover:opacity-100 transition-opacity duration-300"></div>
                                </a>
                            <?php endif; ?>
                            <a href="#features" 
                               class="group px-8 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl 
                                      font-semibold inline-flex items-center justify-center hover:bg-white/20 transition-all">
                                <span class="text-white flex items-center">
                                    View Performance Gains
                                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-y-1 transition-transform" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Interactive 3D Engine Model -->
                <div class="glass-hero p-6 sm:p-8 lg:p-10 rounded-3xl text-white backdrop-blur-lg 
                            bg-black/20 border border-white/10 order-1 lg:order-2 card-hover-effect">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold bg-gradient-to-r from-red-500 to-orange-500 text-transparent bg-clip-text">Interactive Performance</h3>
                        <div class="flex space-x-2">
                            <button id="view-dyno" class="px-3 py-1 bg-red-600/30 rounded-lg text-sm text-white hover:bg-red-600/50 transition-colors">
                                Dyno Chart
                            </button>
                            <button id="view-engine" class="px-3 py-1 bg-red-600/30 rounded-lg text-sm text-white hover:bg-red-600/50 transition-colors">
                                3D Engine
                            </button>
                        </div>
                    </div>
                    
                    <!-- Interactive Engine/Dyno Container -->
                    <div class="relative h-80 w-full">
                        <!-- Dyno Chart View (Default) -->
                        <div id="dyno-view" class="absolute inset-0">
                            <!-- Custom SVG Performance Graph -->
                            <svg class="w-full h-full" viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg">
                                <!-- Graph background -->
                                <rect x="0" y="0" width="400" height="200" fill="rgba(0,0,0,0.2)" rx="8" ry="8"/>
                                
                                <!-- Grid lines -->
                                <g stroke="rgba(255,255,255,0.1)" stroke-width="1">
                                    <line x1="50" y1="20" x2="50" y2="180"/>
                                    <line x1="50" y1="180" x2="380" y2="180"/>
                                    
                                    <!-- Horizontal grid lines -->
                                    <line x1="50" y1="140" x2="380" y2="140" stroke-dasharray="2,2"/>
                                    <line x1="50" y1="100" x2="380" y2="100" stroke-dasharray="2,2"/>
                                    <line x1="50" y1="60" x2="380" y2="60" stroke-dasharray="2,2"/>
                                    <line x1="50" y1="20" x2="380" y2="20" stroke-dasharray="2,2"/>
                                    
                                    <!-- Vertical grid lines -->
                                    <line x1="132" y1="20" x2="132" y2="180" stroke-dasharray="2,2"/>
                                    <line x1="214" y1="20" x2="214" y2="180" stroke-dasharray="2,2"/>
                                    <line x1="296" y1="20" x2="296" y2="180" stroke-dasharray="2,2"/>
                                    <line x1="380" y1="20" x2="380" y2="180" stroke-dasharray="2,2"/>
                                </g>
                                
                                <!-- Y-axis labels (Power) -->
                                <text x="40" y="180" text-anchor="end" fill="white" font-size="10">0</text>
                                <text x="40" y="140" text-anchor="end" fill="white" font-size="10">100</text>
                                <text x="40" y="100" text-anchor="end" fill="white" font-size="10">200</text>
                                <text x="40" y="60" text-anchor="end" fill="white" font-size="10">300</text>
                                <text x="40" y="20" text-anchor="end" fill="white" font-size="10">400</text>
                                
                                <!-- X-axis labels (RPM) -->
                                <text x="50" y="195" text-anchor="middle" fill="white" font-size="10">1000</text>
                                <text x="132" y="195" text-anchor="middle" fill="white" font-size="10">2000</text>
                                <text x="214" y="195" text-anchor="middle" fill="white" font-size="10">4000</text>
                                <text x="296" y="195" text-anchor="middle" fill="white" font-size="10">6000</text>
                                <text x="380" y="195" text-anchor="middle" fill="white" font-size="10">8000</text>
                                
                                <!-- Axis titles -->
                                <text x="215" y="15" text-anchor="middle" fill="white" font-size="12" font-weight="bold">Horsepower & Torque Curves</text>
                                <text x="20" y="100" text-anchor="middle" fill="white" font-size="10" transform="rotate(-90, 20, 100)">Power (HP/TQ)</text>
                                <text x="215" y="198" text-anchor="middle" fill="white" font-size="10">Engine RPM</text>
                                
                                <!-- Stock Power Curve -->
                                <path d="M50,160 C80,130 120,110 180,100 S280,95 320,110 Q350,120 380,140" 
                                      fill="none" stroke="#6B7280" stroke-width="3" class="stock-curve">
                                    <animate attributeName="stroke-dashoffset" from="1000" to="0" dur="2s" fill="freeze" />
                                </path>
                                
                                <!-- Tuned Power Curve -->
                                <path d="M50,160 C90,120 130,90 190,65 S290,55 330,70 Q360,80 380,100" 
                                      fill="none" stroke="#EF4444" stroke-width="3" stroke-dasharray="1000" stroke-dashoffset="1000" class="tuned-curve">
                                    <animate attributeName="stroke-dashoffset" from="1000" to="0" dur="2s" begin="0.5s" fill="freeze" />
                                </path>
                                      
                                <!-- Stock Torque Curve (dashed) -->
                                <path d="M50,140 C90,100 140,90 190,95 S280,115 330,150 Q360,165 380,180" 
                                      fill="none" stroke="#6B7280" stroke-width="3" stroke-dasharray="5,3" class="stock-torque">
                                    <animate attributeName="stroke-dashoffset" from="1000" to="0" dur="2s" begin="1s" fill="freeze" />
                                </path>
                                
                                <!-- Tuned Torque Curve (dashed) -->
                                <path d="M50,120 C90,80 140,60 190,70 S280,90 330,130 Q360,155 380,170" 
                                      fill="none" stroke="#EF4444" stroke-width="3" stroke-dasharray="5,3" stroke-dashoffset="1000" class="tuned-torque">
                                    <animate attributeName="stroke-dashoffset" from="1000" to="0" dur="2s" begin="1.5s" fill="freeze" />
                                </path>
                                
                                <!-- Power gain highlights -->
                                <g fill="rgba(239,68,68,0.2)" opacity="0" class="power-gain">
                                    <animate attributeName="opacity" from="0" to="0.8" dur="1s" begin="2s" fill="freeze" />
                                    <path d="M190,65 L190,100 L100,100 L100,65 Z"/>
                                    <path d="M330,70 L330,110 L280,110 L280,70 Z"/>
                                </g>
                                
                                <!-- Power gain labels -->
                                <g opacity="0" class="gain-labels">
                                    <animate attributeName="opacity" from="0" to="1" dur="1s" begin="2.5s" fill="freeze" />
                                    <text x="145" y="85" text-anchor="middle" fill="white" font-size="10">+35%</text>
                                    <text x="305" y="95" text-anchor="middle" fill="white" font-size="10">+28%</text>
                                </g>
                                
                                <!-- Turbo boost indicator -->
                                <circle cx="190" cy="65" r="5" fill="rgba(239,68,68,0.8)" opacity="0" class="boost-indicator">
                                    <animate attributeName="opacity" from="0" to="0.8" dur="0.5s" begin="3s" fill="freeze" />
                                    <animate attributeName="opacity" values="0.8;0.3;0.8" dur="2s" begin="3.5s" repeatCount="indefinite"/>
                                </circle>
                                <text x="190" y="55" text-anchor="middle" fill="white" font-size="8" opacity="0" class="boost-label">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="3s" fill="freeze" />
                                    Max Boost
                                </text>
                            </svg>
                            
                            <div class="mt-6 flex justify-between">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-500 count-up" data-count="35">0</div>
                                    <div class="text-sm text-gray-400">Horsepower Gain</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-500 count-up" data-count="28">0</div>
                                    <div class="text-sm text-gray-400">Torque Gain</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-500 count-up" data-count="15">0</div>
                                    <div class="text-sm text-gray-400">Fuel Efficiency</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 3D Engine View (Hidden by default) -->
                        <div id="engine-view" class="absolute inset-0 hidden">
                            <!-- 3D Engine SVG -->
                            <svg class="w-full h-full" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                                <!-- Engine Block -->
                                <g class="engine-block" opacity="0">
                                    <animate attributeName="opacity" from="0" to="1" dur="1s" fill="freeze" />
                                    <!-- Engine Base -->
                                    <rect x="100" y="150" width="200" height="100" rx="5" fill="#333" stroke="#222" stroke-width="2"/>
                                    
                                    <!-- Cylinder Head -->
                                    <rect x="90" y="120" width="220" height="30" rx="3" fill="#444" stroke="#222" stroke-width="2"/>
                                    
                                    <!-- Intake Manifold -->
                                    <path d="M90,120 L70,90 H330 L310,120" fill="#555" stroke="#222" stroke-width="2"/>
                                    
                                    <!-- Cylinders -->
                                    <g class="cylinders">
                                        <circle cx="130" cy="135" r="15" fill="#222" stroke="#111" stroke-width="1"/>
                                        <circle cx="170" cy="135" r="15" fill="#222" stroke="#111" stroke-width="1"/>
                                        <circle cx="210" cy="135" r="15" fill="#222" stroke="#111" stroke-width="1"/>
                                        <circle cx="250" cy="135" r="15" fill="#222" stroke="#111" stroke-width="1"/>
                                        <circle cx="290" cy="135" r="15" fill="#222" stroke="#111" stroke-width="1"/>
                                    </g>
                                    
                                    <!-- Turbocharger -->
                                    <g class="turbo">
                                        <circle cx="330" cy="180" r="25" fill="#666" stroke="#444" stroke-width="2"/>
                                        <circle cx="330" cy="180" r="15" fill="#555" stroke="#444" stroke-width="1"/>
                                        <circle cx="330" cy="180" r="8" fill="#EF4444">
                                            <animate attributeName="fill" values="#EF4444;#FCD34D;#EF4444" dur="1s" repeatCount="indefinite"/>
                                        </circle>
                                        <path d="M305,180 H280" stroke="#555" stroke-width="6" stroke-linecap="round"/>
                                        <path d="M355,180 H370" stroke="#555" stroke-width="6" stroke-linecap="round"/>
                                    </g>
                                    
                                    <!-- ECU -->
                                    <g class="ecu">
                                        <rect x="120" y="200" width="60" height="30" rx="2" fill="#222" stroke="#111" stroke-width="1"/>
                                        <rect x="125" y="205" width="50" height="20" rx="1" fill="#111"/>
                                        <circle cx="130" cy="210" r="2" fill="#EF4444">
                                            <animate attributeName="fill" values="#EF4444;#22C55E;#EF4444" dur="2s" repeatCount="indefinite"/>
                                        </circle>
                                        <line x1="135" y1="210" x2="170" y2="210" stroke="#22C55E" stroke-width="1"/>
                                        <line x1="135" y1="215" x2="160" y2="215" stroke="#3B82F6" stroke-width="1"/>
                                        <line x1="135" y1="220" x2="165" y2="220" stroke="#EF4444" stroke-width="1"/>
                                    </g>
                                    
                                    <!-- Connecting Lines -->
                                    <g class="connecting-lines" stroke="#777" stroke-width="1" stroke-dasharray="3,2">
                                        <path d="M180,200 C180,180 200,170 210,135"/>
                                        <path d="M150,200 C150,180 170,170 170,135"/>
                                        <path d="M160,200 C160,190 230,190 250,135"/>
                                        <path d="M170,200 C170,185 270,185 290,135"/>
                                        <path d="M140,200 C140,175 120,165 130,135"/>
                                    </g>
                                </g>
                                
                                <!-- Tuning Hotspots -->
                                <g class="tuning-hotspots" opacity="0">
                                    <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="1s" fill="freeze" />
                                    <circle cx="150" cy="210" r="8" fill="rgba(239,68,68,0.5)" class="hotspot" data-target="ecu">
                                        <animate attributeName="r" values="8;10;8" dur="2s" repeatCount="indefinite"/>
                                    </circle>
                                    <circle cx="330" cy="180" r="8" fill="rgba(239,68,68,0.5)" class="hotspot" data-target="turbo">
                                        <animate attributeName="r" values="8;10;8" dur="2s" repeatCount="indefinite"/>
                                    </circle>
                                    <circle cx="210" cy="135" r="8" fill="rgba(239,68,68,0.5)" class="hotspot" data-target="cylinders">
                                        <animate attributeName="r" values="8;10;8" dur="2s" repeatCount="indefinite"/>
                                    </circle>
                                </g>
                                
                                <!-- Tuning Info Popups -->
                                <g class="tuning-info" opacity="0">
                                    <rect x="50" y="50" width="300" height="80" rx="5" fill="rgba(0,0,0,0.8)" stroke="#EF4444" stroke-width="2"/>
                                    <text x="60" y="75" fill="white" font-size="14" class="info-title">ECU Remapping</text>
                                    <text x="60" y="100" fill="#999" font-size="12" class="info-desc">Custom engine management for optimal performance</text>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <div class="p-2 rounded-full bg-white/10 backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
        </div>
    </section>
    <!-- Features Section with 3D Cards -->
    <section id="features" class="relative overflow-hidden section-spacing backdrop-blur-sm bg-gradient-to-b from-slate-900/90 to-black/90 dark:from-gray-900/90 dark:to-black/90">
        <div class="absolute inset-0 bg-[url('/src/images/grid-pattern.svg')] opacity-5"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-5xl font-bold text-white mb-6">Why Choose Tuning Portal?</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-red-500 to-orange-500 mx-auto rounded-full"></div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-12">
                <?php 
                $features = [
                    [
                        'title' => 'Advanced ECU Tuning',
                        'description' => 'State-of-the-art performance optimization with real-time monitoring and adjustments.',
                        'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'
                    ],
                    [
                        'title' => 'Real-Time Analytics',
                        'description' => 'Monitor your vehicle\'s performance in real-time with our advanced analytics dashboard.',
                        'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
                    ],
                    [
                        'title' => '24/7 Expert Support',
                        'description' => 'Our certified tuning experts are available around the clock to assist you with any issues.',
                        'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'
                    ]
                ];

                foreach ($features as $feature): ?>
                    <div class="group perspective">
                        <div class="relative transform transition-all duration-500 group-hover:rotate-y-12">
                            <div class="absolute inset-0 bg-gradient-to-r from-red-500/30 to-orange-500/30 rounded-2xl transform -rotate-y-12 group-hover:rotate-y-0 transition-all duration-500 opacity-0 group-hover:opacity-100 backdrop-blur-lg"></div>
                            <div class="glass-feature relative rounded-2xl p-8 transform group-hover:rotate-y-12 transition-all duration-500">
                                <div class="w-16 h-16 bg-red-600/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600/20 transition-colors">
                                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $feature['icon'] ?>"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold mb-4 text-white"><?= $feature['title'] ?></h3>
                                <p class="text-gray-400"><?= $feature['description'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Latest Tunes Section with Animated Cards -->
    <section id="latest-tunes" class="relative overflow-hidden section-spacing bg-black dark:bg-gray-900">
        <div class="absolute inset-0 bg-[url('/src/images/texture.jpg')] opacity-5"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-5xl font-bold text-white mb-6">Latest Successful Tunes</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-red-500 to-orange-500 mx-auto rounded-full"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php while ($tune = $latest_tunes->fetch_assoc()): ?>
                    <div class="group bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 
                                transform hover:scale-105 transition-all duration-500 hover:shadow-2xl hover:shadow-red-500/20">
                        <div class="flex items-center justify-between mb-6">
                            <div class="bg-red-600/20 rounded-lg p-3">
                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <span class="text-gray-400 text-sm"><?= date('M d, Y', strtotime($tune['uploaded_at'])) ?></span>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3 group-hover:text-red-500 transition-colors">
                            <?= htmlspecialchars($tune['title'] ?? '') ?>
                        </h3>
                        <p class="text-gray-400 mb-4"><?= htmlspecialchars($tune['car_model'] ?? '') ?></p>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="ml-3 text-gray-400">Tuned by <span class="text-red-500 font-medium">
                                <?= htmlspecialchars($tune['username'] ?? '') ?>
                            </span></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Enhanced CTA Section -->
    <section class="relative overflow-hidden section-spacing bg-gradient-to-br from-red-600 to-orange-600 dark:from-red-700 dark:to-orange-700">
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-5xl md:text-6xl font-bold text-white mb-8">Ready to Transform Your Vehicle?</h2>
                <p class="text-xl text-white/80 mb-12 max-w-2xl mx-auto">
                    Join thousands of satisfied customers who have unlocked their vehicle's true potential with our professional tuning solutions.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" 
                           class="group px-8 py-4 bg-white text-red-600 rounded-lg font-semibold inline-flex items-center justify-center
                                  hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                            Get Started Now
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <a href="#contact" 
                       class="group px-8 py-4 border-2 border-white text-white rounded-lg font-semibold inline-flex items-center justify-center
                              hover:bg-white hover:text-red-600 transition-all duration-300">
                        Contact Support
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>
 <!-- Initialize particles.js -->
 <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        // Particles.js Configuration
        particlesJS("particles-js", {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: "#ffffff" },
                shape: { type: "circle" },
                opacity: {
                    value: 0.5,
                    random: true,
                    animation: { enable: true, speed: 1, minimumValue: 0.1, sync: false }
                },
                size: {
                    value: 3,
                    random: true,
                    animation: { enable: true, speed: 2, minimumValue: 0.1, sync: false }
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: "#ffffff",
                    opacity: 0.4,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: "none",
                    random: false,
                    straight: false,
                    outMode: "out",
                    bounce: false,
                }
            },
            interactivity: {
                detectsOn: "canvas",
                events: {
                    onhover: { enable: true, mode: "repulse" },
                    onclick: { enable: true, mode: "push" },
                    resize: true
                },
                modes: {
                    repulse: { distance: 100, duration: 0.4 },
                    push: { particles_nb: 4 }
                }
            },
            retina_detect: true
        });

        // Interactive Engine/Dyno View Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const dynoView = document.getElementById('dyno-view');
            const engineView = document.getElementById('engine-view');
            const dynoBtn = document.getElementById('view-dyno');
            const engineBtn = document.getElementById('view-engine');

            dynoBtn.addEventListener('click', function() {
                dynoView.classList.remove('hidden');
                engineView.classList.add('hidden');
                dynoBtn.classList.add('bg-red-600/50');
                engineBtn.classList.remove('bg-red-600/50');
            });

            engineBtn.addEventListener('click', function() {
                engineView.classList.remove('hidden');
                dynoView.classList.add('hidden');
                engineBtn.classList.add('bg-red-600/50');
                dynoBtn.classList.remove('bg-red-600/50');
            });

            // Engine Hotspots Interaction
            const hotspots = document.querySelectorAll('.hotspot');
            const tuningInfo = document.querySelector('.tuning-info');

            hotspots.forEach(hotspot => {
                hotspot.addEventListener('mouseover', function() {
                    const target = this.getAttribute('data-target');
                    showTuningInfo(target);
                });

                hotspot.addEventListener('mouseout', function() {
                    hideTuningInfo();
                });
            });

            function showTuningInfo(target) {
                tuningInfo.style.opacity = 1;
                // Update info text based on target
                const infoTitle = tuningInfo.querySelector('.info-title');
                const infoDesc = tuningInfo.querySelector('.info-desc');
                
                switch(target) {
                    case 'ecu':
                        infoTitle.textContent = 'ECU Remapping';
                        infoDesc.textContent = 'Custom engine management for optimal performance';
                        break;
                    case 'turbo':
                        infoTitle.textContent = 'Turbo Optimization';
                        infoDesc.textContent = 'Enhanced boost control and efficiency';
                        break;
                    case 'cylinders':
                        infoTitle.textContent = 'Cylinder Tuning';
                        infoDesc.textContent = 'Precision fuel and timing adjustments';
                        break;
                }
            }

            function hideTuningInfo() {
                tuningInfo.style.opacity = 0;
            }
        });
    </script>

<?php include 'footer.php'; ?>