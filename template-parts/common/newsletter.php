<?php

declare(strict_types=1);

$args = wp_parse_args(
    $args ?? [],
    [
        'context' => 'site',
    ]
);

$context = sanitize_key((string) ($args['context'] ?? 'site')) ?: 'site';
$panel_id = 'newsletter-panel-' . $context;
$title_id = $panel_id . '-title';
$summary_id = $panel_id . '-summary';
$email_id = $panel_id . '-email';
$status_id = $panel_id . '-status';
?>
<section
    id="<?php echo esc_attr($panel_id); ?>"
    class="newsletter-panel newsletter-panel--<?php echo esc_attr($context); ?>"
    aria-labelledby="<?php echo esc_attr($title_id); ?>"
>
    <div class="newsletter-panel__copy">
        <span class="newsletter-panel__mark" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" focusable="false">
                <rect x="10" y="7.5" width="28" height="33" rx="3"></rect>
                <path stroke-linecap="round" d="M10 17h28M10 31h28"></path>
                <circle cx="16" cy="12.25" r="1.8" fill="currentColor" stroke="none"></circle>
                <circle cx="32" cy="12.25" r="1.8" fill="currentColor" stroke="none"></circle>
                <circle cx="16" cy="35.75" r="1.8" fill="currentColor" stroke="none"></circle>
                <circle cx="32" cy="35.75" r="1.8" fill="currentColor" stroke="none"></circle>
            </svg>
        </span>
        <h2 id="<?php echo esc_attr($title_id); ?>" class="newsletter-panel__title"><?php esc_html_e('اختيارات تحريرية تصل إلى بريدك', 'mazaq'); ?></h2>
        <p id="<?php echo esc_attr($summary_id); ?>" class="newsletter-panel__summary"><?php esc_html_e('رسالة خفيفة عند صدور مقالات مميزة، بدون ضجيج أو رسائل متكررة.', 'mazaq'); ?></p>
    </div>

    <form
        class="newsletter-panel__form"
        data-newsletter-form
        aria-describedby="<?php echo esc_attr($summary_id); ?>"
        aria-busy="false"
    >
        <div class="newsletter-panel__field">
            <label for="<?php echo esc_attr($email_id); ?>" class="newsletter-panel__label">
                <?php esc_html_e('البريد الإلكتروني', 'mazaq'); ?>
            </label>
            <div class="newsletter-panel__controls">
                <input
                    id="<?php echo esc_attr($email_id); ?>"
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    inputmode="email"
                    maxlength="254"
                    spellcheck="false"
                    placeholder="<?php esc_attr_e('you@example.com', 'mazaq'); ?>"
                    aria-describedby="<?php echo esc_attr($status_id); ?>"
                    aria-invalid="false"
                    dir="ltr"
                >
                <button type="submit">
                    <span><?php esc_html_e('اشترك', 'mazaq'); ?></span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"></path>
                    </svg>
                </button>
            </div>
        </div>

        <p
            id="<?php echo esc_attr($status_id); ?>"
            class="newsletter-panel__status"
            data-newsletter-status
            role="status"
            aria-live="polite"
            aria-atomic="true"
        ></p>
    </form>
</section>
