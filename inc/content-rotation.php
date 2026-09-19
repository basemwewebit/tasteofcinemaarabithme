<?php

/**
 * Content rotation engine: the daily no-repeat batch mechanic behind the hero
 * and social features. One deep module parameterized by a feature config:
 *
 *   [
 *     'date_key'     => 'rotation_date', // State key holding the batch date.
 *     'batch_key'    => 'hero_post_ids', // State key holding today's batch.
 *     'extra_state'  => [],              // Extra state keys with defaults.
 *   ]
 *
 * The WP-wired half (state store, pool reader, scheduler, widget) hangs off
 * the same config; see below. This file registers no hooks itself: each
 * feature file calls mazaq_rotation_register_feature() with its own config.
 */

declare(strict_types=1);

/**
 * Default state structure for a rotation feature.
 */
function mazaq_rotation_default_state(array $feature): array
{
    return array_merge(
        [
            $feature['date_key'] => '',
            $feature['batch_key'] => [],
            'used_post_ids' => [],
        ],
        $feature['extra_state'] ?? []
    );
}

/**
 * Normalize state data to ensure correct types.
 */
function mazaq_rotation_normalize_state(array $feature, $state): array
{
    $defaults = mazaq_rotation_default_state($feature);
    $state = is_array($state) ? array_merge($defaults, $state) : $defaults;

    $state[$feature['date_key']] = is_string($state[$feature['date_key']]) ? $state[$feature['date_key']] : '';

    $state[$feature['batch_key']] = array_values(array_unique(array_filter(array_map('intval', (array) $state[$feature['batch_key']]))));
    $state['used_post_ids'] = array_values(array_unique(array_filter(array_map('intval', (array) $state['used_post_ids']))));

    foreach ($feature['extra_state'] ?? [] as $extra_key => $extra_default) {
        if (is_string($extra_default)) {
            $state[$extra_key] = is_string($state[$extra_key] ?? null) ? $state[$extra_key] : '';
        }
    }

    return $state;
}

/**
 * Pick random IDs from an array.
 */
function mazaq_rotation_pick_random_ids(array $ids, int $count): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (empty($ids) || $count <= 0) {
        return [];
    }

    shuffle($ids);

    return array_slice($ids, 0, min($count, count($ids)));
}

/**
 * Clean up state by removing non-published posts.
 */
function mazaq_rotation_prune_state(array $feature, array $state, array $published_ids): array
{
    $published_lookup = array_fill_keys($published_ids, true);

    $state[$feature['batch_key']] = array_values(array_filter(
        $state[$feature['batch_key']],
        static fn (int $post_id): bool => isset($published_lookup[$post_id])
    ));

    $state['used_post_ids'] = array_values(array_filter(
        $state['used_post_ids'],
        static fn (int $post_id): bool => isset($published_lookup[$post_id])
    ));

    return $state;
}

/**
 * Generate a no-repeat batch from the published pool, restarting the cycle
 * once every id has been used.
 *
 * @return array{batch_ids: int[], used_ids: int[]}
 */
function mazaq_rotation_generate_batch(array $published_ids, array $used_ids, int $batch_size): array
{
    $published_ids = array_values(array_unique(array_filter(array_map('intval', $published_ids))));
    $used_ids = array_values(array_unique(array_intersect($published_ids, array_map('intval', $used_ids))));
    $batch_size = max(1, $batch_size);

    if (empty($published_ids)) {
        return [
            'batch_ids' => [],
            'used_ids' => [],
        ];
    }

    $batch_size = min($batch_size, count($published_ids));

    $available_ids = array_values(array_diff($published_ids, $used_ids));

    if (count($available_ids) >= $batch_size) {
        $batch_ids = mazaq_rotation_pick_random_ids($available_ids, $batch_size);

        return [
            'batch_ids' => $batch_ids,
            'used_ids' => array_values(array_unique(array_merge($used_ids, $batch_ids))),
        ];
    }

    $batch_ids = $available_ids;

    if (count($batch_ids) < $batch_size) {
        $needed = $batch_size - count($batch_ids);
        $new_cycle_pool = array_values(array_diff($published_ids, $batch_ids));
        $extra_picks = mazaq_rotation_pick_random_ids($new_cycle_pool, $needed);
        $batch_ids = array_values(array_unique(array_merge($batch_ids, $extra_picks)));
    }

    if (count($batch_ids) < $batch_size) {
        $needed = $batch_size - count($batch_ids);
        $remaining_pool = array_values(array_diff($published_ids, $batch_ids));
        $final_picks = mazaq_rotation_pick_random_ids($remaining_pool, $needed);
        $batch_ids = array_values(array_unique(array_merge($batch_ids, $final_picks)));
    }

    return [
        'batch_ids' => $batch_ids,
        'used_ids' => $batch_ids,
    ];
}

