# Tasks: Website Development & UI Updates

**Input**: Design documents from `/specs/001-site-ui-updates/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, quickstart.md ✅

**Tests**: Not explicitly requested — test tasks omitted. Verification steps are included within implementation tasks.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3, US4)
- Exact file paths included in descriptions

---

## Phase 1: Setup

**Purpose**: Ensure the feature branch is clean and the build pipeline is ready.

- [x] T001 Verify branch `001-site-ui-updates` is checked out and clean with `git status` in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/`
- [x] T002 [P] Run `npm install` and verify TailwindCSS build works with `npm run build` in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/`
- [x] T003 [P] Create the `template-parts/homepage/` directory at `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/template-parts/homepage/`

---

## Phase 2: Foundational (CSS Infrastructure)

**Purpose**: Add shared CSS rules that multiple user stories depend on — header transitions and thumbnail effects both need entries in the same stylesheet.

**⚠️ CRITICAL**: These CSS foundations must be in place before US1 and US2 implementation.

- [x] T004 Add smart-sticky header CSS transition rules (`.header--scrolled-up`, `.header--scrolled-down`, compact height `h-14`, transform translateY, 300ms transition) in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/assets/css/src/style.css`
- [x] T005 [P] Add thumbnail enhancement CSS rules (card thumbnail shadows for light/dark mode, hover scale/shadow deepening, cinematic gradient overlay `::after`, fallback background, 400ms transitions) in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/assets/css/src/style.css`
- [x] T006 Run `npm run build` to compile TailwindCSS with new custom styles in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/`

**Checkpoint**: CSS compiled — header transition classes and thumbnail styles ready for JS/PHP to use.

---

## Phase 3: User Story 1 — Sticky Navigation While Scrolling (Priority: P1) 🎯 MVP

**Goal**: Implement smart-sticky header that hides on scroll-down and reveals on scroll-up with smooth animations.

**Independent Test**: Load any page, scroll down past 80px — header hides. Scroll up — header reveals in compact state. Scroll to top — header returns to full height.

### Implementation for User Story 1

- [x] T007 [US1] Update `<header>` element to include `data-scroll-state="top"` attribute and add transition utility classes for transform/opacity in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/header.php` (line 64)
- [x] T008 [US1] Implement scroll direction detection logic in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/assets/js/app.js`: track `lastScrollY`, detect scroll direction, apply `header--scrolled-down` (hide) / `header--scrolled-up` (reveal compact) / default (top) classes to `<header>`. Use 80px threshold, `passive: true` scroll listener, and `requestAnimationFrame` for performance.
- [x] T009 [US1] Update sidebar sticky `top-28` offset in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/sidebar.php` to account for compact header height (`top-20` when scrolled)
- [x] T010 [US1] Run `npm run build` and verify header transitions work on desktop and mobile, confirm no layout shift (CLS ≤ 0.1), confirm header does not overlap site loader (z-index check: header z-40 < loader z-100)

**Checkpoint**: Smart-sticky header fully functional on all pages — independently testable.

---

## Phase 4: User Story 2 — Professional Thumbnail Visual Effects (Priority: P1)

**Goal**: Apply polished visual effects (shadows, borders, hover transitions) to all post card thumbnails across the site.

**Independent Test**: Load homepage or any archive page, hover over card thumbnails — smooth shadow/scale transition visible in both light and dark modes.

### Implementation for User Story 2

- [x] T011 [P] [US2] Update card template to add thumbnail-specific CSS classes for enhanced shadows and hover container in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/template-parts/content/card.php` — add thumbnail wrapper classes and fallback background gradient for missing images
- [x] T012 [P] [US2] Update wide card template with matching thumbnail enhancement classes in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/template-parts/content/card-wide.php` — match visual treatment from card.php
- [x] T013 [P] [US2] Update category card template with matching thumbnail enhancement classes in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/template-parts/content/card-category.php`
- [x] T014 [P] [US2] Update search card template with matching thumbnail enhancement classes in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/template-parts/content/card-search.php`
- [x] T015 [P] [US2] Update related card template with matching thumbnail enhancement classes in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/template-parts/content/card-related.php`
- [x] T016 [US2] Run `npm run build` and visually verify thumbnail effects on homepage, category pages, search results, and single post related cards in both light and dark modes

**Checkpoint**: All card templates display professional thumbnail effects — independently testable.

---

## Phase 5: User Story 3 — ReCaptcha Removal (Priority: P2)

