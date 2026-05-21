<?php

declare(strict_types=1);

$args = wp_parse_args(
    $args ?? [],
    [
        'layout' => 'standard',
        'class' => '',
        'show_category' => true,
    ]
);

$layout = in_array($args['layout'], ['standard', 'wide', 'poster', 'compact', 'related'], true) ? $args['layout'] : 'standard';
$post_id = get_the_ID();
$permalink = get_permalink($post_id);
$title = get_the_title($post_id);
$categories = get_the_category($post_id);
$primary_category = $categories[0] ?? null;
$category_name = $primary_category ? $primary_category->name : __('مقال', 'mazaq');
$category_slug = $primary_category ? $primary_category->slug : '';
$category_url = $primary_category ? get_category_link($primary_category->term_id) : '';
$tint = $primary_category ? mazaq_get_category_tint((int) $primary_category->term_id) : '#C9A227';
$image_size = 'card-thumbnail';
if ($layout === 'wide') {
    $image_size = 'card-wide-thumbnail';
} elseif ($layout === 'poster') {
    $image_size = 'search-poster';
} elseif (in_array($layout, ['compact', 'related'], true)) {
    $image_size = 'sidebar-thumbnail';
}
$excerpt_length = $layout === 'wide' ? 30 : 22;
$article_classes = trim(sprintf(
    'article-card article-card--%s %s %s',
    esc_attr($layout),
    in_array($layout, ['standard', 'wide'], true) ? 'card-enter archive-item' : '',
    (string) $args['class']
));

$render_media = static function (string $class_name = 'article-card__image') use ($post_id, $image_size, $title): void {
    if (has_post_thumbnail($post_id)) {
        echo get_the_post_thumbnail($post_id, $image_size, [
            'class' => $class_name,
            'loading' => 'lazy',
            'decoding' => 'async',
            'alt' => mazaq_get_post_thumbnail_alt($post_id, $title),
        ]);
        return;
    }
    ?>
    <span class="<?php echo esc_attr($class_name . ' article-card__image--fallback'); ?>" aria-hidden="true">
        <span><?php echo esc_html(function_exists('mb_substr') ? mb_substr($title, 0, 1) : substr($title, 0, 1)); ?></span>
    </span>
    <?php
};
?>

<?php if ($layout === 'compact' || $layout === 'related') : ?>
    <article class="<?php echo esc_attr($article_classes); ?>" style="--article-card-tint: <?php echo esc_attr($tint); ?>;" <?php echo $category_slug ? 'data-category="' . esc_attr($category_slug) . '"' : ''; ?>>
        <a href="<?php echo esc_url($permalink); ?>" class="article-card__compact-link">
            <span class="article-card__compact-media">
                <?php $render_media(); ?>
            </span>
            <span class="article-card__compact-body">
                <span class="article-card__title"><?php echo esc_html($title); ?></span>
                <time class="article-card__date num" datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></time>
            </span>
        </a>
    </article>
<?php elseif ($layout === 'poster') : ?>
    <article class="<?php echo esc_attr($article_classes); ?>" style="--article-card-tint: <?php echo esc_attr($tint); ?>;" <?php echo $category_slug ? 'data-category="' . esc_attr($category_slug) . '"' : ''; ?>>
        <a href="<?php echo esc_url($permalink); ?>" class="article-card__poster-link">
            <span class="article-card__media article-card__media--poster">
                <?php $render_media(); ?>
                <span class="article-card__media-shade" aria-hidden="true"></span>
                <?php if ($args['show_category']) : ?>
                    <span class="article-card__category article-card__category--overlay"><?php echo esc_html($category_name); ?></span>
                <?php endif; ?>
            </span>
            <span class="article-card__body">
                <span class="article-card__title"><?php echo esc_html($title); ?></span>
                <time class="article-card__date num" datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></time>
            </span>
        </a>
    </article>
<?php else : ?>
    <article class="<?php echo esc_attr($article_classes); ?>" style="--article-card-tint: <?php echo esc_attr($tint); ?>;" <?php echo $category_slug ? 'data-category="' . esc_attr($category_slug) . '"' : ''; ?>>
        <a href="<?php echo esc_url($permalink); ?>" class="article-card__media-link" aria-label="<?php echo esc_attr(sprintf(__('اقرأ: %s', 'mazaq'), $title)); ?>">
            <span class="article-card__media">
                <?php $render_media(); ?>
                <span class="article-card__media-shade" aria-hidden="true"></span>
                <?php if ($args['show_category'] && $primary_category) : ?>
                    <span class="article-card__category article-card__category--overlay"><?php echo esc_html($category_name); ?></span>
                <?php endif; ?>
            </span>
        </a>
        <div class="article-card__body">
            <?php if ($layout === 'wide' && $primary_category) : ?>
                <a href="<?php echo esc_url($category_url); ?>" class="article-card__category article-card__category--inline"><?php echo esc_html($category_name); ?></a>
            <?php endif; ?>
            <h3 class="article-card__title">
                <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
            </h3>
            <p class="article-card__excerpt"><?php echo esc_html(mazaq_get_excerpt($excerpt_length)); ?></p>
            <div class="article-card__footer">
                <span class="num"><?php echo esc_html(mazaq_reading_time($post_id)); ?></span>
                <time class="num" datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></time>
            </div>
        </div>
    </article>
<?php endif; ?>
