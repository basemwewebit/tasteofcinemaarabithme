<?php

declare(strict_types=1);

/**
 * Article body composition: every insert into a post's body lands at a safe
 * point, and inserts never count as paragraphs.
 *
 * @package Mazaq
 */

/**
 * Compose the finished article body (after the_content) with its inserts.
 *
 * @param string $related_html The Inline related block, or '' for none.
 */
function mazaq_compose_article_body(string $body, string $related_html): string
{
    if ($related_html === '') {
        return $body;
    }

    $points = mazaq_article_body_safe_points($body);
    $count = count($points);
    if ($count < 4) {
        return $body . $related_html;
    }

    $at = $points[max(2, min($count - 1, (int) round($count * 0.6))) - 1];

    return substr($body, 0, $at) . $related_html . substr($body, $at);
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
