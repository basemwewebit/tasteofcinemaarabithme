<?php

/**
 * Standalone self-check for reCAPTCHA verification: every outcome of
 * TOC_Recaptcha_Verify::verify_token() against canned Enterprise REST answers.
 * No framework, no WP: run with `php tests/recaptcha-verify-test.php`.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

// --- Fake WordPress ----------------------------------------------------------
$GLOBALS['fake_env'] = 'production';
$GLOBALS['fake_options'] = [];
$GLOBALS['fake_response'] = null; // WP_Error, or ['code' => int, 'body' => string]
$GLOBALS['fake_requests'] = 0;

class WP_Error
{
}

function is_wp_error($thing): bool
{
    return $thing instanceof WP_Error;
}

function wp_get_environment_type(): string
{
    return $GLOBALS['fake_env'];
}

function get_option($name, $default = false)
{
    return $GLOBALS['fake_options'][$name] ?? $default;
}

function wp_json_encode($data)
{
    return json_encode($data);
}

function wp_remote_post($url, $args = [])
{
    $GLOBALS['fake_requests']++;

    return $GLOBALS['fake_response'];
}

function wp_remote_retrieve_response_code($response)
{
    return is_array($response) ? $response['code'] : '';
}

function wp_remote_retrieve_body($response): string
{
    return is_array($response) ? $response['body'] : '';
}

ini_set('error_log', '/dev/null');

require __DIR__ . '/../inc/recaptcha/class-recaptcha-verify.php';

// --- Fixtures ----------------------------------------------------------------
function reset_recaptcha(array $options = []): void
{
    $GLOBALS['fake_env'] = 'production';
    $_SERVER['HTTP_HOST'] = 'tasteofcinemaarabi.com';
    $GLOBALS['fake_requests'] = 0;
    $GLOBALS['fake_options'] = $options + [
        'toc_recaptcha_site_key' => 'site-key',
        'toc_recaptcha_project_id' => 'project',
        'toc_recaptcha_secret_key' => 'AIza-api-key',
        'toc_recaptcha_score_threshold' => '0.5',
        'toc_recaptcha_fail_open' => '0',
    ];
    google_answers(assessment());
}

/** An Enterprise assessment as Google's JSON spells it; a 0.0 score is omitted. */
function assessment(array $token = [], ?float $score = 0.9): array
{
    $risk = ['reasons' => []];
    if ($score !== null) {
        $risk = ['score' => $score] + $risk;
    }

    return [
        'tokenProperties' => $token + ['valid' => true, 'action' => 'submit'],
        'riskAnalysis' => $risk,
    ];
}

function google_answers(array $body, int $code = 200): void
{
    $GLOBALS['fake_response'] = ['code' => $code, 'body' => json_encode($body)];
}

function verify(string $token = 'token'): bool
{
    return TOC_Recaptcha_Verify::verify_token($token, 'submit');
}

// --- Checks ------------------------------------------------------------------
reset_recaptcha();
rotation_test_check(verify(), 'a valid, high-score token passes');

reset_recaptcha();
google_answers(['tokenProperties' => ['valid' => true, 'action' => 'submit'], 'riskAnalysis' => ['reasons' => ['AUTOMATION']]]);
rotation_test_check(!verify(), 'a missing score is 0.0 and is rejected');

reset_recaptcha();
google_answers(assessment([], 0.3));
rotation_test_check(!verify(), 'a score below the threshold is rejected');

reset_recaptcha();
google_answers(assessment([], 0.5));
rotation_test_check(verify(), 'a score at the threshold passes');

reset_recaptcha();
google_answers(assessment(['valid' => false, 'invalidReason' => 'EXPIRED']));
rotation_test_check(!verify(), 'an invalid token is rejected');

reset_recaptcha();
google_answers(assessment(['action' => 'login']));
rotation_test_check(!verify(), 'a token minted for another action is rejected');

reset_recaptcha();
rotation_test_check(!verify(''), 'an empty token is rejected');
rotation_test_check($GLOBALS['fake_requests'] === 0, 'an empty token never reaches Google');

// Bypass: only the declared environment decides, never the Host header.
foreach (['local', 'development'] as $env) {
    reset_recaptcha();
    $GLOBALS['fake_env'] = $env;
    google_answers(assessment(['valid' => false]));
    rotation_test_check(verify(''), "bypassed in a {$env} environment");
    rotation_test_check($GLOBALS['fake_requests'] === 0, "a {$env} bypass never reaches Google");
}
foreach (['tasteofcinemaarabi.test', 'localhost', 'evil.local'] as $host) {
    reset_recaptcha();
    $_SERVER['HTTP_HOST'] = $host;
    google_answers(assessment(['valid' => false]));
    rotation_test_check(!verify(), "production is not bypassed by Host: {$host}");
}

// Missing configuration and client errors fail closed, even with fail-open on.
foreach (['0', '1'] as $fail_open) {
    foreach (['toc_recaptcha_site_key', 'toc_recaptcha_project_id', 'toc_recaptcha_secret_key'] as $missing) {
        reset_recaptcha([$missing => '', 'toc_recaptcha_fail_open' => $fail_open]);
        rotation_test_check(!verify(), "missing {$missing} is rejected (fail-open {$fail_open})");
        rotation_test_check($GLOBALS['fake_requests'] === 0, "missing {$missing} never reaches Google");
    }

    reset_recaptcha(['toc_recaptcha_fail_open' => $fail_open]);
    google_answers(['error' => ['code' => 400, 'message' => 'API key not valid']], 400);
    rotation_test_check(!verify(), "a 4xx is rejected (fail-open {$fail_open})");

    reset_recaptcha(['toc_recaptcha_fail_open' => $fail_open]);
    $GLOBALS['fake_response'] = ['code' => 404, 'body' => '<html>Not Found</html>'];
    rotation_test_check(!verify(), "a 4xx without JSON is rejected (fail-open {$fail_open})");

    reset_recaptcha(['toc_recaptcha_fail_open' => $fail_open]);
    google_answers(['error' => ['code' => 403, 'message' => 'denied']]);
    rotation_test_check(!verify(), "an error body is rejected (fail-open {$fail_open})");
}

// Google unreachable: the only case fail-open lets through.
$unreachable = [
    'a network error' => new WP_Error(),
    'a 5xx' => ['code' => 503, 'body' => 'Service Unavailable'],
    'an unreadable body' => ['code' => 200, 'body' => '<html>'],
    'an empty body' => ['code' => 200, 'body' => ''],
];
foreach ($unreachable as $what => $response) {
    reset_recaptcha(['toc_recaptcha_fail_open' => '0']);
    $GLOBALS['fake_response'] = $response;
    rotation_test_check(!verify(), "{$what} is rejected with fail-open off");

    reset_recaptcha(['toc_recaptcha_fail_open' => '1']);
    $GLOBALS['fake_response'] = $response;
    rotation_test_check(verify(), "{$what} passes with fail-open on");
}

echo 'recaptcha-verify-test: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " checks passed\n";
