<?php
if (is_home() || is_front_page()) {
    get_template_part('front-page');
    return;
}

get_header();
?>
<main id="main-content" class="archive-main max-w-7xl mx-auto px-4 py-12">
    <?php get_template_part('template-parts/archive/archive-feed'); ?>
</main>
<?php get_footer(); ?>
