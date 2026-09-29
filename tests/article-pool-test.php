<?php

/**
 * Standalone self-check for the Article pool: paged scan, full-pool cache and
 * its clearing hooks, category narrowing, and the random pick. No framework,
 * no WP: run with `php tests/article-pool-test.php`.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

const MINUTE_IN_SECONDS = 60;

// --- Fake transient store ----------------------------------------------------
$GLOBALS['fake_transients'] = [];

function get_transient($key)
{
    return $GLOBALS['fake_transients'][$key] ?? false;
}

function set_transient($key, $value, $expiration = 0): bool
{
    $GLOBALS['fake_transients'][$key] = $value;

    return true;
}

function delete_transient($key): bool
{
    unset($GLOBALS['fake_transients'][$key]);

    return true;
}

// --- Fake WP_Query: serves pages from the fake pool, or a category's pool ----
$GLOBALS['fake_published_pool'] = [];
$GLOBALS['fake_category_pools'] = [];
$GLOBALS['fake_query_count'] = 0;
$GLOBALS['fake_last_query_args'] = [];

class WP_Query
{
    /** @var int[] */
    public $posts = [];

    public function __construct(array $args = [])
    {
        $GLOBALS['fake_query_count']++;
        $GLOBALS['fake_last_query_args'] = $args;

        $pool = isset($args['cat'])
            ? ($GLOBALS['fake_category_pools'][(int) $args['cat']] ?? [])
            : $GLOBALS['fake_published_pool'];
        sort($pool);
        $per_page = (int) ($args['posts_per_page'] ?? 200);
        $page = max(1, (int) ($args['paged'] ?? 1));

        $this->posts = array_slice($pool, ($page - 1) * $per_page, $per_page);
    }
}

// --- Fake terms: a category exists when it has a pool entry -----------------
function get_term($term_id, $taxonomy = '')
{
    if (!array_key_exists((int) $term_id, $GLOBALS['fake_category_pools'])) {
        return null;
    }

    return (object) ['term_id' => (int) $term_id, 'count' => count($GLOBALS['fake_category_pools'][(int) $term_id])];
}

function is_wp_error($thing): bool
{
    return false;
}

function wp_rand($min = 0, $max = 0): int
{
    return mt_rand((int) $min, (int) $max);
}

// --- Fake posts and hook registry -------------------------------------------
class WP_Post
{
    public $post_type;

    public function __construct(string $post_type)
    {
        $this->post_type = $post_type;
    }
}

$GLOBALS['fake_post_types'] = [];
$GLOBALS['fake_revisions'] = [];

function get_post_type($post_id)
{
    return $GLOBALS['fake_post_types'][(int) $post_id] ?? false;
}

function wp_is_post_revision($post_id)
{
    return in_array((int) $post_id, $GLOBALS['fake_revisions'], true);
}

$GLOBALS['fake_autosaves'] = [];

function wp_is_post_autosave($post_id)
{
    return in_array((int) $post_id, $GLOBALS['fake_autosaves'], true);
}

$GLOBALS['fake_hooks'] = [];

function add_action($hook, $callback, $priority = 10, $args = 1): void
{
    $GLOBALS['fake_hooks'][$hook][] = $callback;
}

function fire_hook(string $hook, ...$args): void
{
    foreach ($GLOBALS['fake_hooks'][$hook] ?? [] as $callback) {
        $callback(...$args);
    }
}

function reset_pool_fakes(array $pool = [], array $category_pools = []): void
{
    $GLOBALS['fake_transients'] = [];
    $GLOBALS['fake_published_pool'] = $pool;
    $GLOBALS['fake_category_pools'] = $category_pools;
    $GLOBALS['fake_query_count'] = 0;
}

require __DIR__ . '/../inc/article-pool.php';

// Scan: pages the whole pool, ints, unique, stable order args.
reset_pool_fakes(range(1, 450));
$ids = mazaq_article_pool_ids();
rotation_test_check($ids === range(1, 450), 'pool pages all 450 ids in order');
rotation_test_check($GLOBALS['fake_query_count'] === 3, 'pool runs one short final page');
rotation_test_check($GLOBALS['fake_last_query_args']['posts_per_page'] === 200, 'pool pages 200 at a time');
rotation_test_check($GLOBALS['fake_last_query_args']['orderby'] === 'ID', 'pool orders by id ascending');
rotation_test_check($GLOBALS['fake_last_query_args']['fields'] === 'ids', 'pool fetches ids only');
rotation_test_check($GLOBALS['fake_last_query_args']['post_status'] === 'publish', 'pool reads published posts');
rotation_test_check($GLOBALS['fake_last_query_args']['post_type'] === 'post', 'pool reads posts only');

