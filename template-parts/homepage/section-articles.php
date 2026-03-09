<?php
if (get_theme_mod('toc_hp_articles_enabled', true)):
    $posts_count = get_theme_mod('toc_hp_articles_posts_count', 6);
    $section_title = get_theme_mod('toc_hp_articles_title', 'أحدث المقالات المضافة');
?>
    <div class="w-full">
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white flex items-center gap-3"><span class="w-2 h-8 bg-primary rounded"></span><?php echo esc_html($section_title); ?></h2>
        </div>

        <div id="infinite-scroll-container" class="grid grid-cols-1 md:grid-cols-2 gap-8" data-page="2">
            <?php
            $query = new WP_Query([
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $posts_count,
                'paged' => 1,
                'post__not_in' => function_exists('mazaq_get_hero_post_ids') ? mazaq_get_hero_post_ids() : [],
            ]);

            if ($query->have_posts()) :
                $index = 1;
                while ($query->have_posts()) :
                    $query->the_post();
                    
                    if ($index > 1 && ($index - 1) % 8 === 0) {
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
<?php endif; ?>
