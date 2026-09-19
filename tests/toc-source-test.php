<?php

/**
 * Standalone self-check for the TasteOfCinema source module: the one feed
 * fetcher + normalizer behind the monitor and missing-widget seam. No
 * framework, no WP: run with `php tests/toc-source-test.php`.
 *
 * Uses an explicit check() helper instead of assert(): this environment ships
 * with zend.assertions=-1, under which assert() compiles to a no-op.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

if (!function_exists('esc_url_raw')) {
    function esc_url_raw(string $url): string { return trim($url); }
}
if (!function_exists('sanitize_title')) {
    function sanitize_title(string $title): string
    {
        $title = strtolower(trim($title));
        $title = preg_replace('/[^a-z0-9\-_]+/', '-', $title);
        return trim((string) $title, '-');
    }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $text): string { return trim(strip_tags($text)); }
}

require __DIR__ . '/../inc/toc-source.php';

// --- slug extraction: last path segment, decoded, sanitized ---
rotation_test_check(
    mazaq_toc_source_extract_slug_from_url('https://www.tasteofcinema.com/2024/02/12/best-movies-of-2023/') === 'best-movies-of-2023',
    'slug is last path segment'
);
rotation_test_check(
    mazaq_toc_source_extract_slug_from_url('https://www.tasteofcinema.com/best-movies/') === 'best-movies',
    'slug works without date segments'
);
rotation_test_check(
    mazaq_toc_source_extract_slug_from_url('https://www.tasteofcinema.com/') === '',
    'bare domain has no slug'
);
rotation_test_check(
    mazaq_toc_source_extract_slug_from_url('not-a-url') === 'not-a-url',
    'path-only input still yields a slug'
);
rotation_test_check(
    mazaq_toc_source_extract_slug_from_url('') === '',
    'empty url yields empty slug'
);

// --- normalize_item: shape, defaults, rejection ---
rotation_test_check(
    mazaq_toc_source_normalize_item([
        'title' => 'Best Movies',
        'url' => 'https://www.tasteofcinema.com/best-movies/',
        'slug' => 'best-movies',
        'date' => '2024-01-01 10:00:00',
    ]) === [
        'title' => 'Best Movies',
        'url' => 'https://www.tasteofcinema.com/best-movies/',
        'slug' => 'best-movies',
        'date' => '2024-01-01 10:00:00',
    ],
    'well-formed row survives with url/slug/title/date shape'
);
rotation_test_check(
    mazaq_toc_source_normalize_item([
        'url' => 'https://www.tasteofcinema.com/best-movies/',
        'slug' => 'best-movies',
    ])['title'] === 'https://www.tasteofcinema.com/best-movies/',
    'missing title falls back to url'
);
rotation_test_check(
    mazaq_toc_source_normalize_item(['slug' => 'best-movies']) === [],
    'missing url rejects the row'
);
rotation_test_check(
    mazaq_toc_source_normalize_item(['url' => 'https://www.tasteofcinema.com/best-movies/']) === [],
    'missing slug rejects the row'
);
rotation_test_check(
    mazaq_toc_source_normalize_item('junk') === [],
    'non-array input rejects'
);

// --- normalize_items: skips bad rows, dedupes by slug, keeps first ---
$normalized = mazaq_toc_source_normalize_items([
    ['title' => 'A', 'url' => 'https://a.example/', 'slug' => 'a', 'date' => ''],
    ['title' => 'B', 'url' => 'https://b.example/', 'slug' => 'b', 'date' => ''],
    ['title' => 'A2', 'url' => 'https://a2.example/', 'slug' => 'a', 'date' => ''],
    ['title' => 'No slug', 'url' => 'https://c.example/', 'date' => ''],
]);
rotation_test_check(count($normalized) === 2, 'bad rows dropped, dup slug collapsed');
rotation_test_check($normalized[0]['title'] === 'A', 'first occurrence wins the dedupe');
rotation_test_check($normalized[1]['slug'] === 'b', 'second distinct slug survives');

// --- prepare_feed_item: SimplePie item becomes a unified row ---
class Toc_Source_Test_Item
{
    private $permalink;
    private $title;
    private $date;

    public function __construct(string $permalink, string $title, string $date)
    {
        $this->permalink = $permalink;
        $this->title = $title;
        $this->date = $date;
    }

    public function get_permalink() { return $this->permalink; }
    public function get_title() { return $this->title; }
    public function get_date(string $format = 'Y-m-d H:i:s') { return $this->date; }
}

rotation_test_check(
    mazaq_toc_source_prepare_feed_item(new Toc_Source_Test_Item(
        'https://www.tasteofcinema.com/best-movies/',
        'Best Movies',
        '2024-01-01 10:00:00'
    )) === [
        'title' => 'Best Movies',
        'url' => 'https://www.tasteofcinema.com/best-movies/',
        'slug' => 'best-movies',
        'date' => '2024-01-01 10:00:00',
    ],
    'feed item prepares to unified row with derived slug'
);
rotation_test_check(
    mazaq_toc_source_prepare_feed_item(new stdClass()) === [],
    'object without get_permalink rejects'
);
rotation_test_check(
    mazaq_toc_source_prepare_feed_item(new Toc_Source_Test_Item('', 'T', 'D')) === [],
    'empty permalink rejects'
);
rotation_test_check(
    mazaq_toc_source_prepare_feed_item(new Toc_Source_Test_Item('https://www.tasteofcinema.com/', 'T', 'D')) === [],
    'root url without slug rejects'
);

// --- fetch_feed_items: limit, cache, force, error fallback ---
$GLOBALS['toc_source_test_transients'] = [];
$GLOBALS['toc_source_test_fetch_calls'] = 0;
$GLOBALS['toc_source_test_feed_error'] = false;
$GLOBALS['toc_source_test_feed_item_count'] = 0;

class Toc_Source_Test_Feed
{
    public function get_items(int $start, int $limit): array
    {
        $items = [];
        $count = (int) $GLOBALS['toc_source_test_feed_item_count'];
        for ($i = 0; $i < min($limit, $count); $i++) {
            $items[] = new Toc_Source_Test_Item(
                "https://www.tasteofcinema.com/movie-{$i}/",
                "Movie {$i}",
                '2024-01-01 10:00:00'
            );
        }
        return $items;
    }
}

if (!function_exists('fetch_feed')) {
    function fetch_feed(string $url)
    {
        $GLOBALS['toc_source_test_fetch_calls']++;
        if (!empty($GLOBALS['toc_source_test_feed_error'])) {
            return new WP_Error_Stub();
        }
        return new Toc_Source_Test_Feed();
    }
}
if (!class_exists('WP_Error_Stub')) {
    class WP_Error_Stub {}
}
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing): bool { return $thing instanceof WP_Error_Stub; }
}
if (!function_exists('get_transient')) {
    function get_transient(string $key) { return $GLOBALS['toc_source_test_transients'][$key] ?? false; }
}
if (!function_exists('set_transient')) {
    function set_transient(string $key, $value, int $ttl = 0): bool
    {
        $GLOBALS['toc_source_test_transients'][$key] = $value;
        return true;
    }
}
if (!function_exists('add_filter')) {
    function add_filter(...$args): void {}
}
if (!function_exists('remove_filter')) {
    function remove_filter(...$args): void {}
}

function toc_source_test_reset_fetch_state(): void
{
    $GLOBALS['toc_source_test_transients'] = [];
    $GLOBALS['toc_source_test_fetch_calls'] = 0;
    $GLOBALS['toc_source_test_feed_error'] = false;
}

toc_source_test_reset_fetch_state();
$GLOBALS['toc_source_test_feed_item_count'] = 120;
$fetched = mazaq_toc_source_fetch_feed_items(20);
rotation_test_check(count($fetched) === 20, 'fetch respects the limit');
rotation_test_check($fetched[0]['slug'] === 'movie-0', 'fetched rows carry derived slugs');
rotation_test_check($GLOBALS['toc_source_test_fetch_calls'] === 1, 'cold fetch hits the remote once');

$cached = mazaq_toc_source_fetch_feed_items(20);
rotation_test_check($cached === $fetched, 'warm cache returns identical rows');
rotation_test_check($GLOBALS['toc_source_test_fetch_calls'] === 1, 'warm cache makes no remote call');

$forced = mazaq_toc_source_fetch_feed_items(20, true);
rotation_test_check($GLOBALS['toc_source_test_fetch_calls'] === 2, 'force refresh bypasses the cache');
rotation_test_check($forced === $fetched, 'forced rows match cold rows');

$GLOBALS['toc_source_test_feed_error'] = true;
$stale = mazaq_toc_source_fetch_feed_items(20, true);
rotation_test_check($stale === $fetched, 'remote error falls back to stale cache');

toc_source_test_reset_fetch_state();
$GLOBALS['toc_source_test_feed_error'] = true;
rotation_test_check(mazaq_toc_source_fetch_feed_items(20, true) === [], 'remote error with no cache yields empty');

// --- cache is keyed by limit: consumers never poison each other ---
toc_source_test_reset_fetch_state();
$GLOBALS['toc_source_test_feed_error'] = false;
$GLOBALS['toc_source_test_feed_item_count'] = 120;
$twenty = mazaq_toc_source_fetch_feed_items(20);
rotation_test_check(count($twenty) === 20, 'first consumer caches 20 rows');
$one_twenty = mazaq_toc_source_fetch_feed_items(120);
rotation_test_check(count($one_twenty) === 120, 'second limit misses the first consumer cache');
rotation_test_check($GLOBALS['toc_source_test_fetch_calls'] === 2, 'each limit fetches once');
$twenty_again = mazaq_toc_source_fetch_feed_items(20);
rotation_test_check($twenty_again === $twenty, 'first limit still served from its own cache');
rotation_test_check($GLOBALS['toc_source_test_fetch_calls'] === 2, 'repeat limit makes no remote call');

echo 'toc-source: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " checks passed\n";
