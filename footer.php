<?php
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
$isDashboard = $currentPage === 'dashboard.php';
$footerClass = $isDashboard ? 'lg:ml-64' : 'w-full';
?>

<footer class="bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 text-gray-800 dark:text-white mt-auto z-30 transition-colors duration-300 <?php echo $footerClass; ?>">
    <div class="container mx-auto px-4 <?php echo $isDashboard ? 'lg:max-w-[calc(100%-16rem)]' : ''; ?>">
        <?php if ($currentPage === 'index.php'): ?>
            <!-- Enhanced footer for index page -->
            <div class="grid md:grid-cols-4 gap-8 py-16">
                <!-- Company Info -->
                <div class="space-y-6 md:col-span-2">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <h3 class="text-2xl font-bold text-red-600 dark:text-red-500">TunePortal</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed max-w-md">
                        Unlock your vehicle's full potential with professional-grade tuning solutions. Join our community of automotive enthusiasts and expert tuners.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-red-100 dark:hover:bg-red-900 hover:text-red-600 dark:hover:text-red-400 transition-all duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-red-100 dark:hover:bg-red-900 hover:text-red-600 dark:hover:text-red-400 transition-all duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-red-100 dark:hover:bg-red-900 hover:text-red-600 dark:hover:text-red-400 transition-all duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-red-100 dark:hover:bg-red-900 hover:text-red-600 dark:hover:text-red-400 transition-all duration-300">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="space-y-6">
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>Features
                        </a></li>
                        <li><a href="#about" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>About Us
                        </a></li>
                        <li><a href="faq.php" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>FAQ
                        </a></li>
                        <li><a href="privacy.php" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>Privacy Policy
                        </a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="space-y-6">
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">Contact Us</h4>
                    <div class="space-y-4">
                        <a href="mailto:support@tuneportal.com" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center group">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3 group-hover:bg-red-100 dark:group-hover:bg-red-900 transition-colors">
                                <i class="fas fa-envelope"></i>
                            </div>
                            support@tuneportal.com
                        </a>
                        <a href="tel:+15551234567" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center group">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3 group-hover:bg-red-100 dark:group-hover:bg-red-900 transition-colors">
                                <i class="fas fa-phone"></i>
                            </div>
                            +1 (555) 123-4567
                        </a>
                        <div class="text-gray-600 dark:text-gray-300 flex items-center group">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            Mon - Fri: 9AM - 6PM
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Minimal footer for other pages -->
            <div class="py-6">
                <div class="flex justify-center space-x-6">
                    <a href="privacy.php" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors">Privacy Policy</a>
                    <a href="terms.php" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors">Terms of Service</a>
                    <a href="contact.php" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors">Contact</a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Enhanced Copyright section -->
        <div class="border-t border-gray-200 dark:border-gray-700 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <p class="text-gray-600 dark:text-gray-300">
                    &copy; <?= date('Y') ?> TunePortal. All rights reserved.
                </p>
                <div class="flex items-center space-x-4">
                    <img src="/assets/images/payment-methods.png" alt="Payment Methods" class="h-6">
                    <img src="/assets/images/secure-badge.png" alt="Secure Payment" class="h-6">
                </div>
            </div>
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
