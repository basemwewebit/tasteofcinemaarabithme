<?php get_header(); ?>
<?php global $wp_query; ?>

<header class="archive-head">
    <div class="archive-head__inner max-w-7xl mx-auto px-4">
        <div class="archive-head__content">
            <span class="archive-head__pill">
                <span class="archive-head__tick" aria-hidden="true"></span>
                <?php esc_html_e('الأرشيف', 'mazaq'); ?>
            </span>
            <h1 class="archive-head__title"><?php the_archive_title(); ?></h1>
            <?php $archive_desc = get_the_archive_description(); if ($archive_desc) : ?>
                <p class="archive-head__desc"><?php echo wp_kses_post($archive_desc); ?></p>
            <?php endif; ?>
            <p class="archive-head__meta">
                <span class="archive-head__count num"><?php echo esc_html((string) $wp_query->found_posts); ?></span>
                <span><?php esc_html_e('مقال في هذا الأرشيف', 'mazaq'); ?></span>
            </p>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 mb-10"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20">
    <?php get_template_part('template-parts/archive/archive-feed', null, [
        'ad_context' => 'archive',
        'empty_message' => __('لا توجد مقالات في هذا الأرشيف حالياً.', 'mazaq'),
        'empty_link' => true,
    ]); ?>
</main>

<?php get_footer(); ?>
