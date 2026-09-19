<?php

/**
 * Notification dispatch: payloads in, delivery reports out. Owns the WebPush
 * adapter, the batch send loop, and the failure classification behind
 * subscription invalidation. No hooks; the scheduler and the admin surface
 * call through this seam.
 */

declare(strict_types=1);

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Decide whether a push failure permanently retires the subscription.
 */
function mazaq_browser_notifications_is_permanent_push_failure(string $reason): bool
{
    $reason = strtolower($reason);

    return strpos($reason, '410') !== false
        || strpos($reason, '404') !== false
        || strpos($reason, 'expired') !== false
        || strpos($reason, 'unsubscribe') !== false
        || strpos($reason, 'invalid') !== false;
}

/**
 * Get a configured WebPush client when the library and keys are available.
 */
function mazaq_browser_notifications_get_web_push_client(): ?WebPush
{
    if (!mazaq_browser_notifications_is_push_ready()) {
        return null;
    }

    if (!class_exists(WebPush::class)) {
        return null;
    }

    $settings = mazaq_browser_notifications_get_settings();
    $subject = home_url('/');

    $web_push = new WebPush([
        'VAPID' => [
            'subject' => $subject,
            'publicKey' => (string) $settings['vapid_public_key'],
            'privateKey' => (string) $settings['vapid_private_key'],
        ],
    ], [
        'TTL' => 300,
    ]);

    if (method_exists($web_push, 'setReuseVAPIDHeaders')) {
        $web_push->setReuseVAPIDHeaders(true);
    }

    return $web_push;
}

/**
 * Send one payload to all eligible active subscriptions in batches.
 *
 * @param array<string, mixed> $payload Payload to deliver.
 * @return array<string, int|bool>
 */
function mazaq_browser_notifications_send_push_payload(array $payload): array
{
    $result = [
        'client_ready' => false,
        'queued' => 0,
        'success' => 0,
        'invalidated' => 0,
    ];

    if (empty($payload['type'])) {
        return $result;
    }

    $web_push = mazaq_browser_notifications_get_web_push_client();
    if (!$web_push instanceof WebPush) {
        return $result;
    }

    $result['client_ready'] = true;
    $type = (string) $payload['type'];
    $after_id = 0;

    do {
        $batch = mazaq_browser_notifications_get_subscription_batch($after_id, 100);
        if (empty($batch)) {
            break;
        }

        $queued_subscriptions = [];

        foreach ($batch as $subscription_row) {
            $after_id = max($after_id, (int) $subscription_row['id']);

            if (!mazaq_browser_notifications_subscription_is_eligible($subscription_row, $type)) {
                continue;
            }

            $subscription = Subscription::create([
                'endpoint' => (string) $subscription_row['endpoint'],
                'keys' => [
                    'p256dh' => (string) $subscription_row['public_key'],
                    'auth' => (string) $subscription_row['auth_token'],
                ],
            ]);

            $web_push->queueNotification($subscription, wp_json_encode($payload) ?: '{}');
            $queued_subscriptions[(string) $subscription_row['endpoint']] = $subscription_row;
            $result['queued']++;
        }

        foreach ($web_push->flush() as $report) {
            $endpoint = method_exists($report, 'getEndpoint') ? (string) $report->getEndpoint() : '';
            if ($endpoint === '' || !isset($queued_subscriptions[$endpoint])) {
                continue;
            }

            $subscription_row = $queued_subscriptions[$endpoint];

            if ($report->isSuccess()) {
                mazaq_browser_notifications_mark_delivery_success($subscription_row, $type);
                $result['success']++;
                continue;
            }

            $reason = (string) (method_exists($report, 'getReason') ? $report->getReason() : '');
            if (mazaq_browser_notifications_is_permanent_push_failure($reason)) {
                mazaq_browser_notifications_mark_subscription_invalid((string) $subscription_row['endpoint_hash']);
                $result['invalidated']++;
            }
        }
    } while (!empty($batch));

    return $result;
}
