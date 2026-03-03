<?php get_header(); ?>

<div class="bg-slate-900 text-white py-16 md:py-24 mb-12 relative overflow-hidden flex justify-center items-center text-center">
    <div class="max-w-4xl mx-auto px-4 relative z-10 w-full">
        <h1 class="text-3xl font-medium mb-4 text-slate-400">نتائج البحث عن:</h1>
        <h2 class="text-5xl font-bold mb-10 text-primary"><?php echo esc_html(get_search_query()); ?></h2>
        <div class="max-w-2xl mx-auto"><?php get_search_form(); ?></div>
        <p class="mt-6 text-slate-400 font-mono text-sm">تم العثور على <?php echo esc_html((string) $wp_query->found_posts); ?> نتيجة</p>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 mb-20">
    <?php if (have_posts()) : ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php while (have_posts()) : the_post(); get_template_part('template-parts/content/card-search'); endwhile; ?>
        </div>
    <?php else : ?>
        <p class="text-center text-slate-500">لم يتم العثور على نتائج</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
