<?php
global $wp_query;
$current_page = max(1, (int) get_query_var('paged'));
$total_pages = max(1, (int) $wp_query->max_num_pages);
$links = paginate_links([
    'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
    'format' => '?paged=%#%',
    'current' => $current_page,
    'total' => $total_pages,
    'type' => 'array',
    'prev_text' => __('السابق', 'mazaq'),
    'next_text' => __('التالي', 'mazaq'),
    'mid_size' => 1,
    'end_size' => 1,
]);
if ($total_pages > 1 && !empty($links)) :
?>
<div class="mt-16 border-t border-slate-200 dark:border-slate-800 pt-8">
    <nav class="pagination-nav" aria-label="<?php esc_attr_e('التنقل بين الصفحات', 'mazaq'); ?>">
        <span class="pagination-nav__status num">
            <?php echo esc_html(sprintf(__('صفحة %1$d من %2$d', 'mazaq'), $current_page, $total_pages)); ?>
        </span>
        <div class="pagination-nav__links">
        <?php foreach ($links as $link) : ?>
            <?php
            $is_current = str_contains($link, 'current');
            $is_prev_next = str_contains($link, 'prev') || str_contains($link, 'next');
            $classes = $is_current
                ? 'pagination-nav__link pagination-nav__link--current'
                : 'pagination-nav__link' . ($is_prev_next ? ' pagination-nav__link--step' : '');
            echo preg_replace('/page-numbers/', 'page-numbers ' . $classes, $link);
            ?>
        <?php endforeach; ?>
        </div>
    </nav>
</div>
<?php endif; ?>
