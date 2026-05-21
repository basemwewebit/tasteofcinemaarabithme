<?php get_header(); ?>

<main id="main-content" class="delight-404 min-h-[80vh] flex items-center justify-center relative overflow-hidden">
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 delight-404__vignette"></div>
    <div class="absolute inset-0 delight-404__grain" aria-hidden="true"></div>

    <div class="relative z-10 w-full px-6 max-w-5xl mx-auto">
        <div class="max-w-2xl mx-auto text-center">
            <!-- Film perforation top -->
            <div class="delight-404__sprocket" aria-hidden="true"></div>

            <div class="delight-404__card">
                <span class="delight-404__label">مشهد محذوف</span>
                <h1 class="delight-404__code">404</h1>
                <p class="delight-404__lead">المخرج قرر استبعاد هذا المشهد من النسخة النهائية.</p>
                <p class="delight-404__sub">الصفحة غير موجودة في الأرشيف.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="delight-404__cta">
                    <span>العودة للعرض</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
            </div>

            <!-- Film perforation bottom -->
            <div class="delight-404__sprocket delight-404__sprocket--bottom" aria-hidden="true"></div>

            <?php get_template_part('template-parts/ads/ad-404'); ?>
        </div>

        <?php $popular = mazaq_get_most_read_posts(5); ?>
        <?php if ($popular->have_posts()) : ?>
            <section class="delight-404__popular" aria-labelledby="popular-404-title">
                <h2 id="popular-404-title" class="sr-only">الأكثر قراءة هذا الأسبوع</h2>
                <div class="popular-strip" tabindex="0" aria-label="<?php esc_attr_e('مقالات رائجة يمكن الانتقال إليها', 'mazaq'); ?>">
                    <?php $rank = 1; ?>
                    <?php while ($popular->have_posts()) : $popular->the_post(); ?>
                        <article class="popular-strip__item">
                            <a href="<?php the_permalink(); ?>" class="popular-strip__link group">
                                <span class="popular-strip__rank num"><?php echo esc_html(sprintf('%02d', $rank)); ?></span>
                                <h3 class="popular-strip__title"><?php the_title(); ?></h3>
                                <span class="popular-strip__meta num"><?php echo esc_html(number_format_i18n(mazaq_get_post_views(get_the_ID()))); ?> مشاهدة</span>
                            </a>
                        </article>
                        <?php $rank++; ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
