<?php get_header(); ?>
<?php get_template_part('template-parts/common/reading-progress'); ?>

<main class="max-w-7xl mx-auto px-4 py-12 mb-16">
    <div class="flex flex-col lg:flex-row gap-12">
        <article class="w-full lg:w-2/3">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <?php toc_breadcrumbs(); ?>
                <div class="mb-8">
                    <?php $cats = get_the_category(); if (!empty($cats)) : ?>
                        <span class="inline-block bg-primary text-slate-900 text-xs font-bold px-3 py-1 rounded-full mb-4"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                    <h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-6 leading-[1.25]"><?php the_title(); ?></h1>

                    <div class="flex flex-wrap items-center gap-6 text-sm text-slate-500 dark:text-slate-400 font-medium font-mono pb-6 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <?php echo get_avatar(get_the_author_meta('ID'), 40, '', '', ['class' => 'w-10 h-10 rounded-full']); ?>
                            <div>
                                <span class="block text-slate-900 dark:text-white font-bold font-sans text-base hover:text-primary transition-colors">بواسطة: <?php the_author_posts_link(); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span><?php echo esc_html(get_the_date('j F Y')); ?></span>
                        </div>
                        <p class="text-slate-500">آخر تحديث: <?php echo esc_html(get_the_modified_date('j F Y')); ?></p>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span><?php echo esc_html(toc_estimated_reading_time()); ?></span>
                        </div>

                    </div>
                </div>

                <?php get_template_part('template-parts/common/font-controls'); ?>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="w-full aspect-[21/9] bg-slate-200 dark:bg-slate-800 rounded-3xl overflow-hidden mb-12 shadow-lg">
                        <?php the_post_thumbnail('hero-image', ['class' => 'w-full h-full object-cover']); ?>
                        <?php $caption = wp_get_attachment_caption((int) get_post_thumbnail_id()); if ($caption) : ?>
                            <p class="text-center text-xs text-slate-500 py-2"><?php echo esc_html($caption); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="article-content text-slate-700 dark:text-slate-300 leading-loose prose-lg max-w-none">
                    <?php the_content(); ?>
                </div>

                <?php $tags = get_the_tags(); if (!empty($tags)) : ?>
                    <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800 flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag) : ?>
                            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:bg-primary hover:text-white transition-colors">#<?php echo esc_html($tag->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="my-12"><?php mazaq_render_ad('ad_slot_bottom_article', 'horizontal'); ?></div>

                <?php 
                $related_posts = toc_get_related_posts(get_the_ID(), 4);
                if (!empty($related_posts)) : 
                ?>
                <div class="mt-12 bg-slate-50 dark:bg-slate-800/50 rounded-3xl p-8 border border-slate-200 dark:border-slate-700 relative mb-12">
                    <div class="border-b-[3px] border-primary w-max pb-2 mb-8">
                        <h3 class="text-slate-900 dark:text-white text-xl md:text-2xl font-bold">مقالات ذات صلة</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($related_posts as $related) : ?>
                            <a href="<?php echo esc_url(get_permalink($related->ID)); ?>" class="group flex gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 shadow-sm transition-all duration-300 items-center">
                                <div class="w-24 h-24 shrink-0 rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-700">
                                    <?php echo get_the_post_thumbnail($related->ID, 'thumbnail', ['class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500']); ?>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-slate-900 dark:text-white font-bold text-sm leading-relaxed mb-2 group-hover:text-primary transition-colors line-clamp-2">
                                        <?php echo esc_html($related->post_title); ?>
                                    </h4>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span><?php echo esc_html(get_the_date('j F Y', $related->ID)); ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            <?php endwhile; endif; ?>
        </article>

        <?php get_sidebar('single'); ?>
    </div>
</main>

<?php get_footer(); ?>
