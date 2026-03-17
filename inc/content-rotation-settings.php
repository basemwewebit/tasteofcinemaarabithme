<?php

declare(strict_types=1);

const MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION = 'mazaq_content_rotation_settings';
const MAZAQ_CONTENT_ROTATION_SETTINGS_GROUP = 'mazaq_content_rotation_settings_group';
const MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE = 'mazaq-content-rotation-settings';

function mazaq_content_rotation_default_settings(): array
{
    return [
        'daily_hour' => 8,
        'hero_enabled' => 1,
        'hero_count' => 3,
        'social_enabled' => 1,
        'social_count' => 3,
    ];
}

function mazaq_content_rotation_normalize_settings($settings): array
{
    $defaults = mazaq_content_rotation_default_settings();
    $settings = is_array($settings) ? array_merge($defaults, $settings) : $defaults;

    $settings['daily_hour'] = max(0, min(23, (int) $settings['daily_hour']));
    $settings['hero_enabled'] = !empty($settings['hero_enabled']) ? 1 : 0;
    $settings['hero_count'] = max(1, min(3, (int) $settings['hero_count']));
    $settings['social_enabled'] = !empty($settings['social_enabled']) ? 1 : 0;
    $settings['social_count'] = max(1, min(10, (int) $settings['social_count']));

    return $settings;
}

function mazaq_content_rotation_ensure_option_exists(): void
{
    if (false === get_option(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION, false)) {
        add_option(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION, mazaq_content_rotation_default_settings(), '', 'no');
    }
}

function mazaq_content_rotation_get_settings(): array
{
    mazaq_content_rotation_ensure_option_exists();

    return mazaq_content_rotation_normalize_settings(get_option(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION, []));
}

function mazaq_content_rotation_update_settings(array $settings): void
{
    mazaq_content_rotation_ensure_option_exists();
    update_option(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION, mazaq_content_rotation_normalize_settings($settings), false);
}

function mazaq_content_rotation_get_daily_hour(): int
{
    $settings = mazaq_content_rotation_get_settings();

    return (int) $settings['daily_hour'];
}

function mazaq_content_rotation_get_next_daily_timestamp(int $hour): int
{
    $hour = max(0, min(23, $hour));
    $now = current_datetime();
    $next = $now->setTime($hour, 0, 0);

    if ($next <= $now) {
        $next = $next->modify('+1 day');
    }

    return $next->getTimestamp();
}

function mazaq_content_rotation_settings_url(): string
{
    return admin_url('options-general.php?page=' . MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE);
}

function mazaq_content_rotation_add_settings_page(): void
{
    add_options_page(
        __('Content Rotation Settings', 'mazaq'),
        __('Content Rotation', 'mazaq'),
        'manage_options',
        MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE,
        'mazaq_content_rotation_render_settings_page'
    );
}
add_action('admin_menu', 'mazaq_content_rotation_add_settings_page');

function mazaq_content_rotation_register_settings(): void
{
    register_setting(
        MAZAQ_CONTENT_ROTATION_SETTINGS_GROUP,
        MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION,
        [
            'type' => 'array',
            'sanitize_callback' => 'mazaq_content_rotation_normalize_settings',
            'default' => mazaq_content_rotation_default_settings(),
        ]
    );

    add_settings_section(
        'mazaq_content_rotation_main_section',
        __('Daily Rotation Controls', 'mazaq'),
        static function (): void {
            echo '<p>' . esc_html__('تحكم بعدد العناصر وتوقيت التحديث اليومي لصندوق Hero وصندوق السوشيال.', 'mazaq') . '</p>';
        },
        MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE
    );

    add_settings_field(
        'mazaq_rotation_daily_hour',
        __('وقت التحديث اليومي (الساعة)', 'mazaq'),
        'mazaq_content_rotation_render_daily_hour_field',
        MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE,
        'mazaq_content_rotation_main_section'
    );

    add_settings_field(
        'mazaq_rotation_hero_enabled',
        __('تفعيل تدوير Hero', 'mazaq'),
        'mazaq_content_rotation_render_hero_enabled_field',
        MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE,
        'mazaq_content_rotation_main_section'
    );

    add_settings_field(
        'mazaq_rotation_hero_count',
        __('عدد عناصر Hero', 'mazaq'),
        'mazaq_content_rotation_render_hero_count_field',
        MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE,
        'mazaq_content_rotation_main_section'
    );

    add_settings_field(
        'mazaq_rotation_social_enabled',
        __('تفعيل اقتراحات السوشيال', 'mazaq'),
        'mazaq_content_rotation_render_social_enabled_field',
        MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE,
        'mazaq_content_rotation_main_section'
    );

    add_settings_field(
        'mazaq_rotation_social_count',
        __('عدد اقتراحات السوشيال', 'mazaq'),
        'mazaq_content_rotation_render_social_count_field',
        MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE,
        'mazaq_content_rotation_main_section'
    );
}
add_action('admin_init', 'mazaq_content_rotation_register_settings');

