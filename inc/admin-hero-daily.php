<?php

declare(strict_types=1);

const MAZAQ_HERO_DAILY_EVENT = 'mazaq_hero_daily_rotation';
const MAZAQ_HERO_DAILY_OPTION = 'mazaq_hero_daily_state';
const MAZAQ_HERO_DAILY_REGENERATE_ACTION = 'mazaq_hero_daily_regenerate';
const MAZAQ_HERO_DAILY_REGENERATE_NONCE = 'mazaq_hero_daily_regenerate_nonce';

/**
 * Default state structure for hero daily rotation
 */
function mazaq_hero_daily_default_state(): array
{
    return [
        'rotation_date' => '',
        'hero_post_ids' => [],
        'used_post_ids' => [],
    ];
}

/**
 * Ensure the option exists in database
 */
function mazaq_hero_daily_ensure_option_exists(): void
{
    if (false === get_option(MAZAQ_HERO_DAILY_OPTION, false)) {
        add_option(MAZAQ_HERO_DAILY_OPTION, mazaq_hero_daily_default_state(), '', 'no');
    }
}

/**
 * Normalize state data to ensure correct types
 */
function mazaq_hero_daily_normalize_state($state): array
{
    $defaults = mazaq_hero_daily_default_state();
    $state = is_array($state) ? array_merge($defaults, $state) : $defaults;

    $state['rotation_date'] = is_string($state['rotation_date']) ? $state['rotation_date'] : '';

    $state['hero_post_ids'] = array_values(array_unique(array_filter(array_map('intval', (array) $state['hero_post_ids']))));
    $state['used_post_ids'] = array_values(array_unique(array_filter(array_map('intval', (array) $state['used_post_ids']))));

    return $state;
}

/**
 * Get current state from database
 */
function mazaq_hero_daily_get_state(): array
{
    mazaq_hero_daily_ensure_option_exists();

    return mazaq_hero_daily_normalize_state(get_option(MAZAQ_HERO_DAILY_OPTION, []));
}

/**
 * Update state in database
 */
function mazaq_hero_daily_update_state(array $state): void
{
    mazaq_hero_daily_ensure_option_exists();
    update_option(MAZAQ_HERO_DAILY_OPTION, mazaq_hero_daily_normalize_state($state), false);
}

/**
 * Get today's date string
 */
function mazaq_hero_daily_today(): string
{
    return current_datetime()->format('Y-m-d');
}

/**
 * Get all published post IDs
 */
function mazaq_hero_daily_get_published_post_ids(): array
{
    $ids = [];
    $page = 1;
    $page_size = 200;

    do {
        $query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => $page_size,
            'paged' => $page,
            'orderby' => 'ID',
            'order' => 'ASC',
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $page_ids = array_map('intval', $query->posts);
        if (!empty($page_ids)) {
            $ids = array_merge($ids, $page_ids);
        }

        $page++;
    } while (count($page_ids) === $page_size);

    return array_values(array_unique($ids));
}

/**
 * Pick random IDs from array
 */
function mazaq_hero_daily_pick_random_ids(array $ids, int $count): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (empty($ids) || $count <= 0) {
        return [];
    }

    shuffle($ids);

    return array_slice($ids, 0, min($count, count($ids)));
}

/**
 * Check if post is publishable
 */
function mazaq_hero_daily_is_post_publishable(int $post_id): bool
{
    return $post_id > 0 && 'publish' === get_post_status($post_id) && 'post' === get_post_type($post_id);
}

/**
 * Clean up state by removing non-published posts
 */
function mazaq_hero_daily_prune_state(array $state, array $published_ids): array
{
    $published_lookup = array_fill_keys($published_ids, true);

    $state['hero_post_ids'] = array_values(array_filter(
        $state['hero_post_ids'],
        static fn (int $post_id): bool => isset($published_lookup[$post_id])
    ));

    $state['used_post_ids'] = array_values(array_filter(
        $state['used_post_ids'],
        static fn (int $post_id): bool => isset($published_lookup[$post_id])
    ));

    return $state;
}

/**
 * Generate a batch of 2-3 random hero posts
 */
function mazaq_hero_daily_generate_batch(array $published_ids, array $used_ids): array
{
    $published_ids = array_values(array_unique(array_filter(array_map('intval', $published_ids))));
    $used_ids = array_values(array_unique(array_intersect($published_ids, array_map('intval', $used_ids))));

    if (empty($published_ids)) {
        return [
            'hero_post_ids' => [],
            'used_post_ids' => [],
        ];
    }

    // Determine batch size (2-3 posts)
    $batch_size = count($published_ids) >= 3 ? 3 : (count($published_ids) >= 2 ? 2 : 1);

    $available_ids = array_values(array_diff($published_ids, $used_ids));

    // If we have enough unused posts, use them
    if (count($available_ids) >= $batch_size) {
        $hero_post_ids = mazaq_hero_daily_pick_random_ids($available_ids, $batch_size);

        return [
            'hero_post_ids' => $hero_post_ids,
            'used_post_ids' => array_values(array_unique(array_merge($used_ids, $hero_post_ids))),
        ];
    }

    // If not enough unused posts, reset and start fresh cycle
    if (count($available_ids) < $batch_size) {
        $hero_post_ids = mazaq_hero_daily_pick_random_ids($published_ids, $batch_size);

        return [
            'hero_post_ids' => $hero_post_ids,
            'used_post_ids' => $hero_post_ids,
        ];
    }

    // Fallback
    $hero_post_ids = mazaq_hero_daily_pick_random_ids($published_ids, $batch_size);

    return [
        'hero_post_ids' => $hero_post_ids,
        'used_post_ids' => $hero_post_ids,
    ];
}

