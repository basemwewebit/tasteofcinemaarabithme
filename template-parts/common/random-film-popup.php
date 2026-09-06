<?php

declare(strict_types=1);

$popup_categories = [];
if (isset($args['categories']) && is_array($args['categories'])) {
    $popup_categories = $args['categories'];
}
?>

<section class="random-film-panel mb-10" dir="rtl" aria-labelledby="random-film-heading">
    <div class="random-film-panel__frame">
        <div class="random-film-panel__copy">
            <span class="random-film-panel__sigil" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="7.5" y="7.5" width="33" height="33" rx="2"></rect>
                    <path d="M16 7.5v33M32 7.5v33M7.5 16h33M7.5 32h33"></path>
                    <circle cx="24" cy="24" r="4.5" fill="currentColor" stroke="none"></circle>
                </svg>
            </span>
            <div class="random-film-panel__copy-body">
                <h2 id="random-film-heading" class="random-film-panel__title"><?php esc_html_e('محتار في سهرتك؟ دعنا نختار لك بداية.', 'mazaq'); ?></h2>
                <p class="random-film-panel__summary"><?php esc_html_e('اختر باباً من أبوابنا أو اترك الاختيار لنا. سنفتح لك صفحة من أرشيف مذاق السينما لتبدأ منها.', 'mazaq'); ?></p>
            </div>
        </div>

        <div class="random-film-panel__controls">
            <div class="random-film-panel__field">
                <label for="random-film-category-select" class="random-film-panel__label">
                    <span><?php esc_html_e('اختر باباً', 'mazaq'); ?></span>
                    <span class="random-film-panel__optional"><?php esc_html_e('اختياري', 'mazaq'); ?></span>
                </label>
                <div class="random-film-panel__select-wrap">
                    <select id="random-film-category-select" class="random-film-panel__select">
                        <option value="0"><?php esc_html_e('كل الأبواب', 'mazaq'); ?></option>
                        <?php foreach ($popup_categories as $popup_category) : ?>
                            <?php
                            if (
                                !is_object($popup_category)
                                || !isset($popup_category->term_id, $popup_category->name, $popup_category->count)
                                || (int) $popup_category->count <= 0
                            ) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo esc_attr((string) $popup_category->term_id); ?>"><?php echo esc_html($popup_category->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <svg class="random-film-panel__select-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"></path>
                    </svg>
                </div>
            </div>

            <button id="random-film-open" type="button" class="random-film-trigger random-film-panel__button" aria-controls="random-film-result" aria-expanded="false">
                <span class="random-film-button-text" data-idle-label="<?php esc_attr_e('اقترح لي فيلماً', 'mazaq'); ?>" data-loading-label="<?php esc_attr_e('جاري الاختيار...', 'mazaq'); ?>"><?php esc_html_e('اقترح لي فيلماً', 'mazaq'); ?></span>
                <span class="random-film-button__spinner" aria-hidden="true"></span>
                <span class="random-film-arrow-wrap" aria-hidden="true">
                    <svg class="random-film-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"></path></svg>
                </span>
            </button>
        </div>
    </div>

    <div id="random-film-result" class="random-film-result" aria-busy="false" aria-hidden="true">
        <div class="random-film-result__inner">
            <div class="random-film-card">
                <div id="random-film-loading" class="random-film-state random-film-state--loading hidden">
                    <span class="random-film-state__reel" aria-hidden="true"><span></span><span></span><span></span></span>
                    <p class="random-film-state__text"><?php esc_html_e('نفتّش في الأرشيف عن بداية مناسبة...', 'mazaq'); ?></p>
                </div>

                <div id="random-film-error" class="random-film-state random-film-state--error hidden">
                    <span class="random-film-state__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 0 0 1.75 3h14.9a2 2 0 0 0 1.75-3l-7.5-13.3a2 2 0 0 0-3.4 0Z"></path></svg>
                    </span>
                    <div class="random-film-state__body">
                        <p id="random-film-error-text" class="random-film-state__message" tabindex="-1"><?php esc_html_e('تعذر تحميل الاقتراح حالياً.', 'mazaq'); ?></p>
                        <button id="random-film-retry" type="button" class="random-film-card__primary-action"><?php esc_html_e('حاول مرة أخرى', 'mazaq'); ?></button>
                    </div>
                </div>

                <article id="random-film-content" class="random-film-content hidden" aria-labelledby="random-film-title">
                    <div class="random-film-card__media">
                        <img id="random-film-image" src="<?php echo esc_url(get_template_directory_uri() . '/assets/plates/feature-still.png'); ?>" alt="" class="random-film-card__image" width="1005" height="921" loading="lazy" decoding="async">
                        <div id="random-film-image-fallback" class="random-film-card__image-fallback hidden" aria-hidden="true">
                            <svg class="random-film-card__fallback-mark" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.4">
                                <rect x="7.5" y="7.5" width="33" height="33" rx="2"></rect>
                                <circle cx="16" cy="16" r="2"></circle><circle cx="32" cy="16" r="2"></circle>
                                <circle cx="16" cy="32" r="2"></circle><circle cx="32" cy="32" r="2"></circle>
                            </svg>
                            <span>م</span>
                        </div>
                        <span class="random-film-card__media-rule" aria-hidden="true"></span>
                    </div>

                    <div class="random-film-card__body">
                        <div class="random-film-card__meta">
                            <span class="random-film-card__meta-marker" aria-hidden="true"></span>
                            <span id="random-film-category" class="random-film-card__category"></span>
                        </div>
                        <h3 id="random-film-title" class="random-film-card__title" tabindex="-1"></h3>
                        <p id="random-film-excerpt" class="random-film-card__excerpt"></p>

                        <div class="random-film-card__actions">
                            <a id="random-film-read-link" href="#" class="random-film-card__primary-action">
                                <span><?php esc_html_e('اقرأ المقال', 'mazaq'); ?></span>
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"></path></svg>
                            </a>
                            <button id="random-film-next" type="button" class="random-film-card__secondary-action">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 11a8.1 8.1 0 0 0-14.8-3.8L4 9m0 0V5m0 4h4M4 13a8.1 8.1 0 0 0 14.8 3.8L20 15m0 0v4m0-4h-4"></path></svg>
                                <span><?php esc_html_e('اقتراح آخر', 'mazaq'); ?></span>
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>

    <noscript>
        <p class="random-film-panel__noscript" role="note"><?php esc_html_e('فعّل JavaScript لتشغيل اقتراحات الأرشيف.', 'mazaq'); ?></p>
    </noscript>

    <p id="random-film-status" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></p>
</section>
