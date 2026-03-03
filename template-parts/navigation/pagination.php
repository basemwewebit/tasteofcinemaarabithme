<?php
global $wp_query;
$links = paginate_links([
    'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
    'format' => '?paged=%#%',
    'current' => max(1, get_query_var('paged')),
    'total' => $wp_query->max_num_pages,
    'type' => 'array',
    'prev_text' => '←',
    'next_text' => '→',
]);
if (!empty($links)) :
?>
<div class="mt-16 flex justify-center border-t border-slate-200 dark:border-slate-800 pt-8">
    <nav class="flex items-center gap-2 font-mono">
        <?php foreach ($links as $link) : ?>
            <?php
            $is_current = str_contains($link, 'current');
            $classes = $is_current
                ? 'w-10 h-10 flex items-center justify-center rounded-full bg-primary text-slate-900 font-bold shadow-md'
                : 'w-10 h-10 flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-primary hover:text-primary transition-colors';
            echo preg_replace('/page-numbers/', 'page-numbers ' . $classes, $link);
            ?>
        <?php endforeach; ?>
    </nav>
</div>
<?php endif; ?>
