<?php

declare(strict_types=1);

function mazaq_get_ad_slot(string $slot_name): string
{
    if (!function_exists('get_field')) {
        return '';
    }
    return (string) get_field($slot_name, 'option');
}

/**
 * Render an ad slot as a sponsor's plate: a quiet celluloid-framed stage with
 * an honest «إعلان» note and space reserved per format, so a late creative
 * never shifts the page. An unconfigured slot renders nothing at all.
 */
function mazaq_render_ad(string $slot_name, string $format = 'responsive', string $classes = ''): void
{
    $slot_id = mazaq_get_ad_slot($slot_name);
    $publisher = function_exists('get_field') ? (string) get_field('adsense_publisher_id', 'option') : '';
    $dummy_ads_enabled = false;
    $expects_network_ad = (bool) ($slot_id && $publisher && !$dummy_ads_enabled);

    // Production silence: an unconfigured slot never draws a husk.
    if (!$expects_network_ad && !$dummy_ads_enabled) {
        return;
    }

    $class_attr = trim('ad-container ' . $classes);
    printf(
        '<aside class="%1$s" data-ad-container="true" data-slot-name="%2$s" data-ad-format="%3$s" data-expects-network-ad="%4$d">',
        esc_attr($class_attr),
        esc_attr($slot_name),
        esc_attr($format),
        $expects_network_ad ? 1 : 0
    );

    echo '<p class="ad-container__note"><span class="ad-container__mark" aria-hidden="true"></span>' . esc_html__('إعلان', 'mazaq') . '</p>';
    echo '<div class="ad-container__stage">';

    if ($expects_network_ad) {
        // The creative stays pending until the shared observer promotes it near
        // the viewport (see mazaq_ads_lazy_push); space is reserved meanwhile.
        printf(
            '<ins class="adsbygoogle" style="display:block" data-ad-ins="true" data-ad-pending="true" data-ad-client="%1$s" data-ad-slot="%2$s" data-ad-format="%3$s" data-full-width-responsive="true"></ins>',
            esc_attr($publisher),
            esc_attr($slot_id),
            esc_attr($format)
        );
    } else {
        echo '<span class="ad-container__dummy">' . esc_html__('مساحة إعلانية', 'mazaq') . '</span>';
    }

    echo '</div>';
    echo '</aside>';
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

/**
 * One shared observer promotes every reserved creative to a real request once
 * it approaches the viewport (120% rootMargin), and a MutationObserver rescans
 * for plates appended by infinite scroll. Until promotion, the plate holds its
 * reserved space and the page stays quiet.
 */
function mazaq_ads_lazy_push(): void
{
    if (!function_exists('get_field') || (string) get_field('adsense_publisher_id', 'option') === '') {
        return;
    }

    $js = <<<'JS'
(function () {
    "use strict";
    var SELECTOR = "ins[data-ad-pending]";
    function promote(el) {
        if (!el || !el.hasAttribute("data-ad-pending")) return;
        el.removeAttribute("data-ad-pending");
        try { (window.adsbygoogle = window.adsbygoogle || []).push({}); } catch (e) {}
    }
    var pending = document.querySelectorAll(SELECTOR);
    if (!pending.length) return;
    if (!("IntersectionObserver" in window)) {
        Array.prototype.forEach.call(pending, promote);
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            io.unobserve(entry.target);
            promote(entry.target);
        });
    }, { rootMargin: "120% 0px" });
    Array.prototype.forEach.call(pending, function (el) { io.observe(el); });
    if ("MutationObserver" in window) {
        var scan = function () {
            Array.prototype.forEach.call(document.querySelectorAll(SELECTOR), function (el) {
                if (el.__mazaqAdObserved) return;
                el.__mazaqAdObserved = true;
                io.observe(el);
            });
        };
        new MutationObserver(scan).observe(document.body, { childList: true, subtree: true });
    }
})();
JS;

    if (wp_script_is('mazaq-app', 'registered')) {
        wp_add_inline_script('mazaq-app', $js);
        return;
    }

    wp_register_script('mazaq-ads-lazy-push', '', [], null, true);
    wp_enqueue_script('mazaq-ads-lazy-push');
    wp_add_inline_script('mazaq-ads-lazy-push', $js);
}
add_action('wp_enqueue_scripts', 'mazaq_ads_lazy_push', 20);

function mazaq_inject_in_article_ads(string $content): string
{
    if (!is_single() || is_admin()) {
        return $content;
    }

    $parts = explode('</p>', $content);
    
    // We need at least 3 paragraphs to inject an ad
    if (count($parts) <= 3) {
        return $content;
    }

    $new_content = '';
    $max_ads = 3; // Maximum 3 injected ads to avoid clutter
    $ad_count = 0;

    foreach ($parts as $index => $part) {
        // If it's the last element, it's either empty (if string ended in </p>) 
        // or contains the remainder of the content. Don't append </p>.
        if ($index === count($parts) - 1) {
            $new_content .= $part;
            break;
        }

        $new_content .= $part . '</p>';

        // Check if the current part actually contains text to avoid injecting after empty splits or spacer divs
        if (strlen(trim(strip_tags($part))) > 0 && ($index + 1) % 3 === 0 && $ad_count < $max_ads) {
            ob_start();
            get_template_part('template-parts/ads/ad-in-article');
            $new_content .= (string) ob_get_clean();
            $ad_count++;
        }
    }

    return $new_content;
}
add_filter('the_content', 'mazaq_inject_in_article_ads', 20);
