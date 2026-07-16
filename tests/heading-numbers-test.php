<?php

/**
 * Standalone self-check for the reading index's heading numbering. No framework,
 * no WP: run with `php tests/heading-numbers-test.php`. Stubs the hook functions
 * inc/helpers.php calls at load time so the file can be included in isolation.
 */

declare(strict_types=1);

if (!function_exists('add_action')) {
    function add_action(...$args): void {}
}
if (!function_exists('add_filter')) {
    function add_filter(...$args): void {}
}

require __DIR__ . '/../inc/helpers.php';

// No headings → nothing to number.
assert(mazaq_listicle_heading_numbers([]) === []);

// Prose article, no numbers anywhere → positional 1..N.
assert(mazaq_listicle_heading_numbers(['المقدمة', 'الحبكة', 'الخاتمة']) === [1, 2, 3]);

// A countdown listicle mirrors its own ranks rather than its document order.
assert(mazaq_listicle_heading_numbers([
    '31. Cars 3 (2017)',
    '30. The Good Dinosaur (2015)',
    '1. Toy Story (1995)',
]) === [31, 30, 1]);

// Ascending listicles work the same way.
assert(mazaq_listicle_heading_numbers(['1. Alien', '2. Aliens']) === [1, 2]);

// Separator may be a paren, a dash, or an en/em dash, with or without a space.
// Descending on purpose, so a positional fallback could not produce this.
assert(mazaq_listicle_heading_numbers(['9) Alien', '8 - Aliens', '7 – Alien 3', '6 — Resurrection']) === [9, 8, 7, 6]);

// A title that merely opens with digits is not a rank: no punctuation follows them.
// With nothing else numbered either, the run falls back to position.
assert(mazaq_listicle_heading_numbers(['1917 (2019)', '2001: A Space Odyssey', 'مقدمة']) === [1, 2, 3]);

// An unnumbered heading among ranks (an intro) shows no number at all. Giving it its
// position would print "01" beside "1. Toy Story" -- the very contradiction this ends.
assert(mazaq_listicle_heading_numbers(['مقدمة', '3. Ratatouille', '2. Monsters, Inc.', '1. Toy Story'])
    === [null, 3, 2, 1]);

// Once anything is numbered, real ranks always win: a positional number must never be
// printed next to a heading that carries its own. Half-and-half is no exception.
assert(mazaq_listicle_heading_numbers(['مقدمة', 'خاتمة', '2. Aliens', '1. Alien']) === [null, null, 2, 1]);

// Ranks are capped at three digits, so a four-digit year is never read as one --
// not even a trailing "1000." that would otherwise satisfy the separator.
assert(mazaq_listicle_heading_numbers(['100. Alien', '101. Aliens', '1000. Movies']) === [100, 101, null]);

// Arabic-Indic digits are not parsed as ranks: under /u, \d would match them and
// (int) would cast them to 0, printing "00" for every entry on this Arabic site.
// They fall back to position instead.
assert(mazaq_listicle_heading_numbers(['١١. Up (2009)', '١٠. Inside Out']) === [1, 2]);

// Mixed scripts: only the ASCII rank counts, so the Arabic-numbered one shows nothing
// rather than a bogus 00.
assert(mazaq_listicle_heading_numbers(['١١. Up (2009)', '10. Inside Out']) === [null, 10]);

echo "heading-numbers: all assertions passed\n";
