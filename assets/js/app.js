document.addEventListener('DOMContentLoaded', function () {
    var themeBtn = document.getElementById('theme-toggle');
    var sunIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>';
    var moonIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';

    if (themeBtn) {
        var syncThemeBtn = function (isDark) {
            themeBtn.innerHTML = isDark ? sunIcon : moonIcon;
            themeBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        };

        syncThemeBtn(document.documentElement.classList.contains('dark'));

        themeBtn.addEventListener('click', function () {
            var isDark = document.documentElement.classList.toggle('dark');
            try {
                localStorage.setItem('color-theme', isDark ? 'dark' : 'light');
            } catch (e) {}

            var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReduced) {
                syncThemeBtn(isDark);
                return;
            }

            var svg = themeBtn.querySelector('svg');
            if (svg) {
                svg.style.transform = 'rotate(90deg) scale(0.8)';
                svg.style.opacity = '0.4';
            }

            window.setTimeout(function () {
                syncThemeBtn(isDark);
                var newSvg = themeBtn.querySelector('svg');
                if (newSvg) {
                    newSvg.style.transform = 'rotate(-90deg) scale(0.8)';
                    newSvg.style.opacity = '0.4';
                }
                window.setTimeout(function () {
                    var finalSvg = themeBtn.querySelector('svg');
                    if (finalSvg) {
                        finalSvg.style.transform = 'rotate(0deg) scale(1)';
                        finalSvg.style.opacity = '1';
                    }
                }, 30);
            }, 180);
        });
    }

    var mobileMenu = document.getElementById('mobile-menu');
    var menuOverlay = document.getElementById('menu-overlay');
    var menuFocusTrap = null;

    var mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function () {
            if (mobileMenu) mobileMenu.classList.remove('translate-x-full');
            if (menuOverlay) {
                menuOverlay.classList.remove('hidden');
                menuOverlay.setAttribute('aria-hidden', 'false');
            }
            mobileMenuToggle.setAttribute('aria-expanded', 'true');
            document.body.classList.add('overflow-hidden');
            if (!menuFocusTrap && mobileMenu) {
                menuFocusTrap = window.FocusTrap(mobileMenu, {
                    initialFocus: document.getElementById('close-menu'),
                    onEscape: function () {
                        if (mobileMenu) mobileMenu.classList.add('translate-x-full');
                        if (menuOverlay) {
                            menuOverlay.classList.add('hidden');
                            menuOverlay.setAttribute('aria-hidden', 'true');
                        }
                        mobileMenuToggle.setAttribute('aria-expanded', 'false');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            }
            if (menuFocusTrap) menuFocusTrap.activate();
        });
    }

    function closeMobileMenu() {
        if (mobileMenu) mobileMenu.classList.add('translate-x-full');
        if (menuOverlay) {
            menuOverlay.classList.add('hidden');
            menuOverlay.setAttribute('aria-hidden', 'true');
        }
        if (mobileMenuToggle) mobileMenuToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');
        if (menuFocusTrap) menuFocusTrap.deactivate();
    }

    var closeMenuBtn = document.getElementById('close-menu');
    if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeMobileMenu);
    if (menuOverlay) menuOverlay.addEventListener('click', closeMobileMenu);

    var searchOverlay = document.getElementById('search-overlay');
    var searchFocusTrap = null;

    function fadeInSearch() {
        if (!searchOverlay) return;
        searchOverlay.classList.remove('hidden');
    }

    function fadeOutSearch(callback) {
        if (!searchOverlay) { if (callback) callback(); return; }
        searchOverlay.classList.add('hidden');
        window.setTimeout(function () { if (callback) callback(); }, 450);
    }

    function openSearchOverlay() {
        if (!searchOverlay) return;
        fadeInSearch();
        document.body.classList.add('overflow-hidden');
        if (!searchFocusTrap) {
            searchFocusTrap = window.FocusTrap(searchOverlay, {
                initialFocus: document.getElementById('search-input'),
                onEscape: function () {
                    fadeOutSearch(function () { document.body.classList.remove('overflow-hidden'); });
                    if (searchFocusTrap) searchFocusTrap.deactivate();
                }
            });
        }
        if (searchFocusTrap) searchFocusTrap.activate();
        window.setTimeout(function () {
            var searchInput = document.getElementById('search-input');
            if (searchInput) searchInput.focus();
        }, 300);
    }

    function closeSearchOverlay() {
        fadeOutSearch(function () { document.body.classList.remove('overflow-hidden'); });
        if (searchFocusTrap) searchFocusTrap.deactivate();
    }

    var searchToggle = document.getElementById('search-toggle');
    var searchMobileToggle = document.getElementById('search-mobile-toggle');
    if (searchToggle) {
        searchToggle.addEventListener('click', function (e) { e.preventDefault(); openSearchOverlay(); });
    }
    if (searchMobileToggle) {
        searchMobileToggle.addEventListener('click', function (e) { e.preventDefault(); openSearchOverlay(); });
    }

    var searchClose = document.getElementById('search-close');
    if (searchClose) searchClose.addEventListener('click', closeSearchOverlay);

    var lazyImages = document.querySelectorAll('.lazy-image[data-src]');
    if ('IntersectionObserver' in window && lazyImages.length) {
        var lazyObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var img = entry.target;
                var src = img.getAttribute('data-src');
                if (src) {
                    img.setAttribute('src', src);
                    img.addEventListener('load', function onLoad() { img.classList.add('loaded'); img.removeEventListener('load', onLoad); });
                    img.addEventListener('error', function onError() { img.classList.add('loaded'); img.style.opacity = '0.4'; img.style.filter = 'grayscale(1)'; img.removeEventListener('error', onError); });
                    img.removeAttribute('data-src');
                }
                observer.unobserve(entry.target);
            });
        });
        lazyImages.forEach(function (img) { lazyObserver.observe(img); });
    }

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

    var backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        var showThreshold = 300;
        window.addEventListener('scroll', throttle(function () {
            if (window.scrollY > showThreshold) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        }, 100));

        backToTopBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReduced) { window.scrollTo(0, 0); return; }
            var start = window.scrollY;
            var startTime = performance.now();
            var duration = 600;
            function step(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var ease = 1 - Math.pow(1 - progress, 3);
                window.scrollTo(0, start * (1 - ease));
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }

    var filterBtns = document.querySelectorAll('.filter-btn');
    if (filterBtns.length) {
        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBtns.forEach(function (b) {
                    b.classList.remove('bg-slate-900', 'dark:bg-slate-50', 'text-white', 'dark:text-slate-900', 'shadow-md', 'border-transparent');
                    b.classList.add('bg-slate-50', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'hover:bg-slate-100', 'dark:hover:bg-slate-700', 'border', 'border-slate-200', 'dark:border-slate-700');
                });
                btn.classList.remove('bg-slate-50', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'hover:bg-slate-100', 'dark:hover:bg-slate-700', 'border', 'border-slate-200', 'dark:border-slate-700');
                btn.classList.add('bg-slate-900', 'dark:bg-slate-50', 'text-white', 'dark:text-slate-900', 'shadow-md', 'border-transparent');
                var filterVal = btn.dataset.filter;
                var items = document.querySelectorAll('.archive-item');
                items.forEach(function (item) { item.style.display = 'none'; item.style.opacity = '0'; });
                var toShow = filterVal === 'all'
                    ? Array.from(items)
                    : Array.from(items).filter(function (item) { return item.dataset.category === filterVal; });
                toShow.forEach(function (item) {
                    item.style.display = '';
                    requestAnimationFrame(function () { item.style.transition = 'opacity 200ms ease'; item.style.opacity = '1'; });
                });
            });
        });
    }
});

(function () {
    if (typeof console === 'undefined' || !console.log) return;
    var styles = ['color: #D4AF37', 'font-weight: 700', 'font-size: 13px', 'font-family: monospace', 'padding: 2px 0'].join(';');
    console.log('%cمذاق السينما', styles);
    console.log('%cكل إطار يحكي قصة. شكراً لاهتمامك بالتفاصيل.', 'color: #94a3b8; font-size: 11px;');
    console.log('%cTaste of Cinema — every frame tells a story.', 'color: #94a3b8; font-size: 11px;');
})();
