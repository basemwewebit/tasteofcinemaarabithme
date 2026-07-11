<?php

/**
 * Film Graph — promotes every film named across the site into a queryable
 * `film` taxonomy term, building a reverse index (film → every article that
 * ranks or reviews it) plus a cached cross-article aggregate rating.
 *
 * Two sources, both data editors already produce:
 *   (a) the clean `film_title` SCF field on reviews (empty today, ready for it);
 *   (b) the H2 entries of every ranked listicle — the site's actual film data.
 *
 * Heading mining is gated by the same "≥3 H2 items = a ranked list" threshold
 * schema-film.php uses, plus a filterable skip-list (mazaq_film_heading_skiplist)
 * and a length guard, so ordinary articles and section headers never spawn terms.
 * ponytail: no editor-review queue yet — the skip-list + list threshold are the
 * phase-1 noise guard. Add a review queue if junk terms still slip through.
 *
 * Reuses mazaq_schema_parse_rating() (inc/schema-film.php) for rating parsing so
 * the aggregate can never diverge from per-review schema ratings, and
 * mazaq_extract_article_headings() for the exact H2 list schema-film.php reads.
 * Indexes on the save_post hook that already fires for reading-time and transients.
 *
 * @package Mazaq
 */

declare(strict_types=1);

// ── Layer 2: the taxonomy — WP gives us the reverse index for free ──
add_action('init', static function (): void {
    register_taxonomy('film', ['post'], [
        'labels' => [
            'name'          => __('الأفلام', 'mazaq'),
            'singular_name' => __('فيلم', 'mazaq'),
        ],
        'public'            => true,
        'hierarchical'      => false,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'film'],
        'show_admin_column' => true,
    ]);
});

// ── Layer 1: extraction — runs after reading-time (prio 10) on the same hook ──
add_action('save_post', 'mazaq_film_graph_index', 20, 2);

/**
 * Assign a post's films as `film` terms and refresh affected aggregates.
 *
 * Recomputes both the newly-assigned terms and any term the post previously
 * carried, so editing content or unpublishing leaves no stale aggregate behind.
 */
function mazaq_film_graph_index(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || $post->post_type !== 'post') {
        return;
    }

    // Term IDs this post used before this save — so we can recompute them too.
    $prev = wp_get_object_terms($post_id, 'film', ['fields' => 'ids']);
    $prev = is_wp_error($prev) ? [] : array_map('intval', $prev);

    $titles = [];
    if ($post->post_status === 'publish') {
        // (a) Clean review subject — empty on listicles, ready for future reviews.
        if (function_exists('get_field')) {
            $t = trim((string) get_field('film_title', $post_id));
            if ($t !== '') {
                $titles[] = $t;
            }
        }
        // (b) Listicle H2 entries — the site's actual film data.
        $titles = array_merge($titles, mazaq_film_titles_from_headings($post_id));
    }

    // Resolve to term IDs ourselves. Passing IDs (never slug strings) to
    // wp_set_object_terms is essential: a purely-numeric slug like "1917"
    // (from "1917 (2019)") would otherwise be read as a term_id, not a name.
    $term_ids = [];
    foreach ($titles as $title) {
        if (mazaq_film_is_skipped($title)) {
            continue;
        }
        $tid = mazaq_film_get_or_create_term($title);
        if ($tid > 0) {
            $term_ids[] = $tid;
        }
    }
    $term_ids = array_values(array_unique($term_ids));

    // An empty array clears the post's films (drafts/trash/no film entries).
    wp_set_object_terms($post_id, $term_ids, 'film', false);

    // ponytail: one recompute query per affected film, synchronous on save. O(few)
    // for a listicle; if a post ever carries dozens of films, move to wp-cron.
    foreach (array_unique(array_merge($prev, $term_ids)) as $tid) {
        mazaq_film_recompute_rating((int) $tid);
    }
}

/**
 * Get-or-create a `film` term for a title, returning its term_id (0 on failure).
 *
 * Dedupes by slug so title variants collapse to one term, and stores the human
 * title as the term name so /film/ archives show "Le Samouraï", not the slug.
 */
function mazaq_film_get_or_create_term(string $title): int
{
    $title = trim($title);
    $slug  = sanitize_title($title);
    if ($slug === '') {
        return 0;
    }

    $existing = get_term_by('slug', $slug, 'film');
    if ($existing instanceof WP_Term) {
        return (int) $existing->term_id;
    }

    $result = wp_insert_term($title, 'film', ['slug' => $slug]);
    if (is_wp_error($result)) {
        // Slug clash / race: re-read rather than lose the assignment.
        $again = get_term_by('slug', $slug, 'film');
        return $again instanceof WP_Term ? (int) $again->term_id : 0;
    }

    return (int) ($result['term_id'] ?? 0);
}

