<?php

declare(strict_types=1);

const MAZAQ_SOCIAL_REMINDER_EVENT = 'mazaq_daily_social_reminder';
const MAZAQ_SOCIAL_REMINDER_OPTION = 'mazaq_social_reminder_state';
const MAZAQ_SOCIAL_REMINDER_REGENERATE_ACTION = 'mazaq_social_reminder_regenerate';
const MAZAQ_SOCIAL_REMINDER_REGENERATE_NONCE = 'mazaq_social_reminder_regenerate_nonce';

function mazaq_social_reminder_default_state(): array
{
    return [
        'batch_date' => '',
        'batch_post_ids' => [],
        'used_post_ids' => [],
        'last_email_sent_date' => '',
    ];
}

function mazaq_social_reminder_ensure_option_exists(): void
{
    if (false === get_option(MAZAQ_SOCIAL_REMINDER_OPTION, false)) {
        add_option(MAZAQ_SOCIAL_REMINDER_OPTION, mazaq_social_reminder_default_state(), '', 'no');
    }
}

function mazaq_social_reminder_normalize_state($state): array
{
    $defaults = mazaq_social_reminder_default_state();
    $state = is_array($state) ? array_merge($defaults, $state) : $defaults;

    $state['batch_date'] = is_string($state['batch_date']) ? $state['batch_date'] : '';
    $state['last_email_sent_date'] = is_string($state['last_email_sent_date']) ? $state['last_email_sent_date'] : '';

    $state['batch_post_ids'] = array_values(array_unique(array_filter(array_map('intval', (array) $state['batch_post_ids']))));
    $state['used_post_ids'] = array_values(array_unique(array_filter(array_map('intval', (array) $state['used_post_ids']))));

    return $state;
}

function mazaq_social_reminder_get_state(): array
{
    mazaq_social_reminder_ensure_option_exists();

    return mazaq_social_reminder_normalize_state(get_option(MAZAQ_SOCIAL_REMINDER_OPTION, []));
}

function mazaq_social_reminder_update_state(array $state): void
{
    mazaq_social_reminder_ensure_option_exists();
    update_option(MAZAQ_SOCIAL_REMINDER_OPTION, mazaq_social_reminder_normalize_state($state), false);
}

function mazaq_social_reminder_today(): string
{
    return current_datetime()->format('Y-m-d');
}

function mazaq_social_reminder_get_published_post_ids(): array
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

function mazaq_social_reminder_pick_random_ids(array $ids, int $count): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (empty($ids) || $count <= 0) {
        return [];
    }

    shuffle($ids);

    return array_slice($ids, 0, min($count, count($ids)));
}

function mazaq_social_reminder_is_post_publishable(int $post_id): bool
{
    return $post_id > 0 && 'publish' === get_post_status($post_id) && 'post' === get_post_type($post_id);
}

function mazaq_social_reminder_prune_state(array $state, array $published_ids): array
{
    $published_lookup = array_fill_keys($published_ids, true);

    $state['batch_post_ids'] = array_values(array_filter(
        $state['batch_post_ids'],
        static fn (int $post_id): bool => isset($published_lookup[$post_id])
    ));

    $state['used_post_ids'] = array_values(array_filter(
        $state['used_post_ids'],
        static fn (int $post_id): bool => isset($published_lookup[$post_id])
    ));

    return $state;
}

