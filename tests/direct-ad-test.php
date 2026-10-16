<?php

/**
 * Standalone self-check for the Direct ad: image + URL win the Ad placement,
 * an incomplete or non-http(s) pair is no Direct ad, and one placement shows
 * its Direct ad once per request. No framework, no WP: run with
 * `php tests/direct-ad-test.php`.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

// --- Fake WP adapters ----------------------------------------------------------
$GLOBALS['fake_options'] = [];

function get_field(string $name, $post_id = null)
{
    return $GLOBALS['fake_options'][$name] ?? '';
}

// Mirrors WordPress: a scheme-less value that is not "/", "#" or "?" led gets "http://" prepended.
function esc_url_raw(string $url, array $protocols = []): string
{
    if (!str_contains($url, ':') && !in_array($url[0] ?? '', ['/', '#', '?'], true)) {
        $url = 'http://' . $url;
    }
    return preg_match('#^(' . implode('|', $protocols) . ')://#i', $url) || str_starts_with($url, '/') ? $url : '';
}

function add_action(...$args): void {}
function add_filter(...$args): void {}
$GLOBALS['fake_in_loop'] = true;
function is_admin(): bool { return false; }
function is_feed(): bool { return false; }
function is_embed(): bool { return false; }
function wp_doing_ajax(): bool { return false; }
function is_single(): bool { return true; }
function is_main_query(): bool { return true; }
function in_the_loop(): bool { return $GLOBALS['fake_in_loop']; }
function wp_strip_all_tags(string $text): string { return strip_tags($text); }
function esc_url(string $url): string { return $url; }
function esc_attr(string $text): string { return htmlspecialchars($text, ENT_QUOTES); }
function esc_attr__(string $text, string $domain = ''): string { return esc_attr($text); }
function esc_html__(string $text, string $domain = ''): string { return $text; }

function wp_get_attachment_image_url(int $id, string $size)
{
    return $id === 7 ? "https://cdn.test/ad-{$size}.gif" : false;
}

require __DIR__ . '/../inc/ads.php';

function render(string $placement): string
{
    ob_start();
    mazaq_render_ad($placement);
    return (string) ob_get_clean();
}

// Complete pair: linked image, safe rel, full-size GIF, no network markup.
$GLOBALS['fake_options'] = ['ad_slot_archive_banner_direct_url' => 'https://example.com/?x=1', 'ad_slot_archive_banner_direct_image' => 7];
$html = render('archive_banner');
rotation_test_check(str_contains($html, '<a href="https://example.com/?x=1" target="_blank" rel="sponsored noopener noreferrer"><img src="https://cdn.test/ad-full.gif" alt="إعلان" loading="lazy"></a>'), 'complete pair renders the linked image');
rotation_test_check(!str_contains($html, 'adsbygoogle') && str_contains($html, 'data-expects-network-ad="0"'), 'Direct ad is not a network ad');

// Same placement again in this request: nothing (no network ad configured).
// A different placement in the same request still shows its own Direct ad.
$GLOBALS['fake_options'] += ['ad_slot_hero_banner_direct_url' => 'https://example.org', 'ad_slot_hero_banner_direct_image' => 7];
rotation_test_check(str_contains(render('hero_banner'), 'https://example.org'), 'another placement shows its own Direct ad');
rotation_test_check(render('archive_banner') === '', 'second render of the placement shows no repeat');

// Repeat falls back to the Network ad when one is configured.
$GLOBALS['fake_options'] = ['ad_slot_bottom_article_direct_url' => 'https://example.com', 'ad_slot_bottom_article_direct_image' => 7, 'ad_slot_bottom_article' => '123', 'adsense_publisher_id' => 'ca-pub-1'];
$first = render('bottom_article');
rotation_test_check(str_contains($first, 'https://example.com') && !str_contains($first, 'adsbygoogle'), 'Direct ad beats the Network ad');
rotation_test_check(str_contains(render('bottom_article'), 'adsbygoogle'), 'repeat falls back to the Network ad');

// Incomplete or unsafe: nothing. (None of these spends mobile_menu's budget.)
$GLOBALS['fake_options'] = ['ad_slot_mobile_menu_direct_url' => 'https://example.com'];
rotation_test_check(render('mobile_menu') === '', 'URL without image renders nothing');
$GLOBALS['fake_options'] = ['ad_slot_mobile_menu_direct_image' => 7];
rotation_test_check(render('mobile_menu') === '', 'image without URL renders nothing');
foreach (['javascript:alert(1)', 'example.com', '//evil.test/x', '/relative', 'ftp://example.com'] as $bad) {
    $GLOBALS['fake_options'] = ['ad_slot_mobile_menu_direct_url' => $bad, 'ad_slot_mobile_menu_direct_image' => 7];
    rotation_test_check(render('mobile_menu') === '', "'{$bad}' is not an http(s) URL and renders nothing");
}
$GLOBALS['fake_options'] = ['ad_slot_mobile_menu_direct_url' => 'https://example.com', 'ad_slot_mobile_menu_direct_image' => 99];
rotation_test_check(render('mobile_menu') === '', 'missing attachment renders nothing');

// Each placement reserves its own format; the template names the placement only.
$GLOBALS['fake_options'] = ['adsense_publisher_id' => 'ca-pub-1', 'ad_slot_in_article' => '1', 'ad_slot_in_feed' => '2', 'ad_slot_mobile_menu' => '3', 'ad_slot_hero_banner' => '4'];
rotation_test_check(str_contains(render('in_article'), 'data-ad-format="fluid"'), 'in-article plate reserves the fluid format');
rotation_test_check(str_contains(render('in_feed'), 'data-ad-format="rectangle"'), 'In-feed plate reserves the rectangle format');
rotation_test_check(str_contains(render('mobile_menu'), 'data-ad-format="responsive"'), 'mobile menu plate reserves the responsive format');
rotation_test_check(str_contains(render('hero_banner'), 'data-ad-format="horizontal"'), 'hero banner plate reserves the horizontal format');

// A name outside the catalogue renders nothing, even when an option exists for it.
$GLOBALS['fake_options'] = ['adsense_publisher_id' => 'ca-pub-1', 'ad_slot_sidebar_square' => '5'];
rotation_test_check(render('sidebar_square') === '', 'an unknown placement renders nothing');

// Theme options carry a slot, a Direct ad image and a Direct ad URL for
// exactly the placements that render.
$GLOBALS['acf_groups'] = [];
function acf_add_local_field_group(array $group): void { $GLOBALS['acf_groups'][] = $group; }
require __DIR__ . '/../inc/scf-fields.php';
mazaq_register_acf_fields();
$names = array_column($GLOBALS['acf_groups'][0]['fields'], 'name');
$ad_names = array_values(array_filter($names, static fn (string $n): bool => str_starts_with($n, 'ad_slot_')));
$expected = [];
foreach (['hero_banner', 'mobile_menu', 'in_article', 'bottom_article', 'archive_banner', 'in_feed'] as $placement) {
    array_push($expected, "ad_slot_{$placement}", "ad_slot_{$placement}_direct_image", "ad_slot_{$placement}_direct_url");
}
rotation_test_check($ad_names === $expected, 'theme options list each rendered placement\'s three fields');

// The article-body injection runs only in the main loop: excerpt/meta passes
// through the same filter must not render (and so consume) any plates.
$GLOBALS['fake_options'] = ['adsense_publisher_id' => 'ca-pub-1', 'ad_slot_in_article' => '1'];
$body = str_repeat('<p>text</p>', 4);
$GLOBALS['fake_in_loop'] = false;
rotation_test_check(mazaq_inject_in_article_ads($body) === $body, 'no plates injected outside the main loop');
$GLOBALS['fake_in_loop'] = true;
rotation_test_check(str_contains(mazaq_inject_in_article_ads($body), 'data-slot-name="ad_slot_in_article"'), 'in-article plates injected in the main loop');

echo "direct-ad-test: {$GLOBALS['rotation_test_passed']} checks passed\n";
