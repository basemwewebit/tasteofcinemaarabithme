<?php

/**
 * Standalone self-check for the Content rotation orchestration: pool reader,
 * config mapping, daily prepare matrix, scheduler, and the narrow today-batch
 * getter. No framework, no WP: run with `php tests/content-rotation-prepare-test.php`.
 *
 * Stubs the option store, WP_Query, clock, cron queue, and rotation settings
 * the engine touches, then drives the engine seam the way production does.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

// --- Fake option store -------------------------------------------------------
$GLOBALS['fake_options'] = [];

if (!function_exists('get_option')) {
    function get_option($key, $default = false)
    {
        return array_key_exists($key, $GLOBALS['fake_options']) ? $GLOBALS['fake_options'][$key] : $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($key, $value, $autoload = null): bool
    {
        $GLOBALS['fake_options'][$key] = $value;

        return true;
    }
}

if (!function_exists('add_option')) {
    function add_option($key, $value, $deprecated = '', $autoload = 'yes'): bool
    {
        if (array_key_exists($key, $GLOBALS['fake_options'])) {
            return false;
        }

        $GLOBALS['fake_options'][$key] = $value;

        return true;
    }
}

// --- Fake clock --------------------------------------------------------------
$GLOBALS['fake_today'] = '2024-05-01';

if (!function_exists('current_datetime')) {
    function current_datetime()
    {
        return new class() {
            public function format(string $format): string
            {
                return $GLOBALS['fake_today'];
            }
        };
    }
}

// --- Fake WP_Query: serves pages from the fake published pool ----------------
$GLOBALS['fake_published_pool'] = [];
$GLOBALS['fake_query_count'] = 0;
$GLOBALS['fake_last_query_args'] = [];

if (!class_exists('WP_Query')) {
    class WP_Query
    {
        /** @var int[] */
        public $posts = [];

        public function __construct(array $args = [])
        {
            $GLOBALS['fake_query_count']++;
            $GLOBALS['fake_last_query_args'] = $args;

            $pool = $GLOBALS['fake_published_pool'];
            sort($pool);
            $per_page = (int) ($args['posts_per_page'] ?? 200);
            $page = max(1, (int) ($args['paged'] ?? 1));

            $this->posts = array_slice($pool, ($page - 1) * $per_page, $per_page);
        }
    }
}

// --- Fake posts for publishability checks ------------------------------------
$GLOBALS['fake_posts'] = [];

if (!function_exists('get_post_status')) {
    function get_post_status($post_id)
    {
        return $GLOBALS['fake_posts'][(int) $post_id]['status'] ?? false;
    }
}

if (!function_exists('get_post_type')) {
    function get_post_type($post_id)
    {
        return $GLOBALS['fake_posts'][(int) $post_id]['type'] ?? false;
    }
}

// --- Fake cron queue ---------------------------------------------------------
$GLOBALS['fake_cron'] = [];

if (!function_exists('wp_next_scheduled')) {
    function wp_next_scheduled($hook)
    {
        $stamps = $GLOBALS['fake_cron'][$hook] ?? [];

        return empty($stamps) ? false : min($stamps);
    }
}

if (!function_exists('wp_schedule_event')) {
    function wp_schedule_event($timestamp, $recurrence, $hook): bool
    {
        $GLOBALS['fake_cron'][$hook][] = (int) $timestamp;
        $GLOBALS['fake_cron_recurrence'][$hook] = $recurrence;

        return true;
    }
}

if (!function_exists('wp_unschedule_event')) {
    function wp_unschedule_event($timestamp, $hook): bool
    {
        $stamps = $GLOBALS['fake_cron'][$hook] ?? [];
        $key = array_search((int) $timestamp, $stamps, true);

        if ($key === false) {
            return false;
        }

        unset($stamps[$key]);
        $GLOBALS['fake_cron'][$hook] = array_values($stamps);

        return true;
    }
}

// --- Fake rotation settings page ---------------------------------------------
$GLOBALS['fake_rotation_settings'] = [];

if (!function_exists('mazaq_content_rotation_get_settings')) {
    function mazaq_content_rotation_get_settings(): array
    {
        return $GLOBALS['fake_rotation_settings'];
    }
}