function mazaq_social_reminder_generate_batch(array $published_ids, array $used_ids): array
{
    $published_ids = array_values(array_unique(array_filter(array_map('intval', $published_ids))));
    $used_ids = array_values(array_unique(array_intersect($published_ids, array_map('intval', $used_ids))));

    if (empty($published_ids)) {
        return [
            'batch_post_ids' => [],
            'used_post_ids' => [],
        ];
    }

    $available_ids = array_values(array_diff($published_ids, $used_ids));

    if (count($available_ids) >= 3) {
        $batch_post_ids = mazaq_social_reminder_pick_random_ids($available_ids, 3);

        return [
            'batch_post_ids' => $batch_post_ids,
            'used_post_ids' => array_values(array_unique(array_merge($used_ids, $batch_post_ids))),
        ];
    }

    if (count($available_ids) === 2) {
        $new_cycle_pool = array_values(array_diff($published_ids, $available_ids));
        $extra_pick = mazaq_social_reminder_pick_random_ids($new_cycle_pool, 1);
        $batch_post_ids = array_values(array_unique(array_merge($available_ids, $extra_pick)));

        return [
            'batch_post_ids' => $batch_post_ids,
            'used_post_ids'  => $batch_post_ids,
        ];
    }

    if (count($available_ids) === 1) {
        $leftover_id = (int) $available_ids[0];

        if (count($published_ids) === 1) {
            return [
                'batch_post_ids' => [$leftover_id],
                'used_post_ids' => [$leftover_id],
            ];
        }

        $new_cycle_pool = array_values(array_diff($published_ids, [$leftover_id]));
        $extra_picks = mazaq_social_reminder_pick_random_ids($new_cycle_pool, 2);
        $batch_post_ids = array_values(array_unique(array_merge([$leftover_id], $extra_picks)));

        return [
            'batch_post_ids' => $batch_post_ids,
            'used_post_ids' => $batch_post_ids,
        ];
    }

    $batch_post_ids = mazaq_social_reminder_pick_random_ids($published_ids, min(3, count($published_ids)));

    return [
        'batch_post_ids' => $batch_post_ids,
        'used_post_ids' => $batch_post_ids,
    ];
}

function mazaq_social_reminder_get_batch_posts(array $batch_post_ids): array
{
    $posts = [];

    foreach ($batch_post_ids as $post_id) {
        $post_id = (int) $post_id;
        if (!mazaq_social_reminder_is_post_publishable($post_id)) {
            continue;
        }

        $post = get_post($post_id);
        if ($post instanceof WP_Post) {
            $posts[] = $post;
        }
    }

    return $posts;
}

function mazaq_social_reminder_send_email(array $state): array
{
    $today = mazaq_social_reminder_today();

    if ($state['last_email_sent_date'] === $today || empty($state['batch_post_ids'])) {
        return $state;
    }

    $posts = mazaq_social_reminder_get_batch_posts($state['batch_post_ids']);
    if (empty($posts)) {
        return $state;
    }

    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $subject = sprintf(__('اقتراحات السوشيال اليومية من %s', 'mazaq'), $site_name);

    $lines = [
        sprintf(__('هذه المقالات المقترحة لليوم %s:', 'mazaq'), wp_date(get_option('date_format'))),
        '',
    ];

    foreach ($posts as $index => $post) {
        $lines[] = sprintf('%d. %s', $index + 1, get_the_title($post));
        $lines[] = sprintf(__('الرابط: %s', 'mazaq'), get_permalink($post));
        $lines[] = sprintf(__('تحرير: %s', 'mazaq'), get_edit_post_link($post->ID, ''));
        $lines[] = '';
    }

    $sent = wp_mail(get_option('admin_email'), $subject, implode("\n", $lines));
    if ($sent) {
        $state['last_email_sent_date'] = $today;
    }

    return $state;
}

function mazaq_social_reminder_prepare_today_batch(bool $send_email = true, bool $force_regenerate = false): array
{
    $today = mazaq_social_reminder_today();
    $state = mazaq_social_reminder_get_state();
    $original_batch_ids = $state['batch_post_ids'];
    $published_ids = mazaq_social_reminder_get_published_post_ids();
    $has_invalid_batch_posts = !empty(array_diff($original_batch_ids, $published_ids));

    $state = mazaq_social_reminder_prune_state($state, $published_ids);

    $should_generate_new_batch = $force_regenerate || $state['batch_date'] !== $today || $has_invalid_batch_posts;

    if ($should_generate_new_batch) {
        $batch = mazaq_social_reminder_generate_batch($published_ids, $state['used_post_ids']);
        $state['batch_date'] = $today;
        $state['batch_post_ids'] = $batch['batch_post_ids'];
        $state['used_post_ids'] = $batch['used_post_ids'];
    }

    if ($send_email) {
        $state = mazaq_social_reminder_send_email($state);
    }

    mazaq_social_reminder_update_state($state);

    return $state;
}