/**
 * Prepare today's batch of hero posts
 */
function mazaq_hero_daily_prepare_today_batch(bool $force_regenerate = false): array
{
    $today = mazaq_hero_daily_today();
    $state = mazaq_hero_daily_get_state();
    $original_hero_ids = $state['hero_post_ids'];
    $published_ids = mazaq_hero_daily_get_published_post_ids();
    $has_invalid_hero_posts = !empty(array_diff($original_hero_ids, $published_ids));

    $state = mazaq_hero_daily_prune_state($state, $published_ids);

    $should_generate_new_batch = $force_regenerate || $state['rotation_date'] !== $today || $has_invalid_hero_posts;

    if ($should_generate_new_batch) {
        $batch = mazaq_hero_daily_generate_batch($published_ids, $state['used_post_ids']);
        $state['rotation_date'] = $today;
        $state['hero_post_ids'] = $batch['hero_post_ids'];
        $state['used_post_ids'] = $batch['used_post_ids'];
    }

    mazaq_hero_daily_update_state($state);

    return $state;
}

/**
 * Schedule daily cron event
 */
function mazaq_hero_daily_schedule_event(): void
{
    mazaq_hero_daily_ensure_option_exists();

    if (!wp_next_scheduled(MAZAQ_HERO_DAILY_EVENT)) {
        // Schedule to run at midnight
        wp_schedule_event(strtotime('tomorrow midnight'), 'daily', MAZAQ_HERO_DAILY_EVENT);
    }
}

/**
 * Clear scheduled cron event
 */
function mazaq_hero_daily_clear_event(): void
{
    $timestamp = wp_next_scheduled(MAZAQ_HERO_DAILY_EVENT);

    while ($timestamp) {
        wp_unschedule_event($timestamp, MAZAQ_HERO_DAILY_EVENT);
        $timestamp = wp_next_scheduled(MAZAQ_HERO_DAILY_EVENT);
    }
}

/**
 * Handle cron job - generate new hero posts for the day
 */
function mazaq_hero_daily_handle_cron(): void
{
    mazaq_hero_daily_prepare_today_batch(true);
}
add_action(MAZAQ_HERO_DAILY_EVENT, 'mazaq_hero_daily_handle_cron');

/**
 * Bootstrap schedule on theme activation/init
 */
function mazaq_hero_daily_bootstrap_schedule(): void
{
    mazaq_hero_daily_schedule_event();
}
add_action('after_switch_theme', 'mazaq_hero_daily_bootstrap_schedule');
add_action('init', 'mazaq_hero_daily_bootstrap_schedule');

/**
 * Cleanup schedule when theme is switched
 */
function mazaq_hero_daily_cleanup_schedule(): void
{
    mazaq_hero_daily_clear_event();
}
add_action('switch_theme', 'mazaq_hero_daily_cleanup_schedule');

/**
 * Admin notice showing today's hero posts
 */
function mazaq_hero_daily_render_notice(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $state = mazaq_hero_daily_get_state();
    if (empty($state['hero_post_ids'])) {
        return;
    }

    echo '<div class="notice notice-info is-dismissible" dir="rtl" style="text-align:right">';
    echo '<p><strong>' . esc_html__('مشاركات Hero لهذا اليوم', 'mazaq') . '</strong></p>';

    $posts = get_posts([
        'post_type' => 'post',
        'post__in' => $state['hero_post_ids'],
        'posts_per_page' => -1,
    ]);

    if (!empty($posts)) {
        echo '<ul style="margin-right:1.5em;margin-left:0;list-style:disc">';

        foreach ($posts as $post) {
            $view_link = get_permalink($post);
            $edit_link = get_edit_post_link($post->ID, '');

            echo '<li>';
            echo esc_html(get_the_title($post));
            echo ' - <a href="' . esc_url($view_link) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('عرض', 'mazaq') . '</a>';

            if (!empty($edit_link)) {
                echo ' | <a href="' . esc_url($edit_link) . '">' . esc_html__('تعديل', 'mazaq') . '</a>';
            }

            echo '</li>';
        }

        echo '</ul>';
    }

    $regenerate_url = wp_nonce_url(
        admin_url('admin-post.php?action=' . MAZAQ_HERO_DAILY_REGENERATE_ACTION),
        MAZAQ_HERO_DAILY_REGENERATE_NONCE
    );

    echo '<p><a href="' . esc_url($regenerate_url) . '" class="button button-secondary">' . esc_html__('🔄 تجديد مشاركات Hero', 'mazaq') . '</a></p>';
    echo '</div>';
}
add_action('admin_notices', 'mazaq_hero_daily_render_notice');

/**
 * Handle manual regeneration action
 */
function mazaq_hero_daily_handle_regenerate(): void
{
    if (
        !current_user_can('manage_options') ||
        !check_admin_referer(MAZAQ_HERO_DAILY_REGENERATE_NONCE)
    ) {
        wp_die(__('غير مصرح لك بهذا الإجراء.', 'mazaq'), 403);
    }

    mazaq_hero_daily_prepare_today_batch(true);

    wp_safe_redirect(admin_url());
    exit;
}
add_action('admin_post_' . MAZAQ_HERO_DAILY_REGENERATE_ACTION, 'mazaq_hero_daily_handle_regenerate');
