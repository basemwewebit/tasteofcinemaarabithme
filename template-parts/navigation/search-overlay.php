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
    <div class="w-full max-w-3xl px-6 delight-search__content">
        <h2 id="search-overlay-title" class="delight-search__title text-center">ابحث في أرشيف السينما</h2>
        <div class="delight-search__field">
            <form role="search" method="get" class="relative" action="<?php echo esc_url(home_url('/')); ?>" data-live-search-form>
                <label for="overlay-search-input" class="sr-only"><?php esc_html_e('ابحث عن فيلم أو مقال', 'mazaq'); ?></label>
                <input type="search" id="overlay-search-input" name="s" autocomplete="off" aria-describedby="search-suggestions-status" aria-controls="search-suggestions-list" placeholder="<?php esc_attr_e('ابحث عن فيلم أو مقال...', 'mazaq'); ?>">
                <button type="submit" aria-label="<?php esc_attr_e('بحث', 'mazaq'); ?>">
                    <svg class="w-8 h-8" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>
            <p id="search-suggestions-status" class="delight-search__status" aria-live="polite"></p>
            <div id="search-suggestions-list" class="delight-search__suggestions" role="list"></div>
            <div class="delight-search__chips" aria-label="<?php esc_attr_e('عمليات بحث مقترحة', 'mazaq'); ?>">
                <div class="delight-search__chip-group" data-recent-searches hidden>
                    <p><?php esc_html_e('بحثت مؤخراً', 'mazaq'); ?></p>
                    <div data-recent-searches-list></div>
                </div>
                <?php if (!empty($popular_terms)) : ?>
                    <div class="delight-search__chip-group">
                        <p><?php esc_html_e('الأكثر تداولاً', 'mazaq'); ?></p>
                        <div>
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
