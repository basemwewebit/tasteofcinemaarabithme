<?php
$author_id = get_the_author_meta('ID');
$author_name = get_the_author_meta('display_name');
$author_bio = get_the_author_meta('description');
$author_link = get_author_posts_url($author_id);

if (empty($author_id)) {
    return;
}

if (empty($author_bio)) {
    $author_bio = sprintf(
        /* translators: %s: Author name */
        esc_html__('محرر في منصة Taste of Cinema العربية. يسعى %s لتقديم أفضل التحليلات والقوائم السينمائية لإثراء المحتوى العربي بأهم الأعمال الفنية حول العالم.', 'mazaq'),
        esc_html($author_name)
    );
}

// Fetch latest 3 posts from this author, excluding current post
$author_posts = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 3,
    'author' => $author_id,
    'post__not_in' => [get_the_ID()],
    'post_status' => 'publish',
    'no_found_rows' => true,
    'ignore_sticky_posts' => true
]);
?>

<aside class="author-box" aria-labelledby="author-box-title">
    <div class="author-box__inner <?php echo $author_posts->have_posts() ? 'mb-8 pb-6 border-b border-mist dark:border-border-subtle' : ''; ?>">
        <div class="author-box__media">
            <a href="<?php echo esc_url($author_link); ?>" class="author-box__avatar-link" aria-label="<?php echo esc_attr(sprintf(__('عرض أرشيف الكاتب: %s', 'mazaq'), $author_name)); ?>">
                <?php echo get_avatar($author_id, 96, '', '', ['class' => 'author-box__avatar', 'loading' => 'lazy']); ?>
            </a>
        </div>
        <div class="author-box__body">
            <p class="author-box__kicker"><?php esc_html_e('صوت من غرفة التحرير', 'mazaq'); ?></p>
            <h2 id="author-box-title" class="author-box__name">
                <a href="<?php echo esc_url($author_link); ?>">
                    <?php echo esc_html($author_name); ?>
                </a>
            </h2>
            <p class="author-box__bio">
                <?php echo wp_kses_post($author_bio); ?>
            </p>
            <a href="<?php echo esc_url($author_link); ?>" class="author-box__link">
                <span><?php esc_html_e('عرض جميع مقالات الكاتب', 'mazaq'); ?></span>
                <svg class="author-box__link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m6-6-6 6 6 6"></path></svg>
            </a>
        </div>
    </div>

    <?php if ($author_posts->have_posts()) : ?>
        <div class="author-box__latest">
            <h3 class="font-display text-label text-slate-800 dark:text-primary-tint mb-4"><?php echo esc_html(sprintf(__('آخر مقالات %s', 'mazaq'), $author_name)); ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <?php while ($author_posts->have_posts()) : $author_posts->the_post(); ?>
                    <?php 
                    get_template_part('template-parts/content/article-card', null, [
                        'layout' => 'compact',
                        'class' => 'h-full',
                    ]);
                    ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    <?php endif; ?>
</aside>

