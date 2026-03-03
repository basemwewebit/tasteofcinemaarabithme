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
    add_image_size('card-thumbnail', 800, 500, true);
    add_image_size('card-wide-thumbnail', 800, 500, true);
    add_image_size('sidebar-thumbnail', 150, 150, true);
    add_image_size('search-poster', 400, 533, true);

    $GLOBALS['content_width'] = 800;
}
add_action('after_setup_theme', 'mazaq_theme_setup');
