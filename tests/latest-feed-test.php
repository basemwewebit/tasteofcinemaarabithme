<?php

/**
 * Standalone self-check for the Latest feed: sticky policy, Programme
 * exclusion, page size, per-position presentation, ad-plate cadence, has-more.
 * No framework, no WP: run with `php tests/latest-feed-test.php`.
 *
 * Fakes the adapters behind the seam: WP_Query (serves pages from a fake pool
 * and records the arguments it was built with), get_template_part (records
 * each card request with the article being rendered; ad template parts load
 * for real) and the theme options the real Ad placement module reads. The
 * Programme's ids are stubbed, as the Continuous Programme self-check does.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

// --- Fake Programme ----------------------------------------------------------
$GLOBALS['fake_programme'] = [];

function mazaq_programme_post_ids(): array
{
    return $GLOBALS['fake_programme'];
}

// --- Fake WP_Query: pages the fake pool, minus post__not_in -------------------
$GLOBALS['fake_pool'] = [];
$GLOBALS['fake_last_query_args'] = [];
$GLOBALS['fake_current_post'] = 0;

class WP_Query
{
    public $max_num_pages = 0;
    private $ids = [];
    private $cursor = 0;

    public function __construct(array $args = [])
    {
        $GLOBALS['fake_last_query_args'] = $args;

        $pool = array_values(array_diff($GLOBALS['fake_pool'], $args['post__not_in'] ?? []));
        $per_page = (int) ($args['posts_per_page'] ?? 10);
        $page = max(1, (int) ($args['paged'] ?? 1));

        $this->max_num_pages = (int) ceil(count($pool) / $per_page);
        $this->ids = array_slice($pool, ($page - 1) * $per_page, $per_page);
    }

    public function have_posts(): bool
    {
        return $this->cursor < count($this->ids);
    }

    public function the_post(): void
    {
        $GLOBALS['fake_current_post'] = $this->ids[$this->cursor++];
    }
}

function wp_reset_postdata(): void
{
    $GLOBALS['fake_current_post'] = 0;
}

// --- Fake template loader: records cards, echoes a marker; ads load for real --
$GLOBALS['fake_parts'] = [];

function get_template_part($slug, $name = null, $args = []): void
{
    if (str_starts_with($slug, 'template-parts/ads/')) {
        require __DIR__ . '/../' . $slug . '.php';
        return;
    }
    $GLOBALS['fake_parts'][] = ['slug' => $slug, 'args' => $args, 'post' => $GLOBALS['fake_current_post']];
    echo '[' . basename($slug) . ':' . $GLOBALS['fake_current_post'] . ']';
}

// --- Fake theme options + escaping for the real Ad placement module ----------
$GLOBALS['fake_options'] = [];

function get_field(string $name, $post_id = null)
{
    return $GLOBALS['fake_options'][$name] ?? '';
}
function add_action(...$args): void {}
function add_filter(...$args): void {}
function esc_url(string $url): string { return $url; }
function esc_url_raw(string $url, array $protocols = []): string { return $url; }
function esc_attr(string $text): string { return htmlspecialchars($text, ENT_QUOTES); }
function esc_attr__(string $text, string $domain = ''): string { return esc_attr($text); }
function esc_html__(string $text, string $domain = ''): string { return $text; }
function wp_get_attachment_image_url(int $id, string $size)
{
    return $id === 7 ? 'https://cdn.test/direct.gif' : false;
}

require __DIR__ . '/../inc/ads.php';
require __DIR__ . '/../inc/latest-feed.php';

/** Network ads configured for the In-feed placement. */
const FEED_TEST_NETWORK = ['adsense_publisher_id' => 'ca-pub-1', 'ad_slot_in_feed' => '111'];

/** Newest-first pool of ids 100..(100 - $size + 1). */
function feed_test_reset(int $size = 30): void
{
    $GLOBALS['fake_programme'] = [];
    $GLOBALS['fake_pool'] = $size > 0 ? range(100, 100 - $size + 1) : [];
    $GLOBALS['fake_last_query_args'] = [];
    $GLOBALS['fake_parts'] = [];
    $GLOBALS['fake_options'] = FEED_TEST_NETWORK;
}

