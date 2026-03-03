# Tasks: loader-ads

**Input**: Design documents from `/specs/001-loader-ads/`
**Prerequisites**: plan.md, spec.md, data-model.md, quickstart.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

*(No pure project initialization required as this integrates into an existing WordPress theme. Proceeding to foundational tasks.)*

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

- [x] T001 Register WordPress Customizer settings (`toc_ad_injection_enabled`, `toc_ad_injection_interval`) in `functions.php` (or dedicated customizer file).
- [x] T002 [P] Create the base `template-parts/ad-slot.php` reusable component file.

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Brand-Aligned Site Loader (Priority: P1) 🎯 MVP

**Goal**: Show a premium, beautifully animated loader based on the Taste of Cinema logo on the first page load per session.

**Independent Test**: Simulate a slow network connection in a fresh incognito session. Verify the loader appears, pulses smoothly, and disappears when the content loads, and does not appear on subsequent page navigations in the same session.

### Implementation for User Story 1

- [x] T003 [P] [US1] Add loader HTML markup (including `assets/images/logo.svg`) to `header.php`.
- [x] T004 [P] [US1] Implement loader CSS animations (pulsating effect) and hardware-accelerated transitions in `style.css`.
- [x] T005 [P] [US1] Add vanilla JavaScript to `footer.php` (or existing main JS file) to check `sessionStorage('toc_loader_seen')`, manage the loader visibility, and set the session flag on load.

**Checkpoint**: At this point, the loader should be fully functional and testable independently.

---

## Phase 4: User Story 2 - Reliable Ad System & Documentation (Priority: P1)

**Goal**: Implement a robust ad injection mechanism in post grids, configurable via wp-admin, with comprehensive documentation and CLS safeguards.

**Independent Test**: Enable grid ads in the Customizer, set an interval (e.g., 3), and verify that ad slots appear accurately within the archive post grid without causing layout shifts.

### Implementation for User Story 2

- [x] T006 [US2] Modify `template-parts/post-grid.php` (and any other relevant archive templates) to inject `template-parts/ad-slot.php` using the WP query loop index, respecting the interval and toggle from `wp_options`.
- [x] T007 [P] [US2] Add CSS styling to `style.css` for the ad slot container, ensuring a predefined `min-height` to prevent Cumulative Layout Shift (CLS < 0.1).
- [x] T008 [US2] Implement fallback/collapse logic in `template-parts/ad-slot.php` to handle empty or failing ad loads gracefully.
- [x] T009 [P] [US2] Create user/admin documentation for managing ads in `specs/001-loader-ads/ad-documentation.md`.

**Checkpoint**: Ad system should be fully functional, configurable, and safely injected into grids.

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T010 [P] Test loader behavior and `sessionStorage` logic across multiple simulated sessions and browsers.
- [x] T011 [P] Profile page load with Chrome DevTools to ensure CLS remains < 0.1 during ad injection and loader dismissal.
- [x] T012 Validate code against WordPress Action/Filter hook best practices.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: N/A
- **Foundational (Phase 2)**: Must complete before Phase 4 (US2).
- **User Stories (Phase 3 & 4)**: 
  - **US1 (Loader)**: Can start immediately (independent of Foundational Phase).
  - **US2 (Ads)**: Depends on Foundational Phase (Settings and Ad Slot base).
- **Polish (Final Phase)**: Depends on all user stories being complete.

### Parallel Opportunities

- **US1** and **Foundational** tasks can be worked on concurrently.
- Within **US1**, the HTML markup (T003), CSS (T004), and JS logic (T005) can be developed in parallel by coordinating class names.
- Within **US2**, CSS styling for CLS prevention (T007) and Documentation (T009) can be done in parallel with the PHP injection logic.

---

## Implementation Strategy

### MVP First (User Story 1 & Foundational)

1. Complete Phase 3: User Story 1 (Loader). This provides immediate visual value.
2. Complete Phase 2: Foundational (Ad Settings).
3. Complete Phase 4: User Story 2 (Ad Injection).
4. Polish and test.
