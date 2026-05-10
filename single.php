<?php get_header(); ?>
<?php get_template_part('template-parts/common/reading-progress'); ?>

<main id="main-content" class="max-w-7xl mx-auto px-4 pt-12 pb-8 section-gap">
    <div class="flex flex-col md:flex-row gap-10 md:gap-12 lg:gap-16">
        <article class="w-full md:w-2/3 lg:w-2/3">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <?php toc_breadcrumbs(); ?>
                <div class="mb-10">
                    <?php $cats = get_the_category(); if (!empty($cats)) : ?>
                        <span class="inline-block bg-primary/15 text-[var(--color-gold-tint)] text-label px-4 py-1.5 rounded-full border border-primary/25 mb-5"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                    <h1 class="text-headline text-slate-900 dark:text-white mb-8 break-words"><?php the_title(); ?></h1>

                    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-slate-500 dark:text-slate-400 font-medium tracking-wide">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span><?php echo esc_html(get_the_date('j F Y')); ?></span>
                        </div>
                        <p class="text-slate-500"><?php esc_html_e('آخر تحديث:', 'mazaq'); ?> <?php echo esc_html(get_the_modified_date('j F Y')); ?></p>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span><?php echo esc_html(toc_estimated_reading_time()); ?></span>
                        </div>
                    </div>
                </div>

                <?php get_template_part('template-parts/common/font-controls'); ?>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="w-full aspect-[21/9] bg-slate-200 dark:bg-slate-800 rounded-3xl overflow-hidden mb-12 shadow-lg">
                        <?php the_post_thumbnail('hero-image', ['class' => 'w-full h-full object-cover', 'loading' => 'eager', 'fetchpriority' => 'high']); ?>
                        <?php $caption = wp_get_attachment_caption((int) get_post_thumbnail_id()); if ($caption) : ?>
                            <p class="text-center text-xs text-slate-500 py-2"><?php echo esc_html($caption); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="article-content text-slate-700 dark:text-slate-300 prose-lg">
                    <?php the_content(); ?>
                </div>

                <?php $tags = get_the_tags(); if (!empty($tags)) : ?>
                    <div class="mt-16 pt-10 border-t border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-3 mb-5">
                            <svg class="single-tags-heading-icon w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"></path></svg>
                            <span class="text-label text-slate-500 dark:text-slate-400">الوسوم</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 sm:gap-2">
                            <?php foreach ($tags as $tag) : ?>
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="group inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full text-xs sm:text-sm font-medium border border-slate-200 dark:border-slate-700 hover:bg-primary hover:text-white hover:border-primary dark:hover:border-primary transition-colors duration-200">
                                    <span class="single-tag-hash text-primary group-hover:text-white font-bold">#</span><?php echo esc_html($tag->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php get_template_part('template-parts/content/author-box'); ?>

                <div class="mt-16"><?php mazaq_render_ad('ad_slot_bottom_article', 'horizontal'); ?></div>

           

            <?php endwhile; endif; ?>
        </article>

        <?php get_sidebar('single'); ?>
    </div>
</main>

<?php get_footer(); ?>