/** Article cards only (ad plates filtered out), in render order. */
function feed_test_cards(): array
{
    return array_values(array_filter(
        $GLOBALS['fake_parts'],
        static fn (array $part): bool => str_ends_with($part['slug'], 'article-card')
    ));
}

// The query is chronological on every page (sticky posts ignored) and never
// re-serves the Continuous Programme.
feed_test_reset();
$GLOBALS['fake_programme'] = [100, 99, 98, 97];
mazaq_latest_feed(1);
rotation_test_check(($GLOBALS['fake_last_query_args']['ignore_sticky_posts'] ?? false) === true, 'page 1 ignores sticky posts');
rotation_test_check($GLOBALS['fake_last_query_args']['post__not_in'] === [100, 99, 98, 97], 'exactly the Programme ids are excluded');
mazaq_latest_feed(3);
rotation_test_check(($GLOBALS['fake_last_query_args']['ignore_sticky_posts'] ?? false) === true, 'page 3 ignores sticky posts');

// Six per page; the requested page reaches the query; below 1 means 1.
feed_test_reset();
mazaq_latest_feed(2);
rotation_test_check($GLOBALS['fake_last_query_args']['posts_per_page'] === 6, 'six articles per page');
rotation_test_check($GLOBALS['fake_last_query_args']['paged'] === 2, 'requested page reaches the query');
mazaq_latest_feed(0);
rotation_test_check($GLOBALS['fake_last_query_args']['paged'] === 1, 'page 0 is treated as page 1');
mazaq_latest_feed(-4);
rotation_test_check($GLOBALS['fake_last_query_args']['paged'] === 1, 'negative page is treated as page 1');

// Page 1 keeps the editorial rhythm: wide lead first, finale last, medium
// cards between. Cards render in newest-first order, Programme skipped.
feed_test_reset();
$GLOBALS['fake_programme'] = [100, 99, 98, 97];
$feed = mazaq_latest_feed(1);
$cards = feed_test_cards();
rotation_test_check(array_column($cards, 'post') === [96, 95, 94, 93, 92, 91], 'page 1 renders six articles newest first, Programme skipped');
rotation_test_check($feed['html'] === '[article-card:96][article-card:95][article-card:94][article-card:93][article-card:92][article-card:91]', 'html is the cards in order');

rotation_test_check($cards[0]['args'] === [
    'layout' => 'wide',
    'class' => 'latest-feed__lead',
    'image_size' => 'card-medium-thumbnail',
    'image_sizes' => '(min-width: 1280px) 506px, (min-width: 981px) 40vw, 100vw',
    'small_image_media' => '',
], 'position 0 is the wide lead card');

foreach ([1, 2, 3, 4] as $i) {
    rotation_test_check($cards[$i]['args'] === [
        'layout' => 'standard',
        'class' => '',
        'image_size' => 'card-medium-thumbnail',
        'image_sizes' => '(min-width: 1280px) 400px, (min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw',
        'small_image_media' => '(min-width: 981px) and (max-width: 1440px)',
    ], "position {$i} is a medium standard card");
}

rotation_test_check($cards[5]['args'] === [
    'layout' => 'standard',
    'class' => 'latest-feed__finale',
    'image_size' => '',
    'image_sizes' => '(min-width: 981px) 70vw, 100vw',
    'small_image_media' => '',
], 'position 5 is the finale card');

// Later pages are uniform: every card standard, no lead, no finale.
feed_test_reset(30);
mazaq_latest_feed(2);
$cards = feed_test_cards();
rotation_test_check(array_column($cards, 'post') === [94, 93, 92, 91, 90, 89], 'page 2 continues where page 1 stopped');
foreach ($cards as $i => $card) {
    rotation_test_check($card['args'] === ['layout' => 'standard'], "page 2 position {$i} is a plain standard card");
}

