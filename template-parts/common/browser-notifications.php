<?php

declare(strict_types=1);

$args = wp_parse_args(
    $args ?? [],
    [
        'settings' => [],
    ]
);

$settings = is_array($args['settings']) ? $args['settings'] : [];
$prompt_title = (string) ($settings['prompt_title'] ?? __('اشترك في تنبيهات مذاق السينما', 'mazaq'));
$prompt_body = (string) ($settings['prompt_body'] ?? __('سنرسل لك مقالاً يومياً مختاراً ومقالات جديدة فور نشرها.', 'mazaq'));
?>
<div
    id="mazaq-notification-root"
    class="pointer-events-none fixed inset-x-4 bottom-4 z-[70] flex flex-col items-end gap-3 sm:inset-x-auto sm:end-4 sm:bottom-6"
    data-prompt-title="<?php echo esc_attr($prompt_title); ?>"
    data-prompt-body="<?php echo esc_attr($prompt_body); ?>"
>
    <section
        id="mazaq-notification-prompt"
        class="pointer-events-auto hidden w-full max-w-sm overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white/95 p-5 text-start shadow-2xl dark:border-slate-700 dark:bg-slate-900/95"
        aria-live="polite"
    >
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-primary"><?php esc_html_e('تنبيهات المتصفح', 'mazaq'); ?></p>
                <h3 id="mazaq-notification-prompt-title" class="text-lg font-bold text-slate-900 dark:text-white"><?php echo esc_html($prompt_title); ?></h3>
            </div>
            <button
                id="mazaq-notification-prompt-close"
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white"
                aria-label="<?php esc_attr_e('إغلاق طلب الاشتراك', 'mazaq'); ?>"
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <p id="mazaq-notification-prompt-body" class="mb-4 text-sm leading-7 text-slate-600 dark:text-slate-300"><?php echo esc_html($prompt_body); ?></p>
        <p id="mazaq-notification-prompt-status" class="mb-4 hidden rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-200"></p>
        <div class="flex flex-wrap items-center justify-end gap-3">
            <button
                id="mazaq-notification-dismiss"
                type="button"
                class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white"
            >
                <?php esc_html_e('لاحقاً', 'mazaq'); ?>
            </button>
            <button
                id="mazaq-notification-subscribe"
                type="button"
                class="rounded-full bg-primary px-5 py-2.5 text-sm font-bold text-slate-900 transition hover:brightness-95"
            >
                <?php esc_html_e('اشترك الآن', 'mazaq'); ?>
            </button>
        </div>
    </section>

    <section
        id="mazaq-notification-toast"
        class="pointer-events-auto hidden w-full max-w-sm overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white/95 p-5 text-start shadow-2xl dark:border-slate-700 dark:bg-slate-900/95"
        aria-live="polite"
    >
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                <p id="mazaq-notification-toast-kicker" class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-primary"></p>
                <h3 id="mazaq-notification-toast-title" class="text-lg font-bold text-slate-900 dark:text-white"></h3>
            </div>
            <button
                id="mazaq-notification-toast-close"
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white"
                aria-label="<?php esc_attr_e('إغلاق التنبيه', 'mazaq'); ?>"
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <p id="mazaq-notification-toast-body" class="mb-4 text-sm leading-7 text-slate-600 dark:text-slate-300"></p>
        <div class="flex items-center justify-end gap-3">
            <button
                id="mazaq-notification-toast-dismiss"
                type="button"
                class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white"
            >
                <?php esc_html_e('إخفاء', 'mazaq'); ?>
            </button>
            <a
                id="mazaq-notification-toast-link"
                href="<?php echo esc_url(home_url('/')); ?>"
                class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
            >
                <?php esc_html_e('اقرأ الآن', 'mazaq'); ?>
            </a>
        </div>
    </section>
</div>