/**
 * Resolve a feature's runtime config from the shared settings page.
 *
 * @return array{enabled: bool, batch_size: int}
 */
function mazaq_rotation_get_config(array $feature): array
{
    if (!function_exists('mazaq_content_rotation_get_settings')) {
        return [
            'enabled' => true,
            'batch_size' => 3,
        ];
    }

    $settings = mazaq_content_rotation_get_settings();

    return [
        'enabled' => !empty($settings[$feature['settings_enabled_key']]),
        'batch_size' => max(1, min($feature['max_count'], (int) $settings[$feature['settings_count_key']])),
    ];
}

/**
 * Ensure the feature's state option exists in the database.
 */
function mazaq_rotation_ensure_option_exists(array $feature): void
{
    if (false === get_option($feature['option'], false)) {
        add_option($feature['option'], mazaq_rotation_default_state($feature), '', 'no');
    }
}

/**
 * Get the feature's current state from the database.
 */
function mazaq_rotation_get_state(array $feature): array
{
    mazaq_rotation_ensure_option_exists($feature);

    return mazaq_rotation_normalize_state($feature, get_option($feature['option'], []));
}

/**
 * Update the feature's state in the database.
 */
function mazaq_rotation_update_state(array $feature, array $state): void
{
    mazaq_rotation_ensure_option_exists($feature);
    update_option($feature['option'], mazaq_rotation_normalize_state($feature, $state), false);
}

/**
 * Get today's date string.
 */
function mazaq_rotation_today(): string
{
    return current_datetime()->format('Y-m-d');
}

/**
 * Check if a post is publishable.
 */
function mazaq_rotation_is_post_publishable(int $post_id): bool
{
    return $post_id > 0 && 'publish' === get_post_status($post_id) && 'post' === get_post_type($post_id);
}

/**
 * Scan every published post id in pages. Extra WP_Query args merge in for
 * narrower pools (e.g. ['cat' => $category_id]).
 *
 * @return int[]
 */
function mazaq_rotation_get_published_post_ids(array $extra_args = []): array
{
    $ids = [];
    $page = 1;
    $page_size = 200;

    do {
        $query = new WP_Query(array_merge(
            [
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
            ],
            $extra_args
        ));

        $page_ids = array_map('intval', $query->posts);
        if (!empty($page_ids)) {
            $ids = array_merge($ids, $page_ids);
        }

        $page++;
    } while (count($page_ids) === $page_size);

    return array_values(array_unique($ids));
}

/**
 * Prepare the feature's batch for today, regenerating when forced, stale, or
 * holding unpublished ids.
 */
function mazaq_rotation_prepare_today_batch(array $feature, bool $force_regenerate = false): array
{
    $config = mazaq_rotation_get_config($feature);
    $today = mazaq_rotation_today();
    $state = mazaq_rotation_get_state($feature);

    if (!$config['enabled']) {
        return $state;
    }

    $original_batch_ids = $state[$feature['batch_key']];
    $published_ids = mazaq_rotation_get_published_post_ids();
    $has_invalid_batch_posts = !empty(array_diff($original_batch_ids, $published_ids));

    $state = mazaq_rotation_prune_state($feature, $state, $published_ids);

    $should_generate_new_batch = $force_regenerate || $state[$feature['date_key']] !== $today || $has_invalid_batch_posts;

    if ($should_generate_new_batch) {
        $batch = mazaq_rotation_generate_batch($published_ids, $state['used_post_ids'], (int) $config['batch_size']);
        $state[$feature['date_key']] = $today;
        $state[$feature['batch_key']] = $batch['batch_ids'];
        $state['used_post_ids'] = $batch['used_ids'];
    }

    mazaq_rotation_update_state($feature, $state);

    return $state;
}

/**
 * Serve the feature's batch for today, generating lazily on a miss. This is
 * the narrow read seam for frontend callers: no state keys leak out.
 *
 * @return int[]
 */
