<?php get_header(); ?>

<main class="min-h-[80vh] flex items-center justify-center relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900"></div>
    <div class="relative z-10 text-center px-6">
        <div class="text-8xl md:text-9xl font-black bg-gradient-to-l from-primary to-yellow-200 bg-clip-text text-transparent mb-6">404</div>
        <p class="text-white text-xl md:text-2xl mb-3">عذراً، يبدو أن المخرج قد استغنى عن هذا المشهد!</p>
        <p class="text-slate-300 mb-8">الصفحة التي تبحث عنها غير موجودة.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-block bg-primary text-slate-900 font-bold px-8 py-4 rounded-xl">العودة للصفحة الرئيسية</a>
    </div>
</main>

<?php get_footer(); ?>
