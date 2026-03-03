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
<div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" data-ad-slot="true">
    <div class="ad-slot-content h-[250px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-sm border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden relative w-full">
        <span class="absolute top-2 right-2 text-[10px] uppercase tracking-wider text-slate-300">إعلان</span>
        <!-- Ad Script Here -->
        <span class="ad-placeholder-text">Ad Space (250px min-height)</span>
    </div>
</div>

<!-- T008: Graceful Fallback Logic -->
<script>
    // This script simulates checking if an ad failed to load.
    // In production, you would bind this to the ad network's specific events (e.g., adsbygoogle.push error)
    // For this implementation, we check the content of the slot after a brief delay.
    document.addEventListener('DOMContentLoaded', function() {
        const adSlots = document.querySelectorAll('[data-ad-slot="true"]');
        
        adSlots.forEach(slot => {
            const content = slot.querySelector('.ad-slot-content');
            
            // Timeout to simulate ad loading attempt
            setTimeout(() => {
                // Determine if ad is empty. This condition (height < 50) is an example.
                // Replace with actual ad network empty check.
                const isEmpty = content.offsetHeight < 50 && !content.innerHTML.includes('iframe');
                
                if (isEmpty) {
                    // Gracefully collapse
                    slot.style.display = 'none';
                    slot.classList.add('collapsed-empty-ad');
                    console.log('Ad slot collapsed due to empty response.');
                }
            }, 3000); // Check after 3 seconds
        });
    });
</script>
