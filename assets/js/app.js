jQuery(function ($) {
    const themeBtn = $('#theme-toggle');
    const sunIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>';
    const moonIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';

    if ($('html').hasClass('dark')) {
        themeBtn.html(sunIcon);
    } else {
        themeBtn.html(moonIcon);
    }

    themeBtn.on('click', function () {
        $('html').toggleClass('dark');
        if ($('html').hasClass('dark')) {
            localStorage.setItem('color-theme', 'dark');
            themeBtn.html(sunIcon);
        } else {
            localStorage.setItem('color-theme', 'light');
            themeBtn.html(moonIcon);
        }
    });

    const mobileMenu = $('#mobile-menu');
    const menuOverlay = $('#menu-overlay');
    $('#mobile-menu-toggle').on('click', function () {
        mobileMenu.removeClass('translate-x-full');
        menuOverlay.removeClass('hidden');
        $('body').addClass('overflow-hidden');
    });
    $('#close-menu, #menu-overlay').on('click', function () {
        mobileMenu.addClass('translate-x-full');
        menuOverlay.addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });

    const searchOverlay = $('#search-overlay');
    $('#search-toggle, #search-mobile-toggle').on('click', function (e) {
        e.preventDefault();
        searchOverlay.removeClass('hidden').hide().fadeIn(300);
        setTimeout(function () {
            $('#search-input').trigger('focus');
        }, 300);
        $('body').addClass('overflow-hidden');
    });
    $('#search-close').on('click', function () {
        searchOverlay.fadeOut(300, function () {
            $(this).addClass('hidden');
            $('body').removeClass('overflow-hidden');
        });
    });

    const randomFilmPopup = $('#random-film-popup');
    const randomFilmOpen = $('#random-film-open');
    const randomFilmClose = $('#random-film-close');
    const randomFilmNext = $('#random-film-next');
    const randomFilmRetry = $('#random-film-retry');
    const randomFilmDialog = $('#random-film-dialog');
    const randomFilmLoading = $('#random-film-loading');
    const randomFilmError = $('#random-film-error');
    const randomFilmErrorText = $('#random-film-error-text');
    const randomFilmContent = $('#random-film-content');
    const randomFilmImage = $('#random-film-image');
    const randomFilmCategory = $('#random-film-category');
    const randomFilmCategorySelect = $('#random-film-category-select');
    const randomFilmTitle = $('#random-film-title');
    const randomFilmExcerpt = $('#random-film-excerpt');
    const randomFilmReadLink = $('#random-film-read-link');

    if (randomFilmPopup.length && randomFilmOpen.length) {
        const shownFilmIds = [];
        let isRandomFilmLoading = false;

        function unlockBodyIfNoOverlayOpen() {
            const isSearchOpen = searchOverlay.length && !searchOverlay.hasClass('hidden');
            const isMenuOpen = menuOverlay.length && !menuOverlay.hasClass('hidden');

            if (!isSearchOpen && !isMenuOpen) {
                $('body').removeClass('overflow-hidden');
            }
        }

        function setRandomFilmLoading(isLoading) {
            isRandomFilmLoading = isLoading;
            if (isLoading) {
                randomFilmLoading.removeClass('hidden').addClass('flex');
                randomFilmError.addClass('hidden');
                return;
            }

            randomFilmLoading.addClass('hidden').removeClass('flex');
        }

        function renderRandomFilm(film) {
            randomFilmTitle.text(film.title || '');
            randomFilmExcerpt.text(film.excerpt || '');

            if (film.category) {
                randomFilmCategory.text(film.category).removeClass('hidden');
            } else {
                randomFilmCategory.text('').addClass('hidden');
            }

            randomFilmReadLink.attr('href', film.permalink || '#');
            randomFilmImage.attr('src', film.image || '');
            randomFilmImage.attr('alt', film.title || '');

            randomFilmError.addClass('hidden');
            randomFilmContent.removeClass('hidden');

            const filmId = parseInt(film.id, 10);
            if (Number.isFinite(filmId) && filmId > 0 && shownFilmIds.indexOf(filmId) === -1) {
                shownFilmIds.push(filmId);
                if (shownFilmIds.length > 15) {
                    shownFilmIds.shift();
                }
            }
        }

        function showRandomFilmError(message) {
            randomFilmErrorText.text(message || 'تعذر تحميل الاقتراح حالياً.');
            randomFilmContent.addClass('hidden');
            randomFilmError.removeClass('hidden');
        }

        function requestRandomFilm() {
            if (isRandomFilmLoading) {
                return;
            }

            const selectedCategoryId = randomFilmCategorySelect.length
                ? parseInt(randomFilmCategorySelect.val(), 10) || 0
                : 0;

            setRandomFilmLoading(true);

            $.post(mazaq_ajax.ajax_url, {
                action: mazaq_ajax.random_film_action || 'mazaq_get_random_film',
                nonce: mazaq_ajax.random_film_nonce,
                category_id: selectedCategoryId,
                exclude_ids: shownFilmIds
            }).done(function (response) {
                if (response.success && response.data && response.data.film) {
                    renderRandomFilm(response.data.film);
                    return;
                }

                const message = response && response.data && response.data.message
                    ? response.data.message
                    : 'تعذر تحميل الاقتراح حالياً.';
                showRandomFilmError(message);
            }).fail(function () {
                showRandomFilmError('حدث خطأ في الاتصال. حاول مرة أخرى.');
            }).always(function () {
                setRandomFilmLoading(false);
            });
        }

        function openRandomFilmPopup() {
            randomFilmPopup.removeClass('hidden').addClass('flex');
            randomFilmPopup.attr('aria-hidden', 'false');
            $('body').addClass('overflow-hidden');
            requestRandomFilm();
        }

        function closeRandomFilmPopup() {
            randomFilmPopup.addClass('hidden').removeClass('flex');
            randomFilmPopup.attr('aria-hidden', 'true');
            unlockBodyIfNoOverlayOpen();
        }

        randomFilmOpen.on('click', function (e) {
            e.preventDefault();
            openRandomFilmPopup();
        });

        randomFilmClose.on('click', function (e) {
            e.preventDefault();
            closeRandomFilmPopup();
        });

        randomFilmPopup.on('click', function (e) {
            if (!randomFilmDialog.length) {
                closeRandomFilmPopup();
                return;
            }

            if (randomFilmDialog.is(e.target) || randomFilmDialog.has(e.target).length) {
                return;
            }

            closeRandomFilmPopup();
        });

        randomFilmNext.on('click', function (e) {
            e.preventDefault();
            requestRandomFilm();
        });

        randomFilmRetry.on('click', function (e) {
            e.preventDefault();
            requestRandomFilm();
        });

        if (randomFilmCategorySelect.length) {
            randomFilmCategorySelect.on('change', function () {
                shownFilmIds.length = 0;

                if (!randomFilmPopup.hasClass('hidden')) {
                    requestRandomFilm();
                }
            });
        }

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && !randomFilmPopup.hasClass('hidden')) {
                closeRandomFilmPopup();
            }
        });
    }

    const lazyImages = $('.lazy-image');
    if ('IntersectionObserver' in window) {
        const lazyObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                const img = $(entry.target);
                const src = img.data('src');
                if (src) {
                    img.attr('src', src);
                    img.on('load', function () {
                        $(this).addClass('loaded');
                    });
                    img.removeAttr('data-src');
                }
                observer.unobserve(entry.target);
            });
        });
        lazyImages.each(function () {
            lazyObserver.observe(this);
        });
    }

    const progressBar = $('#reading-progress-bar');
    if (progressBar.length) {
        $(window).on('scroll', function () {
            const s = $(window).scrollTop();
            const d = $(document).height();
            const c = $(window).height();
            const scrollPercent = (s / (d - c)) * 100;
            progressBar.css('width', scrollPercent + '%');
        });
    }

    // Font size controls — use event delegation so they always work
    var fontStorageKey = 'mazaq-article-font-size';
    var fontMin = 14;
    var fontMax = 26;
    var storedFont = parseInt(localStorage.getItem(fontStorageKey) || '18', 10);
    var currentFontSize = isFinite(storedFont) ? storedFont : 18;
    currentFontSize = Math.max(fontMin, Math.min(fontMax, currentFontSize));

    function applyFontSize() {
        var el = document.querySelector('.article-content');
        if (!el) { return; }
        el.classList.add('font-resized');
        el.style.setProperty('--article-font-size', currentFontSize + 'px');
        // Force font-size on every child element directly — overrides Gutenberg/Tailwind inline styles
        var targets = el.querySelectorAll('p, li, blockquote, td, th, .wp-block-paragraph, .wp-block-list, .wp-block-quote');
        targets.forEach(function (child) {
            child.style.setProperty('font-size', currentFontSize + 'px', 'important');
        });
        // Headings: scale relative to base
        el.querySelectorAll('h2').forEach(function (h) { h.style.setProperty('font-size', (currentFontSize + 10) + 'px', 'important'); });
        el.querySelectorAll('h3').forEach(function (h) { h.style.setProperty('font-size', (currentFontSize + 6) + 'px', 'important'); });
        localStorage.setItem(fontStorageKey, String(currentFontSize));
    }

    // Apply stored size on page load if article exists
    if (document.querySelector('.article-content')) {
        applyFontSize();
    }

    // Event delegation — works regardless of DOM structure
    $(document).on('click', '#font-increase', function (e) {
        e.preventDefault();
        if (currentFontSize < fontMax) {
            currentFontSize += 2;
            applyFontSize();
        }
    });
    $(document).on('click', '#font-decrease', function (e) {
        e.preventDefault();
        if (currentFontSize > fontMin) {
            currentFontSize -= 2;
            applyFontSize();
        }
    });

    const container = $('#infinite-scroll-container');
    const loadingIndicator = $('#loading-indicator');
    
    if (container.length && loadingIndicator.length) {
        let currentPage = 2;
        let isLoading = false;
        let hasMore = true;
        
        // Show indicator initially since observer relies on intersection
        loadingIndicator.removeClass('hidden');

        const infiniteScrollObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting || isLoading || !hasMore) {
                    return;
                }

                isLoading = true;

                $.post(mazaq_ajax.ajax_url, {
                    action: 'load_more_posts',
                    nonce: mazaq_ajax.nonce,
                    page: currentPage
                }).done(function (response) {
                    if (response.success && response.data.html) {
                        container.append(response.data.html);
                        hasMore = !!response.data.has_more;
                        currentPage += 1;
                    } else {
                        hasMore = false;
                    }
                    
                    if (!hasMore) {
                        infiniteScrollObserver.disconnect();
                        loadingIndicator.addClass('hidden');
                        container.after('<div class="text-center text-slate-500 dark:text-slate-400 py-8 text-sm">تم الوصول إلى نهاية المقالات</div>');
                    }
                }).always(function () {
                    isLoading = false;
                });
            });
        }, {
            // Options: trigger when indicator comes within 300px of viewport
            rootMargin: '0px 0px 300px 0px',
            threshold: 0
        });

        // Start observing the loading indicator
        infiniteScrollObserver.observe(loadingIndicator[0]);
    }

    const adBlockPromptStorageKey = 'toc_adblock_prompt_dismissed_until';
    const adBlockPrompts = {
        friendly: {
            title: 'وجودك معنا يفرق',
            body: 'الإعلانات الخفيفة تساعدنا على الاستمرار في تقديم محتوى مجاني ومفيد. إذا رغبت، عطّل مانع الإعلانات لهذا الموقع.',
            cta: 'دعم الموقع'
        },
        practical: {
            title: 'ساعدنا نحافظ على المحتوى مجانيًا',
            body: 'نعتمد على الإعلانات لتغطية تكاليف التحرير والاستضافة. أضف موقعنا إلى القائمة المسموح بها.',
            cta: 'السماح بالإعلانات'
        },
        minimalist: {
            title: 'المحتوى المجاني يحتاج دعمك',
            body: 'رجاءً اسمح بالإعلانات لهذا الموقع.',
            cta: 'تم'
        }
    };

    function getMutedUntil() {
        try {
            return parseInt(localStorage.getItem(adBlockPromptStorageKey) || '0', 10);
        } catch (e) {
            return 0;
        }
    }

    function mutePromptForDays(days) {
        try {
            const mutedUntil = Date.now() + (days * 24 * 60 * 60 * 1000);
            localStorage.setItem(adBlockPromptStorageKey, String(mutedUntil));
        } catch (e) {
            // Ignore localStorage failures (private mode, blocked storage, etc.)
        }
    }

    function isPromptMuted() {
        const mutedUntil = getMutedUntil();
        return Number.isFinite(mutedUntil) && mutedUntil > Date.now();
    }

    function pickPromptVariant() {
        if (window.matchMedia('(max-width: 480px)').matches) {
            return 'minimalist';
        }
        if (document.body.classList.contains('single')) {
            return 'practical';
        }
        return 'friendly';
    }

    function renderAdBlockPrompt() {
        if ($('#toc-adblock-prompt').length) {
            return;
        }

        const promptCopy = adBlockPrompts[pickPromptVariant()] || adBlockPrompts.friendly;
        const prompt = $(
            '<aside id="toc-adblock-prompt" class="adblock-prompt" role="dialog" aria-live="polite" aria-label="دعم الموقع">' +
                '<button type="button" class="adblock-prompt-close" aria-label="إغلاق الرسالة">&times;</button>' +
                '<h3 class="adblock-prompt-title"></h3>' +
                '<p class="adblock-prompt-body"></p>' +
                '<button type="button" class="adblock-prompt-action"></button>' +
            '</aside>'
        );

        prompt.find('.adblock-prompt-title').text(promptCopy.title);
        prompt.find('.adblock-prompt-body').text(promptCopy.body);
        prompt.find('.adblock-prompt-action').text(promptCopy.cta);

        prompt.on('click', '.adblock-prompt-close', function () {
            mutePromptForDays(3);
            prompt.remove();
        });

        prompt.on('click', '.adblock-prompt-action', function () {
            mutePromptForDays(14);
            prompt.remove();
        });

        $('body').append(prompt);
    }

    function detectAdBlocker(callback) {
        const bait = document.createElement('div');
        bait.className = 'adsbox ad-banner ad-unit ad-zone';
        bait.setAttribute('aria-hidden', 'true');
        bait.style.position = 'absolute';
        bait.style.left = '-9999px';
        bait.style.top = '-9999px';
        bait.style.width = '1px';
        bait.style.height = '1px';
        bait.style.pointerEvents = 'none';
        document.body.appendChild(bait);

        window.setTimeout(function () {
            const computed = window.getComputedStyle(bait);
            const blocked = (
                bait.offsetWidth === 0 ||
                bait.offsetHeight === 0 ||
                computed.display === 'none' ||
                computed.visibility === 'hidden'
            );
            bait.remove();
            callback(blocked);
        }, 120);
    }

    if (!isPromptMuted()) {
        detectAdBlocker(function (isBlocked) {
            if (isBlocked) {
                renderAdBlockPrompt();
            }
        });
    }

    $('.filter-btn').on('click', function () {
        const filterBtns = $('.filter-btn');
        filterBtns.removeClass('bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-md').addClass('bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700');
        $(this).removeClass('bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700').addClass('bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-md border-transparent');

        const filterVal = $(this).data('filter');
        const items = $('.archive-item');
        items.hide();
        if (filterVal === 'all') {
            items.fadeIn(200);
        } else {
            $('.archive-item[data-category="' + filterVal + '"]').fadeIn(200);
        }
    });
});

