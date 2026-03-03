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
    ]);

    ob_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/content/card');
        }
    }
    wp_reset_postdata();

    wp_send_json_success([
        'html' => ob_get_clean(),
        'has_more' => $query->max_num_pages > $page,
    ]);
}

add_action('wp_ajax_nopriv_load_more_posts', 'mazaq_ajax_load_more_posts');
add_action('wp_ajax_load_more_posts', 'mazaq_ajax_load_more_posts');
