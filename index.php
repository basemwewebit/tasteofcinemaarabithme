<?php
if (is_home() || is_front_page()) {
    get_template_part('front-page');
    return;
}

get_header();
?>
<main class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php get_template_part('template-parts/content/card'); ?>
        <?php endwhile; endif; ?>
    </div>
</main>
<?php get_footer(); ?>
