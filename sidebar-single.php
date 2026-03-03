<aside class="w-full lg:w-1/3">
    <div class="sticky top-28 flex flex-col gap-10">
        <?php get_template_part('template-parts/widgets/sidebar-search'); ?>
        <?php get_template_part('template-parts/ads/ad-vertical'); ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6 border-b-2 border-primary pb-3 inline-block">مقالات ذات صلة</h3>
            <div class="flex flex-col gap-6">
                <?php
                $related = new WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => 4,
                    'post__not_in' => [get_the_ID()],
                    'category__in' => wp_get_post_categories(get_the_ID()),
                ]);
                if ($related->have_posts()) :
                    while ($related->have_posts()) : $related->the_post();
                        get_template_part('template-parts/content/card-related');
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </div>
</aside>
