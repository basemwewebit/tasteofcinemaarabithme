<?php
// Add noindex to author pages to prevent indexing of redirect pages
add_action('wp_head', function() {
    echo '<meta name="robots" content="noindex, follow">' . "\n";
});

// Redirect author pages to home
wp_redirect(home_url());
exit;
