<a href="<?php the_permalink(); ?>" class="group flex gap-4 min-w-0">
    <?php if (has_post_thumbnail()) : ?>
        <?php the_post_thumbnail('sidebar-thumbnail', ['class' => 'w-20 h-20 rounded-xl object-cover shrink-0', 'loading' => 'lazy']); ?>
    <?php endif; ?>
    <div class="min-w-0">
        <h4 class="text-title text-slate-800 dark:text-slate-200 mb-1 group-hover:text-primary transition-colors line-clamp-2"><?php the_title(); ?></h4>
        <span class="text-caption text-slate-500"><?php echo esc_html(get_the_date('j F Y')); ?></span>
    </div>
</a>
