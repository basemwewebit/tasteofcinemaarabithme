<?php get_header(); ?>
<?php
$author = get_queried_object();
$author_id = $author instanceof WP_User ? (int) $author->ID : 0;
$role_title = function_exists('get_field') ? (string) get_field('author_role_title', 'user_' . $author_id) : '';
$twitter_url = function_exists('get_field') ? (string) get_field('twitter_url', 'user_' . $author_id) : '';
$website_url = function_exists('get_field') ? (string) get_field('website_url', 'user_' . $author_id) : '';
$post_count = (int) count_user_posts($author_id, 'post', true);
$latest_author_posts = get_posts([
    'author' => $author_id,
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 24,
    'fields' => 'ids',
]);
$author_category_counts = [];
foreach ($latest_author_posts as $author_post_id) {
    foreach (get_the_category((int) $author_post_id) as $author_category) {
        $author_category_counts[$author_category->term_id] = [
            'name' => $author_category->name,
            'url' => get_category_link($author_category->term_id),
            'count' => ($author_category_counts[$author_category->term_id]['count'] ?? 0) + 1,
        ];
    }
}
usort($author_category_counts, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);
$author_category_counts = array_slice($author_category_counts, 0, 3);
?>

<header class="archive-head">
    <div class="archive-head__inner max-w-7xl mx-auto px-4">
        <div class="archive-head__body md:grid md:grid-cols-[minmax(0,1fr)_minmax(0,10rem)] md:items-center md:gap-10">
            <div class="archive-head__content">
                <span class="archive-head__pill">
                    <span class="archive-head__tick" aria-hidden="true"></span>
                    <?php echo $role_title ? esc_html($role_title) : esc_html__('ملف الكاتب', 'mazaq'); ?>
                </span>
                <h1 class="archive-head__title"><?php echo esc_html(get_the_author_meta('display_name', $author_id)); ?></h1>
                <?php $author_bio = get_the_author_meta('description', $author_id); if ($author_bio) : ?>
                    <p class="archive-head__desc"><?php echo esc_html($author_bio); ?></p>
                <?php endif; ?>
                <p class="archive-head__meta">
                    <span class="archive-head__count num"><?php echo esc_html((string) $post_count); ?></span>
                    <span><?php esc_html_e('مقالات', 'mazaq'); ?></span>
                    <span class="archive-head__sep" aria-hidden="true"></span>
                    <span class="archive-head__count num"><?php echo esc_html((string) count_user_posts($author_id, 'list', true)); ?></span>
                    <span><?php esc_html_e('قوائم', 'mazaq'); ?></span>
                    <?php if ($twitter_url) : ?>
                        <a href="<?php echo esc_url($twitter_url); ?>" aria-label="<?php esc_attr_e('تابعنا على تويتر', 'mazaq'); ?>" class="archive-head__link">Twitter</a>
                    <?php endif; ?>
                    <?php if ($website_url) : ?>
                        <a href="<?php echo esc_url($website_url); ?>" aria-label="<?php esc_attr_e('الموقع الرسمي', 'mazaq'); ?>" class="archive-head__link">Website</a>
                    <?php endif; ?>
                </p>
            </div>
            <div class="archive-head__avatar">
                <?php $author_avatar = get_avatar($author_id, 160, '', '', ['class' => 'w-full h-full object-cover', 'loading' => 'lazy']); ?>
                <?php if ($author_avatar) : ?>
                    <?php echo $author_avatar; ?>
                <?php else : ?>
                    <span class="archive-head__avatar-fallback" aria-hidden="true"><?php echo esc_html(mb_substr(get_the_author_meta('display_name', $author_id), 0, 1)); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 mb-10"><?php mazaq_render_ad('ad_slot_archive_banner', 'horizontal'); ?></div>

<main id="main-content" class="max-w-7xl mx-auto px-4 pb-20">
    <section class="archive-collections" aria-labelledby="archive-collections-title">
        <h2 id="archive-collections-title" class="archive-collections__title"><?php esc_html_e('اهتمامات وسلاسل بارزة', 'mazaq'); ?></h2>
        <?php if (!empty($author_category_counts)) : ?>
            <div class="archive-collections__grid">
                <?php foreach ($author_category_counts as $author_category_stat) : ?>
                    <a href="<?php echo esc_url((string) $author_category_stat['url']); ?>" class="category-row__item">
                        <span class="category-row__name"><?php echo esc_html((string) $author_category_stat['name']); ?></span>
                        <span class="category-row__count num"><?php echo esc_html(sprintf(__('%d مقال', 'mazaq'), (int) $author_category_stat['count'])); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="archive-collections__empty"><?php esc_html_e('ستظهر هنا اهتمامات الكاتب بعد نشر المزيد من المقالات.', 'mazaq'); ?></p>
        <?php endif; ?>
    </section>

    <?php get_template_part('template-parts/archive/archive-feed', null, [
        'ad_context' => 'author',
        'empty_message' => __('لم يُنشر أي مقال بعد.', 'mazaq'),
        'empty_link' => true,
    ]); ?>
</main>

<?php get_footer(); ?>
