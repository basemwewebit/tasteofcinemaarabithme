<?php

declare(strict_types=1);

$fields = [
    'film_title' => function_exists('get_field') ? (string) get_field('film_title') : '',
    'film_year' => function_exists('get_field') ? (string) get_field('film_year') : '',
    'film_director' => function_exists('get_field') ? (string) get_field('film_director') : '',
    'film_rating' => function_exists('get_field') ? (string) get_field('film_rating') : '',
];

$fields = array_filter($fields);

if (empty($fields)) {
    return;
}
?>
<aside class="film-infobox" aria-labelledby="film-infobox-title">
    <p class="film-infobox__kicker"><?php esc_html_e('بطاقة الفيلم', 'mazaq'); ?></p>
    <h2 id="film-infobox-title" class="film-infobox__title"><?php echo esc_html($fields['film_title'] ?? get_the_title()); ?></h2>
    <dl class="film-infobox__list">
        <?php if (!empty($fields['film_year'])) : ?>
            <div><dt><?php esc_html_e('السنة', 'mazaq'); ?></dt><dd class="num"><?php echo esc_html($fields['film_year']); ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($fields['film_director'])) : ?>
            <div><dt><?php esc_html_e('إخراج', 'mazaq'); ?></dt><dd><?php echo esc_html($fields['film_director']); ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($fields['film_rating'])) :
            $rating_stars = function_exists('mazaq_film_rating_stars')
                ? mazaq_film_rating_stars((string) $fields['film_rating'])
                : null; ?>
            <div>
                <dt><?php esc_html_e('التقييم', 'mazaq'); ?></dt>
                <?php if ($rating_stars !== null) : ?>
                    <dd>
                        <span class="film-rating" role="img" aria-label="<?php echo esc_attr($rating_stars['label']); ?>">
                            <span class="film-rating__stars" aria-hidden="true">★★★★★<span class="film-rating__fill" style="width: <?php echo esc_attr((string) $rating_stars['percent']); ?>%;">★★★★★</span></span>
                            <span class="film-rating__value num"><?php echo esc_html($rating_stars['value'] . '/' . $rating_stars['best']); ?></span>
                        </span>
                    </dd>
                <?php else : ?>
                    <dd class="num"><?php echo esc_html($fields['film_rating']); ?></dd>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </dl>
</aside>
