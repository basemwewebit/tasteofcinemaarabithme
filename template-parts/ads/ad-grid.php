<?php
$slot = isset($args['slot']) ? $args['slot'] : 'ad_slot_hero_banner'; // Default slot
mazaq_render_ad($slot, 'rectangle', 'col-span-1 w-full min-h-[300px] rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden shadow-sm');
?>

