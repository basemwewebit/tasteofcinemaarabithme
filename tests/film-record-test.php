<?php

/**
 * Standalone self-check for the Film record. No framework, no WP: run with
 * `php tests/film-record-test.php`.
 *
 * Seam under test: mazaq_film_record() over a fake get_field backed by
 * $GLOBALS['__film_fields'], plus the star helper fed the parsed rating.
 */

declare(strict_types=1);

require_once __DIR__ . '/rotation-test-helper.php';

function get_field(string $name, $post_id = false)
{
    return $GLOBALS['__film_fields'][$post_id][$name] ?? null;
}
if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, $value = null, ...$args) { return $value; }
}
if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string { return $text; }
}

require_once __DIR__ . '/../inc/film-record.php';

/** The Film record of a post whose film fields are $fields. */
function film_record_of(array $fields): ?array
{
    static $post_id = 0;
    $post_id++;
    $GLOBALS['__film_fields'][$post_id] = $fields;
    return mazaq_film_record($post_id);
}

/** The parsed rating of a post whose rating field reads $text. */
function film_rating_of(string $text): ?array
{
    return film_record_of(['film_rating' => $text])['rating'] ?? null;
}

// No film: every field empty, blank or not a scalar.
rotation_test_check(film_record_of([]) === null, 'no fields: no record');
rotation_test_check(film_record_of(['film_title' => '  ', 'film_year' => '', 'film_director' => ['x']]) === null, 'blank and non-scalar fields: no record');

// The raw text, trimmed, as the editor typed it.
rotation_test_check(film_record_of(['film_title' => ' Parasite ', 'film_year' => '٢٠١٩', 'film_director' => 'Bong Joon-ho، Han Jin-won']) === [
    'title' => 'Parasite',
    'year' => '٢٠١٩',
    'director' => 'Bong Joon-ho، Han Jin-won',
    'rating_text' => '',
    'rating' => null,
], 'record keeps raw text, trimmed');

// A zero rating is data, not absence.
$zero = film_record_of(['film_rating' => '0']);
rotation_test_check($zero !== null && $zero['rating_text'] === '0', 'a "0" rating makes a record');
rotation_test_check($zero['rating'] === ['ratingValue' => 0, 'bestRating' => 10, 'worstRating' => 0], '"0" parses as 0/10');

// Every rating format editors write.
rotation_test_check(film_rating_of('8/10') === ['ratingValue' => 8, 'bestRating' => 10, 'worstRating' => 1], '8/10');
rotation_test_check(film_rating_of('٨ من ١٠') === ['ratingValue' => 8, 'bestRating' => 10, 'worstRating' => 1], 'Arabic digits, من');
rotation_test_check(film_rating_of('8 out of 10') === ['ratingValue' => 8, 'bestRating' => 10, 'worstRating' => 1], 'out of');
rotation_test_check(film_rating_of('★★★★') === ['ratingValue' => 4, 'bestRating' => 5, 'worstRating' => 1], 'star glyphs on a /5 scale');
rotation_test_check(film_rating_of('7.5') === ['ratingValue' => 7.5, 'bestRating' => 10, 'worstRating' => 1], 'bare number on the default /10 scale');
rotation_test_check(film_rating_of('7,5/10') === ['ratingValue' => 7.5, 'bestRating' => 10, 'worstRating' => 1], 'comma decimal');

// Unparseable: no rating, but the text survives for display.
$junk = film_record_of(['film_rating' => 'رائع']);
rotation_test_check($junk['rating'] === null && $junk['rating_text'] === 'رائع', 'junk: no rating, text kept');
rotation_test_check(film_rating_of('11/10') === null, 'above the scale: no rating');
rotation_test_check(film_rating_of('12') === null, 'bare number above the default scale: no rating');

// Stars, from a parsed rating.
rotation_test_check(mazaq_film_rating_stars(film_rating_of('8/10')) === [
    'value' => 8,
    'best' => 10,
    'percent' => 80.0,
    'label' => 'التقييم: 8 من 10',
], 'stars for 8/10');
rotation_test_check(mazaq_film_rating_stars(['ratingValue' => 7.5, 'bestRating' => 10])['label'] === 'التقييم: 7.5 من 10', 'stars for a term average');
rotation_test_check(mazaq_film_rating_stars(film_rating_of('★★★'))['percent'] === 60.0, 'stars percent on a /5 scale');

echo 'film-record-test: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " checks passed\n";
