<a href="<?php the_permalink(); ?>" class="group flex flex-col bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl border border-slate-200 dark:border-slate-700 hover:border-primary transition-smooth">
    <div class="aspect-[3/4] overflow-hidden relative">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('search-poster', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-700']); ?>
        <?php endif; ?>
        <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/80 to-transparent p-4">
            <?php $cats = get_the_category(); ?>
            <span class="text-primary font-mono text-sm font-bold"><?php echo esc_html(!empty($cats) ? $cats[0]->name : 'مقال'); ?></span>
        </div>
    </div>
    <div class="p-5 flex-1 flex flex-col justify-between">
        <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-primary transition-colors line-clamp-2 leading-[1.35]"><?php the_title(); ?></h3>
        <span class="text-xs text-slate-500 mt-4 font-mono block"><?php echo esc_html(get_the_date('j F Y')); ?></span>
    </div>
</a>
