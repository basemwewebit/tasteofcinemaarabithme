<?php

declare(strict_types=1);

/**
 * Build short synopsis with content fallback when excerpt is empty.
 */
function mazaq_random_film_build_excerpt(int $post_id, int $length = 30): string
{
    $raw_excerpt = trim(wp_strip_all_tags((string) get_post_field('post_excerpt', $post_id)));
    if ($raw_excerpt !== '') {
        return wp_trim_words($raw_excerpt, $length, '...');
    }

    $content = (string) get_post_field('post_content', $post_id);
    $content = wp_strip_all_tags(strip_shortcodes($content));
    $content = trim(preg_replace('/\s+/u', ' ', $content) ?: '');

    return wp_trim_words($content, $length, '...');
}

/**
 * Build frontend payload for one suggested film.
 *
 * @return array<string, mixed>
 */
function mazaq_random_film_build_payload(int $post_id): array
{
    if ($post_id <= 0 || get_post_status($post_id) !== 'publish') {
        return [];
    }

    $title = trim(wp_strip_all_tags((string) get_the_title($post_id)));
    $excerpt = mazaq_random_film_build_excerpt($post_id, 30);
    $categories = get_the_category($post_id);
    $category_name = !empty($categories) && isset($categories[0]->name)
        ? (string) $categories[0]->name
        : (string) __('غير مصنف', 'mazaq');

    $image_url = get_the_post_thumbnail_url($post_id, 'card-thumbnail');
    if (!$image_url) {
        $image_url = get_template_directory_uri() . '/assets/images/og-cover.jpg';
    }

    return [
        'id' => $post_id,
        'title' => $title !== '' ? $title : (string) __('فيلم بدون عنوان', 'mazaq'),
        'excerpt' => $excerpt !== '' ? $excerpt : (string) __('لا توجد نبذة متاحة حالياً.', 'mazaq'),
        'permalink' => esc_url_raw((string) get_permalink($post_id)),
        'image' => esc_url_raw((string) $image_url),
        'category' => $category_name,
    ];
}

/**
 * AJAX: return one random film suggestion for homepage popup.
 */
function mazaq_ajax_get_random_film(): void
{
    check_ajax_referer('mazaq_random_film_nonce', 'nonce');

    $category_id = isset($_POST['category_id']) ? max(0, (int) $_POST['category_id']) : 0;

    $exclude_ids = [];
    if (isset($_POST['exclude_ids']) && is_array($_POST['exclude_ids'])) {
        $exclude_ids = array_values(array_filter(array_map('intval', $_POST['exclude_ids'])));
    }

    if (count($exclude_ids) > 20) {
        $exclude_ids = array_slice($exclude_ids, -20);
    }

    $post_id = mazaq_article_pool_random_id($exclude_ids, $category_id);
    if ($post_id <= 0) {
        $no_results_message = $category_id > 0
            ? __('لا توجد مقالات متاحة في هذه القائمة حالياً.', 'mazaq')
            : __('لا توجد أفلام متاحة حالياً.', 'mazaq');

        wp_send_json_error([
            'message' => $no_results_message,
        ], 404);
    }

    $payload = mazaq_random_film_build_payload($post_id);
    if (empty($payload)) {
        wp_send_json_error([
            'message' => __('تعذر جلب اقتراح فيلم حالياً.', 'mazaq'),
        ], 500);
    }

    wp_send_json_success([
        'film' => $payload,
    ]);
}
add_action('wp_ajax_mazaq_get_random_film', 'mazaq_ajax_get_random_film');
add_action('wp_ajax_nopriv_mazaq_get_random_film', 'mazaq_ajax_get_random_film');
