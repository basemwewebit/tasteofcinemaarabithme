<?php

declare(strict_types=1);

$mazaq_includes = [
    'inc/theme-setup.php',
    'inc/enqueue.php',
    'inc/scf-fields.php',
    'inc/ads.php',
    'inc/analytics.php',
    'inc/post-views.php',
    'inc/contact-form.php',
    'inc/infinite-scroll.php',
    'inc/breadcrumb.php',
    'inc/helpers.php',
    'inc/loader-ads-customizer.php',
];

foreach ($mazaq_includes as $file) {
    $path = get_template_directory() . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

/**
 * T001: Generate custom breadcrumb markup.
 */
function toc_breadcrumbs(): void
{
    echo '<nav class="flex text-sm text-slate-500 mb-6 font-medium" aria-label="breadcrumb"><ol class="inline-flex items-center gap-2">';
    echo '<li><a href="' . esc_url(home_url('/')) . '" class="hover:text-primary">' . esc_html__('الرئيسية', 'mazaq') . '</a></li>';

    if (is_single()) {
        $cats = get_the_category();
        if (!empty($cats)) {
            echo '<li><span class="text-slate-400">/</span></li>';
            echo '<li><a href="' . esc_url(get_category_link($cats[0]->term_id)) . '" class="hover:text-primary">' . esc_html($cats[0]->name) . '</a></li>';
        }
        echo '<li><span class="text-slate-400">/</span></li>';
        echo '<li class="text-slate-800 dark:text-slate-300" aria-current="page">' . esc_html(get_the_title()) . '</li>';
    } elseif (is_category()) {
        echo '<li><span class="text-slate-400">/</span></li><li class="text-slate-800 dark:text-slate-300" aria-current="page">' . single_cat_title('', false) . '</li>';
    }

    echo '</ol></nav>';
}

/**
 * T004: Calculate estimated reading time.
 */
function toc_estimated_reading_time($content = '') {
    if (empty($content)) {
        global $post;
        $content = $post->post_content ?? '';
    }
    
    // Use preg_split with utf8 mode to correctly count Arabic words
    $words = preg_split('/\s+/u', trim(strip_tags($content)), -1, PREG_SPLIT_NO_EMPTY);
    $word_count = count($words);
    
    $reading_speed = 200; // Average reading speed
    $minutes = ceil($word_count / $reading_speed);
    
    // Ensure minimum 1 minute
    $minutes = max(1, $minutes);
    
    return sprintf(_n('%d دقيقة', '%d دقائق', $minutes, 'mazaq'), $minutes);
}

/**
 * T007: Fetch related posts with Transient caching.
 */
function toc_get_related_posts($post_id, $count = 4) {
    $cats = get_the_category($post_id);
    if (empty($cats)) {
        return [];
    }

    $cat_ids = array_map(function($c) { return $c->term_id; }, $cats);
    $cache_key = 'toc_related_posts_' . $post_id . '_' . implode('_', $cat_ids);
    
    $related = get_transient($cache_key);
    if (false === $related) {
        $args = [
            'category__in'   => $cat_ids,
            'post__not_in'   => [$post_id],
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'no_found_rows'  => true, // Performance boost
            'ignore_sticky_posts' => true
        ];
        
        $query = new WP_Query($args);
        $related = $query->posts;
        
        // Cache for 12 hours
        set_transient($cache_key, $related, 12 * HOUR_IN_SECONDS);
    }
    
    return $related;
}
