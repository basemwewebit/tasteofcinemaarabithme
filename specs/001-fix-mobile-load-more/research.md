# Research & Decisions: Mobile Load More Issue

## 1. IntersectionObserver vs Scroll Event Math

**Decision**: Replace the current `$(window).on('scroll')` math in `assets/js/app.js` with an `IntersectionObserver` watching a dedicated sentinel element.

**Rationale**: 
The existing implementation calculates distance from the bottom using `$(window).scrollTop() + $(window).height() < $(document).height() - 300`.
On mobile browsers (iOS Safari, Mobile Chrome), dynamic navigation bars cause `$(window).height()` to fluctuate, and overscroll logic often misreports `$(document).height()`. This leads to the "Load More" action either never triggering or trapping the user in a state where it triggers incorrectly. 

`IntersectionObserver` is the modern standard for infinite scroll. It reliably fires when an element enters the viewport, regardless of mobile viewport quirks. We already use an observer for lazy loading images in `app.js`, confirming browser support and consistency within the theme's codebase.

**Alternatives considered**: 
- *Fixing the jQ math*: Adjusting the math to use `window.innerHeight` and `document.documentElement.scrollHeight`. Rejected because it still relies on continuous `scroll` events which degrade performance and battery life on mobile devices.
- *Manual Load More Button for Mobile*: Adding a "Load More" button that users must click on smaller viewports. Rejected because it changes the UX and the user expects continuous scrolling.

## 2. Scroll Jump / Freezing Fix

**Decision**: Ensure DOM insertion of new posts does not shift the page scroll position by maintaining the height of the container explicitly or relying on standard CSS flow without layout thrashing. Avoid hiding/showing the loading indicator using `display: none` (`hidden` utility) if it causes layout shifts.

**Rationale**:
The user noted "عندي السكرول" which often implies a scroll freeze or jump. When appending HTML via jQuery's `.append()`, if the loading indicator transitions from `display: none` to `display: flex`, it can cause the document height to jump, confusing the mobile browser's scroll anchoring. Using a permanent sentinel element and keeping the loading indicator in the DOM (but changing opacity or visibility) prevents layout thrashing.

**Alternatives considered**:
- CSS Scroll Anchoring: Explicitly enabling `overflow-anchor: auto`. Good addition, but relying solely on it is insufficient if we cause massive DOM shifts.

## Resolution of NEEDS CLARIFICATION
All technical ambiguities regarding why the Load More action fails on mobile have been resolved. The fix will be entirely frontend-focused located in `app.js` and `front-page.php`.
