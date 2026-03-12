<?php

declare(strict_types=1);

/**
 * Handles WordPress Hooks to inject scripts and protect standard WP Auth forms.
 */
class TOC_Recaptcha_Hooks
{
    public static function init(): void
    {
        // Enqueue Scripts (Frontend & Login Screens)
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']);
        add_action('login_enqueue_scripts', [self::class, 'enqueue_scripts']);

        // Auth Hooks (US2)
        add_filter('authenticate', [self::class, 'verify_login'], 20, 3);
        add_filter('registration_errors', [self::class, 'verify_registration'], 10, 3);
        add_action('lostpassword_post', [self::class, 'verify_lostpassword']);
    }

    public static function enqueue_scripts(): void
    {
        $site_key = get_option('toc_recaptcha_site_key', '');
        
        // Skip loading if no site key is configured
        if (empty($site_key)) {
            return;
        }

        $should_load = false;
        $selectors = [];

        // US2: Login / Register / Lost Password pages
        if (in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'], true)) {
            $should_load = true;
            $selectors[] = '#loginform';
            $selectors[] = '#registerform';
            $selectors[] = '#lostpasswordform';
        }

        // US1: Contact page
        if (is_page_template('page-contact.php')) {
            $should_load = true;
            // Target the form that has the specific submit button inside
            $selectors[] = 'form:has(button[name="mazaq_contact_submit"])';
        }

        if ($should_load) {
            wp_enqueue_script(
                'google-recaptcha-enterprise',
                'https://www.google.com/recaptcha/enterprise.js?render=' . esc_attr($site_key),
                [],
                null,
                true
            );

            wp_enqueue_script(
                'toc-recaptcha-handler',
                get_template_directory_uri() . '/assets/js/recaptcha-handler.js',
                ['google-recaptcha-enterprise'],
                filemtime(get_template_directory() . '/assets/js/recaptcha-handler.js'),
                true
            );

            wp_localize_script('toc-recaptcha-handler', 'tocRecaptchaConfig', [
                'siteKey'   => $site_key,
                'selectors' => $selectors,
            ]);
        }
    }

    /**
     * Verifies reCAPTCHA for the login form.
     */
    public static function verify_login($user, $username, $password)
    {
        // If it's already an error, or it's a cookie auth/xmlrpc, skip
        if (is_wp_error($user) || empty($_POST)) {
            return $user;
        }

        $token = $_POST['g-recaptcha-response'] ?? '';
        
        if (!TOC_Recaptcha_Verify::verify_token($token, 'submit')) {
            return new WP_Error('recaptcha_failed', __('<strong>ERROR</strong>: reCAPTCHA verification failed. You may be a bot.', 'tasteofcinemaarabithme'));
        }

        return $user;
    }

    /**
     * Verifies reCAPTCHA for the registration form.
     */
    public static function verify_registration($errors, $sanitized_user_login, $user_email)
    {
        $token = $_POST['g-recaptcha-response'] ?? '';
        
        if (!TOC_Recaptcha_Verify::verify_token($token, 'submit')) {
            $errors->add('recaptcha_failed', __('<strong>ERROR</strong>: reCAPTCHA verification failed.', 'tasteofcinemaarabithme'));
        }

        return $errors;
    }

    /**
     * Verifies reCAPTCHA for the lost password form.
     */
    public static function verify_lostpassword(): void
    {
        if (isset($_POST['user_login'])) {
            $token = $_POST['g-recaptcha-response'] ?? '';
            
            if (!TOC_Recaptcha_Verify::verify_token($token, 'submit')) {
                wp_die(__('<strong>ERROR</strong>: reCAPTCHA verification failed.', 'tasteofcinemaarabithme'));
            }
        }
    }
}

TOC_Recaptcha_Hooks::init();
