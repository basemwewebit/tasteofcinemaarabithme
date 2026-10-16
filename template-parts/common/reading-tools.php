<?php
/**
 * The Reading preferences panel: a floating button on every page that opens
 * the reader's text size and display switches. JS-only (app.js unhides it);
 * the choices themselves are applied in <head> by reading-preferences.js.
 */
$switches = [
    'contrast' => __('تباين عالٍ', 'mazaq'),
    'underline' => __('تسطير الروابط', 'mazaq'),
    'font' => __('خط أوضح', 'mazaq'),
    'motion' => __('إيقاف الحركة', 'mazaq'),
];
?>
<div class="reading-tools" data-reading-tools hidden>
    <button type="button" class="reading-tools__toggle" aria-expanded="false" aria-controls="reading-tools-panel" aria-label="<?php esc_attr_e('أدوات القراءة', 'mazaq'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="9.25" />
            <circle cx="12" cy="7.4" r="1.35" fill="currentColor" stroke="none" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.2 12 11l4.5-.8M12 11v3.2m0 0-2.2 3.6M12 14.2l2.2 3.6" />
        </svg>
    </button>

    <div id="reading-tools-panel" class="reading-tools__panel" role="group" aria-labelledby="reading-tools-title" hidden>
        <h2 id="reading-tools-title" class="reading-tools__title"><?php esc_html_e('أدوات القراءة', 'mazaq'); ?></h2>

        <div class="reading-tools__size" role="group" aria-labelledby="reading-tools-size-label">
            <span id="reading-tools-size-label" class="reading-tools__label"><?php esc_html_e('حجم النص', 'mazaq'); ?></span>
            <div class="reading-tools__stepper">
                <button type="button" class="reading-tools__step" data-size-step="-1" aria-label="<?php esc_attr_e('تصغير النص', 'mazaq'); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M5 12h14" /></svg>
                </button>
                <output class="reading-tools__value num" data-size-value aria-live="polite">100%</output>
                <button type="button" class="reading-tools__step" data-size-step="1" aria-label="<?php esc_attr_e('تكبير النص', 'mazaq'); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                </button>
            </div>
        </div>

        <ul class="reading-tools__switches">
            <?php foreach ($switches as $key => $label) : ?>
                <li>
                    <button type="button" class="reading-tools__switch" data-pref="<?php echo esc_attr($key); ?>" aria-pressed="false">
                        <span><?php echo esc_html($label); ?></span>
                        <span class="reading-tools__track" aria-hidden="true"></span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <button type="button" class="reading-tools__reset" data-reading-reset><?php esc_html_e('إعادة الضبط', 'mazaq'); ?></button>
    </div>
</div>
