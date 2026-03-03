<?php get_header(); ?>
<?php
$term = get_queried_object();
$bg_id = function_exists('get_field') ? (int) get_field('category_bg_image', 'category_' . $term->term_id) : 0;
$bg_url = $bg_id ? wp_get_attachment_image_url($bg_id, 'full') : '';
?>

<div class="bg-slate-900 text-white py-16 md:py-24 mb-8 relative overflow-hidden flex justify-center items-center text-center">
    <div class="absolute inset-0 opacity-20">
        <?php if ($bg_url) : ?><img src="<?php echo esc_url($bg_url); ?>" class="w-full h-full object-cover grayscale" alt="<?php echo esc_attr(single_cat_title('', false)); ?>"><?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/60"></div>
    </div>
    <div class="max-w-4xl mx-auto px-4 relative z-10 w-full flex flex-col items-center">
        <div class="bg-primary text-slate-900 font-bold px-4 py-1.5 rounded-full text-sm mb-6">قسم التصنيفات</div>
        <h1 class="text-4xl md:text-6xl font-bold mb-6 text-white tracking-wide leading-[1.2]"><?php single_cat_title(); ?></h1>
        <p class="text-lg md:text-xl text-slate-300 font-medium max-w-2xl mx-auto leading-relaxed"><?php echo (category_description()); ?></p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 mb-8">
    <div class="flex items-center gap-4 text-slate-700 dark:text-slate-300 font-medium bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700">
        <span class="text-primary font-bold text-xl"><?php echo esc_html((string) $wp_query->found_posts); ?></span>
        <span>مقال في هذا التصنيف</span>
    </div>
</div>

<div class="container mx-auto px-4"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main class="max-w-7xl mx-auto px-4 py-4 mb-20 flex-1 w-full">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
            <p class="col-span-full text-center text-slate-500">لا توجد مقالات في هذا التصنيف</p>
        <?php endif; ?>
    </div>
    <?php get_template_part('template-parts/navigation/pagination'); ?>
</main>

<?php get_footer(); ?>