/**
 * Film titles mined from a post's H2 headings — but only when the post is a
 * ranked list (≥3 H2 items, the same threshold schema-film.php uses to decide a
 * post is an ItemList). Ordinary articles with a few subheadings return nothing.
 *
 * @return string[] Parsed titles, before skip-list filtering.
 */
function mazaq_film_titles_from_headings(int $post_id): array
{
    if (!function_exists('mazaq_extract_article_headings')) {
        return [];
    }

    $h2 = array_values(array_filter(
        mazaq_extract_article_headings($post_id),
        static fn(array $h): bool => (int) ($h['level'] ?? 0) === 2
    ));
    if (count($h2) < 3) {
        return [];
    }

    $titles = [];
    foreach ($h2 as $h) {
        $title = mazaq_film_title_from_heading((string) ($h['text'] ?? ''));
        if ($title !== '') {
            $titles[] = $title;
        }
    }

    return $titles;
}

/**
 * "1. Parasite (2019)" → "Parasite" — strip a leading rank and a trailing year.
 *
 * Returns '' unless the entry carries a parenthesized release year "(YYYY)".
 * That paren-year is the reliable discriminator between a real film-list entry
 * and prose: "10 reasons/facts" listicles number their sentence H2s exactly like
 * film lists ("1. …"), so a leading rank proves nothing — but only actual films
 * are tagged "(2019)". A bare trailing year ("… عام 2023") is not enough.
 */
function mazaq_film_title_from_heading(string $text): string
{
    $text = trim($text);
    $text = (string) preg_replace('/^\s*\d+\s*[\.\)\-–—:]\s*/u', '', $text); // "7. "

    if (!preg_match('/\(\s*\d{4}\s*\)\s*$/u', $text)) {
        return '';
    }
    $text = (string) preg_replace('/\s*\(\s*\d{4}\s*\)\s*$/u', '', $text);   // "(2019)"

    return trim($text);
}

/**
 * True when a parsed heading is not a film title: a known section header
 * (filterable skip-list) or too long to be a title. Keeps garbage H2s from
 * spawning public /film/ pages without a full editor-review queue.
 */
function mazaq_film_is_skipped(string $title): bool
{
    $title = trim($title);
    if ($title === '') {
        return true;
    }

    // Must contain a real letter — drops divider H2s like "—", "***", "---",
    // which sanitize_title() would otherwise turn into numeric-slug junk terms.
    if (!preg_match('/\p{L}/u', $title)) {
        return true;
    }

    // A film title is not a whole sentence; guard against prose/section H2s.
    $len = function_exists('mb_strlen') ? mb_strlen($title) : strlen($title);
    if ($len > 80) {
        return true;
    }

    $lower = function_exists('mb_strtolower') ? mb_strtolower($title) : strtolower($title);
    $skip  = (array) apply_filters('mazaq_film_heading_skiplist', [
        'مقدمة', 'خاتمة', 'الخاتمة', 'خلاصة', 'الخلاصة', 'ملاحظة', 'ملاحظات',
        'تنويه مشرف', 'تنويهات مشرفة', 'إشارات مشرفة', 'أفلام أخرى',
        'introduction', 'conclusion', 'summary', 'honorable mentions',
    ]);
    $skip = array_map(
        static fn($s) => function_exists('mb_strtolower') ? mb_strtolower((string) $s) : strtolower((string) $s),
        $skip
    );

    return in_array($lower, $skip, true);
}

/**
 * Recompute one film's cached aggregate rating across every publish review of it.
 *
 * Stores a /10-normalized average in term meta. Deletes the meta when no reviews
 * carry a parseable rating, so a stale figure never survives.
 */
