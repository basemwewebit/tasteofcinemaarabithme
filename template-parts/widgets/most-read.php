<?php

declare(strict_types=1);

$widget_args = isset($args) && is_array($args) ? $args : [];
$variant = isset($widget_args['variant']) && $widget_args['variant'] === 'home' ? 'home' : 'sidebar';
$scope = isset($widget_args['scope']) && $widget_args['scope'] === 'all' ? 'all' : 'week';
$count = isset($widget_args['count']) ? (int) $widget_args['count'] : 3;
$count = min(10, max(1, $count));

$most_read = isset($widget_args['query']) && $widget_args['query'] instanceof WP_Query
    ? $widget_args['query']
    : mazaq_get_most_read_posts($count);

if (!$most_read instanceof WP_Query || !$most_read->have_posts()) {
    return;
}

$heading_id = wp_unique_id('most-read-title-');
$list_count = min(10, max(1, (int) $most_read->post_count));
$heading = isset($widget_args['title']) && is_string($widget_args['title']) && trim($widget_args['title']) !== ''
    ? $widget_args['title']
    : __('الأكثر قراءة', 'mazaq');
$scope_label = $scope === 'all' ? __('كل الأوقات', 'mazaq') : __('هذا الأسبوع', 'mazaq');

$root_classes = trim(sprintf(
    '%smost-read-widget most-read-widget--%s %s',
    $variant === 'home' ? 'home-section home-section--popular ' : '',
    $variant,
    isset($widget_args['class']) && is_string($widget_args['class']) ? $widget_args['class'] : ''
));

$initial = static function (string $text): string {
    $character = function_exists('mb_substr') ? mb_substr($text, 0, 1, 'UTF-8') : substr($text, 0, 1);
    if ($character === false || $character === '') {
        $character = function_exists('mb_substr') ? mb_substr(get_bloginfo('name'), 0, 1, 'UTF-8') : substr(get_bloginfo('name'), 0, 1);
    }

    return is_string($character) ? $character : '';
};
?>

<section class="<?php echo esc_attr($root_classes); ?>" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
    <header class="most-read-widget__head">
        <h2 id="<?php echo esc_attr($heading_id); ?>" class="most-read-widget__title"><?php echo esc_html($heading); ?></h2>
        <span class="most-read-widget__scope">
            <span class="most-read-widget__scope-dot" aria-hidden="true"></span>
            <?php echo esc_html($scope_label); ?>
        </span>
    </header>

    <ol class="most-read-widget__list most-read-widget__list--count-<?php echo esc_attr((string) $list_count); ?>" aria-label="<?php esc_attr_e('قائمة المقالات الأكثر قراءة', 'mazaq'); ?>">
        <?php $rank = 1; ?>
        <?php while ($most_read->have_posts()) : $most_read->the_post(); ?>
            <?php
            $post_id = get_the_ID();
            $permalink = $post_id ? get_permalink($post_id) : false;
            if (!$post_id || !is_string($permalink) || $permalink === '') {
                continue;
            }

            $title = get_the_title($post_id);
            $title = is_string($title) && $title !== '' ? $title : __('بدون عنوان', 'mazaq');
            $post_initial = $initial($title);
            $views = max(0, (int) mazaq_get_post_views($post_id));
            $date_w3c = get_the_date(DATE_W3C, $post_id);
            $date_display = get_the_date('j F Y', $post_id);
            $categories = get_the_category($post_id);
            $category = ($categories[0] ?? null) instanceof WP_Term ? $categories[0] : null;
            $category_name = $category instanceof WP_Term && is_string($category->name) && $category->name !== ''
                ? $category->name
                : __('مقال', 'mazaq');
            $tint = $category instanceof WP_Term && function_exists('mazaq_get_category_tint')
                ? mazaq_get_category_tint((int) $category->term_id)
                : '#C9A227';
            $tint = function_exists('sanitize_hex_color') ? sanitize_hex_color($tint) : $tint;
            $tint = is_string($tint) && $tint !== '' ? $tint : '#C9A227';
            $thumbnail = has_post_thumbnail($post_id)
                ? get_the_post_thumbnail($post_id, 'sidebar-thumbnail', [
                    'class' => 'most-read-widget__image',
                    'loading' => 'lazy',
                    'decoding' => 'async',
                    'sizes' => '(min-width: 1024px) 5rem, 4.5rem',
                    'alt' => '',
                ])
                : '';
            ?>
            <li class="most-read-widget__item" style="--most-read-tint: <?php echo esc_attr($tint); ?>;">
                <a href="<?php echo esc_url($permalink); ?>" class="most-read-widget__link">
                    <span class="most-read-widget__rank">
                        <span class="most-read-widget__rank-number num" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $rank)); ?></span>
                        <span class="sr-only"><?php echo esc_html(sprintf(__('الترتيب %d', 'mazaq'), $rank)); ?></span>
                    </span>

                    <span class="most-read-widget__media" aria-hidden="true">
                        <span class="most-read-widget__media-fallback"><?php echo esc_html($post_initial); ?></span>
                        <?php if ($thumbnail !== '') : ?>
                            <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                    </span>

                    <span class="most-read-widget__body">
                        <span class="most-read-widget__item-title"><?php echo esc_html($title); ?></span>
                        <span class="most-read-widget__meta">
                            <span class="most-read-widget__category">
                                <span class="most-read-widget__category-mark" aria-hidden="true"></span>
                                <span><?php echo esc_html($category_name); ?></span>
                            </span>
                            <?php if ($date_display !== '') : ?>
                                <span class="most-read-widget__meta-separator" aria-hidden="true">•</span>
                                <time class="most-read-widget__date num" datetime="<?php echo esc_attr((string) $date_w3c); ?>"><?php echo esc_html($date_display); ?></time>
                            <?php endif; ?>
                            <?php if ($views > 0) : ?>
                                <span class="most-read-widget__meta-separator" aria-hidden="true">•</span>
                                <span class="most-read-widget__views"><span class="num"><?php echo esc_html(number_format_i18n($views)); ?></span> <?php esc_html_e('قراءة', 'mazaq'); ?></span>
                            <?php endif; ?>
                        </span>
                    </span>

                    <svg class="most-read-widget__arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5"></path>
                        <path d="m11 6-6 6 6 6"></path>
                    </svg>
                </a>
            </li>
            <?php $rank++; ?>
        <?php endwhile; wp_reset_postdata(); ?>
    </ol>
</section>
