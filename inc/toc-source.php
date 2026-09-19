<?php

declare(strict_types=1);

/**
 * TasteOfCinema source module.
 *
 * The one fetcher + normalizer for the remote tasteofcinema.com feed behind
 * the source-monitor and missing-widget seam. Every row carries the unified
 * {url, slug, title, date} shape.
 *
 * @package Mazaq
 */

if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}

const MAZAQ_TOC_SOURCE_FEED_URL = 'https://www.tasteofcinema.com/feed/';
const MAZAQ_TOC_SOURCE_FEED_TRANSIENT = 'mazaq_toc_source_feed_items_v1';
const MAZAQ_TOC_SOURCE_FEED_TRANSIENT_TTL = 15 * MINUTE_IN_SECONDS;

/**
 * Derive the post slug from a feed permalink: last path segment, decoded.
 */
function mazaq_toc_source_extract_slug_from_url(string $url): string
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

/**
 * Coerce one raw row to the unified shape. Rejects rows without url + slug.
 *
 * @return array{title: string, url: string, slug: string, date: string}|array{}
 */
function mazaq_toc_source_normalize_item($raw_item): array
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

/**
 * Prepare one SimplePie item to the unified shape, deriving its slug.
 *
 * @return array{title: string, url: string, slug: string, date: string}|array{}
 */
function mazaq_toc_source_prepare_feed_item($item): array
{
    if (!is_object($item) || !method_exists($item, 'get_permalink')) {
        return [];
    }

    $url = esc_url_raw((string) $item->get_permalink());
    if ('' === $url) {
        return [];
    }

    $slug = mazaq_toc_source_extract_slug_from_url($url);
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

function mazaq_toc_source_disable_feed_cache_lifetime(int $seconds): int
{
    return 0;
}

/**
 * Fetch normalized feed rows. The cache key includes the limit so consumers
 * asking for different batch sizes never serve each other truncated rows.
 *
 * @return array<int, array{title: string, url: string, slug: string, date: string}>
 */
function mazaq_toc_source_fetch_feed_items(int $limit, bool $force_refresh = false): array
{
    $transient_key = MAZAQ_TOC_SOURCE_FEED_TRANSIENT . '_' . max(0, $limit);
    $cached_items = get_transient($transient_key);

    if (!$force_refresh && is_array($cached_items) && !empty($cached_items)) {
        return mazaq_toc_source_normalize_items($cached_items);
    }

    if (!function_exists('fetch_feed')) {
        require_once ABSPATH . WPINC . '/feed.php';
    }

    if ($force_refresh) {
        // Bypass SimplePie cache so forced refreshes get genuinely fresh data.
        add_filter('wp_feed_cache_transient_lifetime', 'mazaq_toc_source_disable_feed_cache_lifetime');
    }

    $feed = fetch_feed(MAZAQ_TOC_SOURCE_FEED_URL);

    if ($force_refresh) {
        remove_filter('wp_feed_cache_transient_lifetime', 'mazaq_toc_source_disable_feed_cache_lifetime');
    }

    if (is_wp_error($feed)) {
        return is_array($cached_items) ? mazaq_toc_source_normalize_items($cached_items) : [];
    }

    $items = $feed->get_items(0, $limit);
    if (empty($items)) {
        return is_array($cached_items) ? mazaq_toc_source_normalize_items($cached_items) : [];
    }

    $prepared_items = [];

    foreach ($items as $item) {
        $prepared = mazaq_toc_source_prepare_feed_item($item);
        if (!empty($prepared)) {
            $prepared_items[] = $prepared;
        }
    }

    $prepared_items = mazaq_toc_source_normalize_items($prepared_items);

    if (!empty($prepared_items)) {
        set_transient(
            $transient_key,
            $prepared_items,
            MAZAQ_TOC_SOURCE_FEED_TRANSIENT_TTL
        );
    }

    return $prepared_items;
}

/**
 * Normalize rows, dropping rejects and collapsing duplicate slugs (first wins).
 *
 * @return array<int, array{title: string, url: string, slug: string, date: string}>
 */
function mazaq_toc_source_normalize_items(array $raw_items): array
{
    $items = [];
    $seen = [];

    foreach ($raw_items as $raw_item) {
        $item = mazaq_toc_source_normalize_item($raw_item);
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
