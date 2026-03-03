<div id="menu-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden transition-opacity"></div>
<div id="mobile-menu" class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-slate-900 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out border-l border-slate-200 dark:border-slate-800 flex flex-col">
    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="block">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.png'); ?>" alt="<?php bloginfo('name'); ?>" class="h-10 w-auto dark:brightness-125">
        </a>
        <button id="close-menu" aria-label="Close Mobile Menu" class="p-2 text-slate-500 hover:text-red-500 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <div class="p-6 flex-1 overflow-y-auto flex flex-col gap-6 text-lg font-medium">
        <?php
        wp_nav_menu([
            'theme_location' => 'primary-menu',
            'container' => false,
            'menu_class' => 'flex flex-col gap-6',
            'fallback_cb' => false,
        ]);
        ?>
    </div>
    <div class="p-6">
        <?php get_template_part('template-parts/ads/ad-mobile-menu'); ?>
    </div>
</div>
