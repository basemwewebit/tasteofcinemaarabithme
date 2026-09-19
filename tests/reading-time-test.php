<?php

/**
 * Standalone self-check for the unified reading-time seam. No framework, no WP:
 * run with `php tests/reading-time-test.php`. Stubs the meta, post, and
 * formatting functions inc/helpers.php calls so the file can be included
 * in isolation.
 *
 * Seam under test: mazaq_reading_time() / mazaq_calculate_reading_time_minutes().
 * single.php, the Continuous Programme hero, Editorial cards, and archive rows
 * all read through this one interface; these checks pin the WPM policy, the
 * filter, the meta cache, and the label shape it promises them.
 *
 * Expected values are hand-worked literals (words ÷ wpm, ceiling), never
 * recomputed by the implementation under test.
 */

declare(strict_types=1);

require __DIR__ . '/rotation-test-helper.php';

$GLOBALS['__reading_fixtures'] = [];
$GLOBALS['__reading_meta'] = [];
$GLOBALS['__reading_wpm'] = 180;

if (!function_exists('add_action')) {
    function add_action(...$args): void {}
}
if (!function_exists('add_filter')) {
    function add_filter(...$args): void {}
}
if (!function_exists('get_post_field')) {
    function get_post_field(string $field, int $post_id) {
        return $GLOBALS['__reading_fixtures'][$post_id] ?? '';
    }
}
if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags(string $string, bool $remove_breaks = false): string {
        return strip_tags($string);
    }
}
if (!function_exists('strip_shortcodes')) {
    function strip_shortcodes(string $content): string {
        return (string) preg_replace('/\[[^\]]*\]/', '', $content);
    }
}
if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value): mixed {
        if ($hook === 'mazaq_reading_words_per_minute') {
            return $GLOBALS['__reading_wpm'];
        }
        return $value;
    }
}
if (!function_exists('get_post_meta')) {
    function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed {
        return $GLOBALS['__reading_meta'][$post_id][$key] ?? '';
    }
}
if (!function_exists('update_post_meta')) {
    function update_post_meta(int $post_id, string $key, mixed $value): bool {
        $GLOBALS['__reading_meta'][$post_id][$key] = $value;
        return true;
    }
}
if (!function_exists('__')) {
    function __(string $text, ?string $domain = null): string {
        return $text;
    }
}

require __DIR__ . '/../inc/helpers.php';

// 180 wpm default: 190 words ceil to 2 minutes.
$GLOBALS['__reading_fixtures'][11] = trim(str_repeat('سينما ', 190));
rotation_test_check(
    mazaq_calculate_reading_time_minutes(11) === 2,
    '190 words at default 180 wpm calculate to 2 minutes'
);

// Singular label: exactly 180 words read as "1 دقيقة قراءة".
$GLOBALS['__reading_fixtures'][12] = trim(str_repeat('سينما ', 180));
rotation_test_check(
    mazaq_reading_time(12) === '1 دقيقة قراءة',
    '180 words read as singular minute label'
);

// Plural label: 190 words read as "2 دقائق قراءة".
rotation_test_check(
    mazaq_reading_time(11) === '2 دقائق قراءة',
    '190 words read as plural minute label'
);

// Filter honored: 190 words at 200 wpm drop to 1 minute.
$GLOBALS['__reading_wpm'] = 200;
$GLOBALS['__reading_fixtures'][13] = trim(str_repeat('سينما ', 190));
rotation_test_check(
    mazaq_calculate_reading_time_minutes(13) === 1,
    'wpm filter override changes the calculation'
);
rotation_test_check(
    mazaq_reading_time(13) === '1 دقيقة قراءة',
    'wpm filter override changes the label'
);
$GLOBALS['__reading_wpm'] = 180;

// Cached meta wins over recomputation: seeded 7 survives empty content.
$GLOBALS['__reading_meta'][14]['_mazaq_reading_time_minutes'] = 7;
$GLOBALS['__reading_fixtures'][14] = '';
rotation_test_check(
    mazaq_reading_time(14) === '7 دقائق قراءة',
    'cached meta preferred without recomputing'
);

// First read warms the cache for the next one.
unset($GLOBALS['__reading_meta'][15]);
$GLOBALS['__reading_fixtures'][15] = trim(str_repeat('سينما ', 190));
rotation_test_check(mazaq_reading_time(15) === '2 دقائق قراءة', 'first read computes');
rotation_test_check(
    ($GLOBALS['__reading_meta'][15]['_mazaq_reading_time_minutes'] ?? 0) === 2,
    'first read stores computed minutes in meta'
);

// Floor: empty content with no cache still reads 1 minute, never 0.
unset($GLOBALS['__reading_meta'][16]);
$GLOBALS['__reading_fixtures'][16] = '';
rotation_test_check(
    mazaq_reading_time(16) === '1 دقيقة قراءة',
    'empty content floors at 1 minute'
);

echo 'reading-time: ' . ($GLOBALS['rotation_test_passed'] ?? 0) . " seam checks passed\n";
