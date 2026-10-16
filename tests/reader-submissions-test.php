<?php

/**
 * Standalone self-check for the Reader intake: every Reader submission's
 * checks, rate limit and storage, through mazaq_reader_submit(). No framework,
 * no WP: run with `php tests/reader-submissions-test.php`.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

const HOUR_IN_SECONDS = 3600;

// --- Fake WordPress ----------------------------------------------------------
$GLOBALS['fake_transients'] = [];
$GLOBALS['fake_posts'] = [];      // id => ['post' => array, 'meta' => array]
$GLOBALS['fake_mail_ok'] = true;
$GLOBALS['fake_insert_ok'] = true;
$GLOBALS['fake_mails'] = 0;
$GLOBALS['fake_captcha_ok'] = true;
$GLOBALS['fake_captcha_calls'] = 0;

function get_transient($key)
{
    return $GLOBALS['fake_transients'][$key] ?? false;
}

function set_transient($key, $value, $expiration = 0): bool
{
    $GLOBALS['fake_transients'][$key] = $value;

    return true;
}

/** A valid nonce for $action reads "valid-$action". */
function wp_verify_nonce($nonce, $action)
{
    return $nonce === 'valid-' . $action ? 1 : false;
}

function sanitize_text_field($s): string
{
    return trim(preg_replace('/[\r\n\t]+/', ' ', (string) $s));
}

function sanitize_textarea_field($s): string
{
    return trim((string) $s);
}

function sanitize_email($s): string
{
    $s = trim((string) $s);

    return filter_var($s, FILTER_VALIDATE_EMAIL) ? $s : '';
}

function is_email($s)
{
    return filter_var($s, FILTER_VALIDATE_EMAIL) ? $s : false;
}

function wp_strip_all_tags($s): string
{
    return strip_tags((string) $s);
}

function wp_kses_post($s): string
{
    return (string) $s;
}

function __($text, $domain = 'default'): string
{
    return $text;
}

function get_option($name, $default = false)
{
    return $name === 'admin_email' ? 'editors@example.com' : $default;
}

function add_action(string $hook, $callback, int $priority = 10, int $args = 1): void
{
}

function wp_mail($to, $subject, $body, $headers = []): bool
{
    $GLOBALS['fake_mails']++;

    return $GLOBALS['fake_mail_ok'];
}

class WP_Error
{
}

function is_wp_error($thing): bool
{
    return $thing instanceof WP_Error;
}

function wp_insert_post(array $post, bool $wp_error = false)
{
    if (!$GLOBALS['fake_insert_ok']) {
        return new WP_Error();
    }
    $id = count($GLOBALS['fake_posts']) + 1;
    $GLOBALS['fake_posts'][$id] = ['post' => $post, 'meta' => []];

    return $id;
}

function update_post_meta($id, $key, $value): bool
{
    $GLOBALS['fake_posts'][$id]['meta'][$key] = $value;

    return true;
}

/** Stored posts matching the first meta_query clause. */
class WP_Query
{
    private $found = [];

    public function __construct(array $args = [])
    {
        $clause = $args['meta_query'][0];
        foreach ($GLOBALS['fake_posts'] as $id => $p) {
            if (($p['meta'][$clause['key']] ?? null) === $clause['value']) {
                $this->found[] = $id;
            }
        }
    }

    public function have_posts(): bool
    {
        return $this->found !== [];
    }
}

class TOC_Recaptcha_Verify
{
    public static function verify_token(string $token, string $action = 'submit'): bool
    {
        $GLOBALS['fake_captcha_calls']++;

        return $GLOBALS['fake_captcha_ok'];
    }
}

require __DIR__ . '/../inc/reader-submissions.php';

function reset_intake(): void
{
    $GLOBALS['fake_transients'] = [];
    $GLOBALS['fake_posts'] = [];
    $GLOBALS['fake_mail_ok'] = true;
    $GLOBALS['fake_insert_ok'] = true;
    $GLOBALS['fake_mails'] = 0;
    $GLOBALS['fake_captcha_ok'] = true;
    $GLOBALS['fake_captcha_calls'] = 0;
    $_SERVER['REMOTE_ADDR'] = '203.0.113.7';
}

