<?php
/* Template Name: Ad Support Guide */

declare(strict_types=1);

get_header();

$contact_url = home_url('/contact-us/');
$contact_page = get_page_by_path('contact-us');
if ($contact_page instanceof WP_Post) {
    $contact_url = get_permalink($contact_page) ?: $contact_url;
}
?>

<main id="main-content" class="max-w-5xl mx-auto px-4 py-12 md:py-16">
    <header class="text-center mb-10">
        <p class="inline-flex items-center justify-center bg-primary/20 text-slate-800 dark:text-primary text-xs font-bold rounded-full px-4 py-1.5 mb-4">دعم الموقع</p>
        <h1 class="text-display text-slate-900 dark:text-white mb-4">كيف تدعم الموقع مع مانع الإعلانات؟</h1>
        <p class="text-slate-600 dark:text-slate-300 max-w-3xl mx-auto leading-8">نحن نحترم اختيارك بالكامل. إذا أحببت الاستمرار في دعم المحتوى المجاني، يمكنك السماح بالإعلانات الخفيفة لهذا الموقع فقط خلال ثوانٍ.</p>
    </header>

    <section class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 md:p-8 mb-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">قبل البدء</h2>
        <ul class="list-disc pr-6 space-y-2 text-slate-700 dark:text-slate-300 leading-8">
            <li>لا نطلب تعطيل المانع بالكامل، فقط إضافة الموقع إلى القائمة المسموح بها.</li>
            <li>نحاول إبقاء الإعلانات خفيفة وغير مزعجة قدر الإمكان.</li>
            <li>إذا واجهت أي مشكلة، تواصل معنا وسنساعدك مباشرة.</li>
        </ul>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <article class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">uBlock Origin</h3>
            <ol class="list-decimal pr-6 space-y-2 text-slate-700 dark:text-slate-300 leading-8">
                <li>افتح الصفحة على موقعنا.</li>
                <li>اضغط أيقونة uBlock Origin من المتصفح.</li>
                <li>اضغط زر الإيقاف للموقع الحالي فقط.</li>
                <li>حدّث الصفحة وستظهر الإعلانات بشكل طبيعي.</li>
            </ol>
        </article>

        <article class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">AdBlock Plus</h3>
            <ol class="list-decimal pr-6 space-y-2 text-slate-700 dark:text-slate-300 leading-8">
                <li>افتح أيقونة AdBlock Plus.</li>
                <li>اختر خيار السماح على هذا الموقع.</li>
                <li>تأكد أن الموقع في القائمة البيضاء.</li>
                <li>أعد تحميل الصفحة.</li>
            </ol>
        </article>

        <article class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Brave Shields</h3>
            <ol class="list-decimal pr-6 space-y-2 text-slate-700 dark:text-slate-300 leading-8">
                <li>اضغط أيقونة الأسد بجانب شريط العنوان.</li>
                <li>عطّل الحماية لهذا الموقع فقط.</li>
                <li>اترك باقي المواقع كما هي.</li>
                <li>حدّث الصفحة.</li>
            </ol>
        </article>

        <article class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Safari على iPhone</h3>
            <ol class="list-decimal pr-6 space-y-2 text-slate-700 dark:text-slate-300 leading-8">
                <li>افتح الموقع في Safari.</li>
                <li>اضغط زر aA بجانب العنوان.</li>
                <li>اختر إيقاف مانع المحتوى لهذا الموقع.</li>
                <li>أعد تحميل الصفحة.</li>
            </ol>
        </article>
    </section>

    <section class="bg-primary/10 border border-primary/30 rounded-2xl p-6 md:p-8 text-center">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">هل تحتاج مساعدة؟</h2>
        <p class="text-slate-700 dark:text-slate-300 mb-5 leading-8">إذا لم تنجح الخطوات، أرسل لنا نوع المتصفح والإضافة وسنرسل لك خطوات دقيقة تناسب جهازك.</p>
        <a href="<?php echo esc_url($contact_url); ?>" class="inline-flex items-center justify-center bg-primary hover:bg-slate-900 dark:hover:bg-white text-slate-900 hover:text-white dark:hover:text-slate-900 font-bold px-7 py-3 rounded-xl transition-colors">تواصل معنا</a>
    </section>
</main>

<?php get_footer(); ?>
