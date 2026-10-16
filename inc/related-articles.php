<?php

declare(strict_types=1);

/**
 * Related articles: up to 5 published articles sharing a category with the
 * post, newest first, split between the Inline related block and the closing
 * grid.
 *
 * @package Mazaq
 */

/**
 * The post's Related articles: `inline` for the Inline related block, `bottom`
 * for the closing grid.
 *
 * The first 2 go inline when there are at least 2 and the post isn't
 * password-protected (its body is then the password form, with nowhere honest
 * for the block to land); otherwise up to 3 go to the closing grid alone.
 *
 * @return array{inline: WP_Post[], bottom: WP_Post[]}
 */
function mazaq_related_articles(int $post_id): array
{
    $related = mazaq_related_articles_posts($post_id);

    if (count($related) >= 2 && !post_password_required($post_id)) {
        return ['inline' => array_slice($related, 0, 2), 'bottom' => array_slice($related, 2, 3)];
    }

    return ['inline' => [], 'bottom' => array_slice($related, 0, 3)];
}

/**
 * The related posts themselves, from a 12-hour cache of their ids. Hydrating
 * published-only drops an article unpublished since it was cached.
 *
 * ponytail: a newly published sibling waits for the entry to expire (≤ 12 h);
 * bump a global cache version on publish if editors need it sooner.
 *
 * @return WP_Post[]
 */
function mazaq_related_articles_posts(int $post_id): array
{
    $key = 'mazaq_related_' . $post_id;
    $ids = get_transient($key);

    if (!is_array($ids)) {
        $cat_ids = array_map(static fn($c) => $c->term_id, get_the_category($post_id));
        $ids = [];
        if (!empty($cat_ids)) {
            $query = new WP_Query([
                'category__in' => $cat_ids,
                'post__not_in' => [$post_id],
                'posts_per_page' => 5,
                'post_status' => 'publish',
                'fields' => 'ids',
                'no_found_rows' => true,
                'ignore_sticky_posts' => true,
            ]);
            $ids = array_map('intval', $query->posts);
        }
        set_transient($key, $ids, 12 * HOUR_IN_SECONDS);
    }

    if (empty($ids)) {
        return [];
    }

    return get_posts([
        'post__in' => $ids,
        'orderby' => 'post__in',
        'post_status' => 'publish',
        'posts_per_page' => count($ids),
    ]);
}

add_action('save_post', static function ($post_id): void {
    if (!wp_is_post_revision($post_id)) {
        delete_transient('mazaq_related_' . $post_id);
    }
});
