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
    if (randomFilmImage) {
        randomFilmImage.addEventListener('error', function () {
            randomFilmImage.style.display = 'none';
        });
    }
    const randomFilmCategory = document.getElementById('random-film-category');
    const randomFilmCategorySelect = document.getElementById('random-film-category-select');
    const randomFilmTitle = document.getElementById('random-film-title');
    const randomFilmExcerpt = document.getElementById('random-film-excerpt');
    const randomFilmReadLink = document.getElementById('random-film-read-link');

    if (randomFilmResult && randomFilmOpen) {
        const shownFilmIds = [];
        let isRandomFilmLoading = false;
        let isResultOpen = false;

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
                randomFilmImage.setAttribute('src', imageSrc);
                randomFilmImage.style.display = '';
            } else {
                randomFilmImage.removeAttribute('src');
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

    if (container && loadingIndicator) {
        let currentPage = 2;
        let isLoading = false;
        let hasMore = true;
        let loadError = false;

        loadingIndicator.classList.remove('hidden');

        function renderLoadError() {
            if (loadError) {
                return;
            }
            loadError = true;
            loadingIndicator.classList.add('hidden');
            const errorEl = document.createElement('div');
            errorEl.className = 'text-center py-8 text-slate-600 dark:text-slate-300';
            errorEl.setAttribute('role', 'alert');
            const msg = document.createElement('p');
            msg.className = 'mb-2';
            msg.textContent = 'تعذر تحميل المزيد من المقالات.';
            const retryBtn = document.createElement('button');
            retryBtn.type = 'button';
            retryBtn.className = 'text-primary underline hover:no-underline font-medium';
            retryBtn.textContent = 'أعد تحميل الصفحة';
            retryBtn.addEventListener('click', function () { window.location.reload(); });
            errorEl.appendChild(msg);
            errorEl.appendChild(retryBtn);
            container.insertAdjacentElement('afterend', errorEl);
        }

        function renderEndOfContent() {
            infiniteScrollObserver.disconnect();
            loadingIndicator.classList.add('hidden');
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

        const infiniteScrollObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting || isLoading || !hasMore || loadError) {
                    return;
                }
                isLoading = true;
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
                });
            });
        }, {
            rootMargin: '0px 0px 300px 0px',
            threshold: 0
        });

        infiniteScrollObserver.observe(loadingIndicator);
    }
});
