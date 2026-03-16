<?php

declare(strict_types=1);

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;

const MAZAQ_BROWSER_NOTIFICATIONS_SCHEMA_VERSION = '1.0.0';
const MAZAQ_BROWSER_NOTIFICATIONS_SCHEMA_OPTION = 'mazaq_browser_notifications_schema_version';
const MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION = 'mazaq_browser_notifications_settings';
const MAZAQ_BROWSER_NOTIFICATIONS_FEED_OPTION = 'mazaq_browser_notifications_feed';
const MAZAQ_BROWSER_NOTIFICATIONS_IDS_TRANSIENT = 'mazaq_browser_notification_ids';
const MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT = 'mazaq_browser_notifications_daily_event';
const MAZAQ_BROWSER_NOTIFICATIONS_NEW_POST_EVENT = 'mazaq_browser_notifications_new_post_event';
const MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_ACTION = 'mazaq_browser_notifications_save_settings';
const MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_NONCE = 'mazaq_browser_notifications_save_settings_nonce';
const MAZAQ_BROWSER_NOTIFICATIONS_TEST_ACTION = 'mazaq_browser_notifications_send_test';
const MAZAQ_BROWSER_NOTIFICATIONS_TEST_NONCE = 'mazaq_browser_notifications_send_test_nonce';
const MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_ACTION = 'mazaq_browser_notifications_send_daily_preview';
const MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_NONCE = 'mazaq_browser_notifications_send_daily_preview_nonce';

/**
 * Return the custom subscription table name.
 */
function mazaq_browser_notifications_table_name(): string
{
    global $wpdb;

    return $wpdb->prefix . 'mazaq_push_subscriptions';
}

/**
 * Default notification settings.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_default_settings(): array
{
    return [
        'enabled' => 1,
        'daily_random_enabled' => 1,
        'daily_random_time' => '09:00',
        'new_post_enabled' => 1,
        'vapid_public_key' => '',
        'vapid_private_key' => '',
        'default_icon_url' => get_template_directory_uri() . '/assets/images/logo.webp',
        'default_badge_url' => get_template_directory_uri() . '/assets/images/logo.png',
        'prompt_title' => __('اشترك في تنبيهات مذاق السينما', 'mazaq'),
        'prompt_body' => __('سنرسل لك مقالاً يومياً مختاراً ومقالات جديدة فور نشرها.', 'mazaq'),
    ];
}

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
 * Ensure settings and feed options exist with autoload disabled.
 */
function mazaq_browser_notifications_ensure_options(): void
{
    if (false === get_option(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION, false)) {
        add_option(
            MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION,
            mazaq_browser_notifications_default_settings(),
            '',
            'no'
        );
    }

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
 * Normalize settings from the database.
 *
 * @param mixed $settings Raw option value.
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_normalize_settings($settings): array
{
    $defaults = mazaq_browser_notifications_default_settings();
    $settings = is_array($settings) ? array_merge($defaults, $settings) : $defaults;

    $settings['enabled'] = !empty($settings['enabled']) ? 1 : 0;
    $settings['daily_random_enabled'] = !empty($settings['daily_random_enabled']) ? 1 : 0;
    $settings['daily_random_time'] = mazaq_browser_notifications_normalize_daily_time((string) $settings['daily_random_time']);
    $settings['new_post_enabled'] = !empty($settings['new_post_enabled']) ? 1 : 0;
    $settings['vapid_public_key'] = trim((string) $settings['vapid_public_key']);
    $settings['vapid_private_key'] = trim((string) $settings['vapid_private_key']);
    $settings['default_icon_url'] = esc_url_raw((string) $settings['default_icon_url']);
    $settings['default_badge_url'] = esc_url_raw((string) $settings['default_badge_url']);
    $settings['prompt_title'] = sanitize_text_field((string) $settings['prompt_title']);
    $settings['prompt_body'] = sanitize_textarea_field((string) $settings['prompt_body']);

    return $settings;
}

/**
 * Normalize a daily send time to HH:MM 24-hour format.
 */
function mazaq_browser_notifications_normalize_daily_time(string $time): string
{
    $time = trim($time);
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
        return '09:00';
    }

    return $time;
}

/**
 * Get normalized settings.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_get_settings(): array
{
    mazaq_browser_notifications_ensure_options();

    return mazaq_browser_notifications_normalize_settings(
        get_option(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION, [])
    );
}

/**
 * Persist settings without autoloading them.
 *
 * @param array<string, mixed> $settings Settings to save.
 */
function mazaq_browser_notifications_update_settings(array $settings): void
{
    mazaq_browser_notifications_ensure_options();
    update_option(
        MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION,
        mazaq_browser_notifications_normalize_settings($settings),
        false
    );
}

/**
 * Get the current feed state.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_get_feed_state(): array
{
    mazaq_browser_notifications_ensure_options();

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
    mazaq_browser_notifications_ensure_options();

    $normalized = array_merge(mazaq_browser_notifications_default_feed(), $state);
    $normalized['daily_random'] = is_array($normalized['daily_random']) ? $normalized['daily_random'] : null;
    $normalized['new_post'] = is_array($normalized['new_post']) ? $normalized['new_post'] : null;

    update_option(MAZAQ_BROWSER_NOTIFICATIONS_FEED_OPTION, $normalized, false);
}

/**
 * Create or update the subscription table.
 */
