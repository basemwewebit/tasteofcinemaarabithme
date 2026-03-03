<article class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden group border border-slate-200 dark:border-slate-700 hover:border-primary transition-smooth shadow-sm hover:shadow-xl archive-item">
    <a href="<?php the_permalink(); ?>" class="block relative aspect-video overflow-hidden">
        <?php if (has_post_thumbnail()) { the_post_thumbnail('card-thumbnail', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-500']); } ?>
        <?php $cats = get_the_category(); if (!empty($cats)) : ?><div class="absolute top-4 right-4 bg-primary text-slate-900 text-xs font-bold px-3 py-1 rounded-full"><?php echo esc_html($cats[0]->name); ?></div><?php endif; ?>
    </a>
    <div class="p-6">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-primary transition-colors leading-[1.35] line-clamp-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="text-slate-600 dark:text-slate-400 text-sm mb-5 line-clamp-3 leading-relaxed"><?php echo esc_html(mazaq_get_excerpt(24)); ?></p>
    </div>
</article>
