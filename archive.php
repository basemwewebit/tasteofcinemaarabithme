<?php get_header(); ?>

<div class="relative bg-slate-900 text-white pt-20 pb-16 md:pt-28 md:pb-20 mb-12 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/60"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="max-w-3xl">
            <span class="inline-block bg-primary text-slate-900 font-bold px-4 py-1.5 rounded-full text-sm mb-6">الأرشيف</span>
            <h1 class="text-display mb-6 text-white break-words"><?php the_archive_title(); ?></h1>
            <?php $archive_desc = get_the_archive_description(); if ($archive_desc) : ?>
                <p class="text-lg md:text-xl text-slate-300 font-medium leading-relaxed"><?php echo esc_html($archive_desc); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 mb-10"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20">
    <?php if (have_posts()) : ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10">
        <?php
            $post_index = 0;
            $ad_enabled = get_option('toc_ad_injection_enabled', false);
            $ad_interval = (int) get_option('toc_ad_injection_interval', 6);

            while (have_posts()) : the_post();
                $post_index++;
                get_template_part('template-parts/content/card-category');

                // T006: Inject Ad Slot
                if ($ad_enabled && $ad_interval > 0 && $post_index % $ad_interval === 0) {
                    get_template_part('template-parts/ad-slot', null, ['context' => 'archive']);
                }

                if ($post_index % 6 === 0) {
                    get_template_part('template-parts/ads/ad-grid', null, ['slot' => 'ad_slot_archive_banner']);
                }
            endwhile;
        ?>
    </div>
    <?php else : ?>
    <div class="text-center py-20">
        <p class="text-slate-600 dark:text-slate-300 text-lg mb-4">لا توجد مقالات في هذا الأرشيف حالياً.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-primary font-medium hover:underline">
            <span>تصفح أحدث المقالات</span>
        </a>
    </div>
    <?php endif; ?>
    <?php get_template_part('template-parts/navigation/pagination'); ?>
</main>

<?php get_footer(); ?>
