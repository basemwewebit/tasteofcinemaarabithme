<?php get_header(); ?>

<main id="main-content" class="err-404">
    <div class="err-404__wrap">
        <p class="err-404__code num" aria-hidden="true">404</p>
        <h1 class="err-404__title"><?php esc_html_e('المخرج قرر استبعاد هذا المشهد من النسخة النهائية.', 'mazaq'); ?></h1>
        <p class="err-404__sub"><?php esc_html_e('الصفحة غير موجودة في الأرشيف. ابحث في الأرشيف أو عُد إلى العرض.', 'mazaq'); ?></p>

        <form role="search" method="get" class="err-404__search" action="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('البحث في الأرشيف', 'mazaq'); ?>">
            <label class="sr-only" for="err-404-search"><?php esc_attr_e('ابحث عن فيلم أو مقال', 'mazaq'); ?></label>
            <input type="search" id="err-404-search" name="s" class="err-404__input" placeholder="<?php esc_attr_e('ابحث عن فيلم أو مقال...', 'mazaq'); ?>" autocomplete="off" />
            <button type="submit" class="err-404__search-btn" aria-label="<?php esc_attr_e('بحث', 'mazaq'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>
        </form>

        <?php $popular = mazaq_get_most_read_posts(5); ?>
        <?php $has_popular = $popular instanceof WP_Query && $popular->have_posts(); ?>

        <div class="err-404__actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="err-404__primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                </svg>
                <span><?php esc_html_e('العودة للعرض', 'mazaq'); ?></span>
            </a>
            <?php if ($has_popular) : ?>
                <a href="#err-404-popular" class="err-404__ghost">
                    <span><?php esc_html_e('الأكثر قراءة', 'mazaq'); ?></span>
                </a>
            <?php endif; ?>
        </div>

        <?php $err_cats = get_categories(['orderby' => 'count', 'order' => 'DESC', 'number' => 3, 'hide_empty' => true]); ?>
        <?php if (!empty($err_cats) && !is_wp_error($err_cats)) : ?>
            <nav class="err-404__cats" aria-label="<?php esc_attr_e('تصفح حسب التصنيف', 'mazaq'); ?>">
                <?php foreach ($err_cats as $err_cat) : ?>
                    <?php $err_cat_link = get_category_link($err_cat->term_id); ?>
                    <?php if (is_wp_error($err_cat_link)) { continue; } ?>
                    <a class="err-404__chip" href="<?php echo esc_url($err_cat_link); ?>">
                        <span><?php echo esc_html($err_cat->name); ?></span>
                        <span class="num"><?php echo esc_html(number_format_i18n((int) $err_cat->count)); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($has_popular) : ?>
            <section class="err-404__popular" aria-labelledby="err-404-popular">
                <h2 id="err-404-popular" class="err-404__popular-title"><?php esc_html_e('تابع العرض: الأكثر قراءة هذا الأسبوع', 'mazaq'); ?></h2>
                <ol class="err-404__list">
                    <?php $rank = 1; ?>
                    <?php while ($popular->have_posts()) : $popular->the_post(); ?>
                        <li class="err-404__row">
                            <a href="<?php the_permalink(); ?>" class="err-404__link">
                                <span class="err-404__rank num" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $rank)); ?></span>
                                <span class="err-404__text">
                                    <span class="err-404__post-title"><?php the_title(); ?></span>
                                    <span class="err-404__meta num"><?php echo esc_html(number_format_i18n(mazaq_get_post_views(get_the_ID()))); ?> <?php esc_html_e('مشاهدة', 'mazaq'); ?></span>
                                </span>
                            </a>
                        </li>
                        <?php $rank++; ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ol>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
