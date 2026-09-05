<?php

declare(strict_types=1);

$args = wp_parse_args(
    $args ?? [],
    [
        'layout' => 'standard',
        'class' => '',
        'show_category' => true,
        'fallback_plate' => '',
    ]
);

$layout = in_array($args['layout'], ['standard', 'wide', 'poster', 'compact', 'related'], true) ? $args['layout'] : 'standard';
$post_id = get_the_ID();
$permalink = $post_id ? get_permalink($post_id) : false;
if (!$post_id || !is_string($permalink) || $permalink === '') {
    return;
}
$title = get_the_title($post_id);
if (!is_string($title) || $title === '') {
    $title = __('بدون عنوان', 'mazaq');
}
$initial = static function (string $text): string {
    $char = function_exists('mb_substr') ? mb_substr($text, 0, 1, 'UTF-8') : substr($text, 0, 1);
    if ($char === false || $char === '') {
        $char = function_exists('mb_substr') ? mb_substr(get_bloginfo('name'), 0, 1, 'UTF-8') : substr(get_bloginfo('name'), 0, 1);
    }
    return is_string($char) ? $char : '';
};
$categories = get_the_category($post_id);
$primary_category = ($categories[0] ?? null) instanceof WP_Term ? $categories[0] : null;
$category_name = __('مقال', 'mazaq');
$category_slug = '';
$category_url = '';
$tint = '#C9A227';
if ($primary_category) {
    if (is_string($primary_category->name) && $primary_category->name !== '') {
        $category_name = $primary_category->name;
    }
    $category_slug = is_string($primary_category->slug) ? $primary_category->slug : '';
    $category_link = get_category_link($primary_category->term_id);
    if (is_string($category_link) && $category_link !== '') {
        $category_url = $category_link;
    }
    $category_tint = mazaq_get_category_tint((int) $primary_category->term_id);
    if (is_string($category_tint) && $category_tint !== '') {
        $tint = $category_tint;
    }
}
$image_size = 'card-thumbnail';
if ($layout === 'wide') {
    $image_size = 'card-wide-thumbnail';
} elseif ($layout === 'poster') {
    $image_size = 'search-poster';
} elseif (in_array($layout, ['compact', 'related'], true)) {
    $image_size = 'sidebar-thumbnail';
}
$excerpt_length = $layout === 'wide' ? 30 : 22;
$excerpt = mazaq_get_excerpt($excerpt_length);
$excerpt = is_string($excerpt) ? trim($excerpt) : '';
$reading_time = mazaq_reading_time($post_id);
$reading_time = is_string($reading_time) ? trim($reading_time) : '';
$date_w3c = get_the_date(DATE_W3C, $post_id);
$date_display = $date_w3c ? get_the_date('j F Y', $post_id) : '';
$fallback_plate = (string) $args['fallback_plate'];
$image_sizes = '(min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw';
if ($layout === 'wide') {
    $image_sizes = '(min-width: 1024px) 58vw, 100vw';
} elseif ($layout === 'poster') {
    $image_sizes = '(min-width: 1024px) 22vw, (min-width: 768px) 32vw, 70vw';
} elseif (in_array($layout, ['compact', 'related'], true)) {
    $image_sizes = '5rem';
}
$article_classes = trim(sprintf(
    'article-card article-card--%s %s %s',
    esc_attr($layout),
    in_array($layout, ['standard', 'wide'], true) ? 'card-enter archive-item' : '',
    (string) $args['class']
));

$render_media = static function (string $class_name = 'article-card__image') use ($post_id, $image_size, $image_sizes, $title, $initial, $fallback_plate, $layout): void {
    if (has_post_thumbnail($post_id)) {
        ?>
        <span class="article-card__media-fallback" aria-hidden="true"><?php echo esc_html($initial($title)); ?></span>
        <?php
        echo get_the_post_thumbnail($post_id, $image_size, [
            'class' => $class_name,
            /* Wide is the lead card: never delay it behind lazy loading. */
            'loading' => $layout === 'wide' ? 'eager' : 'lazy',
            'decoding' => 'async',
            'sizes' => $image_sizes,
            'alt' => mazaq_get_post_thumbnail_alt($post_id, $title),
        ]);
        return;
    }
    if ($fallback_plate !== '') {
        ?>
        <img src="<?php echo esc_url($fallback_plate); ?>" class="<?php echo esc_attr($class_name); ?>" alt="" loading="lazy" decoding="async">
        <?php
        return;
    }
    ?>
    <span class="<?php echo esc_attr($class_name . ' article-card__image--fallback'); ?>" aria-hidden="true">
        <span><?php echo esc_html($initial($title)); ?></span>
    </span>
    <?php
};

