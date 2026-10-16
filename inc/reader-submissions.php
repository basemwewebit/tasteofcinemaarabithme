<?php

declare(strict_types=1);

/**
 * Reader intake: every Reader submission passes the same checks (nonce,
 * honeypot, per-form rate limit; reCAPTCHA for contact messages) before it is
 * stored. Each form's handler only translates the returned status.
 *
 * @package Mazaq
 */

const MAZAQ_READER_HOURLY_LIMIT = 5;

/**
 * Check and store one Reader submission.
 *
 * @param string $form 'contact' or 'newsletter'
 * @param array $input The submitted fields, unslashed.
 * @return string success | duplicate (newsletter: already signed up) | invalid | rate_limited | rejected
 *                | failed (not stored, and a contact message not mailed either)
 */
function mazaq_reader_submit(string $form, array $input): string
{
    $nonce = (string) ($input['mazaq_reader_nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'mazaq_reader_' . $form) || (string) ($input['website'] ?? '') !== '') {
        return 'rejected';
    }

    // Every attempt past the nonce counts, so a bot failing reCAPTCHA is throttled too.
    // ponytail: REMOTE_ADDR is the reader only when the edge restores the real IP
    // (ADR 0001); otherwise everyone behind the CDN shares one counter.
    $key = 'mazaq_reader_limit_' . $form . '_' . md5((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $attempts = (int) get_transient($key);
    if ($attempts >= MAZAQ_READER_HOURLY_LIMIT) {
        return 'rate_limited';
    }
    set_transient($key, $attempts + 1, HOUR_IN_SECONDS);

    if ($form === 'newsletter') {
        return mazaq_reader_store_signup($input);
    }

    // ponytail: one reCAPTCHA provider, called directly; add a seam if a second arrives.
    $token = (string) ($input['g-recaptcha-response'] ?? '');
    if (!class_exists('TOC_Recaptcha_Verify') || !TOC_Recaptcha_Verify::verify_token($token)) {
        return 'rejected';
    }

    return mazaq_reader_store_contact($input);
}

/** Store a newsletter signup once per email, matched by its hash. */
function mazaq_reader_store_signup(array $input): string
{
    $email = sanitize_email((string) ($input['email'] ?? ''));
    if ($email === '' || !is_email($email)) {
        return 'invalid';
    }

    $hash = hash('sha256', strtolower($email));
    $existing = new WP_Query([
        'post_type' => 'contact_message',
        'post_status' => 'private',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => [['key' => '_newsletter_email_hash', 'value' => $hash]],
    ]);
    if ($existing->have_posts()) {
        return 'duplicate';
    }

    $post_id = wp_insert_post([
        'post_type' => 'contact_message',
        'post_status' => 'private',
        'post_title' => sprintf(__('اشتراك نشرة: %s', 'mazaq'), $email),
        'post_content' => __('طلب اشتراك في النشرة السينمائية.', 'mazaq'),
    ], true);
    if (is_wp_error($post_id) || !$post_id) {
        return 'failed';
    }

    update_post_meta($post_id, '_contact_email', $email);
    update_post_meta($post_id, '_contact_name', __('مشترك النشرة', 'mazaq'));
    update_post_meta($post_id, '_newsletter_email_hash', $hash);

    return 'success';
}

/** Mail a contact message to the editors and store it; either is enough. */
function mazaq_reader_store_contact(array $input): string
{
    // sanitize_text_field strips newlines, so the name can't inject a mail header.
    $name = sanitize_text_field((string) ($input['name'] ?? ''));
    $email = sanitize_email((string) ($input['email'] ?? ''));
    $subject = sanitize_text_field((string) ($input['subject'] ?? ''));
    $message = sanitize_textarea_field((string) ($input['message'] ?? ''));
    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        return 'invalid';
    }

    $sent = wp_mail(
        get_option('admin_email'),
        $subject,
        "الاسم: {$name}\nالبريد: {$email}\n\n{$message}",
        ['Reply-To: ' . $name . ' <' . $email . '>']
    );

    $post_id = wp_insert_post([
        'post_title' => wp_strip_all_tags($subject),
        'post_content' => wp_kses_post($message),
        'post_status' => 'publish',
        'post_type' => 'contact_message',
    ], true);
    $saved = !is_wp_error($post_id) && $post_id;
    if ($saved) {
        update_post_meta($post_id, '_contact_name', $name);
        update_post_meta($post_id, '_contact_email', $email);
    }

    return ($sent || $saved) ? 'success' : 'failed';
}

/**
 * Print a form's hidden intake fields. A contact form is also marked for
 * reCAPTCHA, and its scripts load with it, wherever the form is rendered.
 */
function mazaq_reader_submission_fields(string $form): void
{
    wp_nonce_field('mazaq_reader_' . $form, 'mazaq_reader_nonce');
    printf('<input type="hidden" name="mazaq_reader_form" value="%s">', esc_attr($form));
    echo '<input type="hidden" name="website" value="">';

    if ($form === 'contact') {
        echo '<input type="hidden" data-reader-captcha>';
        if (class_exists('TOC_Recaptcha_Hooks')) {
            TOC_Recaptcha_Hooks::load(['[data-reader-captcha]']);
        }
    }
}

function mazaq_handle_contact_form(): void
{
    if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '') || ($_POST['mazaq_reader_form'] ?? '') !== 'contact') {
        return;
    }

    $status = mazaq_reader_submit('contact', wp_unslash($_POST));
    $contact_status = ['success' => 'success', 'rate_limited' => 'rate_limit'][$status] ?? 'error';
    wp_safe_redirect(add_query_arg('contact_status', $contact_status, wp_get_referer() ?: home_url('/contact-us/')));
    exit;
}
add_action('template_redirect', 'mazaq_handle_contact_form');

function mazaq_ajax_newsletter_signup(): void
{
    $could_not = __('تعذر تسجيل الاشتراك الآن. حاول لاحقًا.', 'mazaq');
    $replies = [
        'success' => [200, __('تم الاشتراك بنجاح. أهلاً بك في النشرة.', 'mazaq')],
        'duplicate' => [200, __('أنت مشترك بالفعل. شكرًا لاهتمامك.', 'mazaq')],
        'invalid' => [400, __('أدخل بريدًا إلكترونيًا صحيحًا.', 'mazaq')],
        'rate_limited' => [429, ''], // the newsletter script shows its own "too many requests" text
        'rejected' => [403, $could_not],
        'failed' => [500, $could_not],
    ];
    [$code, $message] = $replies[mazaq_reader_submit('newsletter', wp_unslash($_POST))];

    if ($code === 200) {
        wp_send_json_success(['message' => $message]);
    }
    wp_send_json_error($message === '' ? null : ['message' => $message], $code);
}
add_action('wp_ajax_mazaq_newsletter_signup', 'mazaq_ajax_newsletter_signup');
add_action('wp_ajax_nopriv_mazaq_newsletter_signup', 'mazaq_ajax_newsletter_signup');
