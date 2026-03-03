<?php

declare(strict_types=1);

/**
 * Register Customizer settings for Ad Injection
 */
function toc_loader_ads_customize_register($wp_customize) {
    // Add Section
    $wp_customize->add_section('toc_ads_section', [
        'title'    => __('Site Ads / Monetization', 'mazaq'),
        'priority' => 120,
    ]);

    // Enable Grid Ads Toggle
    $wp_customize->add_setting('toc_ad_injection_enabled', [
        'default'           => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $wp_customize->add_control('toc_ad_injection_enabled', [
        'label'   => __('Enable Grid Ads', 'mazaq'),
        'section' => 'toc_ads_section',
        'type'    => 'checkbox',
    ]);

    // Ad Insertion Interval
    $wp_customize->add_setting('toc_ad_injection_interval', [
        'default'           => 6,
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_control('toc_ad_injection_interval', [
        'label'       => __('Ad Insertion Interval', 'mazaq'),
        'description' => __('Number of posts between each ad in the grid.', 'mazaq'),
        'section'     => 'toc_ads_section',
        'type'        => 'number',
        'input_attrs' => [
            'min'  => 1,
            'step' => 1,
        ],
    ]);
}
add_action('customize_register', 'toc_loader_ads_customize_register');