if (!function_exists('mazaq_content_rotation_get_daily_hour')) {
    function mazaq_content_rotation_get_daily_hour(): int
    {
        return 8;
    }
}

if (!function_exists('mazaq_content_rotation_get_next_daily_timestamp')) {
    function mazaq_content_rotation_get_next_daily_timestamp(int $hour): int
    {
        return 1714521600;
    }
}

// --- Fake hook registry (captures registration, runs nothing) ----------------
$GLOBALS['fake_hooks'] = [];

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $args = 1): void
    {
        $GLOBALS['fake_hooks'][$hook][] = $callback;
    }
}

require __DIR__ . '/../inc/content-rotation.php';

$hero_feature = [
    'option' => 'test_hero_state',
    'event' => 'test_hero_event',
    'date_key' => 'rotation_date',
    'batch_key' => 'hero_post_ids',
    'extra_state' => [],
    'settings_enabled_key' => 'hero_enabled',
    'settings_count_key' => 'hero_count',
    'max_count' => 3,
    'widget_id' => 'test-hero-widget',
    'widget_title' => 'Hero test widget',
    'regenerate_action' => 'test_hero_regenerate',
    'regenerate_nonce' => 'test_hero_regenerate_nonce',
    'quick_save_action' => 'test_hero_quick_save',
    'quick_save_nonce' => 'test_hero_quick_save_nonce',
];

$social_feature = [
    'option' => 'test_social_state',
    'event' => 'test_social_event',
    'date_key' => 'batch_date',
    'batch_key' => 'batch_post_ids',
    'extra_state' => ['last_email_sent_date' => ''],
    'settings_enabled_key' => 'social_enabled',
    'settings_count_key' => 'social_count',
    'max_count' => 10,
    'widget_id' => 'test-social-widget',
    'widget_title' => 'Social test widget',
    'regenerate_action' => 'test_social_regenerate',
    'regenerate_nonce' => 'test_social_regenerate_nonce',
    'quick_save_action' => 'test_social_quick_save',
    'quick_save_nonce' => 'test_social_quick_save_nonce',
];

function reset_rotation_fakes(array $pool = [], array $settings = []): void
{
    $GLOBALS['fake_options'] = [];
    $GLOBALS['fake_published_pool'] = $pool;
    $GLOBALS['fake_query_count'] = 0;
    $GLOBALS['fake_cron'] = [];
    $GLOBALS['fake_rotation_settings'] = $settings;
}

// Pool reader: pages the whole pool, ints, unique, stable order args.
reset_rotation_fakes(range(1, 450));
$ids = mazaq_rotation_get_published_post_ids();
rotation_test_check($ids === range(1, 450), 'pool reader pages all 450 ids in order');
rotation_test_check($GLOBALS['fake_query_count'] === 3, 'pool reader runs one short final page');
rotation_test_check($GLOBALS['fake_last_query_args']['posts_per_page'] === 200, 'pool reader pages 200 at a time');
rotation_test_check($GLOBALS['fake_last_query_args']['orderby'] === 'ID', 'pool reader orders by id ascending');
rotation_test_check($GLOBALS['fake_last_query_args']['fields'] === 'ids', 'pool reader fetches ids only');

// Pool reader: extra query args pass through for the category variant.
reset_rotation_fakes([5, 6]);
mazaq_rotation_get_published_post_ids(['cat' => 7]);
rotation_test_check($GLOBALS['fake_last_query_args']['cat'] === 7, 'pool reader passes category through');

// Config: settings keys map per feature, counts clamp to the feature cap.
reset_rotation_fakes([], ['hero_enabled' => 1, 'hero_count' => 9, 'social_enabled' => 0, 'social_count' => 99]);
rotation_test_check(
    mazaq_rotation_get_config($hero_feature) === ['enabled' => true, 'batch_size' => 3],
    'hero config reads hero keys and clamps to 3'
);
rotation_test_check(
    mazaq_rotation_get_config($social_feature) === ['enabled' => false, 'batch_size' => 10],
    'social config reads social keys and clamps to 10'
);

