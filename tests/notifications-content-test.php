<?php

/**
 * Standalone self-check for the notification content seam. No framework, no
 * WP: run with `php tests/notifications-content-test.php`.
 *
 * Seams under test: the Recent-notifications visibility policy, the Push
 * notification id builder, and the feed routing core. The scheduler and the
 * admin surface read notification content through this interface; these checks
 * pin the cutoff policy, the id shapes, and the routing it promises them.
 *
 * Expected values are hand-worked literals, never recomputed by the
 * implementation under test.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

require __DIR__ . '/../inc/notifications-settings.php';
require __DIR__ . '/../inc/notifications-content.php';

$now = strtotime('2026-09-19T12:00:00+00:00');
$today = '2026-09-19';

// New-post alerts stay visible for seven days.
rotation_test_check(
    mazaq_browser_notifications_fallback_slot_is_visible(
        'new_post',
        strtotime('2026-09-18T12:00:00+00:00'),
        '2026-09-18',
        $today,
        $now
    ) === true,
    'day-old new-post alert stays visible'
);
rotation_test_check(
    mazaq_browser_notifications_fallback_slot_is_visible(
        'new_post',
        $now - (7 * 86400),
        '2026-09-12',
        $today,
        $now
    ) === true,
    'new-post alert exactly seven days old stays visible'
);
rotation_test_check(
    mazaq_browser_notifications_fallback_slot_is_visible(
        'new_post',
        $now - (7 * 86400) - 1,
        '2026-09-12',
        $today,
        $now
    ) === false,
    'new-post alert older than seven days hides'
);

// The daily suggestion is visible on its own calendar day only.
rotation_test_check(
    mazaq_browser_notifications_fallback_slot_is_visible(
        'daily_random',
        strtotime('2026-09-19T01:00:00+00:00'),
        '2026-09-19',
        $today,
        $now
    ) === true,
    'daily suggestion from today stays visible'
);
rotation_test_check(
    mazaq_browser_notifications_fallback_slot_is_visible(
        'daily_random',
        strtotime('2026-09-18T23:00:00+00:00'),
        '2026-09-18',
        $today,
        $now
    ) === false,
    'daily suggestion from yesterday hides'
);

// Missing timestamps hide the slot instead of surfacing undated content.
rotation_test_check(
    mazaq_browser_notifications_fallback_slot_is_visible('new_post', null, null, $today, $now) === false,
    'undated new-post alert hides'
);
rotation_test_check(
    mazaq_browser_notifications_fallback_slot_is_visible('daily_random', null, null, $today, $now) === false,
    'undated daily suggestion hides'
);

// Unknown slots never surface.
rotation_test_check(
    mazaq_browser_notifications_fallback_slot_is_visible('test', $now, $today, $today, $now) === false,
    'unknown slot hides'
);

// Payload ids pin each notification kind; the day is ignored for test kinds.
rotation_test_check(
    mazaq_browser_notifications_build_payload_id('daily_random', 42, '2026-09-19', $now) === 'daily_random:2026-09-19:42',
    'daily suggestion id carries day and post'
);
rotation_test_check(
    mazaq_browser_notifications_build_payload_id('new_post', 7, '2026-09-19', 1720000000) === 'new_post:7:1720000000',
    'new-post id carries post and timestamp'
);
rotation_test_check(
    mazaq_browser_notifications_build_payload_id('test', 0, '', 1720000000) === 'test:1720000000',
    'test id carries timestamp only'
);
rotation_test_check(
    mazaq_browser_notifications_build_payload_id('test_daily_random', 9, '', 1720000000) === 'test_daily_random:9:1720000000',
    'daily preview id carries post and timestamp'
);
rotation_test_check(
    mazaq_browser_notifications_build_payload_id('weird', 5, '2026-09-19', 1720000000) === 'new_post:5:1720000000',
    'unknown type keeps the new-post id shape'
);

// Feed routing files each payload into its slot; test kinds never clobber.
$empty_feed = ['daily_random' => null, 'new_post' => null];
$daily_payload = ['type' => 'daily_random', 'id' => 'daily_random:2026-09-19:42'];
$new_post_payload = ['type' => 'new_post', 'id' => 'new_post:7:1720000000'];
rotation_test_check(
    mazaq_browser_notifications_apply_feed_payload($empty_feed, $daily_payload) === ['daily_random' => $daily_payload, 'new_post' => null],
    'daily suggestion files into the daily slot'
);
rotation_test_check(
    mazaq_browser_notifications_apply_feed_payload($empty_feed, $new_post_payload) === ['daily_random' => null, 'new_post' => $new_post_payload],
    'new-post alert files into the new-post slot'
);
rotation_test_check(
    mazaq_browser_notifications_apply_feed_payload(
        ['daily_random' => $daily_payload, 'new_post' => null],
        ['type' => 'test', 'id' => 'test:1720000000']
    ) === ['daily_random' => $daily_payload, 'new_post' => null],
    'test payload leaves the feed untouched'
);
rotation_test_check(
    mazaq_browser_notifications_apply_feed_payload($empty_feed, ['id' => 'typeless']) === $empty_feed,
    'typeless payload leaves the feed untouched'
);

echo 'notifications-content: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " seam checks passed\n";
