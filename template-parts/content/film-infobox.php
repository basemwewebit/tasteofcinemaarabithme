<?php

declare(strict_types=1);

$mazaq_read_film_field = static function (string $key): string {
    if (!function_exists('get_field')) {
        return '';
    }

    $value = get_field($key);

    return is_scalar($value) ? trim((string) $value) : '';
};

$fields = [
    'film_title'    => $mazaq_read_film_field('film_title'),
    'film_year'     => $mazaq_read_film_field('film_year'),
    'film_director' => $mazaq_read_film_field('film_director'),
    'film_rating'   => $mazaq_read_film_field('film_rating'),
];

// Keep "0"-valued fields: a zero rating is data, not absence.
$fields = array_filter($fields, static fn (string $value): bool => $value !== '');

if (empty($fields)) {
    return;
}

$rating_stars = isset($fields['film_rating']) && function_exists('mazaq_film_rating_stars')
    ? mazaq_film_rating_stars((string) $fields['film_rating'])
    : null;

$film_title = $fields['film_title'] ?? (string) get_the_title();
?>
<aside class="film-infobox programme__note" aria-labelledby="film-infobox-title">
    <h2 id="film-infobox-title" class="film-infobox__title"><?php esc_html_e('بطاقة الفيلم', 'mazaq'); ?></h2>
    <?php if ($film_title !== '') : ?>
        <p class="film-infobox__film"><?php echo esc_html($film_title); ?></p>
    <?php endif; ?>
    <?php if (isset($fields['film_year']) || isset($fields['film_director'])) : ?>
        <dl class="film-infobox__list">
            <?php if (isset($fields['film_year'])) : ?>
                <div><dt><?php esc_html_e('السنة', 'mazaq'); ?></dt><dd class="num"><?php echo esc_html($fields['film_year']); ?></dd></div>
            <?php endif; ?>
            <?php if (isset($fields['film_director'])) : ?>
                <div><dt><?php esc_html_e('إخراج', 'mazaq'); ?></dt><dd><?php echo esc_html($fields['film_director']); ?></dd></div>
            <?php endif; ?>
        </dl>
    <?php endif; ?>
    <?php if (isset($fields['film_rating'])) : ?>
        <div class="film-infobox__foot">
            <span class="film-infobox__grade-label"><?php esc_html_e('التقييم', 'mazaq'); ?></span>
            <?php if ($rating_stars !== null) : ?>
                <?php get_template_part('template-parts/content/rating-stars', null, ['stars' => $rating_stars]); ?>
            <?php else : ?>
                <span class="film-infobox__grade num"><?php echo esc_html($fields['film_rating']); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</aside>