function mazaq_rotation_get_today_batch_ids(array $feature): array
{
    $config = mazaq_rotation_get_config($feature);
    if (!$config['enabled']) {
        return [];
    }

    $state = mazaq_rotation_get_state($feature);
    if (!empty($state[$feature['batch_key']]) && $state[$feature['date_key']] === mazaq_rotation_today()) {
        return $state[$feature['batch_key']];
    }

    $state = mazaq_rotation_prepare_today_batch($feature, false);

    return $state[$feature['batch_key']] ?? [];
}

/**
 * Schedule the feature's daily cron event.
 */
function mazaq_rotation_schedule_event(array $feature): void
{
    mazaq_rotation_ensure_option_exists($feature);
    $config = mazaq_rotation_get_config($feature);

    if (!$config['enabled']) {
        mazaq_rotation_clear_event($feature);

        return;
    }

    $hour = function_exists('mazaq_content_rotation_get_daily_hour')
        ? mazaq_content_rotation_get_daily_hour()
        : 8;
    $first_run_timestamp = function_exists('mazaq_content_rotation_get_next_daily_timestamp')
        ? mazaq_content_rotation_get_next_daily_timestamp($hour)
        : strtotime('tomorrow midnight');

    if (!wp_next_scheduled($feature['event'])) {
        wp_schedule_event($first_run_timestamp, 'daily', $feature['event']);
    }
}

/**
 * Clear the feature's scheduled cron event.
 */
function mazaq_rotation_clear_event(array $feature): void
{
    $timestamp = wp_next_scheduled($feature['event']);

    while ($timestamp) {
        wp_unschedule_event($timestamp, $feature['event']);
        $timestamp = wp_next_scheduled($feature['event']);
    }
}

/**
 * Handle the feature's cron job. Whether the tick forces a fresh batch is a
 * per-feature flag: hero forces, social only fills gaps (its shell notifies).
 */
function mazaq_rotation_handle_cron(array $feature): void
{
    mazaq_rotation_prepare_today_batch($feature, !empty($feature['cron_forces']));
}

/**
 * Wire every hook a rotation feature needs: cron, theme lifecycle, dashboard
 * widget, and the regenerate / quick-save admin-post endpoints.
 */
function mazaq_rotation_register_feature(array $feature): void
{
    add_action($feature['event'], static function () use ($feature): void {
        mazaq_rotation_handle_cron($feature);
    });
    add_action('after_switch_theme', static function () use ($feature): void {
        mazaq_rotation_schedule_event($feature);
    });
    add_action('init', static function () use ($feature): void {
        mazaq_rotation_schedule_event($feature);
    });
    add_action('switch_theme', static function () use ($feature): void {
        mazaq_rotation_clear_event($feature);
    });
    add_action('wp_dashboard_setup', static function () use ($feature): void {
        mazaq_rotation_register_dashboard_widget($feature);
    });
    add_action('admin_post_' . $feature['regenerate_action'], static function () use ($feature): void {
        mazaq_rotation_handle_regenerate($feature);
    });
    add_action('admin_post_' . $feature['quick_save_action'], static function () use ($feature): void {
        mazaq_rotation_handle_quick_save($feature);
    });
}

function mazaq_rotation_register_dashboard_widget(array $feature): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_add_dashboard_widget(
        $feature['widget_id'],
        $feature['widget_title'],
        static function () use ($feature): void {
            mazaq_rotation_render_widget($feature);
        }
    );
}

/**
 * Render the feature's dashboard widget. Per-feature copy arrives via
 * $feature['widget']; the shape is shared.
 */