// Disabled feature: state untouched, pool never queried.
reset_rotation_fakes([11, 12, 13], ['hero_enabled' => 0, 'hero_count' => 2]);
$GLOBALS['fake_options']['test_hero_state'] = ['rotation_date' => '2024-04-01', 'hero_post_ids' => [11], 'used_post_ids' => [11]];
$disabled = mazaq_rotation_prepare_today_batch($hero_feature);
rotation_test_check($disabled['hero_post_ids'] === [11], 'disabled prepare keeps the old batch');
rotation_test_check($disabled['rotation_date'] === '2024-04-01', 'disabled prepare keeps the old date');
rotation_test_check($GLOBALS['fake_query_count'] === 0, 'disabled prepare never scans the pool');

// Fresh feature: first run deals a batch for today and persists it.
reset_rotation_fakes([11, 12, 13], ['hero_enabled' => 1, 'hero_count' => 2]);
$fresh = mazaq_rotation_prepare_today_batch($hero_feature);
rotation_test_check($fresh['rotation_date'] === '2024-05-01', 'fresh prepare stamps today');
rotation_test_check(count($fresh['hero_post_ids']) === 2, 'fresh prepare deals the configured size');
rotation_test_check(count(array_intersect($fresh['hero_post_ids'], [11, 12, 13])) === 2, 'fresh batch comes from the pool');
$persisted = $GLOBALS['fake_options']['test_hero_state'];
rotation_test_check($persisted['hero_post_ids'] === $fresh['hero_post_ids'], 'fresh prepare persists the batch');

// Current batch: today's valid batch survives untouched, used ids stable.
reset_rotation_fakes([11, 12, 13], ['hero_enabled' => 1, 'hero_count' => 2]);
$GLOBALS['fake_options']['test_hero_state'] = ['rotation_date' => '2024-05-01', 'hero_post_ids' => [11, 12], 'used_post_ids' => [11, 12]];
$current = mazaq_rotation_prepare_today_batch($hero_feature);
rotation_test_check($current['hero_post_ids'] === [11, 12], 'current batch is not regenerated');
rotation_test_check($current['used_post_ids'] === [11, 12], 'current used ids stay stable');

// Stale date: a new day deals a new batch.
reset_rotation_fakes(range(1, 10), ['hero_enabled' => 1, 'hero_count' => 2]);
$GLOBALS['fake_options']['test_hero_state'] = ['rotation_date' => '2024-04-30', 'hero_post_ids' => [1, 2], 'used_post_ids' => [1, 2]];
$stale = mazaq_rotation_prepare_today_batch($hero_feature);
rotation_test_check($stale['rotation_date'] === '2024-05-01', 'stale date restamps today');
rotation_test_check(count(array_intersect($stale['hero_post_ids'], [1, 2])) === 0, 'stale date avoids the consumed cycle');

// Invalid batch ids: trashed posts force a regenerate even for today.
reset_rotation_fakes([11, 12, 13], ['hero_enabled' => 1, 'hero_count' => 2]);
$GLOBALS['fake_options']['test_hero_state'] = ['rotation_date' => '2024-05-01', 'hero_post_ids' => [11, 99], 'used_post_ids' => [11, 99]];
$healed = mazaq_rotation_prepare_today_batch($hero_feature);
rotation_test_check(!in_array(99, $healed['hero_post_ids'], true), 'trashed batch id leaves the batch');
rotation_test_check(!in_array(99, $healed['used_post_ids'], true), 'trashed batch id leaves used');
rotation_test_check(count($healed['hero_post_ids']) === 2, 'healed batch refills to size');

// Forced regenerate: a valid batch is redealt on demand.
reset_rotation_fakes(range(1, 10), ['hero_enabled' => 1, 'hero_count' => 2]);
$GLOBALS['fake_options']['test_hero_state'] = ['rotation_date' => '2024-05-01', 'hero_post_ids' => [1, 2], 'used_post_ids' => [1, 2]];
$forced = mazaq_rotation_prepare_today_batch($hero_feature, true);
rotation_test_check($forced['rotation_date'] === '2024-05-01', 'forced prepare keeps today');
rotation_test_check(count(array_intersect($forced['hero_post_ids'], [1, 2])) === 0, 'forced prepare redeals outside used');