function contact_input(array $overrides = []): array
{
    return $overrides + [
        'mazaq_reader_nonce' => 'valid-mazaq_reader_contact',
        'website' => '',
        'g-recaptcha-response' => 'token',
        'name' => 'Layla',
        'email' => 'layla@example.com',
        'subject' => 'Hello',
        'message' => 'A note for the editors.',
    ];
}

// A good contact message: mailed, stored with its sender.
reset_intake();
rotation_test_check(mazaq_reader_submit('contact', contact_input()) === 'success', 'contact: success');
rotation_test_check($GLOBALS['fake_mails'] === 1, 'contact: mailed to the editors');
$stored = $GLOBALS['fake_posts'][1] ?? null;
rotation_test_check($stored !== null
    && $stored['post']['post_type'] === 'contact_message'
    && $stored['post']['post_status'] === 'publish'
    && $stored['post']['post_title'] === 'Hello'
    && $stored['meta'] === ['_contact_name' => 'Layla', '_contact_email' => 'layla@example.com'], 'contact: stored as a published contact message');

// Mail or store is enough; both failing is a failure.
reset_intake();
$GLOBALS['fake_mail_ok'] = false;
rotation_test_check(mazaq_reader_submit('contact', contact_input()) === 'success', 'contact: stored but not mailed is a success');
reset_intake();
$GLOBALS['fake_insert_ok'] = false;
rotation_test_check(mazaq_reader_submit('contact', contact_input()) === 'success', 'contact: mailed but not stored is a success');
$GLOBALS['fake_mail_ok'] = false;
rotation_test_check(mazaq_reader_submit('contact', contact_input()) === 'failed', 'contact: neither mailed nor stored is failed');

// Rejected: bad nonce, a filled honeypot, failed reCAPTCHA; nothing stored or mailed.
reset_intake();
rotation_test_check(mazaq_reader_submit('contact', contact_input(['mazaq_reader_nonce' => 'stale'])) === 'rejected', 'contact: bad nonce rejected');
rotation_test_check(mazaq_reader_submit('contact', contact_input(['mazaq_reader_nonce' => 'valid-mazaq_reader_newsletter'])) === 'rejected', 'contact: another form\'s nonce rejected');
rotation_test_check(mazaq_reader_submit('contact', contact_input(['website' => 'http://spam.example'])) === 'rejected', 'contact: honeypot rejected');
$GLOBALS['fake_captcha_ok'] = false;
rotation_test_check(mazaq_reader_submit('contact', contact_input()) === 'rejected', 'contact: failed reCAPTCHA rejected');
rotation_test_check($GLOBALS['fake_posts'] === [] && $GLOBALS['fake_mails'] === 0, 'contact: a rejection stores and mails nothing');

// Invalid: an empty field or a bad email.
reset_intake();
rotation_test_check(mazaq_reader_submit('contact', contact_input(['subject' => '  '])) === 'invalid', 'contact: empty subject invalid');
rotation_test_check(mazaq_reader_submit('contact', contact_input(['email' => 'not-an-email'])) === 'invalid', 'contact: bad email invalid');

// Rate limit: five attempts an hour per IP, failed reCAPTCHA attempts included.
reset_intake();
$GLOBALS['fake_captcha_ok'] = false;
for ($i = 0; $i < 5; $i++) {
    mazaq_reader_submit('contact', contact_input());
}
$GLOBALS['fake_captcha_ok'] = true;
rotation_test_check(mazaq_reader_submit('contact', contact_input()) === 'rate_limited', 'contact: 6th attempt rate limited, failed reCAPTCHA counted');
rotation_test_check($GLOBALS['fake_captcha_calls'] === 5, 'contact: a rate-limited attempt costs no reCAPTCHA call');
$_SERVER['REMOTE_ADDR'] = '198.51.100.9';
rotation_test_check(mazaq_reader_submit('contact', contact_input()) === 'success', 'contact: another IP has its own count');

// A rejected nonce or honeypot is not counted.
reset_intake();
for ($i = 0; $i < 5; $i++) {
    mazaq_reader_submit('contact', contact_input(['website' => 'x']));
}
rotation_test_check(mazaq_reader_submit('contact', contact_input()) === 'success', 'contact: honeypot hits are not counted');

echo 'reader-submissions-test: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " checks passed\n";
