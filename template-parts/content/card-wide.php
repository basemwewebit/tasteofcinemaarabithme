<article class="card-enter group relative bg-white dark:bg-slate-800 rounded-3xl overflow-hidden border border-slate-200/60 dark:border-slate-700/60 hover:border-primary/50 dark:hover:border-primary/50 transition-all duration-500 hover:shadow-2xl hover:shadow-primary/10 hover:-translate-y-1 md:col-span-2 flex flex-col md:flex-row">
    <!-- Accent border -->
    <div class="absolute top-0 right-0 w-1 h-full bg-gradient-to-b from-primary via-amber-400 to-primary hidden md:block"></div>

    <!-- Image section -->
    <a href="<?php the_permalink(); ?>" class="relative block w-full md:w-1/2 aspect-video overflow-hidden">
        <?php if (has_post_thumbnail()) : ?>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-10"></div>
            <?php the_post_thumbnail('card-wide-thumbnail', ['class' => 'lazy-image loaded w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-out']); ?>
        <?php endif; ?>

            </a>

    <!-- Content section -->
    <div class="p-6 md:p-8 w-full md:w-1/2 flex flex-col justify-center">
        <?php $cats = get_the_category(); if (!empty($cats)) : ?>
            <span class="inline-flex items-center gap-2 text-primary text-sm font-bold mb-4 tracking-wider">
                <span class="w-1.5 h-1.5 bg-primary rounded-full"></span>
                <?php echo esc_html($cats[0]->name); ?>
            </span>
        <?php endif; ?>

        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4 group-hover:text-primary transition-colors leading-[1.32]">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <p class="text-slate-600 dark:text-slate-400 text-sm md:text-base mb-6 line-clamp-3 leading-relaxed relative">
            <?php echo esc_html(mazaq_get_excerpt(30)); ?>
        </p>

        <!-- Enhanced footer with reading time -->
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium border-t border-slate-100 dark:border-slate-700/50 pt-4 mb-4">
            <div class="flex items-center gap-2">
                <?php echo get_avatar(get_the_author_meta('ID'), 24, '', '', ['class' => 'w-6 h-6 rounded-full ring-2 ring-slate-100 dark:ring-slate-700']); ?>
                <span><?php echo esc_html(get_the_author()); ?></span>
            </div>
            <span><?php echo esc_html(get_the_date('j F Y')); ?></span>
        </div>

        <!-- Enhanced Read More CTA -->
        <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-2 text-primary font-bold hover:gap-3 transition-all group/btn">
            <span><?php esc_html_e('اقرأ المزيد', 'mazaq'); ?></span>
            <svg class="w-4 h-4 transform rotate-180 group-hover/btn:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
    </div>
</article>
