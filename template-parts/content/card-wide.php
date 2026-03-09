<article class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden group cursor-pointer border border-slate-200 dark:border-slate-700 hover:border-primary dark:hover:border-primary transition-smooth shadow-sm hover:shadow-xl md:col-span-2 flex flex-col md:flex-row">
    <a href="<?php the_permalink(); ?>" class="relative block w-full md:w-1/2 aspect-video overflow-hidden card-thumbnail-wrapper thumbnail-fallback">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('card-wide-thumbnail', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-500']); ?>
        <?php endif; ?>
       
    </a>
    <div class="p-6 md:p-8 w-full md:w-1/2 flex flex-col justify-center">
        <?php $cats = get_the_category(); if (!empty($cats)) : ?>
            <span class="text-primary text-sm font-bold mb-3 tracking-wider"><?php echo esc_html($cats[0]->name); ?></span>
        <?php endif; ?>
        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4 group-hover:text-primary transition-colors leading-[1.32]"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="text-slate-600 dark:text-slate-400 text-sm md:text-base mb-6 line-clamp-3 leading-relaxed"><?php echo esc_html(mazaq_get_excerpt(30)); ?></p>
        <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-2 text-primary font-bold hover:gap-3 transition-all">اقرأ المزيد <svg class="w-4 h-4 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg></a>
    </div>
</article>
