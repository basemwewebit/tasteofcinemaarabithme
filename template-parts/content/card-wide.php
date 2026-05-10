<article class="card-enter group relative bg-slate-100 dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200/60 dark:border-slate-700/60 hover:border-primary/50 dark:hover:border-primary/50 transition-colors duration-300 md:col-span-2 flex flex-col md:flex-row min-w-0">
    <!-- Image section -->
    <a href="<?php the_permalink(); ?>" class="relative block w-full md:w-1/2 aspect-video overflow-hidden">
        <?php if (has_post_thumbnail()) : ?>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-10"></div>
            <?php the_post_thumbnail('card-wide-thumbnail', ['class' => 'w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-out', 'loading' => 'lazy']); ?>
        <?php endif; ?>

            </a>

    <!-- Content section -->
    <div class="p-6 md:p-8 w-full md:w-1/2 flex flex-col justify-center">
        <?php $cats = get_the_category(); if (!empty($cats)) : ?>
            <span class="inline-flex items-center gap-2 dark:text-primary text-slate-800 text-label mb-4">
                <span class="w-1.5 h-1.5 bg-primary rounded-full" aria-hidden="true"></span>
                <?php echo esc_html($cats[0]->name); ?>
            </span>
        <?php endif; ?>

        <h3 class="text-headline text-slate-900 dark:text-white mb-4 group-hover:text-primary transition-colors">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <p class="text-slate-600 dark:text-slate-400 text-body mb-6 line-clamp-3 relative">
            <?php echo esc_html(mazaq_get_excerpt(30)); ?>
        </p>

        <!-- Enhanced footer with reading time -->
        <div class="flex items-center justify-between text-caption text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700/50 pt-4 mb-4">
            <span><?php echo esc_html(get_the_date('j F Y')); ?></span>
        </div>

        <!-- Enhanced Read More CTA -->
        <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-2 dark:text-primary text-slate-900 hover:text-primary text-label group/btn">
            <span><?php esc_html_e('اقرأ المزيد', 'mazaq'); ?></span>
            <svg class="w-4 h-4 transform rotate-180 group-hover/btn:-translate-x-1 transition-transform" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
    </div>
</article>
