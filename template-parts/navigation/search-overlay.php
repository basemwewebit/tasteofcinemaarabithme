<div id="search-overlay" class="fixed inset-0 z-50 hidden flex flex-col justify-center items-center" role="dialog" aria-modal="true" aria-labelledby="search-overlay-title">
    <div class="delight-search__stage" aria-hidden="true"></div>
    <button id="search-close" aria-label="<?php esc_attr_e('إغلاق البحث', 'mazaq'); ?>" class="delight-search__close">
        <svg class="w-6 h-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
    <div class="w-full max-w-3xl px-6 delight-search__content">
        <h2 id="search-overlay-title" class="delight-search__title text-center">ابحث في أرشيف السينما</h2>
        <div class="delight-search__field">
            <?php get_search_form(); ?>
        </div>
    </div>
</div>
