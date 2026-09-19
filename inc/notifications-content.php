<?php

/**
 * Notification content: posts in, Push notifications out. Owns the payload
 * builders, the feed persistence behind Recent notifications, and the
 * visibility policy. No hooks; the scheduler and the admin surface call
 * through this seam.
 */

declare(strict_types=1);

const MAZAQ_BROWSER_NOTIFICATIONS_FEED_OPTION = 'mazaq_browser_notifications_feed';

/**
 * Default fallback notification state.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_default_feed(): array
{
    return [
        'daily_random' => null,
        'new_post' => null,
    ];
}

/**
 * Ensure the feed option exists with autoload disabled.
 */
function mazaq_browser_notifications_ensure_feed_state(): void
{
    if (false === get_option(MAZAQ_BROWSER_NOTIFICATIONS_FEED_OPTION, false)) {
        add_option(
            MAZAQ_BROWSER_NOTIFICATIONS_FEED_OPTION,
            mazaq_browser_notifications_default_feed(),
            '',
            'no'
        );
    }
}

/**
 * Get the current feed state.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_get_feed_state(): array
{
    mazaq_browser_notifications_ensure_feed_state();

    $state = get_option(MAZAQ_BROWSER_NOTIFICATIONS_FEED_OPTION, []);
    $state = is_array($state) ? array_merge(mazaq_browser_notifications_default_feed(), $state) : mazaq_browser_notifications_default_feed();

    return [
        'daily_random' => is_array($state['daily_random']) ? $state['daily_random'] : null,
        'new_post' => is_array($state['new_post']) ? $state['new_post'] : null,
    ];
}

/**
 * Save the current feed state.
 *
 * @param array<string, mixed> $state Feed state to save.
 */
function mazaq_browser_notifications_update_feed_state(array $state): void
{
    mazaq_browser_notifications_ensure_feed_state();

    $normalized = array_merge(mazaq_browser_notifications_default_feed(), $state);
    $normalized['daily_random'] = is_array($normalized['daily_random']) ? $normalized['daily_random'] : null;
    $normalized['new_post'] = is_array($normalized['new_post']) ? $normalized['new_post'] : null;

    update_option(MAZAQ_BROWSER_NOTIFICATIONS_FEED_OPTION, $normalized, false);
}

/**
 * Decide whether one feed slot stays visible: new-post alerts for seven days,
 * the daily suggestion on its own calendar day only.
 */
function mazaq_browser_notifications_fallback_slot_is_visible(string $slot, ?int $created_ts, ?string $created_day, string $today, int $now): bool
{
    if (!$created_ts) {
        return false;
    }

    if ($slot === 'new_post') {
        return $created_ts >= $now - (7 * 86400); // 7 * DAY_IN_SECONDS.
    }

    if ($slot === 'daily_random') {
        return $created_day === $today;
    }

    return false;
}

/**
 * Return the Recent notifications for non-subscribed visitors.
 *
 * @return array<int, array<string, mixed>>
 */
function mazaq_browser_notifications_get_fallback_notifications(): array
{
    $feed = mazaq_browser_notifications_get_feed_state();
    $notifications = [];
    $today = mazaq_browser_notifications_today();
    $now = time();

    foreach (['new_post', 'daily_random'] as $slot) {
        if (!is_array($feed[$slot])) {
            continue;
        }

        $created_at = strtotime((string) ($feed[$slot]['createdAt'] ?? '')) ?: null;
        $created_day = $created_at ? wp_date('Y-m-d', $created_at, wp_timezone()) : null;

        if (mazaq_browser_notifications_fallback_slot_is_visible($slot, $created_at, $created_day, $today, $now)) {
            $notifications[] = $feed[$slot];
        }
    }

    return array_slice($notifications, 0, 2);
}

/**
 * Build the stable id for one Push notification. Unknown types keep the
 * new-post shape, matching the builders this core was extracted from.
 */
function mazaq_browser_notifications_build_payload_id(string $type, int $post_id, string $today, int $timestamp): string
{
    if ($type === 'daily_random') {
        return sprintf('daily_random:%s:%d', $today, $post_id);
    }

    if ($type === 'test') {
        return sprintf('test:%d', $timestamp);
    }

    if ($type === 'test_daily_random') {
        return sprintf('test_daily_random:%d:%d', $post_id, $timestamp);
    }

    return sprintf('new_post:%d:%d', $post_id, $timestamp);
}

