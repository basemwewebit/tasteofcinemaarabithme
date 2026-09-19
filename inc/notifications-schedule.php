<?php

/**
 * Notification schedule: the conductor. Owns the cron wiring, the candidate
 * pool behind the Daily suggestion, and the daily/new-post handlers that move
 * content through the dispatch seam. Thin over the pinned cores.
 */

declare(strict_types=1);

const MAZAQ_BROWSER_NOTIFICATIONS_IDS_TRANSIENT = 'mazaq_browser_notification_ids';
const MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT = 'mazaq_browser_notifications_daily_event';
const MAZAQ_BROWSER_NOTIFICATIONS_NEW_POST_EVENT = 'mazaq_browser_notifications_new_post_event';

/**
 * Calculate the next configured daily notification timestamp for WP-Cron.
 */
function mazaq_browser_notifications_next_daily_timestamp(): int
{
    $settings = mazaq_browser_notifications_get_settings();
    $time = mazaq_browser_notifications_normalize_daily_time((string) ($settings['daily_random_time'] ?? '09:00'));
    [$hours, $minutes] = array_map('intval', explode(':', $time));

    $timezone = wp_timezone();
    $now = new DateTimeImmutable('now', $timezone);
    $next = $now->setTime($hours, $minutes, 0);

    if ($next <= $now) {
        $next = $next->modify('+1 day');
    }

    return $next->getTimestamp();
}

/**
 * Schedule the next daily random notification event.
 */
function mazaq_browser_notifications_schedule_daily_event(): void
{
    $settings = mazaq_browser_notifications_get_settings();

    if (empty($settings['enabled']) || empty($settings['daily_random_enabled'])) {
        mazaq_browser_notifications_clear_event(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT);
        return;
    }

    if (!wp_next_scheduled(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT)) {
        wp_schedule_single_event(
            mazaq_browser_notifications_next_daily_timestamp(),
            MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT
        );
    }
}

/**
 * Unschedule all instances of an event hook.
 */
function mazaq_browser_notifications_clear_event(string $hook): void
{
    $cron = _get_cron_array();
    if (!is_array($cron)) {
        return;
    }

    foreach ($cron as $timestamp => $events) {
        if (!is_array($events) || empty($events[$hook]) || !is_array($events[$hook])) {
            continue;
        }

        foreach ($events[$hook] as $event) {
            $args = isset($event['args']) && is_array($event['args']) ? $event['args'] : [];
            wp_unschedule_event((int) $timestamp, $hook, $args);
        }
    }
}

/**
 * Bootstrap schema, options and schedules.
 */
function mazaq_browser_notifications_bootstrap(): void
{
    mazaq_browser_notifications_maybe_upgrade_schema();
    mazaq_browser_notifications_schedule_daily_event();
}
add_action('init', 'mazaq_browser_notifications_bootstrap', 20);

/**
 * Run one-time setup after theme switch.
 */
function mazaq_browser_notifications_after_switch_theme(): void
{
    mazaq_browser_notifications_bootstrap();
    flush_rewrite_rules(false);
}
add_action('after_switch_theme', 'mazaq_browser_notifications_after_switch_theme');

/**
 * Clean up cron events when leaving the theme.
 */
function mazaq_browser_notifications_cleanup(): void
{
    mazaq_browser_notifications_clear_event(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT);
    mazaq_browser_notifications_clear_event(MAZAQ_BROWSER_NOTIFICATIONS_NEW_POST_EVENT);
}
add_action('switch_theme', 'mazaq_browser_notifications_cleanup');

/**
 * Fetch all candidate published post IDs in batches and cache them.
 *
 * @return int[]
 */
function mazaq_browser_notifications_get_candidate_ids(): array
{
    $cached_ids = get_transient(MAZAQ_BROWSER_NOTIFICATIONS_IDS_TRANSIENT);
    if (is_array($cached_ids)) {
        return array_values(array_filter(array_map('intval', $cached_ids)));
    }

    $post_ids = mazaq_rotation_get_published_post_ids();
    set_transient(MAZAQ_BROWSER_NOTIFICATIONS_IDS_TRANSIENT, $post_ids, 30 * MINUTE_IN_SECONDS);

    return $post_ids;
}

/**
 * Pick one random published post.
 */
function mazaq_browser_notifications_pick_random_post_id(): int
{
    $candidate_ids = mazaq_browser_notifications_get_candidate_ids();

    if (empty($candidate_ids)) {
        return 0;
    }

    $random_index = wp_rand(0, count($candidate_ids) - 1);

    return (int) $candidate_ids[$random_index];
}

