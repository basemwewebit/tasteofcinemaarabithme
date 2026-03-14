<?php get_header(); ?>

<?php get_template_part('template-parts/content/hero'); ?>

<div class="max-w-7xl mx-auto px-4">
    <?php get_template_part('template-parts/ads/ad-responsive'); ?>
</div>

<main class="max-w-7xl mx-auto px-4 py-8 mb-16">

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
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white flex items-center gap-3"><span class="w-2 h-8 bg-primary rounded"></span>أحدث المقالات المضافة</h2>
            </div>

            <div id="infinite-scroll-container" class="grid grid-cols-1 md:grid-cols-2 gap-8" data-page="2">
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

            <div id="loading-indicator" class="hidden mt-12 flex justify-center items-center py-4">
                <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
        </div>

        <?php get_sidebar(); ?>
    </div>
</main>

<?php get_footer(); ?>
