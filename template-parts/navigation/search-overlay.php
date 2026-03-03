<div id="search-overlay" class="fixed inset-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md z-50 hidden flex flex-col justify-center items-center">
    <button id="search-close" aria-label="Close Search" class="absolute top-8 right-8 p-3 text-slate-500 hover:text-red-500 bg-slate-100 dark:bg-slate-800 rounded-full transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
    <div class="w-full max-w-3xl px-6">
        <h2 class="text-3xl font-bold mb-8 text-center text-slate-800 dark:text-white">عن ماذا تبحث؟</h2>
        <?php get_search_form(); ?>
    </div>
</div>
