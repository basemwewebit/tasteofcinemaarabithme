<?php get_header(); ?>
<?php get_template_part('template-parts/common/reading-progress'); ?>
<div class="reading-ambience" data-reading-ambience aria-hidden="true"></div>

<main id="main-content" class="single-main px-4 pt-10 pb-8 section-gap">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="single-article single-article--cinema">
            <?php mazaq_breadcrumbs(); ?>

            <div class="programme-grid">
                <header class="single-article__header" data-credits>
                    <?php $cats = get_the_category(); ?>
                    <?php if (!empty($cats)) : ?>
                        <a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>" class="single-article__category" data-credits-line><?php echo esc_html($cats[0]->name); ?></a>
                    <?php endif; ?>

                    <h1 class="single-article__title" data-credits-line><?php the_title(); ?></h1>

                    <?php $article_deck = has_excerpt() ? wp_trim_words(wp_strip_all_tags((string) get_the_excerpt()), 32, '…') : ''; ?>
                    <?php if ($article_deck !== '') : ?>
                        <p class="single-article__deck" data-credits-line><?php echo esc_html($article_deck); ?></p>
                    <?php endif; ?>

                    <?php $show_modified_date = (int) get_the_modified_time('U') > ((int) get_the_time('U') + DAY_IN_SECONDS); ?>
                    <ul class="single-article__meta" data-credits-line aria-label="<?php esc_attr_e('معلومات المقال', 'mazaq'); ?>">
                        <?php $author_name = get_the_author(); ?>
                        <?php if ($author_name !== '') : ?>
                            <li><?php echo esc_html($author_name); ?></li>
                        <?php endif; ?>
                        <li><time class="num" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date('j F Y')); ?></time></li>
                        <li><?php echo esc_html(mazaq_reading_time(get_the_ID())); ?></li>
                        <?php if ($show_modified_date) : ?>
                            <li><?php esc_html_e('آخر تحديث:', 'mazaq'); ?> <time class="num" datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_modified_date('j F Y')); ?></time></li>
                        <?php endif; ?>
                    </ul>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <figure class="single-article__figure single-article__figure--cinema">
                        <div class="single-article__frame" data-hero-frame>
                            <?php the_post_thumbnail('hero-image', ['class' => 'single-article__image w-full h-full object-cover', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(min-width: 1360px) 60rem, 100vw', 'alt' => mazaq_get_post_thumbnail_alt(get_the_ID(), get_the_title())]); ?>
                            <span class="single-article__shade" aria-hidden="true"></span>
                            <span class="single-article__sweep" aria-hidden="true"></span>
                            <span class="single-article__perf" aria-hidden="true"></span>
                        </div>
                        <?php $caption = wp_get_attachment_caption((int) get_post_thumbnail_id()); if ($caption) : ?>
                            <figcaption><?php echo wp_kses_post($caption); ?></figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>

                <aside class="programme" aria-label="<?php esc_attr_e('برنامج القراءة', 'mazaq'); ?>">
                    <?php get_template_part('template-parts/content/film-infobox'); ?>
                    <?php get_template_part('template-parts/common/reading-rail'); ?>
                </aside>

                <div class="programme-main">
                    <div class="article-content text-slate-700 dark:text-slate-300 prose-lg">
                        <?php
                        $content = get_the_content();
                        $content = apply_filters('the_content', $content);
                        $content = str_replace(']]>', ']]&gt;', $content);

                        $related = mazaq_related_articles(get_the_ID());
                        $inline_related_posts = $related['inline'];
                        $bottom_related_posts = $related['bottom'];

                        $inline_related_html = '';
                        if (!empty($inline_related_posts)) {
                            ob_start();
                            ?>
                            <aside class="inline-related" aria-labelledby="inline-related-title">
                                <div class="inline-related__head mb-6">
                                    <h3 id="inline-related-title" class="font-display text-xl font-bold text-slate-900 dark:text-white"><?php esc_html_e('من نفس العالم السينمائي', 'mazaq'); ?></h3>
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
                            $inline_related_html = (string) ob_get_clean();
                        }

                        echo mazaq_compose_article_body($content, $inline_related_html, static function (): string {
                            ob_start();
                            mazaq_render_ad('in_article');
                            return (string) ob_get_clean();
                        });
                        ?>
                    </div>

                    <?php if (str_contains($content, 'data-still-open')) : ?>
                        <dialog class="still-viewer" data-still-viewer aria-label="<?php esc_attr_e('الصورة كاملة', 'mazaq'); ?>">
                            <img class="still-viewer__img" alt="" data-still-viewer-img>
                            <p class="still-viewer__credit" data-still-viewer-credit></p>
                            <button type="button" class="still-viewer__close" data-still-viewer-close aria-label="<?php esc_attr_e('إغلاق', 'mazaq'); ?>">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                            </button>
                        </dialog>
                    <?php endif; ?>

                    <?php get_template_part('template-parts/content/series-nav'); ?>

                    <?php $tags = get_the_tags(); if (!empty($tags)) : ?>
                        <section class="single-article__tags" aria-labelledby="single-tags-title">
                            <h2 id="single-tags-title" class="single-article__tags-head"><?php esc_html_e('الوسوم', 'mazaq'); ?></h2>
                            <div class="single-article__tag-list">
                                <?php foreach ($tags as $tag) : ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="single-article__tag">
                                        <span class="single-tag-hash text-slate-400" aria-hidden="true">#</span><?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <div class="delight-finale">
                        <div class="delight-finale__stub">
                            <span class="delight-finale__notch delight-finale__notch--top" aria-hidden="true"></span>
                            <span class="delight-finale__notch delight-finale__notch--end" aria-hidden="true"></span>
                            <h3 class="delight-finale__title"><?php esc_html_e('انتهى العرض', 'mazaq'); ?></h3>
                            <p class="delight-finale__subtitle"><?php esc_html_e('شكرًا لمتابعتك القراءة في مذاق سينما', 'mazaq'); ?></p>
                        </div>
                    </div>

                    <?php get_template_part('template-parts/content/author-box'); ?>

                    <?php if (!empty($bottom_related_posts)) : ?>
                        <aside class="more-from-category" aria-labelledby="more-from-category-title">
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

                    <div class="single-article__ad"><?php mazaq_render_ad('bottom_article'); ?></div>
                </div>
            </div>
        </article>
    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
