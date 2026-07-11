<?php

/**
 * Standalone self-check for the pure film-graph aggregate math. No framework,
 * no WP: run with `php tests/film-graph-test.php`. Stubs the two hook functions
 * inc/film-graph.php calls at load time so the file can be included in isolation.
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

require __DIR__ . '/../inc/film-graph.php';

function frag(float $value, float $best): array
{
    return ['ratingValue' => $value, 'bestRating' => $best];
}

// Empty input → null (no aggregate to show).
assert(mazaq_film_average_normalized([]) === null);

// Fragments with no positive scale are ignored → null.
assert(mazaq_film_average_normalized([frag(4, 0)]) === null);

// Single 8/10 review → 8.0 on the /10 scale, count 1.
assert(mazaq_film_average_normalized([frag(8, 10)]) === ['rating' => 8.0, 'count' => 1]);

// Mixed scales normalize before averaging: 8/10 (=8) and 4/5 (=8) → 8.0.
assert(mazaq_film_average_normalized([frag(8, 10), frag(4, 5)]) === ['rating' => 8.0, 'count' => 2]);

// Averaging rounds to one decimal: 7/10 (=7) and 8/10 (=8) → 7.5.
assert(mazaq_film_average_normalized([frag(7, 10), frag(8, 10)]) === ['rating' => 7.5, 'count' => 2]);

// Zero-scale fragments drop out of the count: only the 6/10 counts.
assert(mazaq_film_average_normalized([frag(6, 10), frag(3, 0)]) === ['rating' => 6.0, 'count' => 1]);

// --- heading parser: keep only paren-year film entries; strip rank + year ---
assert(mazaq_film_title_from_heading('1. Desperate Hours (1990)') === 'Desperate Hours');
assert(mazaq_film_title_from_heading('7. Parasite (2019)') === 'Parasite');
assert(mazaq_film_title_from_heading('10. U.S. Marshals (1998)') === 'U.S. Marshals');
assert(mazaq_film_title_from_heading('3 - The Client (1994)') === 'The Client');
// Keeps an inner number, strips only the trailing paren-year.
assert(mazaq_film_title_from_heading('2. Blade Runner 2049 (1982)') === 'Blade Runner 2049');
// Unranked but paren-year-tagged still counts as a film entry.
assert(mazaq_film_title_from_heading('Under the Skin (2013)') === 'Under the Skin');
// Numbered PROSE (a "10 reasons" list) has no paren-year → rejected.
assert(mazaq_film_title_from_heading('1. إنه الفيلم الأكثر تمرداً من إنتاج استوديو') === '');
// A bare trailing year is not a paren-year → still rejected.
assert(mazaq_film_title_from_heading('5. أداء Ryan Gosling أفضل أداءات عام 2023') === '');
assert(mazaq_film_title_from_heading('Se7en') === '');

// --- skip-list + guards ---
assert(mazaq_film_is_skipped('') === true);
assert(mazaq_film_is_skipped('مقدمة') === true);          // section header
assert(mazaq_film_is_skipped('Conclusion') === true);      // case-insensitive
assert(mazaq_film_is_skipped(str_repeat('ا', 90)) === true); // too long to be a title
assert(mazaq_film_is_skipped('---') === true);             // divider H2, no letters
assert(mazaq_film_is_skipped('— ***') === true);           // punctuation-only
assert(mazaq_film_is_skipped('Desperate Hours') === false); // a real title survives

echo "film-graph self-check: OK\n";
