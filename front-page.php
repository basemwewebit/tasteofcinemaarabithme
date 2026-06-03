<?php get_header(); ?>

<?php get_template_part('template-parts/content/hero'); ?>

<?php
$hero_ids = mazaq_get_hero_post_ids();

$categories = get_categories([
    'orderby' => 'count',
    'order' => 'DESC',
    'hide_empty' => true,
]);

$categories = array_values(array_filter(
    $categories,
    static function ($category): bool {
        return isset($category->count) && (int) $category->count > 0;
    }
));

$published_count = (int) wp_count_posts('post')->publish;

// Editor's collection: spotlight one category and surface a curated set.
// Source order: ACF override (home_collection_category) -> top category by volume.
$collection_term = null;
if (function_exists('get_field')) {
    // ACF can return a term ID, a WP_Term, or an array depending on field type.
    $configured = get_field('home_collection_category', 'option');
    if (is_array($configured)) {
        $configured = reset($configured);
    }
    $configured_id = $configured instanceof WP_Term ? (int) $configured->term_id : (int) $configured;
    if ($configured_id > 0) {
        $maybe_term = get_term($configured_id, 'category');
        if ($maybe_term instanceof WP_Term) {
            $collection_term = $maybe_term;
        }
    }
}
if (!$collection_term instanceof WP_Term && !empty($categories)) {
    $collection_term = $categories[0];
}

$collection_query = null;
if ($collection_term instanceof WP_Term) {
    $collection_query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'cat' => (int) $collection_term->term_id,
        'posts_per_page' => 4,
        'post__not_in' => $hero_ids,
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ]);
}

// Most read this week. If the week has no view data, fall back to all-time
// most read (still real popularity) rather than recency, so the rail never
// just mirrors the "latest" feed below it. Tracks scope for honest labelling.
$popular = mazaq_get_most_read_posts(5);
$popular_scope_week = true;
if (!$popular->have_posts()) {
    wp_reset_postdata();
    $popular_scope_week = false;
    $popular = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 5,
        'post__not_in' => $hero_ids,
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'meta_key' => '_post_views_count',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_query' => [
            [
                'key' => '_post_views_count',
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ],
        ],
    ]);
}
?>

<div class="reel-scrubber" aria-hidden="true"><span class="reel-scrubber__gate"></span></div>