function mazaq_browser_notifications_maybe_upgrade_schema(): void
{
    mazaq_browser_notifications_ensure_options();

    $installed_version = (string) get_option(MAZAQ_BROWSER_NOTIFICATIONS_SCHEMA_OPTION, '');
    if ($installed_version === MAZAQ_BROWSER_NOTIFICATIONS_SCHEMA_VERSION) {
        return;
    }

    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table_name = mazaq_browser_notifications_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        endpoint_hash char(64) NOT NULL,
        endpoint text NOT NULL,
        public_key text NOT NULL,
        auth_token text NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        last_seen_at datetime NULL,
        last_sent_at datetime NULL,
        daily_sent_on date NULL,
        daily_window_date date NULL,
        daily_window_count smallint(5) unsigned NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY endpoint_hash (endpoint_hash),
        KEY status (status),
        KEY daily_window (status, daily_window_date, daily_window_count)
    ) {$charset_collate};";

    dbDelta($sql);

    update_option(MAZAQ_BROWSER_NOTIFICATIONS_SCHEMA_OPTION, MAZAQ_BROWSER_NOTIFICATIONS_SCHEMA_VERSION, false);
    flush_rewrite_rules(false);
}

/**
 * Determine whether the site should expose notification functionality.
 */
function mazaq_browser_notifications_is_enabled(): bool
{
    $settings = mazaq_browser_notifications_get_settings();

    return !empty($settings['enabled']);
}

/**
 * Determine whether push can be offered to browsers.
 */
function mazaq_browser_notifications_is_push_ready(): bool
{
    $settings = mazaq_browser_notifications_get_settings();
    $home_scheme = (string) wp_parse_url(home_url('/'), PHP_URL_SCHEME);

    return mazaq_browser_notifications_is_enabled()
        && $home_scheme === 'https'
        && $settings['vapid_public_key'] !== ''
        && $settings['vapid_private_key'] !== '';
}

/**
 * Get today's date in the site timezone.
 */
function mazaq_browser_notifications_today(): string
{
    return current_datetime()->format('Y-m-d');
}

/**
 * Register the root service worker rewrite.
 */
function mazaq_browser_notifications_add_rewrite_rule(): void
{
    add_rewrite_rule('^mazaq-sw\.js/?$', 'index.php?mazaq_sw=1', 'top');
}
add_action('init', 'mazaq_browser_notifications_add_rewrite_rule');

/**
 * Detect requests targeting the root service worker endpoint.
 */
