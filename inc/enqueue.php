<?php

declare(strict_types=1);

function mazaq_enqueue_assets(): void
{
    $theme = wp_get_theme();
    $version = $theme->get('Version') ?: '1.0.0';

    wp_enqueue_style(
        'mazaq-google-fonts',
        'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'mazaq-style',
        get_template_directory_uri() . '/assets/css/style.css',
        ['mazaq-google-fonts'],
        file_exists(get_template_directory() . '/assets/css/style.css') ? (string) filemtime(get_template_directory() . '/assets/css/style.css') : $version
    );

    wp_enqueue_script(
        'mazaq-app',
        get_template_directory_uri() . '/assets/js/app.js',
        ['jquery'],
        file_exists(get_template_directory() . '/assets/js/app.js') ? (string) filemtime(get_template_directory() . '/assets/js/app.js') : $version,
        true
    );

    wp_localize_script('mazaq-app', 'mazaq_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mazaq_load_more_nonce'),
        'random_film_nonce' => wp_create_nonce('mazaq_random_film_nonce'),
        'random_film_action' => 'mazaq_get_random_film',
        'home_url' => home_url('/'),
    ]);
}
add_action('wp_enqueue_scripts', 'mazaq_enqueue_assets');
