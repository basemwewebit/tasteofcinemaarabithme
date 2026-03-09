<a href="<?php the_permalink(); ?>" class="group flex gap-4">
    <?php if (has_post_thumbnail()) : ?>
        <div class="card-thumbnail-wrapper thumbnail-fallback rounded-xl overflow-hidden shrink-0">
            <?php the_post_thumbnail('sidebar-thumbnail', ['class' => 'w-20 h-20 object-cover']); ?>
        </div>
    <?php else: ?>
        <div class="card-thumbnail-wrapper thumbnail-fallback rounded-xl overflow-hidden shrink-0 w-20 h-20"></div>
    <?php endif; ?>
    <div>
        <h4 class="text-slate-800 dark:text-slate-200 font-bold mb-1 group-hover:text-primary transition-colors line-clamp-2 leading-[1.35]"><?php the_title(); ?></h4>
        <span class="text-xs text-slate-500"><?php echo esc_html(get_the_date('j F Y')); ?></span>
    </div>
</a>
