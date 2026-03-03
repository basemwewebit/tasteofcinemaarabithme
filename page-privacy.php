<?php
/* Template Name: Privacy */
get_header();
?>

<main class="max-w-4xl mx-auto px-4 py-16">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <header class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4 leading-[1.25]"><?php the_title(); ?></h1>
            <p class="text-slate-500">آخر تحديث: <?php echo esc_html(get_the_modified_date('j F Y')); ?></p>
        </header>
        <div class="article-content text-slate-700 dark:text-slate-300 leading-loose"><?php the_content(); ?></div>
        <div class="mt-12 p-6 rounded-2xl border border-primary/40 bg-primary/10">
            <p class="mb-4">للاستفسارات حول الخصوصية يمكن التواصل معنا.</p>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="inline-block bg-primary text-slate-900 font-bold px-6 py-3 rounded-xl">اتصل بنا</a>
        </div>
    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
