(function initNotifications() {
    var notificationRoot = document.getElementById('mazaq-notification-root');
    if (!notificationRoot) {
        return;
    }

    var notificationPrompt = document.getElementById('mazaq-notification-prompt');
    var notificationPromptClose = document.getElementById('mazaq-notification-prompt-close');
    var notificationPromptDismiss = document.getElementById('mazaq-notification-dismiss');
    var notificationPromptSubscribe = document.getElementById('mazaq-notification-prompt-subscribe');
    var notificationPromptStatus = document.getElementById('mazaq-notification-prompt-status');
    var notificationToast = document.getElementById('mazaq-notification-toast');
    var notificationToastClose = document.getElementById('mazaq-notification-toast-close');
    var notificationToastDismiss = document.getElementById('mazaq-notification-toast-dismiss');
    var notificationToastKicker = document.getElementById('mazaq-notification-toast-kicker');
    var notificationToastTitle = document.getElementById('mazaq-notification-toast-title');
    var notificationToastBody = document.getElementById('mazaq-notification-toast-body');
    var notificationToastLink = document.getElementById('mazaq-notification-toast-link');

    var promptDismissKey = 'mazaq_notification_prompt_dismissed_until';
    var subscribedKey = 'mazaq_notification_subscribed';
    var seenNotificationsKey = 'mazaq_notification_seen_ids';
    var promptDelayMs = 45000;
    var notificationBootstrap = null;
    var serviceWorkerPromise = null;
    var isSubscribed = false;
    var promptShown = false;
    var engagementTriggered = false;
    var promptTimerId = null;
    var fallbackQueue = [];
    var subscriptionInFlight = false;
    var fetchTimeoutMs = 12000;

    function storageGet(key, fallbackValue) {
        try {
            var value = localStorage.getItem(key);
            return value === null ? fallbackValue : value;
        } catch (e) {
            return fallbackValue;
        }
    }

    function storageSet(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (e) {}
    }

    function storageRemove(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {}
    }

    function getSeenNotificationIds() {
        try {
            var parsed = JSON.parse(storageGet(seenNotificationsKey, '[]'));
            return Array.isArray(parsed)
                ? parsed.map(normalizeNotificationId).filter(function (id) { return id !== ''; })
                : [];
        } catch (e) {
            return [];
        }
    }

    function setSeenNotificationIds(ids) {
        storageSet(seenNotificationsKey, JSON.stringify(ids.slice(-20)));
    }

    function normalizeNotificationId(id) {
        return id === null || typeof id === 'undefined' ? '' : String(id);
    }

    function hasSeenNotification(id) {
        var normalizedId = normalizeNotificationId(id);
        if (!normalizedId) return false;
        return getSeenNotificationIds().indexOf(normalizedId) !== -1;
    }

    function markNotificationSeen(id) {
        var normalizedId = normalizeNotificationId(id);
        if (!normalizedId || hasSeenNotification(normalizedId)) return;
        var seenIds = getSeenNotificationIds();
        seenIds.push(normalizedId);
        setSeenNotificationIds(seenIds);
    }

    function getPromptMutedUntil() {
        var rawValue = parseInt(storageGet(promptDismissKey, '0'), 10);
        return Number.isFinite(rawValue) ? rawValue : 0;
    }

    function mutePromptForDays(days) {
        storageSet(promptDismissKey, String(Date.now() + (days * 24 * 60 * 60 * 1000)));
    }

    function isPromptMuted() {
        return getPromptMutedUntil() > Date.now();
    }

    function supportsPushNotifications() {
        return 'Notification' in window
            && typeof Notification.requestPermission === 'function'
            && 'serviceWorker' in navigator
            && 'PushManager' in window;
    }

    function isUsableNotification(notification) {
        return !!notification
            && typeof notification === 'object'
            && normalizeNotificationId(notification.id) !== ''
            && typeof notification.title === 'string'
            && notification.title.trim() !== ''
            && typeof notification.body === 'string'
            && typeof notification.url === 'string';
    }

    function safeNotificationUrl(value) {
        var fallbackUrl = (window.mazaq_ajax && window.mazaq_ajax.home_url) || '/';
        if (!value || typeof window.URL !== 'function') return fallbackUrl;

        try {
            var parsedUrl = new window.URL(value, window.location.origin);
            if (parsedUrl.protocol !== 'http:' && parsedUrl.protocol !== 'https:') return fallbackUrl;
            if (parsedUrl.origin !== window.location.origin) return fallbackUrl;
            if (parsedUrl.username || parsedUrl.password) return fallbackUrl;
            return parsedUrl.href;
        } catch (e) {
            return fallbackUrl;
        }
    }

    function releaseFocusWithin(element) {
        if (!element || !element.contains(document.activeElement)) return;
        if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }
    }

    function shouldOfferPrompt() {
        if (!notificationBootstrap || !notificationBootstrap.promptEligible) return false;
        if (!supportsPushNotifications()) return false;
        if (Notification.permission === 'denied') return false;
        if (isPromptMuted() || isSubscribed) return false;
        return true;
    }

    function showPromptStatus(message, isError) {
        if (!notificationPromptStatus) return;
        notificationPromptStatus.textContent = message || '';
        notificationPromptStatus.classList.remove('hidden', 'is-error', 'is-success');
        if (isError) {
            notificationPromptStatus.classList.add('is-error');
        } else {
            notificationPromptStatus.classList.add('is-success');
        }
    }

    function clearPromptStatus() {
        if (!notificationPromptStatus) return;
        notificationPromptStatus.classList.add('hidden');
        notificationPromptStatus.classList.remove('is-error', 'is-success');
        notificationPromptStatus.textContent = '';
    }

    function hidePrompt() {
        if (!notificationPrompt) return;
        releaseFocusWithin(notificationPrompt);
        notificationPrompt.classList.add('hidden');
        notificationPrompt.setAttribute('aria-busy', 'false');
    }

    function showPrompt() {
        if (promptShown || !shouldOfferPrompt()) return;
        promptShown = true;
        clearPromptStatus();
        if (notificationPrompt) notificationPrompt.classList.remove('hidden');
    }

    function hideToast() {
        if (!notificationToast) return;
        releaseFocusWithin(notificationToast);
        notificationToast.classList.add('hidden');
    }

    function nextUnseenNotification() {
        for (var index = 0; index < fallbackQueue.length; index += 1) {
            var item = fallbackQueue[index];
            if (isUsableNotification(item) && !hasSeenNotification(item.id)) return item;
        }
        return null;
    }

    function showNextFallbackToast() {
        if (isSubscribed) { hideToast(); return; }
        var notification = nextUnseenNotification();
        if (!notification) { hideToast(); return; }
        var notificationType = notification.type === 'new_post' ? 'new_post' : 'daily_random';
        if (notificationToast) notificationToast.dataset.notificationId = normalizeNotificationId(notification.id);
        if (notificationToast) notificationToast.dataset.notificationType = notificationType;
        if (notificationToastKicker) notificationToastKicker.textContent = notificationType === 'new_post' ? 'مقال جديد' : 'اقتراح اليوم';
        if (notificationToastTitle) notificationToastTitle.textContent = notification.title;
        if (notificationToastBody) notificationToastBody.textContent = notification.body;
        if (notificationToastLink) notificationToastLink.setAttribute('href', safeNotificationUrl(notification.url));
        if (notificationToast) notificationToast.classList.remove('hidden');
    }

    function encodeApplicationServerKey(base64String) {
        var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        var normalized = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var raw = window.atob(normalized);
        var output = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i += 1) output[i] = raw.charCodeAt(i);
        return output;
    }

    function ensureServiceWorker() {
        if (!supportsPushNotifications() || !window.mazaq_ajax.notifications_service_worker_url) return Promise.resolve(null);
        if (!serviceWorkerPromise) {
            serviceWorkerPromise = navigator.serviceWorker.register(
                window.mazaq_ajax.notifications_service_worker_url,
                { scope: '/' }
            ).catch(function () { serviceWorkerPromise = null; return null; });
        }
        return serviceWorkerPromise;
    }

    function fetchJson(url, options) {
        var requestOptions = Object.assign({}, options || {});
        var controller = typeof window.AbortController === 'function' ? new window.AbortController() : null;
        var timeoutId = null;

        if (controller) {
            requestOptions.signal = controller.signal;
            timeoutId = window.setTimeout(function () { controller.abort(); }, fetchTimeoutMs);
        }

        return fetch(url, requestOptions).then(function (response) {
            return response.text().then(function (body) {
                var data = {};
                if (body) {
                    try { data = JSON.parse(body); } catch (e) { data = {}; }
                }
                return { ok: response.ok, status: response.status, data: data };
            });
        }).finally(function () {
            if (timeoutId) window.clearTimeout(timeoutId);
        });
    }

    function setSubscribedState(subscribed) {
        isSubscribed = !!subscribed;
        if (isSubscribed) { storageSet(subscribedKey, '1'); hidePrompt(); hideToast(); return; }
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
        if (subscriptionInFlight) return;
        if (!notificationBootstrap || !notificationBootstrap.publicVapidKey) {
            showPromptStatus('التنبيهات غير جاهزة حالياً. أضف مفاتيح VAPID من لوحة التحكم.', true);
            return;
        }
        if (!supportsPushNotifications()) {
            showPromptStatus('متصفحك لا يدعم تنبيهات الويب.', true);
            return;
        }
        subscriptionInFlight = true;
        if (notificationPromptSubscribe) {
            notificationPromptSubscribe.disabled = true;
            notificationPromptSubscribe.classList.remove('is-complete');
            notificationPromptSubscribe.textContent = 'جارٍ التفعيل...';
        }
        if (notificationPrompt) notificationPrompt.setAttribute('aria-busy', 'true');
        clearPromptStatus();
        ensureServiceWorker().then(function (registration) {
            if (!registration || !registration.pushManager) throw new Error('service-worker');
            if (Notification.permission === 'denied') throw new Error('permission-denied');
            var permissionPromise = Notification.permission === 'granted'
                ? Promise.resolve('granted')
                : Notification.requestPermission();
            return permissionPromise.then(function (permission) {
                if (permission !== 'granted') throw new Error(permission === 'denied' ? 'permission-denied' : 'permission-default');
                return registration.pushManager.getSubscription().then(function (existingSubscription) {
                    if (existingSubscription) return existingSubscription;
                    return registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: encodeApplicationServerKey(notificationBootstrap.publicVapidKey)
                    });
                });
            });
        }).then(function (subscription) {
            return fetchJson(window.mazaq_ajax.notifications_subscription_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(subscription.toJSON())
            });
        }).then(function (response) {
            if (!response.ok || !response.data || !response.data.success) throw new Error('subscription-save');
            setSubscribedState(true);
            if (notificationPrompt) notificationPrompt.classList.remove('hidden');
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
            subscriptionInFlight = false;
            if (notificationPrompt) notificationPrompt.setAttribute('aria-busy', 'false');
            if (notificationPromptSubscribe) {
                if (isSubscribed) {
                    notificationPromptSubscribe.disabled = true;
                    notificationPromptSubscribe.classList.add('is-complete');
                    notificationPromptSubscribe.textContent = 'تم التفعيل';
                } else {
                    notificationPromptSubscribe.disabled = false;
                    notificationPromptSubscribe.classList.remove('is-complete');
                    notificationPromptSubscribe.textContent = 'اشترك الآن';
                }
            }
        });
    }

    function dismissPromptForLater() {
        mutePromptForDays(7);
        stopPromptEngagementWatchers();
        hidePrompt();
    }

    function stopPromptEngagementWatchers() {
        if (promptTimerId) window.clearTimeout(promptTimerId);
        promptTimerId = null;
        window.removeEventListener('scroll', handleScrollEngagement);
    }

    function revealPromptAfterEngagement() {
        if (engagementTriggered) return;
        engagementTriggered = true;
        stopPromptEngagementWatchers();
        showPrompt();
    }

    function handleScrollEngagement() {
        var doc = document.documentElement;
        var maxScroll = doc.scrollHeight - window.innerHeight;
        if (maxScroll <= 0) return;
        if ((window.scrollY / maxScroll) >= 0.5) revealPromptAfterEngagement();
    }

    function initPromptEngagementWatchers() {
        if (!shouldOfferPrompt()) return;
        promptTimerId = window.setTimeout(revealPromptAfterEngagement, promptDelayMs);
        window.addEventListener('scroll', handleScrollEngagement, { passive: true });
    }

    function hydrateNotificationBootstrap() {
        if (!window.mazaq_ajax.notifications_bootstrap_url) return;
        fetchJson(window.mazaq_ajax.notifications_bootstrap_url, { credentials: 'same-origin' }).then(function (response) {
            if (!response.ok || !response.data || typeof response.data !== 'object' || Array.isArray(response.data)) return;
            notificationBootstrap = response.data;
            fallbackQueue = Array.isArray(notificationBootstrap.fallbackNotifications)
                ? notificationBootstrap.fallbackNotifications : [];
            return syncExistingSubscriptionState().then(function () { initPromptEngagementWatchers(); });
        }).catch(function () {});
    }

    if (notificationPromptSubscribe) {
        notificationPromptSubscribe.addEventListener('click', function (e) { e.preventDefault(); subscribeToNotifications(); });
    }
    if (notificationPromptClose) {
        notificationPromptClose.addEventListener('click', function (e) { e.preventDefault(); dismissPromptForLater(); });
    }
    if (notificationPromptDismiss) {
        notificationPromptDismiss.addEventListener('click', function (e) { e.preventDefault(); dismissPromptForLater(); });
    }
    if (notificationToastClose) {
        notificationToastClose.addEventListener('click', function (e) {
            e.preventDefault();
            markNotificationSeen(notificationToast ? notificationToast.dataset.notificationId : '');
            showNextFallbackToast();
        });
    }
    if (notificationToastDismiss) {
        notificationToastDismiss.addEventListener('click', function (e) {
            e.preventDefault();
            markNotificationSeen(notificationToast ? notificationToast.dataset.notificationId : '');
            showNextFallbackToast();
        });
    }
    if (notificationToastLink) {
        notificationToastLink.addEventListener('click', function () {
            markNotificationSeen(notificationToast ? notificationToast.dataset.notificationId : '');
        });
    }

    hydrateNotificationBootstrap();
})();