/**
 * Hero Carousel Controller
 * Implements cross-fade transitions for multiple sticky posts.
 */
(function () {
    const track = document.querySelector('.hero-carousel');
    if (!track) {
        return;
    }

    const slides = track.querySelectorAll('.hero-carousel__slide');
    const dots = track.querySelectorAll('.hero-carousel__dot');
    const totalSlides = parseInt(track.dataset.total, 10) || slides.length;
    const intervalMs = parseInt(track.dataset.interval, 10) || 6000;

    let currentIndex = 0;
    let timer = null;
    let isPaused = false;
    let touchStartX = 0;

    function goTo(n) {
        // Remove active classes from current slide and dot
        slides[currentIndex].classList.remove('opacity-100', 'z-10');
        slides[currentIndex].classList.add('opacity-0', 'z-0');
        dots[currentIndex].classList.remove('bg-white', 'w-6', 'active');

        // Update index (with wrapping)
        currentIndex = (n + totalSlides) % totalSlides;

        // Add active classes to new current slide and dot
        slides[currentIndex].classList.remove('opacity-0', 'z-0');
        slides[currentIndex].classList.add('opacity-100', 'z-10');
        dots[currentIndex].classList.add('bg-white', 'w-6', 'active');

        resetTimer();
    }

    function next() {
        goTo(currentIndex + 1);
    }

    function prev() {
        goTo(currentIndex - 1);
    }

    function startTimer() {
        if (timer) {
            clearInterval(timer);
        }
        timer = setInterval(() => {
            if (!isPaused) {
                next();
            }
        }, intervalMs);
    }

    function resetTimer() {
        startTimer();
    }

    // Dot Click Events
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            goTo(parseInt(dot.dataset.index, 10));
        });
    });

    // Hover Pause
    track.addEventListener('mouseenter', () => {
        isPaused = true;
    });
    track.addEventListener('mouseleave', () => {
        isPaused = false;
    });

    // Touch Swipe
    track.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });

    track.addEventListener('touchend', e => {
        const delta = e.changedTouches[0].clientX - touchStartX;
        if (delta > 50) {
            prev();
        } else if (delta < -50) {
            next();
        }
    }, { passive: true });

    // Initialize Auto-advance
    startTimer();
}());

