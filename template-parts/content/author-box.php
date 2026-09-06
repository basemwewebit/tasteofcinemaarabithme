<?php
$author_id = (int) get_the_author_meta('ID');

if (empty($author_id)) {
    return;
}

$author_name = (string) get_the_author_meta('display_name');

if ($author_name === '') {
    $author_name = (string) get_the_author_meta('user_login');
}

// A credit without a name cannot render.
if ($author_name === '') {
    return;
}

$author_bio  = (string) get_the_author_meta('description');
$author_link = get_author_posts_url($author_id);

$author_role = '';
if (function_exists('get_field')) {
    $role_field  = get_field('author_role_title', 'user_' . $author_id);
    $author_role = is_scalar($role_field) ? trim((string) $role_field) : '';
}

if ($author_bio === '') {
    $author_bio = sprintf(
        /* translators: %s: Author name */
        esc_html__('محرر في منصة Taste of Cinema العربية. يسعى %s لتقديم أفضل التحليلات والقوائم السينمائية لإثراء المحتوى العربي بأهم الأعمال الفنية حول العالم.', 'mazaq'),
        esc_html($author_name)
    );
}

// get_avatar() returns false when avatars are disabled site-wide; swap in
// the branded initial plate so the link never renders empty.
$avatar_html = get_avatar($author_id, 96, '', '', ['class' => 'author-box__avatar', 'loading' => 'lazy']);

if (!is_string($avatar_html) || $avatar_html === '') {
    $initial     = function_exists('mb_substr') ? mb_substr($author_name, 0, 1) : substr($author_name, 0, 1);
    $avatar_html = '<span class="author-box__avatar-plate" aria-hidden="true">' . esc_html($initial) . '</span>';
}
?>

<aside class="author-box" aria-labelledby="author-box-title">
    <div class="author-box__inner">
        <div class="author-box__media">
            <a href="<?php echo esc_url($author_link); ?>" class="author-box__avatar-link" aria-label="<?php echo esc_attr(sprintf(__('عرض أرشيف الكاتب: %s', 'mazaq'), $author_name)); ?>">
                <?php echo $avatar_html; ?>
            </a>
        </div>
        <div class="author-box__body">
            <p class="author-box__credit"><?php esc_html_e('بقلم', 'mazaq'); ?></p>
            <h2 id="author-box-title" class="author-box__name">
                <a href="<?php echo esc_url($author_link); ?>">
                    <?php echo esc_html($author_name); ?>
                </a>
            </h2>
            <?php if ('' !== $author_role) : ?>
                <p class="author-box__role"><?php echo esc_html($author_role); ?></p>
            <?php endif; ?>
            <p class="author-box__bio">
                <?php echo wp_kses_post($author_bio); ?>
            </p>
            <a href="<?php echo esc_url($author_link); ?>" class="author-box__link">
                <?php esc_html_e('عرض جميع مقالات الكاتب', 'mazaq'); ?>
            </a>
        </div>
    </div>
</aside>