/**
 * Clear cached candidate IDs.
 */
function mazaq_browser_notifications_invalidate_ids_cache(): void
{
    delete_transient(MAZAQ_BROWSER_NOTIFICATIONS_IDS_TRANSIENT);
}

/**
 * Invalidate notification caches when posts change.
 */
function mazaq_browser_notifications_invalidate_ids_on_save(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    if ($post->post_type !== 'post') {
        return;
    }

    mazaq_browser_notifications_invalidate_ids_cache();
}
add_action('save_post', 'mazaq_browser_notifications_invalidate_ids_on_save', 10, 2);

/**
 * Invalidate cached IDs after a post is deleted.
 */
function mazaq_browser_notifications_invalidate_ids_on_delete(int $post_id): void
{
    if (get_post_type($post_id) !== 'post') {
        return;
    }

    mazaq_browser_notifications_invalidate_ids_cache();
}
add_action('deleted_post', 'mazaq_browser_notifications_invalidate_ids_on_delete');

/**
 * Invalidate cached IDs when post publish status changes.
 */
function mazaq_browser_notifications_invalidate_ids_on_transition(string $new_status, string $old_status, WP_Post $post): void
{
    if ($post->post_type !== 'post' || $new_status === $old_status) {
        return;
    }

    mazaq_browser_notifications_invalidate_ids_cache();
}
add_action('transition_post_status', 'mazaq_browser_notifications_invalidate_ids_on_transition', 10, 3);

/**
 * Send and store the daily random notification.
 */
function mazaq_browser_notifications_handle_daily_event(): void
{
    mazaq_browser_notifications_clear_event(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT);
    mazaq_browser_notifications_schedule_daily_event();

    $settings = mazaq_browser_notifications_get_settings();
    if (empty($settings['enabled']) || empty($settings['daily_random_enabled'])) {
        return;
    }

    $post_id = mazaq_browser_notifications_pick_random_post_id();
    if ($post_id <= 0) {
        return;
    }

    $payload = mazaq_browser_notifications_build_payload($post_id, 'daily_random');
    if (empty($payload)) {
        return;
    }

    mazaq_browser_notifications_store_feed_payload($payload);
    mazaq_browser_notifications_send_push_payload($payload);
}
add_action(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT, 'mazaq_browser_notifications_handle_daily_event');

/**
 * Queue a new-post push after publishing instead of sending inline.
 */
function mazaq_browser_notifications_queue_new_post_notification(string $new_status, string $old_status, WP_Post $post): void
{
    if ($post->post_type !== 'post') {
        return;
    }

    if ($new_status !== 'publish' || $old_status === 'publish') {
        return;
    }

    $settings = mazaq_browser_notifications_get_settings();
    if (empty($settings['enabled']) || empty($settings['new_post_enabled'])) {
        return;
    }

    $post_id = (int) $post->ID;
    if ($post_id <= 0 || wp_next_scheduled(MAZAQ_BROWSER_NOTIFICATIONS_NEW_POST_EVENT, [$post_id])) {
        return;
    }

    wp_schedule_single_event(time() + 15, MAZAQ_BROWSER_NOTIFICATIONS_NEW_POST_EVENT, [$post_id]);
}
add_action('transition_post_status', 'mazaq_browser_notifications_queue_new_post_notification', 20, 3);

/**
 * Send a queued new-post notification.
 */
function mazaq_browser_notifications_handle_new_post_event(int $post_id): void
{
    $settings = mazaq_browser_notifications_get_settings();
    if (empty($settings['enabled']) || empty($settings['new_post_enabled'])) {
        return;
    }

    if ($post_id <= 0 || get_post_type($post_id) !== 'post' || get_post_status($post_id) !== 'publish') {
        return;
    }

    $published_at = get_post_time(DATE_RFC3339, true, $post_id);
    $created_at = $published_at ? new DateTimeImmutable($published_at) : current_datetime();
    $payload = mazaq_browser_notifications_build_payload($post_id, 'new_post', $created_at);

    if (empty($payload)) {
        return;
    }

    mazaq_browser_notifications_store_feed_payload($payload);
    mazaq_browser_notifications_send_push_payload($payload);
}
add_action(MAZAQ_BROWSER_NOTIFICATIONS_NEW_POST_EVENT, 'mazaq_browser_notifications_handle_new_post_event');
