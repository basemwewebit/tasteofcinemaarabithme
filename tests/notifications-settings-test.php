<?php

/**
 * Standalone self-check for the notifications settings seam. No framework, no
 * WP: run with `php tests/notifications-settings-test.php`.
 *
 * Seam under test: mazaq_browser_notifications_normalize_daily_time().
 * The scheduler reads the daily send time through this interface; these checks
 * pin the HH:MM policy and the 09:00 fallback it promises.
 *
 * Expected values are hand-worked literals, never recomputed by the
 * implementation under test.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

require __DIR__ . '/../inc/notifications-settings.php';

// Valid times survive untouched, including day boundaries.
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('09:00') === '09:00',
    'valid morning time survives'
);
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('00:00') === '00:00',
    'midnight survives'
);
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('23:59') === '23:59',
    'last minute of day survives'
);

// Surrounding whitespace is trimmed before validation.
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('  09:30  ') === '09:30',
    'whitespace-padded time is trimmed'
);

// Malformed input falls back to 09:00, never an empty string.
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('') === '09:00',
    'empty string falls back to 09:00'
);
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('junk') === '09:00',
    'non-time string falls back to 09:00'
);
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('9:00') === '09:00',
    'single-digit hour falls back to 09:00'
);
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('0930') === '09:00',
    'missing colon falls back to 09:00'
);

// Out-of-range values fall back to 09:00.
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('24:00') === '09:00',
    'hour 24 falls back to 09:00'
);
rotation_test_check(
    mazaq_browser_notifications_normalize_daily_time('09:60') === '09:00',
    'minute 60 falls back to 09:00'
);

echo 'notifications-settings: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " seam checks passed\n";
