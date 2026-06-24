<?php

/**
 * Film-entity structured data layer.
 *
 * Yoast SEO already emits a generic Article/WebPage @graph for every post. It
 * has no knowledge of the editorial film metadata (director, year, rating) or
 * of the listicle structure that defines this magazine. This module extends
 * Yoast's existing graph — it never emits a competing JSON-LD block — by
 * appending Movie / Review / ItemList nodes built from data editors already
 * enter, and wiring them into Yoast's WebPage/Article/Organization nodes by
 * @id reference.
 *
 * Net effect: film reviews become eligible for review-star rich results and
 * ranked lists describe themselves to search engines, with zero new editorial
 * work. If Yoast is ever deactivated the `wpseo_schema_graph` filter simply
 * never fires and nothing here runs.
 *
 * @package Mazaq
 */

declare(strict_types=1);

/**
 * Append film-entity pieces to the Yoast schema graph.
 *
 * @param array $graph   The full Schema.org @graph array Yoast assembled.
 * @param mixed $context Yoast Meta_Tags_Context (duck-typed; only ->canonical used).
 * @return array
 */
function mazaq_schema_inject_film($graph, $context): array
{
    if (!is_array($graph) || !is_singular('post')) {
        return $graph;
    }

    $post_id = get_queried_object_id();
    if ($post_id <= 0) {
        return $graph;
    }

    $canonical = '';
    if (is_object($context) && isset($context->canonical) && is_string($context->canonical)) {
        $canonical = $context->canonical;
    }
    if ($canonical === '') {
        $canonical = (string) get_permalink($post_id);
    }
    if ($canonical === '') {
        return $graph;
    }

    $film       = mazaq_schema_film_fields($post_id);
    $is_film     = (bool) apply_filters('mazaq_schema_is_film_review', ($film['film_title'] !== '' || $film['film_director'] !== ''), $post_id);
    $headings   = function_exists('mazaq_extract_article_headings') ? mazaq_extract_article_headings($post_id) : [];
    $list_items = array_values(array_filter($headings, static fn(array $h): bool => (int) ($h['level'] ?? 0) === 2));
    $is_list    = count($list_items) >= 3;

    if (!$is_film && !$is_list) {
        return $graph;
    }

    // Read reusable @ids out of the graph Yoast already built, so we reference
    // its real nodes instead of guessing internal id formats.
    $webpage_idx   = mazaq_schema_find_node_index($graph, 'WebPage');
    $article_idx   = mazaq_schema_find_node_index($graph, 'Article');
    $author_id     = mazaq_schema_node_ref($graph, $article_idx, 'author');
    $publisher_id  = mazaq_schema_first_id_of_type($graph, 'Organization');
    $image_id      = mazaq_schema_node_ref($graph, $webpage_idx, 'primaryImageOfPage')
        ?: mazaq_schema_node_ref($graph, $webpage_idx, 'image');
    $in_language   = mazaq_schema_node_scalar($graph, $webpage_idx, 'inLanguage') ?: 'ar';

    $movie_id = $canonical . '#/schema/movie/' . $post_id;
    $list_id  = $canonical . '#/schema/itemlist/' . $post_id;

    if ($is_film) {
        $movie = [
            '@type'      => 'Movie',
            '@id'        => $movie_id,
            'name'       => $film['film_title'] !== '' ? $film['film_title'] : get_the_title($post_id),
            'url'        => $canonical,
            'inLanguage' => $in_language,
        ];

        $directors = mazaq_schema_people($film['film_director']);
        if ($directors !== []) {
            $movie['director'] = $directors;
        }

        if (preg_match('/\b(\d{4})\b/', mazaq_schema_latin_digits($film['film_year']), $ym)) {
            $movie['dateCreated'] = $ym[1];
        }

        if ($image_id !== '') {
            $movie['image'] = ['@id' => $image_id];
        } elseif (has_post_thumbnail($post_id)) {
            $thumb = (string) get_the_post_thumbnail_url($post_id, 'full');
            if ($thumb !== '') {
                $movie['image'] = $thumb;
            }
        }

        $rating = mazaq_schema_parse_rating($film['film_rating']);
        if ($rating !== null) {
            $review_id = $canonical . '#/schema/review/' . $post_id;

            $review = [
                '@type'         => 'Review',
                '@id'           => $review_id,
                'name'          => get_the_title($post_id),
                'itemReviewed'  => ['@id' => $movie_id],
                'reviewRating'  => array_merge(['@type' => 'Rating'], $rating),
                'datePublished' => (string) get_the_date('c', $post_id),
            ];
            if ($author_id !== '') {
                $review['author'] = ['@id' => $author_id];
            }
            if ($publisher_id !== '') {
                $review['publisher'] = ['@id' => $publisher_id];
            }

            $movie['review'] = ['@id' => $review_id];
            $graph[] = apply_filters('mazaq_schema_review_data', $review, $post_id, $context);
        }

        $graph[] = apply_filters('mazaq_schema_movie_data', $movie, $post_id, $context);

        // Make the page "about" the film so the Movie is the page's main entity.
        if ($webpage_idx !== null && empty($graph[$webpage_idx]['about'])) {
            $graph[$webpage_idx]['about'] = ['@id' => $movie_id];
        }
    }

    if ($is_list) {
        $elements = [];
        $position = 0;
        foreach ($list_items as $item) {
            $text = trim((string) ($item['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $position++;
            $element = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => $text,
            ];
            if (!empty($item['id'])) {
                $element['url'] = $canonical . '#' . $item['id'];
            }
            $elements[] = $element;
        }

        if ($elements !== []) {
            $list = [
                '@type'           => 'ItemList',
                '@id'             => $list_id,
                'name'            => get_the_title($post_id),
                'numberOfItems'   => count($elements),
                'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
                'itemListElement' => $elements,
            ];
            $graph[] = apply_filters('mazaq_schema_itemlist_data', $list, $post_id, $context);

            // Only claim mainEntity if a film review hasn't already claimed `about`.
            if ($webpage_idx !== null && !$is_film && empty($graph[$webpage_idx]['mainEntity'])) {
                $graph[$webpage_idx]['mainEntity'] = ['@id' => $list_id];
            }
        }
    }

    return $graph;
}
add_filter('wpseo_schema_graph', 'mazaq_schema_inject_film', 11, 2);

/**
 * Read the raw editorial film fields for a post.
 *
 * @return array{film_title:string,film_year:string,film_director:string,film_rating:string}
 */
function mazaq_schema_film_fields(int $post_id): array
{
    $get = static function (string $name) use ($post_id): string {
        if (!function_exists('get_field')) {
            return '';
        }
        return trim((string) get_field($name, $post_id));
    };

    return [
        'film_title'    => $get('film_title'),
        'film_year'     => $get('film_year'),
        'film_director' => $get('film_director'),
        'film_rating'   => $get('film_rating'),
    ];
}

/**
 * Convert Arabic-Indic and Eastern-Arabic digits to Latin digits.
 * Editors on an Arabic site routinely type ٨ / ۸ instead of 8.
 */
function mazaq_schema_latin_digits(string $value): string
{
    $map = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    return strtr($value, $map);
}

/**
 * Parse a free-text rating into a Schema.org Rating fragment.
 *
 * Accepts "8/10", "8 من 10", "8 out of 10", star glyphs (★★★★), or a bare
 * number interpreted against a default scale (10, filterable). Returns null
 * when nothing can be parsed confidently — a Review with no real rating is
 * worse than no Review at all.
 *
 * @return array{ratingValue:int|float,bestRating:int|float,worstRating:int|float}|null
 */
function mazaq_schema_parse_rating(string $raw)
{
    $value = trim(mazaq_schema_latin_digits($raw));
    if ($value === '') {
        return null;
    }

    $normalized = str_replace(',', '.', $value);
    $num        = '(\d+(?:\.\d+)?)';

    // "8/10", "8 من 10", "8 out of 10"
    if (preg_match('~' . $num . '\s*(?:/|من|out of|of)\s*' . $num . '~iu', $normalized, $m)) {
        return mazaq_schema_rating_fragment((float) $m[1], (float) $m[2]);
    }

    // Star glyphs: ★ ☆ ⭐ ✩ ✪ ✫
    $stars = preg_match_all('/[\x{2605}\x{2606}\x{2B50}\x{2729}\x{272A}\x{272B}]/u', $value);
    if ($stars > 0) {
        return mazaq_schema_rating_fragment((float) $stars, 5.0);
    }

    // Bare number against the default scale.
    if (preg_match('~^' . $num . '$~', $normalized, $m)) {
        $scale = (float) apply_filters('mazaq_schema_default_rating_scale', 10);
        $val   = (float) $m[1];
        if ($scale > 0 && $val >= 0 && $val <= $scale) {
            return mazaq_schema_rating_fragment($val, $scale);
        }
    }

    return null;
}

/**
 * Build a validated Rating fragment, casting whole numbers to int for clean output.
 *
 * @return array{ratingValue:int|float,bestRating:int|float,worstRating:int|float}|null
 */
function mazaq_schema_rating_fragment(float $value, float $best): ?array
{
    if ($best <= 0 || $value < 0 || $value > $best) {
        return null;
    }

    $cast = static fn(float $n) => (floor($n) === $n) ? (int) $n : $n;

    return [
        'ratingValue' => $cast($value),
        'bestRating'  => $cast($best),
        'worstRating' => $cast(min(1.0, $value)),
    ];
}

/**
 * Split a free-text director field into Schema.org Person nodes.
 */
function mazaq_schema_people(string $value): array
{
    if ($value === '') {
        return [];
    }

    $parts  = preg_split('~\s*[,،/]\s*~u', $value) ?: [$value];
    $people = [];
    foreach ($parts as $name) {
        $name = trim((string) $name);
        if ($name !== '') {
            $people[] = ['@type' => 'Person', 'name' => $name];
        }
    }

    return $people;
}

/**
 * Find the array index of the first graph node whose @type matches (string or list).
 */
function mazaq_schema_find_node_index(array $graph, string $type): ?int
{
    foreach ($graph as $index => $node) {
        if (!is_array($node) || !isset($node['@type'])) {
            continue;
        }
        $types = (array) $node['@type'];
        if (in_array($type, $types, true) && is_int($index)) {
            return $index;
        }
    }

    return null;
}

/**
 * Return the @id of a reference property (e.g. author, image) on a node.
 */
function mazaq_schema_node_ref(array $graph, ?int $index, string $key): string
{
    if ($index === null || !isset($graph[$index][$key])) {
        return '';
    }
    $ref = $graph[$index][$key];
    if (is_array($ref) && isset($ref['@id']) && is_string($ref['@id'])) {
        return $ref['@id'];
    }

    return '';
}

/**
 * Return a scalar property (e.g. inLanguage) from a node.
 */
function mazaq_schema_node_scalar(array $graph, ?int $index, string $key): string
{
    if ($index === null || !isset($graph[$index][$key]) || !is_string($graph[$index][$key])) {
        return '';
    }

    return $graph[$index][$key];
}

/**
 * Return the @id of the first node of a given @type, or '' if none.
 */
function mazaq_schema_first_id_of_type(array $graph, string $type): string
{
    $index = mazaq_schema_find_node_index($graph, $type);
    if ($index === null || !isset($graph[$index]['@id']) || !is_string($graph[$index]['@id'])) {
        return '';
    }

    return $graph[$index]['@id'];
}
