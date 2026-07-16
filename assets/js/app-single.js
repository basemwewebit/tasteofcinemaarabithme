/**
 * Single-post features.
 *
 * - Reading progress beam (top) + projector spark.
 * - The Living Reel: scroll-spy reading index (desktop rail + mobile puck/sheet).
 * - Article font-size controls.
 */
document.addEventListener('DOMContentLoaded', function () {
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function throttle(func, limit) {
        var inThrottle;
        return function () {
            var args = arguments;
            var context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                window.setTimeout(function () { inThrottle = false; }, limit);
            }
        };
    }

    function readingProgress() {
        var s = window.scrollY || 0;
        var d = document.documentElement.scrollHeight || 1;
        var c = window.innerHeight || 1;
        var scrollable = d - c;
        var p = scrollable > 0 ? (s / scrollable) : 0;
        return Math.min(1, Math.max(0, p));
    }

    // --- Element handles ---
    var progressEl = document.getElementById('reading-progress-container');
    var article = document.querySelector('.article-content');
    var rail = document.querySelector('[data-reading-rail]');
    var puck = document.querySelector('[data-reading-puck]');
    var puckToggle = puck ? puck.querySelector('[data-puck-toggle]') : null;
    var puckNum = puck ? puck.querySelector('[data-puck-num]') : null;
    var sheet = document.getElementById('reading-sheet');

    var railItems = rail ? Array.prototype.slice.call(rail.querySelectorAll('[data-rail-item]')) : [];
    var railTrack = rail ? rail.querySelector('.reading-rail__track') : null;
    var sheetItems = sheet ? Array.prototype.slice.call(sheet.querySelectorAll('[data-sheet-item]')) : [];

    // Read the sections off the rendered index instead of re-querying the article:
    // single.php injects the inline-related block *into* .article-content, and its
    // <h3 id> answers to 'h2[id], h3[id]' as if it were a section. That shifted every
    // highlight after the ~60% mark by one and left the final heading with none at all.
    // Order is kept 1:1 with the index (misses stay as null) so headings[i] always
    // addresses railItems[i] and sheetItems[i] -- one template emits both lists from
    // the same headings, so they always agree.
    var headings = railItems.map(function (item) {
        return document.getElementById(item.getAttribute('data-rail-item'));
    });

    var HEADER_OFFSET = 110;
    var activeIndex = -1;
    var lastPct = -1;

    function setActive(index) {
        if (index === activeIndex) {
            return;
        }
        activeIndex = index;
        for (var i = 0; i < railItems.length; i++) {
            railItems[i].classList.toggle('is-active', i === index);
            railItems[i].classList.toggle('is-read', i < index);
        }
        for (var j = 0; j < sheetItems.length; j++) {
            sheetItems[j].classList.toggle('is-active', j === index);
        }
    }

    // Runs off the active heading rather than page progress: the fill has to stop at
    // the active dot, and the rail clips at 72vh so on long listicles the active dot
    // can otherwise sit outside the visible window.
    function syncRail(index) {
        if (!rail || !railItems[index]) {
            return;
        }

        // Terminate the fill at the active dot. Page progress counts the hero, the
        // related-posts block and the footer, so it never agreed with the dots.
        if (railTrack) {
            var dot = railItems[index].querySelector('.reading-rail__dot');
            var trackRect = railTrack.getBoundingClientRect();
            if (dot && trackRect.height > 0) {
                var dotRect = dot.getBoundingClientRect();
                var filled = (dotRect.top + dotRect.height / 2 - trackRect.top) / trackRect.height;
                rail.style.setProperty('--rail-progress', Math.min(1, Math.max(0, filled)));
            }
        }

        var scrollable = rail.scrollHeight - rail.clientHeight;
        rail.classList.toggle('is-scrollable', scrollable > 1);
        if (scrollable <= 1) {
            return;
        }

        var itemRect = railItems[index].getBoundingClientRect();
        var railRect = rail.getBoundingClientRect();
        var next = rail.scrollTop + (itemRect.top - railRect.top) - (rail.clientHeight - itemRect.height) / 2;
        rail.scrollTop = Math.max(0, Math.min(next, scrollable));
    }

    function computeActive() {
        if (!headings.length) {
            return;
        }
        var idx = 0;
        for (var i = 0; i < headings.length; i++) {
            if (!headings[i]) {
                continue;
            }
            if (headings[i].getBoundingClientRect().top - HEADER_OFFSET <= 0) {
                idx = i;
            } else {
                break;
            }
        }
        setActive(idx);
    }

    function tick() {
        var p = readingProgress();

        if (progressEl) {
            progressEl.style.setProperty('--reading-progress', p);
            progressEl.classList.toggle('is-reading', p > 0.004 && p < 0.996);
        }
        if (puck) {
            puck.style.setProperty('--reading-progress', p);
        }
        if (puckNum) {
            var pct = Math.round(p * 100);
            if (pct !== lastPct) {
                puckNum.textContent = String(pct);
                lastPct = pct;
            }
        }
        computeActive();
        // After computeActive, so the active classes are applied before we measure.
        // Called from tick (not setActive) so a resize re-syncs even when the active
        // heading has not changed.
        syncRail(activeIndex);
    }

    var onScroll = throttle(tick, 80);
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', throttle(tick, 150));
    tick();

    // --- Smooth in-page navigation for the rail and sheet ---
    function scrollToId(id) {
        var target = document.getElementById(id);
        if (!target) {
            return;
        }
        var top = window.scrollY + target.getBoundingClientRect().top - 90;
        window.scrollTo({ top: Math.max(0, top), behavior: prefersReduced ? 'auto' : 'smooth' });
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#' + id);
        }
    }

    function wireJumpLinks(scope, selector) {
        if (!scope) {
            return;
        }
        scope.addEventListener('click', function (e) {
            var link = e.target.closest(selector);
            if (!link) {
                return;
            }
            var href = link.getAttribute('href') || '';
            if (href.charAt(0) !== '#') {
                return;
            }
            e.preventDefault();
            scrollToId(href.slice(1));
            if (scope === sheet) {
                closeSheet(true);
            }
        });
    }

    wireJumpLinks(rail, '.reading-rail__link');
    wireJumpLinks(sheet, '.reading-sheet__link');

    // --- Mobile puck / jump sheet ---
    var sheetCloseTimer = null;

    function openSheet() {
        if (!sheet || !puckToggle) {
            return;
        }
        if (sheetCloseTimer) {
            window.clearTimeout(sheetCloseTimer);
            sheetCloseTimer = null;
        }
        sheet.hidden = false;
        window.requestAnimationFrame(function () {
            sheet.classList.add('is-open');
        });
        puckToggle.setAttribute('aria-expanded', 'true');
        document.addEventListener('click', onOutsideClick, true);
        document.addEventListener('keydown', onSheetKeydown);
    }

    function closeSheet(returnFocus) {
        if (!sheet || !puckToggle) {
            return;
        }
        sheet.classList.remove('is-open');
        puckToggle.setAttribute('aria-expanded', 'false');
        document.removeEventListener('click', onOutsideClick, true);
        document.removeEventListener('keydown', onSheetKeydown);
        if (prefersReduced) {
            sheet.hidden = true;
        } else {
            sheetCloseTimer = window.setTimeout(function () {
                if (!sheet.classList.contains('is-open')) {
                    sheet.hidden = true;
                }
            }, 300);
        }
        if (returnFocus) {
            puckToggle.focus();
        }
    }

    function onOutsideClick(e) {
        if (puck && !puck.contains(e.target)) {
            closeSheet(false);
        }
    }

    function onSheetKeydown(e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            closeSheet(true);
        }
    }

    if (puckToggle) {
        puckToggle.addEventListener('click', function () {
            if (puckToggle.getAttribute('aria-expanded') === 'true') {
                closeSheet(false);
            } else {
                openSheet();
            }
        });
    }

    // --- Font size controls ---
    var fontStorageKey = 'mazaq-article-font-size';
    var fontMin = 0.875;
    var fontMax = 1.625;
    var storedFont = parseFloat(localStorage.getItem(fontStorageKey) || '1.125');
    var currentFontSize = isFinite(storedFont) ? storedFont : 1.125;
    currentFontSize = Math.max(fontMin, Math.min(fontMax, currentFontSize));

    function applyFontSize() {
        document.documentElement.style.setProperty('--article-font-size-custom', currentFontSize + 'rem');
        var el = document.querySelector('.article-content');
        if (el) {
            el.classList.add('font-resized');
        }
        try {
            localStorage.setItem(fontStorageKey, String(currentFontSize));
        } catch (e) {}
    }

    if (article) {
        applyFontSize();
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#font-increase')) {
            e.preventDefault();
            if (currentFontSize < fontMax) {
                currentFontSize += 0.125;
                applyFontSize();
            }
        }
        if (e.target.closest('#font-decrease')) {
            e.preventDefault();
            if (currentFontSize > fontMin) {
                currentFontSize -= 0.125;
                applyFontSize();
            }
        }
    });
});
