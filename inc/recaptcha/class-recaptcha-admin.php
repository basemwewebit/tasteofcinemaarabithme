<?php

declare(strict_types=1);

/**
 * Handles the WordPress dashboard settings for Google reCAPTCHA v3.
 */
class TOC_Recaptcha_Admin
{
    private const SETTINGS_GROUP = 'toc_recaptcha_settings_group';

    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'add_settings_page']);
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_notices', [self::class, 'show_missing_keys_warning']);
    }

    public static function add_settings_page(): void
    {
        add_options_page(
            __('reCAPTCHA Settings', 'tasteofcinemaarabithme'),
            __('reCAPTCHA v3', 'tasteofcinemaarabithme'),
            'manage_options',
            'toc-recaptcha-settings',
            [self::class, 'render_settings_page']
        );
    }

    public static function register_settings(): void
    {
        register_setting(self::SETTINGS_GROUP, 'toc_recaptcha_site_key', ['type' => 'string']);
        register_setting(self::SETTINGS_GROUP, 'toc_recaptcha_project_id', ['type' => 'string']);
        register_setting(self::SETTINGS_GROUP, 'toc_recaptcha_secret_key', ['type' => 'string']);
        
        register_setting(self::SETTINGS_GROUP, 'toc_recaptcha_score_threshold', [
            'type'              => 'number',
            'sanitize_callback' => [self::class, 'sanitize_threshold'],
            'default'           => 0.5
        ]);

        add_settings_section(
            'toc_recaptcha_main_section',
            __('API Configuration', 'tasteofcinemaarabithme'),
            null,
            'toc-recaptcha-settings'
        );

        add_settings_field(
            'toc_recaptcha_site_key',
            __('Site Key', 'tasteofcinemaarabithme'),
            [self::class, 'render_site_key_field'],
            'toc-recaptcha-settings',
            'toc_recaptcha_main_section'
        );

        add_settings_field(
            'toc_recaptcha_project_id',
            __('Project ID', 'tasteofcinemaarabithme'),
            [self::class, 'render_project_id_field'],
            'toc-recaptcha-settings',
            'toc_recaptcha_main_section'
        );

        add_settings_field(
            'toc_recaptcha_secret_key',
            __('Secret Key/API Key', 'tasteofcinemaarabithme'),
            [self::class, 'render_secret_key_field'],
            'toc-recaptcha-settings',
            'toc_recaptcha_main_section'
        );

        add_settings_field(
            'toc_recaptcha_score_threshold',
            __('Score Threshold', 'tasteofcinemaarabithme'),
            [self::class, 'render_threshold_field'],
            'toc-recaptcha-settings',
            'toc_recaptcha_main_section'
        );
    }

    public static function sanitize_threshold($input): float
    {
        $val = (float) $input;
        if ($val < 0.0) return 0.0;
        if ($val > 1.0) return 1.0;
        return $val;
    }

    public static function render_site_key_field(): void
    {
        $val = get_option('toc_recaptcha_site_key', '');
        echo '<input type="text" name="toc_recaptcha_site_key" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('The public reCAPTCHA v3 Site Key.', 'tasteofcinemaarabithme') . '</p>';
    }

    public static function render_project_id_field(): void
    {
        $val = get_option('toc_recaptcha_project_id', '');
        echo '<input type="text" name="toc_recaptcha_project_id" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('The Google Cloud Project ID for reCAPTCHA Enterprise.', 'tasteofcinemaarabithme') . '</p>';
    }

    public static function render_secret_key_field(): void
    {
        $val = get_option('toc_recaptcha_secret_key', '');
        echo '<input type="password" name="toc_recaptcha_secret_key" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('The private reCAPTCHA API/Secret Key (or legacy secret). If using Application Default Credentials, you may not need this.', 'tasteofcinemaarabithme') . '</p>';
    }

    public static function render_threshold_field(): void
    {
        $val = get_option('toc_recaptcha_score_threshold', 0.5);
        echo '<input type="number" step="0.1" min="0.0" max="1.0" name="toc_recaptcha_score_threshold" value="' . esc_attr((string)$val) . '" />';
        echo '<p class="description">' . esc_html__('Minimum passing score between 0.0 and 1.0. A score of 0.5 is recommended.', 'tasteofcinemaarabithme') . '</p>';
    }

    public static function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Google reCAPTCHA v3 Settings', 'tasteofcinemaarabithme'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections('toc-recaptcha-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Show admin warning if reCAPTCHA keys are not configured.
     */
    public static function show_missing_keys_warning(): void
    {
        // Only show this warning on the settings page or main dashboard
        $screen = get_current_screen();
        if ($screen && !in_array($screen->id, ['dashboard', 'settings_page_toc-recaptcha-settings'], true)) {
            return;
        }

        $site_key = get_option('toc_recaptcha_site_key', '');
        $project_id = get_option('toc_recaptcha_project_id', '');

        if (empty($site_key) || empty($project_id)) {
            $settings_url = admin_url('options-general.php?page=toc-recaptcha-settings');
            printf(
                '<div class="notice notice-warning"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
                esc_html__('Security Warning:', 'tasteofcinemaarabithme'),
                esc_html__('Google reCAPTCHA Enterprise is not fully configured. Contact form protection may be limited.', 'tasteofcinemaarabithme'),
                esc_url($settings_url),
                esc_html__('Configure reCAPTCHA', 'tasteofcinemaarabithme')
            );
        }
    }
}

TOC_Recaptcha_Admin::init();
