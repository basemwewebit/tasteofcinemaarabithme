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
<html <?php language_attributes(); ?> dir="rtl" class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="<?php echo esc_attr($og_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($og_description); ?>">
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <meta property="og:url" content="<?php echo esc_url($og_url); ?>">
    <meta property="og:type" content="<?php echo esc_attr($og_type); ?>">
    <meta property="og:locale" content="ar_AR">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script>
        (function(){
            var isDark = localStorage.getItem('color-theme') === 'dark' || (!localStorage.getItem('color-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if(isDark){ document.documentElement.classList.add('dark'); }
        })();
    </script>
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 transition-colors duration-300 antialiased font-sans'); ?>>
<?php wp_body_open(); ?>

<header class="sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors">
    <div class="container mx-auto px-4 h-20 flex items-center justify-between">
        <button id="mobile-menu-toggle" aria-label="Toggle Mobile Menu" class="lg:hidden p-2 text-slate-600 dark:text-slate-300 hover:text-primary transition-colors focus:outline-none">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary-menu',
                'container' => false,
                'menu_class' => 'flex items-center gap-6',
                'fallback_cb' => false,
            ]);
            ?>
        </nav>

        <a href="<?php echo esc_url(home_url('/')); ?>" class="block">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.png'); ?>" alt="<?php bloginfo('name'); ?>" class="h-12 w-auto dark:brightness-125 transition-all">
        </a>

        <div class="flex items-center gap-4">
            <button id="search-toggle" aria-label="Toggle Search" class="p-2 text-slate-600 dark:text-slate-300 hover:text-primary transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
            <button id="theme-toggle" aria-label="Toggle Dark Mode" class="p-2 text-slate-600 dark:text-slate-300 hover:text-primary transition-colors focus:outline-none"></button>
        </div>
    </div>
</header>

<?php get_template_part('template-parts/navigation/mobile-menu'); ?>
<?php get_template_part('template-parts/navigation/search-overlay'); ?>
