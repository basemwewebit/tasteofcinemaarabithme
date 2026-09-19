<?php

/**
 * Shared check helper for the standalone rotation self-checks. Explicit
 * failure output instead of assert(): zend.assertions=-1 (this environment's
 * default) compiles assert() to a no-op.
 */

declare(strict_types=1);

if (!function_exists('rotation_test_check')) {
    function rotation_test_check(bool $condition, string $message = ''): void
    {
        if (!$condition) {
            $message = $message !== '' ? $message : 'line ' . debug_backtrace()[0]['line'];
            fwrite(STDERR, "FAIL: {$message}\n");
            exit(1);
        }

        $GLOBALS['rotation_test_passed'] = ($GLOBALS['rotation_test_passed'] ?? 0) + 1;
    }
}
