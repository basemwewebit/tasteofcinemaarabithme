<?php

declare(strict_types=1);

if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
    require_once get_template_directory() . '/vendor/autoload.php';
}

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
    'inc/browser-notifications.php',
    'inc/random-film-popup.php',
    'inc/content-rotation-settings.php',
    'inc/admin-social-reminder.php',
    'inc/admin-hero-daily.php',
    'inc/admin-toc-missing-widget.php',
    'inc/toc-source-monitor.php',
    'inc/loader-ads-customizer.php',
    'inc/post-types/contact-message.php',
    'inc/recaptcha/class-recaptcha-admin.php',
    'inc/recaptcha/class-recaptcha-verify.php',
    'inc/recaptcha/class-recaptcha-hooks.php',
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
    echo '<nav class="mb-6 max-w-full text-sm font-medium text-slate-600 dark:text-slate-300" aria-label="breadcrumb"><ol class="flex flex-wrap items-center gap-x-2 gap-y-1">';
    echo '<li class="shrink-0"><a href="' . esc_url(home_url('/')) . '" class="transition-colors hover:text-primary">' . esc_html__('الرئيسية', 'mazaq') . '</a></li>';

    if (is_single()) {
        $cats = get_the_category();
        if (!empty($cats)) {
            echo '<li class="shrink-0" aria-hidden="true"><span class="text-slate-400">/</span></li>';
            echo '<li class="min-w-0 max-w-full"><a href="' . esc_url(get_category_link($cats[0]->term_id)) . '" class="break-words transition-colors hover:text-primary">' . esc_html($cats[0]->name) . '</a></li>';
        }
        echo '<li class="shrink-0" aria-hidden="true"><span class="text-slate-400">/</span></li>';
        echo '<li class="min-w-0 max-w-full break-words text-slate-800 dark:text-slate-300" aria-current="page">' . esc_html(get_the_title()) . '</li>';
    } elseif (is_category()) {
        echo '<li class="shrink-0" aria-hidden="true"><span class="text-slate-400">/</span></li><li class="min-w-0 max-w-full break-words text-slate-800 dark:text-slate-300" aria-current="page">' . single_cat_title('', false) . '</li>';
    }

    echo '</ol></nav>';
}

/**
 * T004: Calculate estimated reading time.
 */
function toc_estimated_reading_time($content = '') {
    if (empty($content)) {
        $post = get_post();
        $content = $post && isset($post->post_content) ? $post->post_content : '';
    }

    // Use preg_split with utf8 mode to correctly count Arabic words
    $words = preg_split('/\s+/u', trim(strip_tags((string) $content)), -1, PREG_SPLIT_NO_EMPTY);
    $word_count = is_array($words) ? count($words) : 0;

    $reading_speed = 200; // Average reading speed
    $minutes = (int) ceil($word_count / $reading_speed);

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

/**
 * Invalidate related posts transient cache when a post is saved or updated.
 */
add_action('save_post', function($post_id) {
    if (wp_is_post_revision($post_id)) {
        return;
    }
    $cats = get_the_category($post_id);
    if (!empty($cats)) {
        $cat_ids = array_map(function($c) { return $c->term_id; }, $cats);
        $cache_key = 'toc_related_posts_' . $post_id . '_' . implode('_', $cat_ids);
        delete_transient($cache_key);
    }
});

/**
 * Restrict application passwords to users who can edit posts (editors, admins, etc.)
 */
add_filter('wp_is_application_passwords_available', function ($available, $user = null) {
    if (!$user) {
        return (bool) $available;
    }

    // Only allow application passwords for users with edit_posts capability.
    return user_can($user, 'edit_posts');
}, 10, 2);


add_filter( 'wp_is_application_passwords_available', '__return_true' );
