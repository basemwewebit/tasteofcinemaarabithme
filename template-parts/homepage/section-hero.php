<?php
if (get_theme_mod('toc_hp_hero_enabled', true)):
    get_template_part('template-parts/content/hero');
    ?>
    <div class="max-w-7xl mx-auto px-4 mb-4">
        <?php get_template_part('template-parts/ads/ad-responsive'); ?>
    </div>
<?php endif; ?>
