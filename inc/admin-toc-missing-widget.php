<?php

declare(strict_types=1);

/**
 * Dashboard widget: random TasteOfCinema posts missing locally by slug.
 */

const MAZAQ_TOC_MISSING_WIDGET_ID = 'mazaq-toc-missing-widget';
const MAZAQ_TOC_MISSING_WIDGET_OPTION = 'mazaq_toc_missing_widget_state';
const MAZAQ_TOC_MISSING_FEED_TRANSIENT = 'mazaq_toc_missing_feed_items_v1';
const MAZAQ_TOC_MISSING_FEED_TRANSIENT_TTL = 15 * MINUTE_IN_SECONDS;
const MAZAQ_TOC_MISSING_BATCH_SIZE = 5;
const MAZAQ_TOC_MISSING_FEED_URL = 'https://www.tasteofcinema.com/feed/';
const MAZAQ_TOC_MISSING_REFRESH_ACTION = 'mazaq_toc_missing_refresh';
const MAZAQ_TOC_MISSING_REFRESH_NONCE = 'mazaq_toc_missing_refresh_nonce';

function mazaq_toc_missing_default_state(): array
{
    return [
        'items' => [],
        'updated_at' => '',
    ];
}

function mazaq_toc_missing_normalize_item($raw_item): array
{
    if (!is_array($raw_item)) {
        return [];
    }

    $url = esc_url_raw((string) ($raw_item['url'] ?? ''));
    $slug = sanitize_title((string) ($raw_item['slug'] ?? ''));
    $title = sanitize_text_field((string) ($raw_item['title'] ?? ''));
    $date = sanitize_text_field((string) ($raw_item['date'] ?? ''));

    if ('' === $url || '' === $slug) {
        return [];
    }

    if ('' === $title) {
        $title = $url;
    }

    return [
        'title' => $title,
        'url' => $url,
        'slug' => $slug,
        'date' => $date,
    ];
}

function mazaq_toc_missing_normalize_items(array $raw_items): array
{
    $items = [];
    $seen = [];

    foreach ($raw_items as $raw_item) {
        $item = mazaq_toc_missing_normalize_item($raw_item);
        if (empty($item)) {
            continue;
        }

        $slug = $item['slug'];
        if (isset($seen[$slug])) {
            continue;
        }

        $seen[$slug] = true;
        $items[] = $item;
    }

    return $items;
}

function mazaq_toc_missing_get_state(): array
{
    $defaults = mazaq_toc_missing_default_state();
    $state = get_option(MAZAQ_TOC_MISSING_WIDGET_OPTION, []);
    $state = is_array($state) ? array_merge($defaults, $state) : $defaults;

    $state['updated_at'] = is_string($state['updated_at']) ? $state['updated_at'] : '';
    $state['items'] = mazaq_toc_missing_normalize_items((array) ($state['items'] ?? []));

    return $state;
}

function mazaq_toc_missing_update_state(array $state): void
{
    $normalized = mazaq_toc_missing_default_state();
    $normalized['updated_at'] = is_string($state['updated_at'] ?? null) ? $state['updated_at'] : '';
    $normalized['items'] = mazaq_toc_missing_normalize_items((array) ($state['items'] ?? []));

    update_option(MAZAQ_TOC_MISSING_WIDGET_OPTION, $normalized, false);
}

function mazaq_toc_missing_extract_slug_from_url(string $url): string
{
    $path = (string) parse_url($url, PHP_URL_PATH);
    $path = trim($path, '/');

    if ('' === $path) {
        return '';
    }

    $segments = array_values(array_filter(explode('/', $path)));
    if (empty($segments)) {
        return '';
    }

    $last_segment = (string) end($segments);

    return sanitize_title(rawurldecode($last_segment));
}

function mazaq_toc_missing_prepare_feed_item($item): array
{
    if (!is_object($item) || !method_exists($item, 'get_permalink')) {
        return [];
    }

    $url = esc_url_raw((string) $item->get_permalink());
    if ('' === $url) {
        return [];
    }

    $slug = mazaq_toc_missing_extract_slug_from_url($url);
    if ('' === $slug) {
        return [];
    }

    return [
        'title' => sanitize_text_field((string) $item->get_title()),
        'url' => $url,
        'slug' => $slug,
        'date' => sanitize_text_field((string) $item->get_date('Y-m-d H:i:s')),
    ];
}