function mazaq_browser_notifications_is_service_worker_request(): bool
{
    if ((int) get_query_var('mazaq_sw') === 1) {
        return true;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
    if ($request_uri === '') {
        return false;
    }

    $request_path = (string) wp_parse_url($request_uri, PHP_URL_PATH);

    return (bool) preg_match('#/mazaq-sw\.js/?$#', $request_path);
}

/**
 * Prevent canonical redirects from breaking service worker registration.
 *
 * @param string|false $redirect_url Canonical target.
 * @return string|false
 */
function mazaq_browser_notifications_disable_canonical_redirects($redirect_url)
{
    if (mazaq_browser_notifications_is_service_worker_request()) {
        return false;
    }

    return $redirect_url;
}
add_filter('redirect_canonical', 'mazaq_browser_notifications_disable_canonical_redirects');

/**
 * Register service worker query vars.
 *
 * @param string[] $vars Existing vars.
 * @return string[]
 */
function mazaq_browser_notifications_register_query_vars(array $vars): array
{
    $vars[] = 'mazaq_sw';

    return $vars;
}
add_filter('query_vars', 'mazaq_browser_notifications_register_query_vars');

/**
 * Handle service worker requests at the site root.
 */
function mazaq_browser_notifications_render_service_worker(): void
{
    if (!mazaq_browser_notifications_is_service_worker_request()) {
        return;
    }

    $settings = mazaq_browser_notifications_get_settings();

    nocache_headers();
    header('Content-Type: application/javascript; charset=UTF-8');
    header('Service-Worker-Allowed: /');

    $fallback_icon = $settings['default_icon_url'] ?: (get_template_directory_uri() . '/assets/images/logo.webp');
    $fallback_badge = $settings['default_badge_url'] ?: $fallback_icon;
    $fallback_title = __('مذاق السينما', 'mazaq');

    echo "self.addEventListener('install',function(event){event.waitUntil(self.skipWaiting());});\n";
    echo "self.addEventListener('activate',function(event){event.waitUntil(self.clients.claim());});\n";
    echo "self.addEventListener('push',function(event){\n";
    echo "  var payload={};\n";
    echo "  try{payload=event.data?event.data.json():{};}catch(error){payload={};}\n";
    echo '  var title=payload.title||' . wp_json_encode($fallback_title) . ";\n";
    echo "  var options={\n";
    echo "    body: payload.body || '',\n";
    echo "    icon: payload.icon || " . wp_json_encode($fallback_icon) . ",\n";
    echo "    badge: payload.badge || " . wp_json_encode($fallback_badge) . ",\n";
    echo "    image: payload.image || undefined,\n";
    echo "    data: {url: payload.url || '/', id: payload.id || ''}\n";
    echo "  };\n";
    echo "  event.waitUntil(self.registration.showNotification(title, options));\n";
    echo "});\n";
    echo "self.addEventListener('notificationclick',function(event){\n";
    echo "  event.notification.close();\n";
    echo "  var target=(event.notification && event.notification.data && event.notification.data.url) ? event.notification.data.url : '/';\n";
    echo "  event.waitUntil(clients.matchAll({type:'window',includeUncontrolled:true}).then(function(clientList){\n";
    echo "    for(var i=0;i<clientList.length;i++){if('focus' in clientList[i]){clientList[i].navigate(target);return clientList[i].focus();}}\n";
    echo "    if(clients.openWindow){return clients.openWindow(target);} return undefined;\n";
    echo "  }));\n";
    echo "});\n";

    exit;
}
add_action('template_redirect', 'mazaq_browser_notifications_render_service_worker');

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

    $post_ids = [];
    $page = 1;
    $per_page = 200;

    do {
        $query = new WP_Query([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $per_page,
            'paged'                  => $page,
            'fields'                 => 'ids',
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $batch_ids = array_values(array_filter(array_map('intval', (array) $query->posts)));
        if (empty($batch_ids)) {
            break;
        }

        $post_ids = array_merge($post_ids, $batch_ids);
        $page++;
        wp_reset_postdata();
    } while (count($batch_ids) === $per_page);

    $post_ids = array_values(array_unique($post_ids));
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

    $id = $type === 'daily_random'
        ? sprintf('daily_random:%s:%d', mazaq_browser_notifications_today(), $post_id)
        : sprintf('new_post:%d:%d', $post_id, strtotime($created_at_rfc3339));

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
        'id' => sprintf('test:%d', time()),
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

    $payload['id'] = sprintf('test_daily_random:%d:%d', $post_id, time());
    $payload['type'] = 'test_daily_random';

    return $payload;
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

    $state = mazaq_browser_notifications_get_feed_state();
    $type = (string) $payload['type'];

    if ($type === 'daily_random') {
        $state['daily_random'] = $payload;
    } elseif ($type === 'new_post') {
        $state['new_post'] = $payload;
    }

    mazaq_browser_notifications_update_feed_state($state);
}

/**
 * Return the current fallback feed for non-subscribed visitors.
 *
 * @return array<int, array<string, mixed>>
 */
function mazaq_browser_notifications_get_fallback_notifications(): array
{
    $feed = mazaq_browser_notifications_get_feed_state();
    $notifications = [];
    $today = mazaq_browser_notifications_today();
    $new_post_cutoff = time() - (7 * DAY_IN_SECONDS);

    if (is_array($feed['new_post'])) {
        $created_at = strtotime((string) ($feed['new_post']['createdAt'] ?? ''));
        if ($created_at && $created_at >= $new_post_cutoff) {
            $notifications[] = $feed['new_post'];
        }
    }

    if (is_array($feed['daily_random'])) {
        $daily_created = strtotime((string) ($feed['daily_random']['createdAt'] ?? ''));
        if ($daily_created && wp_date('Y-m-d', $daily_created, wp_timezone()) === $today) {
            $notifications[] = $feed['daily_random'];
        }
    }

    return array_slice($notifications, 0, 2);
}

/**
 * Get one batch of active subscriptions.
 *
 * @return array<int, array<string, mixed>>
 */
function mazaq_browser_notifications_get_subscription_batch(int $after_id = 0, int $limit = 100): array
{
    global $wpdb;

    $table_name = mazaq_browser_notifications_table_name();
    $sql = $wpdb->prepare(
        "SELECT id, endpoint_hash, endpoint, public_key, auth_token, status, daily_sent_on, daily_window_date, daily_window_count
         FROM {$table_name}
         WHERE status = %s AND id > %d
         ORDER BY id ASC
         LIMIT %d",
        'active',
        $after_id,
        $limit
    );

    $rows = $wpdb->get_results($sql, ARRAY_A);

    return is_array($rows) ? $rows : [];
}

/**
 * Determine whether a subscription can receive the payload.
 *
 * @param array<string, mixed> $subscription Subscription row.
 */
function mazaq_browser_notifications_subscription_is_eligible(array $subscription, string $type): bool
{
    if (str_starts_with($type, 'test')) {
        return true;
    }

    $today = mazaq_browser_notifications_today();
    $window_date = (string) ($subscription['daily_window_date'] ?? '');
    $window_count = (int) ($subscription['daily_window_count'] ?? 0);

    if ($window_date !== $today) {
        $window_count = 0;
    }

    if ($window_count >= 2) {
        return false;
    }

    if ($type === 'daily_random') {
        return (string) ($subscription['daily_sent_on'] ?? '') !== $today;
    }

    return true;
}

/**
 * Mark a subscription delivery as successful.
 *
 * @param array<string, mixed> $subscription Subscription row.
 */
function mazaq_browser_notifications_mark_delivery_success(array $subscription, string $type): void
{
    global $wpdb;

    $table_name = mazaq_browser_notifications_table_name();

    if (str_starts_with($type, 'test')) {
        $wpdb->update(
            $table_name,
            [
                'last_sent_at' => current_time('mysql', true),
                'updated_at' => current_time('mysql', true),
            ],
            ['id' => (int) $subscription['id']],
            ['%s', '%s'],
            ['%d']
        );

        return;
    }

    $today = mazaq_browser_notifications_today();
    $window_date = (string) ($subscription['daily_window_date'] ?? '');
    $window_count = (int) ($subscription['daily_window_count'] ?? 0);

    if ($window_date !== $today) {
        $window_count = 0;
    }

    $data = [
        'last_sent_at' => current_time('mysql', true),
        'daily_window_date' => $today,
        'daily_window_count' => min(2, $window_count + 1),
        'updated_at' => current_time('mysql', true),
    ];

    if ($type === 'daily_random') {
        $data['daily_sent_on'] = $today;
    }

    $format = ['%s', '%s', '%d', '%s'];
    if ($type === 'daily_random') {
        $format[] = '%s';
    }

    $wpdb->update(
        $table_name,
        $data,
        ['id' => (int) $subscription['id']],
        $format,
        ['%d']
    );
}

/**
 * Mark a subscription as invalid after a permanent push failure.
 */
function mazaq_browser_notifications_mark_subscription_invalid(string $endpoint_hash): void
{
    global $wpdb;

    $wpdb->update(
        mazaq_browser_notifications_table_name(),
        [
            'status' => 'invalid',
            'updated_at' => current_time('mysql', true),
        ],
        ['endpoint_hash' => $endpoint_hash],
        ['%s', '%s'],
        ['%s']
    );
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

            $reason = strtolower((string) (method_exists($report, 'getReason') ? $report->getReason() : ''));
            if (
                strpos($reason, '410') !== false
                || strpos($reason, '404') !== false
                || strpos($reason, 'expired') !== false
                || strpos($reason, 'unsubscribe') !== false
                || strpos($reason, 'invalid') !== false
            ) {
                mazaq_browser_notifications_mark_subscription_invalid((string) $subscription_row['endpoint_hash']);
                $result['invalidated']++;
            }
        }
    } while (!empty($batch));

    return $result;
}

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

