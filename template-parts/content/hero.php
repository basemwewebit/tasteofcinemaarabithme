<?php

/**
 * Front-page feature hero: one marquee feature plus a three-up "bill" of editor picks.
 *
 * The hero IDs are curated in the admin, so this template hardens against a live
 * site: IDs that now point at trashed or deleted posts, duplicates, an empty
 * curation, and posts that have lost their title or thumbnail.
 */

$hero_is_published = static function (int $post_id): bool {
    return $post_id > 0 && get_post_status($post_id) === 'publish';
};

$hero_post_ids = array_values(array_unique(array_filter(
    array_map('intval', (array) mazaq_get_hero_post_ids()),
    $hero_is_published
)));

// Backfill from the most recent posts so the homepage always opens on a valid feature plus a full bill.
if (count($hero_post_ids) < 4) {
    $recent_fill = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 4 - count($hero_post_ids),
        'fields' => 'ids',
        'post__not_in' => $hero_post_ids ?: [0],
        'ignore_sticky_posts' => true,
    ]);

    $hero_post_ids = array_values(array_unique(array_merge($hero_post_ids, array_map('intval', $recent_fill))));
}

if (empty($hero_post_ids)) {
    return;
}

$feature_post_id = $hero_post_ids[0];
$editor_pick_ids = array_slice($hero_post_ids, 1, 3);

$hero_logo_url = get_template_directory_uri() . '/assets/images/logo.webp';

$get_title = static function (int $post_id): string {
    $title = get_the_title($post_id);

    return $title !== '' ? $title : __('مقال بلا عنوان', 'mazaq');
};

$feature_title = $get_title($feature_post_id);
$feature_excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($feature_post_id)), 28, '...');

$get_primary_category = static function (int $post_id): string {
    $categories = get_the_category($post_id);

    return !empty($categories) ? $categories[0]->name : __('مقال مميز', 'mazaq');
};

$render_media = static function (int $post_id, string $size, bool $is_priority, string $fallback_logo_url, string $class_name): void {
    if (has_post_thumbnail($post_id)) {
        // The full-width marquee spans the page; the bill frames sit three-up (~28rem) and become an ~82vw filmstrip on phones.
        $sizes = $size === 'hero-poster'
            ? '(min-width: 1440px) 88rem, (min-width: 1024px) 92vw, 100vw'
            : '(min-width: 1024px) 28rem, (min-width: 768px) 30vw, 82vw';
        $title = get_the_title($post_id);
        $attributes = [
            'class' => $class_name,
            'loading' => $is_priority ? 'eager' : 'lazy',
            'decoding' => 'async',
            'sizes' => $sizes,
            'alt' => mazaq_get_post_thumbnail_alt($post_id, $title !== '' ? $title : __('مقال', 'mazaq')),
        ];

        if ($is_priority) {
            $attributes['fetchpriority'] = 'high';
            $attributes['data-no-lazy'] = '1';
        }

        echo get_the_post_thumbnail($post_id, $size, $attributes);

        return;
    }
    ?>
    <div class="<?php echo esc_attr($class_name . ' feature-hero__fallback'); ?>" aria-hidden="true">
        <img src="<?php echo esc_url($fallback_logo_url); ?>" alt="" width="474" height="460" loading="<?php echo $is_priority ? 'eager' : 'lazy'; ?>" decoding="async">
    </div>
    <?php
};
?>

<section class="feature-hero-section" aria-labelledby="feature-hero-title">
    <div class="feature-hero <?php echo empty($editor_pick_ids) ? 'feature-hero--solo' : ''; ?>">
        <article class="feature-hero__feature">
            <a href="<?php echo esc_url(get_permalink($feature_post_id)); ?>" class="feature-hero__link group" aria-label="<?php echo esc_attr(sprintf(__('اقرأ المقال المميز: %s', 'mazaq'), $feature_title)); ?>">
                <div class="feature-hero__media" aria-hidden="true">
                    <?php $render_media($feature_post_id, 'hero-poster', true, $hero_logo_url, 'feature-hero__image'); ?>
                </div>
                <span class="feature-hero__shade" aria-hidden="true"></span>
                <span class="feature-hero__beam" aria-hidden="true"></span>
                <span class="feature-hero__grain" aria-hidden="true"></span>
                <span class="feature-hero__perforation" aria-hidden="true"></span>

                <div class="feature-hero__content">
                    <div class="feature-hero__kicker">
                        <span><?php esc_html_e('واجهة اليوم', 'mazaq'); ?></span>
                        <span><?php echo esc_html($get_primary_category($feature_post_id)); ?></span>
                    </div>
                    <h2 id="feature-hero-title" class="feature-hero__title"><?php echo esc_html($feature_title); ?></h2>
                    <?php if ($feature_excerpt !== '') : ?>
                        <p class="feature-hero__excerpt"><?php echo esc_html($feature_excerpt); ?></p>
                    <?php endif; ?>
                    <div class="feature-hero__footer">
                        <span class="feature-hero__cta">
                            <span><?php esc_html_e('افتح القراءة', 'mazaq'); ?></span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11 6-6 6 6 6"></path>
                            </svg>
                        </span>
                        <span class="feature-hero__meta">
                            <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $feature_post_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $feature_post_id)); ?></time>
                            <span aria-hidden="true">•</span>
                            <span class="num"><?php echo esc_html(mazaq_reading_time($feature_post_id)); ?></span>
                        </span>
                    </div>
                </div>
            </a>
        </article>

        <?php if (!empty($editor_pick_ids)) : ?>
            <aside class="feature-hero__bill" aria-labelledby="feature-hero-bill-title">
                <div class="feature-hero__bill-head">
                    <h2 id="feature-hero-bill-title" class="feature-hero__bill-title"><?php esc_html_e('ما يستحق القراءة بعده', 'mazaq'); ?></h2>
                    <p class="feature-hero__bill-kicker"><?php esc_html_e('اختيارات التحرير', 'mazaq'); ?></p>
                </div>
                <ol class="feature-hero__bill-list" role="list" style="--bill-count: <?php echo (int) min(3, max(1, count($editor_pick_ids))); ?>;">
                    <?php foreach ($editor_pick_ids as $pick_index => $pick_id) : ?>
                        <?php $pick_title = $get_title($pick_id); ?>
                        <li class="bill-card" style="--i: <?php echo (int) $pick_index; ?>;">
                            <a href="<?php echo esc_url(get_permalink($pick_id)); ?>" class="bill-card__link group" aria-label="<?php echo esc_attr(sprintf(__('اقرأ: %s', 'mazaq'), $pick_title)); ?>">
                                <div class="bill-card__media">
                                    <?php // The three picks read as a programme in order; landscape stills (card-thumbnail, 800x500) suit the three-up row.
                                          $render_media($pick_id, 'card-thumbnail', false, $hero_logo_url, 'bill-card__image'); ?>
                                    <span class="bill-card__shade" aria-hidden="true"></span>
                                    <span class="bill-card__rank num" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $pick_index + 1)); ?></span>
                                </div>
                                <div class="bill-card__body">
                                    <span class="bill-card__category"><?php echo esc_html($get_primary_category($pick_id)); ?></span>
                                    <h3 class="bill-card__title"><?php echo esc_html($pick_title); ?></h3>
                                    <span class="bill-card__meta">
                                        <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $pick_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $pick_id)); ?></time>
                                        <span aria-hidden="true">•</span>
                                        <span class="num"><?php echo esc_html(mazaq_reading_time($pick_id)); ?></span>
                                    </span>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </aside>
        <?php endif; ?>
    </div>
</section>