<main id="main-content" class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="sr-only"><?php bloginfo('name'); ?></h1>

    <?php if ($collection_query && $collection_query->post_count >= 3) : ?>
        <section class="home-section editor-collection" aria-labelledby="editor-collection-title">
            <div class="home-section__head editor-collection__head">
                <div>
                    <p class="home-section__kicker"><?php esc_html_e('ابدأ من هنا', 'mazaq'); ?></p>
                    <h2 id="editor-collection-title" class="home-section__title">
                        <?php echo esc_html(sprintf(__('مختارات %s', 'mazaq'), $collection_term->name)); ?>
                    </h2>
                    <p class="home-section__summary">
                        <?php echo esc_html(sprintf(__('مجموعة من أرشيفنا حول %s، اخترناها لتبدأ منها.', 'mazaq'), $collection_term->name)); ?>
                    </p>
                </div>
                <a class="editor-collection__all" href="<?php echo esc_url(get_category_link($collection_term->term_id)); ?>" aria-label="<?php echo esc_attr(sprintf(__('كل مقالات %s', 'mazaq'), $collection_term->name)); ?>">
                    <span><?php esc_html_e('كل المقالات', 'mazaq'); ?></span>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11 6-6 6 6 6"></path>
                    </svg>
                </a>
            </div>

            <div class="editor-collection__grid">
                <?php while ($collection_query->have_posts()) : $collection_query->the_post(); ?>
                    <?php get_template_part('template-parts/content/article-card', null, [
                        'layout' => 'poster',
                        'class' => 'card-enter',
                    ]); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($categories)) : ?>
        <section class="home-section" aria-labelledby="browse-categories-title">
            <div class="home-section__head">
                <div>
                    <h2 id="browse-categories-title" class="home-section__title"><?php esc_html_e('تصفّح حسب التصنيف', 'mazaq'); ?></h2>
                    <p class="home-section__summary"><?php esc_html_e('أبواب الموقع، مرتبة حسب أكثرها مقالات.', 'mazaq'); ?></p>
                </div>
            </div>
            <div class="category-row">
                <?php foreach (array_slice($categories, 0, 6) as $category) : ?>
                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="category-row__item">
                        <span class="category-row__name"><?php echo esc_html($category->name); ?></span>
                        <span class="category-row__count">
                            <span class="num"><?php echo esc_html(number_format_i18n((int) $category->count)); ?></span>
                            <?php echo esc_html(_n('مقال', 'مقالات', (int) $category->count, 'mazaq')); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/common/random-film-popup', null, [
        'categories' => $categories,
    ]); ?>

    <?php if ($popular->have_posts()) : ?>
        <section class="home-section home-section--popular" aria-labelledby="popular-posts-title">
            <div class="home-section__head">
                <div>
                    <h2 id="popular-posts-title" class="home-section__title">
                        <?php echo esc_html($popular_scope_week ? __('الأكثر قراءة هذا الأسبوع', 'mazaq') : __('الأكثر قراءة', 'mazaq')); ?>
                    </h2>
                </div>
            </div>

            <ol class="popular-rail" aria-label="<?php esc_attr_e('قائمة المقالات الأكثر قراءة', 'mazaq'); ?>">
                <?php $rank = 1; ?>
                <?php while ($popular->have_posts()) : $popular->the_post(); ?>
                    <?php
                    $popular_views = mazaq_get_post_views(get_the_ID());
                    // Thumbnail is decorative here: the title sits beside it as text,
                    // so an empty alt avoids a duplicate screen-reader announcement.
                    $popular_thumb = get_the_post_thumbnail(get_the_ID(), 'sidebar-thumbnail', [
                        'class' => 'popular-rail__image',
                        'loading' => 'lazy',
                        'decoding' => 'async',
                        'alt' => '',
                    ]);
                    ?>
                    <li class="popular-rail__item">
                        <a href="<?php the_permalink(); ?>" class="popular-rail__link">
                            <span class="popular-rail__rank num"><?php echo esc_html(sprintf('%02d', $rank)); ?></span>
                            <span class="popular-rail__media">
                                <?php if ($popular_thumb !== '') : ?>
                                    <?php echo $popular_thumb; ?>
                                <?php else : ?>
                                    <span class="popular-rail__image popular-rail__image--fallback" aria-hidden="true"><?php echo esc_html(function_exists('mb_substr') ? mb_substr(get_the_title(), 0, 1, 'UTF-8') : substr(get_the_title(), 0, 1)); ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="popular-rail__body">
                                <span class="popular-rail__title"><?php the_title(); ?></span>
                                <?php if ($popular_views > 0) : ?>
                                    <span class="popular-rail__meta">
                                        <span class="num"><?php echo esc_html(number_format_i18n($popular_views)); ?></span>
                                        <?php esc_html_e('قراءة', 'mazaq'); ?>
                                    </span>
                                <?php endif; ?>
                            </span>
                        </a>
                    </li>
                    <?php $rank++; ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </ol>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/common/newsletter', null, ['context' => 'home']); ?>

    <section class="home-section home-section--latest" aria-labelledby="latest-posts-title">
        <div class="home-section__head">
            <div>
                <h2 id="latest-posts-title" class="home-section__title"><?php esc_html_e('أحدث المقالات', 'mazaq'); ?></h2>
                <p class="home-section__summary"><?php esc_html_e('تابع آخر ما نُشر من مراجعات وقوائم وتحليلات.', 'mazaq'); ?></p>
            </div>

            <span class="home-section__count">
                <span id="post-count">
                    <span class="num"><?php echo esc_html(number_format_i18n($published_count)); ?></span>
                    <?php echo esc_html(_n('مقال منشور', 'مقالاً منشوراً', $published_count, 'mazaq')); ?>
                </span>
            </span>
        </div>

        <div id="infinite-scroll-container" class="latest-feed" data-page="2" aria-live="polite" aria-relevant="additions" aria-busy="false">
            <?php
            $query = new WP_Query([
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 6,
                'paged' => 1,
                'post__not_in' => $hero_ids,
                'no_found_rows' => true,
            ]);

            if ($query->have_posts()) :
                $post_index = 0;
                while ($query->have_posts()) :
                    $query->the_post();
                    get_template_part('template-parts/content/article-card', null, [
                        'layout' => $post_index === 0 ? 'wide' : 'standard',
                        'class' => $post_index === 0 ? 'latest-feed__lead' : '',
                    ]);
                    $post_index++;
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <div class="home-empty-state col-span-full">
                    <p class="home-empty-state__text"><?php esc_html_e('لا توجد مقالات منشورة بعد. تصفّح التصنيفات للعثور على ما يناسبك.', 'mazaq'); ?></p>
                    <div class="home-empty-state__actions">
                        <?php if (!empty($categories)) : ?>
                            <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>" class="home-empty-state__link">
                                <span><?php echo esc_html(sprintf(__('تصفّح %s', 'mazaq'), $categories[0]->name)); ?></span>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="home-empty-state__link">
                            <span><?php esc_html_e('العودة إلى الرئيسية', 'mazaq'); ?></span>
                        </a>
                    </div>
                </div>
                <?php
            endif;
            ?>
        </div>

        <div id="loading-indicator" class="infinite-scroll-status hidden" role="status" aria-live="polite">
            <div class="infinite-scroll-status__spinner" aria-hidden="true">
                <div class="infinite-scroll-status__track"></div>
                <div class="infinite-scroll-status__ring animate-spin"></div>
            </div>
            <span class="infinite-scroll-status__text"><?php esc_html_e('جاري تحميل المزيد', 'mazaq'); ?></span>
        </div>
        <div class="infinite-scroll-controls">
            <button id="load-more-posts" type="button" class="load-more-posts hidden"><?php esc_html_e('تحميل المزيد', 'mazaq'); ?></button>
        </div>
        <div id="infinite-scroll-sentinel" class="infinite-scroll-sentinel" aria-hidden="true"></div>
    </section>
</main>

<?php get_footer(); ?>
