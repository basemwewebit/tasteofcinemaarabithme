<?php

declare(strict_types=1);

/**
 * Film record: the film a post reviews, as the editor entered it (title, year,
 * director, rating). The only reader of the film fields, and the one place a
 * free-text rating is parsed, so the infobox, the schema and the film's
 * aggregate rating can never disagree.
 *
 * @package Mazaq
 */

/**
 * A post's Film record, or null when none of its film fields is filled.
 *
 * Text is trimmed but otherwise as typed (Arabic digits stay); a "0" is data,
 * not absence. `rating` is the parsed rating, or null when the text can't be
 * parsed confidently.
 *
 * @return array{title:string,year:string,director:string,rating_text:string,rating:?array{ratingValue:int|float,bestRating:int|float,worstRating:int|float}}|null
 */
function mazaq_film_record(int $post_id): ?array
{
    if (!function_exists('get_field')) {
        return null;
    }

    $get = static function (string $name) use ($post_id): string {
        $value = get_field($name, $post_id);
        return is_scalar($value) ? trim((string) $value) : '';
    };

    $record = [
        'title' => $get('film_title'),
        'year' => $get('film_year'),
        'director' => $get('film_director'),
        'rating_text' => $get('film_rating'),
    ];
    if (implode('', $record) === '') {
        return null;
    }

    $record['rating'] = mazaq_film_parse_rating($record['rating_text']);

    return $record;
}

/**
 * Star-display data for a parsed rating: the value, its scale, the fill
 * percent and an accessible label.
 *
 * @param array{ratingValue:int|float,bestRating:int|float} $rating
 * @return array{value:int|float,best:int|float,percent:float,label:string}
 */
function mazaq_film_rating_stars(array $rating): array
{
    $best = (float) $rating['bestRating'];
    $value = (float) $rating['ratingValue'];
    $percent = $best > 0 ? max(0.0, min(100.0, ($value / $best) * 100)) : 0.0;

    return [
        'value' => $rating['ratingValue'],
        'best' => $rating['bestRating'],
        'percent' => round($percent, 2),
        'label' => sprintf(
            /* translators: 1: rating value, 2: best possible rating */
            __('التقييم: %1$s من %2$s', 'mazaq'),
            $rating['ratingValue'],
            $rating['bestRating']
        ),
    ];
}

/**
 * Convert Arabic-Indic and Eastern-Arabic digits to Latin digits.
 * Editors on an Arabic site routinely type ٨ / ۸ instead of 8.
 */
function mazaq_film_latin_digits(string $value): string
{
    $map = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    return strtr($value, $map);
}

/**
 * Parse a free-text rating into a Schema.org Rating fragment.
 *
 * Accepts "8/10", "8 من 10", "8 out of 10", star glyphs (★★★★), or a bare
 * number interpreted against a default scale (10, filterable). Returns null
 * when nothing can be parsed confidently — a Review with no real rating is
 * worse than no Review at all.
 *
 * @return array{ratingValue:int|float,bestRating:int|float,worstRating:int|float}|null
 */
function mazaq_film_parse_rating(string $raw): ?array
{
    $value = trim(mazaq_film_latin_digits($raw));
    if ($value === '') {
        return null;
    }

    $normalized = str_replace(',', '.', $value);
    $num        = '(\d+(?:\.\d+)?)';

    // "8/10", "8 من 10", "8 out of 10"
    if (preg_match('~' . $num . '\s*(?:/|من|out of|of)\s*' . $num . '~iu', $normalized, $m)) {
        return mazaq_film_rating_fragment((float) $m[1], (float) $m[2]);
    }

    // Star glyphs: ★ ☆ ⭐ ✩ ✪ ✫
    $stars = preg_match_all('/[\x{2605}\x{2606}\x{2B50}\x{2729}\x{272A}\x{272B}]/u', $value);
    if ($stars > 0) {
        return mazaq_film_rating_fragment((float) $stars, 5.0);
    }

    // Bare number against the default scale.
    if (preg_match('~^' . $num . '$~', $normalized, $m)) {
        $scale = (float) apply_filters('mazaq_schema_default_rating_scale', 10);
        $val   = (float) $m[1];
        if ($scale > 0 && $val >= 0 && $val <= $scale) {
            return mazaq_film_rating_fragment($val, $scale);
        }
    }

    return null;
}

/**
 * Build a validated Rating fragment, casting whole numbers to int for clean output.
 *
 * @return array{ratingValue:int|float,bestRating:int|float,worstRating:int|float}|null
 */
function mazaq_film_rating_fragment(float $value, float $best): ?array
{
    if ($best <= 0 || $value < 0 || $value > $best) {
        return null;
    }

    $cast = static fn(float $n) => (floor($n) === $n) ? (int) $n : $n;

    return [
        'ratingValue' => $cast($value),
        'bestRating'  => $cast($best),
        'worstRating' => $cast(min(1.0, $value)),
    ];
}
