<?php

declare(strict_types=1);

/**
 * The Living Reel: a scroll-spy reading index.
 *
 * Wide desktop (>=1360px, where the margin clears the centred column) renders a fixed
 * vertical rail of dots and ranks (aria-hidden, mouse-driven enhancement; the
 * in-content listicle TOC remains the accessible equivalent). Section titles are a
 * hover affordance, not an always-open label -- an open label makes the rail wide
 * enough to sit on the article. Everything narrower renders a floating progress puck
 * that opens an accessible jump sheet.
 *
 * Behaviour lives in assets/js/app-single.js; styling in the
 * "Single — Projection Room + Living Reel" CSS block.
 */

$headings = mazaq_extract_article_headings(get_the_ID());

if (count($headings) < 3) {
    return;
}
?>
<aside class="reading-rail" data-reading-rail aria-hidden="true">
    <div class="reading-rail__inner">
        <span class="reading-rail__track" aria-hidden="true"><span class="reading-rail__fill" data-rail-fill></span></span>
        <ol class="reading-rail__list">
            <?php foreach ($headings as $heading) : ?>
                <li class="reading-rail__item<?php echo $heading['level'] > 2 ? ' reading-rail__item--nested' : ''; ?>" data-rail-item="<?php echo esc_attr($heading['id']); ?>">
                    <a class="reading-rail__link" href="#<?php echo esc_attr($heading['id']); ?>" tabindex="-1">
                        <span class="reading-rail__dot" aria-hidden="true"></span>
                        <?php $index = mazaq_heading_index($heading['number']); ?>
                        <?php if ($index !== '') : ?>
                            <span class="reading-rail__index num" aria-hidden="true"><?php echo esc_html($index); ?></span>
                        <?php endif; ?>
                        <span class="reading-rail__label"><?php echo esc_html($heading['text']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</aside>

<div class="reading-puck" data-reading-puck>
    <button type="button" class="reading-puck__toggle" data-puck-toggle aria-expanded="false" aria-controls="reading-sheet">
        <span class="reading-puck__ring" data-puck-ring aria-hidden="true"></span>
        <span class="reading-puck__pct num" aria-hidden="true"><span data-puck-num>0</span><span class="reading-puck__sign">%</span></span>
        <span class="sr-only"><?php esc_html_e('فهرس القراءة', 'mazaq'); ?></span>
    </button>
    <nav class="reading-sheet" id="reading-sheet" data-reading-sheet aria-label="<?php esc_attr_e('فهرس القراءة', 'mazaq'); ?>" hidden>
        <p class="reading-sheet__head"><?php esc_html_e('في هذا المقال', 'mazaq'); ?></p>
        <ol class="reading-sheet__list">
            <?php foreach ($headings as $heading) : ?>
                <li class="reading-sheet__item<?php echo $heading['level'] > 2 ? ' reading-sheet__item--nested' : ''; ?>" data-sheet-item="<?php echo esc_attr($heading['id']); ?>">
                    <a class="reading-sheet__link" href="#<?php echo esc_attr($heading['id']); ?>">
                        <?php $index = mazaq_heading_index($heading['number']); ?>
                        <?php if ($index !== '') : ?>
                            <span class="reading-sheet__index num" aria-hidden="true"><?php echo esc_html($index); ?></span>
                        <?php endif; ?>
                        <span class="reading-sheet__label"><?php echo esc_html($heading['text']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
</div>
