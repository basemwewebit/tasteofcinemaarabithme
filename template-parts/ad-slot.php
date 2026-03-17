<?php
/**
 * Ad Slot Template Part
 * 
 * Used for injecting advertisements into grids or other layouts.
 * 
 * @var array $args Contains variables passed to the template:
 * - 'context' (string) Layout context (e.g., 'grid', 'single'). Default 'grid'.
 */

$context = $args['context'] ?? 'grid';

// Depending on the context, we apply appropriate generic wrapper classes.
$wrapper_classes = ['ad-slot-wrapper', "ad-slot-{$context}"];

// In a real scenario, this would output the actual ad code from an ad network (e.g., Google AdSense)
// For now, it outputs a placeholder block styled via CSS to prevent CLS.
?>
<div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" data-ad-slot="true" data-ad-container="true" data-expects-network-ad="0">
    <div class="ad-slot-content h-[250px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-sm border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden relative w-full">
        <span class="absolute top-2 right-2 text-[10px] uppercase tracking-wider text-slate-300">إعلان</span>
        <!-- Ad Script Here -->
        <span class="ad-placeholder-text">Ad Space (250px min-height)</span>
    </div>
</div>
