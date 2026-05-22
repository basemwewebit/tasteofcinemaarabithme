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
            var isDark = !document.documentElement.classList.contains('dark');
            var toggleThemeClass = function () {
                document.documentElement.classList.toggle('dark', isDark);
            };
            if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.startViewTransition(toggleThemeClass);
            } else {
                toggleThemeClass();
            }
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

    function setMobileMenuOpen(isOpen) {
        if (mobileMenu) {
            mobileMenu.classList.toggle('mobile-menu--open', isOpen);
            mobileMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            if (isOpen) {
                mobileMenu.removeAttribute('inert');
            } else {
                mobileMenu.setAttribute('inert', '');
            }
        }
        if (menuOverlay) {
            menuOverlay.classList.toggle('hidden', !isOpen);
            menuOverlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }
        if (mobileMenuToggle) mobileMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        document.body.classList.toggle('overflow-hidden', isOpen);
    }

    var mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function () {
            setMobileMenuOpen(true);
            if (!menuFocusTrap && mobileMenu) {
                menuFocusTrap = window.FocusTrap(mobileMenu, {
                    initialFocus: document.getElementById('close-menu'),
                    onEscape: function () {
                        setMobileMenuOpen(false);
                    }
                });
            }
            if (menuFocusTrap) menuFocusTrap.activate();
        });
    }

    function closeMobileMenu() {
        setMobileMenuOpen(false);
        if (menuFocusTrap) menuFocusTrap.deactivate();
    }

    var closeMenuBtn = document.getElementById('close-menu');
    if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeMobileMenu);
    if (menuOverlay) menuOverlay.addEventListener('click', closeMobileMenu);

    var searchOverlay = document.getElementById('search-overlay');
    var searchFocusTrap = null;

    function getSearchOverlayInput() {
        return searchOverlay ? searchOverlay.querySelector('input[type="search"]') : null;
    }

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
                initialFocus: getSearchOverlayInput(),
                onEscape: function () {
                    fadeOutSearch(function () { document.body.classList.remove('overflow-hidden'); });
                    if (searchFocusTrap) searchFocusTrap.deactivate();
                }
            });
        }
        if (searchFocusTrap) searchFocusTrap.activate();
        window.setTimeout(function () {
            var searchInput = getSearchOverlayInput();
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

    (function initLiveSearch() {
        if (!searchOverlay) return;

        var form = searchOverlay.querySelector('[data-live-search-form]');
        var input = searchOverlay.querySelector('input[type="search"]');
        var suggestions = document.getElementById('search-suggestions-list');
        var status = document.getElementById('search-suggestions-status');
        var recentGroup = searchOverlay.querySelector('[data-recent-searches]');
        var recentList = searchOverlay.querySelector('[data-recent-searches-list]');
        var settings = window.mazaq_ajax || {};
        var recentKey = 'mazaq_recent_searches';
        var debounceTimer = null;
        var latestRequest = 0;

        if (!form || !input || !suggestions || !settings.ajax_url || !settings.search_nonce) return;

        function getRecentSearches() {
            try {
                var parsed = JSON.parse(localStorage.getItem(recentKey) || '[]');
                return Array.isArray(parsed) ? parsed.filter(Boolean).slice(0, 5) : [];
            } catch (e) {
                return [];
            }
        }

        function setRecentSearches(items) {
            try {
                localStorage.setItem(recentKey, JSON.stringify(items.slice(0, 5)));
            } catch (e) {}
        }

        function addRecentSearch(term) {
            term = term.trim();
            if (term.length < 2) return;
            var items = getRecentSearches().filter(function (item) { return item !== term; });
            items.unshift(term);
            setRecentSearches(items);
            renderRecentSearches();
        }

        function submitTerm(term) {
            input.value = term;
            addRecentSearch(term);
            form.submit();
        }

        function renderRecentSearches() {
            if (!recentGroup || !recentList) return;
            var items = getRecentSearches();
            recentList.innerHTML = '';
            recentGroup.hidden = items.length === 0;
            items.forEach(function (item) {
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'delight-search__chip';
                chip.textContent = item;
                chip.addEventListener('click', function () { submitTerm(item); });
                recentList.appendChild(chip);
            });
        }

        function clearSuggestions(message) {
            suggestions.innerHTML = '';
            if (status) status.textContent = message || '';
        }

        function renderSuggestions(items, term) {
            suggestions.innerHTML = '';
            if (!items.length) {
                clearSuggestions(term.length >= 2 ? 'لا توجد اقتراحات مطابقة.' : '');
                return;
            }

            if (status) status.textContent = 'اقتراحات بحث جاهزة.';
            items.forEach(function (item) {
                var link = document.createElement('a');
                link.href = item.url;
                link.className = 'delight-search__suggestion';
                link.setAttribute('role', 'listitem');

                var media = document.createElement('span');
                media.className = 'delight-search__suggestion-media';
                if (item.thumbnail) {
                    var image = document.createElement('img');
                    image.src = item.thumbnail;
                    image.alt = item.alt || '';
                    image.loading = 'lazy';
                    media.appendChild(image);
                }

                var body = document.createElement('span');
                body.className = 'delight-search__suggestion-body';
                var title = document.createElement('strong');
                title.textContent = item.title;
                var meta = document.createElement('span');
                meta.textContent = [item.category, item.date].filter(Boolean).join(' • ');
                body.appendChild(title);
                body.appendChild(meta);
                link.appendChild(media);
                link.appendChild(body);
                suggestions.appendChild(link);
            });
        }

        function requestSuggestions(term) {
            var requestId = ++latestRequest;
            if (term.length < 2) {
                clearSuggestions('');
                return;
            }
            if (status) status.textContent = 'جاري تحميل الاقتراحات...';

            var url = settings.ajax_url + '?action=' + encodeURIComponent(settings.search_suggestions_action || 'mazaq_search_suggestions') +
                '&nonce=' + encodeURIComponent(settings.search_nonce) +
                '&query=' + encodeURIComponent(term);

            fetch(url, { credentials: 'same-origin' })
                .then(function (response) {
                    if (!response.ok) throw new Error('Search suggestions failed');
                    return response.json();
                })
                .then(function (payload) {
                    if (requestId !== latestRequest) return;
                    renderSuggestions((payload && payload.success && payload.data && payload.data.items) ? payload.data.items : [], term);
                })
                .catch(function () {
                    if (requestId !== latestRequest) return;
                    clearSuggestions('تعذر تحميل الاقتراحات الآن.');
                });
        }

        input.addEventListener('input', function () {
            var term = input.value.trim();
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(function () { requestSuggestions(term); }, 250);
        });

        form.addEventListener('submit', function () {
            addRecentSearch(input.value);
        });

        searchOverlay.querySelectorAll('[data-search-term]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                submitTerm(chip.getAttribute('data-search-term') || chip.textContent || '');
            });
        });

        renderRecentSearches();
    })();

    (function initNewsletterForms() {
        var forms = document.querySelectorAll('[data-newsletter-form]');
        var settings = window.mazaq_ajax || {};
        if (!forms.length || !settings.ajax_url || !settings.newsletter_nonce) return;

        forms.forEach(function (form) {
            var input = form.querySelector('input[type="email"]');
            var status = form.querySelector('[data-newsletter-status]');
            var button = form.querySelector('button[type="submit"]');
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!input || !button) return;
                var email = input.value.trim();
                if (!email || (input.validity && !input.validity.valid)) {
                    if (status) status.textContent = 'أدخل بريدًا إلكترونيًا صحيحًا.';
                    if (typeof input.reportValidity === 'function') input.reportValidity();
                    return;
                }
                button.disabled = true;
                if (status) status.textContent = 'جاري تسجيل الاشتراك...';
                var body = new URLSearchParams();
                body.set('action', settings.newsletter_action || 'mazaq_newsletter_signup');
                body.set('nonce', settings.newsletter_nonce);
                body.set('email', email);
                fetch(settings.ajax_url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: body.toString()
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        if (status) status.textContent = payload && payload.data && payload.data.message ? payload.data.message : 'تم استلام طلبك.';
                        if (payload && payload.success) input.value = '';
                    })
                    .catch(function () {
                        if (status) status.textContent = 'تعذر تسجيل الاشتراك الآن. حاول لاحقًا.';
                    })
                    .finally(function () {
                        button.disabled = false;
                    });
            });
        });
    })();

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

    var prefetchedUrls = new Set();
    document.querySelectorAll('article a[href]').forEach(function (link) {
        link.addEventListener('mouseenter', function () {
            var href = link.href;
            if (!href || prefetchedUrls.has(href) || href.indexOf(window.location.origin) !== 0) return;
            prefetchedUrls.add(href);
            var prefetch = document.createElement('link');
            prefetch.rel = 'prefetch';
            prefetch.href = href;
            document.head.appendChild(prefetch);
        }, { once: true });
    });

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
    var styles = ['color: #C9A227', 'font-weight: 700', 'font-size: 13px', 'font-family: monospace', 'padding: 2px 0'].join(';');
    console.log('%cمذاق السينما', styles);
    console.log('%cكل إطار يحكي قصة. شكراً لاهتمامك بالتفاصيل.', 'color: #94a3b8; font-size: 11px;');
    console.log('%cTaste of Cinema — every frame tells a story.', 'color: #94a3b8; font-size: 11px;');
})();
