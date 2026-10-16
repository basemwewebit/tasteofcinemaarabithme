<?php
/**
 * Archive feed: the listing under every archive page's header. A lead article
 * on page 1, then ruled index rows, with the In-feed placement counted across
 * pages. Renders the main query.
 *
 * Args:
 * - empty_message (string)   Message shown when no posts.
 * - empty_link    (bool)     Whether to show the "browse latest posts" link.
 * - groupings     (string[]) Grouping buttons, of 'month' and 'category'; empty renders no bar.
 * - lead_label    (string)   Accessible label of the page-1 lead.
 *
 * @package Mazaq
 */

$args = wp_parse_args($args ?? [], [
    'empty_message' => __('لا توجد مقالات.', 'mazaq'),
    'empty_link' => true,
    'groupings' => ['month', 'category'],
    'lead_label' => __('المقال المميز في هذا الأرشيف', 'mazaq'),
]);

$empty_message = (string) $args['empty_message'];
$empty_link = (bool) $args['empty_link'];
$groupings = (array) $args['groupings'];
$grouping_labels = [
    'month' => __('حسب الشهر', 'mazaq'),
    'category' => __('حسب التصنيف', 'mazaq'),
];
$is_first_page = ((int) (get_query_var('paged') ?: 1)) <= 1;
?>

<?php if (have_posts()) : ?>

    <?php if ($is_first_page) : ?>
        <section class="archive-lead" aria-label="<?php echo esc_attr((string) $args['lead_label']); ?>">
            <?php the_post(); ?>
            <?php get_template_part('template-parts/content/article-card', null, ['layout' => 'wide', 'class' => 'archive-lead__card']); ?>
            <?php rewind_posts(); ?>
        </section>
    <?php endif; ?>

    <?php
    // Position counts across pages; the page-1 lead is position 1.
    $position = (max(1, (int) get_query_var('paged')) - 1) * (int) get_query_var('posts_per_page');

    ob_start();
    while (have_posts()) : the_post();
        $position++;

        if ($is_first_page && $position === 1) {
            continue;
        }

        mazaq_render_in_feed_ad($position);
        get_template_part('template-parts/archive/archive-row');
    endwhile;
    $rows = (string) ob_get_clean();
    ?>

    <?php if ($rows !== '') : ?>
        <?php if ($groupings) : ?>
            <div class="archive-groupbar" role="group" aria-label="<?php esc_attr_e('تجميع الفهرس', 'mazaq'); ?>">
                <?php foreach ($groupings as $i => $grouping) : ?>
                    <button type="button" class="archive-groupbar__btn<?php echo $i === 0 ? ' is-active' : ''; ?>" data-group="<?php echo esc_attr($grouping); ?>" aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>"><?php echo esc_html($grouping_labels[$grouping]); ?></button>
                <?php endforeach; ?>
                <button type="button" class="archive-groupbar__btn" data-group="all" aria-pressed="false"><?php esc_html_e('الكل', 'mazaq'); ?></button>
            </div>
        <?php endif; ?>

        <div class="archive-index" data-archive-index="true"><?php echo $rows; // Rendered by escaping template parts above. ?></div>

        <p class="screen-reader-text" data-archive-announcer aria-live="polite"></p>
    <?php endif; ?>

<?php else : ?>
    <div class="archive-empty">
        <p class="archive-empty__text"><?php echo esc_html($empty_message); ?></p>
        <?php if ($empty_link) : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="archive-empty__link"><?php esc_html_e('تصفح أحدث المقالات', 'mazaq'); ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php get_template_part('template-parts/navigation/pagination'); ?>
