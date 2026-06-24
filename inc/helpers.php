<?php

declare(strict_types=1);

function mazaq_calculate_reading_time_minutes(int $post_id): int
{
    $content = (string) (get_post_field('post_content', $post_id) ?: '');
    if ($content === '') {
        return 1;
    }

    $clean_text = wp_strip_all_tags(strip_shortcodes($content));
    $clean_text = html_entity_decode($clean_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $clean_text = trim(preg_replace('/\s+/u', ' ', $clean_text) ?: '');

    $word_count = 0;
    if ($clean_text !== '') {
        preg_match_all('/[\p{L}\p{N}]+(?:[\-\'\x{2019}][\p{L}\p{N}]+)*/u', $clean_text, $matches);
        $word_count = count($matches[0]);
    }

    $words_per_minute = (int) apply_filters('mazaq_reading_words_per_minute', 180);
    $words_per_minute = $words_per_minute > 0 ? $words_per_minute : 180;

    return (int) max(1, ceil($word_count / $words_per_minute));
}

function mazaq_reading_time(int $post_id): string
{
    $minutes = (int) get_post_meta($post_id, '_mazaq_reading_time_minutes', true);
    if ($minutes <= 0) {
        $minutes = mazaq_calculate_reading_time_minutes($post_id);
        update_post_meta($post_id, '_mazaq_reading_time_minutes', $minutes);
    }

    $label = $minutes === 1 ? __('دقيقة قراءة', 'mazaq') : __('دقائق قراءة', 'mazaq');

    return sprintf('%d %s', $minutes, $label);
}

function mazaq_update_reading_time_meta_on_save(int $post_id, WP_Post $post): void
{
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    if (!in_array($post->post_type, ['post', 'page'], true)) {
        return;
    }

    if ($post->post_status === 'auto-draft') {
        return;
    }

    $minutes = mazaq_calculate_reading_time_minutes($post_id);
    update_post_meta($post_id, '_mazaq_reading_time_minutes', $minutes);
}
add_action('save_post', 'mazaq_update_reading_time_meta_on_save', 10, 2);

/**
 * Presentation helper: turn a free-text film rating into star-display data.
 *
 * Single source of truth — this delegates to the exact same parser the schema
 * layer uses (mazaq_schema_parse_rating in inc/schema-film.php), so the visible
 * ⭐ rating in the infobox and the Schema.org reviewRating can never diverge.
 * Returns null when the rating can't be parsed; callers should then fall back
 * to printing the raw text.
 *
 * @return array{value:int|float,best:int|float,percent:float,label:string}|null
 */
function mazaq_film_rating_stars(string $raw): ?array
{
    if (!function_exists('mazaq_schema_parse_rating')) {
        return null;
    }

    $rating = mazaq_schema_parse_rating($raw);
    if ($rating === null) {
        return null;
    }

    $best  = (float) $rating['bestRating'];
    $value = (float) $rating['ratingValue'];
    $percent = $best > 0 ? max(0.0, min(100.0, ($value / $best) * 100)) : 0.0;

    return [
        'value'   => $rating['ratingValue'],
        'best'    => $rating['bestRating'],
        'percent' => round($percent, 2),
        'label'   => sprintf(
            /* translators: 1: rating value, 2: best possible rating */
            __('التقييم: %1$s من %2$s', 'mazaq'),
            $rating['ratingValue'],
            $rating['bestRating']
        ),
    ];
}

function mazaq_relative_date(string $date): string
{
    $timestamp = strtotime($date);
    if (!$timestamp) {
        return '';
    }

    $diff = time() - $timestamp;
    if ($diff < DAY_IN_SECONDS) {
        return __('اليوم', 'mazaq');
    }
    if ($diff < 2 * DAY_IN_SECONDS) {
        return __('أمس', 'mazaq');
    }
    if ($diff < WEEK_IN_SECONDS) {
        return sprintf(__('منذ %d أيام', 'mazaq'), (int) floor($diff / DAY_IN_SECONDS));
    }
    if ($diff < 2 * WEEK_IN_SECONDS) {
        return __('منذ أسبوع', 'mazaq');
    }

    return get_date_from_gmt(gmdate('Y-m-d H:i:s', $timestamp), 'j F Y');
}

/**
 * Apply Levantine month naming on frontend and frontend AJAX responses only.
 */
function mazaq_should_localize_frontend_dates(): bool
{
    if (is_admin() && !wp_doing_ajax()) {
        return false;
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();

    return strpos((string) $locale, 'ar') === 0;
}

/**
 * Convert Arabic and English month names to Levantine Arabic names.
 */
function mazaq_convert_to_levantine_month_names(string $date_text): string
{
    if ($date_text === '') {
        return $date_text;
    }

    $month_map = [
        'يناير'   => 'كانون الثاني',
        'فبراير'  => 'شباط',
        'مارس'    => 'آذار',
        'أبريل'   => 'نيسان',
        'ابريل'   => 'نيسان',
        'مايو'    => 'أيار',
        'يونيو'   => 'حزيران',
        'يوليو'   => 'تموز',
        'أغسطس'   => 'آب',
        'اغسطس'   => 'آب',
        'سبتمبر'  => 'أيلول',
        'أكتوبر'  => 'تشرين الأول',
        'اكتوبر'  => 'تشرين الأول',
        'نوفمبر'  => 'تشرين الثاني',
        'ديسمبر'  => 'كانون الأول',
        'January'   => 'كانون الثاني',
        'February'  => 'شباط',
        'March'     => 'آذار',
        'April'     => 'نيسان',
        'May'       => 'أيار',
        'June'      => 'حزيران',
        'July'      => 'تموز',
        'August'    => 'آب',
        'September' => 'أيلول',
        'October'   => 'تشرين الأول',
        'November'  => 'تشرين الثاني',
        'December'  => 'كانون الأول',
    ];

    return strtr($date_text, $month_map);
}

function mazaq_filter_display_date_to_levantine(string $formatted_date): string
{
    if (!mazaq_should_localize_frontend_dates()) {
        return $formatted_date;
    }

    return mazaq_convert_to_levantine_month_names($formatted_date);
}
add_filter('get_the_date', 'mazaq_filter_display_date_to_levantine', 10, 1);
add_filter('get_the_modified_date', 'mazaq_filter_display_date_to_levantine', 10, 1);

function mazaq_get_excerpt(int $length = 24): string
{
    $excerpt = get_the_excerpt();
    return wp_trim_words(wp_strip_all_tags((string) $excerpt), $length, '...');
}

function mazaq_balance_content_tags(string $content): string
{
    if (is_admin()) {
        return $content;
    }

    return force_balance_tags($content);
}
add_filter('the_content', 'mazaq_balance_content_tags', 999);

/**
 * Get all eligible hero post IDs based on priority:
 * 1. ACF option 'hero_featured_post'
 * 2. Daily rotation hero posts (auto-generated)
 * 3. Sticky posts
 * 4. Latest posts fallback
 *
 * @return int[]
 */
function mazaq_get_hero_post_ids(): array
{
    $ids = [];
    $hero_rotation_enabled = true;

    if (function_exists('mazaq_content_rotation_get_settings')) {
        $rotation_settings = mazaq_content_rotation_get_settings();
        $hero_rotation_enabled = !empty($rotation_settings['hero_enabled']);
    }

    // 1. ACF Option (highest priority - manual override)
    $acf_id = function_exists('get_field') ? (int) get_field('hero_featured_post', 'option') : 0;
    if ($acf_id > 0) {
        return [$acf_id];
    }

    // 2. Daily Rotation Hero Posts (auto-generated daily batch)
    if ($hero_rotation_enabled && function_exists('mazaq_hero_daily_get_state')) {
        $daily_state = mazaq_hero_daily_get_state();
        if (!empty($daily_state['hero_post_ids']) && $daily_state['rotation_date'] === mazaq_hero_daily_today()) {
            return $daily_state['hero_post_ids'];
        }

        // Generate batch if not exists for today (lazy generation)
        $daily_state = mazaq_hero_daily_prepare_today_batch(false);
        if (!empty($daily_state['hero_post_ids'])) {
            return $daily_state['hero_post_ids'];
        }
    }

    // 3. Sticky Posts (legacy fallback)
    $sticky = get_option('sticky_posts');
    if (!empty($sticky)) {
        $ids = array_map('intval', (array) $sticky);
    }

    // 4. Fallback to latest posts (2-3 posts)
    if (empty($ids)) {
        $latest = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => 3,
            'fields'         => 'ids',
            'post_status'    => 'publish',
        ]);
        if (!empty($latest)) {
            $ids = array_map('intval', $latest);
        }
    }

    // Filter out zero/falsy values
    return array_values(array_filter($ids));
}

