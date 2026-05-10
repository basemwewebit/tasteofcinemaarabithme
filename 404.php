<?php get_header(); ?>

<main id="main-content" class="delight-404 min-h-[80vh] flex items-center justify-center relative overflow-hidden">
    <div class="absolute inset-0 bg-slate-950"></div>
    <div class="absolute inset-0 delight-404__vignette"></div>
    <div class="absolute inset-0 delight-404__grain" aria-hidden="true"></div>

    <div class="relative z-10 text-center px-6 max-w-2xl mx-auto">
        <!-- Film perforation top -->
        <div class="delight-404__sprocket" aria-hidden="true"></div>

        <div class="delight-404__card">
            <span class="delight-404__label">مشهد محذوف</span>
            <h1 class="delight-404__code">404</h1>
            <p class="delight-404__lead">المخرج قرر استبعاد هذا المشهد من النسخة النهائية.</p>
            <p class="delight-404__sub">الصفحة غير موجودة في الأرشيف.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="delight-404__cta">
                <span>العودة للعرض</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
        </div>

        <!-- Film perforation bottom -->
        <div class="delight-404__sprocket delight-404__sprocket--bottom" aria-hidden="true"></div>

        <?php get_template_part('template-parts/ads/ad-404'); ?>
    </div>
</main>

<?php get_footer(); ?>
