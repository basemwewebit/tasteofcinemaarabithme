# Research and Decisions: Hero Carousel for Multiple Sticky Posts

## 1. Retrieving All Hero Post IDs (Multi-Sticky Support)

**Unknown:** The existing helper `mazaq_get_hero_post_id()` returns a single `int`. When multiple sticky posts exist, we need all of them for the carousel. How should we expose that list while keeping the single-post path unchanged?

- **Decision**: Add a new companion function `mazaq_get_hero_post_ids(): array` in `inc/helpers.php`. It returns an `int[]` of all eligible hero IDs following the same priority chain (ACF option → sticky posts → latest post). The existing `mazaq_get_hero_post_id()` is preserved untouched and internally becomes a one-liner that calls `mazaq_get_hero_post_ids()[0] ?? 0`.
- **Rationale**: This is the minimal-footprint approach. All callers of `mazaq_get_hero_post_id()` (`front-page.php`, `inc/infinite-scroll.php`) continue to work without modification. The new function is the only entry-point for the carousel template.
- **Alternatives considered**:
  - Passing the count to `hero.php` via a global variable → rejected (anti-pattern, tight coupling on include order).
  - Changing `mazaq_get_hero_post_id()` to return an array → rejected (breaks all three call-sites that cast to `int`).

---

## 2. Carousel Architecture: Vanilla JS vs. Library

**Unknown:** The theme's single JS bundle (`assets/js/app.js`) is hand-crafted jQuery/vanilla. Should the carousel use a third-party library (e.g. Swiper.js, Splide) or a lightweight custom implementation?

- **Decision**: Implement a self-contained vanilla JS carousel class appended to the existing `assets/js/app.js` bundle. No external library dependency.
- **Rationale**:
  - `app.js` is already built from a single file with no bundler (scripts are enqueued directly via `wp_enqueue_script`). Introducing a CDN/NPM library would require either a build step or an additional `wp_enqueue_script` call for a 40–80 kB library, adding unnecessary weight for a feature used only on the front page.
  - A hero carousel with fade/slide, dots, swipe, and auto-advance needs ~60–80 lines of vanilla JS — a size where a custom implementation is *simpler* than adding a dependency.
  - The theme already uses `IntersectionObserver`, touch events are natively available, and jQuery is available as a fallback for IE-polyfill concerns.
- **Alternatives considered**:
  - Swiper.js (min ~40 kB gzipped) → rejected (overkill for a single homepage component).
  - Splide (~7 kB gzipped) → rejected (still a new build dependency; not justified for this scope).

---

## 3. Slide Transition Style: Fade vs. Slide

**Unknown:** Should the carousel transition between slides using a cross-fade or a horizontal slide (CSS `transform: translateX`)?

- **Decision**: **Cross-fade** (`opacity` transition, absolute positioning of slides).
- **Rationale**:
  - The hero covers `60vh–70vh`. A horizontal slide looks natural on landing pages with sequential editorial content; however, the hero image text is right-aligned (RTL layout). A horizontal slide towards the left visually conflicts with the RTL reading direction (Arabic content), creating a jarring directional mismatch.
  - A cross-fade is direction-agnostic, cinematic, and consistent with the high-contrast dark overlay already applied to hero images.
  - Fade also avoids layout width overflow during transitions (no `overflow: hidden` hacks needed on the hero wrapper).
- **Alternatives considered**:
  - RTL-aware slide (`translateX` reversed) → possible but adds complexity in calculating direction for manual dot-click jumps across non-adjacent slides.
  - CSS `scroll-snap` + `overflow-x: scroll` → rejected (poor controllability for auto-advance and dot navigation).

---

## 4. Touch/Swipe Implementation

**Unknown:** How to reliably detect swipe gestures without a gesture library, given jQuery is already available?

- **Decision**: Track `touchstart` / `touchend` native events on the hero container. Calculate `deltaX = touchEnd.clientX - touchStart.clientX`. If `|deltaX| > 50px`, advance in the swipe direction.
- **Rationale**: A 50 px threshold is the industry-standard minimum for intentional horizontal swipes (vs. vertical scroll taps). Since slides use cross-fade (no positional movement during swipe), there is no need for a "drag-follow" effect, keeping the implementation minimal.
- **Alternatives considered**:
  - `HammerJS` gestures → rejected (same reasoning as above, library overkill).
  - `pointer-events` API → viable but less universally supported on older iOS/Android than `touch*` events.

---

## 5. Auto-Advance Pause Behaviour

**Unknown:** The spec requires pausing auto-advance on hover (desktop) and after manual interaction. What constitutes "manual interaction" and when does auto-advance resume?

- **Decision**: Auto-advance is paused on `mouseenter` / `touchstart` and resumed on `mouseleave`. After a manual dot-click or swipe, auto-advance is reset (timer restarted from 0) but not permanently paused — it resumes after one full 6-second interval with no further interaction.
- **Rationale**: This matches the UX pattern used by major news sites (NYT, Guardian). Permanently pausing after any interaction would reduce the value of the carousel for passive readers who scroll back up.
- **Alternatives considered**:
  - Permanent pause after any manual interaction → rejected (degrades editorial value; reader may not realise the carousel won't advance).

---

## 6. Enqueueing the Carousel Script

**Unknown:** The carousel JS will live in `app.js`, which is enqueued globally. Should it be conditionally loaded only on the front page?

- **Decision**: Keep it in `app.js` (global load). Add a guard in the JS: `if (!document.querySelector('.hero-carousel')) return;` at the top of the carousel init function, so it is a no-op on all other pages.
- **Rationale**: The overhead of the carousel code block is <100 lines (~3 kB minified). The conditional enqueue approach (using `is_front_page()` in `enqueue.php`) would add maintenance complexity for negligible savings.
- **Alternatives considered**:
  - Conditional `wp_enqueue_script` on `is_front_page()` → rejected (3 kB savings not worth the added branching in `enqueue.php`).
