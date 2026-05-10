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
            bar.style.transition = 'transform ' + intervalMs + 'ms linear';
            bar.style.transform = 'scaleX(1)';
        });
    }

    function syncProgress() {
        railItems.forEach(function (item, index) {
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
        slides.forEach(function (slide, index) {
            const isActive = index === currentIndex;
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });
        dots.forEach(function (dot, index) {
            const isActive = index === currentIndex;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-current', isActive ? 'true' : 'false');
        });
        railItems.forEach(function (item, index) {
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
        timer = window.setInterval(function () {
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
        const config = Object.assign({ restartTimer: true }, options || {});
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
        rafId = window.requestAnimationFrame(function () {
            const rect = root.getBoundingClientRect();
            const progress = Math.min(Math.max((window.innerHeight - rect.top) / (window.innerHeight + rect.height), 0), 1);
            root.style.setProperty('--hero-scroll', progress.toFixed(4));
            rafId = null;
        });
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(dot.dataset.index, 10));
        });
    });

    railItems.forEach(function (item) {
        item.addEventListener('click', function () {
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
    root.addEventListener('focusout', function () {
        window.setTimeout(function () {
            if (!root.contains(document.activeElement)) {
                onPointerLeave();
            }
        }, 0);
    });

    root.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            prev();
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            next();
        }
    });

    root.addEventListener('touchstart', function (event) {
        touchStartX = event.changedTouches[0].clientX;
    }, { passive: true });

    root.addEventListener('touchend', function (event) {
        const delta = event.changedTouches[0].clientX - touchStartX;
        if (delta > 50) {
            prev();
        } else if (delta < -50) {
            next();
        }
    }, { passive: true });

    window.addEventListener('scroll', handleScroll, { passive: true });

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        if (resizeTimer) { return; }
        resizeTimer = window.setTimeout(function () {
            resizeTimer = null;
            handleScroll();
        }, 150);
    }, { passive: true });

    const handleReducedMotionChange = function () {
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

    window.requestAnimationFrame(function () {
        isReady = true;
        root.classList.add('is-ready');
        syncProgress();
    });

    if (!reduceMotionQuery.matches) {
        startTimer();
    }
}());