/**
 * Upsert a browser subscription row.
 *
 * @param array<string, mixed> $subscription Subscription payload.
 */
function mazaq_browser_notifications_save_subscription(array $subscription): bool
{
    global $wpdb;

    $endpoint = esc_url_raw((string) ($subscription['endpoint'] ?? ''));
    $keys = isset($subscription['keys']) && is_array($subscription['keys']) ? $subscription['keys'] : [];
    $public_key = sanitize_text_field((string) ($keys['p256dh'] ?? ''));
    $auth_token = sanitize_text_field((string) ($keys['auth'] ?? ''));

    if ($endpoint === '' || $public_key === '' || $auth_token === '') {
        return false;
    }

    $endpoint_hash = hash('sha256', $endpoint);
    $table_name = mazaq_browser_notifications_table_name();
    $now = current_time('mysql', true);
    $existing_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE endpoint_hash = %s LIMIT 1",
            $endpoint_hash
        )
    );

    $data = [
        'endpoint_hash' => $endpoint_hash,
        'endpoint' => $endpoint,
        'public_key' => $public_key,
        'auth_token' => $auth_token,
        'status' => 'active',
        'last_seen_at' => $now,
        'updated_at' => $now,
    ];

    if ($existing_id > 0) {
        return false !== $wpdb->update(
            $table_name,
            $data,
            ['id' => $existing_id],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s'],
            ['%d']
        );
    }

    $data['created_at'] = $now;

    return false !== $wpdb->insert(
        $table_name,
        $data,
        ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
    );
}

/**
 * Mark a subscription as unsubscribed.
 */
function mazaq_browser_notifications_unsubscribe(string $endpoint): bool
{
    global $wpdb;

    $endpoint = esc_url_raw($endpoint);
    if ($endpoint === '') {
        return false;
    }

    return false !== $wpdb->update(
        mazaq_browser_notifications_table_name(),
        [
            'status' => 'unsubscribed',
            'updated_at' => current_time('mysql', true),
        ],
        ['endpoint_hash' => hash('sha256', $endpoint)],
        ['%s', '%s'],
        ['%s']
    );
}

/**
 * Return subscription counters for the admin screen.
 *
 * @return array<string, int>
 */
function mazaq_browser_notifications_get_subscription_counts(): array
{
    global $wpdb;

    $table_name = mazaq_browser_notifications_table_name();
    $table_exists = $wpdb->get_var(
        $wpdb->prepare('SHOW TABLES LIKE %s', $table_name)
    );

    if ($table_exists !== $table_name) {
        return [
            'total' => 0,
            'active' => 0,
            'unsubscribed' => 0,
            'invalid' => 0,
        ];
    }

    $rows = $wpdb->get_results(
        "SELECT status, COUNT(*) AS total FROM {$table_name} GROUP BY status",
        ARRAY_A
    );

    $counts = [
        'total' => 0,
        'active' => 0,
        'unsubscribed' => 0,
        'invalid' => 0,
    ];

    foreach ((array) $rows as $row) {
        $status = isset($row['status']) ? (string) $row['status'] : '';
        $count = isset($row['total']) ? (int) $row['total'] : 0;
        $counts['total'] += $count;

        if (isset($counts[$status])) {
            $counts[$status] = $count;
        }
    }

    return $counts;
}

/**
 * Return high-level runtime status values for the admin screen.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_get_runtime_status(): array
{
    $settings = mazaq_browser_notifications_get_settings();
    $home_url = home_url('/');
    $scheme = (string) wp_parse_url($home_url, PHP_URL_SCHEME);
    $next_daily_timestamp = wp_next_scheduled(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT);

    return [
        'home_url' => $home_url,
        'is_https' => $scheme === 'https',
        'push_ready' => mazaq_browser_notifications_is_push_ready(),
        'next_daily_timestamp' => $next_daily_timestamp ? (int) $next_daily_timestamp : 0,
        'daily_random_time' => (string) $settings['daily_random_time'],
        'service_worker_url' => home_url('/mazaq-sw.js'),
        'can_generate_keys' => class_exists(VAPID::class),
        'vapid_configured' => $settings['vapid_public_key'] !== '' && $settings['vapid_private_key'] !== '',
    ];
}

/**
 * Register REST endpoints for subscription management and bootstrap data.
 */
