<?php

/**
 * Social reminder: a thin Content rotation feature with a notification shell.
 * Rotation, scheduling, and the dashboard widget live in the engine
 * (inc/content-rotation.php); this file is configuration plus the email
 * notification the engine deliberately knows nothing about.
 */

declare(strict_types=1);

const MAZAQ_SOCIAL_REMINDER_EVENT = 'mazaq_daily_social_reminder';
const MAZAQ_SOCIAL_REMINDER_OPTION = 'mazaq_social_reminder_state';
const MAZAQ_SOCIAL_REMINDER_REGENERATE_ACTION = 'mazaq_social_reminder_regenerate';
const MAZAQ_SOCIAL_REMINDER_REGENERATE_NONCE = 'mazaq_social_reminder_regenerate_nonce';
const MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_ACTION = 'mazaq_social_reminder_quick_save';
const MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_NONCE = 'mazaq_social_reminder_quick_save_nonce';

function mazaq_social_reminder_config(): array
{
    return [
        'option' => MAZAQ_SOCIAL_REMINDER_OPTION,
        'event' => MAZAQ_SOCIAL_REMINDER_EVENT,
        'date_key' => 'batch_date',
        'batch_key' => 'batch_post_ids',
        'extra_state' => ['last_email_sent_date' => ''],
        'settings_enabled_key' => 'social_enabled',
        'settings_count_key' => 'social_count',
        'max_count' => 10,
        'cron_forces' => false,
        'widget_id' => 'mazaq-social-reminder-widget',
        'widget_title' => 'اقتراحات اليوم للنشر على السوشيال ميديا',
        'regenerate_action' => MAZAQ_SOCIAL_REMINDER_REGENERATE_ACTION,
        'regenerate_nonce' => MAZAQ_SOCIAL_REMINDER_REGENERATE_NONCE,
        'quick_save_action' => MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_ACTION,
        'quick_save_nonce' => MAZAQ_SOCIAL_REMINDER_QUICK_SAVE_NONCE,
        'widget' => [
            'saved_flag' => 'mazaq_social_saved',
            'saved_message' => 'تم حفظ إعدادات السوشيال.',
            'disabled_message' => 'اقتراحات السوشيال اليومية معطلة حالياً.',
            'empty_message' => 'لا يوجد اقتراحات متاحة حالياً.',
            'enable_label' => 'تفعيل صندوق السوشيال',
            'count_id' => 'mazaq-social-count',
            'count_label' => 'عدد الاقتراحات',
            'regenerate_pattern' => '🔄 اقتراح %d جديد',
            'view_label' => 'عرض المقال',
            'edit_label' => 'تعديل المقال',
        ],
    ];
}

/**
 * Hydrate batch ids into posts for the notification email.
 *
 * @return WP_Post[]
 */
function mazaq_social_reminder_get_batch_posts(array $batch_post_ids): array
{
    $posts = [];

    foreach ($batch_post_ids as $post_id) {
        $post_id = (int) $post_id;
        if (!mazaq_rotation_is_post_publishable($post_id)) {
            continue;
        }

        $post = get_post($post_id);
        if ($post instanceof WP_Post) {
            $posts[] = $post;
        }
    }

    return $posts;
}

/**
 * Send the daily suggestions email unless already sent today. Returns the
 * state with the email marker stamped on success.
 */
function mazaq_social_reminder_send_email(array $state): array
{
    $today = mazaq_rotation_today();

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

/**
 * Notify after the engine's cron tick prepares the batch. Runs at a later
 * priority than the engine handler, and never while disabled.
 */
function mazaq_social_reminder_handle_cron_email(): void
{
    $feature = mazaq_social_reminder_config();
    $config = mazaq_rotation_get_config($feature);

    if (!$config['enabled']) {
        return;
    }

    $state = mazaq_social_reminder_send_email(mazaq_rotation_get_state($feature));
    mazaq_rotation_update_state($feature, $state);
}

/**
 * Safety net when cron never fires: prepare and notify on admin visits.
 */
function mazaq_social_reminder_admin_fallback(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $feature = mazaq_social_reminder_config();
    $config = mazaq_rotation_get_config($feature);

    if (!$config['enabled']) {
        return;
    }

    $state = mazaq_rotation_prepare_today_batch($feature, false);
    $state = mazaq_social_reminder_send_email($state);
    mazaq_rotation_update_state($feature, $state);
}
add_action('admin_init', 'mazaq_social_reminder_admin_fallback');

/**
 * Schedule the social daily cron event. Kept as a named entry point: the
 * shared settings page reschedules through it.
 */
function mazaq_social_reminder_schedule_event(): void
{
    mazaq_rotation_schedule_event(mazaq_social_reminder_config());
}

/**
 * Clear the social scheduled cron event. Kept as a named entry point: the
 * shared settings page reschedules through it.
 */
function mazaq_social_reminder_clear_event(): void
{
    mazaq_rotation_clear_event(mazaq_social_reminder_config());
}

mazaq_rotation_register_feature(mazaq_social_reminder_config());
add_action(MAZAQ_SOCIAL_REMINDER_EVENT, 'mazaq_social_reminder_handle_cron_email', 20);
