<?php

declare(strict_types=1);

$headings = mazaq_extract_article_headings(get_the_ID());

if (count($headings) < 3) {
    return;
}
?>
<aside class="listicle-toc" aria-labelledby="listicle-toc-title">
    <p class="listicle-toc__kicker"><?php esc_html_e('خريطة القراءة', 'mazaq'); ?></p>
    <h2 id="listicle-toc-title" class="listicle-toc__title"><?php esc_html_e('في هذا المقال', 'mazaq'); ?></h2>
    <nav aria-label="<?php esc_attr_e('فهرس المقال', 'mazaq'); ?>">
        <ol class="listicle-toc__list">
            <?php foreach ($headings as $heading) : ?>
                <li class="<?php echo $heading['level'] > 2 ? 'listicle-toc__item listicle-toc__item--nested' : 'listicle-toc__item'; ?>">
                    <a href="#<?php echo esc_attr($heading['id']); ?>">
                        <?php $index = mazaq_heading_index($heading['number']); ?>
                        <?php if ($index !== '') : ?>
                            <span class="listicle-toc__index num" aria-hidden="true"><?php echo esc_html($index); ?></span>
                        <?php endif; ?>
                        <span><?php echo esc_html($heading['text']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
</aside>