// Cache: a second full-pool read costs no queries.
reset_pool_fakes([1, 2, 3]);
mazaq_article_pool_ids();
$first_read_queries = $GLOBALS['fake_query_count'];
rotation_test_check(mazaq_article_pool_ids() === [1, 2, 3], 'cached pool serves the same ids');
rotation_test_check($GLOBALS['fake_query_count'] === $first_read_queries, 'second read is served from the cache');

// Category: narrowed to one list, always fresh, never cached.
reset_pool_fakes([1, 2, 3, 4], [7 => [2, 4]]);
rotation_test_check(mazaq_article_pool_ids(7) === [2, 4], 'category pool holds only that list');
rotation_test_check($GLOBALS['fake_last_query_args']['cat'] === 7, 'category passes through to the query');
mazaq_article_pool_ids(7);
rotation_test_check($GLOBALS['fake_query_count'] === 2, 'category reads are never cached');
rotation_test_check($GLOBALS['fake_transients'] === [], 'category reads never write the cache');

// Category: unknown or empty lists yield an empty pool without querying.
reset_pool_fakes([1, 2], [8 => []]);
rotation_test_check(mazaq_article_pool_ids(99) === [], 'unknown category yields an empty pool');
rotation_test_check(mazaq_article_pool_ids(8) === [], 'empty category yields an empty pool');
rotation_test_check($GLOBALS['fake_query_count'] === 0, 'unknown and empty categories never query');

// Clearing: post lifecycle changes force a fresh scan on the next read.
$GLOBALS['fake_post_types'] = [5 => 'post', 6 => 'page'];
$GLOBALS['fake_revisions'] = [9];
$GLOBALS['fake_autosaves'] = [10];
$warm_pool = function (): void {
    reset_pool_fakes([1, 2, 3]);
    mazaq_article_pool_ids();
};

$warm_pool();
fire_hook('save_post', 5, new WP_Post('post'));
rotation_test_check($GLOBALS['fake_transients'] === [], 'saving a post clears the pool cache');
$warm_pool();
fire_hook('deleted_post', 5);
rotation_test_check($GLOBALS['fake_transients'] === [], 'deleting a post clears the pool cache');
$warm_pool();
fire_hook('transition_post_status', 'draft', 'publish', new WP_Post('post'));
rotation_test_check($GLOBALS['fake_transients'] === [], 'unpublishing a post clears the pool cache');

$warm_pool();
fire_hook('save_post', 9, new WP_Post('post'));
rotation_test_check($GLOBALS['fake_transients'] !== [], 'saving a revision keeps the pool cache');
fire_hook('save_post', 10, new WP_Post('post'));
rotation_test_check($GLOBALS['fake_transients'] !== [], 'saving an autosave keeps the pool cache');
fire_hook('save_post', 6, new WP_Post('page'));
fire_hook('deleted_post', 6);
fire_hook('transition_post_status', 'draft', 'publish', new WP_Post('page'));
rotation_test_check($GLOBALS['fake_transients'] !== [], 'page changes keep the pool cache');
fire_hook('transition_post_status', 'publish', 'publish', new WP_Post('post'));
rotation_test_check($GLOBALS['fake_transients'] !== [], 'an unchanged status keeps the pool cache');

$GLOBALS['fake_published_pool'] = [1, 2, 3, 4];
fire_hook('save_post', 5, new WP_Post('post'));
rotation_test_check(mazaq_article_pool_ids() === [1, 2, 3, 4], 'read after clearing sees the new article');

// Random pick: empty pool yields 0.
reset_pool_fakes([]);
rotation_test_check(mazaq_article_pool_random_id() === 0, 'empty pool picks nothing');
rotation_test_check(mazaq_article_pool_random_id([], 99) === 0, 'unknown category picks nothing');

// Random pick: never an excluded id while others remain.
reset_pool_fakes([1, 2, 3]);
for ($i = 0; $i < 50; $i++) {
    rotation_test_check(mazaq_article_pool_random_id([1, 3]) === 2, 'pick skips excluded ids');
}

// Random pick: every id excluded falls back to the whole pool.
for ($i = 0; $i < 50; $i++) {
    rotation_test_check(in_array(mazaq_article_pool_random_id([1, 2, 3]), [1, 2, 3], true), 'fully excluded pick falls back to the pool');
}

// Random pick: category-scoped picks stay inside the list.
reset_pool_fakes([1, 2, 3, 4], [7 => [2, 4]]);
for ($i = 0; $i < 50; $i++) {
    rotation_test_check(in_array(mazaq_article_pool_random_id([], 7), [2, 4], true), 'category pick stays in its list');
}
rotation_test_check(mazaq_article_pool_random_id([2], 7) === 4, 'category pick honors exclusions');

echo "article-pool-test: {$GLOBALS['rotation_test_passed']} checks passed\n";
