<?php

/**
 * Standalone self-check for the Archive feed: page-1 lead, position counted
 * across pages, In-feed plates, grouping bar, empty state.
 * No framework, no WP: run with `php tests/archive-feed-test.php`.
 *
 * Fakes the adapters behind the seam: the main loop (serves one page of a
 * fake pool), get_template_part (records each card, row and pagination
 * request with the article being rendered) and the theme options the real Ad
 * placement module reads.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

// --- Fake main loop: one page of the fake pool --------------------------------
$GLOBALS['fake_page_ids'] = [];
$GLOBALS['fake_cursor'] = 0;
$GLOBALS['fake_current_post'] = 0;
$GLOBALS['fake_query_vars'] = [];

function have_posts(): bool
{
    return $GLOBALS['fake_cursor'] < count($GLOBALS['fake_page_ids']);
}

function the_post(): void
{
    $GLOBALS['fake_current_post'] = $GLOBALS['fake_page_ids'][$GLOBALS['fake_cursor']++];
}

function rewind_posts(): void
{
    $GLOBALS['fake_cursor'] = 0;
}

function get_query_var(string $name, $default = '')
{
    return $GLOBALS['fake_query_vars'][$name] ?? $default;
}

// --- Fake template loader: records parts, echoes a marker ---------------------
$GLOBALS['fake_parts'] = [];

function get_template_part($slug, $name = null, $args = []): void
{
    $GLOBALS['fake_parts'][] = ['slug' => $slug, 'args' => $args, 'post' => $GLOBALS['fake_current_post']];
    echo '[' . basename($slug) . ':' . $GLOBALS['fake_current_post'] . ']';
}

// --- Fake i18n, escaping and theme options ------------------------------------
$GLOBALS['fake_options'] = [];

function get_field(string $name, $post_id = null)
{
    return $GLOBALS['fake_options'][$name] ?? '';
}
function add_action(...$args): void {}
function wp_parse_args($args, array $defaults = []): array { return array_merge($defaults, (array) $args); }
function __(string $text, string $domain = ''): string { return $text; }
function esc_html(string $text): string { return htmlspecialchars($text, ENT_QUOTES); }
function esc_html__(string $text, string $domain = ''): string { return esc_html($text); }
function esc_html_e(string $text, string $domain = ''): void { echo esc_html($text); }
function esc_attr(string $text): string { return htmlspecialchars($text, ENT_QUOTES); }
function esc_attr__(string $text, string $domain = ''): string { return esc_attr($text); }
function esc_attr_e(string $text, string $domain = ''): void { echo esc_attr($text); }
function esc_url(string $url): string { return $url; }
function esc_url_raw(string $url, array $protocols = []): string { return $url; }
function home_url(string $path = ''): string { return 'https://site.test' . $path; }
function wp_get_attachment_image_url(int $id, string $size)
{
    return $id === 7 ? 'https://cdn.test/direct.gif' : false;
}

require __DIR__ . '/../inc/ads.php';

/** Network ads configured for the In-feed placement. */
const ARCHIVE_TEST_NETWORK = ['adsense_publisher_id' => 'ca-pub-1', 'ad_slot_in_feed' => '111'];

/**
 * Render page $page of a newest-first pool of $size ids (100, 99, …), $per_page
 * a page, through the real feed template part.
 */
function archive_feed(int $page = 1, array $args = [], int $size = 50, int $per_page = 10): string
{
    $pool = $size > 0 ? range(100, 100 - $size + 1) : [];
    $GLOBALS['fake_page_ids'] = array_slice($pool, ($page - 1) * $per_page, $per_page);
    $GLOBALS['fake_cursor'] = 0;
    $GLOBALS['fake_current_post'] = 0;
    // WordPress leaves "paged" at 0 on the first page.
    $GLOBALS['fake_query_vars'] = ['paged' => $page > 1 ? $page : 0, 'posts_per_page' => $per_page];
    $GLOBALS['fake_parts'] = [];

    ob_start();
    require __DIR__ . '/../template-parts/archive/archive-feed.php';

    return (string) ob_get_clean();
}

function archive_reset(): void
{
    $GLOBALS['fake_options'] = ARCHIVE_TEST_NETWORK;
}

/** Recorded parts whose slug ends with $name, in render order. */
function archive_parts(string $name): array
{
    return array_values(array_filter(
        $GLOBALS['fake_parts'],
        static fn (array $part): bool => str_ends_with($part['slug'], $name)
    ));
}

/** Rows (0-based, within the page) that an ad plate precedes, read from the markup. */
function archive_ad_positions(string $html): array
{
    preg_match_all('/\[archive-row:\d+\]|<aside\b/', $html, $tokens);
    $positions = [];
    $row = 0;
    foreach ($tokens[0] as $token) {
        if ($token === '<aside') {
            $positions[] = $row;
        } else {
            $row++;
        }
    }

    return $positions;
}

