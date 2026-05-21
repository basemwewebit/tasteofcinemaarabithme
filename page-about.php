<?php
/* Template Name: About Mazaq */
get_header();
?>

<main id="main-content" class="max-w-5xl mx-auto px-4 pt-16 pb-20">
    <header class="mb-12 text-center">
        <p class="home-section__kicker"><?php esc_html_e('عن المجلة', 'mazaq'); ?></p>
        <h1 class="single-article__title"><?php esc_html_e('مذاق السينما: كل إطار يحكي قصة', 'mazaq'); ?></h1>
        <p class="mt-6 text-lg leading-9 text-slate-600 dark:text-slate-300 max-w-3xl mx-auto"><?php esc_html_e('مجلة عربية مستقلة تقرأ الأفلام كخبرة ثقافية وجمالية: مراجعات، قوائم، تحليلات، وترشيحات تساعد القارئ على اكتشاف ما يستحق المشاهدة.', 'mazaq'); ?></p>
    </header>

    <div class="grid gap-6 md:grid-cols-3">
        <section class="film-infobox">
            <p class="film-infobox__kicker"><?php esc_html_e('رسالتنا', 'mazaq'); ?></p>
            <h2 class="film-infobox__title"><?php esc_html_e('اكتشاف أعمق', 'mazaq'); ?></h2>
            <p class="mt-4 leading-8 text-slate-600 dark:text-slate-300"><?php esc_html_e('نفتح باباً للسينما العالمية والعربية من خلال كتابة واضحة وممتعة لا تفترض معرفة مسبقة.', 'mazaq'); ?></p>
        </section>
        <section class="film-infobox">
            <p class="film-infobox__kicker"><?php esc_html_e('أسلوبنا', 'mazaq'); ?></p>
            <h2 class="film-infobox__title"><?php esc_html_e('تحرير لا ضجيج', 'mazaq'); ?></h2>
            <p class="mt-4 leading-8 text-slate-600 dark:text-slate-300"><?php esc_html_e('نفضّل الاختيارات المحررة بعناية على تدفق الأخبار السريع، ونكتب للقارئ قبل الخوارزمية.', 'mazaq'); ?></p>
        </section>
        <section class="film-infobox">
            <p class="film-infobox__kicker"><?php esc_html_e('تواصل', 'mazaq'); ?></p>
            <h2 class="film-infobox__title"><?php esc_html_e('شاركنا اقتراحك', 'mazaq'); ?></h2>
            <p class="mt-4 leading-8 text-slate-600 dark:text-slate-300"><?php esc_html_e('للتعاون أو اقتراح ملف سينمائي، استخدم صفحة الاتصال وسنعود إليك.', 'mazaq'); ?></p>
        </section>
    </div>

    <?php get_template_part('template-parts/common/newsletter', null, ['context' => 'about']); ?>
</main>

<?php get_footer(); ?>
