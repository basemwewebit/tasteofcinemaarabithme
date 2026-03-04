<?php

declare(strict_types=1);

function mazaq_register_acf_fields(): void
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => __('Theme Options', 'mazaq'),
            'menu_title' => __('Theme Options', 'mazaq'),
            'menu_slug' => 'mazaq-theme-options',
            'capability' => 'manage_options',
            'redirect' => false,
        ]);
    }

    $option_fields = [
        ['key' => 'field_ga4_measurement_id', 'label' => 'GA4 Measurement ID', 'name' => 'ga4_measurement_id', 'type' => 'text'],
        ['key' => 'field_adsense_publisher_id', 'label' => 'AdSense Publisher ID', 'name' => 'adsense_publisher_id', 'type' => 'text'],
        ['key' => 'field_ad_slot_hero_banner', 'label' => 'Hero Banner Slot', 'name' => 'ad_slot_hero_banner', 'type' => 'text'],
        ['key' => 'field_ad_slot_sidebar_square', 'label' => 'Sidebar Square Slot', 'name' => 'ad_slot_sidebar_square', 'type' => 'text'],
        ['key' => 'field_ad_slot_mobile_menu', 'label' => 'Mobile Menu Slot', 'name' => 'ad_slot_mobile_menu', 'type' => 'text'],
        ['key' => 'field_ad_slot_in_article', 'label' => 'In Article Slot', 'name' => 'ad_slot_in_article', 'type' => 'text'],
        ['key' => 'field_ad_slot_bottom_article', 'label' => 'Bottom Article Slot', 'name' => 'ad_slot_bottom_article', 'type' => 'text'],
        ['key' => 'field_ad_slot_sidebar_vertical', 'label' => 'Sidebar Vertical Slot', 'name' => 'ad_slot_sidebar_vertical', 'type' => 'text'],
        ['key' => 'field_ad_slot_archive_banner', 'label' => 'Archive Banner Slot', 'name' => 'ad_slot_archive_banner', 'type' => 'text'],
        ['key' => 'field_ad_slot_404_banner', 'label' => '404 Banner Slot', 'name' => 'ad_slot_404_banner', 'type' => 'text'],
        ['key' => 'field_hero_featured_post', 'label' => 'Hero Featured Post', 'name' => 'hero_featured_post', 'type' => 'post_object', 'post_type' => ['post'], 'return_format' => 'id'],
        ['key' => 'field_contact_email', 'label' => 'Contact Email', 'name' => 'contact_email', 'type' => 'email'],
        ['key' => 'field_contact_address', 'label' => 'Contact Address', 'name' => 'contact_address', 'type' => 'textarea'],
        [
            'key' => 'field_social_links',
            'label' => 'Social Links',
            'name' => 'social_links',
            'type' => 'repeater',
            'layout' => 'block',
            'button_label' => 'Add Social Link',
            'sub_fields' => [
                ['key' => 'sub_field_platform_name', 'label' => 'Platform Name', 'name' => 'platform_name', 'type' => 'text', 'required' => 1],
                ['key' => 'sub_field_social_url', 'label' => 'URL', 'name' => 'url', 'type' => 'url', 'required' => 1],
            ]
        ],
    ];

    acf_add_local_field_group([
        'key' => 'group_mazaq_theme_options',
        'title' => 'Mazaq Theme Options',
        'fields' => $option_fields,
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'mazaq-theme-options']]],
    ]);

    acf_add_local_field_group([
        'key' => 'group_mazaq_author_fields',
        'title' => 'Author Profile Fields',
        'fields' => [
            ['key' => 'field_author_role_title', 'label' => 'Role Title', 'name' => 'author_role_title', 'type' => 'text'],
            ['key' => 'field_author_twitter_url', 'label' => 'Twitter URL', 'name' => 'twitter_url', 'type' => 'url'],
            ['key' => 'field_author_website_url', 'label' => 'Website URL', 'name' => 'website_url', 'type' => 'url'],
        ],
        'location' => [[['param' => 'user_form', 'operator' => '==', 'value' => 'all']]],
    ]);

    acf_add_local_field_group([
        'key' => 'group_mazaq_category_fields',
        'title' => 'Category Settings',
        'fields' => [
            ['key' => 'field_category_bg_image', 'label' => 'Category Background Image', 'name' => 'category_bg_image', 'type' => 'image', 'return_format' => 'id'],
        ],
        'location' => [[['param' => 'taxonomy', 'operator' => '==', 'value' => 'category']]],
    ]);
}
add_action('acf/init', 'mazaq_register_acf_fields');
