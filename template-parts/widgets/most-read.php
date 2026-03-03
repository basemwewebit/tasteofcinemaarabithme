<?php $most_read = mazaq_get_most_read_posts(3); ?>
<div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6 border-b-2 border-primary pb-3 inline-block">الأكثر قراءة هذا الأسبوع</h3>
    <div class="flex flex-col gap-6">
        <?php if ($most_read->have_posts()) : $rank = 1; ?>
            <?php while ($most_read->have_posts()) : $most_read->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="group flex items-center gap-4">
                    <b class="text-4xl text-slate-200 dark:text-slate-700 font-bold group-hover:text-primary transition-colors"><?php echo esc_html((string) $rank); ?></b>
                    <div>
                        <h4 class="text-slate-800 dark:text-slate-200 font-bold mb-1 group-hover:text-primary transition-colors line-clamp-2"><?php the_title(); ?></h4>
                        <span class="text-xs text-slate-500 font-mono"><?php echo esc_html(number_format_i18n(mazaq_get_post_views(get_the_ID()))); ?> مشاهدة</span>
                    </div>
                </a>
                <?php $rank++; ?>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>
</div>
