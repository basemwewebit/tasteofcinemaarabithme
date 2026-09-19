<?php

/**
 * Notification settings: the tiny shared module every notification module
 * reads. Owns the settings option, its defaults and normalization, and the
 * readiness predicates. Only the admin surface writes through this seam.
 */

declare(strict_types=1);

const MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION = 'mazaq_browser_notifications_settings';

/**
 * Default notification settings.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_default_settings(): array
{
    return [
        'enabled' => 1,
        'daily_random_enabled' => 1,
        'daily_random_time' => '09:00',
        'new_post_enabled' => 1,
        'vapid_public_key' => '',
        'vapid_private_key' => '',
        'default_icon_url' => get_template_directory_uri() . '/assets/images/logo.webp',
        'default_badge_url' => get_template_directory_uri() . '/assets/images/logo.png',
        'prompt_title' => __('اشترك في تنبيهات مذاق السينما', 'mazaq'),
        'prompt_body' => __('سنرسل لك مقالاً يومياً مختاراً ومقالات جديدة فور نشرها.', 'mazaq'),
    ];
}

/**
 * Ensure the settings option exists with autoload disabled.
 */
function mazaq_browser_notifications_ensure_settings(): void
{
    if (false === get_option(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION, false)) {
        add_option(
            MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION,
            mazaq_browser_notifications_default_settings(),
            '',
            'no'
        );
    }
}

/**
 * Normalize settings from the database.
 *
 * @param mixed $settings Raw option value.
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_normalize_settings($settings): array
{
    $defaults = mazaq_browser_notifications_default_settings();
    $settings = is_array($settings) ? array_merge($defaults, $settings) : $defaults;

    $settings['enabled'] = !empty($settings['enabled']) ? 1 : 0;
    $settings['daily_random_enabled'] = !empty($settings['daily_random_enabled']) ? 1 : 0;
    $settings['daily_random_time'] = mazaq_browser_notifications_normalize_daily_time((string) $settings['daily_random_time']);
    $settings['new_post_enabled'] = !empty($settings['new_post_enabled']) ? 1 : 0;
    $settings['vapid_public_key'] = trim((string) $settings['vapid_public_key']);
    $settings['vapid_private_key'] = trim((string) $settings['vapid_private_key']);
    $settings['default_icon_url'] = esc_url_raw((string) $settings['default_icon_url']);
    $settings['default_badge_url'] = esc_url_raw((string) $settings['default_badge_url']);
    $settings['prompt_title'] = sanitize_text_field((string) $settings['prompt_title']);
    $settings['prompt_body'] = sanitize_textarea_field((string) $settings['prompt_body']);

    return $settings;
}

/**
 * Normalize a daily send time to HH:MM 24-hour format.
 */
function mazaq_browser_notifications_normalize_daily_time(string $time): string
{
    $time = trim($time);
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
        return '09:00';
    }

    return $time;
}

/**
 * Get normalized settings.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_get_settings(): array
{
    mazaq_browser_notifications_ensure_settings();

    return mazaq_browser_notifications_normalize_settings(
        get_option(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION, [])
    );
}

/**
 * Persist settings without autoloading them.
 *
 * @param array<string, mixed> $settings Settings to save.
 */
function mazaq_browser_notifications_update_settings(array $settings): void
{
    mazaq_browser_notifications_ensure_settings();
    update_option(
        MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_OPTION,
        mazaq_browser_notifications_normalize_settings($settings),
        false
    );
}

/**
 * Determine whether the site should expose notification functionality.
 */
function mazaq_browser_notifications_is_enabled(): bool
{
    $settings = mazaq_browser_notifications_get_settings();

    return !empty($settings['enabled']);
}

/**
 * Determine whether push can be offered to browsers.
 */
function mazaq_browser_notifications_is_push_ready(): bool
{
    $settings = mazaq_browser_notifications_get_settings();
    $home_scheme = (string) wp_parse_url(home_url('/'), PHP_URL_SCHEME);

    return mazaq_browser_notifications_is_enabled()
        && $home_scheme === 'https'
        && $settings['vapid_public_key'] !== ''
        && $settings['vapid_private_key'] !== '';
}

/**
 * Get today's date in the site timezone.
 */
function mazaq_browser_notifications_today(): string
{
    return current_datetime()->format('Y-m-d');
}