// An ad plate precedes global positions 9, 17, 25 … (greater than 1, and 1
// modulo 8), counted across pages; the first page never gets one.
/** Positions (0-based, within the page) that an ad plate precedes, read from the page's markup. */
function feed_test_ad_positions(string $html): array
{
    preg_match_all('/\[article-card:\d+\]|<aside\b/', $html, $tokens);
    $positions = [];
    $card = 0;
    foreach ($tokens[0] as $token) {
        if ($token === '<aside') {
            $positions[] = $card;
        } else {
            $card++;
        }
    }

    return $positions;
}

feed_test_reset(60);
foreach ([1 => [], 2 => [2], 3 => [4], 4 => [], 5 => [0], 6 => [2]] as $page => $expected) {
    rotation_test_check(feed_test_ad_positions(mazaq_latest_feed($page)['html']) === $expected, "page {$page} ad plates at " . json_encode($expected));
}

// The plates are the In-feed placement, not a borrowed banner.
feed_test_reset(60);
rotation_test_check(str_contains(mazaq_latest_feed(2)['html'], 'data-slot-name="ad_slot_in_feed"'), 'plates are the In-feed placement');
$GLOBALS['fake_options'] = ['adsense_publisher_id' => 'ca-pub-1', 'ad_slot_hero_banner' => '111'];
rotation_test_check(feed_test_ad_positions(mazaq_latest_feed(2)['html']) === [], 'an unconfigured In-feed placement renders no plates');

// A Direct ad shows once per page view: only the feed's first plate (before
// item 9, on page 2) carries it. Each load-more page is its own request, so
// page 3 is rendered first here: its plate must not claim the Direct ad.
feed_test_reset(60);
$GLOBALS['fake_options'] += ['ad_slot_in_feed_direct_url' => 'https://sponsor.test', 'ad_slot_in_feed_direct_image' => 7];
$page3 = mazaq_latest_feed(3)['html'];
rotation_test_check(!str_contains($page3, 'sponsor.test') && str_contains($page3, 'adsbygoogle'), 'a later plate on a load-more page carries the Network ad');
$page2 = mazaq_latest_feed(2)['html'];
rotation_test_check(str_contains($page2, 'https://sponsor.test') && !str_contains($page2, 'adsbygoogle'), 'the first plate of the feed carries the Direct ad');
$page5 = mazaq_latest_feed(5)['html'];
rotation_test_check(!str_contains($page5, 'sponsor.test') && str_contains($page5, 'adsbygoogle'), 'plates after the first never repeat the Direct ad');

// Has-more is reported on every page, page 1 included, and the Programme's
// articles do not count towards it.
feed_test_reset(30);
rotation_test_check(mazaq_latest_feed(1)['has_more'] === true, 'page 1 of a long feed has more');
rotation_test_check(mazaq_latest_feed(5)['has_more'] === false, 'the last page has no more');

feed_test_reset(6);
rotation_test_check(mazaq_latest_feed(1)['has_more'] === false, 'a one-page feed has no more on page 1');

feed_test_reset(10);
$GLOBALS['fake_programme'] = [100, 99, 98, 97];
rotation_test_check(mazaq_latest_feed(1)['has_more'] === false, 'ten articles minus a four-article Programme fit one page');

feed_test_reset(7);
rotation_test_check(mazaq_latest_feed(1)['has_more'] === true, 'seven articles spill onto a second page');
rotation_test_check(mazaq_latest_feed(2)['has_more'] === false, 'the spill page is the last');

// An empty feed yields empty markup and no more, so the front page can show
// its empty state.
feed_test_reset(0);
$feed = mazaq_latest_feed(1);
rotation_test_check($feed === ['html' => '', 'has_more' => false], 'empty feed: no markup, nothing more');

feed_test_reset(4);
$GLOBALS['fake_programme'] = [100, 99, 98, 97];
rotation_test_check(mazaq_latest_feed(1) === ['html' => '', 'has_more' => false], 'a pool wholly inside the Programme leaves an empty feed');

echo "latest-feed: {$GLOBALS['rotation_test_passed']} seam checks passed\n";
