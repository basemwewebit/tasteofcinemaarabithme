<?php

/**
 * Standalone self-check for the film graph. No framework, no WP: run with
 * `php tests/film-graph-test.php`. Stubs the hook and post functions so the
 * film graph and the real Article section module load in isolation; film titles
 * are mined per post from fixture HTML, through both modules.
 */

declare(strict_types=1);

if (!function_exists('add_action')) {
    function add_action(...$args): void {}
}
if (!function_exists('add_filter')) {
    function add_filter(...$args): void {}
}
if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, $value = null, ...$args) { return $value; }
}
if (!function_exists('get_post_field')) {
    function get_post_field(string $field, int $post_id) {
        return $GLOBALS['__film_posts'][$post_id] ?? '';
    }
}
if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags(string $string): string { return trim(strip_tags($string)); }
}

require __DIR__ . '/rotation-test-helper.php';
require __DIR__ . '/../inc/article-headings.php';
require __DIR__ . '/../inc/film-graph.php';

/** Film titles mined from a post whose H2 headings are $texts. */
function film_titles(array $texts): array
{
    static $post_id = 0;
    $post_id++;
    $GLOBALS['__film_posts'][$post_id] = implode('', array_map(
        static fn(string $text): string => '<h2>' . $text . '</h2><p>Body.</p>',
        $texts
    ));
    return mazaq_film_titles_from_headings($post_id);
}

function frag(float $value, float $best): array
{
    return ['ratingValue' => $value, 'bestRating' => $best];
}

// Empty input → null (no aggregate to show).
rotation_test_check(mazaq_film_average_normalized([]) === null);

// Fragments with no positive scale are ignored → null.
rotation_test_check(mazaq_film_average_normalized([frag(4, 0)]) === null);

// Single 8/10 review → 8.0 on the /10 scale, count 1.
rotation_test_check(mazaq_film_average_normalized([frag(8, 10)]) === ['rating' => 8.0, 'count' => 1]);

// Mixed scales normalize before averaging: 8/10 (=8) and 4/5 (=8) → 8.0.
rotation_test_check(mazaq_film_average_normalized([frag(8, 10), frag(4, 5)]) === ['rating' => 8.0, 'count' => 2]);

// Averaging rounds to one decimal: 7/10 (=7) and 8/10 (=8) → 7.5.
rotation_test_check(mazaq_film_average_normalized([frag(7, 10), frag(8, 10)]) === ['rating' => 7.5, 'count' => 2]);

// Zero-scale fragments drop out of the count: only the 6/10 counts.
rotation_test_check(mazaq_film_average_normalized([frag(6, 10), frag(3, 0)]) === ['rating' => 6.0, 'count' => 1]);

// --- film titles, per post: a Ranked list's paren-year entries, rank + year stripped ---
rotation_test_check(film_titles(['1. Desperate Hours (1990)', '7. Parasite (2019)', '10. U.S. Marshals (1998)', '3 - The Client (1994)'])
    === ['Desperate Hours', 'Parasite', 'U.S. Marshals', 'The Client']);
// Keeps an inner number, strips only the trailing paren-year.
rotation_test_check(film_titles(['3. Alien (1979)', '2. Blade Runner 2049 (1982)', '1. Heat (1995)'])
    === ['Alien', 'Blade Runner 2049', 'Heat']);
// A title that opens with digits keeps them: a colon is never a rank marker.
rotation_test_check(film_titles(['2001: A Space Odyssey (1968)', '1917 (2019)', '300: Rise of an Empire (2014)'])
    === ['2001: A Space Odyssey', '1917', '300: Rise of an Empire']);
// Unranked but paren-year-tagged entries count; the intro has no year and drops out.
rotation_test_check(film_titles(['مقدمة', 'Under the Skin (2013)', 'Heat (1995)', 'Alien (1979)'])
    === ['Under the Skin', 'Heat', 'Alien']);
// Numbered PROSE (a "10 reasons" list) has no paren-year → nothing.
rotation_test_check(film_titles(['1. إنه الفيلم الأكثر تمرداً من إنتاج استوديو', '2. أداء Ryan Gosling أفضل أداءات عام 2023', '3. Se7en']) === []);
// Fewer than three H2s is not a Ranked list → nothing, even with paren-years.
rotation_test_check(film_titles(['1. Alien (1979)', '2. Aliens (1986)']) === []);

// --- skip-list + guards ---
rotation_test_check(mazaq_film_is_skipped('') === true);
rotation_test_check(mazaq_film_is_skipped('مقدمة') === true);          // section header
rotation_test_check(mazaq_film_is_skipped('Conclusion') === true);      // case-insensitive
rotation_test_check(mazaq_film_is_skipped(str_repeat('ا', 90)) === true); // too long to be a title
rotation_test_check(mazaq_film_is_skipped('---') === true);             // divider H2, no letters
rotation_test_check(mazaq_film_is_skipped('— ***') === true);           // punctuation-only
rotation_test_check(mazaq_film_is_skipped('Desperate Hours') === false); // a real title survives

echo "film-graph self-check: OK\n";
