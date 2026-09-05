<?php

declare(strict_types=1);

/** Front-page hero — "The Continuous Programme" (البرنامج المتواصل).
 *
 * A paged RTL screening programme: the week's feature opens as the lead
 * spread, three related reads follow as subsequent spreads. Every article
 * is a real link in the document — the snap track, the numbered spine and
 * the progress line are progressive enhancement on top, never a gate.
 */

$is_published = static fn (int $post_id): bool => $post_id > 0 && get_post_status($post_id) === 'publish';
$hero_post_ids = array_values(array_unique(array_filter(array_map('intval', (array) mazaq_get_hero_post_ids()), $is_published)));

if (count($hero_post_ids) < 4) {
    $hero_post_ids = array_values(array_unique(array_merge($hero_post_ids, array_map('intval', get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 4 - count($hero_post_ids),
        'fields' => 'ids',
        'post__not_in' => $hero_post_ids ?: [0],
        'ignore_sticky_posts' => true,
    ])))));
}

if (empty($hero_post_ids)) {
    return;
}

$theme_uri = get_template_directory_uri();
$feature_id = $hero_post_ids[0];
$programme_ids = array_slice($hero_post_ids, 1, 3);
$plate_fallbacks = [
    $theme_uri . '/assets/plates/program-one-still.png',
    $theme_uri . '/assets/plates/program-two-still.png',
    $theme_uri . '/assets/plates/program-three-still.png',
];

/** Eastern Arabic numerals for the programme spine, zero-padded to ٠١–٠٤. */
$eastern_numerals = static fn (int $n): string => strtr(str_pad((string) $n, 2, '0', STR_PAD_LEFT), [
    '0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
    '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩',
]);

$title_for = static function (int $post_id): string {
    $title = get_the_title($post_id);
    return $title !== '' ? $title : __('مقال بلا عنوان', 'mazaq');
};

$category_for = static function (int $post_id): array {
    $categories = get_the_category($post_id);
    $term = !empty($categories) ? $categories[0] : null;
    $name = $term instanceof WP_Term && $term->name !== '' ? $term->name : __('اختيار المحرّر', 'mazaq');
    $tint = $term instanceof WP_Term ? mazaq_get_category_tint((int) $term->term_id) : '#C9A227';
    return [$name, $tint];
};

$render_image = static function (int $post_id, string $size, string $class_name, string $fallback, bool $priority = false): void {
    if (has_post_thumbnail($post_id)) {
        $attributes = [
            'class' => $class_name,
            'alt' => '',
            'loading' => $priority ? 'eager' : 'lazy',
            'decoding' => 'async',
            'sizes' => $priority ? '(min-width: 1024px) 88vw, 100vw' : '(min-width: 1024px) 60vw, 100vw',
            'data-drift' => '',
        ];
        if ($priority) {
            $attributes['fetchpriority'] = 'high';
            $attributes['data-no-lazy'] = '1';
        }
        echo get_the_post_thumbnail($post_id, $size, $attributes);
        return;
    }
    ?>
    <img src="<?php echo esc_url($fallback); ?>" class="<?php echo esc_attr($class_name); ?>" alt="" loading="<?php echo $priority ? 'eager' : 'lazy'; ?>" decoding="async" data-drift>
    <?php
};

$feature_title = $title_for($feature_id);
$feature_excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($feature_id)), 27, '…');
$spread_ids = array_merge([$feature_id], $programme_ids);
$spread_count = count($spread_ids);
?>

