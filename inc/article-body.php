<?php

declare(strict_types=1);

/**
 * Article body composition: every insert into a post's body lands at a safe
 * point, and inserts never count as paragraphs.
 *
 * @package Mazaq
 */

const MAZAQ_IN_ARTICLE_AD_EVERY = 3;
const MAZAQ_IN_ARTICLE_AD_MAX = 3;

/**
 * Compose the finished article body (after the_content) with its inserts.
 *
 * In-article ad plates follow every 3rd safe point, at most 3, and none in a
 * body of 3 or fewer. The Inline related block takes the safe point nearest
 * 60% (from the 2nd to the second-to-last), or the end of a shorter body; a
 * plate due at that point moves one point later.
 *
 * @param string $related_html The Inline related block, or '' for none.
 * @param callable(): string $render_ad One in-article plate. Called only for a
 *        plate that is placed, in document order, so a Direct ad is never spent
 *        on a discarded plate.
 */
function mazaq_compose_article_body(string $body, string $related_html, callable $render_ad): string
{
    $points = mazaq_article_body_safe_points($body);
    $count = count($points);
    $inserts = [];

    $related_at = null;
    if ($related_html !== '') {
        if ($count < 4) {
            $inserts[] = [strlen($body), $related_html];
        } else {
            $related_at = max(2, min($count - 1, (int) round($count * 0.6)));
            $inserts[] = [$points[$related_at - 1], $related_html];
        }
    }

    if ($count > MAZAQ_IN_ARTICLE_AD_EVERY) {
        for ($i = 1; $i <= MAZAQ_IN_ARTICLE_AD_MAX; $i++) {
            $at = $i * MAZAQ_IN_ARTICLE_AD_EVERY;
            if ($at === $related_at) {
                $at++;
            }
            if ($at > $count) {
                break;
            }
            $inserts[] = [$points[$at - 1], $render_ad()];
        }
    }

    usort($inserts, static fn(array $a, array $b): int => $b[0] <=> $a[0]);
    foreach ($inserts as [$offset, $html]) {
        $body = substr($body, 0, $offset) . $html . substr($body, $offset);
    }

    return $body;
}

/**
 * Safe points: byte offsets just after each top-level paragraph that has text.
 * A paragraph inside a blockquote, figure, list, table or aside (an ad plate)
 * is never one, so inserts can't split those blocks or land inside a plate.
 *
 * @return int[] Ascending.
 */
function mazaq_article_body_safe_points(string $body): array
{
    preg_match_all('/<(\/?)(p|blockquote|figure|ul|ol|table|aside)\b[^>]*>/i', $body, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $points = [];
    $depth = 0;
    $open = null;
    foreach ($tags as $tag) {
        $closing = $tag[1][0] === '/';
        $end = $tag[0][1] + strlen($tag[0][0]);

        if (strtolower($tag[2][0]) !== 'p') {
            $depth = max(0, $depth + ($closing ? -1 : 1));
            continue;
        }
        if ($depth > 0) {
            continue;
        }
        if (!$closing) {
            $open = $end;
            continue;
        }
        if ($open !== null) {
            $text = html_entity_decode(wp_strip_all_tags(substr($body, $open, $tag[0][1] - $open)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/[^\s\x{00A0}]/u', $text)) {
                $points[] = $end;
            }
            $open = null;
        }
    }

    return $points;
}
