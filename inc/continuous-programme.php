<?php

declare(strict_types=1);

const MAZAQ_PROGRAMME_SIZE = 4;

/**
 * The Continuous Programme: the homepage's four featured articles, feature
 * first. The editor's pin (ACF `hero_featured_post`) leads, today's Content
 * rotation batch follows, and the latest articles fill any gap.
 *
 * Every surface that shows or excludes the Programme reads this one list, so
 * no Programme article repeats in the Latest feed, its load-more pages, the
 * collection, or Most-read.
 *
 * Accepted edges: a pin outside today's batch displaces the batch's last
 * article, which still counts as used by Content rotation; load-more resolves
 * the list again, so a rotation rollover or a new article between page loads
 * can shift one page of the Latest feed.
 *
 * @return int[] Up to four published post ids, feature first, no duplicates.
 */
function mazaq_programme_post_ids(): array
{
    $is_published = static fn (int $id): bool => $id > 0
        && get_post_type($id) === 'post'
        && get_post_status($id) === 'publish';

    $pin = function_exists('get_field') ? (int) get_field('hero_featured_post', 'option') : 0;
    $batch = mazaq_rotation_get_today_batch_ids(mazaq_hero_daily_config());

    $ids = array_values(array_unique(array_filter(array_map('intval', array_merge([$pin], $batch)), $is_published)));
    $ids = array_slice($ids, 0, MAZAQ_PROGRAMME_SIZE);

    if (count($ids) < MAZAQ_PROGRAMME_SIZE) {
        $ids = array_merge($ids, array_map('intval', get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => MAZAQ_PROGRAMME_SIZE - count($ids),
            'fields' => 'ids',
            'post__not_in' => $ids ?: [0],
            'ignore_sticky_posts' => true,
        ])));
    }

    return $ids;
}
