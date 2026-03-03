<?php

declare(strict_types=1);

function mazaq_handle_contact_form(): void
{
    if ('POST' !== $_SERVER['REQUEST_METHOD']) {
        return;
    }

    if (!isset($_POST['mazaq_contact_submit'])) {
        return;
    }

    $redirect = wp_get_referer() ?: home_url('/contact/');
    $nonce = isset($_POST['mazaq_contact_nonce']) ? sanitize_text_field((string) $_POST['mazaq_contact_nonce']) : '';
    if (!wp_verify_nonce($nonce, 'mazaq_contact_form')) {
        wp_safe_redirect(add_query_arg('contact_status', 'error', $redirect));
        exit;
    }

    $honeypot = isset($_POST['website']) ? sanitize_text_field((string) $_POST['website']) : '';
    if ($honeypot !== '') {
        wp_safe_redirect(add_query_arg('contact_status', 'error', $redirect));
        exit;
    }

    $name = sanitize_text_field((string) ($_POST['name'] ?? ''));
    $email = sanitize_email((string) ($_POST['email'] ?? ''));
    $subject = sanitize_text_field((string) ($_POST['subject'] ?? ''));
    $message = sanitize_textarea_field((string) ($_POST['message'] ?? ''));

    if (!$name || !$email || !$subject || !$message) {
        wp_safe_redirect(add_query_arg('contact_status', 'error', $redirect));
        exit;
    }

    $to = get_option('admin_email');
    $headers = ['Reply-To: ' . $name . ' <' . $email . '>'];
    $body = "الاسم: {$name}\nالبريد: {$email}\n\n{$message}";

    $sent = wp_mail($to, $subject, $body, $headers);
    wp_safe_redirect(add_query_arg('contact_status', $sent ? 'success' : 'error', $redirect));
    exit;
}
add_action('template_redirect', 'mazaq_handle_contact_form');
