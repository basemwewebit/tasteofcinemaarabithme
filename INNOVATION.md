# INNOVATION.md — The Radical Impact Proposal

## The one change: turn the magazine into a **Film Graph**

> Build an on-save indexing pipeline that promotes every film mentioned across the site — the `film_*` metadata on reviews **and** the H2 items inside every "top-N" listicle — into first-class **`film` taxonomy** terms, then auto-generate a browsable film page for each one that aggregates *every article that ranked or reviewed it*, with a cross-article aggregate rating.

Today the site is a **pile of articles**. Every listicle ("أفضل 10 أفلام رعب") mentions 10 films, and every review carries a director/year/rating — but that data evaporates the moment the page renders. A film named in fifteen different lists has no page, no canonical URL, no way to answer the reader's most natural question: *"what else has Mazaq written about this film?"*

This proposal makes the film — not the article — a queryable entity. It is the highest-leverage change available **because it invents almost nothing**: the extraction, parsing, and schema infrastructure already exist and are already running on every save. We are wiring three existing subsystems into a reverse index.

### Why this and not something generic

- It is **100% accretive with zero new editorial work.** Editors keep writing lists and reviews exactly as they do now; the graph builds itself from content already in the database.
- It **reuses code that already ships**: `mazaq_extract_article_headings()` (helpers.php) already parses listicle H2s for the schema `ItemList`; `mazaq_schema_parse_rating()` already normalizes `8/10`, `8 من 10`, star glyphs and Arabic-Indic digits; the `save_post` hooks that backfill reading-time and bust related-post transients are already the right injection point; SCF `film_*` fields already hold structured review metadata.
- It **compounds every existing investment**: the `schema-film.php` layer graduates from per-page `Movie`/`ItemList` to a real cross-linked entity graph with `aggregateRating` and `sameAs` — dramatically stronger rich-result eligibility. Internal linking (the single biggest editorial-SEO lever for a magazine) explodes from ~0 to N×M automatically.
- It **directly serves the PRODUCT.md thesis** — "curation beats volume," "turn film discovery into a curated editorial experience." A film graph *is* curation, extracted.

---

## Architectural blueprint

Three layers, each thin, each built on something that already exists.

```
                 save_post (already hooked for reading-time + transients)
                          │
                          ▼
   ┌─────────────────────────────────────────────────────────────┐
   │  LAYER 1 — Extraction (reuse, don't rewrite)                  │
   │  • mazaq_extract_article_headings($id)  → H2 film titles      │  ← EXISTS
   │  • get_field('film_title', $id)         → review subject      │  ← EXISTS
   │  → normalize each title to a slug (canonical key)             │
   └─────────────────────────────────────────────────────────────┘
                          │  wp_set_object_terms($id, $slugs, 'film')
                          ▼
   ┌─────────────────────────────────────────────────────────────┐
   │  LAYER 2 — `film` taxonomy (native WP, ~15 lines to register) │
   │  • Each film = one term = one permalink (/film/parasite/)      │
   │  • The reverse index is FREE: WP already stores term→posts     │
   │  • Aggregate rating cached in term meta on save                │
   └─────────────────────────────────────────────────────────────┘
                          │
              ┌───────────┴────────────┐
              ▼                        ▼
   ┌────────────────────┐   ┌──────────────────────────────────┐
   │ LAYER 3a — taxonomy │   │ LAYER 3b — schema-film.php uplift │
   │ template            │   │ Movie node gains aggregateRating  │
   │ taxonomy-film.php:  │   │ (avg across all reviewing posts)  │
   │ "every article that │   │ + sameAs the film's canonical URL │
   │  mentions this film"│   │ → far stronger rich results       │
   └────────────────────┘   └──────────────────────────────────┘
```

**Why a taxonomy and not a CPT:** WordPress taxonomies give the reverse index (term → all posts) for free, with built-in archive routing (`/film/{slug}/`), term meta for the cached aggregate rating, and zero custom query code. A CPT would force us to hand-build the article↔film relationship table. The lazy, correct choice is the native one.

---

## Conceptual implementation

A single new module, `inc/film-graph.php`, added to the `$mazaq_includes` manifest. ~70 lines of real logic — everything else is reused.

