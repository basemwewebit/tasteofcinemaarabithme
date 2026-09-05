document.addEventListener('DOMContentLoaded', function () {
    var themeBtn = document.getElementById('theme-toggle');
    var sunIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>';
    var moonIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';

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
        var clearBtn = searchOverlay.querySelector('[data-search-clear]');
        var suggestions = document.getElementById('search-suggestions-list');
        var status = document.getElementById('search-suggestions-status');
        var allWrap = searchOverlay.querySelector('[data-search-all]');
        var allLink = searchOverlay.querySelector('[data-search-all-link]');
        var allLabel = searchOverlay.querySelector('[data-search-all-label]');
        var recentGroup = searchOverlay.querySelector('[data-recent-searches]');
        var recentList = searchOverlay.querySelector('[data-recent-searches-list]');
        var recentClear = searchOverlay.querySelector('[data-recent-searches-clear]');
        var chipsWrap = searchOverlay.querySelector('.delight-search__chips');
        var settings = window.mazaq_ajax || {};
        var recentKey = 'mazaq_recent_searches';
        var debounceTimer = null;
        var latestRequest = 0;
        var activeIndex = -1;
        var errorBox = null;
        var activeController = null;

        if (!form || !input || !suggestions || !settings.ajax_url || !settings.search_nonce) return;

        if (!errorBox) {
            errorBox = document.createElement('div');
            errorBox.className = 'delight-search__error';
            errorBox.hidden = true;
            var errorText = document.createElement('p');
            errorText.textContent = 'تعذر تحميل الاقتراحات الآن.';
            var retryBtn = document.createElement('button');
            retryBtn.type = 'button';
            retryBtn.textContent = 'إعادة المحاولة';
            retryBtn.addEventListener('click', function () {
                errorBox.hidden = true;
                requestSuggestions(input.value.trim());
            });
            errorBox.appendChild(errorText);
            errorBox.appendChild(retryBtn);
            suggestions.insertAdjacentElement('afterend', errorBox);
        }

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

        function setStatus(message) {
            if (status) status.textContent = message || '';
        }

        function getOptions() {
            return suggestions.querySelectorAll('[role="option"]');
        }

        function setActiveOption(index) {
            var options = getOptions();
            if (!options.length) {
                activeIndex = -1;
                input.removeAttribute('aria-activedescendant');
                return;
            }
            activeIndex = (index + options.length) % options.length;
            options.forEach(function (option, i) {
                option.classList.toggle('delight-search__suggestion--active', i === activeIndex);
                option.setAttribute('aria-selected', i === activeIndex ? 'true' : 'false');
            });
            input.setAttribute('aria-activedescendant', options[activeIndex].id);
            options[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function resetActiveOption() {
            activeIndex = -1;
            input.removeAttribute('aria-activedescendant');
            getOptions().forEach(function (option) {
                option.classList.remove('delight-search__suggestion--active');
                option.setAttribute('aria-selected', 'false');
            });
        }

        function setExpanded(open) {
            input.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function resetToIdle() {
            suggestions.innerHTML = '';
            errorBox.hidden = true;
            if (allWrap) allWrap.hidden = true;
            if (chipsWrap) chipsWrap.hidden = false;
            setExpanded(false);
            resetActiveOption();
            setStatus('');
        }

        function showSkeleton() {
            suggestions.innerHTML = '';
            resetActiveOption();
            setExpanded(true);
            if (allWrap) allWrap.hidden = true;
            if (chipsWrap) chipsWrap.hidden = true;
            errorBox.hidden = true;
            for (var i = 0; i < 3; i++) {
                var row = document.createElement('div');
                row.className = 'delight-search__skeleton';
                row.setAttribute('aria-hidden', 'true');
                var media = document.createElement('span');
                var body = document.createElement('div');
                body.appendChild(document.createElement('span'));
                body.appendChild(document.createElement('span'));
                row.appendChild(media);
                row.appendChild(body);
                suggestions.appendChild(row);
            }
        }

        function highlightTitle(title, term) {
            var strong = document.createElement('strong');
            var lowerTitle = title.toLowerCase();
            var lowerTerm = term.toLowerCase();
            var cursor = 0;
            var matchAt = lowerTitle.indexOf(lowerTerm);
            while (matchAt !== -1) {
                if (matchAt > cursor) strong.appendChild(document.createTextNode(title.slice(cursor, matchAt)));
                var mark = document.createElement('mark');
                mark.textContent = title.slice(matchAt, matchAt + term.length);
                strong.appendChild(mark);
                cursor = matchAt + term.length;
                matchAt = lowerTitle.indexOf(lowerTerm, cursor);
            }
            if (cursor < title.length) strong.appendChild(document.createTextNode(title.slice(cursor)));
            return strong;
        }

        function renderSuggestions(items, term) {
            suggestions.innerHTML = '';
            resetActiveOption();
            setExpanded(items.length > 0);
            if (chipsWrap) chipsWrap.hidden = items.length > 0;
            errorBox.hidden = true;
            if (allWrap) allWrap.hidden = term.length < 2;

            var safeItems = (items || []).filter(function (item) {
                return item && item.url && typeof item.url === 'string';
            });
            if (!safeItems.length) {
                setStatus('لا توجد اقتراحات مطابقة. جرّب كلمة أقصر أو تصفح الاقتراحات أدناه.');
                return;
            }

            setStatus('اقتراحات بحث جاهزة.');
            safeItems.forEach(function (item, index) {
                var link = document.createElement('a');
                link.href = item.url;
                link.className = 'delight-search__suggestion';
                link.setAttribute('role', 'option');
                link.id = 'search-option-' + index;
                link.setAttribute('aria-selected', 'false');

                var media = document.createElement('span');
                media.className = 'delight-search__suggestion-media';
                if (item.thumbnail) {
                    var image = document.createElement('img');
                    image.src = item.thumbnail;
                    image.alt = item.alt || '';
                    image.loading = 'lazy';
                    media.appendChild(image);
                } else {
                    media.classList.add('delight-search__suggestion-media--plate');
                    media.setAttribute('data-letter', (item.title || '؟').trim().charAt(0));
                }

                var body = document.createElement('span');
                body.className = 'delight-search__suggestion-body';
                body.appendChild(highlightTitle(item.title || '', term));

                var meta = document.createElement('span');
                meta.className = 'delight-search__suggestion-meta';
                if (item.category) {
                    var category = document.createElement('span');
                    category.textContent = item.category;
                    meta.appendChild(category);
                }
                if (item.category && item.date) {
                    var dot = document.createElement('span');
                    dot.className = 'delight-search__dot';
                    dot.setAttribute('aria-hidden', 'true');
                    meta.appendChild(dot);
                }
                if (item.date) {
                    var date = document.createElement('span');
                    date.className = 'delight-search__num';
                    date.textContent = item.date;
                    meta.appendChild(date);
                }
                body.appendChild(meta);

                link.appendChild(media);
                link.appendChild(body);
                suggestions.appendChild(link);
            });
        }

        function updateAllLink(term) {
            if (!allWrap || !allLink || !allLabel) return;
            if (term.length < 2) {
                allWrap.hidden = true;
                return;
            }
            allLabel.textContent = 'عرض كل نتائج «' + term + '»';
            var base = form.getAttribute('action') || '/';
            allLink.href = base + '?s=' + encodeURIComponent(term);
            allWrap.hidden = false;
        }

        var REQUEST_TIMEOUT = 8000;

        function requestSuggestions(term) {
            var requestId = ++latestRequest;
            if (term.length < 2) {
                resetToIdle();
                return;
            }

            if (activeController) activeController.abort();

            updateAllLink(term);
            setStatus('جاري تحميل الاقتراحات...');
            showSkeleton();

            var controller = typeof AbortController === 'function' ? new AbortController() : null;
            activeController = controller;
            var timeoutId = controller ? window.setTimeout(function () { controller.abort(); }, REQUEST_TIMEOUT) : null;

            var url = settings.ajax_url + '?action=' + encodeURIComponent(settings.search_suggestions_action || 'mazaq_search_suggestions') +
                '&nonce=' + encodeURIComponent(settings.search_nonce) +
                '&query=' + encodeURIComponent(term);

            fetch(url, { credentials: 'same-origin', signal: controller ? controller.signal : undefined })
                .then(function (response) {
                    if (!response.ok) throw new Error('Search suggestions failed');
                    return response.json();
                })
                .then(function (payload) {
                    window.clearTimeout(timeoutId);
                    if (requestId !== latestRequest) return;
                    var items = (payload && payload.success && payload.data && payload.data.items) ? payload.data.items : [];
                    renderSuggestions(items, term);
                })
                .catch(function () {
                    window.clearTimeout(timeoutId);
                    if (requestId !== latestRequest) return;
                    suggestions.innerHTML = '';
                    resetActiveOption();
                    setExpanded(false);
                    if (chipsWrap) chipsWrap.hidden = false;
                    errorBox.hidden = false;
                    setStatus('');
                });
        }

        input.addEventListener('input', function () {
            var term = input.value.trim();
            if (clearBtn) clearBtn.hidden = term.length === 0;
            window.clearTimeout(debounceTimer);
            if (term.length < 2) {
                debounceTimer = window.setTimeout(function () { requestSuggestions(term); }, 100);
                return;
            }
            debounceTimer = window.setTimeout(function () { requestSuggestions(term); }, 250);
        });

        input.addEventListener('keydown', function (event) {
            var options = getOptions();
            if (!options.length) return;
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setActiveOption(activeIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActiveOption(activeIndex <= 0 ? options.length - 1 : activeIndex - 1);
            } else if (event.key === 'Home') {
                event.preventDefault();
                setActiveOption(0);
            } else if (event.key === 'End') {
                event.preventDefault();
                setActiveOption(options.length - 1);
            } else if (event.key === 'Enter' && activeIndex >= 0 && options[activeIndex]) {
                event.preventDefault();
                addRecentSearch(input.value);
                window.location.href = options[activeIndex].href;
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                clearBtn.hidden = true;
                resetToIdle();
                input.focus();
            });
        }

        function resetSearchForOpen() {
            if (activeController) {
                latestRequest += 1;
                activeController.abort();
                activeController = null;
            }
            input.value = '';
            if (clearBtn) clearBtn.hidden = true;
            resetToIdle();
            renderRecentSearches();
        }

        [document.getElementById('search-toggle'), document.getElementById('search-mobile-toggle')].forEach(function (toggle) {
            if (toggle) toggle.addEventListener('click', resetSearchForOpen);
        });

        if (allLink) {
            allLink.addEventListener('click', function () {
                addRecentSearch(input.value);
            });
        }

        if (recentClear) {
            recentClear.addEventListener('click', function () {
                setRecentSearches([]);
                renderRecentSearches();
            });
        }

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

    (function initMasthead() {
        var masthead = document.getElementById('toc-masthead');
        if (!masthead) return;

        var isOverlayPage = masthead.classList.contains('delight-masthead--overlay-page');
        var lastY = window.scrollY;
        var SOLID_AT = 24;
        var HIDE_AFTER = 180;

        function updateMasthead() {
            var y = window.scrollY;
            var delta = y - lastY;

            if (isOverlayPage) {
                if (y > SOLID_AT) {
                    masthead.classList.add('delight-masthead--solid');
                    masthead.classList.remove('delight-masthead--overlay');
                } else {
                    masthead.classList.remove('delight-masthead--solid');
                    masthead.classList.add('delight-masthead--overlay');
                }
            }

            var shouldHide = delta > 2 && y > HIDE_AFTER && !document.body.classList.contains('overflow-hidden');
            if (shouldHide || delta < -2 || y <= HIDE_AFTER) {
                masthead.classList.toggle('delight-masthead--hidden', shouldHide);
                if (shouldHide) {
                    if (!masthead.contains(document.activeElement)) masthead.setAttribute('inert', '');
                } else {
                    masthead.removeAttribute('inert');
                }
            }

            lastY = y;
        }

        var rafPending = false;
        function onMastheadScroll() {
            if (rafPending) return;
            rafPending = true;
            window.requestAnimationFrame(function () {
                rafPending = false;
                updateMasthead();
            });
        }

        window.addEventListener('scroll', onMastheadScroll, { passive: true });
        window.addEventListener('pageshow', updateMasthead);
        updateMasthead();
    })();

    (function initFooterCredits() {
        var footer = document.querySelector('.delight-footer');
        if (!footer) return;

        if (!('IntersectionObserver' in window)) {
            footer.classList.add('delight-footer--in');
            return;
        }

        var reveal = function () {
            footer.classList.add('delight-footer--in');
            observer.disconnect();
            footer.removeEventListener('focusin', reveal);
        };

        var observer;
        try {
            observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    reveal();
                });
            }, { rootMargin: '0px 0px -10% 0px' });
            footer.classList.add('delight-footer--animate');
            footer.addEventListener('focusin', reveal);
            observer.observe(footer);
        } catch (error) {
            footer.classList.add('delight-footer--in');
        }
    })();

    document.querySelectorAll('.article-card__image').forEach(function (image) {
        image.addEventListener('error', function () {
            image.classList.add('article-card__image--unavailable');
        });
    });

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
