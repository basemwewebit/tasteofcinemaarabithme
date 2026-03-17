<?php

declare(strict_types=1);

const MAZAQ_SOCIAL_REMINDER_EVENT = 'mazaq_daily_social_reminder';
const MAZAQ_SOCIAL_REMINDER_OPTION = 'mazaq_social_reminder_state';
const MAZAQ_SOCIAL_REMINDER_REGENERATE_ACTION = 'mazaq_social_reminder_regenerate';
const MAZAQ_SOCIAL_REMINDER_REGENERATE_NONCE = 'mazaq_social_reminder_regenerate_nonce';
const MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_ACTION = 'mazaq_social_reminder_quick_save';
const MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_NONCE = 'mazaq_social_reminder_quick_save_nonce';

function mazaq_social_reminder_get_config(): array
{
    $default = [
        'enabled' => true,
        'batch_size' => 3,
    ];

    if (!function_exists('mazaq_content_rotation_get_settings')) {
        return $default;
    }

    $settings = mazaq_content_rotation_get_settings();

    return [
        'enabled' => !empty($settings['social_enabled']),
        'batch_size' => max(1, min(10, (int) $settings['social_count'])),
    ];
}

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

function mazaq_social_reminder_generate_batch(array $published_ids, array $used_ids, int $batch_size): array
{
    $published_ids = array_values(array_unique(array_filter(array_map('intval', $published_ids))));
    $used_ids = array_values(array_unique(array_intersect($published_ids, array_map('intval', $used_ids))));
    $batch_size = max(1, $batch_size);

    if (empty($published_ids)) {
        return [
            'batch_post_ids' => [],
            'used_post_ids' => [],
        ];
    }

    $batch_size = min($batch_size, count($published_ids));

    $available_ids = array_values(array_diff($published_ids, $used_ids));

    if (count($available_ids) >= $batch_size) {
        $batch_post_ids = mazaq_social_reminder_pick_random_ids($available_ids, $batch_size);

        return [
            'batch_post_ids' => $batch_post_ids,
            'used_post_ids' => array_values(array_unique(array_merge($used_ids, $batch_post_ids))),
        ];
    }

    $batch_post_ids = $available_ids;

    if (count($batch_post_ids) < $batch_size) {
        $needed = $batch_size - count($batch_post_ids);
        $new_cycle_pool = array_values(array_diff($published_ids, $batch_post_ids));
        $extra_picks = mazaq_social_reminder_pick_random_ids($new_cycle_pool, $needed);
        $batch_post_ids = array_values(array_unique(array_merge($batch_post_ids, $extra_picks)));
    }

    if (count($batch_post_ids) < $batch_size) {
        $needed = $batch_size - count($batch_post_ids);
        $remaining_pool = array_values(array_diff($published_ids, $batch_post_ids));
        $final_picks = mazaq_social_reminder_pick_random_ids($remaining_pool, $needed);
        $batch_post_ids = array_values(array_unique(array_merge($batch_post_ids, $final_picks)));
    }

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
    $config = mazaq_social_reminder_get_config();
    $today = mazaq_social_reminder_today();
    $state = mazaq_social_reminder_get_state();

    if (!$config['enabled']) {
        return $state;
    }

    $original_batch_ids = $state['batch_post_ids'];
    $published_ids = mazaq_social_reminder_get_published_post_ids();
    $has_invalid_batch_posts = !empty(array_diff($original_batch_ids, $published_ids));

    $state = mazaq_social_reminder_prune_state($state, $published_ids);

    $should_generate_new_batch = $force_regenerate || $state['batch_date'] !== $today || $has_invalid_batch_posts;

    if ($should_generate_new_batch) {
        $batch = mazaq_social_reminder_generate_batch($published_ids, $state['used_post_ids'], (int) $config['batch_size']);
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
    $config = mazaq_social_reminder_get_config();

    if (!$config['enabled']) {
        mazaq_social_reminder_clear_event();

        return;
    }

    $hour = function_exists('mazaq_content_rotation_get_daily_hour')
        ? mazaq_content_rotation_get_daily_hour()
        : 8;
    $first_run_timestamp = function_exists('mazaq_content_rotation_get_next_daily_timestamp')
        ? mazaq_content_rotation_get_next_daily_timestamp($hour)
        : (time() + MINUTE_IN_SECONDS);

    if (!wp_next_scheduled(MAZAQ_SOCIAL_REMINDER_EVENT)) {
        wp_schedule_event($first_run_timestamp, 'daily', MAZAQ_SOCIAL_REMINDER_EVENT);
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

    $config = mazaq_social_reminder_get_config();
    if (!$config['enabled']) {
        return;
    }

    mazaq_social_reminder_prepare_today_batch(true);
}
add_action('admin_init', 'mazaq_social_reminder_admin_fallback');

function mazaq_social_reminder_render_widget(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $config = mazaq_social_reminder_get_config();
    $state = mazaq_social_reminder_prepare_today_batch(false);
    $count_label = (int) $config['batch_size'];

    echo '<div dir="rtl" style="text-align:right">';

    if (!empty($_GET['mazaq_social_saved'])) {
        echo '<p><span class="dashicons dashicons-yes-alt" style="color:#46b450"></span> ' . esc_html__('تم حفظ إعدادات السوشيال.', 'mazaq') . '</p>';
    }

    if (!$config['enabled']) {
        echo '<p>' . esc_html__('اقتراحات السوشيال اليومية معطلة حالياً.', 'mazaq') . '</p>';
    }

    if ($config['enabled'] && ($state['batch_date'] !== mazaq_social_reminder_today() || empty($state['batch_post_ids']))) {
        echo '<p>' . esc_html__('لا يوجد اقتراحات متاحة حالياً.', 'mazaq') . '</p>';
    }

    if ($config['enabled'] && !empty($state['batch_post_ids'])) {
        $posts = mazaq_social_reminder_get_batch_posts($state['batch_post_ids']);

        if (!empty($posts)) {
            echo '<ul style="margin-right:1.2em;margin-left:0;list-style:disc">';

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

            echo '</ul>';
        }
    }

    $settings_url = function_exists('mazaq_content_rotation_settings_url')
        ? mazaq_content_rotation_settings_url()
        : admin_url('options-general.php');

    $regenerate_url = wp_nonce_url(
        admin_url('admin-post.php?action=' . MAZAQ_SOCIAL_REMINDER_REGENERATE_ACTION),
        MAZAQ_SOCIAL_REMINDER_REGENERATE_NONCE
    );

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:12px">';
    wp_nonce_field(MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_NONCE);
    echo '<input type="hidden" name="action" value="' . esc_attr(MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_ACTION) . '" />';
    echo '<p><label><input type="checkbox" name="social_enabled" value="1" ' . checked($config['enabled'], true, false) . ' /> ' . esc_html__('تفعيل صندوق السوشيال', 'mazaq') . '</label></p>';
    echo '<p><label for="mazaq-social-count">' . esc_html__('عدد الاقتراحات', 'mazaq') . '</label> ';
    echo '<input id="mazaq-social-count" type="number" min="1" max="10" name="social_count" value="' . esc_attr((string) $count_label) . '" class="small-text" /></p>';
    submit_button(__('حفظ سريع', 'mazaq'), 'secondary', 'submit', false);
    echo '</form>';

    echo '<p style="margin-top:10px">';
    echo '<a href="' . esc_url($regenerate_url) . '" class="button button-secondary">' . esc_html(sprintf(__('🔄 اقتراح %d جديد', 'mazaq'), $count_label)) . '</a> ';
    echo '<a href="' . esc_url($settings_url) . '" class="button button-link">' . esc_html__('الإعدادات المتقدمة', 'mazaq') . '</a>';
    echo '</p>';
    echo '</div>';
}

function mazaq_social_reminder_register_dashboard_widget(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_add_dashboard_widget(
        'mazaq-social-reminder-widget',
        __('اقتراحات اليوم للنشر على السوشيال ميديا', 'mazaq'),
        'mazaq_social_reminder_render_widget'
    );
}
add_action('wp_dashboard_setup', 'mazaq_social_reminder_register_dashboard_widget');

function mazaq_social_reminder_handle_regenerate(): void
{
    if (
        !current_user_can('manage_options') ||
        !check_admin_referer(MAZAQ_SOCIAL_REMINDER_REGENERATE_NONCE)
    ) {
        wp_die(__('غير مصرح لك بهذا الإجراء.', 'mazaq'), 403);
    }

    mazaq_social_reminder_prepare_today_batch(false, true);

    wp_safe_redirect(admin_url('index.php'));
    exit;
}
add_action('admin_post_' . MAZAQ_SOCIAL_REMINDER_REGENERATE_ACTION, 'mazaq_social_reminder_handle_regenerate');

function mazaq_social_reminder_handle_quick_save(): void
{
    if (
        !current_user_can('manage_options') ||
        !check_admin_referer(MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_NONCE)
    ) {
        wp_die(__('غير مصرح لك بهذا الإجراء.', 'mazaq'), 403);
    }

    if (!function_exists('mazaq_content_rotation_get_settings') || !function_exists('mazaq_content_rotation_update_settings')) {
        wp_die(__('إعدادات التدوير غير متاحة حالياً.', 'mazaq'), 500);
    }

    $settings = mazaq_content_rotation_get_settings();
    $settings['social_enabled'] = isset($_POST['social_enabled']) ? 1 : 0;

    $posted_count = isset($_POST['social_count']) ? wp_unslash($_POST['social_count']) : $settings['social_count'];
    $settings['social_count'] = max(1, min(10, absint($posted_count)));

    mazaq_content_rotation_update_settings($settings);
    mazaq_social_reminder_prepare_today_batch(false, true);

    wp_safe_redirect(add_query_arg('mazaq_social_saved', '1', admin_url('index.php')));
    exit;
}
add_action('admin_post_' . MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_ACTION, 'mazaq_social_reminder_handle_quick_save');
