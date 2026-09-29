<?php

/**
 * Article pool: every published article editorial picks draw from — Content
 * rotation batches, the Daily suggestion, and the random film panel.
 */

declare(strict_types=1);

const MAZAQ_ARTICLE_POOL_TRANSIENT = 'mazaq_article_pool_ids';

/**
 * Scan published post ids in pages, optionally narrowed to one category.
 *
 * @return int[]
 */
function mazaq_article_pool_scan(int $category_id = 0): array
{
    $ids = [];
    $page = 1;
    $page_size = 200;
    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => $page_size,
        'orderby' => 'ID',
        'order' => 'ASC',
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ];

    if ($category_id > 0) {
        $args['cat'] = $category_id;
    }

    do {
        $query = new WP_Query($args + ['paged' => $page]);

        $page_ids = array_map('intval', $query->posts);
        if (!empty($page_ids)) {
            $ids = array_merge($ids, $page_ids);
        }

        $page++;
    } while (count($page_ids) === $page_size);

    return array_values(array_unique($ids));
}

/**
 * Every published article id, or only those in one category.
 *
 * @return int[]
 */
function mazaq_article_pool_ids(int $category_id = 0): array
{
    if ($category_id > 0) {
        // ponytail: category pools scan fresh; per-category transients plus
        // term-change clearing if the random film panel's list filter gets hot.
        $term = get_term($category_id, 'category');
        if (!$term || is_wp_error($term) || (int) ($term->count ?? 0) <= 0) {
            return [];
        }

        return mazaq_article_pool_scan($category_id);
    }

    $cached_ids = get_transient(MAZAQ_ARTICLE_POOL_TRANSIENT);
    if (is_array($cached_ids)) {
        return array_values(array_filter(array_map('intval', $cached_ids)));
    }

    $ids = mazaq_article_pool_scan();
    set_transient(MAZAQ_ARTICLE_POOL_TRANSIENT, $ids, 30 * MINUTE_IN_SECONDS);

    return $ids;
}

/**
 * Pick one random article id, skipping excluded ids unless that empties the
 * pool. Returns 0 when the pool is empty.
 */
function mazaq_article_pool_random_id(array $exclude_ids = [], int $category_id = 0): int
{
    $ids = mazaq_article_pool_ids($category_id);
    if (empty($ids)) {
        return 0;
    }

    $remaining = array_values(array_diff($ids, array_map('intval', $exclude_ids)));
    $choices = !empty($remaining) ? $remaining : $ids;

    return (int) $choices[wp_rand(0, count($choices) - 1)];
}

/**
 * Clear the full-pool cache whenever a post enters or leaves the pool.
 */
function mazaq_article_pool_clear_cache(): void
{
    delete_transient(MAZAQ_ARTICLE_POOL_TRANSIENT);
}

function mazaq_article_pool_clear_on_save(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || $post->post_type !== 'post') {
        return;
    }

    mazaq_article_pool_clear_cache();
}
add_action('save_post', 'mazaq_article_pool_clear_on_save', 10, 2);

function mazaq_article_pool_clear_on_delete(int $post_id): void
{
    if (get_post_type($post_id) !== 'post') {
        return;
    }

    mazaq_article_pool_clear_cache();
}
add_action('deleted_post', 'mazaq_article_pool_clear_on_delete');

function mazaq_article_pool_clear_on_transition(string $new_status, string $old_status, WP_Post $post): void
{
    if ($post->post_type !== 'post' || $new_status === $old_status) {
        return;
    }

    mazaq_article_pool_clear_cache();
}
add_action('transition_post_status', 'mazaq_article_pool_clear_on_transition', 10, 3);
