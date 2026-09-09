<?php
/**
 * Grid-cell ad: a one-column plate inside the feed grids, injected inline by
 * the archive feed and by infinite scroll.
 */
$slot = isset($args['slot']) ? $args['slot'] : 'ad_slot_hero_banner';
mazaq_render_ad($slot, 'rectangle');