function mazaq_toc_missing_fetch_feed_items(bool $force_refresh = false): array
{
    $cached_items = get_transient(MAZAQ_TOC_MISSING_FEED_TRANSIENT);

    if (!$force_refresh && is_array($cached_items) && !empty($cached_items)) {
        return mazaq_toc_missing_normalize_items($cached_items);
    }

    if (!function_exists('fetch_feed')) {
        require_once ABSPATH . WPINC . '/feed.php';
    }

    $feed = fetch_feed(MAZAQ_TOC_MISSING_FEED_URL);
    if (is_wp_error($feed)) {
        return is_array($cached_items) ? mazaq_toc_missing_normalize_items($cached_items) : [];
    }

    $items = $feed->get_items(0, 120);
    if (empty($items)) {
        return is_array($cached_items) ? mazaq_toc_missing_normalize_items($cached_items) : [];
    }

    $prepared_items = [];

    foreach ($items as $item) {
        $prepared = mazaq_toc_missing_prepare_feed_item($item);
        if (!empty($prepared)) {
            $prepared_items[] = $prepared;
        }
    }

    $prepared_items = mazaq_toc_missing_normalize_items($prepared_items);

    if (!empty($prepared_items)) {
        set_transient(
            MAZAQ_TOC_MISSING_FEED_TRANSIENT,
            $prepared_items,
            MAZAQ_TOC_MISSING_FEED_TRANSIENT_TTL
        );
    }

    return $prepared_items;
}

function mazaq_toc_missing_get_existing_post_slugs(array $slugs): array
{
    global $wpdb;

    $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', $slugs))));
    if (empty($slugs)) {
        return [];
    }

    $placeholders = implode(', ', array_fill(0, count($slugs), '%s'));
    $params = array_merge(['post', 'publish'], $slugs);

    $query = $wpdb->prepare(
        "SELECT post_name FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_name IN ({$placeholders})",
        $params
    );

    if (!is_string($query) || '' === $query) {
        return [];
    }

    $rows = $wpdb->get_col($query);
    if (!is_array($rows)) {
        return [];
    }

    return array_values(array_unique(array_filter(array_map('sanitize_title', $rows))));
}

function mazaq_toc_missing_filter_items(array $items, array $exclude_slugs = []): array
{
    $items = mazaq_toc_missing_normalize_items($items);
    if (empty($items)) {
        return [];
    }

    $exclude_slugs = array_values(array_unique(array_filter(array_map('sanitize_title', $exclude_slugs))));
    $exclude_lookup = array_fill_keys($exclude_slugs, true);

    $candidate_slugs = [];
    foreach ($items as $item) {
        if (!isset($exclude_lookup[$item['slug']])) {
            $candidate_slugs[] = $item['slug'];
        }
    }

    $existing_lookup = array_fill_keys(mazaq_toc_missing_get_existing_post_slugs($candidate_slugs), true);

    $filtered = [];
    foreach ($items as $item) {
        $slug = $item['slug'];
        if (isset($exclude_lookup[$slug]) || isset($existing_lookup[$slug])) {
            continue;
        }

        $filtered[] = $item;
    }

    return $filtered;
}

function mazaq_toc_missing_pick_random_items(array $items, int $count): array
{
    $items = mazaq_toc_missing_normalize_items($items);
    if (empty($items) || $count <= 0) {
        return [];
    }

    shuffle($items);

    return array_slice($items, 0, min($count, count($items)));
}

function mazaq_toc_missing_prepare_batch(bool $force_refresh = false): array
{
    $state = mazaq_toc_missing_get_state();
    $current_items = mazaq_toc_missing_filter_items((array) ($state['items'] ?? []));
    $feed_items = mazaq_toc_missing_fetch_feed_items($force_refresh);

    if ($force_refresh) {
        $current_slugs = array_column($current_items, 'slug');
        $candidates = mazaq_toc_missing_filter_items($feed_items, $current_slugs);
        $batch = mazaq_toc_missing_pick_random_items($candidates, MAZAQ_TOC_MISSING_BATCH_SIZE);

        if (count($batch) < MAZAQ_TOC_MISSING_BATCH_SIZE) {
            $fallback_candidates = mazaq_toc_missing_filter_items($feed_items);
            $fallback_batch = mazaq_toc_missing_pick_random_items($fallback_candidates, MAZAQ_TOC_MISSING_BATCH_SIZE);
            if (count($fallback_batch) > count($batch)) {
                $batch = $fallback_batch;
            }
        }

        $state['items'] = $batch;
        $state['updated_at'] = current_datetime()->format('Y-m-d H:i:s');
        mazaq_toc_missing_update_state($state);

        return $state;
    }

    if (count($current_items) >= MAZAQ_TOC_MISSING_BATCH_SIZE) {
        $state['items'] = array_slice($current_items, 0, MAZAQ_TOC_MISSING_BATCH_SIZE);
        $state['updated_at'] = current_datetime()->format('Y-m-d H:i:s');
        mazaq_toc_missing_update_state($state);

        return $state;
    }

    $needed = MAZAQ_TOC_MISSING_BATCH_SIZE - count($current_items);
    $exclude_slugs = array_column($current_items, 'slug');
    $candidates = mazaq_toc_missing_filter_items($feed_items, $exclude_slugs);
    $new_items = mazaq_toc_missing_pick_random_items($candidates, $needed);

    $state['items'] = array_merge($current_items, $new_items);
    $state['updated_at'] = current_datetime()->format('Y-m-d H:i:s');

    mazaq_toc_missing_update_state($state);

    return $state;
}

