<?php

declare(strict_types=1);

function mazaq_ga4_tracking_script(): void
{
    if (!function_exists('get_field')) {
        return;
    }

    $measurement_id = (string) get_field('ga4_measurement_id', 'option');
    if (!$measurement_id) {
        return;
    }

    $id = esc_attr($measurement_id);
    echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$id}\"></script>";
    echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', '{$id}');</script>";
}
add_action('wp_head', 'mazaq_ga4_tracking_script');
