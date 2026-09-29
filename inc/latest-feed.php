<?php

/**
 * Latest feed: the front page's reverse-chronological run of articles below
 * the Continuous Programme, in pages of six. Page 1 renders with the front
 * page, later pages through the load-more endpoint, both via mazaq_latest_feed().
 */

declare(strict_types=1);

const MAZAQ_LATEST_FEED_PER_PAGE = 6;

// An ad plate precedes every Nth article of the feed (counted across pages),
// starting with the (N + 1)th. ponytail: absorbed by the Ad module when it exists.
const MAZAQ_LATEST_FEED_AD_INTERVAL = 8;

/**
 * One page of the Latest feed.
 *
 * @return array{html: string, has_more: bool}
 */
function mazaq_latest_feed(int $page = 1): array
{
    $page = max(1, $page);

    $query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => MAZAQ_LATEST_FEED_PER_PAGE,
        'paged' => $page,
        'post__not_in' => mazaq_programme_post_ids(),
        'ignore_sticky_posts' => true,
    ]);

    $index = 0;
    ob_start();
    while ($query->have_posts()) {
        $query->the_post();

        $position = ($page - 1) * MAZAQ_LATEST_FEED_PER_PAGE + $index + 1;
        if ($position > 1 && ($position - 1) % MAZAQ_LATEST_FEED_AD_INTERVAL === 0) {
            get_template_part('template-parts/ads/ad-grid');
        }

        get_template_part(
            'template-parts/content/article-card',
            null,
            $page === 1 ? mazaq_latest_feed_card_args($index) : ['layout' => 'standard']
        );
        $index++;
    }
    wp_reset_postdata();

    return ['html' => (string) ob_get_clean(), 'has_more' => $query->max_num_pages > $page];
}

/**
 * Card arguments for one position of page 1: wide lead first, finale last,
 * medium cards between.
 *
 * @return array<string, string>
 */
function mazaq_latest_feed_card_args(int $index): array
{
    $last = MAZAQ_LATEST_FEED_PER_PAGE - 1;

    if ($index === 0) {
        return [
            'layout' => 'wide',
            'class' => 'latest-feed__lead',
            'image_size' => 'card-medium-thumbnail',
            'image_sizes' => '(min-width: 1280px) 506px, (min-width: 981px) 40vw, 100vw',
            'small_image_media' => '',
        ];
    }

    if ($index === $last) {
        return [
            'layout' => 'standard',
            'class' => 'latest-feed__finale',
            'image_size' => '',
            'image_sizes' => '(min-width: 981px) 70vw, 100vw',
            'small_image_media' => '',
        ];
    }

    return [
        'layout' => 'standard',
        'class' => '',
        'image_size' => 'card-medium-thumbnail',
        'image_sizes' => '(min-width: 1280px) 400px, (min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw',
        'small_image_media' => '(min-width: 981px) and (max-width: 1440px)',
    ];
}
