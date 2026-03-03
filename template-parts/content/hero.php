<?php
$hero_post_id = function_exists('get_field') ? (int) get_field('hero_featured_post', 'option') : 0;
if (!$hero_post_id) {
    $sticky = get_option('sticky_posts');
    $hero_post_id = !empty($sticky) ? (int) $sticky[0] : 0;
}
if (!$hero_post_id) {
    $latest = get_posts(['post_type' => 'post', 'posts_per_page' => 1, 'fields' => 'ids']);
    $hero_post_id = !empty($latest) ? (int) $latest[0] : 0;
}
if (!$hero_post_id) {
    return;
}
?>
<section class="max-w-7xl mx-auto px-4 py-8">
    <a href="<?php echo esc_url(get_permalink($hero_post_id)); ?>" class="relative block w-full h-[60vh] md:h-[70vh] rounded-3xl overflow-hidden group cursor-pointer shadow-2xl">
        <?php if (has_post_thumbnail($hero_post_id)) : ?>
            <?php echo get_the_post_thumbnail($hero_post_id, 'hero-image', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-700']); ?>
        <?php else : ?>
            <div class="w-full h-full bg-gradient-to-t from-slate-900 to-slate-700"></div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent z-10"></div>
        <div class="absolute bottom-0 left-0 w-full p-8 md:p-16 z-20">
            <?php $cats = get_the_category($hero_post_id); ?>
            <?php if (!empty($cats)) : ?>
                <span class="inline-block bg-primary text-slate-900 text-sm font-bold px-4 py-1.5 rounded-full mb-6"><?php echo esc_html($cats[0]->name); ?></span>
            <?php endif; ?>
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-[1.22] max-w-4xl group-hover:text-primary transition-colors"><?php echo esc_html(get_the_title($hero_post_id)); ?></h1>
            <p class="text-slate-300 text-lg md:text-xl max-w-2xl hidden md:block leading-relaxed mb-6"><?php echo esc_html(wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($hero_post_id)), 30)); ?></p>
            <div class="flex items-center gap-4 text-sm text-slate-400 font-mono">
                <span><?php echo esc_html(get_the_author_meta('display_name', (int) get_post_field('post_author', $hero_post_id))); ?></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                <span><?php echo esc_html(get_the_date('j F Y', $hero_post_id)); ?></span>
            </div>
        </div>
    </a>
</section>
