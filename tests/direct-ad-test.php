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
function get_template_part(string $slug): void { echo '<!--plate-->'; }
function esc_url(string $url): string { return $url; }
function esc_attr(string $text): string { return htmlspecialchars($text, ENT_QUOTES); }
function esc_attr__(string $text, string $domain = ''): string { return esc_attr($text); }
function esc_html__(string $text, string $domain = ''): string { return $text; }

function wp_get_attachment_image_url(int $id, string $size)
{
    return $id === 7 ? "https://cdn.test/ad-{$size}.gif" : false;
}

require __DIR__ . '/../inc/ads.php';

function render(string $slot, string $format = 'horizontal'): string
{
    ob_start();
    mazaq_render_ad($slot, $format);
    return (string) ob_get_clean();
}

// Complete pair: linked image, safe rel, full-size GIF, no network markup.
$GLOBALS['fake_options'] = ['a_direct_url' => 'https://example.com/?x=1', 'a_direct_image' => 7];
$html = render('a');
rotation_test_check(str_contains($html, '<a href="https://example.com/?x=1" target="_blank" rel="sponsored noopener noreferrer"><img src="https://cdn.test/ad-full.gif" alt="إعلان" loading="lazy"></a>'), 'complete pair renders the linked image');
rotation_test_check(!str_contains($html, 'adsbygoogle') && str_contains($html, 'data-expects-network-ad="0"'), 'Direct ad is not a network ad');

// Same placement again in this request: nothing (no network ad configured).
// A different placement in the same request still shows its own Direct ad.
$GLOBALS['fake_options'] += ['k_direct_url' => 'https://example.org', 'k_direct_image' => 7];
rotation_test_check(str_contains(render('k'), 'https://example.org'), 'another placement shows its own Direct ad');
rotation_test_check(render('a') === '', 'second render of the placement shows no repeat');

// Repeat falls back to the Network ad when one is configured.
$GLOBALS['fake_options'] = ['b_direct_url' => 'https://example.com', 'b_direct_image' => 7, 'b' => '123', 'adsense_publisher_id' => 'ca-pub-1'];
$first = render('b');
rotation_test_check(str_contains($first, 'https://example.com') && !str_contains($first, 'adsbygoogle'), 'Direct ad beats the Network ad');
rotation_test_check(str_contains(render('b'), 'adsbygoogle'), 'repeat falls back to the Network ad');

// Incomplete or unsafe: nothing.
$GLOBALS['fake_options'] = ['c_direct_url' => 'https://example.com'];
rotation_test_check(render('c') === '', 'URL without image renders nothing');
$GLOBALS['fake_options'] = ['d_direct_image' => 7];
rotation_test_check(render('d') === '', 'image without URL renders nothing');
$GLOBALS['fake_options'] = ['e_direct_url' => 'javascript:alert(1)', 'e_direct_image' => 7];
rotation_test_check(render('e') === '', 'non-http(s) URL renders nothing');
foreach (['g' => 'example.com', 'h' => '//evil.test/x', 'i' => '/relative', 'j' => 'ftp://example.com'] as $slot => $bad) {
    $GLOBALS['fake_options'] = ["{$slot}_direct_url" => $bad, "{$slot}_direct_image" => 7];
    rotation_test_check(render($slot) === '', "'{$bad}' is not an http(s) URL and renders nothing");
}
$GLOBALS['fake_options'] = ['f_direct_url' => 'https://example.com', 'f_direct_image' => 99];
rotation_test_check(render('f') === '', 'missing attachment renders nothing');

// The article-body injection runs only in the main loop: excerpt/meta passes
// through the same filter must not render (and so consume) any plates.
$body = str_repeat('<p>text</p>', 4);
$GLOBALS['fake_in_loop'] = false;
rotation_test_check(mazaq_inject_in_article_ads($body) === $body, 'no plates injected outside the main loop');
$GLOBALS['fake_in_loop'] = true;
rotation_test_check(str_contains(mazaq_inject_in_article_ads($body), '<!--plate-->'), 'plates injected in the main loop');

echo "direct-ad-test: {$GLOBALS['rotation_test_passed']} checks passed\n";
