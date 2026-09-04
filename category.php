<?php get_header(); ?>
<?php
global $wp_query;
$term = get_queried_object();
$term_id = $term instanceof WP_Term ? (int) $term->term_id : 0;
$bg_id = $term_id && function_exists('get_field') ? (int) get_field('category_bg_image', 'category_' . $term_id) : 0;
$bg_url = $bg_id ? wp_get_attachment_image_url($bg_id, 'full') : '';
?>

<header class="archive-head">
    <div class="archive-head__inner max-w-7xl mx-auto px-4">
        <div class="archive-head__body md:grid md:grid-cols-[minmax(0,1fr)_minmax(0,15rem)] md:items-start md:gap-10">
            <div class="archive-head__content">
                <span class="archive-head__pill">
                    <span class="archive-head__tick" aria-hidden="true"></span>
                    <?php esc_html_e('قسم التصنيفات', 'mazaq'); ?>
                </span>
                <h1 class="archive-head__title"><?php single_cat_title(); ?></h1>
                <?php $cat_desc = category_description(); if ($cat_desc) : ?>
                    <p class="archive-head__desc"><?php echo wp_kses_post($cat_desc); ?></p>
                <?php endif; ?>
                <p class="archive-head__meta">
                    <span class="archive-head__count num"><?php echo esc_html((string) $wp_query->found_posts); ?></span>
                    <span><?php esc_html_e('مقال في هذا التصنيف', 'mazaq'); ?></span>
                </p>
            </div>
            <?php if ($bg_url) : ?>
                <figure class="archive-head__still">
                    <img src="<?php echo esc_url($bg_url); ?>" alt="<?php echo esc_attr(single_cat_title('', false)); ?>" loading="lazy" decoding="async">
                </figure>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 mb-10"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20">
    <?php get_template_part('template-parts/archive/archive-feed', null, [
        'ad_context' => 'category',
        'empty_message' => __('لا توجد مقالات في هذا التصنيف حالياً.', 'mazaq'),
        'empty_link' => true,
        'group_by_category' => false,
    ]); ?>
</main>

<?php get_footer(); ?>
