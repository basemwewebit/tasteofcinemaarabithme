<?php
$popular_terms = get_categories([
    'orderby' => 'count',
    'order' => 'DESC',
    'hide_empty' => true,
    'number' => 4,
]);
?>
<div id="search-overlay" class="fixed inset-0 z-50 hidden flex flex-col justify-center items-center" role="dialog" aria-modal="true" aria-labelledby="search-overlay-title">
    <div class="delight-search__stage" aria-hidden="true"></div>
    <button id="search-close" aria-label="<?php esc_attr_e('إغلاق البحث', 'mazaq'); ?>" class="delight-search__close">
        <svg class="w-6 h-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
    <div class="delight-search__content w-full max-w-3xl px-6">
        <h2 id="search-overlay-title" class="delight-search__title">ابحث في أرشيف السينما</h2>
        <div class="delight-search__field">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" data-live-search-form>
                <label for="overlay-search-input" class="sr-only"><?php esc_html_e('ابحث عن فيلم أو مقال', 'mazaq'); ?></label>
                <input type="search" id="overlay-search-input" name="s" role="combobox" aria-expanded="false" aria-controls="search-suggestions-list" aria-describedby="search-suggestions-status" aria-autocomplete="list" autocomplete="off" spellcheck="false" maxlength="80" enterkeyhint="search" placeholder="<?php esc_attr_e('ابحث عن فيلم أو مقال...', 'mazaq'); ?>">
                <button type="button" class="delight-search__clear" data-search-clear aria-label="<?php esc_attr_e('مسح حقل البحث', 'mazaq'); ?>" hidden>
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <button type="submit" class="delight-search__submit" aria-label="<?php esc_attr_e('بحث', 'mazaq'); ?>">
                    <svg class="w-6 h-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>
            <p id="search-suggestions-status" class="delight-search__status" role="status" aria-live="polite"></p>
            <div id="search-suggestions-list" class="delight-search__suggestions" role="listbox" aria-label="<?php esc_attr_e('نتائج البحث الفوري', 'mazaq'); ?>"></div>
            <div class="delight-search__all" data-search-all hidden>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="delight-search__all-link" data-search-all-link>
                    <span data-search-all-label></span>
                    <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
            </div>
            <div class="delight-search__chips" aria-label="<?php esc_attr_e('عمليات بحث مقترحة', 'mazaq'); ?>">
                <div class="delight-search__chip-group" data-recent-searches hidden>
                    <div class="delight-search__chip-head">
                        <p><?php esc_html_e('بحثت مؤخراً', 'mazaq'); ?></p>
                        <button type="button" class="delight-search__chip-clear" data-recent-searches-clear><?php esc_html_e('مسح السجل', 'mazaq'); ?></button>
                    </div>
                    <div class="delight-search__chip-row" data-recent-searches-list></div>
                </div>
                <?php if (!empty($popular_terms)) : ?>
                    <div class="delight-search__chip-group">
                        <div class="delight-search__chip-head">
                            <p><?php esc_html_e('الأكثر تداولاً', 'mazaq'); ?></p>
                        </div>
                        <div class="delight-search__chip-row">
                            <?php foreach ($popular_terms as $popular_term) : ?>
                                <button type="button" class="delight-search__chip" data-search-term="<?php echo esc_attr($popular_term->name); ?>"><?php echo esc_html($popular_term->name); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