/**
 * Build a push/fallback notification payload for a post.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_build_payload(int $post_id, string $type, ?DateTimeInterface $created_at = null): array
{
    if ($post_id <= 0 || get_post_type($post_id) !== 'post' || get_post_status($post_id) !== 'publish') {
        return [];
    }

    $settings = mazaq_browser_notifications_get_settings();
    $post_title = trim(wp_strip_all_tags((string) get_the_title($post_id)));
    $post_title = $post_title !== '' ? $post_title : (string) __('مقال بدون عنوان', 'mazaq');
    $created_at = $created_at ?: current_datetime();
    $created_at_rfc3339 = $created_at->format(DATE_RFC3339);
    $image_url = get_the_post_thumbnail_url($post_id, 'card-thumbnail');

    if (!$image_url) {
        $image_url = $settings['default_icon_url'] ?: (get_template_directory_uri() . '/assets/images/logo.webp');
    }

    $body = $type === 'daily_random'
        ? sprintf(__('اقتراح اليوم: %s', 'mazaq'), $post_title)
        : sprintf(__('مقال جديد نُشر الآن: %s', 'mazaq'), $post_title);

    $title = $type === 'daily_random'
        ? (string) __('اقتراح يومي من مذاق السينما', 'mazaq')
        : (string) __('مقال جديد على مذاق السينما', 'mazaq');

    $id = mazaq_browser_notifications_build_payload_id($type, $post_id, mazaq_browser_notifications_today(), (int) strtotime($created_at_rfc3339));

    return [
        'id' => $id,
        'type' => $type,
        'postId' => $post_id,
        'title' => $title,
        'body' => $body,
        'url' => esc_url_raw((string) get_permalink($post_id)),
        'image' => esc_url_raw((string) $image_url),
        'createdAt' => $created_at_rfc3339,
        'icon' => esc_url_raw((string) ($settings['default_icon_url'] ?: $image_url)),
        'badge' => esc_url_raw((string) ($settings['default_badge_url'] ?: $image_url)),
    ];
}

/**
 * Build a one-off test notification payload.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_build_test_payload(): array
{
    $settings = mazaq_browser_notifications_get_settings();
    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $now = current_datetime();
    $image_url = $settings['default_icon_url'] ?: (get_template_directory_uri() . '/assets/images/logo.webp');

    return [
        'id' => mazaq_browser_notifications_build_payload_id('test', 0, '', time()),
        'type' => 'test',
        'postId' => 0,
        'title' => sprintf(__('تنبيه تجريبي من %s', 'mazaq'), $site_name),
        'body' => __('إذا وصلك هذا الإشعار، فهذا يعني أن نظام التنبيهات يعمل بشكل صحيح.', 'mazaq'),
        'url' => esc_url_raw(home_url('/')),
        'image' => esc_url_raw((string) $image_url),
        'createdAt' => $now->format(DATE_RFC3339),
        'icon' => esc_url_raw((string) ($settings['default_icon_url'] ?: $image_url)),
        'badge' => esc_url_raw((string) ($settings['default_badge_url'] ?: $image_url)),
    ];
}

/**
 * Build a preview payload using the daily random suggestion format without consuming the daily quota.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_build_daily_preview_payload(): array
{
    $post_id = mazaq_browser_notifications_pick_random_post_id();
    if ($post_id <= 0) {
        return [];
    }

    $payload = mazaq_browser_notifications_build_payload($post_id, 'daily_random');
    if (empty($payload)) {
        return [];
    }

    $payload['id'] = mazaq_browser_notifications_build_payload_id('test_daily_random', $post_id, '', time());
    $payload['type'] = 'test_daily_random';

    return $payload;
}

/**
 * File one payload into its feed slot. Test and typeless payloads leave the
 * state untouched so admin test sends never clobber Recent notifications.
 *
 * @param array<string, mixed> $state Feed state.
 * @param array<string, mixed> $payload Notification payload.
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_apply_feed_payload(array $state, array $payload): array
{
    $type = (string) ($payload['type'] ?? '');

    if ($type === 'daily_random') {
        $state['daily_random'] = $payload;
    } elseif ($type === 'new_post') {
        $state['new_post'] = $payload;
    }

    return $state;
}

/**
 * Save one payload into the feed state.
 *
 * @param array<string, mixed> $payload Notification payload.
 */
function mazaq_browser_notifications_store_feed_payload(array $payload): void
{
    if (empty($payload['type'])) {
        return;
    }

    mazaq_browser_notifications_update_feed_state(
        mazaq_browser_notifications_apply_feed_payload(
            mazaq_browser_notifications_get_feed_state(),
            $payload
        )
    );
}
