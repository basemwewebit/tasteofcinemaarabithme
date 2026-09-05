<?php

declare(strict_types=1);

/**
 * The programme index — the margin's living table of contents.
 *
 * Wide screens (>=1360px) render the index as a sticky programme note in the
 * margin column: scroll-spy ticks the current section like a "now showing"
 * marker, and a gold fill creeps along the hairline. Everything narrower
 * gets the floating progress puck with an accessible jump sheet.
 *
 * Behaviour lives in assets/js/app-single.js; styling in the
 * "Single — The Festival Programme" and "Single — Projection Room" CSS blocks.
 */

$headings = mazaq_extract_article_headings(get_the_ID());

if (count($headings) < 3) {
    return;
}

/**
 * The index already prints the rank in its own mono column, so a heading that
 * numbers itself ("18. Bring Her Back (2025)") must not print that rank again
 * inside the label. In an RTL rail the leading "18." also bidi-jumps to the
 * far end of an LTR film title, which is the ".18 … 18" doubling in the bug
 * report. Strip one leading rank marker for display only; schema + anchors
 * keep reading the full heading text.
 */
$mazaq_rail_label = static function (string $text): string {
    $clean = (string) preg_replace('/^[0-9]{1,3}\s*[.)\-–—:]\s*/u', '', ltrim($text));
    return $clean !== '' ? $clean : $text;
};
?>
<nav class="programme-index programme__note" data-reading-rail aria-labelledby="programme-index-title">
    <h2 id="programme-index-title" class="programme-index__title"><?php esc_html_e('في هذا المقال', 'mazaq'); ?></h2>
    <div class="programme-index__reel">
        <ol class="programme-index__list">
            <?php foreach ($headings as $heading) : ?>
                <li class="programme-index__item<?php echo $heading['level'] > 2 ? ' programme-index__item--nested' : ''; ?>" data-rail-item="<?php echo esc_attr($heading['id']); ?>">
                    <a class="programme-index__link" href="#<?php echo esc_attr($heading['id']); ?>">
                        <span class="programme-index__marker" aria-hidden="true"></span>
                        <?php $index = mazaq_heading_index($heading['number']); ?>
                        <?php if ($index !== '') : ?>
                            <span class="programme-index__index num" aria-hidden="true"><?php echo esc_html($index); ?></span>
                        <?php endif; ?>
                        <span class="programme-index__label" dir="auto"><?php echo esc_html($mazaq_rail_label($heading['text'])); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

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
                        <span class="reading-sheet__label" dir="auto"><?php echo esc_html($mazaq_rail_label($heading['text'])); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
</div>
