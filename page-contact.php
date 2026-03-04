<?php
/* Template Name: Contact */
get_header();

$contact_email = function_exists('get_field') ? (string) get_field('contact_email', 'option') : get_option('admin_email');
$contact_address = function_exists('get_field') ? (string) get_field('contact_address', 'option') : '';
$status = isset($_GET['contact_status']) ? sanitize_text_field((string) $_GET['contact_status']) : '';
?>

<div class="bg-slate-900 text-white py-16 md:py-24 mb-12 relative overflow-hidden text-center">
    <div class="max-w-4xl mx-auto px-4 relative z-10 w-full">
        <h1 class="text-4xl md:text-5xl font-bold mb-6 text-white leading-[1.25]">اتصل بنا</h1>
        <p class="text-xl text-slate-400 font-medium max-w-2xl mx-auto">نحن دائماً هنا للاستماع إليك!</p>
    </div>
</div>

<main class="flex-1 max-w-7xl mx-auto px-4 py-8 mb-16 w-full">
    <?php if ($status === 'success') : ?><p class="mb-6 p-4 rounded bg-green-100 text-green-800">تم إرسال رسالتك بنجاح.</p><?php endif; ?>
    <?php if ($status === 'error') : ?><p class="mb-6 p-4 rounded bg-red-100 text-red-800">حدث خطأ أثناء إرسال الرسالة.</p><?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-12">
        <div class="w-full lg:w-2/3 bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-8 border-r-4 border-primary pr-4">أرسل رسالة</h2>
            <form method="post" class="flex flex-col gap-6">
                <?php wp_nonce_field('mazaq_contact_form', 'mazaq_contact_nonce'); ?>
                <input type="hidden" name="website" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="text" name="name" placeholder="الاسم الكامل" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 outline-none focus:border-primary text-slate-800 dark:text-white">
                    <input type="email" name="email" placeholder="البريد الإلكتروني" required dir="ltr" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 outline-none focus:border-primary text-slate-800 dark:text-white text-left">
                </div>
                <input type="text" name="subject" placeholder="الموضوع" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 outline-none focus:border-primary text-slate-800 dark:text-white">
                <textarea name="message" rows="6" placeholder="نص الرسالة" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 outline-none focus:border-primary text-slate-800 dark:text-white resize-none"></textarea>
                <button type="submit" name="mazaq_contact_submit" value="1" class="bg-primary hover:bg-slate-900 dark:hover:bg-white text-slate-900 hover:text-white dark:hover:text-slate-900 font-bold text-lg px-8 py-4 rounded-xl transition-all shadow-md self-start w-full md:w-auto">إرسال الرسالة</button>
            </form>
        </div>

        <div class="w-full lg:w-1/3 flex flex-col gap-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">معلومات التواصل</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-3" dir="ltr"><?php echo esc_html($contact_email); ?></p>
                <p class="text-slate-600 dark:text-slate-400"><?php echo nl2br(esc_html($contact_address)); ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">تابعنا على</h3>
                <div class="flex gap-4 flex-wrap">
                    <?php if (function_exists('have_rows') && have_rows('social_links', 'option')) : ?>
                        <?php while (have_rows('social_links', 'option')) : the_row();
                            $platform_name = get_sub_field('platform_name');
                            $url = get_sub_field('url');
                        ?>
                            <a href="<?php echo esc_url((string)$url); ?>" class="flex items-center justify-center px-6 py-2 rounded-full border border-slate-200 dark:border-slate-600 transition-colors bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 group" target="_blank" rel="noopener noreferrer">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-100 group-hover:text-primary transition-colors"><?php echo esc_html((string)$platform_name); ?></span>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-500">لا توجد روابط مسجلة.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
