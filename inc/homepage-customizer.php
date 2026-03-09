<?php
declare(strict_types=1);

function toc_homepage_sections_customizer(WP_Customize_Manager $wp_customize): void {
    $wp_customize->add_panel('toc_homepage_sections', [
        'title'       => esc_html__('Homepage Sections', 'mazaq'),
        'description' => esc_html__('Manage and reorder homepage sections.', 'mazaq'),
        'priority'    => 30,
    ]);

    $sections_data = [
        'hero' => ['title' => 'Hero Carousel', 'default_enabled' => true, 'default_priority' => 1],
        'articles' => ['title' => 'Latest Articles', 'default_enabled' => true, 'default_priority' => 2],
        'categories' => ['title' => 'Category Highlights', 'default_enabled' => false, 'default_priority' => 3],
        'banner' => ['title' => 'Promotional Banner', 'default_enabled' => false, 'default_priority' => 4],
        'sidebar' => ['title' => 'Sidebar', 'default_enabled' => true, 'default_priority' => 5],
    ];

    foreach ($sections_data as $slug => $data) {
        $section_id = "toc_hp_{$slug}";
        
        $wp_customize->add_section("section_{$section_id}", [
            'title' => esc_html($data['title']),
            'panel' => 'toc_homepage_sections',
        ]);

        $wp_customize->add_setting("{$section_id}_enabled", [
            'default' => $data['default_enabled'],
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        $wp_customize->add_control("{$section_id}_enabled", [
            'label' => esc_html__('Enable Section', 'mazaq'),
            'type' => 'checkbox',
            'section' => "section_{$section_id}",
        ]);

        $wp_customize->add_setting("{$section_id}_priority", [
            'default' => $data['default_priority'],
            'sanitize_callback' => 'absint',
        ]);
        $wp_customize->add_control("{$section_id}_priority", [
            'label' => esc_html__('Section Priority (1-5)', 'mazaq'),
            'type' => 'number',
            'input_attrs' => ['min' => 1, 'max' => 5],
            'section' => "section_{$section_id}",
        ]);

        if ($slug === 'articles') {
            $wp_customize->add_setting("{$section_id}_title", [
                'default' => 'أحدث المقالات المضافة',
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            $wp_customize->add_control("{$section_id}_title", [
                'label' => esc_html__('Section Title', 'mazaq'),
                'type' => 'text',
                'section' => "section_{$section_id}",
            ]);

            $wp_customize->add_setting("{$section_id}_posts_count", [
                'default' => 6,
                'sanitize_callback' => 'absint',
            ]);
            $wp_customize->add_control("{$section_id}_posts_count", [
                'label' => esc_html__('Number of Posts', 'mazaq'),
                'type' => 'number',
                'input_attrs' => ['min' => 2, 'max' => 12],
                'section' => "section_{$section_id}",
            ]);
        } elseif ($slug === 'categories') {
            $wp_customize->add_setting("{$section_id}_title", [
                'default' => 'تصفح حسب الفئة',
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            $wp_customize->add_control("{$section_id}_title", [
                'label' => esc_html__('Section Title', 'mazaq'),
                'type' => 'text',
                'section' => "section_{$section_id}",
            ]);

            $wp_customize->add_setting("{$section_id}_category_count", [
                'default' => 6,
                'sanitize_callback' => 'absint',
            ]);
            $wp_customize->add_control("{$section_id}_category_count", [
                'label' => esc_html__('Number of Categories', 'mazaq'),
                'type' => 'number',
                'input_attrs' => ['min' => 3, 'max' => 12],
                'section' => "section_{$section_id}",
            ]);
        } elseif ($slug === 'banner') {
            $wp_customize->add_setting("{$section_id}_banner_image", [
                'default' => '',
                'sanitize_callback' => 'esc_url_raw',
            ]);
            $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, "{$section_id}_banner_image", [
                'label' => esc_html__('Banner Image URL', 'mazaq'),
                'section' => "section_{$section_id}",
            ]));

            $wp_customize->add_setting("{$section_id}_banner_url", [
                'default' => '',
                'sanitize_callback' => 'esc_url_raw',
            ]);
            $wp_customize->add_control("{$section_id}_banner_url", [
                'label' => esc_html__('Banner Link URL', 'mazaq'),
                'type' => 'url',
                'section' => "section_{$section_id}",
            ]);

            $wp_customize->add_setting("{$section_id}_banner_text", [
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            $wp_customize->add_control("{$section_id}_banner_text", [
                'label' => esc_html__('Banner Overlay Text', 'mazaq'),
                'type' => 'text',
                'section' => "section_{$section_id}",
            ]);
        }
    }
}
add_action('customize_register', 'toc_homepage_sections_customizer');
