<?php

declare(strict_types=1);

$social_twitter = function_exists('get_field') ? (string) get_field('social_twitter', 'option') : '';
$social_website = function_exists('get_field') ? (string) get_field('social_website', 'option') : '';
?>
<footer role="contentinfo" class="bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 transition-colors pt-20 pb-10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 gap-y-12">
            <div class="md:col-span-2">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="block mb-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2 rounded-sm">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.webp'); ?>" alt="<?php bloginfo('name'); ?>" class="h-16 w-auto dark:brightness-125 transition-all" loading="lazy" decoding="async" width="474" height="460" data-no-lazy="1">
                </a>
                <p class="text-slate-600 dark:text-slate-400 max-w-sm leading-relaxed mb-6">مجلة رقمية تهتم بشؤون الفن السابع. مراجعات، قوائم، تحليلات وأخبار السينما العالمية نقربها للمشاهد العربي بأسلوب عصري وحيوي.</p>
                <div class="flex items-center gap-4">
                    <?php if ($social_twitter): ?>
                        <a href="<?php echo esc_url($social_twitter); ?>" aria-label="<?php esc_attr_e('تابعنا على تويتر', 'mazaq'); ?>" class="hover:text-primary transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2 rounded-sm">Twitter</a>
                    <?php endif; ?>
                    <?php if ($social_website): ?>
                        <a href="<?php echo esc_url($social_website); ?>" aria-label="<?php esc_attr_e('الموقع الرسمي', 'mazaq'); ?>" class="hover:text-primary transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2 rounded-sm">Website</a>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5"><?php esc_html_e('الأقسام', 'mazaq'); ?></h2>
                <?php wp_nav_menu(['theme_location' => 'footer-sections', 'container' => 'nav', 'container_class' => 'flex flex-col gap-2.5 text-slate-600 dark:text-slate-400 font-medium', 'menu_class' => 'flex flex-col gap-2.5']); ?>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5"><?php esc_html_e('روابط هامة', 'mazaq'); ?></h2>
                <?php wp_nav_menu(['theme_location' => 'footer-links', 'container' => 'nav', 'container_class' => 'flex flex-col gap-2.5 text-slate-600 dark:text-slate-400 font-medium', 'menu_class' => 'flex flex-col gap-2.5']); ?>
            </div>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-800 mt-16 pt-10 flex flex-col md:flex-row items-center justify-between text-label text-slate-600 dark:text-slate-300 text-center md:text-start">
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

<button id="back-to-top" class="back-to-top" aria-label="العودة إلى أعلى الصفحة">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
