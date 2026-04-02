<?php

$hero_post_ids = mazaq_get_hero_post_ids();

if (empty($hero_post_ids)) {
    return;
}

$hero_post_ids = array_slice($hero_post_ids, 0, 3);
$hero_logo_url = get_template_directory_uri() . '/assets/images/logo.webp';
$hero_total = count($hero_post_ids);
$has_multiple_slides = $hero_total > 1;

$render_hero_media = static function (int $post_id, bool $is_priority, string $logo_url): void {
    if (has_post_thumbnail($post_id)) {
        echo get_the_post_thumbnail(
            $post_id,
            'hero-poster',
            [
                'class' => 'hero-media__image',
                'sizes' => '100vw',
                'loading' => $is_priority ? 'eager' : 'lazy',
                'fetchpriority' => $is_priority ? 'high' : 'auto',
                'decoding' => 'async',
            ]
        );

        return;
    }
    ?>
    <div class="hero-media hero-media--fallback" aria-hidden="true">
        <span class="hero-media__fallback-glow"></span>
        <span class="hero-media__fallback-noise"></span>
        <img src="<?php echo esc_url($logo_url); ?>" alt="" class="hero-media__fallback-logo" loading="lazy" decoding="async">
    </div>
    <?php
};

$render_hero_content = static function (int $post_id, string $title_tag = 'h2'): void {
    $categories = get_the_category($post_id);
    $title = get_the_title($post_id);
    $excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($post_id)), 20, '...');
    $reading_time = function_exists('mazaq_reading_time') ? mazaq_reading_time($post_id) : '';
    $category_name = !empty($categories) ? $categories[0]->name : __('مقال مميز', 'mazaq');
    ?>
    <div class="hero-shell__inner">
        <div class="hero-copy">
            <div class="hero-copy__eyebrow">
                <span class="hero-site-label"><?php esc_html_e('مذاق السينما', 'mazaq'); ?></span>
                <span class="hero-copy__divider" aria-hidden="true"></span>
                <span class="hero-badge"><?php echo esc_html($category_name); ?></span>
            </div>
            <<?php echo tag_escape($title_tag); ?> class="hero-title"><?php echo esc_html($title); ?></<?php echo tag_escape($title_tag); ?>>
            <?php if (!empty($excerpt)) : ?>
                <p class="hero-excerpt"><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>
            <div class="hero-meta" aria-label="<?php esc_attr_e('Post information', 'mazaq'); ?>">
                <span><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></span>
                <?php if ($reading_time !== '') : ?>
                    <span class="hero-meta__separator" aria-hidden="true"></span>
                    <span><?php echo esc_html($reading_time); ?></span>
                <?php endif; ?>
            </div>
            <span class="hero-cta">
                <span><?php esc_html_e('اقرأ المقال', 'mazaq'); ?></span>
                <span class="hero-cta__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M19 12H5"></path>
                        <path d="M11 6l-6 6 6 6"></path>
                    </svg>
                </span>
            </span>
        </div>
    </div>
    <?php
};
?>
<section class="hero-section" aria-label="<?php echo esc_attr($has_multiple_slides ? __('Featured articles', 'mazaq') : __('Featured article', 'mazaq')); ?>">
    <div class="hero-carousel <?php echo $has_multiple_slides ? 'has-multiple' : 'has-single'; ?>" data-interval="6000" data-total="<?php echo esc_attr((string) $hero_total); ?>" tabindex="<?php echo $has_multiple_slides ? '0' : '-1'; ?>">
        <div class="hero-carousel__viewport">
            <div class="hero-carousel__track">
                <?php foreach ($hero_post_ids as $slide_index => $slide_post_id) : ?>
                    <?php
                    $is_active_slide = $slide_index === 0;
                    $title_tag = !$has_multiple_slides ? 'h1' : 'h2';
                    ?>
                    <article class="hero-carousel__slide <?php echo $is_active_slide ? 'is-active' : ''; ?>" data-slide-index="<?php echo esc_attr((string) $slide_index); ?>" aria-hidden="<?php echo $is_active_slide ? 'false' : 'true'; ?>">
                        <a href="<?php echo esc_url(get_permalink($slide_post_id)); ?>" class="hero-shell group">
                            <div class="hero-media">
                                <?php $render_hero_media($slide_post_id, $slide_index === 0, $hero_logo_url); ?>
                            </div>
                            <div class="hero-overlay hero-overlay--shade" aria-hidden="true"></div>
                            <div class="hero-overlay hero-overlay--glow" aria-hidden="true"></div>
                            <div class="hero-overlay hero-overlay--mesh" aria-hidden="true"></div>
                            <div class="hero-overlay hero-overlay--vignette" aria-hidden="true"></div>
                            <?php $render_hero_content($slide_post_id, $title_tag); ?>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($has_multiple_slides) : ?>
            <div class="hero-carousel__controls hidden md:flex" aria-label="<?php esc_attr_e('Hero controls', 'mazaq'); ?>">
                <button type="button" class="hero-carousel__button" data-hero-prev aria-label="<?php esc_attr_e('Previous featured article', 'mazaq'); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M15 5l-7 7 7 7"></path>
                    </svg>
                </button>
                <button type="button" class="hero-carousel__button" data-hero-next aria-label="<?php esc_attr_e('Next featured article', 'mazaq'); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <div class="hero-carousel__rail hidden xl:flex" aria-label="<?php esc_attr_e('Featured article index', 'mazaq'); ?>">
                <?php foreach ($hero_post_ids as $slide_index => $slide_post_id) : ?>
                    <button type="button" class="hero-rail__item <?php echo $slide_index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $slide_index); ?>" aria-current="<?php echo $slide_index === 0 ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr(sprintf(__('Go to featured article %d', 'mazaq'), $slide_index + 1)); ?>">
                        <span class="hero-rail__number"><?php echo esc_html(sprintf('%02d', $slide_index + 1)); ?></span>
                        <span class="hero-rail__body">
                            <span class="hero-rail__title"><?php echo esc_html(wp_trim_words(get_the_title($slide_post_id), 7, '...')); ?></span>
                            <span class="hero-rail__progress" aria-hidden="true">
                                <span class="hero-rail__progress-bar"></span>
                            </span>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="hero-carousel__mobile md:hidden">
                <div class="hero-carousel__mobile-head">
                    <span class="hero-carousel__count" data-hero-current><?php echo esc_html(sprintf('%02d', 1)); ?></span>
                    <span class="hero-carousel__count-total">/ <?php echo esc_html(sprintf('%02d', $hero_total)); ?></span>
                </div>
                <div class="hero-carousel__dots">
                    <?php foreach ($hero_post_ids as $slide_index => $slide_post_id) : ?>
                        <button type="button" class="hero-carousel__dot <?php echo $slide_index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $slide_index); ?>" aria-current="<?php echo $slide_index === 0 ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr(sprintf(__('Go to featured article %d', 'mazaq'), $slide_index + 1)); ?>">
                            <span class="screen-reader-text"><?php echo esc_html(get_the_title($slide_post_id)); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <span class="hero-carousel__progress" aria-hidden="true">
                    <span class="hero-carousel__progress-bar"></span>
                </span>
            </div>
        <?php endif; ?>
    </div>
</section>
