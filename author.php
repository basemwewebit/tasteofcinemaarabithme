<?php get_header(); ?>
<?php
$author = get_queried_object();
$author_id = (int) $author->ID;
$role_title = function_exists('get_field') ? (string) get_field('author_role_title', 'user_' . $author_id) : '';
$twitter_url = function_exists('get_field') ? (string) get_field('twitter_url', 'user_' . $author_id) : '';
$website_url = function_exists('get_field') ? (string) get_field('website_url', 'user_' . $author_id) : '';
?>

<div class="bg-slate-50 dark:bg-slate-800 pt-20 pb-16 transition-colors">
    <div class="max-w-4xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12 text-center md:text-right">
            <div class="relative w-40 h-40 shrink-0"><?php echo get_avatar($author_id, 160, '', '', ['class' => 'w-full h-full object-cover rounded-full border-4 border-slate-100 dark:border-slate-700 shadow-xl', 'loading' => 'lazy']); ?></div>
            <div class="flex-1">
                <h1 class="text-display text-slate-900 dark:text-white mb-4 break-words"><?php echo esc_html(get_the_author_meta('display_name', $author_id)); ?></h1>
                <p class="text-slate-600 dark:text-slate-400 text-lg leading-relaxed mb-6 max-w-2xl"><?php echo esc_html(get_the_author_meta('description', $author_id)); ?></p>
                <?php if ($role_title) : ?><p class="text-primary font-bold mb-6"><?php echo esc_html($role_title); ?></p><?php endif; ?>
                <div class="flex items-center justify-center md:justify-start gap-6 pt-6">
                    <div><span class="text-sm text-slate-500">مراجعات</span><div class="text-xl font-bold text-slate-900 dark:text-white"><?php echo esc_html((string) count_user_posts($author_id, 'post', true)); ?></div></div>
                    <div><span class="text-sm text-slate-500">قوائم</span><div class="text-xl font-bold text-slate-900 dark:text-white"><?php echo esc_html((string) count_user_posts($author_id, 'list', true)); ?></div></div>
                    <?php if ($twitter_url) : ?><a href="<?php echo esc_url($twitter_url); ?>" aria-label="<?php esc_attr_e('تابعنا على تويتر', 'mazaq'); ?>" class="hover:text-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2 rounded-sm">Twitter</a><?php endif; ?>
                    <?php if ($website_url) : ?><a href="<?php echo esc_url($website_url); ?>" aria-label="<?php esc_attr_e('الموقع الرسمي', 'mazaq'); ?>" class="hover:text-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2 rounded-sm">Website</a><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 mb-10"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main id="main-content" class="flex-1 max-w-7xl mx-auto px-4 pb-20">
    <?php if (have_posts()) : ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10">
        <?php
            $post_index = 0;
            $ad_enabled = get_option('toc_ad_injection_enabled', false);
            $ad_interval = (int) get_option('toc_ad_injection_interval', 6);

            while (have_posts()) : the_post();
                $post_index++;
                get_template_part('template-parts/content/card-author');

                // T006: Inject Ad Slot
                if ($ad_enabled && $ad_interval > 0 && $post_index % $ad_interval === 0) {
                    get_template_part('template-parts/ad-slot', null, ['context' => 'author']);
                }

                if ($post_index % 6 === 0) {
                    get_template_part('template-parts/ads/ad-grid', null, ['slot' => 'ad_slot_archive_banner']);
                }
            endwhile;
        ?>
    </div>
    <?php else : ?>
    <div class="text-center py-20">
        <p class="text-slate-500 dark:text-slate-400 text-lg mb-4">لم يُنشر أي مقال بعد.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center gap-2 text-primary font-medium hover:underline">
            <span>تصفح أحدث المقالات</span>
        </a>
    </div>
    <?php endif; ?>
    <?php get_template_part('template-parts/navigation/pagination'); ?>
</main>

<?php get_footer(); ?>
