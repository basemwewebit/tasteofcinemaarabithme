/**
 * Reading preferences: a reader's text size, high contrast, underlined links,
 * clearer font and stopped motion, kept across pages and visits.
 *
 * Printed inline in <head> so the page draws with them already applied, and
 * required by tests/reading-preferences-test.js. Everything that asks "is
 * motion reduced?" reads the html.motion-reduced class this sets: it holds
 * the device setting until the reader chooses otherwise.
 */
(function () {
    var STORAGE_KEY = 'mazaq-reading-preferences';
    // The retired article-only control saved a rem size; 1.125rem was its default.
    var LEGACY_KEY = 'mazaq-article-font-size';
    var SIZE_MIN = -1;
    var SIZE_MAX = 4;
    var CLASSES = { contrast: 'pref-contrast', underline: 'pref-underline', font: 'pref-readable-font', motion: 'motion-reduced' };

    function textScale(size) {
        return 1 + size * 0.125;
    }

    function validSize(size) {
        return typeof size === 'number' && size % 1 === 0 && size >= SIZE_MIN && size <= SIZE_MAX;
    }

    function legacySize(raw) {
        var rem = parseFloat(raw);
        if (!(rem >= 0.875 && rem <= 1.625)) {
            return 0;
        }
        var size = Math.round((rem / 1.125 - 1) / 0.125);
        return Math.max(SIZE_MIN, Math.min(SIZE_MAX, size));
    }

    // Anything not exactly a valid choice falls back to its default.
    function normalize(saved, deviceReducesMotion) {
        return {
            size: validSize(saved.size) ? saved.size : 0,
            contrast: saved.contrast === true,
            underline: saved.underline === true,
            font: saved.font === true,
            motion: typeof saved.motion === 'boolean' ? saved.motion : !!deviceReducesMotion
        };
    }

    function readPreferences(stored, legacy, deviceReducesMotion) {
        var saved = null;
        try {
            saved = stored ? JSON.parse(stored) : null;
        } catch (e) {}
        if (!saved || typeof saved !== 'object') {
            return normalize({ size: legacySize(legacy) }, deviceReducesMotion);
        }
        return normalize(saved, deviceReducesMotion);
    }

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { readPreferences: readPreferences, textScale: textScale };
        return;
    }

    var html = document.documentElement;
    var deviceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    var stored = null;
    var legacy = null;
    try {
        stored = localStorage.getItem(STORAGE_KEY);
        legacy = localStorage.getItem(LEGACY_KEY);
    } catch (e) {}
    var prefs = readPreferences(stored, legacy, deviceMotion.matches);

    function apply() {
        Object.keys(CLASSES).forEach(function (key) {
            html.classList.toggle(CLASSES[key], prefs[key]);
        });
        if (prefs.size === 0) {
            html.style.removeProperty('font-size');
        } else {
            html.style.fontSize = textScale(prefs.size) * 100 + '%';
        }
    }

    function save() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
            localStorage.removeItem(LEGACY_KEY);
        } catch (e) {}
    }

    function changed() {
        apply();
        document.dispatchEvent(new CustomEvent('mazaq:reading-preferences', { detail: prefs }));
    }

    apply();
    if (!stored && legacy !== null) {
        save();
    }

    // Until the reader picks, stopped motion keeps following the device.
    deviceMotion.addEventListener('change', function () {
        var saved = null;
        try {
            saved = localStorage.getItem(STORAGE_KEY);
        } catch (e) {}
        prefs = readPreferences(saved, null, deviceMotion.matches);
        changed();
    });

    window.mazaqReadingPreferences = {
        sizeMin: SIZE_MIN,
        sizeMax: SIZE_MAX,
        get: function () {
            return Object.assign({}, prefs);
        },
        set: function (patch) {
            prefs = normalize(Object.assign({}, prefs, patch), deviceMotion.matches);
            save();
            changed();
        },
        reset: function () {
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch (e) {}
            prefs = readPreferences(null, null, deviceMotion.matches);
            changed();
        },
        textScale: textScale
    };

    window.mazaqMotionReduced = function () {
        return html.classList.contains('motion-reduced');
    };
})();
