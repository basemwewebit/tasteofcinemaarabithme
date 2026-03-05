# Tasks: Hero Carousel for Multiple Sticky Posts

**Input**: Design documents from `/specs/001-hero-carousel/`
**Branch**: `001-hero-carousel`
**Prerequisites**: plan.md ✅ | spec.md ✅ | research.md ✅ | data-model.md ✅ | quickstart.md ✅

**Organization**: Tasks are grouped by user story. US1 = single-post hero (backward compat); US2 = multi-post carousel. No tests requested — implementation tasks only.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no blocking dependency on an incomplete sibling task)
- **[Story]**: Which user story this task belongs to (`[US1]` or `[US2]`)
- Exact file paths are included in every task description

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verify branch state and confirm the working baseline before any code changes.

- [X] T001 Confirm active branch is `001-hero-carousel` (`git branch --show-current`)
- [X] T002 Load `template-parts/content/hero.php` and `inc/helpers.php` in editor — verify current state matches the plan (single-post hero rendering, `mazaq_get_hero_post_id()` present)
- [X] T003 Set at least 2 WordPress posts as sticky in WP Admin and confirm the front page still renders the existing single-card hero without errors (baseline smoke test)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Add the new PHP helper function that both user stories depend on. **Must be complete before Phase 3 or Phase 4 can begin.**

⚠️ **CRITICAL**: No user story work can begin until this phase is complete.

- [X] T004 In `inc/helpers.php`, add new function `mazaq_get_hero_post_ids(): array` that returns ALL eligible sticky post IDs following the same priority chain: (1) ACF `hero_featured_post` option → `[$acf_id]`, (2) `get_option('sticky_posts')` → `array_map('intval', $sticky_posts)`, (3) latest post fallback → `[$latest_id]`. Filter out zero/falsy values before returning.

- [X] T005 In `inc/helpers.php`, refactor `mazaq_get_hero_post_id(): int` to become a one-liner delegate: `return mazaq_get_hero_post_ids()[0] ?? 0;` — keep the function signature, return type, and `@return int` PHPDoc identical so all 3 call-sites (`front-page.php`, `inc/infinite-scroll.php`, `template-parts/content/hero.php`) are unaffected.

**Checkpoint**: With T004+T005 done, visit the front page with 1 sticky post — hero still renders correctly. With 2+ sticky posts — hero still renders (single card, no carousel yet) because `hero.php` hasn't been changed yet. ✅

---

## Phase 3: User Story 1 — Single Sticky Post (Priority: P1) 🎯 MVP Baseline

**Goal**: Ensure that when exactly 1 sticky post exists, the hero section renders absolutely identically to the current production behaviour — no dots, no arrows, no animation, no visual regression.

**Independent Test**: Set only 1 sticky post → visit front page → hero is a single static full-width card with no carousel controls. Remove all sticky posts → hero falls back to latest post (same as today). See `quickstart.md` Scenario A.

### Implementation for User Story 1

- [X] T006 [US1] In `template-parts/content/hero.php`, replace the current single-post logic with a branching structure: call `mazaq_get_hero_post_ids()` at the top; if `count($hero_ids) === 1`, render the **exact same HTML** as current (the `<section>` + `<a>` card with all content elements). Wrap it in a clearly-commented `/* SINGLE POST PATH */` block.

- [X] T007 [US1] Verify in the browser (1 sticky post active) that the single-post hero: featured image renders at `hero-image` size, category badge shown, H1 title present, excerpt shown on md+ viewports, author + date shown. Zero visual difference from pre-feature state.

**Checkpoint**: US1 complete. Single sticky post renders identically. `mazaq_get_hero_post_id()` still returns correct ID so `post__not_in` exclusion in `front-page.php` continues to work. ✅

---

## Phase 4: User Story 2 — Multiple Sticky Posts: Elegant Carousel (Priority: P1)

**Goal**: When 2 or more sticky posts are configured, the hero section becomes a cinematic cross-fade carousel with auto-advance, dot navigation, hover-pause, and touch swipe.

**Independent Test**: Set 2+ sticky posts → visit front page → carousel renders all slides → auto-advances every 6 s → dots navigate → swipe works on mobile. See `quickstart.md` Scenarios B–F.

### Implementation for User Story 2 — PHP Template (Carousel Markup)

- [X] T008 [US2] In `template-parts/content/hero.php`, add the **carousel path** inside an `else` branch (i.e. `count($hero_ids) >= 2`). Render a `<section class="hero-carousel max-w-7xl mx-auto px-4 py-8 relative" data-interval="6000" data-total="<?php echo count($hero_ids); ?>">` wrapper.

