<article class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden group cursor-pointer border border-slate-200 dark:border-slate-700 hover:border-primary dark:hover:border-primary transition-smooth shadow-sm hover:shadow-xl md:col-span-2 flex flex-col md:flex-row">
    <a href="<?php the_permalink(); ?>" class="relative block w-full md:w-1/2 aspect-video overflow-hidden">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('card-wide-thumbnail', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-500']); ?>
        <?php endif; ?>
        <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm border border-white/20 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 shadow-md">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
            <?php echo esc_html__('مقال الأسبوع', 'mazaq'); ?>
        </div>
    </a>
    <div class="p-6 md:p-8 w-full md:w-1/2 flex flex-col justify-center">
        <div class="text-primary text-sm font-bold mb-3 tracking-wider"><?php echo esc_html__('نظرة تحليلية', 'mazaq'); ?></div>
        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4 group-hover:text-primary transition-colors leading-[1.32]"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="text-slate-600 dark:text-slate-400 text-sm md:text-base mb-6 line-clamp-3 leading-relaxed"><?php echo esc_html(mazaq_get_excerpt(30)); ?></p>
        <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-2 text-primary font-bold hover:gap-3 transition-all">اقرأ المزيد <svg class="w-4 h-4 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg></a>
    </div>
</article>
