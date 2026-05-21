<?php get_header(); ?>

<?php
global $wp_query;
$tag = get_queried_object();
?>
<div class="relative overflow-hidden bg-slate-950 text-white pt-20 pb-16 md:pt-28 md:pb-20 mb-12">
    <div class="absolute inset-0 delight-404__grain" aria-hidden="true"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="max-w-3xl">
            <span class="inline-flex rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-sm font-bold text-primary"><?php esc_html_e('وسم سينمائي', 'mazaq'); ?></span>
            <h1 class="text-display mt-6 mb-6 text-white break-words">#<?php single_tag_title(); ?></h1>
            <?php $tag_desc = tag_description(); if ($tag_desc) : ?>
                <p class="text-lg md:text-xl text-slate-300 font-medium leading-relaxed"><?php echo esc_html(wp_strip_all_tags($tag_desc)); ?></p>
            <?php else : ?>
                <p class="text-lg md:text-xl text-slate-300 font-medium leading-relaxed"><?php esc_html_e('مجموعة مقالات مرتبطة بنفس المزاج أو الفكرة السينمائية.', 'mazaq'); ?></p>
            <?php endif; ?>
            <p class="mt-6 text-sm font-bold text-slate-300"><span class="num"><?php echo esc_html((string) $wp_query->found_posts); ?></span> <?php esc_html_e('مقال ضمن هذا الوسم', 'mazaq'); ?></p>
        </div>
    </div>
</div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20">
    <?php if (have_posts()) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/content/article-card', null, ['layout' => 'standard']); ?>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <div class="text-center py-20">
            <p class="text-slate-600 dark:text-slate-300 text-lg mb-4"><?php esc_html_e('لا توجد مقالات ضمن هذا الوسم حالياً.', 'mazaq'); ?></p>
        </div>
    <?php endif; ?>
    <?php get_template_part('template-parts/navigation/pagination'); ?>
</main>

<?php get_footer(); ?>
