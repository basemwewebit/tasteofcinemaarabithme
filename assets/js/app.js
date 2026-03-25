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

    const notificationRoot = $('#mazaq-notification-root');
    const notificationPrompt = $('#mazaq-notification-prompt');
    const notificationPromptClose = $('#mazaq-notification-prompt-close');
    const notificationPromptDismiss = $('#mazaq-notification-dismiss');
    const notificationPromptSubscribe = $('#mazaq-notification-subscribe');
    const notificationPromptStatus = $('#mazaq-notification-prompt-status');
    const notificationToast = $('#mazaq-notification-toast');
    const notificationToastClose = $('#mazaq-notification-toast-close');
    const notificationToastDismiss = $('#mazaq-notification-toast-dismiss');
    const notificationToastKicker = $('#mazaq-notification-toast-kicker');
    const notificationToastTitle = $('#mazaq-notification-toast-title');
    const notificationToastBody = $('#mazaq-notification-toast-body');
    const notificationToastLink = $('#mazaq-notification-toast-link');

    if (notificationRoot.length) {
        const promptDismissKey = 'mazaq_notification_prompt_dismissed_until';
        const subscribedKey = 'mazaq_notification_subscribed';
        const seenNotificationsKey = 'mazaq_notification_seen_ids';
        const promptDelayMs = 45000;
        let notificationBootstrap = null;
        let serviceWorkerPromise = null;
        let isSubscribed = false;
        let promptShown = false;
        let engagementTriggered = false;
        let promptTimerId = null;
        let fallbackQueue = [];

        function storageGet(key, fallbackValue) {
            try {
                const value = localStorage.getItem(key);
                return value === null ? fallbackValue : value;
            } catch (e) {
                return fallbackValue;
            }
        }

        function storageSet(key, value) {
            try {
                localStorage.setItem(key, value);
            } catch (e) {
                // Ignore storage errors.
            }
        }

        function storageRemove(key) {
            try {
                localStorage.removeItem(key);
            } catch (e) {
                // Ignore storage errors.
            }
        }

        function getSeenNotificationIds() {
            try {
                const parsed = JSON.parse(storageGet(seenNotificationsKey, '[]'));
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function setSeenNotificationIds(ids) {
            storageSet(seenNotificationsKey, JSON.stringify(ids.slice(-20)));
        }

        function hasSeenNotification(id) {
            if (!id) {
                return false;
            }

            return getSeenNotificationIds().indexOf(id) !== -1;
        }

        function markNotificationSeen(id) {
            if (!id || hasSeenNotification(id)) {
                return;
            }

            const seenIds = getSeenNotificationIds();
            seenIds.push(id);
            setSeenNotificationIds(seenIds);
        }

        function getPromptMutedUntil() {
            const rawValue = parseInt(storageGet(promptDismissKey, '0'), 10);
            return Number.isFinite(rawValue) ? rawValue : 0;
        }

        function mutePromptForDays(days) {
            storageSet(promptDismissKey, String(Date.now() + (days * 24 * 60 * 60 * 1000)));
        }

        function isPromptMuted() {
            return getPromptMutedUntil() > Date.now();
        }

        function supportsPushNotifications() {
            return (
                'Notification' in window &&
                'serviceWorker' in navigator &&
                'PushManager' in window
            );
        }

        function shouldOfferPrompt() {
            if (!notificationBootstrap || !notificationBootstrap.promptEligible) {
                return false;
            }

            if (!supportsPushNotifications()) {
                return false;
            }

            if (Notification.permission === 'denied') {
                return false;
            }

            if (isPromptMuted() || isSubscribed) {
                return false;
            }

            return true;
        }

        function showPromptStatus(message, isError) {
            if (!notificationPromptStatus.length) {
                return;
            }

            notificationPromptStatus
                .text(message || '')
                .removeClass('hidden text-slate-600 text-red-600 dark:text-slate-200 dark:text-red-300')
                .addClass(isError ? 'text-red-600 dark:text-red-300' : 'text-slate-600 dark:text-slate-200');
        }

        function clearPromptStatus() {
            notificationPromptStatus.addClass('hidden').text('');
        }

        function hidePrompt() {
            notificationPrompt.addClass('hidden');
        }

        function showPrompt() {
            if (promptShown || !shouldOfferPrompt()) {
                return;
            }

            promptShown = true;
            clearPromptStatus();
            notificationPrompt.removeClass('hidden');
        }

        function hideToast() {
            notificationToast.addClass('hidden');
        }

        function nextUnseenNotification() {
            for (let index = 0; index < fallbackQueue.length; index += 1) {
                const item = fallbackQueue[index];
                if (item && item.id && !hasSeenNotification(item.id)) {
                    return item;
                }
            }

            return null;
        }

        function showNextFallbackToast() {
            if (isSubscribed) {
                hideToast();
                return;
            }

            const notification = nextUnseenNotification();
            if (!notification) {
                hideToast();
                return;
            }

            notificationToast.data('notificationId', notification.id || '');
            notificationToastKicker.text(notification.type === 'new_post' ? 'مقال جديد' : 'اقتراح اليوم');
            notificationToastTitle.text(notification.title || '');
            notificationToastBody.text(notification.body || '');
            notificationToastLink.attr('href', notification.url || mazaq_ajax.home_url || '/');
            notificationToast.removeClass('hidden');
        }

        function encodeApplicationServerKey(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const normalized = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const raw = window.atob(normalized);
            const output = new Uint8Array(raw.length);

            for (let i = 0; i < raw.length; i += 1) {
                output[i] = raw.charCodeAt(i);
            }

            return output;
        }

        function ensureServiceWorker() {
            if (!supportsPushNotifications() || !mazaq_ajax.notifications_service_worker_url) {
                return Promise.resolve(null);
            }

            if (!serviceWorkerPromise) {
                serviceWorkerPromise = navigator.serviceWorker.register(
                    mazaq_ajax.notifications_service_worker_url,
                    { scope: '/' }
                ).catch(function () {
                    serviceWorkerPromise = null;
                    return null;
                });
            }

            return serviceWorkerPromise;
        }

        function fetchJson(url, options) {
            return fetch(url, options).then(function (response) {
                return response.json().then(function (data) {
                    return {
                        ok: response.ok,
                        status: response.status,
                        data: data
                    };
                });
            });
        }

        function setSubscribedState(subscribed) {
            isSubscribed = !!subscribed;

            if (isSubscribed) {
                storageSet(subscribedKey, '1');
                hidePrompt();
                hideToast();
                return;
            }

            storageRemove(subscribedKey);
        }

        function syncExistingSubscriptionState() {
            if (!notificationBootstrap || !notificationBootstrap.publicVapidKey || !supportsPushNotifications()) {
                setSubscribedState(storageGet(subscribedKey, '') === '1');
                showNextFallbackToast();
                return Promise.resolve();
            }

            return ensureServiceWorker().then(function (registration) {
                if (!registration || !registration.pushManager) {
                    setSubscribedState(storageGet(subscribedKey, '') === '1');
                    showNextFallbackToast();
                    return;
                }

                return registration.pushManager.getSubscription().then(function (subscription) {
                    setSubscribedState(!!subscription);
                    showNextFallbackToast();
                }).catch(function () {
                    setSubscribedState(storageGet(subscribedKey, '') === '1');
                    showNextFallbackToast();
                });
            });
        }

        function subscribeToNotifications() {
            if (!notificationBootstrap || !notificationBootstrap.publicVapidKey) {
                showPromptStatus('التنبيهات غير جاهزة حالياً. أضف مفاتيح VAPID من لوحة التحكم.', true);
                return;
            }

            if (!supportsPushNotifications()) {
                showPromptStatus('متصفحك لا يدعم تنبيهات الويب.', true);
                return;
            }

            notificationPromptSubscribe.prop('disabled', true).text('جارٍ التفعيل...');
            clearPromptStatus();

            ensureServiceWorker().then(function (registration) {
                if (!registration || !registration.pushManager) {
                    throw new Error('service-worker');
                }

                if (Notification.permission === 'denied') {
                    throw new Error('permission-denied');
                }

                const permissionPromise = Notification.permission === 'granted'
                    ? Promise.resolve('granted')
                    : Notification.requestPermission();

                return permissionPromise.then(function (permission) {
                    if (permission !== 'granted') {
                        throw new Error(permission === 'denied' ? 'permission-denied' : 'permission-default');
                    }

                    return registration.pushManager.getSubscription().then(function (existingSubscription) {
                        if (existingSubscription) {
                            return existingSubscription;
                        }

                        return registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: encodeApplicationServerKey(notificationBootstrap.publicVapidKey)
                        });
                    });
                });
            }).then(function (subscription) {
                return fetchJson(mazaq_ajax.notifications_subscription_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(subscription.toJSON())
                });
            }).then(function (response) {
                if (!response.ok || !response.data || !response.data.success) {
                    throw new Error('subscription-save');
                }

                setSubscribedState(true);
                showPromptStatus('تم تفعيل التنبيهات بنجاح.', false);
                window.setTimeout(hidePrompt, 900);
            }).catch(function (error) {
                if (error && error.message === 'permission-denied') {
                    showPromptStatus('تم حظر التنبيهات من المتصفح. يمكنك إعادة تفعيلها من إعدادات المتصفح.', true);
                    mutePromptForDays(7);
                    window.setTimeout(hidePrompt, 1500);
                    return;
                }

                if (error && error.message === 'permission-default') {
                    showPromptStatus('لم يتم منح الإذن بعد. جرّب مرة أخرى عندما تكون جاهزاً.', true);
                    return;
                }

                showPromptStatus('تعذر تفعيل التنبيهات الآن. حاول لاحقاً.', true);
            }).finally(function () {
                notificationPromptSubscribe.prop('disabled', false).text('اشترك الآن');
            });
        }

        function dismissPromptForLater() {
            mutePromptForDays(7);
            hidePrompt();
        }

        function revealPromptAfterEngagement() {
            if (engagementTriggered) {
                return;
            }

            engagementTriggered = true;
            if (promptTimerId) {
                window.clearTimeout(promptTimerId);
            }

            showPrompt();
            window.removeEventListener('scroll', handleScrollEngagement);
        }

        function handleScrollEngagement() {
            const doc = document.documentElement;
            const maxScroll = doc.scrollHeight - window.innerHeight;
            if (maxScroll <= 0) {
                return;
            }

            const scrollRatio = window.scrollY / maxScroll;
            if (scrollRatio >= 0.5) {
                revealPromptAfterEngagement();
            }
        }

        function initPromptEngagementWatchers() {
            if (!shouldOfferPrompt()) {
                return;
            }

            promptTimerId = window.setTimeout(revealPromptAfterEngagement, promptDelayMs);
            window.addEventListener('scroll', handleScrollEngagement, { passive: true });
        }

        function hydrateNotificationBootstrap() {
            if (!mazaq_ajax.notifications_bootstrap_url) {
                return;
            }

            fetchJson(mazaq_ajax.notifications_bootstrap_url, {
                credentials: 'same-origin'
            }).then(function (response) {
                if (!response.ok || !response.data) {
                    return;
                }

                notificationBootstrap = response.data;
                fallbackQueue = Array.isArray(notificationBootstrap.fallbackNotifications)
                    ? notificationBootstrap.fallbackNotifications
                    : [];

                return syncExistingSubscriptionState().then(function () {
                    initPromptEngagementWatchers();
                });
            }).catch(function () {
                // Ignore bootstrap failures and leave notifications inactive for this request.
            });
        }

        notificationPromptSubscribe.on('click', function (e) {
            e.preventDefault();
            subscribeToNotifications();
        });

        notificationPromptClose.on('click', function (e) {
            e.preventDefault();
            dismissPromptForLater();
        });

        notificationPromptDismiss.on('click', function (e) {
            e.preventDefault();
            dismissPromptForLater();
        });

        notificationToastClose.on('click', function (e) {
            e.preventDefault();
            const currentId = notificationToast.data('notificationId');
            markNotificationSeen(currentId);
            showNextFallbackToast();
        });

        notificationToastDismiss.on('click', function (e) {
            e.preventDefault();
            const currentId = notificationToast.data('notificationId');
            markNotificationSeen(currentId);
            showNextFallbackToast();
        });

        notificationToastLink.on('click', function () {
            const currentId = notificationToast.data('notificationId');
            markNotificationSeen(currentId);
        });

        hydrateNotificationBootstrap();
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

    // Back to top button
    const backToTopBtn = $('#back-to-top');
    if (backToTopBtn.length) {
        const showThreshold = 300;

        $(window).on('scroll', function () {
            if ($(this).scrollTop() > showThreshold) {
                backToTopBtn.addClass('visible');
            } else {
                backToTopBtn.removeClass('visible');
            }
        });

        backToTopBtn.on('click', function (e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 600);
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

    const adBlockConfig = (window.mazaq_ajax && window.mazaq_ajax.adblock) || {};
    const adBlockStateSessionKey = adBlockConfig.session_storage_key || 'mazaq_adblock_state';
    const adBlockPromptStorageKey = adBlockConfig.mute_storage_key || 'mazaq_adblock_prompt_muted_until';
    const adBlockPromptShownSessionKey = 'mazaq_adblock_prompt_session_shown';
    const adContainerSelector = '[data-ad-container="true"][data-expects-network-ad="1"]';
    const fallbackCopy = {
        title: adBlockConfig.fallback_title || 'ادعم استمرار المحتوى',
        body: adBlockConfig.fallback_body || 'هذه المساحة غير متاحة حاليًا. يمكنك دعم الموقع بالسماح بالإعلانات أو التواصل معنا.',
        cta: adBlockConfig.fallback_cta || 'اعرف كيف تدعمنا',
        supportUrl: adBlockConfig.support_url || (mazaq_ajax && mazaq_ajax.home_url ? mazaq_ajax.home_url : '/')
    };
    const promptCopy = {
        title: adBlockConfig.prompt_title || 'يسعدنا دعمك للموقع',
        body: adBlockConfig.prompt_body || 'الإعلانات الخفيفة تساعدنا في استمرار المحتوى مجانًا. يمكنك متابعة التصفح أو التواصل معنا للدعم.',
        primaryCta: adBlockConfig.prompt_primary_cta || 'اعرف كيف تدعمنا',
        secondaryCta: adBlockConfig.prompt_secondary_cta || 'متابعة التصفح',
        supportUrl: adBlockConfig.support_url || (mazaq_ajax && mazaq_ajax.home_url ? mazaq_ajax.home_url : '/')
    };

    function pushMonetizationEvent(eventName, payload) {
        const dataLayer = window.dataLayer = window.dataLayer || [];
        dataLayer.push($.extend({
            event: eventName,
            source: 'adblock_module'
        }, payload || {}));
    }

    function getLocalNumber(key) {
        try {
            return parseInt(localStorage.getItem(key) || '0', 10);
        } catch (e) {
            return 0;
        }
    }

    function setLocalNumber(key, value) {
        try {
            localStorage.setItem(key, String(value));
        } catch (e) {
            // Ignore localStorage failures (private mode, blocked storage, etc.)
        }
    }

    function getSessionJson(key) {
        try {
            const raw = sessionStorage.getItem(key);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function setSessionJson(key, value) {
        try {
            sessionStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            // Ignore sessionStorage failures.
        }
    }

    function isPromptMuted() {
        const mutedUntil = getLocalNumber(adBlockPromptStorageKey);
        return Number.isFinite(mutedUntil) && mutedUntil > Date.now();
    }

    function mutePromptForDays(days) {
        setLocalNumber(adBlockPromptStorageKey, Date.now() + (days * 24 * 60 * 60 * 1000));
    }

    function detectByBaitElement() {
        return new Promise(function (resolve) {
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
                resolve(blocked);
            }, 140);
        });
    }

    function hasLoadedAnyAdFrame() {
        const adContainers = document.querySelectorAll(adContainerSelector);
        if (!adContainers.length) {
            return false;
        }

        return Array.prototype.some.call(adContainers, function (container) {
            const adNode = container.querySelector('ins.adsbygoogle, ins[data-ad-ins="true"]');
            if (!adNode) {
                return false;
            }

            if (adNode.querySelector('iframe')) {
                return true;
            }

            const status = adNode.getAttribute('data-adsbygoogle-status');
            if (status === 'done') {
                return true;
            }

            return false;
        });
    }

    function isContainerAdFilled(container) {
        const adNode = container.querySelector('ins.adsbygoogle, ins[data-ad-ins="true"]');
        if (!adNode) {
            return false;
        }

        if (adNode.querySelector('iframe')) {
            return true;
        }

        return adNode.getAttribute('data-adsbygoogle-status') === 'done';
    }

    function detectByRuntimeSignals() {
        return new Promise(function (resolve) {
            const expectedAdCount = document.querySelectorAll(adContainerSelector).length;
            if (!expectedAdCount) {
                resolve(false);
                return;
            }

            window.setTimeout(function () {
                const hasAdSenseScript = !!document.querySelector('script[src*="pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"]');
                const hasRuntime = typeof window.adsbygoogle !== 'undefined';
                const hasRenderedFrame = hasLoadedAnyAdFrame();
                resolve(Boolean(hasAdSenseScript && !hasRuntime && !hasRenderedFrame));
            }, 2600);
        });
    }

    async function resolveAdBlockState() {
        const cached = getSessionJson(adBlockStateSessionKey);
        if (cached && typeof cached.blocked === 'boolean') {
            return cached;
        }

        const results = await Promise.all([
            detectByBaitElement(),
            detectByRuntimeSignals()
        ]);

        const state = {
            blocked: Boolean(results[0] || results[1]),
            baitBlocked: Boolean(results[0]),
            runtimeBlocked: Boolean(results[1]),
            timestamp: Date.now()
        };

        setSessionJson(adBlockStateSessionKey, state);
        return state;
    }

    function createFallbackNode(slotName) {
        const card = document.createElement('section');
        card.className = 'ad-fallback-card';

        const title = document.createElement('h4');
        title.className = 'ad-fallback-card__title';
        title.textContent = fallbackCopy.title;

        const body = document.createElement('p');
        body.className = 'ad-fallback-card__body';
        body.textContent = fallbackCopy.body;

        const link = document.createElement('a');
        link.className = 'ad-fallback-card__cta';
        link.href = fallbackCopy.supportUrl;
        link.textContent = fallbackCopy.cta;
        link.setAttribute('rel', 'noopener');

        link.addEventListener('click', function () {
            pushMonetizationEvent('ad_fallback_click', {
                slot_name: slotName || 'unknown'
            });
        });

        card.appendChild(title);
        card.appendChild(body);
        card.appendChild(link);

        return card;
    }

    function applyAdFallbackToEmptySlots(forceFallback) {
        const adContainers = document.querySelectorAll(adContainerSelector);
        if (!adContainers.length) {
            return;
        }

        Array.prototype.forEach.call(adContainers, function (container) {
            if (container.getAttribute('data-ad-fallback-rendered') === '1') {
                return;
            }

            const slotName = container.getAttribute('data-slot-name') || 'unknown';
            const hasFilledAd = isContainerAdFilled(container);

            if (!forceFallback && hasFilledAd) {
                return;
            }

            const adNode = container.querySelector('ins.adsbygoogle, ins[data-ad-ins="true"]');
            const adStatus = adNode ? adNode.getAttribute('data-adsbygoogle-status') : '';
            const shouldFallback = forceFallback || !adNode || adStatus === 'unfilled' || !hasFilledAd;

            if (!shouldFallback) {
                return;
            }

            container.innerHTML = '';
            container.classList.add('ad-container--fallback');
            container.appendChild(createFallbackNode(slotName));
            container.setAttribute('data-ad-fallback-rendered', '1');

            pushMonetizationEvent('ad_fallback_rendered', {
                slot_name: slotName,
                forced: forceFallback ? 1 : 0
            });
        });
    }

    function renderAdBlockPrompt() {
        if ($('#toc-adblock-prompt').length || isPromptMuted()) {
            return;
        }

        const prompt = $(
            '<aside id="toc-adblock-prompt" class="adblock-prompt" role="dialog" aria-live="polite" aria-label="دعم الموقع">' +
                '<button type="button" class="adblock-prompt-close" aria-label="إغلاق الرسالة">&times;</button>' +
                '<h3 class="adblock-prompt-title"></h3>' +
                '<p class="adblock-prompt-body"></p>' +
                '<div class="adblock-prompt-actions">' +
                    '<a class="adblock-prompt-action" target="_self"></a>' +
                    '<button type="button" class="adblock-prompt-secondary"></button>' +
                '</div>' +
            '</aside>'
        );

        prompt.find('.adblock-prompt-title').text(promptCopy.title);
        prompt.find('.adblock-prompt-body').text(promptCopy.body);
        prompt.find('.adblock-prompt-action').attr('href', promptCopy.supportUrl).text(promptCopy.primaryCta);
        prompt.find('.adblock-prompt-secondary').text(promptCopy.secondaryCta);

        prompt.on('click', '.adblock-prompt-close', function () {
            mutePromptForDays(3);
            prompt.remove();
            pushMonetizationEvent('ad_prompt_closed');
        });

        prompt.on('click', '.adblock-prompt-secondary', function () {
            mutePromptForDays(3);
            prompt.remove();
            pushMonetizationEvent('ad_prompt_continue');
        });

        prompt.on('click', '.adblock-prompt-action', function () {
            mutePromptForDays(14);
            pushMonetizationEvent('ad_prompt_support_click');
        });

        $('body').append(prompt);
    }

    resolveAdBlockState().then(function (state) {
        if (state.blocked) {
            applyAdFallbackToEmptySlots(true);

            const promptShownInSession = getSessionJson(adBlockPromptShownSessionKey);
            if (!promptShownInSession) {
                window.setTimeout(function () {
                    renderAdBlockPrompt();
                }, 900);
                setSessionJson(adBlockPromptShownSessionKey, true);
            }

            pushMonetizationEvent('adblock_detected', {
                bait_blocked: state.baitBlocked ? 1 : 0,
                runtime_blocked: state.runtimeBlocked ? 1 : 0
            });
        } else {
            window.setTimeout(function () {
                applyAdFallbackToEmptySlots(false);
            }, 4500);
        }

        if ('MutationObserver' in window) {
            const dynamicContainer = document.getElementById('infinite-scroll-container');
            if (dynamicContainer) {
                const observer = new MutationObserver(function () {
                    applyAdFallbackToEmptySlots(state.blocked);
                });
                observer.observe(dynamicContainer, {
                    childList: true,
                    subtree: true
                });
            }
        }
    });

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
 * Drives the cinematic front-page hero.
 */
(function () {
    const root = document.querySelector('.hero-carousel');
    if (!root) {
        return;
    }

    const slides = Array.from(root.querySelectorAll('.hero-carousel__slide'));
    if (!slides.length) {
        return;
    }

    const dots = Array.from(root.querySelectorAll('.hero-carousel__dot'));
    const railItems = Array.from(root.querySelectorAll('.hero-rail__item'));
    const prevButton = root.querySelector('[data-hero-prev]');
    const nextButton = root.querySelector('[data-hero-next]');
    const currentLabel = root.querySelector('[data-hero-current]');
    const mobileProgressBar = root.querySelector('.hero-carousel__progress-bar');
    const totalSlides = parseInt(root.dataset.total, 10) || slides.length;
    const intervalMs = parseInt(root.dataset.interval, 10) || 6000;
    const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    const supportsReducedMotionListener = typeof reduceMotionQuery.addEventListener === 'function';

    let currentIndex = 0;
    let timer = null;
    let isPaused = false;
    let isReady = false;
    let touchStartX = 0;
    let rafId = null;

    function stopTimer() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    function resetBar(bar) {
        if (!bar) {
            return;
        }

        bar.style.transition = 'none';
        bar.style.transform = 'scaleX(0)';
    }

    function animateBar(bar) {
        if (!bar || totalSlides < 2 || reduceMotionQuery.matches || isPaused) {
            return;
        }

        window.requestAnimationFrame(() => {
            bar.style.transition = `transform ${intervalMs}ms linear`;
            bar.style.transform = 'scaleX(1)';
        });
    }

    function syncProgress() {
        railItems.forEach((item, index) => {
            const bar = item.querySelector('.hero-rail__progress-bar');
            resetBar(bar);

            if (index === currentIndex) {
                animateBar(bar);
            }
        });

        resetBar(mobileProgressBar);
        animateBar(mobileProgressBar);
    }

    function syncSlides() {
        slides.forEach((slide, index) => {
            const isActive = index === currentIndex;
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });

        dots.forEach((dot, index) => {
            const isActive = index === currentIndex;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-current', isActive ? 'true' : 'false');
        });

        railItems.forEach((item, index) => {
            const isActive = index === currentIndex;
            item.classList.toggle('is-active', isActive);
            item.setAttribute('aria-current', isActive ? 'true' : 'false');
        });

        if (currentLabel) {
            currentLabel.textContent = String(currentIndex + 1).padStart(2, '0');
        }

        if (isReady) {
            syncProgress();
        }
    }

    function startTimer() {
        if (totalSlides < 2 || reduceMotionQuery.matches) {
            stopTimer();
            return;
        }

        stopTimer();
        timer = window.setInterval(() => {
            if (!isPaused) {
                goTo(currentIndex + 1, { restartTimer: false });
            }
        }, intervalMs);
    }

    function resetTimer() {
        startTimer();
        syncProgress();
    }

    function goTo(n, options) {
        const config = Object.assign({
            restartTimer: true
        }, options || {});

        currentIndex = (n + totalSlides) % totalSlides;
        syncSlides();

        if (config.restartTimer) {
            resetTimer();
        }
    }

    function next() {
        goTo(currentIndex + 1);
    }

    function prev() {
        goTo(currentIndex - 1);
    }

    function onPointerEnter() {
        isPaused = true;
        stopTimer();
        syncProgress();
    }

    function onPointerLeave() {
        isPaused = false;
        startTimer();
        syncProgress();
    }

    function handleScroll() {
        if (reduceMotionQuery.matches) {
            root.style.setProperty('--hero-scroll', '0');
            return;
        }

        if (rafId) {
            return;
        }

        rafId = window.requestAnimationFrame(() => {
            const rect = root.getBoundingClientRect();
            const progress = Math.min(Math.max((window.innerHeight - rect.top) / (window.innerHeight + rect.height), 0), 1);
            root.style.setProperty('--hero-scroll', progress.toFixed(4));
            rafId = null;
        });
    }

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            goTo(parseInt(dot.dataset.index, 10));
        });
    });

    railItems.forEach((item) => {
        item.addEventListener('click', () => {
            goTo(parseInt(item.dataset.index, 10));
        });
    });

    if (prevButton) {
        prevButton.addEventListener('click', prev);
    }

    if (nextButton) {
        nextButton.addEventListener('click', next);
    }

    root.addEventListener('mouseenter', onPointerEnter);
    root.addEventListener('mouseleave', onPointerLeave);
    root.addEventListener('focusin', onPointerEnter);
    root.addEventListener('focusout', () => {
        window.setTimeout(() => {
            if (!root.contains(document.activeElement)) {
                onPointerLeave();
            }
        }, 0);
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            prev();
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            next();
        }
    });

    root.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0].clientX;
    }, { passive: true });

    root.addEventListener('touchend', (event) => {
        const delta = event.changedTouches[0].clientX - touchStartX;
        if (delta > 50) {
            prev();
        } else if (delta < -50) {
            next();
        }
    }, { passive: true });

    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('resize', handleScroll);

    const handleReducedMotionChange = () => {
        if (reduceMotionQuery.matches) {
            stopTimer();
        } else if (!isPaused) {
            startTimer();
        }

        syncProgress();
        handleScroll();
    };

    if (supportsReducedMotionListener) {
        reduceMotionQuery.addEventListener('change', handleReducedMotionChange);
    } else if (typeof reduceMotionQuery.addListener === 'function') {
        reduceMotionQuery.addListener(handleReducedMotionChange);
    }

    syncSlides();
    handleScroll();

    window.requestAnimationFrame(() => {
        isReady = true;
        root.classList.add('is-ready');
        syncProgress();
    });

    if (!reduceMotionQuery.matches) {
        startTimer();
    }
}());
