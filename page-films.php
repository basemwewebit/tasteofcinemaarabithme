<?php
/* Template Name: Films Index */
get_header();

$films_query = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 24,
    'paged' => max(1, (int) get_query_var('paged')),
    'ignore_sticky_posts' => true,
]);
?>

<main id="main-content" class="max-w-7xl mx-auto px-4 pt-16 pb-20">
    <header class="mb-12">
        <p class="home-section__kicker"><?php esc_html_e('فهرس الأفلام', 'mazaq'); ?></p>
        <h1 class="single-article__title"><?php esc_html_e('أفلام ومقالات للبدء منها', 'mazaq'); ?></h1>
        <p class="mt-5 max-w-3xl text-lg leading-9 text-slate-600 dark:text-slate-300"><?php esc_html_e('واجهة مخصصة لاستكشاف المقالات بصيغة ملصقات سينمائية إلى حين إضافة تصنيف أفلام مستقل.', 'mazaq'); ?></p>
    </header>

    <?php if ($films_query->have_posts()) : ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10">
            <?php while ($films_query->have_posts()) : $films_query->the_post(); ?>
                <?php get_template_part('template-parts/content/article-card', null, ['layout' => 'poster']); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php else : ?>
        <p class="text-center text-slate-600 dark:text-slate-300"><?php esc_html_e('لا توجد مقالات متاحة حالياً.', 'mazaq'); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
