<?php

declare(strict_types=1);

function mazaq_ajax_load_more_posts(): void
{
    check_ajax_referer('mazaq_load_more_nonce', 'nonce');

    $page = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;
    $query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'paged' => $page,
        'posts_per_page' => 6,
        'post__not_in' => mazaq_get_hero_post_ids(),
    ]);

    // Force site locale for AJAX responses so that formatted dates are not in English
    $site_locale = get_option('WPLANG') ?: 'ar';
    switch_to_locale($site_locale);

    ob_start();
    if ($query->have_posts()) {
        $index = 1;
        while ($query->have_posts()) {
            $query->the_post();
            
            $global_index = (($page - 1) * 6) + $index;
            
            if ($global_index > 1 && ($global_index - 1) % 8 === 0) {
                get_template_part('template-parts/ads/ad-grid');
            }

            if ($index % 3 === 0) {
                get_template_part('template-parts/content/card-wide');
            } else {
                get_template_part('template-parts/content/card');
            }
            $index++;
        }
    }
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    restore_current_locale();

    wp_send_json_success([
        'html' => $html,
        'has_more' => $query->max_num_pages > $page,
    ]);
}

add_action('wp_ajax_nopriv_load_more_posts', 'mazaq_ajax_load_more_posts');
add_action('wp_ajax_load_more_posts', 'mazaq_ajax_load_more_posts');
