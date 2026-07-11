<?php
/**
 * Overlaid star rating. Single markup for both the film infobox and the
 * /film/ archive header, fed by mazaq_film_rating_stars().
 *
 * @var array{value:int|float,best:int|float,percent:float,label:string}|null $stars
 */
$stars = $args['stars'] ?? null;
if (!is_array($stars)) {
    return;
}
?>
<span class="film-rating" role="img" aria-label="<?php echo esc_attr($stars['label']); ?>">
    <span class="film-rating__stars" aria-hidden="true">★★★★★<span class="film-rating__fill" style="width: <?php echo esc_attr((string) $stars['percent']); ?>%;">★★★★★</span></span>
    <span class="film-rating__value num"><?php echo esc_html($stars['value'] . '/' . $stars['best']); ?></span>
</span>
