<?php

/**
 * Standalone self-check for the Continuous Programme resolver: pin, rotation
 * batch, latest-articles gap fill. No framework, no WP: run with
 * `php tests/continuous-programme-test.php`.
 *
 * Stubs the editor pin (get_field), the rotation reader, the post store, and
 * get_posts, then drives mazaq_programme_post_ids() the way templates do.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

// --- Fake editor pin ---------------------------------------------------------
$GLOBALS['fake_pin'] = 0;

function get_field($name, $post_id = false)
{
    return $name === 'hero_featured_post' && $post_id === 'option' ? $GLOBALS['fake_pin'] : null;
}

// --- Fake Content rotation reader --------------------------------------------
$GLOBALS['fake_batch'] = [];

function mazaq_hero_daily_config(): array
{
    return ['batch_key' => 'hero_post_ids'];
}

function mazaq_rotation_get_today_batch_ids(array $feature): array
{
    return $GLOBALS['fake_batch'];
}

// --- Fake post store: id => [status, type]; latest = newest-first ids ---------
$GLOBALS['fake_posts'] = [];
$GLOBALS['fake_latest'] = [];

function get_post_status($post_id)
{
    return $GLOBALS['fake_posts'][(int) $post_id][0] ?? false;
}

function get_post_type($post_id)
{
    return $GLOBALS['fake_posts'][(int) $post_id][1] ?? false;
}

function get_posts(array $args): array
{
    $excluded = array_map('intval', $args['post__not_in'] ?? []);
    $ids = array_values(array_diff($GLOBALS['fake_latest'], $excluded));

    return array_slice($ids, 0, (int) $args['posts_per_page']);
}

require __DIR__ . '/../inc/continuous-programme.php';

/** Seed ids 1..20 as published posts; newest first is 20, 19, 18 … */
function programme_test_reset(): void
{
    $GLOBALS['fake_pin'] = 0;
    $GLOBALS['fake_batch'] = [];
    $GLOBALS['fake_posts'] = [];
    foreach (range(1, 20) as $id) {
        $GLOBALS['fake_posts'][$id] = ['publish', 'post'];
    }
    $GLOBALS['fake_latest'] = range(20, 1);
}

// Rotation alone deals the whole Programme.
programme_test_reset();
$GLOBALS['fake_batch'] = [5, 6, 7, 8];
rotation_test_check(mazaq_programme_post_ids() === [5, 6, 7, 8], 'rotation batch fills all four');

// A short batch is topped up from the latest articles, not repeated.
programme_test_reset();
$GLOBALS['fake_batch'] = [5, 20, 7];
rotation_test_check(mazaq_programme_post_ids() === [5, 20, 7, 19], 'short batch gap filled from latest, skipping chosen');

// The pin leads; the batch follows without the pinned article; capped at four.
programme_test_reset();
$GLOBALS['fake_pin'] = 6;
$GLOBALS['fake_batch'] = [5, 6, 7, 8];
rotation_test_check(mazaq_programme_post_ids() === [6, 5, 7, 8], 'pin leads, batch fills the slides');

// A pin outside a full batch displaces the batch's last article.
programme_test_reset();
$GLOBALS['fake_pin'] = 12;
$GLOBALS['fake_batch'] = [5, 6, 7, 8];
rotation_test_check(mazaq_programme_post_ids() === [12, 5, 6, 7], 'pin outside batch displaces the last batch article');

// Unpublished or non-post ids leave the Programme and the gap is filled.
programme_test_reset();
$GLOBALS['fake_posts'][7] = ['draft', 'post'];
$GLOBALS['fake_posts'][8] = ['publish', 'page'];
$GLOBALS['fake_batch'] = [5, 7, 8, 9];
rotation_test_check(mazaq_programme_post_ids() === [5, 9, 20, 19], 'unpublished and non-post batch ids dropped');

// An unpublished pin is ignored rather than leading the Programme.
programme_test_reset();
$GLOBALS['fake_posts'][6] = ['trash', 'post'];
$GLOBALS['fake_pin'] = 6;
$GLOBALS['fake_batch'] = [5, 7, 8, 9];
rotation_test_check(mazaq_programme_post_ids() === [5, 7, 8, 9], 'unpublished pin ignored');

// Empty rotation (disabled or exhausted) falls back to the latest articles.
programme_test_reset();
rotation_test_check(mazaq_programme_post_ids() === [20, 19, 18, 17], 'empty rotation served by latest');

// Fewer than four articles on the site: return what exists, never pad with 0.
programme_test_reset();
$GLOBALS['fake_posts'] = [3 => ['publish', 'post'], 4 => ['publish', 'post']];
$GLOBALS['fake_latest'] = [4, 3];
rotation_test_check(mazaq_programme_post_ids() === [4, 3], 'small site returns what exists');

// Duplicate or junk ids in the batch never duplicate a slide.
programme_test_reset();
$GLOBALS['fake_batch'] = [5, 5, 0, '6'];
rotation_test_check(mazaq_programme_post_ids() === [5, 6, 20, 19], 'batch ids coerced and deduped');

echo "OK: {$GLOBALS['rotation_test_passed']} checks passed\n";
