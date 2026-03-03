<form role="search" method="get" class="relative" action="<?php echo esc_url(home_url('/')); ?>">
    <input type="search" id="search-input" class="w-full text-2xl bg-transparent border-b-2 border-slate-300 dark:border-slate-700 focus:border-primary dark:focus:border-primary px-4 py-4 pr-14 outline-none text-slate-800 dark:text-white transition-colors" placeholder="ابحث عن فيلم أو مقال..." value="<?php echo esc_attr(get_search_query()); ?>" name="s" autocomplete="off" />
    <button type="submit" class="absolute right-0 top-1/2 transform -translate-y-1/2 p-4 text-slate-400 hover:text-primary transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </button>
</form>
