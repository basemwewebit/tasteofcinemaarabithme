<?php $most_read = mazaq_get_most_read_posts(3); ?>
<div class="bg-slate-100 dark:bg-slate-800 rounded-lg p-6 border border-slate-200 dark:border-slate-700">
    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6 pb-3 inline-block">الأكثر قراءة هذا الأسبوع</h3>
    <div class="flex flex-col gap-5">
        <?php if ($most_read->have_posts()) : $rank = 1; ?>
            <?php while ($most_read->have_posts()) : $most_read->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="group flex items-start gap-4">
                    <b class="text-3xl text-slate-200 dark:text-slate-700 font-bold group-hover:text-primary transition-colors leading-none mt-0.5"><?php echo esc_html((string) $rank); ?></b>
                    <div>
                        <h4 class="text-slate-800 dark:text-slate-200 font-bold mb-1 group-hover:text-primary transition-colors line-clamp-2 leading-snug"><?php the_title(); ?></h4>
                        <span class="text-caption text-slate-600 dark:text-slate-300"><?php echo esc_html(number_format_i18n(mazaq_get_post_views(get_the_ID()))); ?> مشاهدة</span>
                    </div>
                </a>
                <?php $rank++; ?>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>
</div>
