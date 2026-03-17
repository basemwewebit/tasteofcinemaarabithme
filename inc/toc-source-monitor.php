<?php

declare(strict_types=1);

/**
 * TasteOfCinema Source Monitor
 *
 * Monitors https://www.tasteofcinema.com/ RSS feed for new posts
 * and surfaces an admin-dashboard notice when one is detected.
 */

const TOC_MONITOR_CRON_HOOK   = 'toc_source_monitor_check';
const TOC_MONITOR_SEEN_OPTION  = 'toc_monitor_last_seen_url';
const TOC_MONITOR_PENDING_OPTION = 'toc_monitor_pending_posts';
const TOC_MONITOR_FEED_URL    = 'https://www.tasteofcinema.com/feed/';
const TOC_MONITOR_DISMISS_ACTION = 'toc_monitor_dismiss';
const TOC_MONITOR_DISMISS_NONCE = 'toc_monitor_dismiss_nonce';
const TOC_MONITOR_EMAIL_ENABLED_OPTION = 'toc_monitor_email_enabled';

// ---------------------------------------------------------------------------
// Cron scheduling
// ---------------------------------------------------------------------------

add_action('init', 'toc_monitor_schedule_cron');

function toc_monitor_schedule_cron(): void
{
    if (! wp_next_scheduled(TOC_MONITOR_CRON_HOOK)) {
        wp_schedule_event(time(), 'hourly', TOC_MONITOR_CRON_HOOK);
    }
}

add_action(TOC_MONITOR_CRON_HOOK, 'toc_monitor_fetch_feed');

// ---------------------------------------------------------------------------
// Feed fetcher
// ---------------------------------------------------------------------------

function toc_monitor_disable_feed_cache_lifetime(int $seconds): int
{
    return 0;
}

function toc_monitor_prepare_item_data($item): array
{
    if (!is_object($item) || !method_exists($item, 'get_permalink')) {
        return [];
    }

    $url = esc_url_raw((string) $item->get_permalink());
    if (empty($url)) {
        return [];
    }

    return [
        'url' => $url,
        'title' => sanitize_text_field((string) $item->get_title()),
        'date' => sanitize_text_field((string) $item->get_date('Y-m-d H:i:s')),
    ];
}

function toc_monitor_email_enabled(): bool
{
    $enabled = get_option(TOC_MONITOR_EMAIL_ENABLED_OPTION, '1');

    return '0' !== (string) $enabled;
}

function toc_monitor_send_email(array $new_posts): void
{
    if (empty($new_posts) || !toc_monitor_email_enabled()) {
        return;
    }

    $to = sanitize_email((string) get_option('admin_email'));
    if (empty($to)) {
        return;
    }

    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $count = count($new_posts);
    $subject = $count === 1
        ? sprintf(__('مقال جديد على TasteOfCinema - %s', 'mazaq'), $site_name)
        : sprintf(__('مقالات جديدة على TasteOfCinema (%d) - %s', 'mazaq'), $count, $site_name);

    $lines = [
        $count === 1
            ? __('تم رصد مقال جديد على TasteOfCinema:', 'mazaq')
            : sprintf(__('تم رصد %d مقالات جديدة على TasteOfCinema:', 'mazaq'), $count),
        '',
    ];

    foreach ($new_posts as $index => $post) {
        $title = isset($post['title']) ? (string) $post['title'] : '';
        $url = isset($post['url']) ? (string) $post['url'] : '';
        $date = isset($post['date']) ? (string) $post['date'] : '';

        $lines[] = sprintf('%d. %s', $index + 1, $title !== '' ? $title : $url);
        if ($url !== '') {
            $lines[] = sprintf(__('الرابط: %s', 'mazaq'), $url);
        }
        if ($date !== '') {
            $lines[] = sprintf(__('التاريخ: %s', 'mazaq'), $date);
        }
        $lines[] = '';
    }

    wp_mail($to, $subject, implode("\n", $lines));
}