<section class="programme-hero" dir="rtl" aria-label="<?php esc_attr_e('برنامج العرض — مختارات هذا الأسبوع', 'mazaq'); ?>" data-programme-hero data-count="<?php echo esc_attr((string) $spread_count); ?>">
    <div class="programme-hero__stage">
        <ol class="programme-hero__track" tabindex="0" aria-label="<?php esc_attr_e('شرائح البرنامج — استخدم الأسهم للتنقل بينها', 'mazaq'); ?>">
            <?php foreach ($spread_ids as $index => $post_id) : ?>
                <?php
                $is_lead = 0 === $index;
                $spread_title = $title_for($post_id);
                $spread_excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($post_id)), $is_lead ? 27 : 18, '…');
                [$spread_category, $spread_tint] = $category_for($post_id);
                ?>
                <li class="programme-hero__slide" id="programme-slide-<?php echo esc_attr((string) ($index + 1)); ?>" role="group" aria-roledescription="<?php esc_attr_e('شريحة', 'mazaq'); ?>" aria-label="<?php echo esc_attr(sprintf(__('%1$s من %2$s: %3$s', 'mazaq'), $eastern_numerals($index + 1), $eastern_numerals($spread_count), $spread_title)); ?>">
                    <article class="programme-spread<?php echo $is_lead ? ' programme-spread--lead' : ''; ?>"<?php echo $spread_tint !== '' ? ' style="--ph-tint: ' . esc_attr($spread_tint) . ';"' : ''; ?>>
                        <a class="programme-spread__link" href="<?php echo esc_url(get_permalink($post_id)); ?>" aria-label="<?php echo esc_attr(sprintf(__('اقرأ: %s', 'mazaq'), $spread_title)); ?>">
                            <span class="programme-spread__media" aria-hidden="true">
                                <?php
                                if ($is_lead) {
                                    $render_image($post_id, 'hero-poster', 'programme-spread__image', $theme_uri . '/assets/plates/feature-still.png', true);
                                } else {
                                    $render_image($post_id, 'large', 'programme-spread__image', $plate_fallbacks[$index - 1] ?? $plate_fallbacks[0]);
                                }
                                ?>
                            </span>
                            <span class="programme-spread__panel">
                                <span class="programme-spread__copy">
                                    <span class="programme-spread__title" role="heading" aria-level="2"><?php echo esc_html($spread_title); ?></span>
                                    <?php if ($spread_excerpt !== '') : ?>
                                        <span class="programme-spread__deck"><?php echo esc_html($spread_excerpt); ?></span>
                                    <?php endif; ?>
                                    <?php if ($is_lead) : ?>
                                        <span class="programme-spread__action">
                                            <?php esc_html_e('اقرأ المقال', 'mazaq'); ?>
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"></path></svg>
                                        </span>
                                    <?php else : ?>
                                        <span class="programme-spread__more">
                                            <?php esc_html_e('تابع القراءة', 'mazaq'); ?>
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"></path></svg>
                                        </span>
                                    <?php endif; ?>
                                    <span class="programme-spread__credit">
                                        <span class="programme-spread__category">
                                            <span class="programme-spread__category-marker" aria-hidden="true"></span>
                                            <span class="programme-spread__category-name"><?php echo esc_html($spread_category); ?></span>
                                        </span>
                                        <span class="programme-spread__credit-sep" aria-hidden="true">•</span>
                                        <span class="num"><?php echo esc_html(mazaq_reading_time($post_id)); ?></span>
                                        <span class="programme-spread__credit-sep" aria-hidden="true">•</span>
                                        <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></time>
                                    </span>
                                </span>
                            </span>
                        </a>
                    </article>
                </li>
            <?php endforeach; ?>
        </ol>

        <div class="programme-hero__spine" hidden>
            <ol class="programme-hero__pager">
                <?php foreach ($spread_ids as $index => $post_id) : ?>
                    <li class="programme-hero__page-item">
                        <button type="button" class="programme-hero__page<?php echo 0 === $index ? ' is-active' : ''; ?>" data-slide="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr(sprintf(__('الانتقال إلى الشريحة %1$s: %2$s', 'mazaq'), $eastern_numerals($index + 1), $title_for($post_id))); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>">
                            <span class="num"><?php echo esc_html($eastern_numerals($index + 1)); ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ol>
            <span class="programme-hero__progress" aria-hidden="true"><span class="programme-hero__progress-fill" style="transform: scaleX(<?php echo esc_attr(number_format(1 / max(1, $spread_count), 4, '.', '')); ?>)"></span></span>
            <p class="programme-hero__live programme-sr-only" role="status" aria-live="polite"></p>
        </div>
    </div>
</section>
