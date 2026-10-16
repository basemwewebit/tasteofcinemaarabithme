<?php

declare(strict_types=1);

/**
 * Server-side validation of Google reCAPTCHA Enterprise tokens, through the
 * Enterprise REST API only (see docs/adr/0002).
 */
class TOC_Recaptcha_Verify
{
    /**
     * reCAPTCHA is skipped only where WordPress itself declares a local or
     * development environment. Never decided from the request's Host header.
     */
    public static function should_bypass_recaptcha(): bool
    {
        return in_array(wp_get_environment_type(), ['local', 'development'], true);
    }

    /**
     * Validates a token against the reCAPTCHA Enterprise REST API.
     *
     * Fails closed on missing configuration, a client error from Google, an
     * invalid token, a different action, or a score below the threshold (a
     * missing score is 0.0).
     *
     * @param string $token The g-recaptcha-response token.
     * @param string $action The action the token must have been minted for.
     */
    public static function verify_token(string $token, string $action = 'submit'): bool
    {
        if (self::should_bypass_recaptcha()) {
            return true;
        }

        $site_key = (string) get_option('toc_recaptcha_site_key', '');
        $project_id = (string) get_option('toc_recaptcha_project_id', '');
        $api_key = (string) get_option('toc_recaptcha_secret_key', ''); // stored under its old "secret key" name

        if ($site_key === '' || $project_id === '' || $api_key === '') {
            error_log('reCAPTCHA: missing credentials, rejected');
            return false;
        }

        if ($token === '') {
            return false;
        }

        $response = wp_remote_post(
            'https://recaptchaenterprise.googleapis.com/v1/projects/' . rawurlencode($project_id) . '/assessments?key=' . rawurlencode($api_key),
            [
                'body' => wp_json_encode([
                    'event' => [
                        'token' => $token,
                        'siteKey' => $site_key,
                        'expectedAction' => $action,
                    ],
                ]),
                'headers' => ['Content-Type' => 'application/json'],
                'timeout' => 5,
            ]
        );

        $code = (int) wp_remote_retrieve_response_code($response);
        $result = is_wp_error($response) ? null : json_decode(wp_remote_retrieve_body($response), true);

        $ok = $code >= 200 && $code < 300;

        // Unreachable: a network error, a 5xx, or a 2xx whose body isn't JSON.
        // The only case fail-open lets through.
        if (is_wp_error($response) || $code >= 500 || ($ok && !is_array($result))) {
            $fail_open = get_option('toc_recaptcha_fail_open', '0') === '1';
            error_log($fail_open ? 'reCAPTCHA: Google unreachable, failed open' : 'reCAPTCHA: Google unreachable, rejected');
            return $fail_open;
        }

        // Anything else that isn't a clean 2xx: a client or configuration error
        // (bad API key, wrong project), a redirect. Always closed.
        if (!$ok || !is_array($result) || isset($result['error'])) {
            error_log('reCAPTCHA: Google client error, rejected');
            return false;
        }

        if (($result['tokenProperties']['valid'] ?? false) !== true) {
            error_log('reCAPTCHA: invalid token, rejected');
            return false;
        }

        if (($result['tokenProperties']['action'] ?? '') !== $action) {
            error_log('reCAPTCHA: action mismatch, rejected');
            return false;
        }

        $threshold_input = get_option('toc_recaptcha_score_threshold', '0.5');
        $threshold = is_numeric($threshold_input) ? (float) $threshold_input : 0.5;
        // Google's JSON omits a 0.0 score.
        $score = $result['riskAnalysis']['score'] ?? 0.0;
        $score = is_numeric($score) ? (float) $score : 0.0;

        if ($score < $threshold) {
            error_log('reCAPTCHA: score below threshold, rejected');
            return false;
        }

        return true;
    }
}