function mazaq_toc_missing_register_dashboard_widget(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_add_dashboard_widget(
        MAZAQ_TOC_MISSING_WIDGET_ID,
        __('مقالات عشوائية من TasteOfCinema غير موجودة محلياً', 'mazaq'),
        'mazaq_toc_missing_render_widget'
    );
}
add_action('wp_dashboard_setup', 'mazaq_toc_missing_register_dashboard_widget');

function mazaq_toc_missing_render_widget(): void
{
    $state = mazaq_toc_missing_prepare_batch(false);
    $items = (array) ($state['items'] ?? []);

    $refresh_url = wp_nonce_url(
        admin_url('admin-post.php?action=' . MAZAQ_TOC_MISSING_REFRESH_ACTION),
        MAZAQ_TOC_MISSING_REFRESH_NONCE
    );

    echo '<div class="mazaq-toc-missing-widget">';
    echo '<p>' . esc_html__('يعرض هذا الصندوق 5 روابط من المصدر الخارجي غير المنشورة لدينا (اعتماداً على slug).', 'mazaq') . '</p>';

    if (empty($items)) {
        echo '<p><strong>' . esc_html__('لا توجد حالياً عناصر مرشحة من RSS أو أن كل العناصر موجودة محلياً.', 'mazaq') . '</strong></p>';
    } else {
        echo '<ul style="margin-right:1.2em;margin-left:0;list-style:disc">';
        foreach ($items as $item) {
            $title = (string) ($item['title'] ?? '');
            $url = (string) ($item['url'] ?? '');
            $slug = (string) ($item['slug'] ?? '');

            if ('' === $url || '' === $slug) {
                continue;
            }

            echo '<li>';
            echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($title !== '' ? $title : $url) . '</a>';
            echo '<br /><span style="color:#666;font-size:12px">slug: ' . esc_html($slug) . '</span>';
            echo '</li>';
        }
        echo '</ul>';
    }

    echo '<p style="margin-top:10px">';
    echo '<a href="' . esc_url($refresh_url) . '" class="button button-secondary">' . esc_html__('🔄 Refresh 5 مواد جديدة', 'mazaq') . '</a>';
    echo '</p>';

    if (!empty($state['updated_at'])) {
        echo '<p style="margin-top:8px;color:#666;font-size:12px">';
        echo esc_html(sprintf(__('آخر تحديث: %s', 'mazaq'), $state['updated_at']));
        echo '</p>';
    }

    echo '</div>';
}

function mazaq_toc_missing_handle_refresh(): void
{
    if (
        !current_user_can('manage_options') ||
        !check_admin_referer(MAZAQ_TOC_MISSING_REFRESH_NONCE)
    ) {
        wp_die(__('غير مصرح لك بهذا الإجراء.', 'mazaq'), 403);
    }

    $state = mazaq_toc_missing_prepare_batch(true);
    $count = count((array) ($state['items'] ?? []));

    wp_safe_redirect(
        add_query_arg(
            [
                'mazaq_toc_missing_refreshed' => '1',
                'mazaq_toc_missing_count' => (string) $count,
            ],
            admin_url('index.php')
        )
    );
    exit;
}
add_action('admin_post_' . MAZAQ_TOC_MISSING_REFRESH_ACTION, 'mazaq_toc_missing_handle_refresh');

function mazaq_toc_missing_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $refreshed = isset($_GET['mazaq_toc_missing_refreshed'])
        ? sanitize_text_field(wp_unslash((string) $_GET['mazaq_toc_missing_refreshed']))
        : '';

    if ('1' !== $refreshed) {
        return;
    }

    $count = isset($_GET['mazaq_toc_missing_count'])
        ? absint(wp_unslash((string) $_GET['mazaq_toc_missing_count']))
        : 0;

    echo '<div class="notice notice-success is-dismissible"><p>';
    if ($count > 0) {
        echo esc_html(sprintf(__('تم تحديث الويدجت. عدد العناصر الحالية: %d', 'mazaq'), $count));
    } else {
        echo esc_html__('تم تنفيذ التحديث، لكن لا توجد عناصر مرشحة حالياً.', 'mazaq');
    }
    echo '</p></div>';
}
add_action('admin_notices', 'mazaq_toc_missing_admin_notice');