```php
<?php
declare(strict_types=1);

/**
 * Film Graph — promotes films named in reviews and listicles into a
 * queryable `film` taxonomy, building a reverse index (film → all articles)
 * from data editors already enter. Reuses mazaq_extract_article_headings()
 * and mazaq_schema_parse_rating(); indexes on the existing save_post hook.
 */

// ── Layer 2: the taxonomy (native WP does the reverse index for us) ──
add_action('init', static function (): void {
    register_taxonomy('film', ['post'], [
        'label'             => __('الأفلام', 'mazaq'),
        'public'            => true,
        'hierarchical'      => false,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'film'],
        'show_admin_column' => true,
    ]);
});

// ── Layer 1: extraction — runs on the hook that's ALREADY firing ──
add_action('save_post', 'mazaq_film_graph_index', 20, 2); // after reading-time (prio 10)

function mazaq_film_graph_index(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || $post->post_type !== 'post'
        || $post->post_status !== 'publish') {
        return;
    }

    $titles = [];

    // (a) Review subject — the SCF field editors already fill in.
    if (function_exists('get_field')) {
        $t = trim((string) get_field('film_title', $post_id));
        if ($t !== '') {
            $titles[] = $t;
        }
    }

    // (b) Listicle entries — REUSE the exact parser schema-film.php uses.
    if (function_exists('mazaq_extract_article_headings')) {
        foreach (mazaq_extract_article_headings($post_id) as $h) {
            if ((int) ($h['level'] ?? 0) === 2) {
                $titles[] = mazaq_film_title_from_heading((string) $h['text']);
            }
        }
    }

    // Normalize → dedupe → assign. WP creates missing terms automatically.
    $slugs = array_values(array_unique(array_filter(array_map(
        'mazaq_film_slug', $titles
    ))));
    wp_set_object_terms($post_id, $slugs, 'film', false);

    // Refresh each touched film's cached aggregate rating.
    foreach ($slugs as $slug) {
        mazaq_film_recompute_rating($slug);
    }
}

/** "1. Parasite (2019)" → "Parasite" — strip rank number + trailing year. */
function mazaq_film_title_from_heading(string $text): string
{
    $text = preg_replace('/^\s*\d+[\.\)\-–—:]\s*/u', '', $text);   // leading "7. "
    $text = preg_replace('/\s*\(?\b\d{4}\b\)?\s*$/u', '', $text);  // trailing "(2019)"
    return trim($text);
}

/** Latin + Arabic-safe slug. Reuses sanitize_title for non-ASCII. */
function mazaq_film_slug(string $title): string
{
    $title = trim($title);
    return $title === '' ? '' : sanitize_title($title);
}

// ── Layer 3b: aggregate rating across every reviewing post ──
function mazaq_film_recompute_rating(string $slug): void
{
    $term = get_term_by('slug', $slug, 'film');
    if (!$term instanceof WP_Term || !function_exists('mazaq_schema_parse_rating')) {
        return;
    }

    $post_ids = get_posts([
        'post_type' => 'post', 'fields' => 'ids', 'nopaging' => true,
        'tax_query' => [['taxonomy' => 'film', 'field' => 'slug', 'terms' => $slug]],
    ]);

    $values = [];
    foreach ($post_ids as $pid) {
        $raw = (string) get_field('film_rating', $pid);        // reviews only
        $r   = mazaq_schema_parse_rating($raw);                // REUSED parser
        if ($r !== null && (float) $r['bestRating'] > 0) {
            $values[] = ((float) $r['ratingValue'] / (float) $r['bestRating']) * 10;
        }
    }

    if ($values !== []) {
        update_term_meta($term->term_id, '_mazaq_agg_rating',
            round(array_sum($values) / count($values), 1));   // normalized /10
        update_term_meta($term->term_id, '_mazaq_agg_count', count($values));
    }
}
```

**Layer 3a** is then a `taxonomy-film.php` template — a film page that lists every article assigned to the term (WordPress routes and queries this for free), headed by the cached aggregate rating rendered with the existing `mazaq_film_rating_stars()` display helper.

**Layer 3b** is a ~6-line addition inside the existing `schema-film.php` Movie node:

```php
// Inside mazaq_schema_inject_film(), when building $movie:
$film_term = /* the film term assigned to this post */;
$agg = (float) get_term_meta($film_term->term_id, '_mazaq_agg_rating', true);
if ($agg > 0) {
    $movie['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => $agg,
        'bestRating'  => 10,
        'ratingCount' => (int) get_term_meta($film_term->term_id, '_mazaq_agg_count', true),
    ];
    $movie['url'] = get_term_link($film_term);  // canonical film entity URL
}
```

---

## What it multiplies

| Lever | Before | After |
|---|---|---|
| **Internal linking** | Manual, sparse | Automatic N×M — every list links to every film page, every film page links back to every article |
| **Reader discovery** | "read next" by category only | "every article Mazaq wrote about *this film*" — a page that didn't exist |
| **SEO / rich results** | Per-page `Movie`/`ItemList` | Cross-article `AggregateRating` + canonical film entities with `ratingCount` |
| **Editorial insight** | None | Admin can see which films are most-covered / highest-rated across the whole magazine |
| **Editorial cost** | — | **Zero.** The graph is derived, not authored |

### Rollout & safety notes
- **Backfill:** one WP-CLI/admin one-shot that runs `mazaq_film_graph_index()` over all published posts seeds the graph from existing content — no re-editing.
- **Heading noise:** listicle H2s aren't always clean film titles (some are section headers). Ship `mazaq_film_title_from_heading()` as the first heuristic and gate term-creation behind an editor-review queue (reuse the existing dashboard-widget pattern from `admin-toc-missing-widget.php`) before terms go public — so a garbage H2 never spawns a public `/film/` page unreviewed. *(ponytail: start with auto-assign on review-subject SCF fields only, which are always clean; add the heading-mining queue in phase 2 once the taxonomy is proven.)*
- **Cost:** indexing runs on `save_post` (already firing) and aggregate-rating recompute is O(reviews-of-one-film), trivial. No front-end request pays anything — film pages are native taxonomy queries.

This is the change that converts Mazaq Cinema from *software that displays articles* into *a film-knowledge platform that happens to publish articles* — using code it already runs.