**Goal**: Completely remove all reCAPTCHA code, scripts, admin settings, and database options while keeping the contact form functional with existing anti-spam protections.

**Independent Test**: Load contact page — no reCAPTCHA scripts in Network tab. Submit form — succeeds. Check WP admin — no reCAPTCHA settings page. Run `grep -r "recaptcha" --include="*.php" --include="*.js" .` — zero matches (excluding specs/).

### Implementation for User Story 3

- [x] T017 [US3] Delete reCAPTCHA PHP class files: remove `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/inc/recaptcha/class-recaptcha-admin.php`, `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/inc/recaptcha/class-recaptcha-hooks.php`, `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/inc/recaptcha/class-recaptcha-verify.php`, and the directory `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/inc/recaptcha/`
- [x] T018 [P] [US3] Delete reCAPTCHA client-side handler at `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/assets/js/recaptcha-handler.js`
- [x] T019 [P] [US3] Delete test file at `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/test-recaptcha-settings.php`
- [x] T020 [US3] Remove the 3 reCAPTCHA `require_once` entries (lines 20-22) from `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/functions.php` — remove `'inc/recaptcha/class-recaptcha-admin.php'`, `'inc/recaptcha/class-recaptcha-verify.php'`, `'inc/recaptcha/class-recaptcha-hooks.php'` from the `$mazaq_includes` array
- [x] T021 [US3] Remove the reCAPTCHA verification block (lines 38-43) from `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/inc/contact-form.php` — delete the `$token` variable assignment and the `TOC_Recaptcha_Verify::verify_token()` check while preserving the honeypot, rate limit, and nonce protections
- [x] T022 [US3] Create a one-time database cleanup function in `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/functions.php` that deletes `toc_recaptcha_site_key`, `toc_recaptcha_secret_key`, and `toc_recaptcha_score_threshold` from `wp_options` using `delete_option()`, triggered on `after_switch_theme` or a one-time admin_init check with a version flag
- [x] T023 [US3] Run `php -l` on modified files, run `grep -r "recaptcha" --include="*.php" --include="*.js" . | grep -v specs/` to verify zero remaining references, and test contact form submission

**Checkpoint**: reCAPTCHA fully removed — contact form works with honeypot + rate limit + nonce. Independently testable.

---

## Phase 6: User Story 4 — Modular Homepage Sections (Priority: P2)

**Goal**: Refactor front-page.php into 5 toggleable, priority-ordered sections controlled via the WordPress Customizer.

**Independent Test**: Open Customizer → Homepage Sections panel → toggle sections on/off, change priorities → preview and verify. Disable all sections → fallback message displayed.

### Implementation for User Story 4

- [x] T024 [US4] Create Customizer registration file at `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/inc/homepage-customizer.php`
- [x] T025 [US4] Add `require_once` for `inc/homepage-customizer.php` in the `$mazaq_includes` array in `functions.php`
- [x] T026 [P] [US4] Create template `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/template-parts/homepage/section-hero.php` — extract the hero template-part call and ad-responsive block from current front-page.php, wrapped with a Customizer-enabled check
- [x] T027 [P] [US4] Create template `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/template-parts/homepage/section-articles.php` — extract the `id="infinite-scroll-container"` loop block from current front-page.php, use `toc_hp_articles_posts_count` setting for `posts_per_page`, `toc_hp_articles_title` for the h2
- [x] T028 [P] [US4] Create template `template-parts/homepage/section-categories.php`
- [x] T029 [P] [US4] Create template `template-parts/homepage/section-banner.php`
- [x] T030 [P] [US4] Create template `template-parts/homepage/section-sidebar.php` (wraps existing sidebar)
- [x] T031 [US4] Refactor `front-page.php` to use dynamic section rendering: collect all 5 sections with their enabled state and priority from Customizer settings, sort enabled sections by priority ascending, loop through and call `get_template_part('template-parts/homepage/section-' . $slug)` for each. Handle sidebar specially: render within the flex layout container alongside main content sections. Add fallback message when all sections are disabled.
- [x] T032 [US4] Add homepage section spacing and layout CSS rules in `style.css` — consistent vertical spacing between sections, responsive padding, category grid styles, banner section styles
- [ ] T033 [US4] Run `npm run build`, run `php -l` on all new/modified PHP files, test Customizer panel (toggle each section, change priorities, verify live preview), test all-sections-disabled fallback

**Checkpoint**: Homepage fully modular — 5 independently toggleable sections with Customizer controls. Independently testable.

---

