document.addEventListener('DOMContentLoaded', function () {
    const randomFilmResult = document.getElementById('random-film-result');
    const randomFilmOpen = document.getElementById('random-film-open');
    const randomFilmNext = document.getElementById('random-film-next');
    const randomFilmRetry = document.getElementById('random-film-retry');
    const randomFilmLoading = document.getElementById('random-film-loading');
    const randomFilmError = document.getElementById('random-film-error');
    const randomFilmErrorText = document.getElementById('random-film-error-text');
    const randomFilmContent = document.getElementById('random-film-content');
    const randomFilmImage = document.getElementById('random-film-image');
    const randomFilmImageFallback = document.getElementById('random-film-image-fallback');
    const randomFilmCategory = document.getElementById('random-film-category');
    const randomFilmCategorySelect = document.getElementById('random-film-category-select');
    const randomFilmTitle = document.getElementById('random-film-title');
    const randomFilmExcerpt = document.getElementById('random-film-excerpt');
    const randomFilmReadLink = document.getElementById('random-film-read-link');

    if (randomFilmResult && randomFilmOpen) {
        const shownFilmIds = [];
        let isRandomFilmLoading = false;
        let isResultOpen = false;

        function showRandomFilmImageFallback(title) {
            if (!randomFilmImageFallback) return;
            const fallbackText = randomFilmImageFallback.querySelector('span');
            if (fallbackText) fallbackText.textContent = title ? title.trim().charAt(0) : 'م';
            randomFilmImageFallback.classList.remove('hidden');
        }

        function hideRandomFilmImageFallback() {
            if (randomFilmImageFallback) {
                randomFilmImageFallback.classList.add('hidden');
            }
        }

        if (randomFilmImage) {
            randomFilmImage.addEventListener('error', function () {
                randomFilmImage.style.display = 'none';
                showRandomFilmImageFallback(randomFilmTitle ? randomFilmTitle.textContent : '');
            });
        }

        function setRandomFilmLoading(isLoading) {
            isRandomFilmLoading = isLoading;
            if (isLoading) {
                randomFilmLoading.classList.remove('hidden');
                randomFilmLoading.classList.add('flex');
                randomFilmError.classList.add('hidden');
                return;
            }
            randomFilmLoading.classList.add('hidden');
            randomFilmLoading.classList.remove('flex');
        }

        function renderRandomFilm(film) {
            randomFilmTitle.textContent = film.title || '';
            randomFilmExcerpt.textContent = film.excerpt || '';
            if (film.category) {
                randomFilmCategory.textContent = film.category;
                randomFilmCategory.classList.remove('hidden');
            } else {
                randomFilmCategory.textContent = '';
                randomFilmCategory.classList.add('hidden');
            }
            randomFilmReadLink.setAttribute('href', film.permalink || '#');
            const imageSrc = film.image || '';
            const imageAlt = film.title ? 'بوستر ' + film.title : 'بوستر الفيلم';
            randomFilmImage.setAttribute('alt', imageAlt);
            if (imageSrc) {
                hideRandomFilmImageFallback();
                randomFilmImage.setAttribute('src', imageSrc);
                randomFilmImage.style.display = '';
            } else {
                randomFilmImage.removeAttribute('src');
                randomFilmImage.style.display = 'none';
                showRandomFilmImageFallback(film.title || '');
            }
            randomFilmError.classList.add('hidden');
            randomFilmContent.classList.remove('hidden');
            const filmId = parseInt(film.id, 10);
            if (Number.isFinite(filmId) && filmId > 0 && shownFilmIds.indexOf(filmId) === -1) {
                shownFilmIds.push(filmId);
                if (shownFilmIds.length > 15) {
                    shownFilmIds.shift();
                }
            }
        }

        function showRandomFilmError(message) {
            randomFilmErrorText.textContent = message || 'تعذر تحميل الاقتراح حالياً.';
            randomFilmContent.classList.add('hidden');
            randomFilmError.classList.remove('hidden');
        }

        function postRandomFilm() {
            const formData = new FormData();
            const selectedCategoryId = randomFilmCategorySelect
                ? parseInt(randomFilmCategorySelect.value, 10) || 0
                : 0;
            formData.append('action', window.mazaq_ajax.random_film_action || 'mazaq_get_random_film');
            formData.append('nonce', window.mazaq_ajax.random_film_nonce);
            formData.append('category_id', selectedCategoryId);
            shownFilmIds.forEach(function (id) {
                formData.append('exclude_ids[]', id);
            });
            return fetch(window.mazaq_ajax.ajax_url, {
                method: 'POST',
                body: formData
            }).then(function (response) {
                return response.json();
            });
        }

        function requestRandomFilm() {
            if (isRandomFilmLoading) {
                return;
            }
            if (!isResultOpen) {
                randomFilmResult.classList.add('is-open');
                isResultOpen = true;
            }
            randomFilmContent.classList.add('hidden');
            setRandomFilmLoading(true);
            postRandomFilm().then(function (response) {
                if (response.success && response.data && response.data.film) {
                    renderRandomFilm(response.data.film);
                    return;
                }
                const message = response && response.data && response.data.message
                    ? response.data.message
                    : 'تعذر تحميل الاقتراح حالياً.';
                showRandomFilmError(message);
            }).catch(function () {
                showRandomFilmError('حدث خطأ في الاتصال. حاول مرة أخرى.');
            }).finally(function () {
                setRandomFilmLoading(false);
            });
        }

        randomFilmOpen.addEventListener('click', function (e) {
            e.preventDefault();
            requestRandomFilm();
        });
        randomFilmNext.addEventListener('click', function (e) {
            e.preventDefault();
            requestRandomFilm();
        });
        randomFilmRetry.addEventListener('click', function (e) {
            e.preventDefault();
            requestRandomFilm();
        });
        if (randomFilmCategorySelect) {
            randomFilmCategorySelect.addEventListener('change', function () {
                shownFilmIds.length = 0;
                if (isResultOpen) {
                    requestRandomFilm();
                }
            });
        }
    }

    const container = document.getElementById('infinite-scroll-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const loadMoreButton = document.getElementById('load-more-posts');
    const infiniteScrollSentinel = document.getElementById('infinite-scroll-sentinel');

    if (container && loadingIndicator) {
        let currentPage = 2;
        let isLoading = false;
        let hasMore = true;
        let loadError = false;
        let endOfContentRendered = false;
        let infiniteScrollObserver = null;

        function setLoadingState(isActive) {
            loadingIndicator.classList.toggle('hidden', !isActive);
            container.setAttribute('aria-busy', isActive ? 'true' : 'false');
            if (loadMoreButton) {
                loadMoreButton.disabled = isActive;
                loadMoreButton.textContent = isActive ? 'جاري التحميل...' : 'تحميل المزيد';
            }
        }

        function showLoadMoreButton() {
            if (loadMoreButton && hasMore && !loadError) {
                loadMoreButton.classList.remove('hidden');
            }
        }

        function hideLoadMoreButton() {
            if (loadMoreButton) {
                loadMoreButton.classList.add('hidden');
            }
        }

        function renderLoadError() {
            if (loadError) {
                return;
            }
            loadError = true;
            setLoadingState(false);
            hideLoadMoreButton();
            const errorEl = document.createElement('div');
            errorEl.className = 'infinite-scroll-message';
            errorEl.setAttribute('role', 'alert');
            const msg = document.createElement('p');
            msg.className = 'infinite-scroll-message__text';
            msg.textContent = 'تعذر تحميل المزيد من المقالات.';
            const retryBtn = document.createElement('button');
            retryBtn.type = 'button';
            retryBtn.className = 'infinite-scroll-message__button';
            retryBtn.textContent = 'حاول مرة أخرى';
            retryBtn.addEventListener('click', function () {
                errorEl.remove();
                loadError = false;
                showLoadMoreButton();
                loadMorePosts();
            });
            errorEl.appendChild(msg);
            errorEl.appendChild(retryBtn);
            container.insertAdjacentElement('afterend', errorEl);
        }

        function renderEndOfContent() {
            if (endOfContentRendered) {
                return;
            }
            endOfContentRendered = true;
            if (infiniteScrollObserver) {
                infiniteScrollObserver.disconnect();
            }
            setLoadingState(false);
            hideLoadMoreButton();
            if (infiniteScrollSentinel) {
                infiniteScrollSentinel.hidden = true;
            }
            const finale = document.createElement('div');
            finale.className = 'delight-finale';
            finale.innerHTML = '<span class="delight-finale__line" aria-hidden="true"></span>' +
                '<p class="delight-finale__title">نهاية</p>' +
                '<p class="delight-finale__subtitle">The End</p>' +
                '<span class="delight-finale__line delight-finale__line--bottom" aria-hidden="true"></span>';
            container.insertAdjacentElement('afterend', finale);
        }

        function postLoadMore() {
            const formData = new FormData();
            formData.append('action', 'load_more_posts');
            formData.append('nonce', window.mazaq_ajax.nonce);
            formData.append('page', currentPage);
            return fetch(window.mazaq_ajax.ajax_url, {
                method: 'POST',
                body: formData
            }).then(function (response) {
                return response.json();
            });
        }

        function loadMorePosts() {
            if (isLoading || !hasMore || loadError) {
                return;
            }
            isLoading = true;
            setLoadingState(true);
            postLoadMore().then(function (response) {
                if (response.success && response.data.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = response.data.html;
                    while (temp.firstChild) {
                        container.appendChild(temp.firstChild);
                    }
                    hasMore = !!response.data.has_more;
                    currentPage += 1;
                } else {
                    hasMore = false;
                }
                if (!hasMore) {
                    renderEndOfContent();
                }
            }).catch(function () {
                renderLoadError();
            }).finally(function () {
                isLoading = false;
                setLoadingState(false);
                if (infiniteScrollObserver && hasMore && !loadError) {
                    hideLoadMoreButton();
                }
            });
        }

        if (loadMoreButton) {
            loadMoreButton.addEventListener('click', loadMorePosts);
        }

        if ('IntersectionObserver' in window && infiniteScrollSentinel) {
            hideLoadMoreButton();
            infiniteScrollObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || isLoading || !hasMore || loadError) {
                        return;
                    }
                    loadMorePosts();
                });
            }, {
                rootMargin: '0px 0px 300px 0px',
                threshold: 0
            });

            infiniteScrollObserver.observe(infiniteScrollSentinel);
        } else {
            showLoadMoreButton();
        }
    }

    // Cinematic Hero Spotlight Tracking (Overdrive)
    const heroLink = document.querySelector('.feature-hero__link');
    const heroBeam = document.querySelector('.feature-hero__beam');

    if (heroLink && heroBeam && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        let rect = heroLink.getBoundingClientRect();
        
        let targetX = 64; // Default position %
        let targetY = 42;
        let currentX = 64;
        let currentY = 42;
        let isHovered = false;
        let rafId = null;

        function updateBounds() {
            rect = heroLink.getBoundingClientRect();
        }
        window.addEventListener('resize', updateBounds, { passive: true });
        window.addEventListener('scroll', updateBounds, { passive: true });

        heroLink.addEventListener('mouseenter', function() {
            isHovered = true;
            updateBounds();
            if (!rafId) {
                rafId = requestAnimationFrame(animateBeam);
            }
        });

        heroLink.addEventListener('mousemove', function(e) {
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            targetX = (x / rect.width) * 100;
            targetY = (y / rect.height) * 100;
        });

        heroLink.addEventListener('mouseleave', function() {
            isHovered = false;
            targetX = 64;
            targetY = 42;
        });

        function animateBeam() {
            const ease = 0.08;
            currentX += (targetX - currentX) * ease;
            currentY += (targetY - currentY) * ease;

            heroBeam.style.setProperty('--beam-x', `${currentX.toFixed(2)}%`);
            heroBeam.style.setProperty('--beam-y', `${currentY.toFixed(2)}%`);

            const diffX = Math.abs(targetX - currentX);
            const diffY = Math.abs(targetY - currentY);

            if (!isHovered && diffX < 0.1 && diffY < 0.1) {
                currentX = 64;
                currentY = 42;
                heroBeam.style.setProperty('--beam-x', '64%');
                heroBeam.style.setProperty('--beam-y', '42%');
                rafId = null;
            } else {
                rafId = requestAnimationFrame(animateBeam);
            }
        }
    }
});
