<?php get_header(); ?>
<?php
$term = get_queried_object();
$bg_id = function_exists('get_field') ? (int) get_field('category_bg_image', 'category_' . $term->term_id) : 0;
$bg_url = $bg_id ? wp_get_attachment_image_url($bg_id, 'full') : '';
?>

<div class="relative bg-slate-900 text-white pt-20 pb-16 md:pt-28 md:pb-20 mb-12 overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <?php if ($bg_url) : ?><img src="<?php echo esc_url($bg_url); ?>" class="w-full h-full object-cover grayscale" alt="<?php echo esc_attr(single_cat_title('', false)); ?>" decoding="async"><?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/60"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="max-w-3xl">
            <span class="inline-block bg-primary text-slate-900 font-bold px-4 py-1.5 rounded-full text-sm mb-6">قسم التصنيفات</span>
            <h1 class="text-display mb-6 text-white break-words"><?php single_cat_title(); ?></h1>
            <?php $cat_desc = category_description(); if ($cat_desc) : ?>
                <p class="text-lg md:text-xl text-slate-300 font-medium leading-relaxed"><?php echo esc_html($cat_desc); ?></p>
            <?php endif; ?>
            <div class="mt-6 flex items-center gap-3 text-slate-400 text-sm">
                <span class="w-1.5 h-1.5 bg-primary rounded-full" aria-hidden="true"></span>
                <span><?php echo esc_html((string) $wp_query->found_posts); ?> مقال في هذا التصنيف</span>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 mb-10"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20 flex-1 w-full">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10">
        <?php if (have_posts()) : 
            $post_index = 0;
            $ad_enabled = get_option('toc_ad_injection_enabled', false);
            $ad_interval = (int) get_option('toc_ad_injection_interval', 6);
            
            while (have_posts()) : the_post(); 
                $post_index++;
                get_template_part('template-parts/content/card-category'); 
                
                // T006: Inject Ad Slot
                if ($ad_enabled && $ad_interval > 0 && $post_index % $ad_interval === 0) {
                    get_template_part('template-parts/ad-slot', null, ['context' => 'category']);
                }
                
                if ($post_index % 6 === 0) {
                    get_template_part('template-parts/ads/ad-grid', null, ['slot' => 'ad_slot_archive_banner']);
                }
            endwhile;
        else : ?>
            <div class="col-span-full text-center py-16">
                <p class="text-slate-500 dark:text-slate-400 text-lg mb-4">لا توجد مقالات في هذا التصنيف حالياً.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-primary font-medium hover:underline">
                    <span>تصفح أحدث المقالات</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php get_template_part('template-parts/navigation/pagination'); ?>
</main>

<?php get_footer(); ?>
