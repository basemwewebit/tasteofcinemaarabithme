<?php

declare(strict_types=1);

$mazaq_includes = [
    'inc/theme-setup.php',
    'inc/enqueue.php',
    'inc/scf-fields.php',
    'inc/ads.php',
    'inc/analytics.php',
    'inc/post-views.php',
    'inc/contact-form.php',
    'inc/infinite-scroll.php',
    'inc/breadcrumb.php',
    'inc/helpers.php',
];

foreach ($mazaq_includes as $file) {
    $path = get_template_directory() . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}
