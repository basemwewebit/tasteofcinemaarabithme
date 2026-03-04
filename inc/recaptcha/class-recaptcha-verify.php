<?php

declare(strict_types=1);

/**
 * Handles server-side validation of Google reCAPTCHA v3 tokens.
 */
class TOC_Recaptcha_Verify
{
    /**
     * Google's site verify API endpoint.
     */
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Validates a given token against the Google API.
     *
     * @param string $token The g-recaptcha-response token.
     * @param string $remote_ip The user's IP.
     * @return bool True if valid or if failed open, false if invalid/spam.
     */
    public static function verify_token(string $token, string $remote_ip = ''): bool
    {
        $secret_key = get_option('toc_recaptcha_secret_key', '');
        
        // Quickstart requirement: if secret key is missing, skip validation (fail open for local testing)
        if (empty($secret_key)) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $threshold_input = get_option('toc_recaptcha_score_threshold', '0.5');
        $threshold = is_numeric($threshold_input) ? (float) $threshold_input : 0.5;

        $response = wp_remote_post(self::VERIFY_URL, [
            'body' => [
                'secret'   => $secret_key,
                'response' => $token,
                'remoteip' => $remote_ip,
            ],
            'timeout' => 3, // 3 seconds timeout max
        ]);

        // Fail-open logic (FR-006) on API timeouts or network errors
        if (is_wp_error($response)) {
            error_log('reCAPTCHA Verify Error: ' . $response->get_error_message() . ' -> Failing open.');
            return true;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('reCAPTCHA Verify HTTP Error: ' . $response_code . ' -> Failing open.');
            return true; // Still fail open if Google returns 500s
        }

        $body_json = wp_remote_retrieve_body($response);
        $result = json_decode($body_json, true);

        if (!is_array($result) || empty($result['success'])) {
            // It could mean the token was completely invalid or expired
            return false; 
        }

        // Validate action if we enforced it (for now acting as a general score check based on v3)
        // Validate score
        if (isset($result['score']) && ((float) $result['score'] < $threshold)) {
            return false; // Classified as a bot based on threshold
        }

        return true;
    }
}
