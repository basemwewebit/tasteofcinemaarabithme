<?php

/**
 * Standalone self-check for Article section identity. No framework, no WP:
 * run with `php tests/article-headings-test.php`. Stubs the hook, post, and
 * formatting functions inc/article-headings.php calls so the file can be
 * included in isolation.
 *
 * Seam under test: the extractor/injector agreement. The extractor reports
 * rows (id/level/text/number/label); the injector stamps the same ids into content.
 * These tests prove the two sides agree by construction on fixture HTML.
 */

declare(strict_types=1);

if (!function_exists('add_filter')) {
    function add_filter(...$args): void {}
}
if (!function_exists('get_post_field')) {
    function get_post_field(string $field, int $post_id) {
        return $GLOBALS['__heading_fixtures'][$post_id] ?? '';
    }
}
if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags(string $string, bool $remove_breaks = false): string {
        $string = (string) preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);
        if ($remove_breaks) {
            $string = (string) preg_replace('/[\r\n\t ]+/', ' ', $string);
        }
        return trim($string);
    }
}
if (!function_exists('is_admin')) {
    function is_admin(): bool { return false; }
}
if (!function_exists('is_singular')) {
    function is_singular($post_types = ''): bool { return true; }
}
if (!function_exists('in_the_loop')) {
    function in_the_loop(): bool { return true; }
}
if (!function_exists('is_main_query')) {
    function is_main_query(): bool { return true; }
}

require __DIR__ . '/rotation-test-helper.php';
require __DIR__ . '/../inc/article-headings.php';

// Slice 1: row schema. Prose article, no ids, no ranks → positional numbers,
// stable anchor ids, exactly the id/level/text/number/label shape consumers read.
$GLOBALS['__heading_fixtures'][11] = '<h2>Plot</h2><p>Body.</p><h3>Twist</h3><h2>Ending</h2>';
rotation_test_check(mazaq_extract_article_headings(11) === [
    ['id' => 'article-section-1', 'level' => 2, 'text' => 'Plot', 'number' => 1, 'label' => 'Plot'],
    ['id' => 'article-section-2', 'level' => 3, 'text' => 'Twist', 'number' => 2, 'label' => 'Twist'],
    ['id' => 'article-section-3', 'level' => 2, 'text' => 'Ending', 'number' => 3, 'label' => 'Ending'],
]);

// Slice 2: agreement. The injector stamps exactly the ids the extractor
// reports, spliced after any attributes, before the closing bracket.
$GLOBALS['__heading_fixtures'][12] = '<h2>Plot</h2><p>Body.</p><h3 class="lede">Twist</h3>';
rotation_test_check(mazaq_add_article_heading_ids($GLOBALS['__heading_fixtures'][12]) === '<h2 id="article-section-1">Plot</h2><p>Body.</p><h3 class="lede" id="article-section-2">Twist</h3>');
$rows12 = mazaq_extract_article_headings(12);
rotation_test_check(array_column($rows12, 'id') === ['article-section-1', 'article-section-2']);

// Slice 3: empty headings consume ids but stay out of the rows, so the rail
// never prints an empty link and numbering skips nothing visible.
$GLOBALS['__heading_fixtures'][13] = '<h2>Plot</h2><h2></h2><h2>Ending</h2>';
rotation_test_check(mazaq_extract_article_headings(13) === [
    ['id' => 'article-section-1', 'level' => 2, 'text' => 'Plot', 'number' => 1, 'label' => 'Plot'],
    ['id' => 'article-section-3', 'level' => 2, 'text' => 'Ending', 'number' => 2, 'label' => 'Ending'],
]);
rotation_test_check(mazaq_add_article_heading_ids($GLOBALS['__heading_fixtures'][13]) === '<h2 id="article-section-1">Plot</h2><h2 id="article-section-2"></h2><h2 id="article-section-3">Ending</h2>');

// Slice 4: author ids pass through byte-preserved without consuming a number,
// so rail hrefs and rendered anchors agree even on hand-written markup.
$GLOBALS['__heading_fixtures'][14] = '<h2 id="custom">Plot</h2><h3 id=\'lead\'>Twist</h3><h2 id="my heading">Spaced</h2><h2>Ending</h2>';
rotation_test_check(mazaq_extract_article_headings(14) === [
    ['id' => 'custom', 'level' => 2, 'text' => 'Plot', 'number' => 1, 'label' => 'Plot'],
    ['id' => 'lead', 'level' => 3, 'text' => 'Twist', 'number' => 2, 'label' => 'Twist'],
    ['id' => 'my heading', 'level' => 2, 'text' => 'Spaced', 'number' => 3, 'label' => 'Spaced'],
    ['id' => 'article-section-1', 'level' => 2, 'text' => 'Ending', 'number' => 4, 'label' => 'Ending'],
]);
rotation_test_check(mazaq_add_article_heading_ids($GLOBALS['__heading_fixtures'][14]) === '<h2 id="custom">Plot</h2><h3 id=\'lead\'>Twist</h3><h2 id="my heading">Spaced</h2><h2 id="article-section-1">Ending</h2>');

