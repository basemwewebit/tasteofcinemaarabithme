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

    $published_count = (int) wp_count_posts('post')->publish;

    get_template_part('template-parts/common/random-film-popup', null, [
        'categories' => $categories,
    ]);
    ?>

 

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
                    <?php $view_count = mazaq_get_post_views(get_the_ID()); ?>
                    <article class="popular-strip__item">
                        <a href="<?php the_permalink(); ?>" class="popular-strip__link group">
                            <span class="popular-strip__rank num"><?php echo esc_html(sprintf('%02d', $rank)); ?></span>
                            <h3 class="popular-strip__title"><?php the_title(); ?></h3>
                            
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
                        <span class="category-row__count">
                            <span class="num"><?php echo esc_html(number_format_i18n((int) $category->count)); ?></span>
                            <?php echo esc_html(_n('مقال', 'مقالات', (int) $category->count, 'mazaq')); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/common/newsletter', null, ['context' => 'home']); ?>


       <section class="home-section" aria-labelledby="latest-posts-title">
        <div class="home-section__head">
            <div>
                <p class="home-section__kicker"><?php esc_html_e('الأحدث', 'mazaq'); ?></p>
                <h2 id="latest-posts-title" class="home-section__title"><?php esc_html_e('أحدث المقالات المضافة', 'mazaq'); ?></h2>
                <p class="home-section__summary"><?php esc_html_e('اكتشف أحدث المحتوى السينمائي من مراجعات وقوائم وتحليلات.', 'mazaq'); ?></p>
            </div>

            <span class="home-section__count">
                <span class="home-section__count-dot" aria-hidden="true"></span>
                <span id="post-count">
                    <span class="num"><?php echo esc_html(number_format_i18n($published_count)); ?></span>
                    <?php echo esc_html(_n('مقال', 'مقالات', $published_count, 'mazaq')); ?>
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
                'post__not_in' => mazaq_get_hero_post_ids(),
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
                    <p class="home-empty-state__text"><?php esc_html_e('لا توجد مقالات حالياً.', 'mazaq'); ?></p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="home-empty-state__link">
                        <span><?php esc_html_e('العودة إلى الصفحة الرئيسية', 'mazaq'); ?></span>
                    </a>
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
