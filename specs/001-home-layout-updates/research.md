# Research and Decisions: Home Layout Updates

## 1. Exclude Hero Article From Grid

**Unknown:** How to reliably exclude the active hero article from the subsequent `WP_Query` in `front-page.php` without causing double database hits or duplicating logic.

- **Decision**: Extract the hero selection logic into a global utility function `mazaq_get_hero_post_id()` in `functions.php` (or an `inc/` file).
- **Rationale**: `template-parts/content/hero.php` currently houses the ID selection logic containing ACF options, sticky posts, or fallbacks. Extracting this guarantees that `front-page.php` gets the exact same ID. It can then pass this ID directly to `post__not_in` in its `WP_Query`.
- **Alternatives considered**: Setting a global variable directly inside `hero.php` and reading it later. Rejected because it relies on strict include ordering and is an anti-pattern (tight coupling). 

## 2. Ad Injection in Infinite Scroll Grid

**Unknown:** How to inject ads exactly every 8 articles when the grid relies on paged queries and index loops that reset per page.

- **Decision**: Calculate the `global_index` based on the query's current `$paged` state and loop counter (`$index`). `$global_index = (($paged - 1) * $posts_per_page) + $index;`. Inject an ad template when `$global_index % 8 === 0`.
- **Rationale**: The grid currently shows 6 posts per page. Using the global index ensures the ad appears after the true 8th, 16th, and 24th posts independently of page boundaries.
- **Alternatives considered**: Handling injection via JavaScript post-load. Rejected because it can cause Layout Shifts (CLS) and delays ad rendering. Injecting from PHP provides a solid SSR ad slot.

## 3. "Most Read This Week" Logic

**Unknown:** How to correctly count and query views for *only* the current week without overhauling the database scheme, given that `_post_views_count` increments eternally.

- **Decision**: Modify `mazaq_get_most_read_posts()` to use a `date_query` restricting results to articles published within the last 7 days (`'after' => '1 week ago'`) and adding a `meta_query` to exclude posts with exactly `'0'` views. 
- **Rationale**: Without a complex daily view logging system/custom tables, the standard definition of "Most Read This Week" in efficient WordPress themes is "articles published this week, ordered by their all-time views" (which are inherently views acquired this week). Adding a clause to hide 0-view posts fulfills the acceptance criterion.
- **Alternatives considered**: Creating a new meta key `_post_views_count_{WEEK_NUM}`. Rejected because it bloats post_meta rapidly and increases write loads significantly on every page view. Also considered WPP plugin integration, but no plugin was specified. The date_query approach is performant and standard for this constraint.
