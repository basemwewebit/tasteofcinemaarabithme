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
