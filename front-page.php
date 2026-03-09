<?php get_header(); ?>

<?php
$sections = [
    'hero' => ['slug' => 'hero', 'priority' => get_theme_mod('toc_hp_hero_priority', 1), 'enabled' => get_theme_mod('toc_hp_hero_enabled', true)],
    'articles' => ['slug' => 'articles', 'priority' => get_theme_mod('toc_hp_articles_priority', 2), 'enabled' => get_theme_mod('toc_hp_articles_enabled', true)],
    'categories' => ['slug' => 'categories', 'priority' => get_theme_mod('toc_hp_categories_priority', 3), 'enabled' => get_theme_mod('toc_hp_categories_enabled', false)],
    'banner' => ['slug' => 'banner', 'priority' => get_theme_mod('toc_hp_banner_priority', 4), 'enabled' => get_theme_mod('toc_hp_banner_enabled', false)],
    'sidebar' => ['slug' => 'sidebar', 'priority' => get_theme_mod('toc_hp_sidebar_priority', 5), 'enabled' => get_theme_mod('toc_hp_sidebar_enabled', true)],
];
uasort($sections, function($a, $b) { return $a['priority'] <=> $b['priority']; });

$enabled_sections = array_filter($sections, function($s) { return $s['enabled']; });
?>

<?php if (empty($enabled_sections)): ?>
    <main class="max-w-7xl mx-auto px-4 py-20 mb-16 text-center">
        <h1 class="text-3xl font-bold mb-6 text-slate-900 dark:text-white">مرحباً بك في <?php bloginfo('name'); ?></h1>
        <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto"><?php bloginfo('description'); ?></p>
    </main>
<?php else: ?>
    <main class="front-page-sections flex flex-col pt-0 pb-16">
        <?php
        // Buffer the main content blocks so we can layout correctly
        $main_content = '';
        ob_start();
        foreach ($enabled_sections as $s) {
            if ($s['slug'] !== 'sidebar' && $s['slug'] !== 'hero') {
                get_template_part('template-parts/homepage/section', $s['slug']);
            }
        }
        $main_content = ob_get_clean();
        
        // Loop again to output exactly in priority order, but wrap sidebar/articles together
        $flex_started = false;
        
        foreach ($enabled_sections as $s) {
            if ($s['slug'] === 'hero') {
                // Hero breaks out, so if we started flex we must end it (though hero is usually first)
                if ($flex_started) {
                    echo '</div></div></div>';
                    $flex_started = false;
                }
                get_template_part('template-parts/homepage/section', 'hero');
                echo '<div class="h-8"></div>'; // Some spacing
            } else {
                if (!$flex_started) {
                    echo '<div class="max-w-7xl mx-auto px-4 w-full">';
                    echo '<div class="flex flex-col lg:flex-row gap-12">';
                    echo '<div class="w-full ' . (!empty($enabled_sections['sidebar']) ? 'lg:w-2/3' : '') . ' flex flex-col gap-16">';
                    $flex_started = true;
                    // Echo main content once inside the flex layout
                    echo $main_content;
                    echo '</div>'; // end w-full / lg:w-2/3
                    
                    if (!empty($enabled_sections['sidebar'])) {
                        get_template_part('template-parts/homepage/section', 'sidebar');
                    }
                    echo '</div></div>';
                }
            }
        }
        
        if ($flex_started) {
            // It's closed directly after main content within the loop.
        }
        ?>
    </main>
<?php endif; ?>

<?php get_footer(); ?>
