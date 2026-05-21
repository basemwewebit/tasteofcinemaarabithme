<?php

declare(strict_types=1);

$args = wp_parse_args(
    $args ?? [],
    [
        'context' => 'site',
    ]
);
?>
<section class="newsletter-panel newsletter-panel--<?php echo esc_attr((string) $args['context']); ?>" aria-labelledby="newsletter-panel-title">
    <div>
        <p class="newsletter-panel__kicker"><?php esc_html_e('النشرة السينمائية', 'mazaq'); ?></p>
        <h2 id="newsletter-panel-title" class="newsletter-panel__title"><?php esc_html_e('اختيارات تحريرية تصل إلى بريدك', 'mazaq'); ?></h2>
        <p class="newsletter-panel__summary"><?php esc_html_e('رسالة خفيفة عند صدور مقالات مميزة، بدون ضجيج أو رسائل متكررة.', 'mazaq'); ?></p>
    </div>
    <form class="newsletter-panel__form" data-newsletter-form>
        <label for="newsletter-email-<?php echo esc_attr((string) $args['context']); ?>" class="sr-only"><?php esc_html_e('البريد الإلكتروني', 'mazaq'); ?></label>
        <input id="newsletter-email-<?php echo esc_attr((string) $args['context']); ?>" type="email" name="email" required autocomplete="email" placeholder="<?php esc_attr_e('you@example.com', 'mazaq'); ?>" dir="ltr">
        <button type="submit"><?php esc_html_e('اشترك', 'mazaq'); ?></button>
        <p class="newsletter-panel__status" data-newsletter-status aria-live="polite"></p>
    </form>
</section>