function toc_monitor_fetch_feed(): void
{
    // Bypass SimplePie cache so we always get fresh data.
    add_filter('wp_feed_cache_transient_lifetime', 'toc_monitor_disable_feed_cache_lifetime');

    $feed = fetch_feed(TOC_MONITOR_FEED_URL);

    remove_filter('wp_feed_cache_transient_lifetime', 'toc_monitor_disable_feed_cache_lifetime');

    if (is_wp_error($feed)) {
        return;
    }

    $items = $feed->get_items(0, 20);
    if (empty($items)) {
        return;
    }

    $latest = toc_monitor_prepare_item_data($items[0]);
    if (empty($latest['url'])) {
        return;
    }

    $last_seen = get_option(TOC_MONITOR_SEEN_OPTION, '');
    $latest_url = (string) $latest['url'];

    if ($latest_url === $last_seen) {
        return;
    }

    // First run should initialize marker only to prevent noisy false positives.
    if (empty($last_seen)) {
        update_option(TOC_MONITOR_SEEN_OPTION, $latest_url, false);

        return;
    }

    $new_posts = [];
    foreach ($items as $item) {
        $post_data = toc_monitor_prepare_item_data($item);
        if (empty($post_data['url'])) {
            continue;
        }

        if ($post_data['url'] === $last_seen) {
            break;
        }

        $new_posts[] = $post_data;
    }

    if (empty($new_posts)) {
        update_option(TOC_MONITOR_SEEN_OPTION, $latest_url, false);

        return;
    }

    // New post detected – store for display and update the seen marker.
    $pending   = toc_monitor_get_pending();
    $pending = array_merge($pending, array_reverse($new_posts));

    // Keep only the latest 10 unseen posts to avoid unbounded growth.
    $pending = array_slice($pending, -10);

    update_option(TOC_MONITOR_SEEN_OPTION, $latest_url, false);
    update_option(TOC_MONITOR_PENDING_OPTION, $pending, false);

    toc_monitor_send_email(array_reverse($new_posts));
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function toc_monitor_get_pending(): array
{
    $raw = get_option(TOC_MONITOR_PENDING_OPTION, []);
    return is_array($raw) ? $raw : [];
}

// ---------------------------------------------------------------------------
// Admin notice
// ---------------------------------------------------------------------------

add_action('admin_notices', 'toc_monitor_admin_notice');

function toc_monitor_admin_notice(): void
{
    if (! current_user_can('edit_posts')) {
        return;
    }

    $pending = toc_monitor_get_pending();
    if (empty($pending)) {
        return;
    }

    $count = count($pending);
    $nonce = wp_create_nonce(TOC_MONITOR_DISMISS_NONCE);
    $email_enabled = toc_monitor_email_enabled();

    echo '<div class="notice notice-info is-dismissible" id="toc-monitor-notice">';
    echo '<p>';
    printf(
        '<strong>🎬 TasteOfCinema:</strong> %s',
        esc_html(
            $count === 1
                ? 'تم نشر مقال جديد على tasteofcinema.com'
                : sprintf('تم نشر %d مقالات جديدة على tasteofcinema.com', $count)
        )
    );
    echo '</p>';
    echo '<ul style="margin:.3em 0 .6em 1.5em;list-style:disc">';
    foreach (array_reverse($pending) as $post) {
        echo '<li>';
        printf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a> <span style="color:#888;font-size:.9em">(%s)</span>',
            esc_url($post['url']),
            esc_html($post['title'] ?: $post['url']),
            esc_html($post['date'])
        );
        echo '</li>';
    }
    echo '</ul>';
    printf(
        '<p><a href="%s" class="button button-secondary">%s</a></p>',
        esc_url(
            admin_url('admin-post.php?action=' . TOC_MONITOR_DISMISS_ACTION . '&_wpnonce=' . $nonce)
        ),
        esc_html__('تمييز كمقروء', 'mazaq')
    );
    echo '<p style="margin-top:0">';
    echo esc_html($email_enabled ? 'تنبيه الإيميل: مفعّل' : 'تنبيه الإيميل: غير مفعّل');
    echo '</p>';
    echo '</div>';
}

// ---------------------------------------------------------------------------
// Dismiss handler
// ---------------------------------------------------------------------------

add_action('admin_post_' . TOC_MONITOR_DISMISS_ACTION, 'toc_monitor_handle_dismiss');

function toc_monitor_handle_dismiss(): void
{
    if (
        ! current_user_can('edit_posts') ||
        ! check_admin_referer(TOC_MONITOR_DISMISS_NONCE)
    ) {
        wp_die(__('غير مصرح لك بهذا الإجراء.', 'mazaq'), 403);
    }

    delete_option(TOC_MONITOR_PENDING_OPTION);

    wp_safe_redirect(admin_url());
    exit;
}

// ---------------------------------------------------------------------------
// Cleanup on theme switch
// ---------------------------------------------------------------------------

add_action('switch_theme', 'toc_monitor_deactivate');

function toc_monitor_deactivate(): void
{
    $timestamp = wp_next_scheduled(TOC_MONITOR_CRON_HOOK);
    if ($timestamp) {
        wp_unschedule_event($timestamp, TOC_MONITOR_CRON_HOOK);
    }
}
