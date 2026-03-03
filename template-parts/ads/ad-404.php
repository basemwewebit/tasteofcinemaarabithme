<?php
/**
 * Ad component for 404 page
 */
?>
<div class="max-w-4xl mx-auto px-6 mt-12 mb-8 relative z-20">
    <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-4 md:p-8 flex flex-col items-center">
        <span class="text-slate-500 text-[10px] font-bold tracking-widest uppercase mb-4 opacity-50">إعلان</span>
        <div class="w-full flex justify-center overflow-hidden">
            <?php mazaq_render_ad('ad_slot_404_banner', 'horizontal', 'w-full min-h-[90px] md:min-h-[120px]'); ?>
        </div>
    </div>
</div>
