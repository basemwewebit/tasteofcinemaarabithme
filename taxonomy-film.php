<?php get_header(); ?>
<?php
global $wp_query;
$term = get_queried_object();

// Cross-article aggregate, rendered through the same helper + partial as the infobox.
$agg    = $term instanceof WP_Term ? mazaq_film_agg($term->term_id) : null;
$stars  = $agg !== null ? mazaq_film_rating_stars(['ratingValue' => $agg['rating'], 'bestRating' => 10]) : null;
$film_article_count = (int) $wp_query->found_posts;
?>

<header class="archive-head">
    <div class="archive-head__inner max-w-7xl mx-auto px-4">
        <div class="archive-head__content">
            <span class="archive-head__pill">
                <span class="archive-head__tick" aria-hidden="true"></span>
                <?php esc_html_e('فيلم', 'mazaq'); ?>
            </span>
            <h1 class="archive-head__title"><?php single_term_title(); ?></h1>
            <?php $film_desc = term_description(); if ($film_desc) : ?>
                <div class="archive-head__desc"><?php echo wp_kses_post($film_desc); ?></div>
            <?php endif; ?>
            <p class="archive-head__meta">
                <?php if ($stars !== null) : ?>
                    <?php get_template_part('template-parts/content/rating-stars', null, ['stars' => $stars]); ?>
                    <span><?php echo esc_html(sprintf(_n('عن %d تقييم', 'عن %d تقييمات', $agg['count'], 'mazaq'), $agg['count'])); ?></span>
                    <span class="archive-head__sep" aria-hidden="true"></span>
                <?php endif; ?>
                <span class="archive-head__count num"><?php echo esc_html((string) $film_article_count); ?></span>
                <span><?php echo esc_html(_n('مقال يذكر هذا الفيلم', 'مقالات تذكر هذا الفيلم', $film_article_count, 'mazaq')); ?></span>
            </p>
        </div>
    </div>
</header>

<div class="archive-ad max-w-7xl mx-auto px-4"><?php mazaq_render_ad('archive_banner'); ?></div>

<main id="main-content" class="archive-main max-w-7xl mx-auto px-4 pb-20">
    <?php get_template_part('template-parts/archive/archive-feed', null, [
        'empty_message' => __('لا توجد مقالات تذكر هذا الفيلم حالياً.', 'mazaq'),
        'empty_link' => true,
    ]); ?>
</main>

<?php get_footer(); ?>
