/**
 * Single-post features: reading progress and font-size controls.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Reading progress bar
    const progressBar = document.getElementById('reading-progress-bar');
    if (progressBar) {
        function throttle(func, limit) {
            let inThrottle;
            return function () {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    window.setTimeout(function () { inThrottle = false; }, limit);
                }
            };
        }
        window.addEventListener('scroll', throttle(function () {
            const s = window.scrollY || 0;
            const d = document.documentElement.scrollHeight || 1;
            const c = window.innerHeight || 1;
            const scrollable = d - c;
            const scrollPercent = scrollable > 0 ? (s / scrollable) : 0;
            const clamped = Math.min(1, Math.max(0, scrollPercent));
            progressBar.style.transform = 'scaleX(' + clamped + ')';
        }, 80));
    }

    // Font size controls
    var fontStorageKey = 'mazaq-article-font-size';
    var fontMin = 0.875;
    var fontMax = 1.625;
    var storedFont = parseFloat(localStorage.getItem(fontStorageKey) || '1.125');
    var currentFontSize = isFinite(storedFont) ? storedFont : 1.125;
    currentFontSize = Math.max(fontMin, Math.min(fontMax, currentFontSize));

    function applyFontSize() {
        var el = document.querySelector('.article-content');
        if (!el) { return; }
        el.classList.add('font-resized');
        el.style.setProperty('--article-font-size', currentFontSize + 'rem');
        try {
            localStorage.setItem(fontStorageKey, String(currentFontSize));
        } catch (e) {}
    }

    if (document.querySelector('.article-content')) {
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
