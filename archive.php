<?php get_header(); ?>

<div class="bg-slate-900 text-white py-16 md:py-24 mb-8 relative overflow-hidden flex justify-center items-center text-center">
    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/60"></div>
    <div class="max-w-4xl mx-auto px-4 relative z-10 w-full flex flex-col items-center">
        <div class="bg-primary text-slate-900 font-bold px-4 py-1.5 rounded-full text-sm mb-6">الأرشيف</div>
        <h1 class="text-4xl md:text-6xl font-bold mb-6 text-white tracking-wide leading-[1.2]"><?php the_archive_title(); ?></h1>
        <p class="text-lg md:text-xl text-slate-300 font-medium max-w-2xl mx-auto leading-relaxed"><?php echo (get_the_archive_description()); ?></p>
    </div>
</div>
<div class="container mx-auto px-4 mt-8"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main class="max-w-7xl mx-auto px-4 py-4 mb-20">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (have_posts()) : 
            $post_index = 0;
            while (have_posts()) : the_post(); 
                $post_index++;
                get_template_part('template-parts/content/card-category'); 
                if ($post_index % 6 === 0) {
                    get_template_part('template-parts/ads/ad-grid', null, ['slot' => 'ad_slot_archive_banner']);
                }
            endwhile; 
        endif; ?>
    </div>
    <?php get_template_part('template-parts/navigation/pagination'); ?>
</main>

<?php get_footer(); ?>
