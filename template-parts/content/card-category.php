<article class="bg-slate-100 dark:bg-slate-800 rounded-2xl overflow-hidden group border border-slate-200 dark:border-slate-700 hover:border-primary transition-smooth archive-item min-w-0">
    <a href="<?php the_permalink(); ?>" class="block relative aspect-video overflow-hidden">
        <?php if (has_post_thumbnail()) { the_post_thumbnail('card-thumbnail', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-500', 'loading' => 'lazy']); } ?>
        <?php if(!is_category()) { ?>
            <?php $cats = get_the_category(); if (!empty($cats)) : ?><span class="absolute top-4 right-4 bg-primary/15 dark:text-[var(--color-gold-tint)] text-slate-800 text-label px-3 py-1.5 rounded-full border border-primary/25"><?php echo esc_html($cats[0]->name); ?></span><?php endif; ?>
        <?php } ?>
    </a>
    <div class="p-6">
        <h3 class="text-title text-slate-900 dark:text-white mb-3 group-hover:text-primary transition-colors line-clamp-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="text-slate-600 dark:text-slate-400 text-sm mb-6 line-clamp-3"><?php echo esc_html(mazaq_get_excerpt(24)); ?></p>
          <div class="flex items-center justify-between text-caption text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-5">
            <span><?php echo esc_html(get_the_date('j F Y')); ?></span>
        </div>
    </div>
</article>
