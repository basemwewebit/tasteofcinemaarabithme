<?php

declare(strict_types=1);

/** Front-page screening programme: one decisive feature with three related reads. */

$is_published = static fn (int $post_id): bool => $post_id > 0 && get_post_status($post_id) === 'publish';
$hero_post_ids = array_values(array_unique(array_filter(array_map('intval', (array) mazaq_get_hero_post_ids()), $is_published)));

if (count($hero_post_ids) < 4) {
    $hero_post_ids = array_values(array_unique(array_merge($hero_post_ids, array_map('intval', get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 4 - count($hero_post_ids),
        'fields' => 'ids',
        'post__not_in' => $hero_post_ids ?: [0],
        'ignore_sticky_posts' => true,
    ])))));
}

if (empty($hero_post_ids)) {
    return;
}

$feature_id = $hero_post_ids[0];
$programme_ids = array_slice($hero_post_ids, 1, 3);
$theme_uri = get_template_directory_uri();
$plate_fallbacks = [
    $theme_uri . '/assets/plates/program-one-still.png',
    $theme_uri . '/assets/plates/program-two-still.png',
    $theme_uri . '/assets/plates/program-three-still.png',
];

$title_for = static function (int $post_id): string {
    $title = get_the_title($post_id);
    return $title !== '' ? $title : __('مقال بلا عنوان', 'mazaq');
};

$category_for = static function (int $post_id): string {
    $categories = get_the_category($post_id);
    return !empty($categories) ? $categories[0]->name : __('اختيار المحرّر', 'mazaq');
};

$render_image = static function (int $post_id, string $size, string $class_name, string $fallback, bool $priority = false): void {
    if (has_post_thumbnail($post_id)) {
        $attributes = [
            'class' => $class_name,
            'alt' => mazaq_get_post_thumbnail_alt($post_id, get_the_title($post_id)),
            'loading' => $priority ? 'eager' : 'lazy',
            'decoding' => 'async',
            'sizes' => $priority ? '(min-width: 1024px) 58vw, 100vw' : '(min-width: 1024px) 11rem, 36vw',
        ];
        if ($priority) {
            $attributes['fetchpriority'] = 'high';
            $attributes['data-no-lazy'] = '1';
        }
        echo get_the_post_thumbnail($post_id, $size, $attributes);
        return;
    }
    ?>
    <img src="<?php echo esc_url($fallback); ?>" class="<?php echo esc_attr($class_name); ?>" alt="" loading="<?php echo $priority ? 'eager' : 'lazy'; ?>" decoding="async">
    <?php
};

$feature_title = $title_for($feature_id);
$feature_excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($feature_id)), 27, '…');
?>

<section class="screening-hero" aria-labelledby="screening-feature-title">
    <span class="screening-hero__film-edge" aria-hidden="true"></span>

    <div class="screening-hero__stage">
        <article class="screening-feature">
            <a class="screening-feature__link" href="<?php echo esc_url(get_permalink($feature_id)); ?>" aria-label="<?php echo esc_attr(sprintf(__('اقرأ المقال المميز: %s', 'mazaq'), $feature_title)); ?>">
                <span class="screening-feature__media" aria-hidden="true">
                    <?php $render_image($feature_id, 'hero-poster', 'screening-feature__image', $theme_uri . '/assets/plates/feature-still.png', true); ?>
                </span>
                <span class="screening-feature__shade" aria-hidden="true"></span>
                <span class="screening-feature__copy">
                    <span id="screening-feature-title" class="screening-feature__title" role="heading" aria-level="2"><?php echo esc_html($feature_title); ?></span>
                    <?php if ($feature_excerpt !== '') : ?>
                        <span class="screening-feature__deck"><?php echo esc_html($feature_excerpt); ?></span>
                    <?php endif; ?>
                    <span class="screening-feature__footer">
                        <span class="screening-feature__action">
                            <?php esc_html_e('اقرأ المقال', 'mazaq'); ?>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"></path></svg>
                        </span>
                        <span class="screening-feature__meta">
                            <span><?php echo esc_html($category_for($feature_id)); ?></span>
                            <span aria-hidden="true">•</span>
                            <span class="num"><?php echo esc_html(mazaq_reading_time($feature_id)); ?></span>
                            <span aria-hidden="true">•</span>
                            <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $feature_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $feature_id)); ?></time>
                        </span>
                    </span>
                </span>
            </a>
        </article>

        <?php if (!empty($programme_ids)) : ?>
            <aside class="screening-programme" aria-labelledby="screening-programme-title">
                <h2 id="screening-programme-title" class="screening-programme__title"><?php esc_html_e('ما يستحق القراءة بعده', 'mazaq'); ?></h2>
                <ol class="screening-programme__list">
                    <?php foreach ($programme_ids as $index => $post_id) : ?>
                        <?php $item_title = $title_for($post_id); ?>
                        <li class="screening-programme__item">
                            <a class="screening-programme__link" href="<?php echo esc_url(get_permalink($post_id)); ?>">
                                <span class="screening-programme__media" aria-hidden="true">
                                    <?php $render_image($post_id, 'card-thumbnail', 'screening-programme__image', $plate_fallbacks[$index] ?? $plate_fallbacks[0]); ?>
                                </span>
                                <span class="screening-programme__copy">
                                    <span class="screening-programme__category"><?php echo esc_html($category_for($post_id)); ?></span>
                                    <span class="screening-programme__item-title"><?php echo esc_html($item_title); ?></span>
                                    <time class="screening-programme__date num" datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></time>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </aside>
        <?php endif; ?>
    </div>
</section>
