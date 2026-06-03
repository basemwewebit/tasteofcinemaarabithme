<?php

declare(strict_types=1);

$popup_categories = [];
if (isset($args['categories']) && is_array($args['categories'])) {
    $popup_categories = $args['categories'];
}
?>

<section class="random-film-panel mb-10">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="random-film-panel__kicker"><?php esc_html_e('توصية المحرّر', 'mazaq'); ?></p>
            <h2 class="random-film-panel__title"><?php esc_html_e('محتار؟ دعنا نقترح عليك فيلماً', 'mazaq'); ?></h2>
            <p class="random-film-panel__summary"><?php esc_html_e('اختر تصنيفاً أو دع الاختيار لنا، ونقترح عليك مقالاً من أرشيفنا تبدأ به ليلتك.', 'mazaq'); ?></p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 w-full md:w-auto">
            <label for="random-film-category-select" class="sr-only"><?php esc_html_e('اختر قائمة', 'mazaq'); ?></label>
            <select id="random-film-category-select" class="random-film-panel__select">
                <option value="0"><?php esc_html_e('كل القوائم', 'mazaq'); ?></option>
                <?php foreach ($popup_categories as $popup_category) : ?>
                    <?php if (!isset($popup_category->count) || (int) $popup_category->count <= 0) { continue; } ?>
                    <option value="<?php echo esc_attr((string) $popup_category->term_id); ?>"><?php echo esc_html($popup_category->name); ?></option>
                <?php endforeach; ?>
            </select>

            <button id="random-film-open" type="button" class="random-film-trigger random-film-panel__button">
                <span class="random-film-button-text"><?php esc_html_e('اقترح لي فيلم', 'mazaq'); ?></span>
                <span class="random-film-arrow-wrap" aria-hidden="true">
                    <svg class="random-film-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </span>
            </button>
        </div>
    </div>
</section>

<div id="random-film-result" class="random-film-result" aria-live="polite">
    <div class="random-film-result__inner">
        <div class="random-film-card-glow random-film-card mb-6">
            <div id="random-film-loading" class="hidden items-center justify-center py-16" role="status">
                <div class="random-film-loader">
                    <span class="random-film-loader__reel" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </span>
                    <p class="random-film-loader__text"><?php esc_html_e('جاري اختيار فيلم لك...', 'mazaq'); ?></p>
                </div>
            </div>

            <div id="random-film-error" class="hidden p-8 text-center">
                <p id="random-film-error-text" class="random-film-card__error mb-4"><?php esc_html_e('تعذر تحميل الاقتراح حالياً.', 'mazaq'); ?></p>
                <button id="random-film-retry" type="button" class="random-film-card__primary-action"><?php esc_html_e('إعادة المحاولة', 'mazaq'); ?></button>
            </div>

            <div id="random-film-content" class="hidden grid md:grid-cols-[1.1fr_1fr]">
                <div class="random-film-card__media">
                    <img id="random-film-image" src="" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                    <div id="random-film-image-fallback" class="random-film-card__image-fallback hidden" aria-hidden="true">
                        <span>م</span>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <span id="random-film-category" class="random-film-card__category"></span>
                </div>

                <div class="p-6 md:p-8 flex flex-col">
                    <p class="random-film-card__kicker"><?php esc_html_e('ترشيح عشوائي', 'mazaq'); ?></p>
                    <h3 id="random-film-title" class="random-film-card__title"></h3>
                    <p id="random-film-excerpt" class="random-film-card__excerpt"></p>

                    <div class="flex flex-wrap gap-3">
                        <a id="random-film-read-link" href="#" class="random-film-card__primary-action"><?php esc_html_e('قراءة المقال الكامل', 'mazaq'); ?></a>
                        <button id="random-film-next" type="button" class="random-film-card__secondary-action"><?php esc_html_e('اقتراح آخر', 'mazaq'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
