<?php

/**
 * Standalone self-check for Article body composition. No framework, no WP:
 * run with `php tests/article-body-test.php`.
 *
 * Seam under test: mazaq_compose_article_body(), string in, string out. Fixture
 * bodies go in; the tests read where the Inline related block and the in-article
 * ad plates landed.
 */

declare(strict_types=1);

require_once __DIR__ . '/rotation-test-helper.php';

if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags(string $string): string
    {
        return trim(strip_tags((string) preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string)));
    }
}

require_once __DIR__ . '/../inc/article-body.php';

$no_ad = static fn(): string => '';

const BODY_TEST_RELATED = '<aside class="inline-related"><h3 id="inline-related-title">r</h3></aside>';

/** n paragraphs "<p>pN</p>", joined by newlines like wpautop output. */
function body_test_paragraphs(int $n, int $from = 1): string
{
    return implode("\n", array_map(static fn(int $i): string => "<p>p{$i}</p>", range($from, $from + $n - 1)));
}

/** The text of the paragraph right before the related block, or 'END' when appended last. */
function body_test_related_after(string $html): string
{
    $at = strpos($html, BODY_TEST_RELATED);
    rotation_test_check($at !== false, 'related block is placed');
    if (trim(substr($html, $at + strlen(BODY_TEST_RELATED))) === '') {
        return 'END';
    }
    preg_match_all('/<p>(p\d+)<\/p>/', substr($html, 0, $at), $m);
    return (string) end($m[1]);
}

// The related block takes the safe point nearest 60%: 10 paragraphs -> after p6.
$out = mazaq_compose_article_body(body_test_paragraphs(10), BODY_TEST_RELATED, $no_ad);
rotation_test_check(body_test_related_after($out) === 'p6', '10 paragraphs: related after p6');
rotation_test_check(substr_count($out, BODY_TEST_RELATED) === 1, 'related placed once');
rotation_test_check(str_replace(BODY_TEST_RELATED, '', $out) === body_test_paragraphs(10), 'body otherwise untouched');

// Clamped between the 2nd point and the second-to-last.
$out = mazaq_compose_article_body(body_test_paragraphs(4), BODY_TEST_RELATED, $no_ad);
rotation_test_check(body_test_related_after($out) === 'p2', '4 paragraphs: related after p2');

// Fewer than 4 safe points: appended at the end.
$out = mazaq_compose_article_body(body_test_paragraphs(3), BODY_TEST_RELATED, $no_ad);
rotation_test_check(body_test_related_after($out) === 'END', '3 paragraphs: related at the end');

// No related block: body comes back unchanged.
rotation_test_check(mazaq_compose_article_body(body_test_paragraphs(10), '', $no_ad) === body_test_paragraphs(10), 'empty related changes nothing');

// Today's bug as a fixture: an ad plate's note paragraph is not a safe point,
// so the related block never lands inside the plate and the note is not counted.
$plate = '<aside class="ad-container"><div class="ad"></div><p class="ad-container__note">إعلان</p></aside>';
$body = body_test_paragraphs(3) . "\n" . $plate . "\n" . body_test_paragraphs(7, 4);
$out = mazaq_compose_article_body($body, BODY_TEST_RELATED, $no_ad);
rotation_test_check(body_test_related_after($out) === 'p6', 'ad note not counted: related still after p6');
rotation_test_check(str_contains($out, $plate), 'related never inside an ad plate');

// No insert inside a blockquote, figure, list or table: their paragraphs are not safe points.
foreach ([
    '<blockquote><p>q1</p><p>q2</p><p>q3</p><p>q4</p></blockquote>',
    '<figure><img src="x"><figcaption><p>c1</p><p>c2</p><p>c3</p><p>c4</p></figcaption></figure>',
    '<ul><li><p>l1</p></li><li><p>l2</p></li><li><p>l3</p></li><li><p>l4</p></li></ul>',
    '<table><tr><td><p>t1</p></td><td><p>t2</p></td><td><p>t3</p></td><td><p>t4</p></td></tr></table>',
] as $block) {
    $body = body_test_paragraphs(5) . "\n" . $block . "\n" . body_test_paragraphs(5, 6);
    $out = mazaq_compose_article_body($body, BODY_TEST_RELATED, $no_ad);
    rotation_test_check(str_contains($out, $block), 'block left whole: ' . substr($block, 0, 12));
    rotation_test_check(body_test_related_after($out) === 'p6', 'block paragraphs not counted: ' . substr($block, 0, 12));
}

// An empty paragraph is not a safe point.
$body = "<p>p1</p>\n<p>&nbsp;</p>\n<p> </p>\n<p>p2</p>\n<p>p3</p>";
rotation_test_check(body_test_related_after(mazaq_compose_article_body($body, BODY_TEST_RELATED, $no_ad)) === 'END', 'empty paragraphs are not safe points');

// Ad plates: after every 3rd safe point, at most 3.
$calls = 0;
$plate = static function () use (&$calls): string {
    $calls++;
    return '<aside class="ad-container">AD</aside>';
};
/** Paragraph texts each plate follows, in order. */
function body_test_plates_after(string $html): array
{
    preg_match_all('/<p>(p\d+)<\/p>(?=\s*(?:<aside class="inline-related">.*?<\/aside>)?\s*<aside class="ad-container">AD)/s', $html, $m);
    return $m[1];
}
$out = mazaq_compose_article_body(body_test_paragraphs(14), '', $plate);
rotation_test_check(body_test_plates_after($out) === ['p3', 'p6', 'p9'], 'plates after p3, p6, p9 only');
rotation_test_check($calls === 3, 'ad renderer called once per placed plate');

// 3 or fewer safe points: no plates, and the renderer is never called.
$calls = 0;
$out = mazaq_compose_article_body(body_test_paragraphs(3), '', $plate);
rotation_test_check($out === body_test_paragraphs(3) && $calls === 0, '3 paragraphs: no plate, no render');

// Collision: 10 points put the related block after p6, where a plate is due.
// Inserts are not counted, so the other plates stay after p3 and p9.
$calls = 0;
$out = mazaq_compose_article_body(body_test_paragraphs(10), BODY_TEST_RELATED, $plate);
rotation_test_check(body_test_related_after($out) === 'p6', 'collision: related keeps p6');
rotation_test_check(body_test_plates_after($out) === ['p3', 'p7', 'p9'], 'collision: the p6 plate moves to p7');
rotation_test_check(!str_contains($out, BODY_TEST_RELATED . '<aside class="ad-container">') && !str_contains($out, '</aside>' . BODY_TEST_RELATED), 'related and a plate never back to back');
rotation_test_check($calls === 3, 'collision still places 3 plates');

// Plates skip block paragraphs the same way.
$calls = 0;
$body = body_test_paragraphs(2) . "\n<blockquote><p>q1</p><p>q2</p></blockquote>\n" . body_test_paragraphs(4, 3);
$out = mazaq_compose_article_body($body, '', $plate);
rotation_test_check(body_test_plates_after($out) === ['p3', 'p6'], 'quote paragraphs not counted for plates');
rotation_test_check(str_contains($out, '<blockquote><p>q1</p><p>q2</p></blockquote>'), 'no plate inside a quote');

echo 'article-body-test: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " checks passed\n";
