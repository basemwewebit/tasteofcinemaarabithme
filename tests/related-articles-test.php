<?php

/**
 * Standalone self-check for Related articles: the inline/closing split, the
 * id cache and its clearing on save, and dropping no-longer-published
 * articles. No framework, no WP: run with `php tests/related-articles-test.php`.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

const HOUR_IN_SECONDS = 3600;

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

// --- Fake site: each post's categories, status and password ------------------
$GLOBALS['fake_posts'] = [];      // id => ['cats' => int[], 'status' => string, 'password' => bool]
$GLOBALS['fake_query_count'] = 0;
$GLOBALS['fake_actions'] = [];

/** Add posts newest first; each shares category 1 unless told otherwise. */
function fake_post(int $id, array $cats = [1], string $status = 'publish', bool $password = false): void
{
    $GLOBALS['fake_posts'][$id] = ['cats' => $cats, 'status' => $status, 'password' => $password];
}

function get_the_category($post_id): array
{
    return array_map(static fn(int $c) => (object) ['term_id' => $c], $GLOBALS['fake_posts'][$post_id]['cats'] ?? []);
}

function post_password_required($post_id): bool
{
    return $GLOBALS['fake_posts'][$post_id]['password'] ?? false;
}

function wp_is_post_revision($post_id)
{
    return false;
}

function add_action(string $hook, callable $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['fake_actions'][$hook][] = $callback;
}

/** Ids of published posts in any of the categories, newest (insertion) first. */
class WP_Query
{
    /** @var int[] */
    public $posts = [];

    public function __construct(array $args = [])
    {
        $GLOBALS['fake_query_count']++;
        foreach ($GLOBALS['fake_posts'] as $id => $post) {
            if ($post['status'] === 'publish'
                && !in_array($id, $args['post__not_in'], true)
                && array_intersect($post['cats'], $args['category__in'])) {
                $this->posts[] = $id;
            }
        }
        $this->posts = array_slice($this->posts, 0, $args['posts_per_page']);
    }
}

/** Published posts among post__in, in post__in order. */
function get_posts(array $args): array
{
    $found = [];
    foreach ($args['post__in'] as $id) {
        if (($GLOBALS['fake_posts'][$id]['status'] ?? '') === 'publish') {
            $found[] = (object) ['ID' => $id];
        }
    }

    return $found;
}

require __DIR__ . '/../inc/related-articles.php';

function reset_site(): void
{
    $GLOBALS['fake_posts'] = [];
    $GLOBALS['fake_transients'] = [];
    $GLOBALS['fake_query_count'] = 0;
}

/** @return array{inline: int[], bottom: int[]} */
function related_ids(int $post_id): array
{
    $related = mazaq_related_articles($post_id);

    return [
        'inline' => array_map(static fn($p) => $p->ID, $related['inline']),
        'bottom' => array_map(static fn($p) => $p->ID, $related['bottom']),
    ];
}

// No category: nothing.
reset_site();
fake_post(1, []);
fake_post(2);
rotation_test_check(related_ids(1) === ['inline' => [], 'bottom' => []], 'no category: no related articles');

// Five or more: two inline, the next three closing, newest first, never itself.
reset_site();
foreach ([1, 10, 11, 12, 13, 14, 15] as $id) {
    fake_post($id);
}
rotation_test_check(related_ids(1) === ['inline' => [10, 11], 'bottom' => [12, 13, 14]], '2 inline + 3 closing');

// Only one: closing grid only.
reset_site();
fake_post(1);
fake_post(10);
rotation_test_check(related_ids(1) === ['inline' => [], 'bottom' => [10]], 'one related: closing only');

// Password-protected: no Inline related block, up to three closing.
reset_site();
fake_post(1, [1], 'publish', true);
foreach ([10, 11, 12, 13, 14] as $id) {
    fake_post($id);
}
rotation_test_check(related_ids(1) === ['inline' => [], 'bottom' => [10, 11, 12]], 'password-protected: closing only');

// Cache: a second read doesn't query.
reset_site();
foreach ([1, 10, 11] as $id) {
    fake_post($id);
}
related_ids(1);
related_ids(1);
rotation_test_check($GLOBALS['fake_query_count'] === 1, 'cache hit skips the query');

// A cached article since unpublished drops out on the next read.
$GLOBALS['fake_posts'][10]['status'] = 'trash';
rotation_test_check(related_ids(1) === ['inline' => [], 'bottom' => [11]], 'unpublished article dropped from cache');

// Saving the post clears its entry: a category change shows on the next read.
fake_post(20, [2]);
$GLOBALS['fake_posts'][1]['cats'] = [2];
foreach ($GLOBALS['fake_actions']['save_post'] ?? [] as $callback) {
    $callback(1);
}
rotation_test_check(related_ids(1) === ['inline' => [], 'bottom' => [20]], 'save clears the entry');
rotation_test_check($GLOBALS['fake_query_count'] === 2, 'save forces one fresh query');

echo 'related-articles-test: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " checks passed\n";
