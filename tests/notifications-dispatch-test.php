<?php

/**
 * Standalone self-check for the notification dispatch seam. No framework, no
 * WP: run with `php tests/notifications-dispatch-test.php`.
 *
 * Seam under test: mazaq_browser_notifications_is_permanent_push_failure().
 * The send loop invalidates subscriptions through this interface; these checks
 * pin which delivery failures retire a subscription and which leave it alone.
 *
 * Expected values are hand-worked literals, never recomputed by the
 * implementation under test.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

require __DIR__ . '/../inc/notifications-dispatch.php';

// Gone, missing, expired, unsubscribed, or invalid endpoints retire.
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('410 Gone') === true,
    '410 Gone retires the subscription'
);
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('404 Not Found') === true,
    '404 retires the subscription'
);
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('Subscription expired') === true,
    'expired subscription retires'
);
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('Endpoint has been unsubscribed') === true,
    'unsubscribed endpoint retires'
);
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('Invalid JWT signature') === true,
    'invalid credentials retire whatever the case'
);

// Anything else leaves the subscription alone for the next send.
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('401 Unauthorized') === false,
    'auth failure keeps the subscription'
);
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('500 Internal Server Error') === false,
    'server error keeps the subscription'
);
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('413 Payload Too Large') === false,
    'oversize payload keeps the subscription'
);
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('timeout') === false,
    'timeout keeps the subscription'
);
rotation_test_check(
    mazaq_browser_notifications_is_permanent_push_failure('') === false,
    'empty reason keeps the subscription'
);

echo 'notifications-dispatch: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " seam checks passed\n";