function mazaq_get_hero_post_id(): int
{
    return mazaq_get_hero_post_ids()[0] ?? 0;
}

function mazaq_nav_menu_link_attributes(array $atts, WP_Post $menu_item): array
{
    if (!empty($menu_item->current) || in_array('current-menu-item', (array) $menu_item->classes, true)) {
        $atts['aria-current'] = 'page';
    }

    return $atts;
}
add_filter('nav_menu_link_attributes', 'mazaq_nav_menu_link_attributes', 10, 2);

function mazaq_get_post_thumbnail_alt(int $post_id, string $fallback = ''): string
{
    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    if ($thumbnail_id > 0) {
        $alt = trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));
        if ($alt !== '') {
            return $alt;
        }
    }

    $fallback = trim(wp_strip_all_tags($fallback));
    return $fallback !== '' ? $fallback : get_the_title($post_id);
}

function mazaq_get_category_tint(int $term_id): string
{
    $palette = ['#8E2A2A', '#C9A227', '#2A4A8E', '#4E4A40', '#D4C9A8', '#7C3AED'];
    return $palette[abs($term_id) % count($palette)];
}

function mazaq_extract_article_headings(int $post_id): array
{
    $content = (string) get_post_field('post_content', $post_id);
    if ($content === '') {
        return [];
    }

    preg_match_all('/<h([23])\b([^>]*)>(.*?)<\/h\1>/isu', $content, $matches, PREG_SET_ORDER);
    $headings = [];
    $index = 0;

    foreach ($matches as $match) {
        $text = trim(html_entity_decode(wp_strip_all_tags((string) $match[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($text === '') {
            continue;
        }

        $index++;
        $id = 'article-section-' . $index;
        if (preg_match('/\sid=(["\'])(.*?)\1/isu', (string) $match[2], $id_match) && trim((string) $id_match[2]) !== '') {
            $id = sanitize_html_class((string) $id_match[2]);
        }

        $headings[] = [
            'id' => $id,
            'level' => (int) $match[1],
            'text' => $text,
        ];
    }

    return $headings;
}

function mazaq_add_article_heading_ids(string $content): string
{
    if (is_admin() || !is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $index = 0;
    return (string) preg_replace_callback(
        '/<h([23])\b([^>]*)>(.*?)<\/h\1>/isu',
        static function (array $matches) use (&$index): string {
            $attributes = (string) $matches[2];
            if (preg_match('/\sid=(["\'])(.*?)\1/isu', $attributes)) {
                return (string) $matches[0];
            }

            $index++;
            return '<h' . $matches[1] . $attributes . ' id="article-section-' . $index . '">' . $matches[3] . '</h' . $matches[1] . '>';
        },
        $content
    );
}
add_filter('the_content', 'mazaq_add_article_heading_ids', 8);

function mazaq_ajax_search_suggestions(): void
{
    check_ajax_referer('mazaq_search_suggestions_nonce', 'nonce');

    $query_text = isset($_GET['query']) ? sanitize_text_field(wp_unslash((string) $_GET['query'])) : '';
    $query_text = trim($query_text);
    $query_length = function_exists('mb_strlen') ? mb_strlen($query_text) : strlen($query_text);
    if ($query_length < 2) {
        wp_send_json_success(['items' => []]);
    }

    $query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 5,
        's' => $query_text,
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
    ]);

    $items = [];
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $categories = get_the_category($post_id);
        $items[] = [
            'title' => get_the_title($post_id),
            'url' => get_permalink($post_id),
            'date' => get_the_date('j F Y', $post_id),
            'category' => !empty($categories) ? $categories[0]->name : __('مقال', 'mazaq'),
            'thumbnail' => get_the_post_thumbnail_url($post_id, 'sidebar-thumbnail') ?: '',
            'alt' => mazaq_get_post_thumbnail_alt($post_id, get_the_title($post_id)),
        ];
    }
    wp_reset_postdata();

    wp_send_json_success(['items' => $items]);
}
add_action('wp_ajax_mazaq_search_suggestions', 'mazaq_ajax_search_suggestions');
add_action('wp_ajax_nopriv_mazaq_search_suggestions', 'mazaq_ajax_search_suggestions');

function mazaq_ajax_newsletter_signup(): void
{
    check_ajax_referer('mazaq_newsletter_signup_nonce', 'nonce');

    $email = isset($_POST['email']) ? sanitize_email(wp_unslash((string) $_POST['email'])) : '';
    if ($email === '' || !is_email($email)) {
        wp_send_json_error(['message' => __('أدخل بريدًا إلكترونيًا صحيحًا.', 'mazaq')], 400);
    }

    $hash = hash('sha256', strtolower($email));
    $existing = new WP_Query([
        'post_type' => 'contact_message',
        'post_status' => 'private',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => [
            [
                'key' => '_newsletter_email_hash',
                'value' => $hash,
            ],
        ],
    ]);

    if ($existing->have_posts()) {
        wp_send_json_success(['message' => __('أنت مشترك بالفعل. شكرًا لاهتمامك.', 'mazaq')]);
    }

    $post_id = wp_insert_post([
        'post_type' => 'contact_message',
        'post_status' => 'private',
        'post_title' => sprintf(__('اشتراك نشرة: %s', 'mazaq'), $email),
        'post_content' => __('طلب اشتراك في النشرة السينمائية.', 'mazaq'),
    ], true);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => __('تعذر تسجيل الاشتراك الآن. حاول لاحقًا.', 'mazaq')], 500);
    }

    update_post_meta((int) $post_id, '_contact_email', $email);
    update_post_meta((int) $post_id, '_contact_name', __('مشترك النشرة', 'mazaq'));
    update_post_meta((int) $post_id, '_newsletter_email_hash', $hash);

    wp_send_json_success(['message' => __('تم الاشتراك بنجاح. أهلاً بك في النشرة.', 'mazaq')]);
}
add_action('wp_ajax_mazaq_newsletter_signup', 'mazaq_ajax_newsletter_signup');
add_action('wp_ajax_nopriv_mazaq_newsletter_signup', 'mazaq_ajax_newsletter_signup');
