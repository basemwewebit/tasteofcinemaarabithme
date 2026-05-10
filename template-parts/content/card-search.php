<a href="<?php the_permalink(); ?>" class="group flex flex-col bg-slate-100 dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-primary transition-smooth min-w-0">
    <div class="aspect-[3/4] overflow-hidden relative">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('search-poster', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-700', 'loading' => 'lazy']); ?>
        <?php endif; ?>
        <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/80 to-transparent p-4">
            <?php $cats = get_the_category(); ?>
            <span class="text-primary text-label"><?php echo esc_html(!empty($cats) ? $cats[0]->name : 'مقال'); ?></span>
        </div>
    </div>
    <div class="p-5 flex-1 flex flex-col justify-between">
        <h3 class="text-title text-slate-900 dark:text-white group-hover:text-primary transition-colors line-clamp-2"><?php the_title(); ?></h3>
        <span class="text-caption text-slate-500 mt-4 block"><?php echo esc_html(get_the_date('j F Y')); ?></span>
    </div>
</a>
