<?php

declare(strict_types=1);

$film = mazaq_film_record((int) get_the_ID());
if ($film === null) {
    return;
}

$rating_stars = $film['rating'] !== null ? mazaq_film_rating_stars($film['rating']) : null;
$film_title = $film['title'] !== '' ? $film['title'] : (string) get_the_title();
?>
<aside class="film-infobox programme__note" aria-labelledby="film-infobox-title">
    <h2 id="film-infobox-title" class="film-infobox__title"><?php esc_html_e('بطاقة الفيلم', 'mazaq'); ?></h2>
    <?php if ($film_title !== '') : ?>
        <p class="film-infobox__film"><?php echo esc_html($film_title); ?></p>
    <?php endif; ?>
    <?php if ($film['year'] !== '' || $film['director'] !== '') : ?>
        <dl class="film-infobox__list">
            <?php if ($film['year'] !== '') : ?>
                <div><dt><?php esc_html_e('السنة', 'mazaq'); ?></dt><dd class="num"><?php echo esc_html($film['year']); ?></dd></div>
            <?php endif; ?>
            <?php if ($film['director'] !== '') : ?>
                <div><dt><?php esc_html_e('إخراج', 'mazaq'); ?></dt><dd><?php echo esc_html($film['director']); ?></dd></div>
            <?php endif; ?>
        </dl>
    <?php endif; ?>
    <?php if ($film['rating_text'] !== '') : ?>
        <div class="film-infobox__foot">
            <span class="film-infobox__grade-label"><?php esc_html_e('التقييم', 'mazaq'); ?></span>
            <?php if ($rating_stars !== null) : ?>
                <?php get_template_part('template-parts/content/rating-stars', null, ['stars' => $rating_stars]); ?>
            <?php else : ?>
                <span class="film-infobox__grade num"><?php echo esc_html($film['rating_text']); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</aside>
