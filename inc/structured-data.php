<?php

declare(strict_types=1);

/**
 * Schema.org Structured Data Implementation
 * Adds JSON-LD structured data for better SEO and Google Rich Results
 */

/**
 * Output Article Schema for single posts
 */
function toc_article_schema(): void
{
    if (!is_singular('post')) {
        return;
    }

    global $post;

    $author_id = $post->post_author;
    $author_name = get_the_author_meta('display_name', $author_id);
    $categories = get_the_category($post->ID);
    $category = !empty($categories) ? $categories[0]->name : '';
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
    $published_time = get_the_date('c');
    $modified_time = get_the_modified_date('c');

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => get_the_title(),
        'description' => wp_strip_all_tags(get_the_excerpt()),
        'datePublished' => $published_time,
        'dateModified' => $modified_time,
        'author' => [
            '@type' => 'Person',
            'name' => $author_name,
            'url' => get_author_posts_url($author_id),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
        ],
    ];

    if ($category) {
        $schema['articleSection'] = $category;
    }

    if ($thumbnail) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url' => $thumbnail,
        ];
    }

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

/**
 * Output Breadcrumb Schema
 */
function toc_breadcrumb_schema(): void
{
    $breadcrumbs = [];
    $position = 1;

    // Always include home
    $breadcrumbs[] = [
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => __('الرئيسية', 'mazaq'),
        'item' => home_url('/'),
    ];

    if (is_singular('post')) {
        // Single post breadcrumbs
        $categories = get_the_category();
        if (!empty($categories)) {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $categories[0]->name,
                'item' => get_category_link($categories[0]->term_id),
            ];
        }

        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => get_the_title(),
            'item' => get_permalink(),
        ];
    } elseif (is_category()) {
        // Category page
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => single_cat_title('', false),
            'item' => get_category_link(get_queried_object()->term_id),
        ];
    } elseif (is_tag()) {
        // Tag page
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => single_tag_title('', false),
            'item' => get_tag_link(get_queried_object()->term_id),
        ];
    }

    if (empty($breadcrumbs) || count($breadcrumbs) <= 1) {
        return;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbs,
    ];

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

/**
 * Output Organization Schema
 */
function toc_organization_schema(): void
{
    $logo_url = get_template_directory_uri() . '/assets/images/logo.webp';
    $site_url = home_url('/');

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'url' => $site_url,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logo_url,
        ],
        'description' => get_bloginfo('description'),
    ];

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

/**
 * Output WebSite Schema with potential search action
 */
function toc_website_schema(): void
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'description' => get_bloginfo('description'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => home_url('/?s={search_term_string}'),
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

/**
 * Output CollectionPage Schema for archive pages
 */
function toc_collection_page_schema(): void
{
    if (!is_archive() && !is_home()) {
        return;
    }

    $page_title = wp_get_document_title();
    $page_url = home_url($_SERVER['REQUEST_URI'] ?? '/');

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $page_title,
        'url' => $page_url,
        'description' => get_bloginfo('description'),
    ];

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

// Hook all schema outputs to wp_head
add_action('wp_head', 'toc_organization_schema', 1);
add_action('wp_head', 'toc_website_schema', 2);
add_action('wp_head', 'toc_breadcrumb_schema', 5);
add_action('wp_head', 'toc_article_schema', 10);
add_action('wp_head', 'toc_collection_page_schema', 10);