function mazaq_content_rotation_render_daily_hour_field(): void
{
    $settings = mazaq_content_rotation_get_settings();

    echo '<input type="number" min="0" max="23" name="' . esc_attr(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION) . '[daily_hour]" value="' . esc_attr((string) $settings['daily_hour']) . '" class="small-text" />';
    echo '<p class="description">' . esc_html__('الوقت بصيغة 24 ساعة حسب المنطقة الزمنية للموقع. مثال: 8 = 08:00 صباحاً.', 'mazaq') . '</p>';
}

function mazaq_content_rotation_render_hero_enabled_field(): void
{
    $settings = mazaq_content_rotation_get_settings();

    echo '<label>';
    echo '<input type="hidden" name="' . esc_attr(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION) . '[hero_enabled]" value="0" />';
    echo '<input type="checkbox" name="' . esc_attr(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION) . '[hero_enabled]" value="1" ' . checked((int) $settings['hero_enabled'], 1, false) . ' /> ';
    echo esc_html__('تشغيل تدوير Hero اليومي', 'mazaq');
    echo '</label>';
}

function mazaq_content_rotation_render_hero_count_field(): void
{
    $settings = mazaq_content_rotation_get_settings();

    echo '<input type="number" min="1" max="3" name="' . esc_attr(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION) . '[hero_count]" value="' . esc_attr((string) $settings['hero_count']) . '" class="small-text" />';
    echo '<p class="description">' . esc_html__('الحد المسموح: من 1 إلى 3 عناصر.', 'mazaq') . '</p>';
}

function mazaq_content_rotation_render_social_enabled_field(): void
{
    $settings = mazaq_content_rotation_get_settings();

    echo '<label>';
    echo '<input type="hidden" name="' . esc_attr(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION) . '[social_enabled]" value="0" />';
    echo '<input type="checkbox" name="' . esc_attr(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION) . '[social_enabled]" value="1" ' . checked((int) $settings['social_enabled'], 1, false) . ' /> ';
    echo esc_html__('تشغيل اقتراحات السوشيال اليومية', 'mazaq');
    echo '</label>';
}

function mazaq_content_rotation_render_social_count_field(): void
{
    $settings = mazaq_content_rotation_get_settings();

    echo '<input type="number" min="1" max="10" name="' . esc_attr(MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION) . '[social_count]" value="' . esc_attr((string) $settings['social_count']) . '" class="small-text" />';
    echo '<p class="description">' . esc_html__('الحد المسموح: من 1 إلى 10 عناصر.', 'mazaq') . '</p>';
}

function mazaq_content_rotation_render_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('إعدادات تدوير المحتوى', 'mazaq') . '</h1>';
    echo '<form action="options.php" method="post">';
    settings_fields(MAZAQ_CONTENT_ROTATION_SETTINGS_GROUP);
    do_settings_sections(MAZAQ_CONTENT_ROTATION_SETTINGS_PAGE);
    submit_button(__('حفظ الإعدادات', 'mazaq'));
    echo '</form>';
    echo '</div>';
}

function mazaq_content_rotation_reschedule_after_update($old_value, $value): void
{
    $old_settings = mazaq_content_rotation_normalize_settings($old_value);
    $new_settings = mazaq_content_rotation_normalize_settings($value);

    if ($old_settings === $new_settings) {
        return;
    }

    if (function_exists('mazaq_social_reminder_clear_event') && function_exists('mazaq_social_reminder_schedule_event')) {
        mazaq_social_reminder_clear_event();
        mazaq_social_reminder_schedule_event();
    }

    if (function_exists('mazaq_hero_daily_clear_event') && function_exists('mazaq_hero_daily_schedule_event')) {
        mazaq_hero_daily_clear_event();
        mazaq_hero_daily_schedule_event();
    }
}
add_action('update_option_' . MAZAQ_CONTENT_ROTATION_SETTINGS_OPTION, 'mazaq_content_rotation_reschedule_after_update', 10, 2);
