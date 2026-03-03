<?php

declare(strict_types=1);

function mazaq_track_post_views_head(): void
{
    if (!is_single() || !is_singular('post')) {
        return;
    }
    if (current_user_can('manage_options')) {
        return;
    }

    $post_id = get_the_ID();
    if ($post_id) {
        mazaq_track_post_views((int) $post_id);
    }
}
add_action('wp_head', 'mazaq_track_post_views_head');

function mazaq_track_post_views(int $post_id): void
{
    $key = '_post_views_count';
    $count = (int) get_post_meta($post_id, $key, true);
    update_post_meta($post_id, $key, (string) ($count + 1));
}

function mazaq_get_post_views(int $post_id): int
{
    return (int) get_post_meta($post_id, '_post_views_count', true);
}

function mazaq_get_most_read_posts(int $count = 3): WP_Query
{
    return new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => $count,
        'meta_key' => '_post_views_count',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'no_found_rows' => true,
        'date_query' => [
            [
                'after' => '1 week ago',
            ],
        ],
        'meta_query' => [
            [
                'key'     => '_post_views_count',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ],
        ],
    ]);
}
