<?php get_header(); ?>

<?php get_template_part('template-parts/content/hero'); ?>

<section class="hero-transition">
    <div class="hero-transition__band mx-auto max-w-7xl px-4">
        <div class="hero-transition__ad-shell">
            <?php get_template_part('template-parts/ads/ad-responsive'); ?>
        </div>
    </div>
</section>

<main class="max-w-7xl mx-auto px-4 pb-8 mb-16">

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
    <div class="w-full pb-16">
       
        
        <div class="grid grid-cols-2 lg:grid-cols-3  gap-6">
            <?php foreach ($categories as $category) : ?>
                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="group bg-slate-100 dark:bg-slate-800 rounded-xl p-6 text-center border border-slate-200 dark:border-slate-700 hover:border-primary dark:hover:border-primary transition-all hover:shadow-md">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2 group-hover:text-primary transition-colors"><?php echo esc_html($category->name); ?></h3>
                    <span class="text-sm text-slate-500 dark:text-slate-400 block"><?php echo sprintf(esc_html__('%d مقال', 'mazaq'), $category->count); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>



    <div class="flex flex-col lg:flex-row gap-12">
        <div class="w-full lg:w-2/3">
            <div class="relative mb-10 pb-6 border-b border-slate-200/50 dark:border-slate-800/50 overflow-hidden">
                <div class="flex items-center justify-between relative">
                    <div class="flex items-center gap-4">
                        <!-- Animated accent bar -->
                        <div class="relative">
                            <span class="w-1.5 h-12 bg-gradient-to-b from-primary via-amber-400 to-primary rounded-full shadow-[0_0_20px_rgba(212,175,55,0.4)]"></span>
                            <span class="absolute inset-0 w-1.5 h-12 bg-primary rounded-full blur-md animate-pulse"></span>
                        </div>
                        <div>
                            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">أحدث المقالات المضافة</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">اكتشف أحدث المحتوى السينمائي</p>
                        </div>
                    </div>

                    <!-- Post count badge -->
                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-full text-sm font-medium text-slate-600 dark:text-slate-400">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            <span id="post-count">24</span> مقال
                        </span>
                    </div>
                </div>
            </div>

            <div id="infinite-scroll-container" class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8" data-page="2">
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
                        // We check $global_index % 8 == 1 to show it AFTER the 8th item, or just use 0 depending on the logic.
                        // Actually if we want an ad every 8 posts, and index is 1-based, we can output the ad BEFORE the 9th post (i.e. global_index % 8 === 1 && global_index > 1) or AFTER the 8th post (output ad after the template part)
                        
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
                endif;
                ?>
            </div>

            <div id="loading-indicator" class="hidden mt-12 flex flex-col items-center justify-center py-8 gap-4">
                <div class="relative">
                    <div class="w-12 h-12 border-4 border-slate-200 dark:border-slate-700 rounded-full"></div>
                    <div class="absolute inset-0 w-12 h-12 border-4 border-primary rounded-full border-t-transparent animate-spin"></div>
                    <div class="absolute inset-2 w-8 h-8 bg-primary/20 rounded-full animate-ping"></div>
                </div>
                <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">جاري تحميل المزيد...</span>
            </div>
        </div>

        <?php get_sidebar(); ?>
    </div>
</main>

<?php get_footer(); ?>
