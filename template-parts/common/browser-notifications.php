<?php

declare(strict_types=1);

$args = wp_parse_args(
    $args ?? [],
    [
        'settings' => [],
    ]
);

$settings = is_array($args['settings']) ? $args['settings'] : [];
$default_prompt_title = __('اشترك في تنبيهات مذاق السينما', 'mazaq');
$default_prompt_body = __('سنرسل لك مقالاً يومياً مختاراً ومقالات جديدة فور نشرها.', 'mazaq');
$prompt_title = trim((string) ($settings['prompt_title'] ?? '')) ?: $default_prompt_title;
$prompt_body = trim((string) ($settings['prompt_body'] ?? '')) ?: $default_prompt_body;
?>
<div
    id="mazaq-notification-root"
    class="mazaq-notification-suite"
    dir="rtl"
>
    <section
        id="mazaq-notification-prompt"
        class="mazaq-notification mazaq-notification--prompt hidden"
        aria-labelledby="mazaq-notification-prompt-title"
        aria-describedby="mazaq-notification-prompt-body"
        aria-live="polite"
        aria-busy="false"
    >
        <div class="mazaq-notification__topline">
            <span class="mazaq-notification__emblem" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 27.5V20a8 8 0 0 1 16 0v7.5l2.5 3H13.5l2.5-3Z"></path>
                    <path stroke-linecap="round" d="M21.5 34a3 3 0 0 0 5 0"></path>
                </svg>
            </span>
            <button
                id="mazaq-notification-prompt-close"
                type="button"
                class="mazaq-notification__close"
                aria-label="<?php esc_attr_e('إغلاق طلب الاشتراك', 'mazaq'); ?>"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"></path>
                </svg>
            </button>
        </div>

        <div class="mazaq-notification__content">
            <h2 id="mazaq-notification-prompt-title" class="mazaq-notification__title"><?php echo esc_html($prompt_title); ?></h2>
            <p id="mazaq-notification-prompt-body" class="mazaq-notification__body"><?php echo esc_html($prompt_body); ?></p>
        </div>

        <p
            id="mazaq-notification-prompt-status"
            class="mazaq-notification__status hidden"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        ></p>

        <div class="mazaq-notification__actions">
            <button
                id="mazaq-notification-prompt-subscribe"
                type="button"
                class="mazaq-notification__button mazaq-notification__button--primary"
            >
                <?php esc_html_e('اشترك الآن', 'mazaq'); ?>
            </button>
            <button
                id="mazaq-notification-dismiss"
                type="button"
                class="mazaq-notification__button mazaq-notification__button--quiet"
            >
                <?php esc_html_e('لاحقاً', 'mazaq'); ?>
            </button>
        </div>
    </section>

    <section
        id="mazaq-notification-toast"
        class="mazaq-notification mazaq-notification--toast hidden"
        aria-labelledby="mazaq-notification-toast-title"
        aria-describedby="mazaq-notification-toast-body"
        aria-live="polite"
        aria-atomic="true"
    >
        <div class="mazaq-notification__topline">
            <span class="mazaq-notification__emblem" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 16.5h28v22H10z"></path>
                    <path stroke-linecap="round" d="M10 22h28M13 11l4 5.5M22 11l4 5.5M31 11l4 5.5"></path>
                </svg>
            </span>
            <button
                id="mazaq-notification-toast-close"
                type="button"
                class="mazaq-notification__close"
                aria-label="<?php esc_attr_e('إغلاق التنبيه', 'mazaq'); ?>"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"></path>
                </svg>
            </button>
        </div>

        <div class="mazaq-notification__content">
            <h2 id="mazaq-notification-toast-title" class="mazaq-notification__title"></h2>
            <p id="mazaq-notification-toast-kicker" class="mazaq-notification__meta"></p>
            <p id="mazaq-notification-toast-body" class="mazaq-notification__body"></p>
        </div>

        <div class="mazaq-notification__actions">
            <a
                id="mazaq-notification-toast-link"
                href="<?php echo esc_url(home_url('/')); ?>"
                class="mazaq-notification__button mazaq-notification__button--primary"
            >
                <span><?php esc_html_e('اقرأ الآن', 'mazaq'); ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"></path>
                </svg>
            </a>
            <button
                id="mazaq-notification-toast-dismiss"
                type="button"
                class="mazaq-notification__button mazaq-notification__button--quiet"
            >
                <?php esc_html_e('إخفاء', 'mazaq'); ?>
            </button>
        </div>
    </section>
</div>
