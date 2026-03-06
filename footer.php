<?php

declare(strict_types=1);

$social_twitter = function_exists('get_field') ? (string) get_field('social_twitter', 'option') : '';
$social_website = function_exists('get_field') ? (string) get_field('social_website', 'option') : '';
?>
<footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 transition-colors pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div class="md:col-span-2">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="block mb-6">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.webp'); ?>" alt="<?php bloginfo('name'); ?>" class="h-16 w-auto dark:brightness-125 transition-all">
                </a>
                <p class="text-slate-600 dark:text-slate-400 max-w-sm leading-relaxed mb-6">مجلة رقمية تهتم بشؤون الفن السابع. مراجعات، قوائم، تحليلات وأخبار السينما العالمية نقربها للمشاهد العربي بأسلوب عصري وحيوي.</p>
                <div class="flex items-center gap-4">
                    <?php if ($social_twitter): ?>
                        <a href="<?php echo esc_url($social_twitter); ?>" class="hover:text-primary transition-colors">Twitter</a>
                    <?php endif; ?>
                    <?php if ($social_website): ?>
                        <a href="<?php echo esc_url($social_website); ?>" class="hover:text-primary transition-colors">Website</a>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-6">الأقسام</h4>
                <?php wp_nav_menu(['theme_location' => 'footer-sections', 'container' => false, 'menu_class' => 'flex flex-col gap-3 text-slate-600 dark:text-slate-400 font-medium']); ?>
            </div>
            <div>
                <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-6">روابط هامة</h4>
                <?php wp_nav_menu(['theme_location' => 'footer-links', 'container' => false, 'menu_class' => 'flex flex-col gap-3 text-slate-600 dark:text-slate-400 font-medium']); ?>
            </div>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between text-sm text-slate-500 font-mono text-center md:text-right">
            <p>&copy; <?php echo esc_html((string) date('Y')); ?> مذاق السينما. جميع الحقوق محفوظة.</p>
           
        </div>
    </div>
</footer>

<!-- T005: Loader Session Logic -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('toc-site-loader');
        if (!loader) return;

        // Check if already seen in this session
        if (sessionStorage.getItem('toc_loader_seen') === 'true') {
            // Instantly hide it to prevent flash
            loader.style.display = 'none';
        } else {
            // Show it visibly for debugging just to be sure
            loader.style.opacity = '1';
            loader.style.visibility = 'visible';

            // Wait for full window load, then smoothly hide and set flag
            window.addEventListener('load', function() {
                // Add a small delay for the animation to be appreciated
                setTimeout(function() {
                    loader.style.opacity = '0';
                    loader.style.visibility = 'hidden';
                    loader.style.pointerEvents = 'none';
                    loader.classList.add('toc-loader-hidden');
                    sessionStorage.setItem('toc_loader_seen', 'true');
                    
                    // Remove from DOM after transition completes to free memory
                    setTimeout(function() {
                        if(loader.parentNode) loader.remove();
                    }, 500); // Matches the 500ms duration in tailwind transition class
                }, 1200); // Increased delay
            });
        }
    });
</script>

<?php wp_footer(); ?>
</body>
</html>
