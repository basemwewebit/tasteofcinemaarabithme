<?php

declare(strict_types=1);

$series_label = function_exists('get_field') ? (string) get_field('series_label') : '';
$previous_id = function_exists('get_field') ? (int) get_field('series_previous_post') : 0;
$next_id = function_exists('get_field') ? (int) get_field('series_next_post') : 0;

if (!$series_label && !$previous_id && !$next_id) {
    return;
}
?>
<nav class="series-nav" aria-labelledby="series-nav-title">
    <p class="series-nav__kicker"><?php esc_html_e('سلسلة تحريرية', 'mazaq'); ?></p>
    <h2 id="series-nav-title" class="series-nav__title"><?php echo esc_html($series_label ?: __('اقرأ أجزاء السلسلة', 'mazaq')); ?></h2>
    <div class="series-nav__links">
        <?php if ($previous_id) : ?>
            <a class="series-nav__link" href="<?php echo esc_url(get_permalink($previous_id)); ?>">
                <span><?php esc_html_e('السابق', 'mazaq'); ?></span>
                <strong><?php echo esc_html(get_the_title($previous_id)); ?></strong>
            </a>
        <?php endif; ?>
        <?php if ($next_id) : ?>
            <a class="series-nav__link series-nav__link--next" href="<?php echo esc_url(get_permalink($next_id)); ?>">
                <span><?php esc_html_e('التالي', 'mazaq'); ?></span>
                <strong><?php echo esc_html(get_the_title($next_id)); ?></strong>
            </a>
        <?php endif; ?>
    </div>
</nav>
