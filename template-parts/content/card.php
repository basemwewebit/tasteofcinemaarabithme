<article class="card-enter group relative bg-slate-100 dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200/60 dark:border-slate-700/60 hover:border-primary/50 dark:hover:border-primary/50 transition-colors duration-300 min-w-0">
    <!-- Image with gradient overlay -->
    <a href="<?php the_permalink(); ?>" class="relative block aspect-video overflow-hidden">
        <?php if (has_post_thumbnail()) : ?>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-10"></div>
            <?php the_post_thumbnail('card-thumbnail', ['class' => 'w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-out', 'loading' => 'lazy']); ?>
        <?php endif; ?>

        <?php $cats = get_the_category(); if (!empty($cats)) : ?>
            <span class="absolute top-4 right-4 z-20 bg-primary/15 dark:text-[var(--color-gold-tint)] text-slate-800 text-label px-3 py-1.5 rounded-full border border-primary/25 flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-primary rounded-full" aria-hidden="true"></span>
                <?php echo esc_html($cats[0]->name); ?>
            </span>
        <?php endif; ?>

            </a>

    <!-- Content -->
    <div class="p-6">
        <h3 class="text-title text-slate-900 dark:text-white mb-3 group-hover:text-primary transition-colors line-clamp-2">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <p class="text-slate-600 dark:text-slate-400 text-body mb-6 line-clamp-2">
            <?php echo esc_html(mazaq_get_excerpt(22)); ?>
        </p>

        <!-- Enhanced footer with reading time -->
        <div class="flex items-center justify-between text-caption text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700/50 pt-5">
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                    <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?php echo mazaq_reading_time(get_the_ID()); ?>
                </span>
                <span><?php echo esc_html(get_the_date('j F Y')); ?></span>
            </div>
        </div>
    </div>
</article>
