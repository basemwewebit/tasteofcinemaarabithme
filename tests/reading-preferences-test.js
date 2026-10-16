/**
 * Standalone self-check for Reading preferences: what a reader's saved choices
 * read back as, and the one-time move of the old article text size.
 * No framework: run with `node tests/reading-preferences-test.js`.
 */

'use strict';

const assert = require('assert');
const { readPreferences, textScale } = require('../assets/js/reading-preferences.js');

const defaults = { size: 0, contrast: false, underline: false, font: false, motion: false };

// A first visit reads as the defaults; stopped motion follows the device.
assert.deepStrictEqual(readPreferences(null, null, false), defaults);
assert.deepStrictEqual(readPreferences(null, null, true), { ...defaults, motion: true });

// Saved choices read back exactly as they were saved.
const saved = { size: 3, contrast: true, underline: true, font: true, motion: false };
assert.deepStrictEqual(readPreferences(JSON.stringify(saved), null, true), saved);

// Broken or tampered storage falls back to the defaults, field by field.
assert.deepStrictEqual(readPreferences('{not json', null, false), defaults);
assert.deepStrictEqual(readPreferences('"a string"', null, false), defaults);
assert.deepStrictEqual(
    readPreferences(JSON.stringify({ size: 9, contrast: 'yes', underline: 1, font: true }), null, true),
    { size: 0, contrast: false, underline: false, font: true, motion: true }
);
assert.strictEqual(readPreferences(JSON.stringify({ size: 1.5 }), null, false).size, 0);
assert.strictEqual(readPreferences(JSON.stringify({ size: -1 }), null, false).size, -1);
assert.strictEqual(readPreferences(JSON.stringify({ size: -2 }), null, false).size, 0);

// The old article-only text size (rem, 1.125 = default) moves over once,
// to the nearest whole-page step.
assert.strictEqual(readPreferences(null, '1.125', false).size, 0);
assert.strictEqual(readPreferences(null, '1.25', false).size, 1);
assert.strictEqual(readPreferences(null, '1.625', false).size, 4);
assert.strictEqual(readPreferences(null, '0.875', false).size, -1);
assert.strictEqual(readPreferences(null, 'junk', false).size, 0);
assert.strictEqual(readPreferences(null, '9', false).size, 0);
// Once the new preferences exist, the old value is ignored.
assert.strictEqual(readPreferences(JSON.stringify({ size: 2 }), '0.875', false).size, 2);

// Text size runs from 87.5% to 150% of the page's base size.
assert.strictEqual(textScale(-1), 0.875);
assert.strictEqual(textScale(0), 1);
assert.strictEqual(textScale(4), 1.5);

console.log('reading-preferences: ok');
