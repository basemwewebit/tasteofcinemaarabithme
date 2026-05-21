<?php

declare(strict_types=1);

function mazaq_breadcrumb(): void
{
    echo '<nav class="mb-6 max-w-full text-sm font-medium text-slate-600 dark:text-slate-300" aria-label="breadcrumb"><ol class="flex flex-wrap items-center gap-x-2 gap-y-1">';
    echo '<li class="shrink-0"><a href="' . esc_url(home_url('/')) . '" class="transition-colors hover:text-primary">' . esc_html__('الرئيسية', 'mazaq') . '</a></li>';

    if (is_single()) {
        $posts_page_id = (int) get_option('page_for_posts');
        $reviews_url = $posts_page_id ? get_permalink($posts_page_id) : home_url('/');
        echo '<li class="shrink-0" aria-hidden="true"><span class="text-slate-400">/</span></li>';
        echo '<li class="min-w-0 max-w-full"><a href="' . esc_url((string) $reviews_url) . '" class="break-words transition-colors hover:text-primary">' . esc_html__('المراجعات', 'mazaq') . '</a></li>';
        echo '<li class="shrink-0" aria-hidden="true"><span class="text-slate-400">/</span></li>';
        echo '<li class="min-w-0 max-w-full break-words text-slate-800 dark:text-slate-300" aria-current="page">' . esc_html(get_the_title()) . '</li>';
    } elseif (is_category()) {
        echo '<li class="shrink-0" aria-hidden="true"><span class="text-slate-400">/</span></li><li class="min-w-0 max-w-full break-words text-slate-800 dark:text-slate-300">' . single_cat_title('', false) . '</li>';
    }

    echo '</ol></nav>';
}
