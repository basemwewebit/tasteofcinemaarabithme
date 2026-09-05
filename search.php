<?php
/**
 * Search room: the archive's quiet operative sibling.
 *
 * Best match leads as a wide card; the rest file into ruled index rows;
 * emptiness recovers like the 404 with popular ledger and category chips.
 *
 * @package Mazaq
 */

get_header(); ?>
<?php global $wp_query; ?>
<?php
$search_query_text = (string) get_search_query();
$search_total = (int) $wp_query->found_posts;
$is_first_page = max(1, (int) get_query_var('paged')) <= 1;
?>

<header class="archive-head search-head">
    <div class="archive-head__inner max-w-7xl mx-auto px-4">
        <div class="archive-head__content">
            <span class="archive-head__pill">
                <span class="archive-head__tick" aria-hidden="true"></span>
                <?php esc_html_e('فهرس البحث', 'mazaq'); ?>
            </span>
            <?php if ($search_query_text !== '') : ?>
                <h1 class="archive-head__title">
                    <?php esc_html_e('نتائج البحث عن:', 'mazaq'); ?>
                    <bdi class="search-head__query">&ldquo;<?php echo esc_html($search_query_text); ?>&rdquo;</bdi>
                </h1>
            <?php else : ?>
                <h1 class="archive-head__title"><?php esc_html_e('البحث في الأرشيف', 'mazaq'); ?></h1>
            <?php endif; ?>
            <p class="archive-head__meta" role="status" aria-live="polite">
                <span class="archive-head__count num"><?php echo esc_html(number_format_i18n($search_total)); ?></span>
                <span><?php echo esc_html(_n('نتيجة', 'نتائج', $search_total, 'mazaq')); ?></span>
            </p>
            <div class="search-head__field"><?php get_search_form(); ?></div>
        </div>
    </div>
</header>

<main id="main-content" class="archive-main search-room max-w-7xl mx-auto px-4 pb-20">
    <?php if (have_posts()) : ?>

        <?php if ($is_first_page) : ?>
            <section class="archive-lead" aria-label="<?php esc_attr_e('أقرب نتيجة لبحثك', 'mazaq'); ?>">
                <?php the_post(); ?>
                <?php get_template_part('template-parts/content/article-card', null, ['layout' => 'wide', 'class' => 'archive-lead__card']); ?>
                <?php rewind_posts(); ?>
            </section>
        <?php endif; ?>

        <?php
        $search_page_count = isset($wp_query->posts) && is_array($wp_query->posts) ? count($wp_query->posts) : 0;
        $search_has_rows = $is_first_page ? $search_page_count > 1 : $search_page_count > 0;
        ?>
        <?php if ($search_has_rows) : ?>
        <div class="archive-index">
            <?php
            $search_index = 0;
            while (have_posts()) :
                the_post();
                $search_index++;
                if ($is_first_page && $search_index === 1) {
                    continue;
                }
                get_template_part('template-parts/archive/archive-row');
            endwhile;
            ?>
        </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/navigation/pagination'); ?>

    <?php else : ?>

        <div class="err-404__wrap search-empty">
            <h2 class="archive-head__title search-empty__title"><?php esc_html_e('لم يتم العثور على نتائج', 'mazaq'); ?></h2>
            <?php if ($search_query_text !== '') : ?>
                <p class="search-empty__sub">
                    <?php
                    echo esc_html(sprintf(
                        /* translators: %s: the search query, bidi-isolated for mixed-direction text. */
                        __('لا توجد نتائج عن "%s". جرّب كلمة أقصر أو تصفح التصنيفات أدناه.', 'mazaq'),
                        "\u{2066}" . $search_query_text . "\u{2069}"
                    ));
                    ?>
                </p>
            <?php else : ?>
                <p class="search-empty__sub"><?php esc_html_e('اكتب كلمة في حقل البحث أعلاه لبدء البحث في الأرشيف.', 'mazaq'); ?></p>
            <?php endif; ?>

            <?php $search_popular = mazaq_get_most_read_posts(5); ?>
            <?php $search_has_popular = $search_popular instanceof WP_Query && $search_popular->have_posts(); ?>

            <div class="err-404__actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="err-404__primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                    </svg>
                    <span><?php esc_html_e('العودة للعرض', 'mazaq'); ?></span>
                </a>
                <?php if ($search_has_popular) : ?>
                    <a href="#search-empty-popular" class="err-404__ghost">
                        <span><?php esc_html_e('الأكثر قراءة', 'mazaq'); ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <?php $search_cats = get_categories(['orderby' => 'count', 'order' => 'DESC', 'number' => 3, 'hide_empty' => true]); ?>
            <?php if (!empty($search_cats) && !is_wp_error($search_cats)) : ?>
                <nav class="err-404__cats" aria-label="<?php esc_attr_e('تصفح حسب التصنيف', 'mazaq'); ?>">
                    <?php foreach ($search_cats as $search_cat) : ?>
                        <?php $search_cat_link = get_category_link($search_cat->term_id); ?>
                        <?php if (is_wp_error($search_cat_link)) { continue; } ?>
                        <a class="err-404__chip" href="<?php echo esc_url($search_cat_link); ?>">
                            <span><?php echo esc_html($search_cat->name); ?></span>
                            <span class="num"><?php echo esc_html(number_format_i18n((int) $search_cat->count)); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <?php if ($search_has_popular) : ?>
                <section class="err-404__popular" aria-labelledby="search-empty-popular">
                    <h3 id="search-empty-popular" class="err-404__popular-title"><?php esc_html_e('تابع العرض: الأكثر قراءة هذا الأسبوع', 'mazaq'); ?></h3>
                    <ol class="err-404__list">
                        <?php $search_rank = 1; ?>
                        <?php while ($search_popular->have_posts()) : $search_popular->the_post(); ?>
                            <li class="err-404__row">
                                <a href="<?php the_permalink(); ?>" class="err-404__link">
                                    <span class="err-404__rank num" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $search_rank)); ?></span>
                                    <span class="err-404__text">
                                        <span class="err-404__post-title"><?php the_title(); ?></span>
                                        <span class="err-404__meta num"><?php echo esc_html(number_format_i18n(mazaq_get_post_views(get_the_ID()))); ?> <?php esc_html_e('مشاهدة', 'mazaq'); ?></span>
                                    </span>
                                </a>
                            </li>
                            <?php $search_rank++; ?>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ol>
                </section>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</main>

<?php get_footer(); ?>