### Phase 7: Polish and Quality Assurance

- [x] T034 [P] [ALL] Verify layout stability by resizing the browser aggressively, ensuring sticky elements compute offsets accurately.
- [x] T035 [P] [ALL] Validate Customizer changes reflect correctly in live preview.
- [x] T036 [P] [ALL] Perform a Lighthouse/accessibility check on the homepage, category, and single post pages.
- [x] T037 [P] [US2] Verify `.thumbnail-fallback` appearance across light and dark modes.
- [x] T038 [P] [ALL] Update project requirements and mark UI features as finalized.
- [ ] T039 JavaScript-disabled fallback test: disable JS in browser, verify header remains static (no broken layout), contact form still submits, homepage sections still render
- [ ] T040 Run `grep -r "recaptcha" --include="*.php" --include="*.js" . | grep -v specs/` to confirm zero remaining reCAPTCHA references in codebase
- [ ] T041 Commit all changes with descriptive commit message referencing feature branch `001-site-ui-updates`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 — BLOCKS US1 and US2 (CSS must be compiled)
- **US1 Sticky Header (Phase 3)**: Depends on Phase 2 (CSS transitions must exist)
- **US2 Thumbnails (Phase 4)**: Depends on Phase 2 (CSS styles must exist). Can run in parallel with US1.
- **US3 ReCaptcha (Phase 5)**: No dependency on Phase 2 — can run in parallel with US1/US2
- **US4 Homepage (Phase 6)**: No dependency on Phases 3-5 — can run in parallel. Depends on Phase 1 (directory created).
- **Polish (Phase 7)**: Depends on ALL user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Depends on Phase 2 (CSS). No other story dependencies.
- **User Story 2 (P1)**: Depends on Phase 2 (CSS). No other story dependencies. **Can run in parallel with US1.**
- **User Story 3 (P2)**: Independent — no CSS/Phase 2 dependency. **Can run in parallel with US1/US2.**
- **User Story 4 (P2)**: Depends on Phase 1 (directory). **Can run in parallel with US1/US2/US3.**

### Within Each User Story

- CSS foundation → PHP/JS implementation → Build → Verify
- File deletions (US3) before file edits
- Customizer registration (US4) before template parts

### Parallel Opportunities

**Maximum parallelism after Phase 2:**
```
Phase 2 complete
    ├── US1 (T007-T010) — Developer A
    ├── US2 (T011-T016) — Developer B (parallel with US1)
    ├── US3 (T017-T023) — Developer C (parallel with US1/US2)
    └── US4 (T024-T033) — Developer D (parallel with all)
```

**Within US2 (5 card templates are independent):**
```
T011 card.php          ─┐
T012 card-wide.php     ─┤
T013 card-category.php ─┤── All [P] — parallel
T014 card-search.php   ─┤
T015 card-related.php  ─┘
```

**Within US4 (5 section templates are independent):**
```
T026 section-hero.php       ─┐
T027 section-articles.php   ─┤
T028 section-categories.php ─┤── All [P] — parallel
T029 section-banner.php     ─┤
T030 section-sidebar.php    ─┘
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup ✓
2. Complete Phase 2: Foundational CSS ✓
3. Complete Phase 3: User Story 1 (Sticky Header) ✓
4. **STOP and VALIDATE**: Test sticky header independently on all page types
5. Deploy/demo if ready — immediate UX improvement on every page

### Incremental Delivery

1. Setup + Foundational → CSS ready
2. Add US1 (Sticky Header) → Test → Deploy (MVP! — affects all pages)
3. Add US2 (Thumbnails) → Test → Deploy (visual polish across all listings)
4. Add US3 (ReCaptcha Removal) → Test → Deploy (cleanup + performance)
5. Add US4 (Homepage Modularization) → Test → Deploy (admin empowerment)
6. Polish phase → Final QA → Production release

### Recommended Sequential Order

For a single developer working sequentially:

1. **US3 first** (ReCaptcha Removal) — smallest scope, immediate cleanup, no visual regressions possible
2. **US2 next** (Thumbnails) — CSS-only, low risk, immediate visual improvement
3. **US1 next** (Sticky Header) — JS + CSS, medium complexity, high impact
4. **US4 last** (Homepage Modularization) — largest scope, structural refactor

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story is independently completable and testable
- Commit after each phase or logical group
- Stop at any checkpoint to validate story independently
- Run `npm run build` after any CSS changes to recompile TailwindCSS
- All paths are absolute to the theme directory at `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/`
