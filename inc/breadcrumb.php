<?php

declare(strict_types=1);

/**
 * The article page's breadcrumb: home / first category / title.
 */
function mazaq_breadcrumbs(): void
{
    echo '<nav class="mb-6 max-w-full text-sm font-medium text-slate-600 dark:text-slate-300" aria-label="breadcrumb"><ol class="flex flex-wrap items-center gap-x-2 gap-y-1">';
    echo '<li class="shrink-0"><a href="' . esc_url(home_url('/')) . '" class="transition-colors hover:text-primary">' . esc_html__('الرئيسية', 'mazaq') . '</a></li>';

    $cats = get_the_category();
    if (!empty($cats)) {
        echo '<li class="shrink-0" aria-hidden="true"><span class="text-slate-400">/</span></li>';
        echo '<li class="min-w-0 max-w-full"><a href="' . esc_url(get_category_link($cats[0]->term_id)) . '" class="break-words transition-colors hover:text-primary">' . esc_html($cats[0]->name) . '</a></li>';
    }
    echo '<li class="shrink-0" aria-hidden="true"><span class="text-slate-400">/</span></li>';
    echo '<li class="min-w-0 max-w-full break-words text-slate-800 dark:text-slate-300" aria-current="page">' . esc_html(get_the_title()) . '</li>';

    echo '</ol></nav>';
}
