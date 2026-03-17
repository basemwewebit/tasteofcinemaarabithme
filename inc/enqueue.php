<?php

declare(strict_types=1);

function mazaq_get_ad_support_url(): string
{
    static $cached_url = null;
    if ($cached_url !== null) {
        return $cached_url;
    }

    $support_slugs = ['ad-support', 'support-us', 'support'];
    foreach ($support_slugs as $slug) {
        $page = get_page_by_path($slug);
        if ($page instanceof WP_Post) {
            $cached_url = get_permalink($page);
            return $cached_url ?: home_url('/');
        }
    }

    $support_pages = get_pages([
        'meta_key' => '_wp_page_template',
        'meta_value' => 'page-ad-support.php',
        'number' => 1,
    ]);
    if (!empty($support_pages) && isset($support_pages[0]) && $support_pages[0] instanceof WP_Post) {
        $cached_url = get_permalink($support_pages[0]);
        return $cached_url ?: home_url('/');
    }

    foreach (['contact-us', 'contact'] as $slug) {
        $contact_page = get_page_by_path($slug);
        if ($contact_page instanceof WP_Post) {
            $cached_url = get_permalink($contact_page);
            return $cached_url ?: home_url('/');
        }
    }

    $cached_url = home_url('/contact-us/');
    return $cached_url;
}

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

    $ad_support_url = mazaq_get_ad_support_url();

    wp_localize_script('mazaq-app', 'mazaq_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mazaq_load_more_nonce'),
        'random_film_nonce' => wp_create_nonce('mazaq_random_film_nonce'),
        'random_film_action' => 'mazaq_get_random_film',
        'home_url' => home_url('/'),
        'notifications_bootstrap_url' => esc_url_raw(rest_url('mazaq/v1/notifications/bootstrap')),
        'notifications_subscription_url' => esc_url_raw(rest_url('mazaq/v1/notifications/subscription')),
        'notifications_service_worker_url' => '/mazaq-sw.js',
        'adblock' => [
            'session_storage_key' => 'mazaq_adblock_state',
            'mute_storage_key' => 'mazaq_adblock_prompt_muted_until',
            'support_url' => esc_url_raw($ad_support_url),
            'learn_more_url' => esc_url_raw($ad_support_url),
            'prompt_title' => esc_html__('يسعدنا دعمك للموقع', 'mazaq'),
            'prompt_body' => esc_html__('الإعلانات الخفيفة تساعدنا في استمرار المحتوى مجانًا. إذا رغبت، اسمح بالإعلانات لهذا الموقع أو تواصل معنا للدعم.', 'mazaq'),
            'prompt_primary_cta' => esc_html__('اعرف كيف تدعمنا', 'mazaq'),
            'prompt_secondary_cta' => esc_html__('متابعة التصفح', 'mazaq'),
            'fallback_title' => esc_html__('ادعم استمرار المحتوى', 'mazaq'),
            'fallback_body' => esc_html__('يبدو أن مساحة الإعلان غير متاحة. يمكنك دعمنا بالسماح بالإعلانات أو التواصل معنا مباشرة.', 'mazaq'),
            'fallback_cta' => esc_html__('اعرف كيف تدعمنا', 'mazaq'),
        ],
    ]);
}
add_action('wp_enqueue_scripts', 'mazaq_enqueue_assets');
