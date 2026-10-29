<?php

/**
 * Standalone self-check for Article stills. No framework, no WP:
 * run with `php tests/article-stills-test.php`.
 *
 * Seam under test: mazaq_mount_article_stills(), string in, string out.
 */

declare(strict_types=1);

require_once __DIR__ . '/rotation-test-helper.php';

function add_filter(...$args): void {}
function __(string $s): string { return $s; }
function esc_attr(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function esc_url(string $s): string { return $s; }

require_once __DIR__ . '/../inc/article-stills.php';

$img = static fn(string $alt, int $w = 560): string => '<img src="https://x.test/a.jpg" alt="' . $alt . '" width="' . $w . '" height="300" class="my-4 rounded">';

// A still right under its section heading: heading becomes the alt, credit goes to the lightbox only.
$out = mazaq_mount_article_stills('<h2 id="s2">2. Gremlins 2: The New Batch (1990)</h2>' . "\n" . '<p class="mb-4">' . $img('gremlins-2') . '</p>');
rotation_test_check(str_contains($out, '<figure class="still">'), 'section still is mounted');
rotation_test_check(str_contains($out, 'alt="Gremlins 2: The New Batch (1990)"'), 'slug alt replaced by heading');
rotation_test_check(str_contains($out, 'data-still-credit="Gremlins 2: The New Batch (1990)"'), 'heading credit travels to the lightbox');
rotation_test_check(!str_contains($out, '<figcaption'), 'no visible credit repeating the heading');
rotation_test_check(!str_contains($out, '<p class="mb-4">'), 'paragraph wrapper removed');

// Real-words alt that differs from the heading is shown, with the year in numerals.
$out = mazaq_mount_article_stills('<h2>5. Cape Fear (1991)</h2><p>' . $img('Max Cady (1991)') . '</p>');
rotation_test_check(str_contains($out, '<figcaption class="still__credit">Max Cady <span class="still__year num">(1991)</span></figcaption>'), 'distinct alt is a visible credit');

// An alt that is a shorter copy of the heading gives way to the heading.
$out = mazaq_mount_article_stills('<h2>2. Gremlins 2: The New Batch (1990)</h2><p>' . $img('Gremlins 2 The New Batch') . '</p>');
rotation_test_check(!str_contains($out, '<figcaption') && str_contains($out, 'data-still-credit="Gremlins 2: The New Batch (1990)"'), 'partial-copy alt hidden, heading credits the lightbox');

// Mid-section still: no heading credit, slug alt left alone, no caption.
$out = mazaq_mount_article_stills('<h2>Section</h2><p>Text.</p><p>' . $img('angels-egg-2') . '</p>');
rotation_test_check(str_contains($out, 'alt="angels-egg-2"') && !str_contains($out, 'data-still-credit') && !str_contains($out, '<figcaption'), 'mid-section slug still gets no invented credit');

// Author's link wins over the lightbox.
$out = mazaq_mount_article_stills('<p><a href="https://y.test/">' . $img('Some film (2000)') . '</a></p>');
rotation_test_check(str_contains($out, '<a href="https://y.test/">') && !str_contains($out, 'data-still-open'), 'author link kept, no lightbox');

// Wide image fills its plate; text paragraphs are untouched.
$out = mazaq_mount_article_stills('<p>Words <b>here</b>.</p><p>' . $img('', 1200) . '</p>');
rotation_test_check(str_contains($out, 'still still--full'), 'wide image is full');
rotation_test_check(str_contains($out, '<p>Words <b>here</b>.</p>'), 'text paragraph untouched');

// A paragraph with text around the image is not a still.
$out = mazaq_mount_article_stills('<p>Caption ' . $img('x y') . '</p>');
rotation_test_check(!str_contains($out, '<figure'), 'mixed paragraph not mounted');

echo 'OK: ' . $GLOBALS['rotation_test_passed'] . " checks\n";
