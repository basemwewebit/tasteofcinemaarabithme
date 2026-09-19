<?php

/**
 * Standalone self-check for the notification store seam. No framework, no WP:
 * run with `php tests/notifications-store-test.php`.
 *
 * Seam under test: mazaq_browser_notifications_subscription_is_eligible().
 * The dispatcher reads per-subscription quota through this interface; these
 * checks pin the daily window cap, the once-a-day suggestion rule, and the
 * test bypass it promises.
 *
 * Expected values are hand-worked literals, never recomputed by the
 * implementation under test.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

if (!function_exists('current_datetime')) {
    function current_datetime(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-09-19 12:00:00', new DateTimeZone('UTC'));
    }
}

require __DIR__ . '/../inc/notifications-settings.php';
require __DIR__ . '/../inc/notifications-store.php';

$today = '2026-09-19';
$yesterday = '2026-09-18';

// Test sends bypass quota entirely, even over cap and already sent today.
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 2, 'daily_sent_on' => $today],
        'test',
        $today
    ) === true,
    'test type bypasses quota'
);
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 2, 'daily_sent_on' => $today],
        'test_daily_random',
        $today
    ) === true,
    'daily preview type bypasses quota'
);

// Fresh subscriptions are eligible for both kinds.
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible([], 'daily_random', $today) === true,
    'fresh row is eligible for daily suggestion'
);
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible([], 'new_post', $today) === true,
    'fresh row is eligible for new-post alert'
);

// The daily suggestion fires once per day.
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 1, 'daily_sent_on' => $today],
        'daily_random',
        $today
    ) === false,
    'daily suggestion already sent today is ineligible'
);
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 1, 'daily_sent_on' => $yesterday],
        'daily_random',
        $today
    ) === true,
    'daily suggestion sent yesterday is eligible again'
);

// New-post alerts ignore the suggestion marker.
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 1, 'daily_sent_on' => $today],
        'new_post',
        $today
    ) === true,
    'new-post alert ignores the daily suggestion marker'
);

// The window caps at two deliveries per day.
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 2],
        'new_post',
        $today
    ) === false,
    'window count 2 blocks a third delivery'
);
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 2, 'daily_sent_on' => $yesterday],
        'daily_random',
        $today
    ) === false,
    'window cap wins over suggestion eligibility'
);

// A new day resets the window.
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $yesterday, 'daily_window_count' => 2],
        'new_post',
        $today
    ) === true,
    'stale window resets for the new day'
);

// Without an explicit day the seam reads site-today (stubbed 2026-09-19).
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 1, 'daily_sent_on' => $today],
        'daily_random'
    ) === false,
    'default day blocks a repeat suggestion'
);
rotation_test_check(
    mazaq_browser_notifications_subscription_is_eligible(
        ['daily_window_date' => $today, 'daily_window_count' => 1, 'daily_sent_on' => $yesterday],
        'daily_random'
    ) === true,
    'default day allows a fresh suggestion'
);

echo 'notifications-store: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " seam checks passed\n";