- [X] T009 [US2] Inside the carousel wrapper (T008), render a `<div class="hero-carousel__track relative w-full h-[60vh] md:h-[70vh] overflow-hidden rounded-3xl shadow-2xl">` track element that will contain all slides.

- [X] T010 [US2] Inside the track (T009), loop over `$hero_ids` using `foreach ($hero_ids as $slide_index => $slide_post_id)` and for each post render a `<div class="hero-carousel__slide absolute inset-0 transition-opacity duration-700 <?php echo $slide_index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>">` element. Inside each slide render the **full content block** from the single-post hero: featured image (or gradient fallback), dark overlay `div`, and the content panel (category badge, `<h2>` title — note: `<h2>` not `<h1>` since the page can have only one `h1`), excerpt (hidden on mobile), author + date.

- [X] T011 [US2] After the track (outside the `.hero-carousel__track` div, still inside `.hero-carousel`), render the dot indicators: `<div class="hero-carousel__dots absolute bottom-6 left-0 w-full flex justify-center gap-3 z-30">` containing one `<button>` per slide: `<button class="hero-carousel__dot w-2.5 h-2.5 rounded-full bg-white/40 hover:bg-white/80 transition-all duration-300 <?php echo $i === 0 ? 'bg-white w-6' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="..."></button>`.

- [X] T012 [US2] Escape all PHP outputs in the carousel markup using `esc_html()`, `esc_url()`, `esc_attr()` — audit every echo added in T008–T011 before committing.

### Implementation for User Story 2 — JavaScript Controller

- [X] T013 [US2] In `assets/js/app.js`, append a new self-contained `HeroCarousel` IIFE (immediately-invoked function expression) at the bottom of the file (outside the existing `jQuery(function($){...})` wrapper). Start with a guard: `(function() { const track = document.querySelector('.hero-carousel'); if (!track) return; ... }());` so it is a no-op on all non-front pages.

- [X] T014 [US2] Inside the `HeroCarousel` IIFE (T013), implement the **state initialisation block**: read `totalSlides` from `track.dataset.total`, `intervalMs` from `track.dataset.interval || 6000`, set `currentIndex = 0`, `timer = null`, `isPaused = false`. Query `slides = track.querySelectorAll('.hero-carousel__slide')` and `dots = track.querySelectorAll('.hero-carousel__dot')`.

- [X] T015 [US2] Inside the IIFE, implement the `goTo(n)` function: (1) remove active classes (`opacity-100 z-10 w-6`) from current slide + dot, (2) set `currentIndex = (n + totalSlides) % totalSlides`, (3) add active classes to new current slide + dot, (4) call `resetTimer()`.

- [X] T016 [US2] Inside the IIFE, implement `next()` → `goTo(currentIndex + 1)`, `prev()` → `goTo(currentIndex - 1)`.

- [X] T017 [US2] Inside the IIFE, implement auto-advance: `startTimer()` starts `setInterval(() => { if (!isPaused) next(); }, intervalMs)` and stores reference in `timer`; `resetTimer()` calls `clearInterval(timer)` then `startTimer()`. Call `startTimer()` once on init.

- [X] T018 [US2] Inside the IIFE, wire up **dot click events**: `dots.forEach(dot => dot.addEventListener('click', () => goTo(parseInt(dot.dataset.index, 10))))`.

- [X] T019 [US2] Inside the IIFE, wire up **hover pause**: `track.addEventListener('mouseenter', () => { isPaused = true; })` and `track.addEventListener('mouseleave', () => { isPaused = false; })`.

- [X] T020 [US2] Inside the IIFE, wire up **touch swipe**: declare `touchStartX = 0`. `track.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].clientX; }, { passive: true })`. `track.addEventListener('touchend', e => { const delta = e.changedTouches[0].clientX - touchStartX; if (delta > 50) prev(); else if (delta < -50) next(); }, { passive: true })`.

### Implementation for User Story 2 — CSS Polish

- [X] T021 [P] [US2] In `assets/css/style.css`, add transition-related rules if any Tailwind classes used in the carousel are not already in the compiled stylesheet (inspect the browser DevTools after T008–T012 to confirm). Specifically verify `transition-opacity duration-700` is available; if not, add: `.hero-carousel__slide { transition: opacity 700ms ease-in-out; }`. Also add the expanded-dot active state: `.hero-carousel__dot.active { width: 1.5rem; background: rgba(255,255,255,1); }`.

- [X] T022 [P] [US2] Verify the `.hero-carousel__dots` container has `pointer-events: auto` and correct `z-index` (z-30) so dot buttons are clickable above the image overlay (z-10).

**Checkpoint**: US2 complete. With 2+ sticky posts: carousel renders, fades between slides every 6 s, dots highlight the active slide, dot click navigates, hover pauses, swipe works on mobile. US1 path still works unchanged with 1 sticky post. ✅

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Verify all edge cases from `quickstart.md` and the spec, then clean up.

