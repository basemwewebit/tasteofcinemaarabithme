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
        const SESSION_KEY = 'toc_loader_seen';
        const MIN_VISIBLE_MS = 450;
        const MAX_VISIBLE_MS = 2500;
        const HIDE_TRANSITION_MS = 450;

        const storage = (function() {
            try {
                const probeKey = '__toc_loader_probe__';
                sessionStorage.setItem(probeKey, '1');
                sessionStorage.removeItem(probeKey);
                return sessionStorage;
            } catch (e) {
                return null;
            }
        })();

        if (!storage) {
            loader.style.display = 'none';
            return;
        }

        if (storage.getItem(SESSION_KEY) === 'true') {
            loader.style.display = 'none';
            return;
        }

        const startTime = Date.now();
        let hideStarted = false;
        let finished = false;

        const hideLoader = function() {
            if (hideStarted) return;
            hideStarted = true;
            loader.classList.add('toc-loader-hidden');
            loader.setAttribute('aria-hidden', 'true');
            window.setTimeout(function() {
                if (loader.parentNode) {
                    loader.remove();
                }
            }, HIDE_TRANSITION_MS);
        };

        const completeLoader = function() {
            if (finished) return;
            finished = true;
            storage.setItem(SESSION_KEY, 'true');
            const elapsed = Date.now() - startTime;
            const waitForMinDuration = Math.max(0, MIN_VISIBLE_MS - elapsed);
            window.setTimeout(hideLoader, waitForMinDuration);
        };

        const loadOrTimeout = function() {
            completeLoader();
        };

        const hardTimeout = window.setTimeout(loadOrTimeout, MAX_VISIBLE_MS);
        if (document.readyState === 'complete') {
            window.clearTimeout(hardTimeout);
            loadOrTimeout();
        } else {
            window.addEventListener('load', function() {
                window.clearTimeout(hardTimeout);
                loadOrTimeout();
            }, { once: true });
        }
    });
</script>

<?php wp_footer(); ?>
</body>
</html>