function mazaq_browser_notifications_register_rest_routes(): void
{
    register_rest_route('mazaq/v1', '/notifications/bootstrap', [
        'methods' => WP_REST_Server::READABLE,
        'permission_callback' => '__return_true',
        'callback' => function (): WP_REST_Response {
            $settings = mazaq_browser_notifications_get_settings();

            return new WP_REST_Response([
                'enabled' => !empty($settings['enabled']),
                'publicVapidKey' => mazaq_browser_notifications_is_push_ready() ? (string) $settings['vapid_public_key'] : '',
                'promptEligible' => mazaq_browser_notifications_is_push_ready(),
                'fallbackNotifications' => mazaq_browser_notifications_get_fallback_notifications(),
            ]);
        },
    ]);

    register_rest_route('mazaq/v1', '/notifications/subscription', [
        'methods' => WP_REST_Server::CREATABLE,
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request): WP_REST_Response {
            if (!mazaq_browser_notifications_is_enabled()) {
                return new WP_REST_Response([
                    'message' => __('ميزة التنبيهات غير مفعلة حالياً.', 'mazaq'),
                ], 400);
            }

            $saved = mazaq_browser_notifications_save_subscription((array) $request->get_json_params());

            return new WP_REST_Response([
                'success' => $saved,
            ], $saved ? 200 : 400);
        },
    ]);

    register_rest_route('mazaq/v1', '/notifications/subscription', [
        'methods' => WP_REST_Server::DELETABLE,
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request): WP_REST_Response {
            $params = (array) $request->get_json_params();
            $endpoint = isset($params['endpoint']) ? (string) $params['endpoint'] : '';
            $removed = mazaq_browser_notifications_unsubscribe($endpoint);

            return new WP_REST_Response([
                'success' => $removed,
            ], $removed ? 200 : 400);
        },
    ]);
}
add_action('rest_api_init', 'mazaq_browser_notifications_register_rest_routes');

/**
 * Render the shared prompt/toast markup.
 */
function mazaq_browser_notifications_render_markup(): void
{
    if (!mazaq_browser_notifications_is_enabled()) {
        return;
    }

    get_template_part('template-parts/common/browser-notifications', null, [
        'settings' => mazaq_browser_notifications_get_settings(),
    ]);
}
add_action('wp_footer', 'mazaq_browser_notifications_render_markup', 5);

/**
 * Add the admin settings page.
 */
function mazaq_browser_notifications_register_admin_page(): void
{
    add_theme_page(
        __('تنبيهات المتصفح', 'mazaq'),
        __('تنبيهات المتصفح', 'mazaq'),
        'manage_options',
        'mazaq-browser-notifications',
        'mazaq_browser_notifications_render_admin_page'
    );
}
add_action('admin_menu', 'mazaq_browser_notifications_register_admin_page');

/**
 * Save admin settings.
 */
function mazaq_browser_notifications_handle_settings_save(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('غير مسموح لك بتنفيذ هذا الإجراء.', 'mazaq'));
    }

    check_admin_referer(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_NONCE);

    $settings = mazaq_browser_notifications_get_settings();
    $settings['enabled'] = !empty($_POST['enabled']) ? 1 : 0;
    $settings['daily_random_enabled'] = !empty($_POST['daily_random_enabled']) ? 1 : 0;
    if (!empty($_POST['quick_toggle_daily_random'])) {
        $settings['daily_random_enabled'] = !empty($settings['daily_random_enabled']) ? 0 : 1;
    }
    $settings['daily_random_time'] = isset($_POST['daily_random_time'])
        ? mazaq_browser_notifications_normalize_daily_time(wp_unslash((string) $_POST['daily_random_time']))
        : (string) ($settings['daily_random_time'] ?? '09:00');
    $settings['new_post_enabled'] = !empty($_POST['new_post_enabled']) ? 1 : 0;
    $settings['vapid_public_key'] = isset($_POST['vapid_public_key']) ? sanitize_text_field(wp_unslash((string) $_POST['vapid_public_key'])) : '';
    $settings['vapid_private_key'] = isset($_POST['vapid_private_key']) ? sanitize_text_field(wp_unslash((string) $_POST['vapid_private_key'])) : '';
    $settings['default_icon_url'] = isset($_POST['default_icon_url']) ? esc_url_raw(wp_unslash((string) $_POST['default_icon_url'])) : '';
    $settings['default_badge_url'] = isset($_POST['default_badge_url']) ? esc_url_raw(wp_unslash((string) $_POST['default_badge_url'])) : '';
    $settings['prompt_title'] = isset($_POST['prompt_title']) ? sanitize_text_field(wp_unslash((string) $_POST['prompt_title'])) : '';
    $settings['prompt_body'] = isset($_POST['prompt_body']) ? sanitize_textarea_field(wp_unslash((string) $_POST['prompt_body'])) : '';

    if (!empty($_POST['generate_vapid_keys']) && class_exists(VAPID::class)) {
        $keys = VAPID::createVapidKeys();
        $settings['vapid_public_key'] = isset($keys['publicKey']) ? (string) $keys['publicKey'] : $settings['vapid_public_key'];
        $settings['vapid_private_key'] = isset($keys['privateKey']) ? (string) $keys['privateKey'] : $settings['vapid_private_key'];
    }

    mazaq_browser_notifications_update_settings($settings);
    mazaq_browser_notifications_clear_event(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT);
    mazaq_browser_notifications_schedule_daily_event();

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'mazaq-browser-notifications',
                'updated' => '1',
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_post_' . MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_ACTION, 'mazaq_browser_notifications_handle_settings_save');

