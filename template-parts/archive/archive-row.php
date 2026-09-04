<?php
/**
 * Single ruled row in the filing-cabinet index.
 *
 * Carries data attributes used by assets/js/archive-index.js to regroup rows
 * by month or by category. Ads injected between rows stay anchored to the row
 * they followed.
 *
 * @package Mazaq
 */

$archive_post_id = get_the_ID();
$archive_permalink = get_permalink($archive_post_id);
$archive_categories = get_the_category($archive_post_id);
$archive_primary_category = $archive_categories[0] ?? null;
$archive_month_key = get_the_date('Y-m', $archive_post_id);
$archive_month_label = get_the_date('F Y', $archive_post_id);
$archive_category_slug = $archive_primary_category ? (string) $archive_primary_category->slug : '';
$archive_category_label = $archive_primary_category ? (string) $archive_primary_category->name : __('بدون تصنيف', 'mazaq');
?>

<article class="archive-row"
    data-month="<?php echo esc_attr($archive_month_key); ?>"
    data-month-label="<?php echo esc_attr($archive_month_label); ?>"
    data-category="<?php echo esc_attr($archive_category_slug); ?>"
    data-category-label="<?php echo esc_attr($archive_category_label); ?>">

    <time class="archive-row__date num" datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $archive_post_id)); ?>">
        <?php echo esc_html(get_the_date('j F Y', $archive_post_id)); ?>
    </time>

    <h3 class="archive-row__title">
        <a href="<?php echo esc_url($archive_permalink); ?>"><?php echo esc_html(get_the_title($archive_post_id)); ?></a>
    </h3>

    <span class="archive-row__meta">
        <?php if ($archive_primary_category && !is_category()) : ?>
            <a href="<?php echo esc_url(get_category_link($archive_primary_category->term_id)); ?>" class="archive-row__chip">
                <?php echo esc_html($archive_primary_category->name); ?>
            </a>
        <?php endif; ?>
        <span class="archive-row__time num"><?php echo esc_html(mazaq_reading_time($archive_post_id)); ?></span>
    </span>

</article>
