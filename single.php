<?php get_header(); ?>
<?php get_template_part('template-parts/common/reading-progress'); ?>

<main id="main-content" class="max-w-5xl mx-auto px-4 pt-12 pb-8 section-gap">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="single-article">
            <?php toc_breadcrumbs(); ?>

            <header class="single-article__header">
                <?php $cats = get_the_category(); if (!empty($cats)) : ?>
                    <a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>" class="single-article__category"><?php echo esc_html($cats[0]->name); ?></a>
                <?php endif; ?>

                <h1 class="single-article__title"><?php the_title(); ?></h1>

                <div class="single-article__meta" aria-label="<?php esc_attr_e('معلومات المقال', 'mazaq'); ?>">
                    <span><?php echo esc_html(get_the_author()); ?></span>
                    <span aria-hidden="true">•</span>
                    <time class="num" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date('j F Y')); ?></time>
                    <span aria-hidden="true">•</span>
                    <span class="num"><?php echo esc_html(toc_estimated_reading_time()); ?></span>
                    <span aria-hidden="true">•</span>
                    <span><?php esc_html_e('آخر تحديث:', 'mazaq'); ?> <time class="num" datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_modified_date('j F Y')); ?></time></span>
                </div>
            </header>

            <?php get_template_part('template-parts/common/font-controls'); ?>

            <?php if (has_post_thumbnail()) : ?>
                <figure class="single-article__figure">
                    <?php the_post_thumbnail('hero-image', ['class' => 'single-article__image', 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => mazaq_get_post_thumbnail_alt(get_the_ID(), get_the_title())]); ?>
                    <?php $caption = wp_get_attachment_caption((int) get_post_thumbnail_id()); if ($caption) : ?>
                        <figcaption><?php echo esc_html($caption); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>

            <?php get_template_part('template-parts/content/film-infobox'); ?>
            <?php get_template_part('template-parts/common/listicle-toc'); ?>

            <div class="article-content text-slate-700 dark:text-slate-300 prose-lg">
                <?php the_content(); ?>
            </div>

            <?php get_template_part('template-parts/content/series-nav'); ?>

            <?php
            $related_posts = toc_get_related_posts(get_the_ID(), 3);
            if (!empty($related_posts)) :
                ?>
                <aside class="inline-related" aria-labelledby="inline-related-title">
                    <div class="inline-related__head">
                        <p class="inline-related__kicker"><?php esc_html_e('اقرأ أيضًا', 'mazaq'); ?></p>
                        <h2 id="inline-related-title" class="inline-related__title"><?php esc_html_e('من نفس العالم السينمائي', 'mazaq'); ?></h2>
                    </div>
                    <div class="inline-related__grid">
                        <?php foreach ($related_posts as $related_post) : ?>
                            <?php
                            $GLOBALS['post'] = $related_post;
                            setup_postdata($related_post);
                            get_template_part('template-parts/content/card');
                            ?>
                        <?php endforeach; wp_reset_postdata(); ?>
                    </div>
                </aside>
            <?php endif; ?>

            <?php $tags = get_the_tags(); if (!empty($tags)) : ?>
                <section class="single-article__tags" aria-labelledby="single-tags-title">
                    <div class="single-article__tags-head">
                        <svg class="single-tags-heading-icon w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"></path></svg>
                        <h2 id="single-tags-title"><?php esc_html_e('الوسوم', 'mazaq'); ?></h2>
                    </div>
                    <div class="single-article__tag-list">
                        <?php foreach ($tags as $tag) : ?>
                            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="single-article__tag">
                                <span class="single-tag-hash" aria-hidden="true">#</span><?php echo esc_html($tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php get_template_part('template-parts/content/author-box'); ?>

            <?php get_template_part('template-parts/common/newsletter', null, ['context' => 'single']); ?>

            <div class="mt-16"><?php mazaq_render_ad('ad_slot_bottom_article', 'horizontal'); ?></div>
        </article>
    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
