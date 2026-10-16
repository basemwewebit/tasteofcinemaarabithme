<?php

declare(strict_types=1);

function mazaq_theme_setup(): void
{
    load_theme_textdomain('mazaq', get_template_directory() . '/languages');

    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo');
    add_theme_support('automatic-feed-links');

    register_nav_menus([
        'primary-menu' => __('Primary Menu', 'mazaq'),
        'footer-sections' => __('Footer Sections', 'mazaq'),
        'footer-links' => __('Footer Links', 'mazaq'),
    ]);

    add_image_size('hero-image', 1600, 700, true);
    add_image_size('hero-poster', 1800, 1100, true);
    add_image_size('programme-mobile-thumbnail', 480, 320, true);
    add_image_size('card-thumbnail', 800, 500, true);
    add_image_size('card-wide-thumbnail', 800, 500, true);
    add_image_size('card-small-thumbnail', 400, 250, true);
    add_image_size('card-medium-thumbnail', 600, 375, true);
    add_image_size('sidebar-thumbnail', 150, 150, true);
    add_image_size('search-poster', 400, 533, true);
    add_image_size('poster-card', 600, 800, true);

    $GLOBALS['content_width'] = 800;
}
add_action('after_setup_theme', 'mazaq_theme_setup');

/**
 * Hand an article image's own size to CSS, so it can be capped in height
 * without upscaling, distorting, or losing its reserved space before load:
 * CSS alone can't read the width/height attributes.
 */
function mazaq_content_image_size_vars(string $image): string
{
    if (!preg_match('/\swidth="(\d+)"/', $image, $width) || !preg_match('/\sheight="(\d+)"/', $image, $height)) {
        return $image;
    }
    if ((int) $width[1] === 0 || (int) $height[1] === 0) {
        return $image;
    }

    $vars = sprintf('--img-w:%d;--img-h:%d;', (int) $width[1], (int) $height[1]);
    if (preg_match('/\sstyle="/', $image)) {
        return (string) preg_replace('/\sstyle="/', ' style="' . $vars, $image, 1);
    }

    return (string) preg_replace('/^<img\s/', '<img style="' . $vars . '" ', $image, 1);
}
add_filter('wp_content_img_tag', 'mazaq_content_image_size_vars');
