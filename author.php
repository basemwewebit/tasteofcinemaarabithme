<?php get_header(); ?>
<?php
$author = get_queried_object();
$author_id = (int) $author->ID;
$role_title = function_exists('get_field') ? (string) get_field('author_role_title', 'user_' . $author_id) : '';
$twitter_url = function_exists('get_field') ? (string) get_field('twitter_url', 'user_' . $author_id) : '';
$website_url = function_exists('get_field') ? (string) get_field('website_url', 'user_' . $author_id) : '';
?>

<div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 pt-16 pb-12 transition-colors">
    <div class="max-w-4xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12 text-center md:text-right">
            <div class="relative w-40 h-40 shrink-0"><?php echo get_avatar($author_id, 160, '', '', ['class' => 'w-full h-full object-cover rounded-full border-4 border-slate-100 dark:border-slate-700 shadow-xl']); ?></div>
            <div class="flex-1">
                <h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4 leading-[1.25]"><?php echo esc_html(get_the_author_meta('display_name', $author_id)); ?></h1>
                <p class="text-slate-600 dark:text-slate-400 text-lg leading-relaxed mb-6 max-w-2xl"><?php echo esc_html(get_the_author_meta('description', $author_id)); ?></p>
                <?php if ($role_title) : ?><p class="text-primary font-bold mb-4"><?php echo esc_html($role_title); ?></p><?php endif; ?>
                <div class="flex items-center justify-center md:justify-start gap-6 font-mono border-t border-slate-200 dark:border-slate-700 pt-6">
                    <div><span class="text-sm text-slate-500">مراجعات</span><div class="text-xl font-bold text-slate-900 dark:text-white"><?php echo esc_html((string) count_user_posts($author_id, 'post', true)); ?></div></div>
                    <div><span class="text-sm text-slate-500">قوائم</span><div class="text-xl font-bold text-slate-900 dark:text-white"><?php echo esc_html((string) count_user_posts($author_id, 'post', true)); ?></div></div>
                    <?php if ($twitter_url) : ?><a href="<?php echo esc_url($twitter_url); ?>" class="hover:text-primary">Twitter</a><?php endif; ?>
                    <?php if ($website_url) : ?><a href="<?php echo esc_url($website_url); ?>" class="hover:text-primary">Website</a><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<main class="flex-1 max-w-7xl mx-auto px-4 py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (have_posts()) : while (have_posts()) : the_post(); get_template_part('template-parts/content/card-author'); endwhile; endif; ?>
    </div>
    <?php get_template_part('template-parts/navigation/pagination'); ?>
</main>

<?php get_footer(); ?>
