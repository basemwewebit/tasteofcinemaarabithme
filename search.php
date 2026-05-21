<?php get_header(); ?>
<?php global $wp_query; ?>

<div class="relative bg-slate-900 text-white pt-20 pb-16 md:pt-28 md:pb-20 mb-12 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="max-w-3xl">
            <h1 class="text-title font-medium mb-4 text-slate-400">نتائج البحث عن:</h1>
            <h2 class="text-display mb-8 text-primary break-words"><?php echo esc_html(get_search_query()); ?></h2>
            <div class="max-w-xl"><?php get_search_form(); ?></div>
            <p class="mt-6 text-slate-400 text-label">تم العثور على <?php echo esc_html((string) $wp_query->found_posts); ?> نتيجة</p>
        </div>
    </div>
</div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20">
    <?php if (have_posts()) : ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10">
            <?php while (have_posts()) : the_post(); get_template_part('template-parts/content/card-search'); endwhile; ?>
        </div>
    <?php else : ?>
        <p class="text-center text-slate-600 dark:text-slate-300">لم يتم العثور على نتائج</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
