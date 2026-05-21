<?php $search_input_id = wp_unique_id('search-input-'); ?>
<form role="search" method="get" class="relative" action="<?php echo esc_url(home_url('/')); ?>">
    <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">ابحث عن فيلم أو مقال</label>
    <input type="search" id="<?php echo esc_attr($search_input_id); ?>" class="w-full text-2xl bg-transparent border-b-2 border-slate-300 dark:border-slate-700 focus:border-primary dark:focus:border-primary px-4 py-4 ps-14 outline-none text-slate-800 dark:text-white transition-colors" placeholder="ابحث عن فيلم أو مقال..." value="<?php echo esc_attr(get_search_query()); ?>" name="s" autocomplete="off" aria-label="حقل البحث" />
    <button type="submit" class="absolute start-0 top-1/2 transform -translate-y-1/2 p-4 text-slate-600 dark:text-slate-300 hover:text-primary transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2 rounded-sm" aria-label="<?php esc_attr_e('بحث', 'mazaq'); ?>">
        <svg class="w-8 h-8" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </button>
</form>
