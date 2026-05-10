(function initAdblock() {
    if (!document.querySelector('[data-ad-container="true"]')) return;

    var adBlockConfig = (window.mazaq_ajax && window.mazaq_ajax.adblock) || {};
    var adBlockStateSessionKey = adBlockConfig.session_storage_key || 'mazaq_adblock_state';
    var adBlockPromptStorageKey = adBlockConfig.mute_storage_key || 'mazaq_adblock_prompt_muted_until';
    var adBlockPromptShownSessionKey = 'mazaq_adblock_prompt_session_shown';
    var adContainerSelector = '[data-ad-container="true"][data-expects-network-ad="1"]';
    var fallbackCopy = {
        title: adBlockConfig.fallback_title || 'ادعم استمرار المحتوى',
        body: adBlockConfig.fallback_body || 'هذه المساحة غير متاحة حاليًا. يمكنك دعم الموقع بالسماح بالإعلانات أو التواصل معنا.',
        cta: adBlockConfig.fallback_cta || 'اعرف كيف تدعمنا',
        supportUrl: adBlockConfig.support_url || (window.mazaq_ajax && window.mazaq_ajax.home_url ? window.mazaq_ajax.home_url : '/')
    };
    var promptCopy = {
        title: adBlockConfig.prompt_title || 'يسعدنا دعمك للموقع',
        body: adBlockConfig.prompt_body || 'الإعلانات الخفيفة تساعدنا في استمرار المحتوى مجانًا. يمكنك متابعة التصفح أو التواصل معنا للدعم.',
        primaryCta: adBlockConfig.prompt_primary_cta || 'اعرف كيف تدعمنا',
        secondaryCta: adBlockConfig.prompt_secondary_cta || 'متابعة التصفح',
        supportUrl: adBlockConfig.support_url || (window.mazaq_ajax && window.mazaq_ajax.home_url ? window.mazaq_ajax.home_url : '/')
    };

    function pushMonetizationEvent(eventName, payload) {
        var dataLayer = window.dataLayer = window.dataLayer || [];
        dataLayer.push(Object.assign({ event: eventName, source: 'adblock_module' }, payload || {}));
    }

    function getLocalNumber(key) {
        try { return parseInt(localStorage.getItem(key) || '0', 10); } catch (e) { return 0; }
    }

    function setLocalNumber(key, value) {
        try { localStorage.setItem(key, String(value)); } catch (e) {}
    }

    function getSessionJson(key) {
        try { var raw = sessionStorage.getItem(key); return raw ? JSON.parse(raw) : null; } catch (e) { return null; }
    }

    function setSessionJson(key, value) {
        try { sessionStorage.setItem(key, JSON.stringify(value)); } catch (e) {}
    }

    function isPromptMuted() {
        var mutedUntil = getLocalNumber(adBlockPromptStorageKey);
        return Number.isFinite(mutedUntil) && mutedUntil > Date.now();
    }

    function mutePromptForDays(days) {
        setLocalNumber(adBlockPromptStorageKey, Date.now() + (days * 24 * 60 * 60 * 1000));
    }

    function detectByBaitElement() {
        return new Promise(function (resolve) {
            var bait = document.createElement('div');
            bait.className = 'adsbox ad-banner ad-unit ad-zone';
            bait.setAttribute('aria-hidden', 'true');
            bait.style.cssText = 'position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;pointer-events:none';
            document.body.appendChild(bait);
            window.setTimeout(function () {
                var computed = window.getComputedStyle(bait);
                var blocked = bait.offsetWidth === 0 || bait.offsetHeight === 0 || computed.display === 'none' || computed.visibility === 'hidden';
                bait.remove();
                resolve(blocked);
            }, 140);
        });
    }

    function hasLoadedAnyAdFrame() {
        var adContainers = document.querySelectorAll(adContainerSelector);
        if (!adContainers.length) return false;
        return Array.prototype.some.call(adContainers, function (container) {
            var adNode = container.querySelector('ins.adsbygoogle, ins[data-ad-ins="true"]');
            if (!adNode) return false;
            if (adNode.querySelector('iframe')) return true;
            return adNode.getAttribute('data-adsbygoogle-status') === 'done';
        });
    }

    function isContainerAdFilled(container) {
        var adNode = container.querySelector('ins.adsbygoogle, ins[data-ad-ins="true"]');
        if (!adNode) return false;
        if (adNode.querySelector('iframe')) return true;
        return adNode.getAttribute('data-adsbygoogle-status') === 'done';
    }

    function detectByRuntimeSignals() {
        return new Promise(function (resolve) {
            var expectedAdCount = document.querySelectorAll(adContainerSelector).length;
            if (!expectedAdCount) { resolve(false); return; }
            window.setTimeout(function () {
                var hasAdSenseScript = !!document.querySelector('script[src*="pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"]');
                var hasRuntime = typeof window.adsbygoogle !== 'undefined';
                var hasRenderedFrame = hasLoadedAnyAdFrame();
                resolve(Boolean(hasAdSenseScript && !hasRuntime && !hasRenderedFrame));
            }, 2600);
        });
    }

    function resolveAdBlockState() {
        var cached = getSessionJson(adBlockStateSessionKey);
        if (cached && typeof cached.blocked === 'boolean') return Promise.resolve(cached);
        return Promise.all([detectByBaitElement(), detectByRuntimeSignals()]).then(function (results) {
            var state = { blocked: Boolean(results[0] || results[1]), baitBlocked: Boolean(results[0]), runtimeBlocked: Boolean(results[1]), timestamp: Date.now() };
            setSessionJson(adBlockStateSessionKey, state);
            return state;
        });
    }

    function createFallbackNode(slotName) {
        var card = document.createElement('section');
        card.className = 'ad-fallback-card';
        var title = document.createElement('h4');
        title.className = 'ad-fallback-card__title';
        title.textContent = fallbackCopy.title;
        var body = document.createElement('p');
        body.className = 'ad-fallback-card__body';
        body.textContent = fallbackCopy.body;
        var link = document.createElement('a');
        link.className = 'ad-fallback-card__cta';
        link.href = fallbackCopy.supportUrl;
        link.textContent = fallbackCopy.cta;
        link.setAttribute('rel', 'noopener');
        link.addEventListener('click', function () { pushMonetizationEvent('ad_fallback_click', { slot_name: slotName || 'unknown' }); });
        card.appendChild(title);
        card.appendChild(body);
        card.appendChild(link);
        return card;
    }

    function applyAdFallbackToEmptySlots(forceFallback) {
        var adContainers = document.querySelectorAll(adContainerSelector);
        if (!adContainers.length) return;
        Array.prototype.forEach.call(adContainers, function (container) {
            if (container.getAttribute('data-ad-fallback-rendered') === '1') return;
            var slotName = container.getAttribute('data-slot-name') || 'unknown';
            var hasFilledAd = isContainerAdFilled(container);
            if (!forceFallback && hasFilledAd) return;
            var adNode = container.querySelector('ins.adsbygoogle, ins[data-ad-ins="true"]');
            var adStatus = adNode ? adNode.getAttribute('data-adsbygoogle-status') : '';
            var shouldFallback = forceFallback || !adNode || adStatus === 'unfilled' || !hasFilledAd;
            if (!shouldFallback) return;
            container.innerHTML = '';
            container.classList.add('ad-container--fallback');
            container.appendChild(createFallbackNode(slotName));
            container.setAttribute('data-ad-fallback-rendered', '1');
            pushMonetizationEvent('ad_fallback_rendered', { slot_name: slotName, forced: forceFallback ? 1 : 0 });
        });
    }

    var adblockFocusTrap = null;

    function renderAdBlockPrompt() {
        if (document.getElementById('toc-adblock-prompt') || isPromptMuted()) return;
        var prompt = document.createElement('aside');
        prompt.id = 'toc-adblock-prompt';
        prompt.className = 'adblock-prompt';
        prompt.setAttribute('role', 'dialog');
        prompt.setAttribute('aria-modal', 'true');
        prompt.setAttribute('aria-live', 'polite');
        prompt.setAttribute('aria-label', 'دعم الموقع');
        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'adblock-prompt-close';
        closeBtn.style.width = '2.75rem';
        closeBtn.style.height = '2.75rem';
        closeBtn.setAttribute('aria-label', 'إغلاق الرسالة');
        closeBtn.innerHTML = '&times;';
        var titleEl = document.createElement('h3');
        titleEl.className = 'adblock-prompt-title';
        titleEl.textContent = promptCopy.title;
        var bodyEl = document.createElement('p');
        bodyEl.className = 'adblock-prompt-body';
        bodyEl.textContent = promptCopy.body;
        var actionsEl = document.createElement('div');
        actionsEl.className = 'adblock-prompt-actions';
        var primaryLink = document.createElement('a');
        primaryLink.className = 'adblock-prompt-action';
        primaryLink.target = '_self';
        primaryLink.href = promptCopy.supportUrl;
        primaryLink.textContent = promptCopy.primaryCta;
        var secondaryBtn = document.createElement('button');
        secondaryBtn.type = 'button';
        secondaryBtn.className = 'adblock-prompt-secondary';
        secondaryBtn.textContent = promptCopy.secondaryCta;
        actionsEl.appendChild(primaryLink);
        actionsEl.appendChild(secondaryBtn);
        prompt.appendChild(closeBtn);
        prompt.appendChild(titleEl);
        prompt.appendChild(bodyEl);
        prompt.appendChild(actionsEl);
        function removePrompt() {
            prompt.remove();
            if (adblockFocusTrap) { adblockFocusTrap.deactivate(); adblockFocusTrap = null; }
        }
        closeBtn.addEventListener('click', function () { mutePromptForDays(3); removePrompt(); pushMonetizationEvent('ad_prompt_closed'); });
        secondaryBtn.addEventListener('click', function () { mutePromptForDays(3); removePrompt(); pushMonetizationEvent('ad_prompt_continue'); });
        primaryLink.addEventListener('click', function () { mutePromptForDays(14); removePrompt(); pushMonetizationEvent('ad_prompt_support_click'); });
        document.body.appendChild(prompt);
        adblockFocusTrap = window.FocusTrap(prompt, {
            initialFocus: closeBtn,
            onEscape: function () { mutePromptForDays(3); removePrompt(); pushMonetizationEvent('ad_prompt_escape'); }
        });
        adblockFocusTrap.activate();
    }

    resolveAdBlockState().then(function (state) {
        if (state.blocked) {
            applyAdFallbackToEmptySlots(true);
            var promptShownInSession = getSessionJson(adBlockPromptShownSessionKey);
            if (!promptShownInSession) {
                window.setTimeout(renderAdBlockPrompt, 900);
                setSessionJson(adBlockPromptShownSessionKey, true);
            }
            pushMonetizationEvent('adblock_detected', { bait_blocked: state.baitBlocked ? 1 : 0, runtime_blocked: state.runtimeBlocked ? 1 : 0 });
        } else {
            window.setTimeout(function () { applyAdFallbackToEmptySlots(false); }, 4500);
        }
        if ('MutationObserver' in window) {
            var dynamicContainer = document.getElementById('infinite-scroll-container');
            if (dynamicContainer) {
                var observer = new MutationObserver(function () { applyAdFallbackToEmptySlots(state.blocked); });
                observer.observe(dynamicContainer, { childList: true, subtree: true });
            }
        }
    });
})();
