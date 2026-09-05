/**
 * The Continuous Programme — front-page hero enhancement.
 *
 * The track is a native RTL scroll-snap list of four article spreads.
 * This layer adds the spine controls, the progress line, arrow-key
 * paging, slide announcements and the multiplane drift. Without it the
 * four articles remain plain links in a swipeable, keyboard-scrollable
 * track — enhancement, never a gate.
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-programme-hero]');
	if (!root) {
		return;
	}

	var track = root.querySelector('.programme-hero__track');
	var slides = track ? Array.prototype.slice.call(track.querySelectorAll('.programme-hero__slide')) : [];
	var spine = root.querySelector('.programme-hero__spine');
	var pages = spine ? Array.prototype.slice.call(spine.querySelectorAll('.programme-hero__page')) : [];
	var fill = root.querySelector('.programme-hero__progress-fill');
	var live = root.querySelector('.programme-hero__live');

	if (!track || slides.length === 0) {
		return;
	}

	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
	var stackedLayout = window.matchMedia('(max-width: 760px)');
	var current = 0;
	var scrollRaf = 0;

	function motionAllowed() {
		return !reduceMotion.matches;
	}

	function goTo(index) {
		var slide = slides[index];
		if (!slide) {
			return;
		}
		slide.scrollIntoView({
			behavior: motionAllowed() ? 'smooth' : 'auto',
			block: 'nearest',
			inline: 'start'
		});
	}

	function setActive(index) {
		current = index;

		pages.forEach(function (page, i) {
			var isActive = i === index;
			page.classList.toggle('is-active', isActive);
			page.setAttribute('aria-current', isActive ? 'true' : 'false');
		});

		if (fill) {
			fill.style.transform = 'scaleX(' + ((index + 1) / slides.length).toFixed(4) + ')';
		}

		if (live) {
			live.textContent = slides[index].getAttribute('aria-label') || '';
		}
	}

	function indexFromScroll() {
		var scrolled = Math.abs(track.scrollLeft);
		var best = 0;
		var bestDistance = Infinity;

		slides.forEach(function (slide, i) {
			// In RTL the slide's right edge distance from the scroll origin
			// (the inline-start edge) is offsetLeft + offsetWidth - clientWidth.
			var fromOrigin = Math.abs(slide.offsetLeft + slide.offsetWidth - track.clientWidth);
			var distance = Math.abs(fromOrigin - scrolled);
			if (distance < bestDistance) {
				bestDistance = distance;
				best = i;
			}
		});

		return best;
	}

	function updateDrift() {
		if (reduceMotion.matches || stackedLayout.matches) {
			return;
		}

		var trackRect = track.getBoundingClientRect();

		slides.forEach(function (slide) {
			var image = slide.querySelector('.programme-spread__image');
			if (!image) {
				return;
			}
			var rect = slide.getBoundingClientRect();
			var ratio = (rect.left + rect.width / 2 - trackRect.left - trackRect.width / 2) / trackRect.width;
			image.style.setProperty('--drift', (ratio * 26).toFixed(1));
		});
	}

	function onScroll() {
		if (scrollRaf) {
			return;
		}
		scrollRaf = window.requestAnimationFrame(function () {
			scrollRaf = 0;
			var index = indexFromScroll();
			if (index !== current) {
				setActive(index);
			}
			updateDrift();
		});
	}

	track.addEventListener('keydown', function (event) {
		var next = null;

		if (event.key === 'ArrowLeft') {
			next = Math.min(current + 1, slides.length - 1);
		} else if (event.key === 'ArrowRight') {
			next = Math.max(current - 1, 0);
		}

		if (next !== null) {
			event.preventDefault();
			goTo(next);
		}
	});

	pages.forEach(function (page, i) {
		page.addEventListener('click', function () {
			goTo(i);
		});
	});

	track.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', updateDrift, { passive: true });

	if (spine) {
		spine.hidden = false;
	}

	setActive(0);
	updateDrift();
})();
