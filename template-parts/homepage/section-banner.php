<?php
if (get_theme_mod('toc_hp_banner_enabled', false)):
    $banner_image = get_theme_mod('toc_hp_banner_banner_image', '');
    $banner_url = get_theme_mod('toc_hp_banner_banner_url', '');
    $banner_text = get_theme_mod('toc_hp_banner_banner_text', '');
    
    if ($banner_image):
?>
    <div class="w-full">
        <a href="<?php echo esc_url($banner_url); ?>" class="block relative rounded-2xl overflow-hidden group shadow-md hover:shadow-lg transition-all" <?php echo empty($banner_url) ? 'onclick="return false;" style="cursor:default;"' : ''; ?>>
            <div class="aspect-[21/9] md:aspect-[32/9] w-full">
                <img src="<?php echo esc_url($banner_image); ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            </div>
            <?php if ($banner_text): ?>
            <div class="absolute inset-0 bg-black/40 flex items-center justify-center p-6 text-center">
                <h3 class="text-white text-2xl md:text-4xl font-bold drop-shadow-lg"><?php echo esc_html($banner_text); ?></h3>
            </div>
            <?php endif; ?>
        </a>
    </div>
<?php 
    endif;
endif; 
?>
