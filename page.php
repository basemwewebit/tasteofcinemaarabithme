<?php

get_header();
?>

<main id="main-content" class="max-w-4xl mx-auto px-4 pt-16 pb-20">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <header class="mb-12">
            <h1 class="text-headline text-slate-900 dark:text-white mb-4 break-words"><?php the_title(); ?></h1>
            <p class="text-slate-500 text-sm">آخر تحديث: <?php echo esc_html(get_the_modified_date('j F Y')); ?></p>
        </header>
        <div class="article-content text-slate-700 dark:text-slate-300 leading-loose"><?php the_content(); ?></div>
    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
