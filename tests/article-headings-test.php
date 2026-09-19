<?php

/**
 * Standalone self-check for Article section identity. No framework, no WP:
 * run with `php tests/article-headings-test.php`. Stubs the hook, post, and
 * formatting functions inc/article-headings.php calls so the file can be
 * included in isolation.
 *
 * Seam under test: the extractor/injector agreement. The extractor reports
 * rows (id/level/text/number); the injector stamps the same ids into content.
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

require __DIR__ . '/../inc/article-headings.php';

// Slice 1: row schema. Prose article, no ids, no ranks → positional numbers,
// stable anchor ids, exactly the id/level/text/number shape consumers read.
$GLOBALS['__heading_fixtures'][11] = '<h2>Plot</h2><p>Body.</p><h3>Twist</h3><h2>Ending</h2>';
assert(mazaq_extract_article_headings(11) === [
    ['id' => 'article-section-1', 'level' => 2, 'text' => 'Plot', 'number' => 1],
    ['id' => 'article-section-2', 'level' => 3, 'text' => 'Twist', 'number' => 2],
    ['id' => 'article-section-3', 'level' => 2, 'text' => 'Ending', 'number' => 3],
]);

// Slice 2: agreement. The injector stamps exactly the ids the extractor
// reports, spliced after any attributes, before the closing bracket.
$GLOBALS['__heading_fixtures'][12] = '<h2>Plot</h2><p>Body.</p><h3 class="lede">Twist</h3>';
assert(mazaq_add_article_heading_ids($GLOBALS['__heading_fixtures'][12]) === '<h2 id="article-section-1">Plot</h2><p>Body.</p><h3 class="lede" id="article-section-2">Twist</h3>');
$rows12 = mazaq_extract_article_headings(12);
assert(array_column($rows12, 'id') === ['article-section-1', 'article-section-2']);

// Slice 3: empty headings consume ids but stay out of the rows, so the rail
// never prints an empty link and numbering skips nothing visible.
$GLOBALS['__heading_fixtures'][13] = '<h2>Plot</h2><h2></h2><h2>Ending</h2>';
assert(mazaq_extract_article_headings(13) === [
    ['id' => 'article-section-1', 'level' => 2, 'text' => 'Plot', 'number' => 1],
    ['id' => 'article-section-3', 'level' => 2, 'text' => 'Ending', 'number' => 2],
]);
assert(mazaq_add_article_heading_ids($GLOBALS['__heading_fixtures'][13]) === '<h2 id="article-section-1">Plot</h2><h2 id="article-section-2"></h2><h2 id="article-section-3">Ending</h2>');

// Slice 4: author ids pass through byte-preserved without consuming a number,
// so rail hrefs and rendered anchors agree even on hand-written markup.
$GLOBALS['__heading_fixtures'][14] = '<h2 id="custom">Plot</h2><h3 id=\'lead\'>Twist</h3><h2 id="my heading">Spaced</h2><h2>Ending</h2>';
assert(mazaq_extract_article_headings(14) === [
    ['id' => 'custom', 'level' => 2, 'text' => 'Plot', 'number' => 1],
    ['id' => 'lead', 'level' => 3, 'text' => 'Twist', 'number' => 2],
    ['id' => 'my heading', 'level' => 2, 'text' => 'Spaced', 'number' => 3],
    ['id' => 'article-section-1', 'level' => 2, 'text' => 'Ending', 'number' => 4],
]);
assert(mazaq_add_article_heading_ids($GLOBALS['__heading_fixtures'][14]) === '<h2 id="custom">Plot</h2><h3 id=\'lead\'>Twist</h3><h2 id="my heading">Spaced</h2><h2 id="article-section-1">Ending</h2>');

// Slice 5 (review finding): a present-but-empty author id is replaced with the
// assigned id, never doubled into a duplicate attribute.
$GLOBALS['__heading_fixtures'][15] = '<h2 id="">Blank</h2><h2>Next</h2>';
assert(mazaq_extract_article_headings(15) === [
    ['id' => 'article-section-1', 'level' => 2, 'text' => 'Blank', 'number' => 1],
    ['id' => 'article-section-2', 'level' => 2, 'text' => 'Next', 'number' => 2],
]);
assert(mazaq_add_article_heading_ids($GLOBALS['__heading_fixtures'][15]) === '<h2 id="article-section-1">Blank</h2><h2 id="article-section-2">Next</h2>');

echo "article-headings: all assertions passed\n";