function mazaq_film_recompute_rating(int $term_id): void
{
    $term = get_term($term_id, 'film');
    if (!$term instanceof WP_Term) {
        return;
    }

    $agg = null;
    if (function_exists('get_field') && function_exists('mazaq_schema_parse_rating')) {
        $post_ids = get_posts([
            'post_type'   => 'post',
            'post_status' => 'publish',
            'fields'      => 'ids',
            'nopaging'    => true,
            'tax_query'   => [[
                'taxonomy' => 'film',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ]],
        ]);

        $fragments = [];
        foreach ($post_ids as $pid) {
            $fragment = mazaq_schema_parse_rating((string) get_field('film_rating', $pid));
            if ($fragment !== null) {
                $fragments[] = $fragment;
            }
        }
        $agg = mazaq_film_average_normalized($fragments);
    }

    if ($agg === null) {
        delete_term_meta($term->term_id, '_mazaq_agg_rating');
        delete_term_meta($term->term_id, '_mazaq_agg_count');
        return;
    }

    update_term_meta($term->term_id, '_mazaq_agg_rating', $agg['rating']);
    update_term_meta($term->term_id, '_mazaq_agg_count', $agg['count']);
}

/**
 * Average a set of Rating fragments onto a /10 scale. Pure — no WP calls — so it
 * is unit-testable (see tests/film-graph-test.php).
 *
 * @param array<int,array{ratingValue:int|float,bestRating:int|float}> $fragments
 * @return array{rating:float,count:int}|null Null when nothing has a positive scale.
 */
function mazaq_film_average_normalized(array $fragments): ?array
{
    $values = [];
    foreach ($fragments as $r) {
        $best = (float) ($r['bestRating'] ?? 0);
        if ($best > 0) {
            $values[] = ((float) $r['ratingValue'] / $best) * 10;
        }
    }

    if ($values === []) {
        return null;
    }

    return [
        'rating' => round(array_sum($values) / count($values), 1),
        'count'  => count($values),
    ];
}

/**
 * The film term that is a post's review *subject*, or null.
 *
 * Matches the `film_title` term specifically: a review may also carry
 * heading-mined terms, but the Movie schema node is about the reviewed film, so
 * an arbitrary "first term" would risk aggregating the wrong one.
 */
function mazaq_film_term_for_post(int $post_id): ?WP_Term
{
    if (!function_exists('get_field')) {
        return null;
    }
    $slug = sanitize_title(trim((string) get_field('film_title', $post_id)));
    if ($slug === '') {
        return null;
    }
    $term = get_term_by('slug', $slug, 'film');

    return $term instanceof WP_Term ? $term : null;
}

/**
 * A film's cached aggregate rating, or null. One accessor for the twin metas so
 * read sites don't repeat the meta-key strings.
 *
 * @return array{rating:float,count:int}|null
 */
function mazaq_film_agg(int $term_id): ?array
{
    $rating = (float) get_term_meta($term_id, '_mazaq_agg_rating', true);
    $count  = (int) get_term_meta($term_id, '_mazaq_agg_count', true);

    return ($rating > 0 && $count > 0) ? ['rating' => $rating, 'count' => $count] : null;
}

// ── Layer 3b: uplift the schema Movie node with the cross-article aggregate ──
add_filter('mazaq_schema_movie_data', 'mazaq_film_graph_schema_uplift', 10, 2);

/**
 * Add aggregateRating + a sameAs link to the film-entity page on the Movie node.
 * sameAs is additive — the Movie's `url` stays the reviewing article's canonical.
 * No-op when the post has no review-subject film term or no cached rating.
 */
function mazaq_film_graph_schema_uplift(array $movie, int $post_id): array
{
    $term = mazaq_film_term_for_post($post_id);
    if ($term === null) {
        return $movie;
    }

    $link = get_term_link($term);
    if (!is_wp_error($link)) {
        $movie['sameAs'] = $link;  // canonical film-entity URL, alongside `url`
    }

    $agg = mazaq_film_agg((int) $term->term_id);
    if ($agg !== null) {
        $movie['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => $agg['rating'],
            'bestRating'  => 10,
            'worstRating' => 1,
            'ratingCount' => $agg['count'],
        ];
    }

    return $movie;
}

// ── Backfill: seed the graph from existing content in one pass ──
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('mazaq film-graph backfill', static function (): void {
        $ids = get_posts([
            'post_type'   => 'post',
            'post_status' => 'publish',
            'fields'      => 'ids',
            'nopaging'    => true,
        ]);

        // Defer term counting so the bulk run ends with one accurate recount
        // instead of thousands of interleaved updates (which leave counts stale).
        wp_defer_term_counting(true);
        foreach ($ids as $id) {
            $post = get_post($id);
            if ($post instanceof WP_Post) {
                mazaq_film_graph_index((int) $id, $post);
            }
        }
        wp_defer_term_counting(false);

        WP_CLI::success(sprintf('Indexed %d posts into the film graph.', count($ids)));
    });
}
