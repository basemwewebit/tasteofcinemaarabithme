<?php

/**
 * Standalone self-check for the Content rotation kernel: pure cycle math behind
 * the engine seam. No framework, no WP: run with `php tests/content-rotation-test.php`.
 *
 * Uses an explicit check() helper instead of assert(): this environment ships
 * with zend.assertions=-1, under which assert() compiles to a no-op.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

require __DIR__ . '/../inc/content-rotation.php';

$hero_feature = [
    'date_key' => 'rotation_date',
    'batch_key' => 'hero_post_ids',
    'extra_state' => [],
];

$social_feature = [
    'date_key' => 'batch_date',
    'batch_key' => 'batch_post_ids',
    'extra_state' => ['last_email_sent_date' => ''],
];

// default_state: feature key names shape the state, extras included.
rotation_test_check(
    mazaq_rotation_default_state($hero_feature) === ['rotation_date' => '', 'hero_post_ids' => [], 'used_post_ids' => []],
    'hero default state shape'
);
rotation_test_check(
    mazaq_rotation_default_state($social_feature) === ['batch_date' => '', 'batch_post_ids' => [], 'used_post_ids' => [], 'last_email_sent_date' => ''],
    'social default state shape carries email marker'
);

// normalize_state: non-array input falls back to defaults.
rotation_test_check(
    mazaq_rotation_normalize_state($hero_feature, null) === mazaq_rotation_default_state($hero_feature),
    'normalize null gives defaults'
);
rotation_test_check(
    mazaq_rotation_normalize_state($hero_feature, 'junk') === mazaq_rotation_default_state($hero_feature),
    'normalize string gives defaults'
);

// normalize_state: ids coerce to unique positive ints, dates to strings.
$normalized = mazaq_rotation_normalize_state($hero_feature, [
    'rotation_date' => 20240101,
    'hero_post_ids' => ['7', 7, 0, 'x', 3],
    'used_post_ids' => [3, '3', 0],
]);
rotation_test_check($normalized['rotation_date'] === '', 'non-string date resets');
rotation_test_check($normalized['hero_post_ids'] === [7, 3], 'batch ids coerce, dedupe, drop empties');
rotation_test_check($normalized['used_post_ids'] === [3], 'used ids coerce and dedupe');

// normalize_state: social extras survive with string type.
$social_normalized = mazaq_rotation_normalize_state($social_feature, [
    'batch_date' => '2024-01-01',
    'batch_post_ids' => [1],
    'used_post_ids' => [1, 2],
    'last_email_sent_date' => null,
]);
rotation_test_check($social_normalized['last_email_sent_date'] === '', 'null email marker resets to string');
rotation_test_check($social_normalized['batch_date'] === '2024-01-01', 'valid date survives');

// pick_random_ids: empty pool or non-positive count picks nothing.
rotation_test_check(mazaq_rotation_pick_random_ids([], 3) === [], 'empty pool picks nothing');
rotation_test_check(mazaq_rotation_pick_random_ids([1, 2, 3], 0) === [], 'zero count picks nothing');
rotation_test_check(mazaq_rotation_pick_random_ids([1, 2, 3], -2) === [], 'negative count picks nothing');

// pick_random_ids: filtering matches the twins (ints, unique, truthy).
$picks = mazaq_rotation_pick_random_ids(['3', '3', 0, 'x', 5], 5);
sort($picks);
rotation_test_check($picks === [3, 5], 'picks filter to unique positive ints');

// pick_random_ids: full-pool request returns every id exactly once.
$full = mazaq_rotation_pick_random_ids([9, 4, 6], 3);
sort($full);
rotation_test_check($full === [4, 6, 9], 'full-pool pick returns every id');

// pick_random_ids: partial pick returns the right count from the pool.
$partial = mazaq_rotation_pick_random_ids([1, 2, 3, 4, 5, 6, 7, 8], 3);
rotation_test_check(count($partial) === 3, 'partial pick returns requested count');
rotation_test_check(count(array_intersect($partial, [1, 2, 3, 4, 5, 6, 7, 8])) === 3, 'partial pick stays inside pool');
rotation_test_check(count(array_unique($partial)) === 3, 'partial pick has no repeats');

// prune_state: unpublished ids leave both lists, survivors re-index.
$pruned = mazaq_rotation_prune_state(
    $hero_feature,
    ['rotation_date' => '2024-01-01', 'hero_post_ids' => [2, 99], 'used_post_ids' => [1, 2, 99]],
    [1, 2, 3]
);
rotation_test_check($pruned['hero_post_ids'] === [2], 'prune drops unpublished batch ids');
rotation_test_check($pruned['used_post_ids'] === [1, 2], 'prune drops unpublished used ids');
rotation_test_check($pruned['rotation_date'] === '2024-01-01', 'prune keeps the date');

// prune_state honors the feature batch key.
$social_pruned = mazaq_rotation_prune_state(
    $social_feature,
    ['batch_date' => '2024-01-01', 'batch_post_ids' => [5, 6], 'used_post_ids' => [5], 'last_email_sent_date' => '2024-01-01'],
    [5]
);
rotation_test_check($social_pruned['batch_post_ids'] === [5], 'prune honors social batch key');
rotation_test_check($social_pruned['last_email_sent_date'] === '2024-01-01', 'prune keeps extras');

// generate_batch: empty pool yields empties.
rotation_test_check(
    mazaq_rotation_generate_batch([], [], 3) === ['batch_ids' => [], 'used_ids' => []],
    'empty pool generates empties'
);

// generate_batch: fresh pool deals a batch and seeds used.
$fresh = mazaq_rotation_generate_batch([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [], 3);
rotation_test_check(count($fresh['batch_ids']) === 3, 'fresh batch has requested size');
$sorted_fresh_batch = $fresh['batch_ids'];
sort($sorted_fresh_batch);
$sorted_fresh_used = $fresh['used_ids'];
sort($sorted_fresh_used);
rotation_test_check($sorted_fresh_batch === $sorted_fresh_used, 'fresh batch seeds used');

// generate_batch: mid-cycle batch avoids used ids and extends them.
$mid = mazaq_rotation_generate_batch([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [1, 2, 3, 4, 5], 3);
rotation_test_check(count(array_intersect($mid['batch_ids'], [1, 2, 3, 4, 5])) === 0, 'mid-cycle batch avoids used');
rotation_test_check(count($mid['used_ids']) === 8, 'mid-cycle batch extends used');

// generate_batch: wrap refills from a new cycle and resets used to the batch.
$wrap = mazaq_rotation_generate_batch([1, 2, 3], [1, 2], 3);
$sorted_wrap_batch = $wrap['batch_ids'];
sort($sorted_wrap_batch);
rotation_test_check($sorted_wrap_batch === [1, 2, 3], 'wrap fills the batch from a new cycle');
$sorted_wrap_used = $wrap['used_ids'];
sort($sorted_wrap_used);
rotation_test_check($sorted_wrap_used === $sorted_wrap_batch, 'wrap resets used to the batch');

// generate_batch: exhausted pool restarts the cycle wholesale.
$restart = mazaq_rotation_generate_batch([1, 2], [1, 2], 2);
$sorted_restart = $restart['batch_ids'];
sort($sorted_restart);
rotation_test_check($sorted_restart === [1, 2], 'exhausted pool restarts the cycle');

// generate_batch: oversize requests cap at the pool, junk sizes floor at one.
$capped = mazaq_rotation_generate_batch([1, 2], [], 9);
rotation_test_check(count($capped['batch_ids']) === 2, 'oversize batch caps at pool size');
$floored = mazaq_rotation_generate_batch([1, 2, 3], [], 0);
rotation_test_check(count($floored['batch_ids']) === 1, 'zero size floors at one batch id');

// generate_batch: used ids outside the pool are intersected away.
$stale_used = mazaq_rotation_generate_batch([1, 2, 3, 4], [1, 99], 2);
rotation_test_check(!in_array(99, $stale_used['used_ids'], true), 'stale used ids leave the cycle');
rotation_test_check(count(array_intersect($stale_used['batch_ids'], [1])) === 0, 'live used ids still avoided');

echo 'content-rotation: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " kernel checks passed\n";
