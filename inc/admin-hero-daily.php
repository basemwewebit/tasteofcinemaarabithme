<?php

/**
 * Hero daily rotation: a thin Content rotation feature. Rotation, scheduling,
 * and the dashboard widget live in the engine (inc/content-rotation.php);
 * this file is configuration plus the schedule entry points the shared
 * settings page calls back into.
 */

declare(strict_types=1);

const MAZAQ_HERO_DAILY_EVENT = 'mazaq_hero_daily_rotation';
const MAZAQ_HERO_DAILY_OPTION = 'mazaq_hero_daily_state';
const MAZAQ_HERO_DAILY_REGENERATE_ACTION = 'mazaq_hero_daily_regenerate';
const MAZAQ_HERO_DAILY_REGENERATE_NONCE = 'mazaq_hero_daily_regenerate_nonce';
const MAZAQ_HERO_DAILY_QUICK_SAVE_ACTION = 'mazaq_hero_daily_quick_save';
const MAZAQ_HERO_DAILY_QUICK_SAVE_NONCE = 'mazaq_hero_daily_quick_save_nonce';

function mazaq_hero_daily_config(): array
{
    return [
        'option' => MAZAQ_HERO_DAILY_OPTION,
        'event' => MAZAQ_HERO_DAILY_EVENT,
        'date_key' => 'rotation_date',
        'batch_key' => 'hero_post_ids',
        'extra_state' => [],
        'settings_enabled_key' => 'hero_enabled',
        'settings_count_key' => 'hero_count',
        'max_count' => 3,
        'cron_forces' => true,
        'widget_id' => 'mazaq-hero-daily-widget',
        'widget_title' => 'مشاركات Hero لهذا اليوم',
        'regenerate_action' => MAZAQ_HERO_DAILY_REGENERATE_ACTION,
        'regenerate_nonce' => MAZAQ_HERO_DAILY_REGENERATE_NONCE,
        'quick_save_action' => MAZAQ_HERO_DAILY_QUICK_SAVE_ACTION,
        'quick_save_nonce' => MAZAQ_HERO_DAILY_QUICK_SAVE_NONCE,
        'widget' => [
            'saved_flag' => 'mazaq_hero_saved',
            'saved_message' => 'تم حفظ إعدادات Hero.',
            'disabled_message' => 'تدوير Hero اليومي معطل حالياً.',
            'empty_message' => 'لا توجد عناصر Hero متاحة حالياً.',
            'enable_label' => 'تفعيل تدوير Hero',
            'count_id' => 'mazaq-hero-count',
            'count_label' => 'عدد عناصر Hero',
            'regenerate_pattern' => '🔄 تجديد %d عناصر Hero',
            'view_label' => 'عرض',
            'edit_label' => 'تعديل',
        ],
    ];
}

/**
 * Schedule the hero daily cron event. Kept as a named entry point: the
 * shared settings page reschedules through it.
 */
function mazaq_hero_daily_schedule_event(): void
{
    mazaq_rotation_schedule_event(mazaq_hero_daily_config());
}

/**
 * Clear the hero scheduled cron event. Kept as a named entry point: the
 * shared settings page reschedules through it.
 */
function mazaq_hero_daily_clear_event(): void
{
    mazaq_rotation_clear_event(mazaq_hero_daily_config());
}

mazaq_rotation_register_feature(mazaq_hero_daily_config());