// Narrow getter: today's batch served without a rescan.
reset_rotation_fakes([11, 12, 13], ['hero_enabled' => 1, 'hero_count' => 2]);
$GLOBALS['fake_options']['test_hero_state'] = ['rotation_date' => '2024-05-01', 'hero_post_ids' => [12, 13], 'used_post_ids' => [12, 13]];
$served = mazaq_rotation_get_today_batch_ids($hero_feature);
rotation_test_check($served === [12, 13], 'getter serves today batch as stored');
rotation_test_check($GLOBALS['fake_query_count'] === 0, 'getter skips the pool scan when fresh');

// Narrow getter: missing batch generates lazily.
$GLOBALS['fake_options']['test_hero_state'] = ['rotation_date' => '', 'hero_post_ids' => [], 'used_post_ids' => []];
$lazy = mazaq_rotation_get_today_batch_ids($hero_feature);
rotation_test_check(count($lazy) === 2, 'getter lazily generates a missing batch');

// Narrow getter: disabled feature yields nothing without scanning.
reset_rotation_fakes([11, 12, 13], ['hero_enabled' => 0, 'hero_count' => 2]);
$GLOBALS['fake_options']['test_hero_state'] = ['rotation_date' => '2024-05-01', 'hero_post_ids' => [12], 'used_post_ids' => [12]];
rotation_test_check(mazaq_rotation_get_today_batch_ids($hero_feature) === [], 'getter yields nothing when disabled');

// Social shape: extras round-trip through prepare untouched by the engine.
reset_rotation_fakes([21, 22, 23], ['social_enabled' => 1, 'social_count' => 2]);
$GLOBALS['fake_options']['test_social_state'] = ['batch_date' => '2024-05-01', 'batch_post_ids' => [21, 22], 'used_post_ids' => [21, 22], 'last_email_sent_date' => '2024-04-30'];
$social = mazaq_rotation_prepare_today_batch($social_feature);
rotation_test_check($social['batch_post_ids'] === [21, 22], 'social batch honors the social key');
rotation_test_check($social['last_email_sent_date'] === '2024-04-30', 'engine never touches the email marker');

// Publishability: positive published posts only.
$GLOBALS['fake_posts'] = [
    5 => ['status' => 'publish', 'type' => 'post'],
    6 => ['status' => 'draft', 'type' => 'post'],
    7 => ['status' => 'publish', 'type' => 'page'],
];
rotation_test_check(mazaq_rotation_is_post_publishable(5) === true, 'published post is publishable');
rotation_test_check(mazaq_rotation_is_post_publishable(6) === false, 'draft is not publishable');
rotation_test_check(mazaq_rotation_is_post_publishable(7) === false, 'page is not publishable');
rotation_test_check(mazaq_rotation_is_post_publishable(0) === false, 'zero id is not publishable');

// Scheduler: registers a daily event once, clears on disable.
reset_rotation_fakes([], ['hero_enabled' => 1, 'hero_count' => 2]);
mazaq_rotation_schedule_event($hero_feature);
rotation_test_check(wp_next_scheduled('test_hero_event') === 1714521600, 'schedule registers the daily event');
rotation_test_check($GLOBALS['fake_cron_recurrence']['test_hero_event'] === 'daily', 'schedule recurs daily');
mazaq_rotation_schedule_event($hero_feature);
rotation_test_check(count($GLOBALS['fake_cron']['test_hero_event']) === 1, 'schedule never duplicates the event');
mazaq_rotation_clear_event($hero_feature);
rotation_test_check(wp_next_scheduled('test_hero_event') === false, 'clear drains the event');
$GLOBALS['fake_cron']['test_hero_event'] = [1714521600];
$GLOBALS['fake_rotation_settings'] = ['hero_enabled' => 0, 'hero_count' => 2];
mazaq_rotation_schedule_event($hero_feature);
rotation_test_check(wp_next_scheduled('test_hero_event') === false, 'disabled schedule clears the event');

// Registration: one call wires cron, lifecycle, widget, and admin-post hooks.
$GLOBALS['fake_hooks'] = [];
mazaq_rotation_register_feature($hero_feature);
foreach (['test_hero_event', 'after_switch_theme', 'init', 'switch_theme', 'wp_dashboard_setup', 'admin_post_test_hero_regenerate', 'admin_post_test_hero_quick_save'] as $hook) {
    rotation_test_check(!empty($GLOBALS['fake_hooks'][$hook]), "register wires {$hook}");
}

echo 'content-rotation-prepare: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " orchestration checks passed\n";