/** The data-group values of the grouping bar's buttons, in order. */
function archive_groupings(string $html): array
{
    preg_match_all('/data-group="([a-z]+)"/', $html, $groups);

    return $groups[1];
}

// --- Checks ------------------------------------------------------------------

// Page 1: the first article leads as a wide Editorial card and is not
// repeated as a row.
archive_reset();
$html = archive_feed(1);
$lead = archive_parts('article-card');
rotation_test_check(count($lead) === 1 && $lead[0]['post'] === 100, 'page 1 leads with the newest article');
rotation_test_check($lead[0]['args']['layout'] === 'wide', 'the lead is a wide card');
rotation_test_check(array_column(archive_parts('archive-row'), 'post') === range(99, 91), 'page 1 rows are the other nine, the lead not repeated');
rotation_test_check(str_contains($html, 'aria-label="المقال المميز في هذا الأرشيف"'), 'the lead carries the default label');

// Later pages: no lead, every article a row.
archive_reset();
archive_feed(2);
rotation_test_check(archive_parts('article-card') === [], 'page 2 has no lead');
rotation_test_check(array_column(archive_parts('archive-row'), 'post') === range(90, 81), 'page 2 rows continue where page 1 stopped');

// Plates fall before items 9, 17, 25 … counted across pages (10 a page):
// item 9 is page 1's 8th row (the lead is item 1), item 17 page 2's 7th row,
// item 25 page 3's 5th, item 33 page 4's 3rd, items 41 and 49 page 5's 1st and 9th.
archive_reset();
foreach ([1 => [7], 2 => [6], 3 => [4], 4 => [2], 5 => [0, 8]] as $page => $expected) {
    rotation_test_check(archive_ad_positions(archive_feed($page)) === $expected, "page {$page} plates at rows " . json_encode($expected));
}
rotation_test_check(str_contains(archive_feed(2), 'data-slot-name="ad_slot_in_feed"'), 'plates are the In-feed placement');
$GLOBALS['fake_options'] = ['adsense_publisher_id' => 'ca-pub-1', 'ad_slot_hero_banner' => '111'];
rotation_test_check(archive_ad_positions(archive_feed(1)) === [], 'an unconfigured In-feed placement renders no plates');

// Only the feed's first plate (before item 9) carries the Direct ad. Page 2
// is rendered first: its plate must not claim it.
archive_reset();
$GLOBALS['fake_options'] += ['ad_slot_in_feed_direct_url' => 'https://sponsor.test', 'ad_slot_in_feed_direct_image' => 7];
$page2 = archive_feed(2);
rotation_test_check(!str_contains($page2, 'sponsor.test') && str_contains($page2, 'adsbygoogle'), 'a later plate carries the Network ad');
$page1 = archive_feed(1);
rotation_test_check(str_contains($page1, 'https://sponsor.test') && !str_contains($page1, 'adsbygoogle'), 'the plate before item 9 carries the Direct ad');

// The grouping bar: month, category and "all" by default.
archive_reset();
rotation_test_check(archive_groupings(archive_feed(1)) === ['month', 'category', 'all'], 'default bar groups by month, category, all');
rotation_test_check(archive_groupings(archive_feed(1, ['groupings' => ['month']])) === ['month', 'all'], 'month-only bar has no category button');
$html = archive_feed(1, ['groupings' => []]);
rotation_test_check(archive_groupings($html) === [] && !str_contains($html, 'archive-groupbar'), 'no groupings renders no bar');
rotation_test_check(count(archive_parts('archive-row')) === 9, 'rows still render without a bar');

// The lead's label is the caller's to name.
archive_reset();
rotation_test_check(str_contains(archive_feed(1, ['lead_label' => 'أقرب نتيجة لبحثك']), 'aria-label="أقرب نتيجة لبحثك"'), 'the lead takes a caller label');

// A lone result is only the lead: no empty rows container.
archive_reset();
$html = archive_feed(1, [], 1);
rotation_test_check(count(archive_parts('article-card')) === 1 && !str_contains($html, 'archive-index'), 'a one-article page renders the lead and no index');

// Empty: the message, the link when asked for, no bar, no rows.
archive_reset();
$html = archive_feed(1, ['empty_message' => 'لا شيء هنا.'], 0);
rotation_test_check(str_contains($html, 'لا شيء هنا.') && str_contains($html, 'href="https://site.test/"'), 'empty feed shows its message and the latest-articles link');
rotation_test_check(!str_contains($html, 'archive-groupbar') && archive_parts('archive-row') === [], 'empty feed has no bar and no rows');
$html = archive_feed(1, ['empty_link' => false], 0);
rotation_test_check(!str_contains($html, 'href="https://site.test/"'), 'empty link can be turned off');

echo "archive-feed: {$GLOBALS['rotation_test_passed']} seam checks passed\n";
