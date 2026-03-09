<?php
if (get_theme_mod('toc_hp_categories_enabled', false)):
    $category_count = get_theme_mod('toc_hp_categories_category_count', 6);
    $section_title = get_theme_mod('toc_hp_categories_title', 'تصفح حسب الفئة');
    
    $categories = get_categories([
        'orderby' => 'count',
        'order'   => 'DESC',
        'number'  => $category_count,
        'hide_empty' => true,
    ]);
?>
    <div class="w-full">
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white flex items-center gap-3"><span class="w-2 h-8 bg-primary rounded"></span><?php echo esc_html($section_title); ?></h2>
        </div>
        
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categories as $category) : ?>
                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="group bg-slate-100 dark:bg-slate-800 rounded-xl p-6 text-center border border-slate-200 dark:border-slate-700 hover:border-primary dark:hover:border-primary transition-all hover:shadow-md">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2 group-hover:text-primary transition-colors"><?php echo esc_html($category->name); ?></h3>
                    <span class="text-sm text-slate-500 dark:text-slate-400 block"><?php echo sprintf(esc_html__('%d مقال', 'mazaq'), $category->count); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