/**
 * Send a one-off test notification to active subscriptions.
 */
function mazaq_browser_notifications_handle_test_send(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('غير مسموح لك بتنفيذ هذا الإجراء.', 'mazaq'));
    }

    check_admin_referer(MAZAQ_BROWSER_NOTIFICATIONS_TEST_NONCE);

    $result = mazaq_browser_notifications_send_push_payload(
        mazaq_browser_notifications_build_test_payload()
    );

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'mazaq-browser-notifications',
                'test_sent' => '1',
                'test_client_ready' => !empty($result['client_ready']) ? '1' : '0',
                'test_queued' => (string) (int) $result['queued'],
                'test_success' => (string) (int) $result['success'],
                'test_invalidated' => (string) (int) $result['invalidated'],
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_post_' . MAZAQ_BROWSER_NOTIFICATIONS_TEST_ACTION, 'mazaq_browser_notifications_handle_test_send');

/**
 * Send a preview of the daily random suggestion immediately.
 */
function mazaq_browser_notifications_handle_daily_preview_send(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('غير مسموح لك بتنفيذ هذا الإجراء.', 'mazaq'));
    }

    check_admin_referer(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_NONCE);

    $payload = mazaq_browser_notifications_build_daily_preview_payload();
    $result = empty($payload)
        ? ['client_ready' => mazaq_browser_notifications_is_push_ready(), 'queued' => 0, 'success' => 0, 'invalidated' => 0, 'has_payload' => false]
        : array_merge(
            mazaq_browser_notifications_send_push_payload($payload),
            ['has_payload' => true]
        );

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'mazaq-browser-notifications',
                'daily_preview_sent' => '1',
                'daily_preview_client_ready' => !empty($result['client_ready']) ? '1' : '0',
                'daily_preview_has_payload' => !empty($result['has_payload']) ? '1' : '0',
                'daily_preview_queued' => (string) (int) $result['queued'],
                'daily_preview_success' => (string) (int) $result['success'],
                'daily_preview_invalidated' => (string) (int) $result['invalidated'],
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_post_' . MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_ACTION, 'mazaq_browser_notifications_handle_daily_preview_send');

/**
 * Render the admin settings screen.
 */