// Slice 5 (review finding): a present-but-empty author id is replaced with the
// assigned id, never doubled into a duplicate attribute.
$GLOBALS['__heading_fixtures'][15] = '<h2 id="">Blank</h2><h2>Next</h2>';
rotation_test_check(mazaq_extract_article_headings(15) === [
    ['id' => 'article-section-1', 'level' => 2, 'text' => 'Blank', 'number' => 1, 'label' => 'Blank'],
    ['id' => 'article-section-2', 'level' => 2, 'text' => 'Next', 'number' => 2, 'label' => 'Next'],
]);
rotation_test_check(mazaq_add_article_heading_ids($GLOBALS['__heading_fixtures'][15]) === '<h2 id="article-section-1">Blank</h2><h2 id="article-section-2">Next</h2>');

// --- numbering, through the extractor: h2 fixtures, one per heading text ---
function heading_fixture(array $texts): int
{
    static $post_id = 100;
    $post_id++;
    $GLOBALS['__heading_fixtures'][$post_id] = implode('', array_map(
        static fn(string $text): string => '<h2>' . $text . '</h2><p>Body.</p>',
        $texts
    ));
    return $post_id;
}

function heading_numbers(array $texts): array
{
    return array_column(mazaq_extract_article_headings(heading_fixture($texts)), 'number');
}

// No headings → nothing to number.
rotation_test_check(heading_numbers([]) === []);

// Prose article, no numbers anywhere → positional 1..N.
rotation_test_check(heading_numbers(['المقدمة', 'الحبكة', 'الخاتمة']) === [1, 2, 3]);

// A countdown listicle mirrors its own ranks rather than its document order.
rotation_test_check(heading_numbers(['31. Cars 3 (2017)', '30. The Good Dinosaur (2015)', '1. Toy Story (1995)']) === [31, 30, 1]);

// Ascending listicles work the same way.
rotation_test_check(heading_numbers(['1. Alien', '2. Aliens']) === [1, 2]);

// Separator may be a paren, a dash, or an en/em dash, with or without a space.
// Descending on purpose, so a positional fallback could not produce this.
rotation_test_check(heading_numbers(['9) Alien', '8 - Aliens', '7 – Alien 3', '6 — Resurrection']) === [9, 8, 7, 6]);

// A title that merely opens with digits is not a rank: no punctuation follows them,
// and a colon is never a rank marker. Nothing else numbered → position.
rotation_test_check(heading_numbers(['1917 (2019)', '2001: A Space Odyssey', 'مقدمة']) === [1, 2, 3]);

// An unnumbered heading among ranks (an intro) shows no number at all. Giving it its
// position would print "01" beside "1. Toy Story" -- the very contradiction this ends.
rotation_test_check(heading_numbers(['مقدمة', '3. Ratatouille', '2. Monsters, Inc.', '1. Toy Story']) === [null, 3, 2, 1]);

// Once anything is numbered, real ranks always win, half-and-half included.
rotation_test_check(heading_numbers(['مقدمة', 'خاتمة', '2. Aliens', '1. Alien']) === [null, null, 2, 1]);

// Ranks are capped at three digits, so a four-digit year is never read as one.
rotation_test_check(heading_numbers(['100. Alien', '101. Aliens', '1000. Movies']) === [100, 101, null]);

// Arabic-Indic digits are not parsed as ranks ((int) would cast them to 0, printing
// "00" on this Arabic site); they fall back to position instead.
rotation_test_check(heading_numbers(['١١. Up (2009)', '١٠. Inside Out']) === [1, 2]);

// Mixed scripts: only the ASCII rank counts.
rotation_test_check(heading_numbers(['١١. Up (2009)', '10. Inside Out']) === [null, 10]);

// --- label: the heading text minus its own rank marker ---
function heading_labels(array $texts): array
{
    return array_column(mazaq_extract_article_headings(heading_fixture($texts)), 'label');
}

// A ranked heading drops exactly the rank its number came from.
rotation_test_check(heading_labels(['31. Cars 3 (2017)', '9) Alien', '8 - Aliens', '7 – Alien 3', '6 — Resurrection'])
    === ['Cars 3 (2017)', 'Alien', 'Aliens', 'Alien 3', 'Resurrection']);

// A colon is never a rank: "300:" and "2001:" keep their full titles, even in a ranked list.
rotation_test_check(heading_labels(['301. Troy (2004)', '300: Rise of an Empire (2014)', '2001: A Space Odyssey (1968)'])
    === ['Troy (2004)', '300: Rise of an Empire (2014)', '2001: A Space Odyssey (1968)']);

// Unranked headings (prose, Arabic-Indic digits, a four-digit lead) keep their full text.
rotation_test_check(heading_labels(['مقدمة', '١١. Up (2009)', '1000. Movies'])
    === ['مقدمة', '١١. Up (2009)', '1000. Movies']);

echo "article-headings: all assertions passed\n";
