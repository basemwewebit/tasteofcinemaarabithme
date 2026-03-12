<?php

declare(strict_types=1);

use Google\Cloud\RecaptchaEnterprise\V1\Client\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\CreateAssessmentRequest;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

/**
 * Handles server-side validation of Google reCAPTCHA Enterprise tokens.
 */
class TOC_Recaptcha_Verify
{
    /**
     * Validates a given token against the Google Cloud Recaptcha Enterprise API.
     *
     * @param string $token The g-recaptcha-response token.
     * @param string $action The action to verify against.
     * @return bool True if valid or if failed open, false if invalid/spam.
     */
    public static function verify_token(string $token, string $action = 'submit'): bool
    {
        $site_key = get_option('toc_recaptcha_site_key', '');
        $project_id = get_option('toc_recaptcha_project_id', '');
        
        // Quickstart requirement: if configuration is missing, skip validation (fail open for local testing)
        if (empty($site_key) || empty($project_id)) {
            return true; // Fail open
        }

        if (empty($token)) {
            return false;
        }

        $threshold_input = get_option('toc_recaptcha_score_threshold', '0.5');
        $threshold = is_numeric($threshold_input) ? (float) $threshold_input : 0.5;

        try {
            $api_key = get_option('toc_recaptcha_secret_key', ''); // This field is now labeled "Secret Key/API Key"
            
            // Try to use the Google Cloud PHP Client if credentials exist
            $use_grpc = getenv('GOOGLE_APPLICATION_CREDENTIALS') !== false;

            if ($use_grpc) {
                $client = new RecaptchaEnterpriseServiceClient();
                $projectName = $client->projectName($project_id);

                $event = (new Event())
                    ->setSiteKey($site_key)
                    ->setToken($token);

                $assessment = (new Assessment())
                    ->setEvent($event);

                $request = (new CreateAssessmentRequest())
                    ->setParent($projectName)
                    ->setAssessment($assessment);

                $response = $client->createAssessment($request);
                
                $is_valid = $response->getTokenProperties()->getValid();
                $invalid_reason = $is_valid ? '' : InvalidReason::name($response->getTokenProperties()->getInvalidReason());
                $response_action = $response->getTokenProperties()->getAction();
                $score = $response->getRiskAnalysis() ? $response->getRiskAnalysis()->getScore() : 0.0;

            } else {
                // Fallback to REST API passing the API Key or Legacy Secret
                if (empty($api_key)) {
                    error_log('reCAPTCHA Verify Error: Missing API Key for REST fallback. Failing open.');
                    return true;
                }

                // If it looks like a legacy secret key (starts with 6L), use the siteverify endpoint
                if (strpos($api_key, '6L') === 0 || strpos($api_key, '6l') === 0) {
                    $url = 'https://www.google.com/recaptcha/api/siteverify';
                    $body = [
                        'secret' => $api_key,
                        'response' => $token
                    ];

                    $response = wp_remote_post($url, [
                        'body' => $body,
                        'timeout' => 5
                    ]);

                    if (is_wp_error($response)) {
                        error_log('reCAPTCHA Verify Error: ' . $response->get_error_message() . ' -> Failing open.');
                        return true;
                    }

                    $body_json = wp_remote_retrieve_body($response);
                    $result = json_decode($body_json, true);

                    if (empty($result)) {
                        error_log('reCAPTCHA Verify Legacy REST Error: Empty response -> Failing open.');
                        return true;
                    }

                    $is_valid = !empty($result['success']) && $result['success'] === true;
                    $invalid_reason = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : '';
                    $response_action = $result['action'] ?? '';
                    $score = $result['score'] ?? 0.0;

                } else {
                    // It's a Google Cloud API Key (starts with AIza)
                    $url = "https://recaptchaenterprise.googleapis.com/v1/projects/{$project_id}/assessments?key={$api_key}";
                    $body = [
                        'event' => [
                            'token' => $token,
                            'siteKey' => $site_key,
                            'expectedAction' => $action
                        ]
                    ];

                    $response = wp_remote_post($url, [
                        'body' => wp_json_encode($body),
                        'headers' => ['Content-Type' => 'application/json'],
                        'timeout' => 5
                    ]);

                    if (is_wp_error($response)) {
                        error_log('reCAPTCHA Verify Error: ' . $response->get_error_message() . ' -> Failing open.');
                        return true;
                    }

                    $body_json = wp_remote_retrieve_body($response);
                    $result = json_decode($body_json, true);

                    if (empty($result) || isset($result['error'])) {
                        error_log('reCAPTCHA Verify REST Error: ' . ($result['error']['message'] ?? 'Unknown Error') . ' -> Failing open.');
                        return true;
                    }

                    $is_valid = $result['tokenProperties']['valid'] ?? false;
                    $invalid_reason = $result['tokenProperties']['invalidReason'] ?? '';
                    $response_action = $result['tokenProperties']['action'] ?? '';
                    $score = current($result['riskAnalysis'] ?? [0.0]); // API returns score inside riskAnalysis object
                }
            }

            // Check if the token is valid.
            if (!$is_valid) {
                error_log("reCAPTCHA Verify Error: Token invalid. Reason: {$invalid_reason}");
                return false;
            }

            // Check if the expected action was executed.
            if ($response_action !== $action) {
                error_log("reCAPTCHA Verify Error: Action mismatch. Expected: {$action}, Got: {$response_action}");
                return false;
            }

            if ($score < $threshold) {
                error_log("reCAPTCHA Verify failed score. Score is: {$score}");
                return false; // Spam/bot detected
            }

        } catch (\Exception $e) {
            error_log('reCAPTCHA Verify Exception: ' . $e->getMessage() . ' -> Failing open.');
            return true; // Fail open on API failure
        } finally {
            if (isset($client)) {
                $client->close();
            }
        }

        return true;
    }
}
