<?php

declare(strict_types=1);

/**
 * Article section identity: stable anchors and optional ranks for h2/h3.
 *
 * One internal parser owns the heading pattern, the numbering rule, and the
 * anchor-id rules. The extractor (rows for the rail, schema, and film graph)
 * and the injector (ids stamped into rendered content) both delegate to it,
 * so reported rows and stamped ids agree by construction.
 *
 * numbering: every heading without an author id consumes the next
 * article-section-N id, including empty-text headings. Author ids pass
 * through byte-preserved without consuming a number. A present-but-empty
 * author id is replaced with the assigned id, never doubled.
 *
 * @package Mazaq
 */

/**
 * Parse h2/h3 headings from HTML. Internal: callers use the extractor for
 * rows and the content filter for stamped markup, never this directly.
 *
 * @return array<int, array{id: string, level: int, text: string, empty: bool, has_id: bool, offset: int, empty_id_offset: int|null, empty_id_length: int}>
 */
function mazaq_parse_article_headings(string $content): array
{
    preg_match_all('/<h([23])\b([^>]*)>(.*?)<\/h\1>/isu', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $rows = [];
    $index = 0;

    foreach ($matches as $match) {
        $level = (int) $match[1][0];
        $attributes = (string) $match[2][0];
        $text = trim(html_entity_decode(wp_strip_all_tags((string) $match[3][0]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $empty_id_offset = null;
        $empty_id_length = 0;
        $id_match = [];
        if (preg_match('/\sid=(["\'])(.*?)\1/isu', $attributes, $id_match, PREG_OFFSET_CAPTURE)
            && trim((string) $id_match[2][0]) !== ''
        ) {
            $id = (string) $id_match[2][0];
            $has_id = true;
        } else {
            $index++;
            $id = 'article-section-' . $index;
            $has_id = false;
            if (isset($id_match[0]) && trim((string) $id_match[2][0]) === '') {
                // Present-but-empty author id: record its span (leading space
                // included) so the injector replaces it with the assigned id.
                $empty_id_offset = (int) $match[2][1] + (int) $id_match[0][1];
                $empty_id_length = strlen((string) $id_match[0][0]);
            }
        }

        $rows[] = [
            'id' => $id,
            'level' => $level,
            'text' => $text,
            'empty' => $text === '',
            'has_id' => $has_id,
            'offset' => (int) $match[0][1],
            'empty_id_offset' => $empty_id_offset,
            'empty_id_length' => $empty_id_length,
        ];
    }

    return $rows;
}

function mazaq_extract_article_headings(int $post_id): array
{
    $content = (string) get_post_field('post_content', $post_id);
    if ($content === '') {
        return [];
    }

    $rows = array_values(array_filter(
        mazaq_parse_article_headings($content),
        static fn(array $row): bool => !$row['empty']
    ));

    foreach (mazaq_listicle_heading_numbers(array_column($rows, 'text')) as $i => $number) {
        $rows[$i]['number'] = $number;
    }

    return array_map(
        static fn(array $row): array => [
            'id' => $row['id'],
            'level' => $row['level'],
            'text' => $row['text'],
            'number' => $row['number'],
        ],
        $rows
    );
}

/**
 * Display number for each heading in a run.
 *
 * A ranked listicle numbers its own headings ("31. Cars 3 (2017)") and counts down as
 * often as up, so a positional 01..N index contradicts the heading it sits beside.
 * Mirror the article's own numbers whenever it has any; number by position only when
 * no heading carries one. A heading with no number of its own inside a numbered list
 * (an intro, a conclusion) returns null: showing its position there would put a
 * positional number next to real ranks, which is the contradiction this exists to end.
 *
 * Pure: no WP, no post. Covered by tests/heading-numbers-test.php.
 *
 * @param string[] $texts Heading texts, in document order.
 * @return array<int, int|null> One entry per heading, same order. Null = show no number.
 */
function mazaq_listicle_heading_numbers(array $texts): array
{
    $numbers = [];
    $numbered = 0;

    foreach ($texts as $text) {
        // [0-9] rather than \d: under /u, \d also matches Arabic-Indic digits, which
        // (int) then casts to 0 -- every entry would render "00" on this Arabic site.
        // Headings numbered in Arabic-Indic digits fall back to position instead.
        // Digits must be followed by punctuation, so "1917 (2019)" and
        // "2001: A Space Odyssey" are read as titles, not as ranks.
        if (preg_match('/^([0-9]{1,3})\s*[.)\-–—]/u', $text, $match)) {
            $numbers[] = (int) $match[1];
            $numbered++;
            continue;
        }
        $numbers[] = null;
    }

    if ($numbered === 0) {
        foreach ($numbers as $i => $number) {
            $numbers[$i] = $i + 1;
        }
    }

    return $numbers;
}

function mazaq_add_article_heading_ids(string $content): string
{
    if (is_admin() || !is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $rows = mazaq_parse_article_headings($content);
    for ($i = count($rows) - 1; $i >= 0; $i--) {
        if ($rows[$i]['has_id']) {
            continue;
        }
        if ($rows[$i]['empty_id_offset'] !== null) {
            $content = substr($content, 0, $rows[$i]['empty_id_offset'])
                . ' id="' . $rows[$i]['id'] . '"'
                . substr($content, $rows[$i]['empty_id_offset'] + $rows[$i]['empty_id_length']);
            continue;
        }
        // The pattern guarantees no '>' inside the opening tag, so the first
        // '>' past the match start closes it. Splice back-to-front so offsets
        // from the single parse stay valid.
        $tag_end = strpos($content, '>', $rows[$i]['offset']);
        if ($tag_end === false) {
            continue;
        }
        $content = substr($content, 0, $tag_end) . ' id="' . $rows[$i]['id'] . '"' . substr($content, $tag_end);
    }

    return $content;
}
add_filter('the_content', 'mazaq_add_article_heading_ids', 8);

/**
 * A heading's index as the reading indexes print it: zero-padded, or '' for no number.
 *
 * @param int|null $number From mazaq_listicle_heading_numbers().
 */
function mazaq_heading_index(?int $number): string
{
    return $number === null ? '' : str_pad((string) $number, 2, '0', STR_PAD_LEFT);
}
