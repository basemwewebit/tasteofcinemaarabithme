<?php
/**
 * Template part for displaying the author bio box at the end of a single post
 */

$author_id = get_the_author_meta('ID');
$author_name = get_the_author_meta('display_name');
$author_bio = get_the_author_meta('description');
$author_link = get_author_posts_url($author_id);

// Only show if there's an author
if (empty($author_id)) {
    return;
}

// Fallback bio if empty
if (empty($author_bio)) {
    $author_bio = sprintf(
        /* translators: %s: Author name */
        esc_html__('محرر في منصة Taste of Cinema العربية. يسعى %s لتقديم أفضل التحليلات والقوائم السينمائية لإثراء المحتوى العربي بأهم الأعمال الفنية حول العالم.', 'mazaq'),
        $author_name
    );
}
?>

<div class="author-box mt-12 p-6 md:p-8 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700/50 transition-colors">
    <div class="flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-start">
        <div class="shrink-0">
            <a href="<?php echo esc_url($author_link); ?>">
                <?php echo get_avatar($author_id, 96, '', '', ['class' => 'w-24 h-24 object-cover rounded-full shadow-md hover:scale-105 transition-transform duration-300', 'loading' => 'lazy']); ?>
            </a>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
                <a href="<?php echo esc_url($author_link); ?>" class="hover:text-primary transition-colors">
                    <?php echo esc_html($author_name); ?>
                </a>
            </h3>
            <p class="text-slate-600 dark:text-slate-400 text-sm md:text-base leading-relaxed mb-4">
                <?php echo wp_kses_post($author_bio); ?>
            </p>
            <a href="<?php echo esc_url($author_link); ?>" class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:text-slate-900 dark:hover:text-white transition-colors">
                <span><?php esc_html_e('عرض جميع مقالات الكاتب', 'mazaq'); ?></span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m6-6-6 6 6 6"></path></svg>
            </a>
        </div>
    </div>
</div>
