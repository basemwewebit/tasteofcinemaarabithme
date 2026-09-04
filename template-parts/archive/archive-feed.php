<?php
/**
 * Shared archive feed for the archive family: curated lead + filing-cabinet index.
 *
 * Args:
 * - ad_context       (string|null) Context passed to ad injections; null disables ads.
 * - empty_message    (string)      Message shown when no posts.
 * - empty_link       (bool)        Whether to show the "browse latest posts" link.
 * - group_by_category (bool)       Whether the index may be grouped by category.
 *
 * @package Mazaq
 */

$args = wp_parse_args($args ?? [], [
    'ad_context' => 'archive',
    'empty_message' => __('لا توجد مقالات.', 'mazaq'),
    'empty_link' => true,
    'group_by_category' => true,
]);

$ad_context = $args['ad_context'];
$empty_message = (string) $args['empty_message'];
$empty_link = (bool) $args['empty_link'];
$group_by_category = (bool) $args['group_by_category'];
$is_first_page = ((int) (get_query_var('paged') ?: 1)) <= 1;
?>

<?php if (have_posts()) : ?>

    <?php if ($is_first_page) : ?>
        <section class="archive-lead" aria-label="<?php esc_attr_e('المقال المميز في هذا الأرشيف', 'mazaq'); ?>">
            <?php the_post(); ?>
            <?php get_template_part('template-parts/content/article-card', null, ['layout' => 'wide', 'class' => 'archive-lead__card']); ?>
            <?php rewind_posts(); ?>
        </section>
    <?php endif; ?>

    <div class="archive-groupbar" role="group" aria-label="<?php esc_attr_e('تجميع الفهرس', 'mazaq'); ?>">
        <button type="button" class="archive-groupbar__btn is-active" data-group="month" aria-pressed="true"><?php esc_html_e('حسب الشهر', 'mazaq'); ?></button>
        <?php if ($group_by_category) : ?>
            <button type="button" class="archive-groupbar__btn" data-group="category" aria-pressed="false"><?php esc_html_e('حسب التصنيف', 'mazaq'); ?></button>
        <?php endif; ?>
        <button type="button" class="archive-groupbar__btn" data-group="all" aria-pressed="false"><?php esc_html_e('الكل', 'mazaq'); ?></button>
    </div>

    <div class="archive-index" data-archive-index="true">
        <?php
        $post_index = 0;
        $ad_enabled = get_option('toc_ad_injection_enabled', false);
        $ad_interval = (int) get_option('toc_ad_injection_interval', 6);

        while (have_posts()) : the_post();
            $post_index++;

            if ($is_first_page && $post_index === 1) {
                continue;
            }

            get_template_part('template-parts/archive/archive-row');

            if ($ad_context && $ad_enabled && $ad_interval > 0 && $post_index % $ad_interval === 0) {
                get_template_part('template-parts/ads/ad-grid', null, ['slot' => 'ad_slot_archive_banner']);
            }
        endwhile;
        ?>
    </div>

    <p class="screen-reader-text" data-archive-announcer aria-live="polite"></p>

<?php else : ?>
    <div class="archive-empty">
        <p class="archive-empty__text"><?php echo esc_html($empty_message); ?></p>
        <?php if ($empty_link) : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="archive-empty__link"><?php esc_html_e('تصفح أحدث المقالات', 'mazaq'); ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php get_template_part('template-parts/navigation/pagination'); ?>