/* The drawer label: a gold marker and the category, leading every note. */
$show_category = (bool) $args['show_category'];
$render_kicker = static function (bool $linked) use ($category_url, $category_name, $show_category): void {
    if (!$show_category) {
        return;
    }
    ?>
    <span class="article-card__kicker">
        <?php if ($linked && $category_url !== '') : ?>
            <a href="<?php echo esc_url($category_url); ?>" class="article-card__category">
                <span class="article-card__marker" aria-hidden="true"></span>
                <span class="article-card__category-name"><?php echo esc_html($category_name); ?></span>
            </a>
        <?php else : ?>
            <span class="article-card__category">
                <span class="article-card__marker" aria-hidden="true"></span>
                <span class="article-card__category-name"><?php echo esc_html($category_name); ?></span>
            </span>
        <?php endif; ?>
    </span>
    <?php
};

/* The programme line: one bidi-isolated mono line, dots between entries. */
$render_meta = static function (bool $with_reading_time) use ($reading_time, $date_w3c, $date_display): void {
    if ($reading_time === '' && $date_display === '') {
        return;
    }
    ?>
    <span class="article-card__meta">
        <?php if ($with_reading_time && $reading_time !== '') : ?>
            <span class="article-card__meta-item num"><?php echo esc_html($reading_time); ?></span>
        <?php endif; ?>
        <?php if ($date_display !== '') : ?>
            <time class="article-card__meta-item num" datetime="<?php echo esc_attr((string) $date_w3c); ?>"><?php echo esc_html($date_display); ?></time>
        <?php endif; ?>
    </span>
    <?php
};
$card_attrs = sprintf(
    'style="--article-card-tint: %s;"%s',
    esc_attr($tint),
    $category_slug !== '' ? ' data-category="' . esc_attr($category_slug) . '"' : ''
);
?>

<?php if ($layout === 'compact' || $layout === 'related') : ?>
    <article class="<?php echo esc_attr($article_classes); ?>" <?php echo $card_attrs; ?>>
        <a href="<?php echo esc_url($permalink); ?>" class="article-card__compact-link">
            <span class="article-card__compact-media">
                <?php $render_media(); ?>
            </span>
            <span class="article-card__compact-body">
                <?php $render_kicker(false); ?>
                <span class="article-card__title"><?php echo esc_html($title); ?></span>
                <?php $render_meta(false); ?>
            </span>
        </a>
    </article>
<?php elseif ($layout === 'poster') : ?>
    <article class="<?php echo esc_attr($article_classes); ?>" <?php echo $card_attrs; ?>>
        <a href="<?php echo esc_url($permalink); ?>" class="article-card__poster-link">
            <span class="article-card__media article-card__media--poster">
                <?php $render_media(); ?>
                <span class="article-card__media-shade" aria-hidden="true"></span>
                <span class="article-card__body">
                    <?php $render_kicker(false); ?>
                    <span class="article-card__title"><?php echo esc_html($title); ?></span>
                    <?php $render_meta(false); ?>
                </span>
            </span>
        </a>
    </article>
<?php else : ?>
    <article class="<?php echo esc_attr($article_classes); ?>" <?php echo $card_attrs; ?>>
        <a href="<?php echo esc_url($permalink); ?>" class="article-card__media-link" aria-label="<?php echo esc_attr(sprintf(__('اقرأ: %s', 'mazaq'), $title)); ?>">
            <span class="article-card__media">
                <?php $render_media(); ?>
                <span class="article-card__media-shade" aria-hidden="true"></span>
            </span>
        </a>
        <div class="article-card__body">
            <?php $render_kicker(true); ?>
            <h3 class="article-card__title">
                <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
            </h3>
            <?php if ($excerpt !== '') : ?>
                <p class="article-card__excerpt"><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>
            <?php $render_meta(true); ?>
        </div>
    </article>
<?php endif; ?>