function mazaq_rotation_render_widget(array $feature): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $config = mazaq_rotation_get_config($feature);
    $state = mazaq_rotation_prepare_today_batch($feature, false);
    $count_label = (int) $config['batch_size'];
    $widget = $feature['widget'];

    echo '<div dir="rtl" style="text-align:right">';

    if (!empty($_GET[$widget['saved_flag']])) {
        echo '<p><span class="dashicons dashicons-yes-alt" style="color:#46b450"></span> ' . esc_html($widget['saved_message']) . '</p>';
    }

    if (!$config['enabled']) {
        echo '<p>' . esc_html($widget['disabled_message']) . '</p>';
    }

    if ($config['enabled'] && empty($state[$feature['batch_key']])) {
        echo '<p>' . esc_html($widget['empty_message']) . '</p>';
    }

    if ($config['enabled'] && !empty($state[$feature['batch_key']])) {
        $posts = get_posts([
            'post_type' => 'post',
            'post__in' => $state[$feature['batch_key']],
            'posts_per_page' => count($state[$feature['batch_key']]),
            'orderby' => 'post__in',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        if (!empty($posts)) {
            echo '<ul style="margin-right:1.2em;margin-left:0;list-style:disc">';

            foreach ($posts as $post) {
                $view_link = get_permalink($post);
                $edit_link = get_edit_post_link($post->ID, '');

                echo '<li>';
                echo '<strong>' . esc_html(get_the_title($post)) . '</strong><br />';
                echo '<a href="' . esc_url($view_link) . '" target="_blank" rel="noopener noreferrer">' . esc_html($widget['view_label']) . '</a>';

                if (!empty($edit_link)) {
                    echo ' | <a href="' . esc_url($edit_link) . '">' . esc_html($widget['edit_label']) . '</a>';
                }

                echo '</li>';
            }

            echo '</ul>';
        }
    }

    $settings_url = function_exists('mazaq_content_rotation_settings_url')
        ? mazaq_content_rotation_settings_url()
        : admin_url('options-general.php');

    $regenerate_url = wp_nonce_url(
        admin_url('admin-post.php?action=' . $feature['regenerate_action']),
        $feature['regenerate_nonce']
    );

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:12px">';
    wp_nonce_field($feature['quick_save_nonce']);
    echo '<input type="hidden" name="action" value="' . esc_attr($feature['quick_save_action']) . '" />';
    echo '<p><label><input type="checkbox" name="' . esc_attr($feature['settings_enabled_key']) . '" value="1" ' . checked($config['enabled'], true, false) . ' /> ' . esc_html($widget['enable_label']) . '</label></p>';
    echo '<p><label for="' . esc_attr($widget['count_id']) . '">' . esc_html($widget['count_label']) . '</label> ';
    echo '<input id="' . esc_attr($widget['count_id']) . '" type="number" min="1" max="' . esc_attr((string) $feature['max_count']) . '" name="' . esc_attr($feature['settings_count_key']) . '" value="' . esc_attr((string) $count_label) . '" class="small-text" /></p>';
    submit_button(__('حفظ سريع', 'mazaq'), 'secondary', 'submit', false);
    echo '</form>';

    echo '<p style="margin-top:10px">';
    echo '<a href="' . esc_url($regenerate_url) . '" class="button button-secondary">' . esc_html(sprintf($widget['regenerate_pattern'], $count_label)) . '</a> ';
    echo '<a href="' . esc_url($settings_url) . '" class="button button-link">' . esc_html__('الإعدادات المتقدمة', 'mazaq') . '</a>';
    echo '</p>';
    echo '</div>';
}

/**
 * Handle the feature's manual regeneration action.
 */
function mazaq_rotation_handle_regenerate(array $feature): void
{
    if (
        !current_user_can('manage_options') ||
        !check_admin_referer($feature['regenerate_nonce'])
    ) {
        wp_die(__('غير مصرح لك بهذا الإجراء.', 'mazaq'), 403);
    }

    mazaq_rotation_prepare_today_batch($feature, true);

    wp_safe_redirect(admin_url('index.php'));
    exit;
}

/**
 * Handle the feature's dashboard quick-save form.
 */
function mazaq_rotation_handle_quick_save(array $feature): void
{
    if (
        !current_user_can('manage_options') ||
        !check_admin_referer($feature['quick_save_nonce'])
    ) {
        wp_die(__('غير مصرح لك بهذا الإجراء.', 'mazaq'), 403);
    }

    if (!function_exists('mazaq_content_rotation_get_settings') || !function_exists('mazaq_content_rotation_update_settings')) {
        wp_die(__('إعدادات التدوير غير متاحة حالياً.', 'mazaq'), 500);
    }

    $settings = mazaq_content_rotation_get_settings();
    $settings[$feature['settings_enabled_key']] = isset($_POST[$feature['settings_enabled_key']]) ? 1 : 0;

    $posted_count = isset($_POST[$feature['settings_count_key']]) ? wp_unslash($_POST[$feature['settings_count_key']]) : $settings[$feature['settings_count_key']];
    $settings[$feature['settings_count_key']] = max(1, min($feature['max_count'], absint($posted_count)));

    mazaq_content_rotation_update_settings($settings);
    mazaq_rotation_prepare_today_batch($feature, true);

    wp_safe_redirect(add_query_arg($feature['widget']['saved_flag'], '1', admin_url('index.php')));
    exit;
}