function mazaq_social_reminder_schedule_event(): void
{
    mazaq_social_reminder_ensure_option_exists();

    if (!wp_next_scheduled(MAZAQ_SOCIAL_REMINDER_EVENT)) {
        wp_schedule_event(time() + MINUTE_IN_SECONDS, 'daily', MAZAQ_SOCIAL_REMINDER_EVENT);
    }
}

function mazaq_social_reminder_clear_event(): void
{
    $timestamp = wp_next_scheduled(MAZAQ_SOCIAL_REMINDER_EVENT);

    while ($timestamp) {
        wp_unschedule_event($timestamp, MAZAQ_SOCIAL_REMINDER_EVENT);
        $timestamp = wp_next_scheduled(MAZAQ_SOCIAL_REMINDER_EVENT);
    }
}

function mazaq_social_reminder_handle_cron(): void
{
    mazaq_social_reminder_prepare_today_batch(true);
}
add_action(MAZAQ_SOCIAL_REMINDER_EVENT, 'mazaq_social_reminder_handle_cron');

function mazaq_social_reminder_bootstrap_schedule(): void
{
    mazaq_social_reminder_schedule_event();
}
add_action('after_switch_theme', 'mazaq_social_reminder_bootstrap_schedule');
add_action('init', 'mazaq_social_reminder_bootstrap_schedule');

function mazaq_social_reminder_cleanup_schedule(): void
{
    mazaq_social_reminder_clear_event();
}
add_action('switch_theme', 'mazaq_social_reminder_cleanup_schedule');

function mazaq_social_reminder_admin_fallback(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    mazaq_social_reminder_prepare_today_batch(true);
}
add_action('admin_init', 'mazaq_social_reminder_admin_fallback');

function mazaq_social_reminder_render_notice(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $state = mazaq_social_reminder_get_state();
    if ($state['batch_date'] !== mazaq_social_reminder_today() || empty($state['batch_post_ids'])) {
        return;
    }

    $posts = mazaq_social_reminder_get_batch_posts($state['batch_post_ids']);
    if (empty($posts)) {
        return;
    }

    echo '<div class="notice notice-info" dir="rtl" style="text-align:right">';
    echo '<p><strong>' . esc_html__('اقتراحات اليوم للنشر على السوشيال ميديا', 'mazaq') . '</strong></p>';
    echo '<ul style="margin-right:1.5em;margin-left:0;list-style:disc">';

    foreach ($posts as $post) {
        $view_link = get_permalink($post);
        $edit_link = get_edit_post_link($post->ID, '');

        echo '<li>';
        echo '<strong>' . esc_html(get_the_title($post)) . '</strong><br />';
        echo '<a href="' . esc_url($view_link) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('عرض المقال', 'mazaq') . '</a>';

        if (!empty($edit_link)) {
            echo ' | <a href="' . esc_url($edit_link) . '">' . esc_html__('تعديل المقال', 'mazaq') . '</a>';
        }

        echo '</li>';
    }

    $regenerate_url = wp_nonce_url(
        admin_url('admin-post.php?action=' . MAZAQ_SOCIAL_REMINDER_REGENERATE_ACTION),
        MAZAQ_SOCIAL_REMINDER_REGENERATE_NONCE
    );

    echo '</ul>';
    echo '<p><a href="' . esc_url($regenerate_url) . '" class="button button-secondary">' . esc_html__('🔄 اقتراح 3 جداد', 'mazaq') . '</a></p>';
    echo '</div>';
}
add_action('admin_notices', 'mazaq_social_reminder_render_notice');

function mazaq_social_reminder_handle_regenerate(): void
{
    if (
        !current_user_can('manage_options') ||
        !check_admin_referer(MAZAQ_SOCIAL_REMINDER_REGENERATE_NONCE)
    ) {
        wp_die(__('غير مصرح لك بهذا الإجراء.', 'mazaq'), 403);
    }

    mazaq_social_reminder_prepare_today_batch(false, true);

    wp_safe_redirect(admin_url());
    exit;
}
add_action('admin_post_' . MAZAQ_SOCIAL_REMINDER_REGENERATE_ACTION, 'mazaq_social_reminder_handle_regenerate');
