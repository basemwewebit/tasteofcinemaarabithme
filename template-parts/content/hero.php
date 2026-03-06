<?php

$hero_post_ids = mazaq_get_hero_post_ids();

if (empty($hero_post_ids)) {
    return;
}

$render_hero_media = static function (int $post_id): void {
    if (has_post_thumbnail($post_id)) {
        echo get_the_post_thumbnail(
            $post_id,
            'hero-image',
            [
                'class' => 'hero-media__image',
            ]
        );

        return;
    }
    ?>
    <div class="hero-media hero-media--fallback" aria-hidden="true"></div>
    <?php
};

$render_hero_content = static function (int $post_id, string $title_tag = 'h2'): void {
    $categories = get_the_category($post_id);
    $author_id = (int) get_post_field('post_author', $post_id);
    $title = get_the_title($post_id);
    $excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($post_id)), 30);
    ?>
    <div class="hero-content-wrap">
        <div class="hero-content">
            <?php if (!empty($categories)) : ?>
                <span class="hero-badge"><?php echo esc_html($categories[0]->name); ?></span>
            <?php endif; ?>
            <<?php echo tag_escape($title_tag); ?> class="hero-title"><?php echo esc_html($title); ?></<?php echo tag_escape($title_tag); ?>>
            <?php if (!empty($excerpt)) : ?>
                <p class="hero-excerpt"><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>
            <div class="hero-meta" aria-label="<?php esc_attr_e('Post information', 'mazaq'); ?>">
                <span><?php echo esc_html(get_the_author_meta('display_name', $author_id)); ?></span>
                <span class="hero-meta__separator" aria-hidden="true"></span>
                <span><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></span>
            </div>
        </div>
    </div>
    <?php
};

if (count($hero_post_ids) === 1) :
    /* SINGLE POST PATH */
    $hero_post_id = $hero_post_ids[0];
    ?>
    <section class="hero-section" aria-label="<?php esc_attr_e('Featured article', 'mazaq'); ?>">
        <a href="<?php echo esc_url(get_permalink($hero_post_id)); ?>" class="hero-shell group">
            <div class="hero-media">
                <?php $render_hero_media($hero_post_id); ?>
            </div>
            <div class="hero-overlay" aria-hidden="true"></div>
            <div class="hero-overlay hero-overlay--accent" aria-hidden="true"></div>
            <?php $render_hero_content($hero_post_id, 'h1'); ?>
        </a>
    </section>
<?php else : ?>
    <section class="hero-section">
        <div class="hero-carousel relative" data-interval="6000" data-total="<?php echo esc_attr(count($hero_post_ids)); ?>">
            <div class="hero-carousel__track">
                <?php foreach ($hero_post_ids as $slide_index => $slide_post_id) : ?>
                    <div class="hero-carousel__slide <?php echo $slide_index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>" data-slide-index="<?php echo esc_attr($slide_index); ?>">
                        <a href="<?php echo esc_url(get_permalink($slide_post_id)); ?>" class="hero-shell group">
                            <div class="hero-media">
                                <?php $render_hero_media($slide_post_id); ?>
                            </div>
                            <div class="hero-overlay" aria-hidden="true"></div>
                            <div class="hero-overlay hero-overlay--accent" aria-hidden="true"></div>
                            <?php $render_hero_content($slide_post_id, 'h2'); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="hero-carousel__dots">
                <?php for ($i = 0; $i < count($hero_post_ids); $i++) : ?>
                    <button class="hero-carousel__dot <?php echo $i === 0 ? 'bg-white w-6 active' : ''; ?>" data-index="<?php echo esc_attr($i); ?>" aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'mazaq'), $i + 1)); ?>"></button>
                <?php endfor; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
