<?php

declare(strict_types=1);

$og_title = wp_get_document_title();
$og_description = is_singular() ? wp_strip_all_tags((string) get_the_excerpt()) : get_bloginfo('description');
$og_url = is_singular() ? get_permalink() : home_url('/');
$og_type = is_singular('post') ? 'article' : 'website';
$og_image = '';
if (is_singular() && has_post_thumbnail()) {
    $og_image_data = wp_get_attachment_image_src((int) get_post_thumbnail_id(), 'full');
    $og_image = $og_image_data ? $og_image_data[0] : '';
}
if (!$og_image) {
    $og_image = get_template_directory_uri() . '/assets/images/og-cover.jpg';
}
?>
<!doctype html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="<?php echo esc_attr($og_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($og_description); ?>">
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <meta property="og:url" content="<?php echo esc_url($og_url); ?>">
    <meta property="og:type" content="<?php echo esc_attr($og_type); ?>">
    <meta property="og:locale" content="ar_AR">
    <link rel="preload" href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/fonts/ibm-plex-sans-arabic-arabic-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/fonts/tajawal-arabic-800-normal.woff2" as="font" type="font/woff2" crossorigin>
    <?php if (is_singular('post')) : ?>
        <link rel="preload" href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/fonts/amiri-arabic-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    <?php endif; ?>
    <script>
        (function(){
            document.documentElement.classList.add('toc-js');
            var isDark = localStorage.getItem('color-theme') === 'dark' || (!localStorage.getItem('color-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if(isDark){ document.documentElement.classList.add('dark'); }
            try {
                var storedFont = localStorage.getItem('mazaq-article-font-size');
                if (storedFont) {
                    var val = parseFloat(storedFont);
                    if (val >= 0.875 && val <= 1.625) {
                        document.documentElement.style.setProperty('--article-font-size-custom', val + 'rem');
                    }
                }
            } catch (e) {}
        })();
    </script>
    <script>
        (function () {
            try {
                var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                if (
                    sessionStorage.getItem('toc_loader_seen') === 'true' ||
                    (connection && (connection.saveData || connection.effectiveType === '2g'))
                ) {
                    document.documentElement.classList.add('toc-loader-seen');
                }
            } catch (e) {
                document.documentElement.classList.add('toc-loader-seen');
            }
        })();
    </script>
    <noscript>
        <style>html.toc-loader-seen #toc-site-loader, #toc-site-loader { display: none !important; }</style>
    </noscript>
    <meta name="google-adsense-account" content="ca-pub-8042646813554704">
    <?php wp_head(); ?>
</head>
<?php $is_overlay_page = is_front_page(); ?>
<body <?php body_class('bg-primary-cream dark:bg-nocturnal text-slate-900 dark:text-white transition-colors duration-300 antialiased font-sans' . ($is_overlay_page ? ' toc-overlay-nav' : '')); ?>>
<?php wp_body_open(); ?>
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-[200] focus:bg-primary focus:text-nocturnal-slate focus:px-4 focus:py-2 focus:rounded-lg focus:text-sm focus:font-bold focus:shadow-lg"><?php esc_html_e('تخطى إلى المحتوى', 'mazaq'); ?></a>
<!-- T003: Brand-Aligned Site Loader -->
<div id="toc-site-loader" class="toc-site-loader fixed inset-0 z-[100] flex items-center justify-center" role="status" aria-live="polite" aria-label="<?php echo esc_attr__('Loading site', 'mazaq'); ?>">
    <div class="toc-loader-core" aria-hidden="true">
        <span class="toc-loader-glow"></span>
        <span class="toc-loader-ring toc-loader-ring--outer"></span>
        <span class="toc-loader-ring toc-loader-ring--inner"></span>
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.webp'); ?>" alt="" class="toc-loader-logo dark:brightness-125" decoding="async" width="474" height="460" data-no-lazy="1">
    </div>
</div>

<header id="toc-masthead" class="delight-masthead sticky top-0 z-40<?php echo $is_overlay_page ? ' delight-masthead--overlay-page delight-masthead--overlay' : ' delight-masthead--solid'; ?>">
    <span class="delight-masthead__scrim" aria-hidden="true"></span>
    <span class="delight-masthead__surface" aria-hidden="true"></span>

    <div class="delight-masthead__inner container mx-auto w-full px-4">
        <div class="delight-masthead__cluster delight-masthead__cluster--start">
            <button id="mobile-menu-toggle" aria-label="<?php esc_attr_e('فتح القائمة', 'mazaq'); ?>" aria-expanded="false" class="delight-masthead__btn lg:hidden">
                <svg class="w-6 h-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            <nav aria-label="<?php esc_attr_e('القائمة الرئيسية', 'mazaq'); ?>" class="hidden lg:block">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary-menu',
                    'container' => false,
                    'menu_class' => 'delight-masthead__nav-list',
                    'fallback_cb' => false,
                    'depth' => 2,
                ]);
                ?>
            </nav>
        </div>

        <a href="<?php echo esc_url(home_url('/')); ?>" class="delight-masthead__brand focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.webp'); ?>" alt="<?php bloginfo('name'); ?>" class="delight-masthead__logo" width="474" height="460" data-no-lazy="1">
        </a>

        <div class="delight-masthead__cluster delight-masthead__cluster--end">
            <button id="search-toggle" aria-label="<?php esc_attr_e('فتح البحث', 'mazaq'); ?>" class="delight-masthead__btn">
                <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
            <button id="theme-toggle" aria-label="<?php esc_attr_e('تبديل الوضع الليلي', 'mazaq'); ?>" aria-pressed="false" class="delight-masthead__btn">
                <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
        </div>
    </div>
</header>

<?php get_template_part('template-parts/navigation/mobile-menu'); ?>
<?php get_template_part('template-parts/navigation/search-overlay'); ?>
