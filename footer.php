<?php

declare(strict_types=1);

$social_twitter = function_exists('get_field') ? (string) get_field('social_twitter', 'option') : '';
$social_website = function_exists('get_field') ? (string) get_field('social_website', 'option') : '';
$logo_src = mazaq_theme_logo_url(96);
?>
<footer role="contentinfo" class="delight-footer">
    <span class="delight-footer__stage" aria-hidden="true"></span>
    <div class="delight-footer__inner container mx-auto px-4">
        <div class="delight-footer__grid">
            <div class="delight-footer__col delight-footer__col--brand">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="delight-footer__brand focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2">
                    <img src="<?php echo esc_url($logo_src); ?>" alt="<?php bloginfo('name'); ?>" class="delight-footer__logo" loading="lazy" decoding="async" width="96" height="93" data-no-lazy="1">
                </a>
                <p class="delight-footer__desc">مجلة رقمية تهتم بشؤون الفن السابع. مراجعات، قوائم، تحليلات وأخبار السينما العالمية نقربها للمشاهد العربي بأسلوب عصري وحيوي.</p>
                <?php if ($social_twitter || $social_website) : ?>
                <div class="delight-footer__social">
                    <?php if ($social_twitter): ?>
                        <a href="<?php echo esc_url($social_twitter); ?>" aria-label="<?php esc_attr_e('تابعنا على تويتر', 'mazaq'); ?>" class="delight-footer__btn">
                            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M23 4.9c-.8.36-1.66.6-2.56.71a4.48 4.48 0 001.96-2.47 8.94 8.94 0 01-2.83 1.08 4.46 4.46 0 00-7.6 4.07A12.66 12.66 0 012.8 3.65a4.46 4.46 0 001.38 5.95 4.42 4.42 0 01-2.02-.56v.06a4.46 4.46 0 003.58 4.37 4.47 4.47 0 01-2.01.08 4.46 4.46 0 004.16 3.1A8.95 8.95 0 011 18.57a12.62 12.62 0 006.84 2c8.2 0 12.7-6.8 12.7-12.7l-.01-.58A9.07 9.07 0 0023 4.9z"></path></svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($social_website): ?>
                        <a href="<?php echo esc_url($social_website); ?>" aria-label="<?php esc_attr_e('الموقع الرسمي', 'mazaq'); ?>" class="delight-footer__btn">
                            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.5-2.3 3.9-5.5 3.9-9S14.5 5.3 12 3C9.5 5.3 8.1 8.5 8.1 12s1.4 6.7 3.9 9zM3.5 9h17m-17 6h17"></path></svg>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if (has_nav_menu('footer-sections')) : ?>
            <div class="delight-footer__col">
                <h2 class="delight-footer__heading"><?php esc_html_e('الأقسام', 'mazaq'); ?></h2>
                <?php wp_nav_menu(['theme_location' => 'footer-sections', 'container' => 'nav', 'container_class' => 'delight-footer__nav', 'menu_class' => 'delight-footer__nav-list', 'fallback_cb' => false, 'depth' => 1]); ?>
            </div>
            <?php endif; ?>
            <?php if (has_nav_menu('footer-links')) : ?>
            <div class="delight-footer__col">
                <h2 class="delight-footer__heading"><?php esc_html_e('روابط هامة', 'mazaq'); ?></h2>
                <?php wp_nav_menu(['theme_location' => 'footer-links', 'container' => 'nav', 'container_class' => 'delight-footer__nav', 'menu_class' => 'delight-footer__nav-list', 'fallback_cb' => false, 'depth' => 1]); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="delight-footer__slate">
            <p>&copy; <?php echo esc_html(wp_date('Y')); ?> مذاق السينما. جميع الحقوق محفوظة.</p>
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

<button id="back-to-top" class="back-to-top" aria-label="<?php esc_attr_e('العودة إلى أعلى الصفحة', 'mazaq'); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
