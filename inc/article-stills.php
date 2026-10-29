<?php

declare(strict_types=1);

/**
 * Article stills: every image an article paragraph holds alone is mounted as a
 * still — a plate at the column's width, the image at its own size on it, a
 * credit line, and a link that opens it large (the lightbox in app-single.js).
 *
 * @package Mazaq
 */

/** An image at least this wide fills its plate edge to edge instead of sitting on a mount. */
const MAZAQ_STILL_FULL_WIDTH = 700;

/**
 * Mount the stills in a finished article body. Pure: string in, string out.
 *
 * The credit is the image's alt when it reads as words (not a file slug). An
 * image that directly follows a heading is that section's still: the heading
 * names it, so it becomes the alt when the alt is a slug or empty, and the
 * credit line is left out of the page (the heading already says it) but still
 * travels to the lightbox, where the heading isn't in view.
 */
function mazaq_mount_article_stills(string $html): string
{
    $pattern = '#<h([2-4])\b[^>]*>(.*?)</h\1>'
        . '|<p\b[^>]*>\s*(<a\b[^>]*>)?\s*(<img\b[^>]*>)\s*(?(3)</a>)\s*</p>#is';

    $heading = '';
    $heading_end = -1;

    return (string) preg_replace_callback($pattern, static function (array $m) use ($html, &$heading, &$heading_end): string {
        [$whole, $at] = $m[0];

        if (($m[1][0] ?? '') !== '') {
            $heading = mazaq_still_heading_text($m[2][0]);
            $heading_end = $at + strlen($whole);
            return $whole;
        }

        $link = $m[3][0] ?? '';
        $img = $m[4][0];
        $alt = mazaq_still_attr($img, 'alt');
        $section = $heading !== '' && $heading_end >= 0 && trim(substr($html, $heading_end, $at - $heading_end)) === '';

        $named = mazaq_still_alt_is_words($alt) ? $alt : '';
        if ($named === '' && $section) {
            $img = mazaq_still_set_alt($img, $heading);
        }
        // An alt that only repeats (part of) its heading gives way to the fuller heading.
        $repeats = $section && ($named === '' || mazaq_still_within($named, $heading));
        $credit = $repeats ? $heading : $named;
        $shown = $credit !== '' && !$repeats;

        $classes = 'still';
        if ((int) mazaq_still_attr($img, 'width') >= MAZAQ_STILL_FULL_WIDTH) {
            $classes .= ' still--full';
        }

        $zoom = '<span class="still__zoom" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></span>';

        if ($link !== '') {
            // The author's own link wins over the lightbox.
            $plate = '<div class="still__plate">' . $link . $img . '</a></div>';
        } else {
            $label = $credit !== '' ? sprintf(__('اعرض الصورة كاملة: %s', 'mazaq'), $credit) : __('اعرض الصورة كاملة', 'mazaq');
            $plate = '<a class="still__plate" href="' . esc_url(mazaq_still_attr($img, 'src')) . '" data-still-open'
                . ($credit !== '' ? ' data-still-credit="' . esc_attr($credit) . '"' : '')
                . ' aria-label="' . esc_attr($label) . '">' . $img . $zoom . '</a>';
        }

        $caption = $shown ? '<figcaption class="still__credit">' . mazaq_still_credit_html($credit) . '</figcaption>' : '';

        return '<figure class="' . $classes . '">' . $plate . $caption . '</figure>';
    }, $html, -1, $count, PREG_OFFSET_CAPTURE);
}

/** A heading's plain text without its list number ("2. Gremlins 2" → "Gremlins 2"). */
function mazaq_still_heading_text(string $inner): string
{
    $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    return trim((string) preg_replace('/^\d+\s*[.\-–)]\s*/u', '', $text));
}

function mazaq_still_attr(string $img, string $name): string
{
    return preg_match('/\s' . $name . '="([^"]*)"/i', $img, $m) ? html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
}

function mazaq_still_set_alt(string $img, string $alt): string
{
    $attr = ' alt="' . esc_attr($alt) . '"';
    return preg_match('/\salt="[^"]*"/i', $img)
        ? (string) preg_replace('/\salt="[^"]*"/i', $attr, $img, 1)
        : (string) preg_replace('/^<img\b/i', '<img' . $attr, $img, 1);
}

/** Words, not a file slug: "Charley Varrick (1973)" yes; "angels-egg-2", "p11606569_i_h10_ac", "Avalon" no. */
function mazaq_still_alt_is_words(string $alt): bool
{
    return (bool) preg_match('/\S\s+\S/u', trim($alt));
}

/** Is $part inside $whole, ignoring case, spacing and punctuation? */
function mazaq_still_within(string $part, string $whole): bool
{
    $norm = static fn(string $s): string => (string) preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($s));
    return $norm($part) !== '' && str_contains($norm($whole), $norm($part));
}

/** The credit, with a trailing year set in numerals: "Cronos <span class="num">(1993)</span>". */
function mazaq_still_credit_html(string $credit): string
{
    $safe = esc_html($credit);
    return (string) preg_replace('/\((\d{4})\)\s*$/', '<span class="still__year num">($1)</span>', $safe);
}

add_filter('the_content', static function (string $content): string {
    return is_singular('post') ? mazaq_mount_article_stills($content) : $content;
}, 20);
