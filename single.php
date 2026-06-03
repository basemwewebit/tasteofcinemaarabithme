<?php get_header(); ?>
<?php get_template_part('template-parts/common/reading-progress'); ?>

<main id="main-content" class="single-main max-w-6xl mx-auto px-4 pt-12 pb-8 section-gap">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="single-article">
            <?php toc_breadcrumbs(); ?>

            <header class="single-article__header">
                <?php $cats = get_the_category(); ?>
                <?php if (!empty($cats)) : ?>
                    <div class="flex justify-start">
                        <a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>" class="single-article__category"><?php echo esc_html($cats[0]->name); ?></a>
                    </div>
                <?php endif; ?>

                <h1 class="single-article__title text-display font-display leading-display text-slate-900 dark:text-white mb-6"><?php the_title(); ?></h1>

                <?php $article_deck = has_excerpt() ? wp_trim_words(wp_strip_all_tags((string) get_the_excerpt()), 32, '...') : ''; ?>
                <?php if ($article_deck !== '') : ?>
                    <p class="single-article__deck text-lead text-slate-600 dark:text-slate-400 font-medium leading-relaxed max-w-2xl mt-4 mb-6"><?php echo esc_html($article_deck); ?></p>
                <?php endif; ?>

                <?php $show_modified_date = (int) get_the_modified_time('U') > ((int) get_the_time('U') + DAY_IN_SECONDS); ?>
                <ul class="single-article__meta" aria-label="<?php esc_attr_e('معلومات المقال', 'mazaq'); ?>">
                    <li><?php echo esc_html(get_the_author()); ?></li>
                    <li><time class="num" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date('j F Y')); ?></time></li>
                    <li class="num"><?php echo esc_html(toc_estimated_reading_time()); ?></li>
                    <?php if ($show_modified_date) : ?>
                        <li><?php esc_html_e('آخر تحديث:', 'mazaq'); ?> <time class="num" datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_modified_date('j F Y')); ?></time></li>
                    <?php endif; ?>
                </ul>
            </header>

            <?php get_template_part('template-parts/common/font-controls'); ?>

            <?php if (has_post_thumbnail()) : ?>
                <figure class="single-article__figure my-8 md:my-12">
                    <?php the_post_thumbnail('hero-image', ['class' => 'single-article__image w-full aspect-video object-cover rounded-lg shadow-xl', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(min-width: 1180px) 72rem, 100vw', 'alt' => mazaq_get_post_thumbnail_alt(get_the_ID(), get_the_title())]); ?>
                    <?php $caption = wp_get_attachment_caption((int) get_post_thumbnail_id()); if ($caption) : ?>
                        <figcaption class="text-center italic text-sm text-slate-500 dark:text-slate-400 mt-3"><?php echo wp_kses_post($caption); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>

            <?php get_template_part('template-parts/content/film-infobox'); ?>
            <?php get_template_part('template-parts/common/listicle-toc'); ?>

            <div class="article-content text-slate-700 dark:text-slate-300 prose-lg">
                <?php 
                $content = get_the_content();
                $content = apply_filters('the_content', $content);
                $content = str_replace(']]>', ']]&gt;', $content);
                
                // Fetch related posts (5 posts max to cover both inline and bottom slots)
                $related_posts = toc_get_related_posts(get_the_ID(), 5);
                
                $inline_related_posts = [];
                $bottom_related_posts = [];
                
                if (!empty($related_posts)) {
                    if (count($related_posts) >= 2) {
                        $inline_related_posts = array_slice($related_posts, 0, 2);
                        $bottom_related_posts = array_slice($related_posts, 2, 3);
                    } else {
                        $bottom_related_posts = $related_posts;
                    }
                }
                
                if (!empty($inline_related_posts)) {
                    // Render the inline related posts block
                    ob_start();
                    ?>
                    <aside class="inline-related my-12 py-8 border-y border-mist dark:border-border-subtle" aria-labelledby="inline-related-title">
                        <div class="inline-related__head mb-6">
                            <h3 id="inline-related-title" class="font-editorial text-title font-bold text-slate-900 dark:text-white"><?php esc_html_e('من نفس العالم السينمائي', 'mazaq'); ?></h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($inline_related_posts as $related_post) : ?>
                                <?php
                                $GLOBALS['post'] = $related_post;
                                setup_postdata($related_post);
                                get_template_part('template-parts/content/article-card', null, [
                                    'layout' => 'standard',
                                    'class' => 'h-full',
                                ]);
                                ?>
                            <?php endforeach; wp_reset_postdata(); ?>
                        </div>
                    </aside>
                    <?php
                    $inline_related_html = ob_get_clean();
                    
                    // Split content by paragraph tags to find the 60% mark
                    $paragraphs = explode('</p>', $content);
                    $para_count = count($paragraphs);
                    if ($para_count > 3) {
                        $insert_at = (int) floor($para_count * 0.6);
                        $insert_at = max(2, min($para_count - 2, $insert_at));
                        $paragraphs[$insert_at] .= $inline_related_html;
                        $content = implode('</p>', $paragraphs);
                    } else {
                        $content .= $inline_related_html;
                    }
                }
                
                echo $content;
                ?>
            </div>

            <?php get_template_part('template-parts/content/series-nav'); ?>

            <?php $tags = get_the_tags(); if (!empty($tags)) : ?>
                <section class="single-article__tags" aria-labelledby="single-tags-title">
                    <div class="single-article__tags-head">
                        <svg class="single-tags-heading-icon w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"></path></svg>
                        <h2 id="single-tags-title"><?php esc_html_e('الوسوم', 'mazaq'); ?></h2>
                    </div>
                    <div class="single-article__tag-list">
                        <?php foreach ($tags as $tag) : ?>
                            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="single-article__tag">
                                <span class="single-tag-hash text-slate-400" aria-hidden="true">#</span><?php echo esc_html($tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <div class="delight-finale my-16 text-center">
                <span class="delight-finale__line delight-finale__line--top"></span>
                <h3 class="delight-finale__title font-display text-2xl font-bold text-slate-800 dark:text-white"><?php esc_html_e('انتهى العرض', 'mazaq'); ?></h3>
                <p class="delight-finale__subtitle text-sm text-slate-500 dark:text-slate-400 mt-2"><?php esc_html_e('شكرًا لمتابعتك القراءة في مذاق سينما', 'mazaq'); ?></p>
                <span class="delight-finale__line delight-finale__line--bottom"></span>
            </div>

            <?php get_template_part('template-parts/content/author-box'); ?>

            <?php if (!empty($bottom_related_posts)) : ?>
                <aside class="more-from-category mt-16 pt-8 border-t border-mist dark:border-border-subtle" aria-labelledby="more-from-category-title">
                    <div class="more-from-category__head mb-8 flex justify-between items-end">
                        <div>
                            <h2 id="more-from-category-title" class="font-display text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e('اقرأ المزيد من هذا التصنيف', 'mazaq'); ?></h2>
                        </div>
                        <?php if (!empty($cats)) : ?>
                            <a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>" class="text-sm font-bold text-primary hover:text-primary-hover flex items-center gap-1">
                                <span><?php esc_html_e('عرض الكل', 'mazaq'); ?></span>
                                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($bottom_related_posts as $related_post) : ?>
                            <?php
                            $GLOBALS['post'] = $related_post;
                            setup_postdata($related_post);
                            get_template_part('template-parts/content/article-card', null, [
                                'layout' => 'standard',
                                'class' => 'h-full',
                            ]);
                            ?>
                        <?php endforeach; wp_reset_postdata(); ?>
                    </div>
                </aside>
            <?php endif; ?>

            <?php get_template_part('template-parts/common/newsletter', null, ['context' => 'single']); ?>

            <div class="mt-16"><?php mazaq_render_ad('ad_slot_bottom_article', 'horizontal', 'w-full min-h-[90px] md:min-h-[120px] rounded-xl'); ?></div>
        </article>
    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
