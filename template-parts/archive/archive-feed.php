<?php
/**
 * Shared archive feed for the archive family: curated lead + filing-cabinet index.
 *
 * Args:
 * - empty_message    (string)      Message shown when no posts.
 * - empty_link       (bool)        Whether to show the "browse latest posts" link.
 * - group_by_category (bool)       Whether the index may be grouped by category.
 *
 * @package Mazaq
 */

$args = wp_parse_args($args ?? [], [
    'empty_message' => __('لا توجد مقالات.', 'mazaq'),
    'empty_link' => true,
    'group_by_category' => true,
]);

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
        // Position counts across pages; the page-1 lead is position 1.
        $position = (max(1, (int) get_query_var('paged')) - 1) * (int) get_query_var('posts_per_page');

        while (have_posts()) : the_post();
            $position++;

            if ($is_first_page && $position === 1) {
                continue;
            }

            mazaq_render_in_feed_ad($position);
            get_template_part('template-parts/archive/archive-row');
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
