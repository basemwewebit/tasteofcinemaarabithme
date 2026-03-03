<?php
if (is_home() || is_front_page()) {
    get_template_part('front-page');
    return;
}

get_header();
?>
<main class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php if (have_posts()) : 
            $post_index = 0;
            $ad_enabled = get_option('toc_ad_injection_enabled', false);
            $ad_interval = (int) get_option('toc_ad_injection_interval', 6);
            
            while (have_posts()) : the_post(); 
                $post_index++;
                get_template_part('template-parts/content/card'); 
                
                // T006: Inject Ad Slot
                if ($ad_enabled && $ad_interval > 0 && $post_index % $ad_interval === 0) {
                    get_template_part('template-parts/ad-slot', null, ['context' => 'index']);
                }
            endwhile; 
        endif; ?>
    </div>
</main>
<?php get_footer(); ?>
