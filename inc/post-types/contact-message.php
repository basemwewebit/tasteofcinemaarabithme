<?php

declare(strict_types=1);

/**
 * Register Contact Message Custom Post Type
 */
function mazaq_register_contact_message_cpt(): void
{
    $labels = [
        'name'               => _x('رسائل الاتصال', 'post type general name', 'mazaq'),
        'singular_name'      => _x('رسالة اتصال', 'post type singular name', 'mazaq'),
        'menu_name'          => _x('رسائل الاتصال', 'admin menu', 'mazaq'),
        'name_admin_bar'     => _x('رسالة اتصال', 'add new on admin bar', 'mazaq'),
        'add_new'            => _x('إضافة جديدة', 'contact message', 'mazaq'),
        'add_new_item'       => __('إضافة رسالة اتصال جديدة', 'mazaq'),
        'new_item'           => __('رسالة اتصال جديدة', 'mazaq'),
        'edit_item'          => __('عرض رسالة الاتصال', 'mazaq'), // Using edit as view
        'view_item'          => __('عرض رسالة الاتصال', 'mazaq'),
        'all_items'          => __('كل رسائل الاتصال', 'mazaq'),
        'search_items'       => __('البحث في رسائل الاتصال', 'mazaq'),
        'not_found'          => __('لم يتم العثور على رسائل اتصال.', 'mazaq'),
        'not_found_in_trash' => __('لم يتم العثور على رسائل اتصال في سلة المهملات.', 'mazaq')
    ];

    $args = [
        'labels'             => $labels,
        'description'        => __('رسائل مرسلة من نموذج الاتصال في الموقع.', 'mazaq'),
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-email-alt',
        'supports'           => ['title', 'editor'], // Title as subject, editor as message
        'map_meta_cap'       => true,
    ];

    register_post_type('contact_message', $args);
}
add_action('init', 'mazaq_register_contact_message_cpt');

/**
 * Add custom columns to contact_message CPT list
 */
function mazaq_contact_message_columns($columns): array
{
    $new_columns = [];
    $new_columns['cb'] = $columns['cb']; // Keep checkbox
    $new_columns['title'] = __('الموضوع', 'mazaq');
    $new_columns['sender_name'] = __('اسم المرسل', 'mazaq');
    $new_columns['sender_email'] = __('البريد الإلكتروني', 'mazaq');
    $new_columns['date'] = $columns['date']; // Keep date

    return $new_columns;
}
add_filter('manage_contact_message_posts_columns', 'mazaq_contact_message_columns');

/**
 * Populate custom columns for contact_message CPT
 */
function mazaq_contact_message_custom_column($column, $post_id): void
{
    switch ($column) {
        case 'sender_name':
            $sender_name = get_post_meta($post_id, '_contact_name', true);
            echo esc_html($sender_name ? $sender_name : '-');
            break;
        case 'sender_email':
            $sender_email = get_post_meta($post_id, '_contact_email', true);
            if ($sender_email) {
                echo '<a href="mailto:' . esc_attr($sender_email) . '">' . esc_html($sender_email) . '</a>';
            } else {
                echo '-';
            }
            break;
    }
}
add_action('manage_contact_message_posts_custom_column', 'mazaq_contact_message_custom_column', 10, 2);
