<?php get_header(); ?>

<?php get_template_part('template-parts/content/hero'); ?>

<section class="hero-transition">
    <div class="hero-transition__band mx-auto max-w-7xl px-4">
        <div class="hero-transition__ad-shell">
            <?php get_template_part('template-parts/ads/ad-responsive'); ?>
        </div>
    </div>
</section>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-8 section-gap">
    <h1 class="sr-only"><?php bloginfo('name'); ?></h1>

    <?php
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

    get_template_part('template-parts/common/random-film-popup', null, [
        'categories' => $categories,
    ]);
    ?>

    <section class="home-section" aria-labelledby="latest-posts-title">
        <div class="home-section__head">
            <div>
                <p class="home-section__kicker"><?php esc_html_e('الأحدث', 'mazaq'); ?></p>
                <h2 id="latest-posts-title" class="home-section__title"><?php esc_html_e('أحدث المقالات المضافة', 'mazaq'); ?></h2>
                <p class="home-section__summary"><?php esc_html_e('اكتشف أحدث المحتوى السينمائي من مراجعات وقوائم وتحليلات.', 'mazaq'); ?></p>
            </div>

            <span class="home-section__count">
                <span class="home-section__count-dot" aria-hidden="true"></span>
                <span class="num" id="post-count"><?php echo esc_html((string) wp_count_posts('post')->publish); ?></span>
                <?php esc_html_e('مقال', 'mazaq'); ?>
            </span>
        </div>

        <div id="infinite-scroll-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10" data-page="2" aria-live="polite" aria-relevant="additions">
            <?php
            $query = new WP_Query([
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 6,
                'paged' => 1,
                'post__not_in' => mazaq_get_hero_post_ids(),
            ]);

            if ($query->have_posts()) :
                while ($query->have_posts()) :
                    $query->the_post();
                    get_template_part('template-parts/content/card');
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <div class="col-span-full text-center py-16">
                    <p class="text-slate-600 dark:text-slate-300 text-lg mb-4"><?php esc_html_e('لا توجد مقالات حالياً.', 'mazaq'); ?></p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-primary font-medium hover:underline">
                        <span><?php esc_html_e('العودة إلى الصفحة الرئيسية', 'mazaq'); ?></span>
                    </a>
                </div>
                <?php
            endif;
            ?>
        </div>

        <div id="loading-indicator" class="hidden mt-12 flex flex-col items-center justify-center py-8 gap-4" role="status" aria-live="polite">
            <div class="relative" aria-hidden="true">
                <div class="w-12 h-12 border-4 border-slate-200 dark:border-slate-700 rounded-full"></div>
                <div class="absolute inset-0 w-12 h-12 border-4 border-primary rounded-full border-t-transparent animate-spin"></div>
            </div>
            <span class="text-sm text-slate-600 dark:text-slate-300 font-medium"><?php esc_html_e('جاري تحميل المزيد...', 'mazaq'); ?></span>
        </div>
    </section>

    <?php
    $popular = mazaq_get_most_read_posts(5);
    if (!$popular->have_posts()) {
        wp_reset_postdata();
        $popular = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            'post__not_in' => mazaq_get_hero_post_ids(),
            'ignore_sticky_posts' => true,
        ]);
    }
    ?>
    <?php if ($popular->have_posts()) : ?>
        <section class="home-section home-section--popular" aria-labelledby="popular-posts-title">
            <div class="home-section__head">
                <div>
                    <p class="home-section__kicker"><?php esc_html_e('رائج هذا الأسبوع', 'mazaq'); ?></p>
                    <h2 id="popular-posts-title" class="home-section__title"><?php esc_html_e('الأكثر قراءة', 'mazaq'); ?></h2>
                </div>
            </div>

            <div class="popular-strip" tabindex="0" aria-label="<?php esc_attr_e('قائمة المقالات الأكثر قراءة أفقياً', 'mazaq'); ?>">
                <?php $rank = 1; ?>
                <?php while ($popular->have_posts()) : $popular->the_post(); ?>
                    <article class="popular-strip__item">
                        <a href="<?php the_permalink(); ?>" class="popular-strip__link group">
                            <span class="popular-strip__rank num"><?php echo esc_html(sprintf('%02d', $rank)); ?></span>
                            <h3 class="popular-strip__title"><?php the_title(); ?></h3>
                            <span class="popular-strip__meta num"><?php echo esc_html(number_format_i18n(mazaq_get_post_views(get_the_ID()))); ?> <?php esc_html_e('مشاهدة', 'mazaq'); ?></span>
                        </a>
                    </article>
                    <?php $rank++; ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($categories)) : ?>
        <section class="home-section" aria-labelledby="browse-categories-title">
            <div class="home-section__head">
                <div>
                    <p class="home-section__kicker"><?php esc_html_e('تصفّح', 'mazaq'); ?></p>
                    <h2 id="browse-categories-title" class="home-section__title"><?php esc_html_e('حسب التصنيف', 'mazaq'); ?></h2>
                </div>
            </div>
            <div class="category-row">
                <?php foreach (array_slice($categories, 0, 6) as $category) : ?>
                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="category-row__item">
                        <span class="category-row__name"><?php echo esc_html($category->name); ?></span>
                        <span class="category-row__count num"><?php echo sprintf(esc_html__('%d مقال', 'mazaq'), (int) $category->count); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/common/newsletter', null, ['context' => 'home']); ?>
</main>

<?php get_footer(); ?>
