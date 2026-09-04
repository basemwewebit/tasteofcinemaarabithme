<?php get_header(); ?>
<?php global $wp_query; ?>

<header class="archive-head">
    <div class="archive-head__inner max-w-7xl mx-auto px-4">
        <div class="archive-head__content">
            <span class="archive-head__pill">
                <span class="archive-head__tick" aria-hidden="true"></span>
                <?php esc_html_e('وسم سينمائي', 'mazaq'); ?>
            </span>
            <h1 class="archive-head__title">#<?php single_tag_title(); ?></h1>
            <?php $tag_desc = tag_description(); if ($tag_desc) : ?>
                <p class="archive-head__desc"><?php echo (wp_strip_all_tags($tag_desc)); ?></p>
            <?php else : ?>
                <p class="archive-head__desc"><?php esc_html_e('مجموعة مقالات مرتبطة بنفس المزاج أو الفكرة السينمائية.', 'mazaq'); ?></p>
            <?php endif; ?>
            <p class="archive-head__meta">
                <span class="archive-head__count num"><?php echo esc_html((string) $wp_query->found_posts); ?></span>
                <span><?php esc_html_e('مقال ضمن هذا الوسم', 'mazaq'); ?></span>
            </p>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 mb-10"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20">
    <?php get_template_part('template-parts/archive/archive-feed', null, [
        'ad_context' => 'tag',
        'empty_message' => __('لا توجد مقالات ضمن هذا الوسم حالياً.', 'mazaq'),
        'empty_link' => false,
    ]); ?>
</main>

<?php get_footer(); ?>
