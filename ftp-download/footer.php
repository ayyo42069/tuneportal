<?php
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
$isDashboard = $currentPage === 'dashboard.php';
$footerClass = $isDashboard ? 'lg:ml-64' : 'w-full';
?>

<footer class="relative mt-auto z-30 transition-all duration-300 <?php echo $footerClass; ?>">
    <!-- Glassmorphism background -->
    <div class="absolute inset-0 backdrop-blur-xl bg-white/80 dark:bg-gray-800/80 border-t border-white/10 dark:border-gray-700/20"></div>

    <div class="container mx-auto px-4 relative <?php echo $isDashboard ? 'lg:max-w-[calc(100%-16rem)]' : ''; ?>">
        <?php if ($currentPage === 'index.php'): ?>
            <!-- Full footer for index page -->
            <div class="grid md:grid-cols-3 gap-8 py-12">
                <!-- Brand Section -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="glass-icon p-2 rounded-xl">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold bg-gradient-to-r from-red-500 to-orange-500 text-transparent bg-clip-text">TunePortal</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                        Unlock your vehicle's full potential with professional-grade tuning solutions.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="glass-social-button">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="glass-social-button">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="glass-social-button">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold bg-gradient-to-r from-red-500 to-orange-500 text-transparent bg-clip-text">
                        Quick Links
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="#features" class="footer-link group flex items-center">
                                <span class="glass-dot"></span>
                                <span>Features</span>
                            </a>
                        </li>
                        <li>
                            <a href="#contact" class="footer-link group flex items-center">
                                <span class="glass-dot"></span>
                                <span>Contact</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Contact Section -->
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold bg-gradient-to-r from-red-500 to-orange-500 text-transparent bg-clip-text">
                        Contact
                    </h4>
                    <div class="space-y-4">
                        <a href="mailto:support@tuneportal.com" class="footer-link group flex items-center space-x-3">
                            <span class="glass-icon-sm">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <span>support@tuneportal.com</span>
                        </a>
                        <a href="tel:+15551234567" class="footer-link group flex items-center space-x-3">
                            <span class="glass-icon-sm">
                                <i class="fas fa-phone"></i>
                            </span>
                            <span>+1 (555) 123-4567</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Minimal footer for other pages -->
            <div class="py-4">
        <?php endif; ?>
        
        <!-- Copyright section -->
        <div class="border-t border-white/10 dark:border-gray-700/20 py-4">
            <p class="text-center text-gray-600 dark:text-gray-300">
                &copy; <?= date('Y') ?> TunePortal. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const footer = document.querySelector('footer');
    const sidebar = document.getElementById('sidebar');
    const currentPage = '<?php echo $currentPage; ?>';

    // Only apply sidebar-related adjustments on dashboard
    if (currentPage === 'dashboard.php') {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        if (sidebarToggle && sidebar && footer) {
            sidebarToggle.addEventListener('click', () => {
                footer.classList.toggle('lg:ml-64');
            });
        }

        function adjustFooterPosition() {
            if (window.innerWidth >= 1024 && sidebar && !sidebar.classList.contains('-translate-x-full')) {
                footer.classList.add('lg:ml-64');
            }
        }

        window.addEventListener('resize', adjustFooterPosition);
        adjustFooterPosition();
    }
});
</script>

</body>
</html>