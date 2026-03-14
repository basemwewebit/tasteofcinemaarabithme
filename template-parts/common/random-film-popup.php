<?php

declare(strict_types=1);

$popup_categories = [];
if (isset($args['categories']) && is_array($args['categories'])) {
    $popup_categories = $args['categories'];
}
?>

<section class="mb-10 rounded-3xl border border-slate-200/80 dark:border-slate-700 bg-gradient-to-br from-white via-slate-50 to-amber-50/70 dark:from-slate-800 dark:via-slate-800 dark:to-slate-900 p-5 md:p-7 shadow-sm">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-primary mb-2"><?php esc_html_e('قاعدة معرفة الأفلام', 'mazaq'); ?></p>
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white"><?php esc_html_e('محتار؟ دعنا نقترح عليك فيلماً', 'mazaq'); ?></h2>
            <p class="text-sm md:text-base text-slate-600 dark:text-slate-300 mt-2"><?php esc_html_e('اضغط على الزر لتحصل على ترشيح عشوائي من مقالات الأفلام الموجودة لدينا.', 'mazaq'); ?></p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 w-full md:w-auto">
            <label for="random-film-category-select" class="sr-only"><?php esc_html_e('اختر قائمة', 'mazaq'); ?></label>
            <select id="random-film-category-select" class="h-12 min-w-[13rem] rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-primary/60">
                <option value="0"><?php esc_html_e('كل القوائم', 'mazaq'); ?></option>
                <?php foreach ($popup_categories as $popup_category) : ?>
                    <?php if (!isset($popup_category->count) || (int) $popup_category->count <= 0) { continue; } ?>
                    <option value="<?php echo esc_attr((string) $popup_category->term_id); ?>"><?php echo esc_html($popup_category->name); ?></option>
                <?php endforeach; ?>
            </select>

            <button id="random-film-open" type="button" class="random-film-trigger h-12 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold px-5 shadow-md hover:scale-[1.01] transition-transform">
                <span class="random-film-button-text"><?php esc_html_e('اقترح لي فيلم', 'mazaq'); ?></span>
                <span class="random-film-arrow-wrap" aria-hidden="true">
                    <svg class="random-film-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </span>
            </button>
        </div>
    </div>
</section>

<div id="random-film-popup" class="fixed inset-0 z-[55] hidden items-center justify-center bg-slate-950/70 backdrop-blur-sm px-4 py-6" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="random-film-title">
    <div id="random-film-dialog" class="random-film-card-glow relative w-full max-w-4xl overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl">
        <button id="random-film-close" type="button" class="absolute top-4 left-4 z-30 w-10 h-10 rounded-full bg-white/85 dark:bg-slate-800/85 text-slate-600 dark:text-slate-200 hover:text-red-500 transition-colors" aria-label="<?php esc_attr_e('إغلاق الاقتراح', 'mazaq'); ?>">
            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div id="random-film-loading" class="absolute inset-0 z-20 hidden items-center justify-center bg-white/85 dark:bg-slate-900/85">
            <div class="text-center">
                <svg class="animate-spin h-8 w-8 text-primary mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-300"><?php esc_html_e('جاري تجهيز اقتراح الفيلم...', 'mazaq'); ?></p>
            </div>
        </div>

        <div id="random-film-error" class="hidden p-8 text-center">
            <p id="random-film-error-text" class="text-base text-red-600 dark:text-red-400 mb-4"><?php esc_html_e('تعذر تحميل الاقتراح حالياً.', 'mazaq'); ?></p>
            <button id="random-film-retry" type="button" class="inline-flex items-center justify-center rounded-xl bg-primary text-slate-900 font-bold px-5 py-2.5 hover:brightness-95 transition"><?php esc_html_e('إعادة المحاولة', 'mazaq'); ?></button>
        </div>

        <div id="random-film-content" class="grid md:grid-cols-[1.1fr_1fr]">
            <div class="relative min-h-[230px] md:min-h-[380px] bg-slate-200 dark:bg-slate-800">
                <img id="random-film-image" src="" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"></div>
                <span id="random-film-category" class="absolute bottom-4 right-4 inline-flex items-center rounded-full bg-white/90 dark:bg-slate-900/85 px-3 py-1 text-xs font-bold text-slate-800 dark:text-slate-100"></span>
            </div>

            <div class="p-6 md:p-8 flex flex-col">
                <p class="text-sm font-semibold text-primary mb-2"><?php esc_html_e('ترشيح عشوائي', 'mazaq'); ?></p>
                <h3 id="random-film-title" class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white leading-relaxed mb-4"></h3>
                <p id="random-film-excerpt" class="text-slate-600 dark:text-slate-300 leading-8 mb-6 flex-1"></p>

                <div class="flex flex-wrap gap-3">
                    <a id="random-film-read-link" href="#" class="inline-flex items-center justify-center rounded-xl bg-primary text-slate-900 font-bold px-5 py-3 hover:brightness-95 transition"><?php esc_html_e('قراءة المقال الكامل', 'mazaq'); ?></a>
                    <button id="random-film-next" type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold px-5 py-3 hover:bg-slate-100 dark:hover:bg-slate-800 transition"><?php esc_html_e('اقتراح آخر', 'mazaq'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
