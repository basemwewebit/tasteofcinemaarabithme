<?php

/**
 * Notification store: subscription rows in, subscription rows out. Owns the
 * push table, its schema version, the save/read/counter interface, and the
 * per-subscription delivery quota. No hooks; the dispatcher, scheduler, web,
 * and admin modules call through this seam.
 */

declare(strict_types=1);

const MAZAQ_BROWSER_NOTIFICATIONS_SCHEMA_VERSION = '1.0.0';
const MAZAQ_BROWSER_NOTIFICATIONS_SCHEMA_OPTION = 'mazaq_browser_notifications_schema_version';

/**
 * Return the custom subscription table name.
 */
function mazaq_browser_notifications_table_name(): string
{
    global $wpdb;

    return $wpdb->prefix . 'mazaq_push_subscriptions';
}

/**
 * Create or update the subscription table.
 */
function mazaq_browser_notifications_maybe_upgrade_schema(): void
{
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
function mazaq_browser_notifications_subscription_is_eligible(array $subscription, string $type, ?string $today = null): bool
{
    if (str_starts_with($type, 'test')) {
        return true;
    }

    $today = $today ?? mazaq_browser_notifications_today();
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
