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
        $author_name
    );
}
?>

<aside class="author-box" aria-labelledby="author-box-title">
    <div class="author-box__inner">
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
</aside>
