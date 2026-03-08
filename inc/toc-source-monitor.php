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

// ---------------------------------------------------------------------------
// Cron scheduling
// ---------------------------------------------------------------------------

add_action('wp', 'toc_monitor_schedule_cron');

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

function toc_monitor_fetch_feed(): void
{
    // Bypass SimplePie cache so we always get fresh data.
    add_filter('wp_feed_cache_transient_lifetime', fn() => 0);

    $feed = fetch_feed(TOC_MONITOR_FEED_URL);

    remove_filter('wp_feed_cache_transient_lifetime', fn() => 0);

    if (is_wp_error($feed)) {
        return;
    }

    $latest = $feed->get_item(0);
    if (! $latest) {
        return;
    }

    $url   = esc_url_raw($latest->get_permalink());
    $title = sanitize_text_field($latest->get_title());
    $date  = $latest->get_date('Y-m-d H:i:s');

    $last_seen = get_option(TOC_MONITOR_SEEN_OPTION, '');

    if ($url === $last_seen || empty($url)) {
        return;
    }

    // New post detected – store for display and update the seen marker.
    $pending   = toc_monitor_get_pending();
    $pending[] = [
        'url'   => $url,
        'title' => $title,
        'date'  => $date,
    ];

    // Keep only the latest 10 unseen posts to avoid unbounded growth.
    $pending = array_slice($pending, -10);

    update_option(TOC_MONITOR_SEEN_OPTION, $url, false);
    update_option(TOC_MONITOR_PENDING_OPTION, $pending, false);
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
