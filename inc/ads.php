<?php

declare(strict_types=1);

/**
 * The Ad placement catalogue: every placement the theme renders, with the
 * format its plate reserves. Theme options are generated from it; a
 * placement's options are stored under "ad_slot_{placement}".
 */
const MAZAQ_AD_PLACEMENTS = [
    'hero_banner' => 'horizontal',
    'mobile_menu' => 'responsive',
    'in_article' => 'fluid',
    'bottom_article' => 'horizontal',
    'archive_banner' => 'horizontal',
    'in_feed' => 'rectangle',
];

// The In-feed placement precedes every Nth item of a feed, counted across pages.
const MAZAQ_IN_FEED_AD_INTERVAL = 8;

/** The placement's AdSense slot id (its Network ad), or '' when none is set. */
function mazaq_get_ad_slot(string $placement): string
{
    if (!function_exists('get_field')) {
        return '';
    }
    return trim((string) get_field('ad_slot_' . $placement, 'option'));
}

/**
 * AdSense client ids look like "ca-pub-1234567890123456"; anything outside
 * that alphabet is stripped so a malformed option can never reach the
 * attribute or the script URL.
 */
function mazaq_get_adsense_publisher_id(): string
{
    if (!function_exists('get_field')) {
        return '';
    }
    return (string) preg_replace('/[^A-Za-z0-9-]/', '', (string) get_field('adsense_publisher_id', 'option'));
}

/**
 * A Direct ad is the image and destination URL an editor set for an Ad
 * placement. Both must be present and the URL must be http(s); anything less
 * is no Direct ad at all.
 *
 * @return array{url: string, image: string}|null
 */
function mazaq_get_direct_ad(string $placement): ?array
{
    if (!function_exists('get_field')) {
        return null;
    }
    $slot_name = 'ad_slot_' . $placement;

    // esc_url_raw() would repair "example.com" or keep "//host" and "/path";
    // only an explicit http(s) URL counts.
    $url = trim((string) get_field($slot_name . '_direct_url', 'option'));
    $url = preg_match('#^https?://#i', $url) ? esc_url_raw($url, ['http', 'https']) : '';
    $image_id = (int) get_field($slot_name . '_direct_image', 'option');
    // "full" keeps a GIF animated; resized copies are static frames.
    $image = $image_id ? wp_get_attachment_image_url($image_id, 'full') : false;

    return ($url !== '' && $image) ? ['url' => $url, 'image' => $image] : null;
}

/**
 * The In-feed placement's plate before the item at $position (1-based, counted
 * across pages), or nothing when none is due there. Plates fall before items
 * 9, 17, 25 … Only the first may carry the Direct ad: load-more pages are
 * separate requests, so the once-per-page-view budget is read from position.
 */
function mazaq_render_in_feed_ad(int $position): void
{
    if ($position <= 1 || ($position - 1) % MAZAQ_IN_FEED_AD_INTERVAL !== 0) {
        return;
    }

    mazaq_render_ad('in_feed', $position === MAZAQ_IN_FEED_AD_INTERVAL + 1);
}

/**
 * Render a placement as a sponsor's plate: a quiet celluloid-framed stage with
 * an honest «إعلان» note and space reserved per the placement's format, so a
 * late creative never shifts the page. A complete Direct ad takes the
 * placement (once per request); otherwise the Network ad; an unconfigured or
 * unknown placement renders nothing.
 *
 * @param bool $direct_allowed False only for In-feed plates past the first.
 */
function mazaq_render_ad(string $placement, bool $direct_allowed = true): void
{
    $format = MAZAQ_AD_PLACEMENTS[$placement] ?? null;
    if ($format === null) {
        return;
    }

    // A Direct ad wins its placement, but shows once per placement per request
    // so the in-article injection never repeats one image down a single article.
    static $direct_shown = [];
    $direct = (!$direct_allowed || isset($direct_shown[$placement])) ? null : mazaq_get_direct_ad($placement);
    if ($direct) {
        $direct_shown[$placement] = true;
    }

    $slot_id = mazaq_get_ad_slot($placement);
    $publisher = mazaq_get_adsense_publisher_id();
    if (!$direct && ($slot_id === '' || $publisher === '')) {
        return;
    }

    printf(
        '<aside class="ad-container" data-ad-container="true" data-slot-name="%1$s" data-ad-format="%2$s" data-expects-network-ad="%3$d">',
        esc_attr('ad_slot_' . $placement),
        esc_attr($format),
        $direct ? 0 : 1
    );

    echo '<p class="ad-container__note"><span class="ad-container__mark" aria-hidden="true"></span>' . esc_html__('إعلان', 'mazaq') . '</p>';
    echo '<div class="ad-container__stage">';

    if ($direct) {
        printf(
            '<a href="%1$s" target="_blank" rel="sponsored noopener noreferrer"><img src="%2$s" alt="%3$s" loading="lazy"></a>',
            esc_url($direct['url']),
            esc_url($direct['image']),
            esc_attr__('إعلان', 'mazaq')
        );
    } else {
        // The creative stays pending until the shared observer promotes it near
        // the viewport (see mazaq_ads_lazy_push); space is reserved meanwhile.
        printf(
            '<ins class="adsbygoogle" style="display:block" data-ad-ins="true" data-ad-pending="true" data-ad-client="%1$s" data-ad-slot="%2$s" data-ad-format="%3$s" data-full-width-responsive="true"></ins>',
            esc_attr($publisher),
            esc_attr($slot_id),
            esc_attr($format)
        );
    }

    echo '</div>';
    echo '</aside>';
}

function mazaq_adsense_head_script(): void
{
    $publisher = mazaq_get_adsense_publisher_id();
    if ($publisher === '') {
        return;
    }

    $src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode($publisher);
    echo '<script async src="' . esc_url($src) . '" crossorigin="anonymous"></script>';
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
    if (mazaq_get_adsense_publisher_id() === '') {
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
