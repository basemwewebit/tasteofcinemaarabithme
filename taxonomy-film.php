<?php get_header(); ?>
<?php
global $wp_query;
$term = get_queried_object();

// Cross-article aggregate, rendered through the same helper + partial as the infobox.
$agg    = ($term instanceof WP_Term && function_exists('mazaq_film_agg')) ? mazaq_film_agg($term->term_id) : null;
$stars  = ($agg !== null && function_exists('mazaq_film_rating_stars'))
    ? mazaq_film_rating_stars($agg['rating'] . '/10')
    : null;
?>

<div class="relative bg-slate-900 text-white pt-20 pb-16 md:pt-28 md:pb-20 mb-12 overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/60"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="max-w-3xl">
            <span class="inline-block bg-primary text-slate-900 font-bold px-4 py-1.5 rounded-full text-sm mb-6">فيلم</span>
            <h1 class="text-display mb-6 text-white break-words"><?php single_term_title(); ?></h1>
            <?php $film_desc = term_description(); if ($film_desc) : ?>
                <p class="text-lg md:text-xl text-slate-300 font-medium leading-relaxed"><?php echo wp_kses_post($film_desc); ?></p>
            <?php endif; ?>
            <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-slate-300 text-sm">
                <?php if ($stars !== null) : ?>
                    <?php get_template_part('template-parts/content/rating-stars', null, ['stars' => $stars]); ?>
                    <span class="w-1.5 h-1.5 bg-primary rounded-full" aria-hidden="true"></span>
                    <span><?php echo esc_html(sprintf(_n('عن %d تقييم', 'عن %d تقييمات', $agg['count'], 'mazaq'), $agg['count'])); ?></span>
                    <span class="w-1.5 h-1.5 bg-primary rounded-full" aria-hidden="true"></span>
                <?php endif; ?>
                <span><?php echo esc_html(sprintf(_n('%d مقال يذكر هذا الفيلم', '%d مقالات تذكر هذا الفيلم', (int) $wp_query->found_posts, 'mazaq'), (int) $wp_query->found_posts)); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 mb-10"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20 flex-1 w-full">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10">
        <?php if (have_posts()) :
            $post_index = 0;
            while (have_posts()) : the_post();
                $post_index++;
                get_template_part('template-parts/content/card-category');

                if ($post_index % 6 === 0) {
                    get_template_part('template-parts/ads/ad-grid', null, ['slot' => 'ad_slot_archive_banner']);
                }
            endwhile;
        else : ?>
            <div class="col-span-full text-center py-16">
                <p class="text-slate-600 dark:text-slate-300 text-lg mb-4">لا توجد مقالات تذكر هذا الفيلم حالياً.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-primary font-medium hover:underline">
                    <span>تصفح أحدث المقالات</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php get_template_part('template-parts/navigation/pagination'); ?>
</main>

<?php get_footer(); ?>
