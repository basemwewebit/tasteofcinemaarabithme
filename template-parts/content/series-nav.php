<?php

declare(strict_types=1);

$series_label = function_exists('get_field') ? (string) get_field('series_label') : '';
$previous_id = function_exists('get_field') ? (int) get_field('series_previous_post') : 0;
$next_id = function_exists('get_field') ? (int) get_field('series_next_post') : 0;

$previous_post = $previous_id ? get_post($previous_id) : null;
$next_post = $next_id ? get_post($next_id) : null;

$show_previous = $previous_post && $previous_post->post_status === 'publish';
$show_next = $next_post && $next_post->post_status === 'publish';

if (!$series_label && !$show_previous && !$show_next) {
    return;
}
?>
<nav class="series-nav" aria-labelledby="series-nav-title">
    <p class="series-nav__kicker"><?php esc_html_e('سلسلة تحريرية', 'mazaq'); ?></p>
    <h2 id="series-nav-title" class="series-nav__title"><?php echo esc_html($series_label ?: __('اقرأ أجزاء السلسلة', 'mazaq')); ?></h2>
    <div class="series-nav__links">
        <?php if ($show_previous) : ?>
            <a class="series-nav__link flex items-center justify-between gap-4 group" href="<?php echo esc_url(get_permalink($previous_id)); ?>">
                <div class="flex flex-col">
                    <span class="text-xs text-slate-500 dark:text-primary-tint font-bold mb-1"><?php esc_html_e('السابق', 'mazaq'); ?></span>
                    <strong class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors"><?php echo esc_html(get_the_title($previous_id)); ?></strong>
                </div>
                <svg class="w-5 h-5 text-primary shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        <?php endif; ?>
        <?php if ($show_next) : ?>
            <a class="series-nav__link flex items-center justify-between gap-4 group" href="<?php echo esc_url(get_permalink($next_id)); ?>">
                <div class="flex flex-col">
                    <span class="text-xs text-slate-500 dark:text-primary-tint font-bold mb-1"><?php esc_html_e('التالي', 'mazaq'); ?></span>
                    <strong class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors"><?php echo esc_html(get_the_title($next_id)); ?></strong>
                </div>
                <svg class="w-5 h-5 text-primary shrink-0 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
        <?php endif; ?>
    </div>

</nav>
