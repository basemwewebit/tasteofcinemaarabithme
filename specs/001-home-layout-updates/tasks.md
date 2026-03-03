---
description: "Task list for Home Layout Updates implementation"
---

# Tasks: Home Layout Updates

**Input**: Design documents from `/specs/001-home-layout-updates/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure verification

- [x] T001 Verify WordPress environment and theme structure is accessible in `front-page.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 In `functions.php` (or a dedicated `inc/*.php` included file), extract the hero article ID resolution logic into a new global function `mazaq_get_hero_post_id()`. This ensures the hero logic is centralized.

**Checkpoint**: Foundation ready - user story implementation can now begin.

---

## Phase 3: User Story 1 - Exclude Hero Article from Grid (Priority: P1) 🎯 MVP

**Goal**: Readers should not see the active hero article duplicated in the subsequent posts grid.

**Independent Test**: Load the homepage and verify the lead hero article does not appear anywhere in the recent articles grid below it.

### Implementation for User Story 1

- [x] T003 [US1] Update `template-parts/content/hero.php` to use `mazaq_get_hero_post_id()` instead of calculating it inline, verifying it falls back smoothly.
- [x] T004 [US1] Modify the `WP_Query` arguments in `front-page.php` to exclude the hero post explicitly using `'post__not_in' => [mazaq_get_hero_post_id()]`.

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently.

---

## Phase 4: User Story 2 - Fix "Most Read This Week" Mechanism (Priority: P1)

**Goal**: Display actual popular articles sorted by genuine views within the current week, discarding articles with 0 views.

**Independent Test**: The widget lists articles published in the last 7 days ordered tightly by `_post_views_count` descending, omitting any posts that have `_post_views_count` = 0.

### Implementation for User Story 2

- [x] T005 [P] [US2] In `inc/post-views.php`, modify `mazaq_get_most_read_posts()`: Add a `date_query` limiting results to `'after' => '1 week ago'`.
- [x] T006 [P] [US2] In `inc/post-views.php`, modify `mazaq_get_most_read_posts()`: Ensure the query excludes posts with `0` views by either applying a strict `meta_query` (value > 0).

**Checkpoint**: User Stories 1 AND 2 should both work independently.

---

## Phase 5: User Story 3 - Inject Ads in Articles Grid (Priority: P2)

**Goal**: Inject regular advertisements seamlessly within the infinite scroll grid (e.g., every 8th post).

**Independent Test**: Scroll through the homepage grid and count posts. An ad block must appear reliably every 8 posts regardless of pagination chunks.

### Implementation for User Story 3

- [x] T007 [P] [US3] Create an ad placeholder template file at `template-parts/ads/ad-grid.php` (if it doesn't already exist or reuse a responsive ad component like `ad-responsive.php` formatted for the grid constraint).
- [x] T008 [US3] In `front-page.php`, calculate the absolute global index for the ongoing query loop using `$paged` and `$posts_per_page`.
- [x] T009 [US3] In `front-page.php`, inject the ad block inside the loop specifically when `global_index % 8 === 0`.

**Checkpoint**: All user stories should now be independently functional.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T010 Final review of responsive spacing where ad blocks were injected in `front-page.php`.
- [x] T011 Verify PHP 8 compatibility and WordPress coding standards across all modified files (`front-page.php`, `inc/post-views.php`, `template-parts/content/hero.php`).

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion.
- **User Stories**: 
  - **US1 (Phase 3)**: Depends on Foundational Phase (T002).
  - **US2 (Phase 4)**: Independent of Foundational Phase, can run in parallel.
  - **US3 (Phase 5)**: Independent of Foundational Phase, can run in parallel.
- **Polish (Phase 6)**: Depends on all stories completion.

### Parallel Opportunities

- T005 and T006 (US2) can run in parallel with US1 and US3.
- T007 (US3) can run in parallel with any other tasks.

## Implementation Strategy

### MVP First (User Story 1 & 2)

1. Complete Phase 2: Foundational `mazaq_get_hero_post_id()` abstraction.
2. Complete Phase 3: Modify `front-page.php` query and `hero.php` to exclude the hero content.
3. Complete Phase 4: Patch `inc/post-views.php` logical bugs.
4. Test and validate.

### Incremental Delivery

1. MVP completed.
2. Proceed to Phase 5: Implement layout math for ad injection during infinite scrolling.
3. Proceed to Polish phase.
