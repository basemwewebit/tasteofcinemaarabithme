<?php get_header(); ?>

<?php get_template_part('template-parts/content/hero'); ?>


<section class="hero-transition ">
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
        'order'   => 'DESC',
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
    <div class="w-full section-gap">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-12 gap-4 md:gap-5">
            <?php foreach ($categories as $index => $category) :
                $is_first = $index === 0;
                $is_second = $index === 1;
                $span_class = $is_first ? 'col-span-2 md:col-span-3 lg:col-span-6 lg:row-span-2' : ($is_second ? 'col-span-1 lg:col-span-3' : 'col-span-1 lg:col-span-3');
                $pad_class = $is_first ? 'p-8 md:p-10' : 'p-5 md:p-6';
                $title_size = $is_first ? 'text-2xl md:text-3xl' : 'text-lg md:text-xl';
            ?>
                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="group <?php echo esc_attr($span_class); ?> bg-slate-100 dark:bg-slate-800 rounded-2xl <?php echo esc_attr($pad_class); ?> text-right border border-slate-200 dark:border-slate-700 hover:border-primary/40 dark:hover:border-primary/40 transition-colors duration-300 flex flex-col justify-end relative overflow-hidden min-w-0">
                    <div class="absolute inset-0 bg-gradient-to-tl from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <h3 class="<?php echo esc_attr($title_size); ?> font-bold text-slate-900 dark:text-white mb-2 group-hover:text-primary transition-colors relative z-10 break-words"><?php echo esc_html($category->name); ?></h3>
                    <span class="text-sm text-slate-500 dark:text-slate-400 relative z-10"><?php echo sprintf(esc_html__('%d مقال', 'mazaq'), $category->count); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>



    <div class="flex flex-col md:flex-row lg:flex-row gap-10 md:gap-12 lg:gap-16">
        <div class="w-full md:w-2/3 lg:w-2/3">
            <div class="mb-12 lg:mb-14">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-headline text-slate-900 dark:text-white">أحدث المقالات المضافة</h2>
                        <p class="text-label text-slate-500 dark:text-slate-400 mt-3">اكتشف أحدث المحتوى السينمائي</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-full text-sm font-medium text-slate-600 dark:text-slate-400">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse" aria-hidden="true"></span>
                            <span id="post-count"><?php echo esc_html((string) wp_count_posts('post')->publish); ?></span> <?php esc_html_e('مقال', 'mazaq'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div id="infinite-scroll-container" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-10" data-page="2" aria-live="polite" aria-relevant="additions">
                <?php
                $query = new WP_Query([
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => 6,
                    'paged' => 1,
                    'post__not_in' => mazaq_get_hero_post_ids(),
                ]);

                if ($query->have_posts()) :
                    $index = 1;
                    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
                    while ($query->have_posts()) :
                        $query->the_post();

                        $global_index = (($paged - 1) * 6) + $index;

                        // Inject Ad when global index is a multiple of 8
                        if ($global_index > 1 && ($global_index - 1) % 8 === 0) {
                            get_template_part('template-parts/ads/ad-grid');
                        }

                        if ($index % 3 === 0) {
                            get_template_part('template-parts/content/card-wide');
                        } else {
                            get_template_part('template-parts/content/card');
                        }

                        $index++;
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <div class="col-span-full text-center py-16">
                        <p class="text-slate-500 dark:text-slate-400 text-lg mb-4">لا توجد مقالات حالياً.</p>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-primary font-medium hover:underline">
                            <span>العودة إلى الصفحة الرئيسية</span>
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
                <span class="text-sm text-slate-500 dark:text-slate-400 font-medium"><?php esc_html_e('جاري تحميل المزيد...', 'mazaq'); ?></span>
            </div>
        </div>

        <?php get_sidebar(); ?>
    </div>
</main>

<?php get_footer(); ?>
