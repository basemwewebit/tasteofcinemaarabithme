<?php

declare(strict_types=1);

if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
    require_once get_template_directory() . '/vendor/autoload.php';
}

$mazaq_includes = [
    'inc/theme-setup.php',
    'inc/enqueue.php',
    'inc/scf-fields.php',
    'inc/film-record.php',
    'inc/schema-film.php',
    'inc/ads.php',
    'inc/analytics.php',
    'inc/post-views.php',
    'inc/reader-submissions.php',
    'inc/infinite-scroll.php',
    'inc/breadcrumb.php',
    'inc/helpers.php',
    'inc/article-headings.php',
    'inc/article-body.php',
    'inc/article-stills.php',
    'inc/film-graph.php',
    'inc/notifications-settings.php',
    'inc/notifications-store.php',
    'inc/notifications-content.php',
    'inc/notifications-dispatch.php',
    'inc/notifications-schedule.php',
    'inc/notifications-web.php',
    'inc/notifications-admin.php',
    'inc/article-pool.php',
    'inc/related-articles.php',
    'inc/random-film-popup.php',
    'inc/content-rotation.php',
    'inc/continuous-programme.php',
    'inc/latest-feed.php',
    'inc/content-rotation-settings.php',
    'inc/admin-social-reminder.php',
    'inc/admin-hero-daily.php',
    'inc/toc-source.php',
    'inc/admin-toc-missing-widget.php',
    'inc/toc-source-monitor.php',
    'inc/post-types/contact-message.php',
    'inc/recaptcha/class-recaptcha-admin.php',
    'inc/recaptcha/class-recaptcha-verify.php',
    'inc/recaptcha/class-recaptcha-hooks.php',
];

foreach ($mazaq_includes as $file) {
    $path = get_template_directory() . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

/**
 * Restrict application passwords to users who can edit posts (editors, admins, etc.)
 */
add_filter('wp_is_application_passwords_available', function ($available, $user = null) {
    if (!$user) {
        return (bool) $available;
    }

    // Only allow application passwords for users with edit_posts capability.
    return user_can($user, 'edit_posts');
}, 10, 2);


