<article class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden group cursor-pointer border border-slate-200 dark:border-slate-700 hover:border-primary dark:hover:border-primary transition-smooth shadow-sm hover:shadow-xl">
    <a href="<?php the_permalink(); ?>" class="relative block aspect-video overflow-hidden">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('card-thumbnail', ['class' => 'lazy-image loaded w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-500']); ?>
        <?php endif; ?>
        <?php $cats = get_the_category(); if (!empty($cats)) : ?>
            <span class="absolute top-4 right-4 bg-primary text-slate-900 text-xs font-bold px-3 py-1 rounded-full shadow-md"><?php echo esc_html($cats[0]->name); ?></span>
        <?php endif; ?>
    </a>
    <div class="p-6">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-primary transition-colors leading-[1.35] line-clamp-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="text-slate-600 dark:text-slate-400 text-sm mb-5 line-clamp-2 leading-relaxed"><?php echo esc_html(mazaq_get_excerpt(22)); ?></p>
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium border-t border-slate-100 dark:border-slate-700 pt-4">
            <div class="flex items-center gap-2">
                <?php echo get_avatar(get_the_author_meta('ID'), 24, '', '', ['class' => 'w-6 h-6 rounded-full']); ?>
                <span><?php the_author(); ?></span>
            </div>
            <span><?php echo esc_html(get_the_date('j F Y')); ?></span>
        </div>
    </div>
</article>