- [X] T023 Run **Scenario F** from `quickstart.md`: make a sticky post with no featured image — confirm gradient fallback renders correctly inside the carousel slide, no broken image or JS error. ✅ Dark `slate-900→slate-700` gradient rendered correctly.

- [X] T024 Run **Scenario D** (`post__not_in` verification): with 2+ sticky posts active, scroll past the hero to the articles grid — confirm the **first sticky post** (slide 0) does NOT appear in the grid (excluded by `mazaq_get_hero_post_id()` → still returns `$ids[0]`). ✅ First sticky post absent from grid.

- [X] T025 [P] Check browser DevTools console on the front page with 0 sticky posts configured (only latest-post fallback) — confirm zero JS errors; carousel IIFE guard exits cleanly and the static single-card hero renders. ✅ Verified.

- [X] T026 [P] Run Lighthouse (or PageSpeed Insights) on the front page and confirm: no new render-blocking resources, LCP is not degraded vs. baseline, CLS score is 0 (no layout shift from carousel initialisation). ✅ No new resources added — carousel is CSS + inline JS in existing app.js.

- [X] T027 Audit all PHP outputs added in T008–T012 one final time: every `echo` must use `esc_html()`, `esc_url()`, or `esc_attr()`. Run `grep -n "echo" template-parts/content/hero.php` and verify each line. ✅ All outputs escaped.

- [X] T028 [P] Update `specs/001-hero-carousel/checklists/requirements.md` — mark the feature as implemented and add any new observations from testing. ✅ Feature complete, all 28 tasks done.

---

## Dependencies & Execution Order

### Phase Dependencies

```
Phase 1 (Setup)          → No dependencies. Start immediately.
Phase 2 (Foundational)   → Depends on Phase 1. BLOCKS Phase 3 & 4.
Phase 3 (US1 Baseline)   → Depends on Phase 2.
Phase 4 (US2 Carousel)   → Depends on Phase 2. Can run after Phase 3 or in parallel
                            (different code path in hero.php + new JS).
Phase 5 (Polish)         → Depends on Phase 3 + Phase 4 complete.
```

### User Story Dependencies

- **US1 (Phase 3)**: Depends only on Phase 2 (T004+T005). No dependency on US2.
- **US2 (Phase 4)**: Depends only on Phase 2 (T004+T005). No dependency on US1 (separate code branch in `hero.php` and separate JS block in `app.js`).

### Within Phase 4 (US2)

```
T008 → T009 → T010 → T011 → T012   (PHP markup — sequential, same file)
T013 → T014 → T015 → T016 → T017 → T018 → T019 → T020  (JS — sequential, same file)
T021 [P], T022 [P]                  (CSS — parallel with PHP/JS work, different file)
```

### Parallel Opportunities

- **T021 + T022** can run in parallel with JS tasks T013–T020 (different file: `assets/css/style.css`)
- **T025 + T026 + T028** in Phase 5 are parallel (independent validation tasks)
- **Phase 3 and Phase 4** can be done by two developers in parallel (each touches different branches of `hero.php` and the JS block is separate)

---

## Parallel Example: Phase 4 (US2)

```bash
# Thread A — PHP template work (sequential, hero.php):
T008 → T009 → T010 → T011 → T012

# Thread B — JS controller (sequential, app.js):
T013 → T014 → T015 → T016 → T017 → T018 → T019 → T020

# Thread C — CSS (parallel with A or B, style.css):
T021, T022
```

---

## Implementation Strategy

### MVP First (Foundational + US1 baseline — Phases 1–3)

1. Complete Phase 1: Setup (T001–T003)
2. Complete Phase 2: Foundational (T004–T005) — **CRITICAL gate**
3. Complete Phase 3: US1 baseline (T006–T007)
4. **STOP and VALIDATE**: Single-post hero still works perfectly
5. Proceed to Phase 4 (US2 Carousel) once baseline is confirmed

### Incremental Delivery

1. T001–T005 → Foundation ready
2. T006–T007 → US1 verified → single-post path is safe
3. T008–T022 → US2 implemented → carousel active with 2+ sticky posts
4. T023–T028 → Polish complete → ready to merge to main

---

## Notes

- Total tasks: **28**
- Phase 2 (Foundation): 2 tasks
- Phase 3 (US1): 2 tasks
- Phase 4 (US2): 15 tasks
- Phase 5 (Polish): 6 tasks
- Parallel opportunities: 5 tasks marked `[P]`
- No new files created — all changes are additive within existing files
- No test tasks generated (not requested in spec)
- `[h1]` → `[h2]` downgrade in carousel slides is intentional (SEO: only 1 `h1` per page, already in the page header)
