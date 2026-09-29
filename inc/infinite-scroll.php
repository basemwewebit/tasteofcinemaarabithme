<?php

declare(strict_types=1);

/**
 * Load-more endpoint: request handling only. What a page of the Latest feed
 * contains is decided by mazaq_latest_feed().
 */
function mazaq_ajax_load_more_posts(): void
{
    check_ajax_referer('mazaq_load_more_nonce', 'nonce');

    $page = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;

    // Force the site locale for AJAX responses so that formatted dates are not in English.
    switch_to_locale(get_locale());
    $feed = mazaq_latest_feed($page);
    restore_current_locale();

    wp_send_json_success($feed);
}

add_action('wp_ajax_nopriv_load_more_posts', 'mazaq_ajax_load_more_posts');
add_action('wp_ajax_load_more_posts', 'mazaq_ajax_load_more_posts');