function mazaq_browser_notifications_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mazaq_browser_notifications_get_settings();
    $counts = mazaq_browser_notifications_get_subscription_counts();
    $runtime = mazaq_browser_notifications_get_runtime_status();
    $is_updated = isset($_GET['updated']) && $_GET['updated'] === '1';
    $is_test_sent = isset($_GET['test_sent']) && $_GET['test_sent'] === '1';
    $test_client_ready = isset($_GET['test_client_ready']) && $_GET['test_client_ready'] === '1';
    $test_queued = isset($_GET['test_queued']) ? (int) $_GET['test_queued'] : 0;
    $test_success = isset($_GET['test_success']) ? (int) $_GET['test_success'] : 0;
    $test_invalidated = isset($_GET['test_invalidated']) ? (int) $_GET['test_invalidated'] : 0;
    $is_daily_preview_sent = isset($_GET['daily_preview_sent']) && $_GET['daily_preview_sent'] === '1';
    $daily_preview_client_ready = isset($_GET['daily_preview_client_ready']) && $_GET['daily_preview_client_ready'] === '1';
    $daily_preview_has_payload = isset($_GET['daily_preview_has_payload']) && $_GET['daily_preview_has_payload'] === '1';
    $daily_preview_queued = isset($_GET['daily_preview_queued']) ? (int) $_GET['daily_preview_queued'] : 0;
    $daily_preview_success = isset($_GET['daily_preview_success']) ? (int) $_GET['daily_preview_success'] : 0;
    $daily_preview_invalidated = isset($_GET['daily_preview_invalidated']) ? (int) $_GET['daily_preview_invalidated'] : 0;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('تنبيهات المتصفح', 'mazaq'); ?></h1>

        <?php if ($is_updated) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('تم حفظ إعدادات التنبيهات.', 'mazaq'); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($is_test_sent) : ?>
            <div class="notice <?php echo $test_client_ready ? 'notice-info' : 'notice-warning'; ?> is-dismissible">
                <p>
                    <?php
                    echo esc_html(
                        $test_client_ready
                            ? sprintf(
                                __('تم إرسال التنبيه التجريبي. تم وضع %1$d اشتراك في الإرسال، ونجح %2$d، وتم تعطيل %3$d غير صالح.', 'mazaq'),
                                $test_queued,
                                $test_success,
                                $test_invalidated
                            )
                            : __('لم يتم إرسال التنبيه التجريبي لأن Push غير جاهز بعد. تأكد من HTTPS ومفاتيح VAPID.', 'mazaq')
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($is_daily_preview_sent) : ?>
            <div class="notice <?php echo ($daily_preview_client_ready && $daily_preview_has_payload) ? 'notice-info' : 'notice-warning'; ?> is-dismissible">
                <p>
                    <?php
                    if (!$daily_preview_has_payload) {
                        echo esc_html__('لم يتم إرسال تجربة الاقتراح اليومي لأنه لا يوجد مقال منشور صالح للاختيار.', 'mazaq');
                    } elseif (!$daily_preview_client_ready) {
                        echo esc_html__('لم يتم إرسال تجربة الاقتراح اليومي لأن Push غير جاهز بعد. تأكد من HTTPS ومفاتيح VAPID.', 'mazaq');
                    } else {
                        echo esc_html(
                            sprintf(
                                __('تم إرسال تجربة الاقتراح اليومي. تم وضع %1$d اشتراك في الإرسال، ونجح %2$d، وتم تعطيل %3$d غير صالح.', 'mazaq'),
                                $daily_preview_queued,
                                $daily_preview_success,
                                $daily_preview_invalidated
                            )
                        );
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin:20px 0 24px;">
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('المشتركون النشطون', 'mazaq'); ?></div>
                <div style="font-size:28px;font-weight:700;"><?php echo esc_html((string) $counts['active']); ?></div>
            </div>
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('إجمالي السجلات', 'mazaq'); ?></div>
                <div style="font-size:28px;font-weight:700;"><?php echo esc_html((string) $counts['total']); ?></div>
            </div>
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('حالة Push', 'mazaq'); ?></div>
                <div style="font-size:16px;font-weight:600;color:<?php echo $runtime['push_ready'] ? '#008a20' : '#b32d2e'; ?>;">
                    <?php echo esc_html($runtime['push_ready'] ? __('جاهز', 'mazaq') : __('غير مكتمل', 'mazaq')); ?>
                </div>
            </div>
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('التنبيه اليومي القادم', 'mazaq'); ?></div>
                <div style="font-size:16px;font-weight:600;">
                    <?php
                    echo esc_html(
                        $runtime['next_daily_timestamp'] > 0
                            ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $runtime['next_daily_timestamp'], wp_timezone())
                            : __('غير مجدول', 'mazaq')
                    );
                    ?>
                </div>
            </div>
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('الاقتراح اليومي العشوائي', 'mazaq'); ?></div>
                <div style="font-size:16px;font-weight:600;">
                    <?php
                    echo esc_html(
                        sprintf(
                            __('إرسال اقتراح عشوائي يومي الساعة %s', 'mazaq'),
                            (string) $runtime['daily_random_time']
                        )
                    );
                    ?>
                </div>
                <div style="margin-top:8px;font-size:13px;color:<?php echo !empty($settings['daily_random_enabled']) ? '#008a20' : '#646970'; ?>;">
                    <?php echo esc_html(!empty($settings['daily_random_enabled']) ? __('مفعّل حالياً', 'mazaq') : __('غير مفعّل حالياً', 'mazaq')); ?>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;">
                    <input type="hidden" name="action" value="<?php echo esc_attr(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_ACTION); ?>">
                    <input type="hidden" name="enabled" value="<?php echo esc_attr((string) (int) !empty($settings['enabled'])); ?>">
                    <input type="hidden" name="daily_random_enabled" value="<?php echo esc_attr((string) (int) !empty($settings['daily_random_enabled'])); ?>">
                    <input type="hidden" name="daily_random_time" value="<?php echo esc_attr((string) $settings['daily_random_time']); ?>">
                    <input type="hidden" name="new_post_enabled" value="<?php echo esc_attr((string) (int) !empty($settings['new_post_enabled'])); ?>">
                    <input type="hidden" name="vapid_public_key" value="<?php echo esc_attr((string) $settings['vapid_public_key']); ?>">
                    <input type="hidden" name="vapid_private_key" value="<?php echo esc_attr((string) $settings['vapid_private_key']); ?>">
                    <input type="hidden" name="default_icon_url" value="<?php echo esc_attr((string) $settings['default_icon_url']); ?>">
                    <input type="hidden" name="default_badge_url" value="<?php echo esc_attr((string) $settings['default_badge_url']); ?>">
                    <input type="hidden" name="prompt_title" value="<?php echo esc_attr((string) $settings['prompt_title']); ?>">
                    <input type="hidden" name="prompt_body" value="<?php echo esc_attr((string) $settings['prompt_body']); ?>">
                    <?php wp_nonce_field(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_NONCE); ?>
                    <button type="submit" name="quick_toggle_daily_random" value="1" class="button button-secondary">
                        <?php echo esc_html(!empty($settings['daily_random_enabled']) ? __('إيقاف الآن', 'mazaq') : __('تشغيل الآن', 'mazaq')); ?>
                    </button>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:10px;">
                    <input type="hidden" name="action" value="<?php echo esc_attr(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_ACTION); ?>">
                    <?php wp_nonce_field(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_NONCE); ?>
                    <button type="submit" class="button button-secondary"><?php esc_html_e('إرسال الاقتراح اليومي الآن كتجربة', 'mazaq'); ?></button>
                </form>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;margin-bottom:24px;">
            <p style="margin:0 0 10px;"><strong><?php esc_html_e('رابط الموقع:', 'mazaq'); ?></strong> <code><?php echo esc_html((string) $runtime['home_url']); ?></code></p>
            <p style="margin:0 0 10px;"><strong><?php esc_html_e('رابط Service Worker:', 'mazaq'); ?></strong> <code><?php echo esc_html((string) $runtime['service_worker_url']); ?></code></p>
            <p style="margin:0 0 10px;"><strong><?php esc_html_e('HTTPS:', 'mazaq'); ?></strong> <?php echo esc_html($runtime['is_https'] ? __('مفعل', 'mazaq') : __('غير مفعل', 'mazaq')); ?></p>
            <p style="margin:0;"><strong><?php esc_html_e('مفاتيح VAPID:', 'mazaq'); ?></strong> <?php echo esc_html($runtime['vapid_configured'] ? __('موجودة', 'mazaq') : __('غير مضبوطة', 'mazaq')); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:14px;">
                <input type="hidden" name="action" value="<?php echo esc_attr(MAZAQ_BROWSER_NOTIFICATIONS_TEST_ACTION); ?>">
                <?php wp_nonce_field(MAZAQ_BROWSER_NOTIFICATIONS_TEST_NONCE); ?>
                <button type="submit" class="button button-primary"><?php esc_html_e('إرسال تنبيه تجريبي الآن', 'mazaq'); ?></button>
            </form>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_ACTION); ?>">
            <?php wp_nonce_field(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_NONCE); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('تفعيل النظام', 'mazaq'); ?></th>
                        <td><label><input type="checkbox" name="enabled" value="1" <?php checked(!empty($settings['enabled'])); ?>> <?php esc_html_e('تفعيل التنبيهات للزوار', 'mazaq'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('التنبيه اليومي', 'mazaq'); ?></th>
                        <td>
                            <label><input type="checkbox" name="daily_random_enabled" value="1" <?php checked(!empty($settings['daily_random_enabled'])); ?>> <?php esc_html_e('تفعيل إرسال الاقتراح العشوائي اليومي', 'mazaq'); ?></label>
                            <p class="description"><?php esc_html_e('سيتم إرسال اقتراح عشوائي واحد يوميًا حسب الوقت المحدد أدناه.', 'mazaq'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-daily-random-time"><?php esc_html_e('وقت الإرسال اليومي', 'mazaq'); ?></label></th>
                        <td>
                            <input id="mazaq-daily-random-time" class="regular-text" type="time" name="daily_random_time" value="<?php echo esc_attr((string) $settings['daily_random_time']); ?>" step="60">
                            <p class="description"><?php esc_html_e('صيغة 24 ساعة حسب المنطقة الزمنية المضبوطة في ووردبريس.', 'mazaq'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('تنبيهات المقالات الجديدة', 'mazaq'); ?></th>
                        <td><label><input type="checkbox" name="new_post_enabled" value="1" <?php checked(!empty($settings['new_post_enabled'])); ?>> <?php esc_html_e('إرسال تنبيه عند نشر مقال جديد', 'mazaq'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-vapid-public"><?php esc_html_e('VAPID Public Key', 'mazaq'); ?></label></th>
                        <td>
                            <textarea id="mazaq-vapid-public" class="large-text code" rows="3" name="vapid_public_key"><?php echo esc_textarea((string) $settings['vapid_public_key']); ?></textarea>
                            <p class="description"><?php esc_html_e('المفتاح العام الذي يستخدمه المتصفح أثناء الاشتراك.', 'mazaq'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-vapid-private"><?php esc_html_e('VAPID Private Key', 'mazaq'); ?></label></th>
                        <td>
                            <textarea id="mazaq-vapid-private" class="large-text code" rows="3" name="vapid_private_key"><?php echo esc_textarea((string) $settings['vapid_private_key']); ?></textarea>
                            <p class="description"><?php esc_html_e('المفتاح الخاص المستخدم للإرسال من السيرفر. لا تشاركه علنًا.', 'mazaq'); ?></p>
                            <?php if (!empty($runtime['can_generate_keys'])) : ?>
                                <p style="margin-top:10px;">
                                    <button type="submit" name="generate_vapid_keys" value="1" class="button button-secondary"><?php esc_html_e('توليد مفاتيح جديدة تلقائيًا', 'mazaq'); ?></button>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-icon-url"><?php esc_html_e('رابط أيقونة التنبيه', 'mazaq'); ?></label></th>
                        <td><input id="mazaq-icon-url" class="regular-text code" type="url" name="default_icon_url" value="<?php echo esc_attr((string) $settings['default_icon_url']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-badge-url"><?php esc_html_e('رابط Badge التنبيه', 'mazaq'); ?></label></th>
                        <td><input id="mazaq-badge-url" class="regular-text code" type="url" name="default_badge_url" value="<?php echo esc_attr((string) $settings['default_badge_url']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-prompt-title"><?php esc_html_e('عنوان طلب الاشتراك', 'mazaq'); ?></label></th>
                        <td><input id="mazaq-prompt-title" class="regular-text" type="text" name="prompt_title" value="<?php echo esc_attr((string) $settings['prompt_title']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-prompt-body"><?php esc_html_e('نص طلب الاشتراك', 'mazaq'); ?></label></th>
                        <td><textarea id="mazaq-prompt-body" class="large-text" rows="4" name="prompt_body"><?php echo esc_textarea((string) $settings['prompt_body']); ?></textarea></td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(__('حفظ الإعدادات', 'mazaq')); ?>
        </form>

        <div style="margin-top:24px;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
            <h2 style="margin-top:0;"><?php esc_html_e('ملخص الاشتراكات', 'mazaq'); ?></h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('الحالة', 'mazaq'); ?></th>
                        <th><?php esc_html_e('العدد', 'mazaq'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php esc_html_e('نشط', 'mazaq'); ?></td>
                        <td><?php echo esc_html((string) $counts['active']); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('ألغى الاشتراك', 'mazaq'); ?></td>
                        <td><?php echo esc_html((string) $counts['unsubscribed']); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('غير صالح', 'mazaq'); ?></td>
                        <td><?php echo esc_html((string) $counts['invalid']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('الإجمالي', 'mazaq'); ?></strong></td>
                        <td><strong><?php echo esc_html((string) $counts['total']); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
