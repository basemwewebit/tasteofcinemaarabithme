<?php

declare(strict_types=1);

const MAZAQ_RANDOM_FILM_IDS_TRANSIENT = 'mazaq_random_film_ids';

/**
 * Fetch all published post IDs in batches, then cache them for reuse.
 *
 * @return int[]
 */
function mazaq_random_film_get_candidate_ids(): array
{
    $cached_ids = get_transient(MAZAQ_RANDOM_FILM_IDS_TRANSIENT);
    if (is_array($cached_ids)) {
        return array_values(array_filter(array_map('intval', $cached_ids)));
    }

    $post_ids = [];
    $page = 1;
    $per_page = 200;

    do {
        $query = new WP_Query([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $per_page,
            'paged'                  => $page,
            'fields'                 => 'ids',
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $batch_ids = array_values(array_filter(array_map('intval', (array) $query->posts)));
        if (empty($batch_ids)) {
            break;
        }

        $post_ids = array_merge($post_ids, $batch_ids);
        $page++;
        wp_reset_postdata();
    } while (count($batch_ids) === $per_page);

    $post_ids = array_values(array_unique($post_ids));
    set_transient(MAZAQ_RANDOM_FILM_IDS_TRANSIENT, $post_ids, 30 * MINUTE_IN_SECONDS);

    return $post_ids;
}

/**
 * Fetch post IDs for one list/category without persistent cache.
 *
 * @return int[]
 */
function mazaq_random_film_get_candidate_ids_by_category(int $category_id): array
{
    if ($category_id <= 0) {
        return [];
    }

    $category_term = get_term($category_id, 'category');
    if (!$category_term || is_wp_error($category_term)) {
        return [];
    }

    if ((int) ($category_term->count ?? 0) <= 0) {
        return [];
    }

    $post_ids = [];
    $page = 1;
    $per_page = 200;

    do {
        $query = new WP_Query([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $per_page,
            'paged'                  => $page,
            'fields'                 => 'ids',
            'cat'                    => $category_id,
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $batch_ids = array_values(array_filter(array_map('intval', (array) $query->posts)));
        if (empty($batch_ids)) {
            break;
        }

        $post_ids = array_merge($post_ids, $batch_ids);
        $page++;
        wp_reset_postdata();
    } while (count($batch_ids) === $per_page);

    return array_values(array_unique($post_ids));
}

/**
 * Pick one random post ID, optionally excluding recently shown IDs.
 */
function mazaq_random_film_pick_post_id(array $exclude_ids = [], int $category_id = 0): int
{
    $candidate_ids = $category_id > 0
        ? mazaq_random_film_get_candidate_ids_by_category($category_id)
        : mazaq_random_film_get_candidate_ids();

    if (empty($candidate_ids)) {
        return 0;
    }

    $exclude_lookup = array_fill_keys(array_values(array_filter(array_map('intval', $exclude_ids))), true);
    $filtered_ids = [];

    foreach ($candidate_ids as $candidate_id) {
        if (!isset($exclude_lookup[$candidate_id])) {
            $filtered_ids[] = $candidate_id;
        }
    }

    $pool = !empty($filtered_ids) ? $filtered_ids : $candidate_ids;
    $random_index = wp_rand(0, count($pool) - 1);

    return (int) $pool[$random_index];
}

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

    $post_id = mazaq_random_film_pick_post_id($exclude_ids, $category_id);
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

/**
 * Invalidate cached IDs whenever post publishing state changes.
 */
function mazaq_random_film_invalidate_cache(): void
{
    delete_transient(MAZAQ_RANDOM_FILM_IDS_TRANSIENT);
}

function mazaq_random_film_invalidate_cache_on_save(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    if ($post->post_type !== 'post') {
        return;
    }

    mazaq_random_film_invalidate_cache();
}
add_action('save_post', 'mazaq_random_film_invalidate_cache_on_save', 10, 2);

function mazaq_random_film_invalidate_cache_on_delete(int $post_id): void
{
    if (get_post_type($post_id) !== 'post') {
        return;
    }

    mazaq_random_film_invalidate_cache();
}
add_action('deleted_post', 'mazaq_random_film_invalidate_cache_on_delete');

function mazaq_random_film_invalidate_cache_on_transition(string $new_status, string $old_status, WP_Post $post): void
{
    if ($post->post_type !== 'post') {
        return;
    }

    if ($new_status === $old_status) {
        return;
    }

    mazaq_random_film_invalidate_cache();
}
add_action('transition_post_status', 'mazaq_random_film_invalidate_cache_on_transition', 10, 3);
