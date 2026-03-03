<?php

declare(strict_types=1);

function mazaq_breadcrumb(): void
{
    echo '<nav class="flex text-sm text-slate-500 mb-6 font-medium" aria-label="breadcrumb"><ol class="inline-flex items-center gap-2">';
    echo '<li><a href="' . esc_url(home_url('/')) . '" class="hover:text-primary">' . esc_html__('الرئيسية', 'mazaq') . '</a></li>';

    if (is_single()) {
        $posts_page_id = (int) get_option('page_for_posts');
        $reviews_url = $posts_page_id ? get_permalink($posts_page_id) : home_url('/');
        echo '<li><span class="text-slate-400">/</span></li>';
        echo '<li><a href="' . esc_url((string) $reviews_url) . '" class="hover:text-primary">' . esc_html__('المراجعات', 'mazaq') . '</a></li>';
        echo '<li><span class="text-slate-400">/</span></li>';
        echo '<li class="text-slate-800 dark:text-slate-300" aria-current="page">' . esc_html(get_the_title()) . '</li>';
    } elseif (is_category()) {
        echo '<li><span class="text-slate-400">/</span></li><li class="text-slate-800 dark:text-slate-300">' . single_cat_title('', false) . '</li>';
    }

    echo '</ol></nav>';
}
