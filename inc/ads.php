<?php

declare(strict_types=1);

function mazaq_get_ad_slot(string $slot_name): string
{
    if (!function_exists('get_field')) {
        return '';
    }
    return (string) get_field($slot_name, 'option');
}

function mazaq_render_ad(string $slot_name, string $format = 'responsive', string $classes = ''): void
{
    $slot_id = mazaq_get_ad_slot($slot_name);

    $class_attr = trim('ad-container ' . $classes);
    echo '<div class="' . esc_attr($class_attr) . '" data-slot-name="' . esc_attr($slot_name) . '">';

    if ($slot_id) {
        echo '<ins class="adsbygoogle" style="display:block" data-ad-client="' . esc_attr((string) get_field('adsense_publisher_id', 'option')) . '" data-ad-slot="' . esc_attr($slot_id) . '" data-ad-format="' . esc_attr($format) . '" data-full-width-responsive="true"></ins>';
        echo '<script>(adsbygoogle=window.adsbygoogle||[]).push({});</script>';
    } else {
        echo '<span class="text-center px-4">مساحة إعلانية</span>';
    }

    echo '</div>';
}

function mazaq_adsense_head_script(): void
{
    if (!function_exists('get_field')) {
        return;
    }

    $publisher = (string) get_field('adsense_publisher_id', 'option');
    if (!$publisher) {
        return;
    }

    echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr($publisher) . '" crossorigin="anonymous"></script>';
}
add_action('wp_head', 'mazaq_adsense_head_script');

function mazaq_inject_in_article_ads(string $content): string
{
    if (!is_single() || is_admin()) {
        return $content;
    }

    if (!mazaq_get_ad_slot('ad_slot_in_article')) {
        return $content;
    }

    $parts = explode('</p>', $content);
    if (count($parts) < 4) {
        return $content;
    }

    $new_content = '';
    foreach ($parts as $index => $part) {
        if (trim($part) === '') {
            continue;
        }
        $new_content .= $part . '</p>';
        if (($index + 1) % 3 === 0) {
            ob_start();
            get_template_part('template-parts/ads/ad-in-article');
            $new_content .= (string) ob_get_clean();
        }
    }

    return $new_content;
}
add_filter('the_content', 'mazaq_inject_in_article_ads', 20);
