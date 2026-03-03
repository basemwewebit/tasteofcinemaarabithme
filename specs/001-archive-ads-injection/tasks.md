# Tasks: Archive and 404 Ad Injection

**Input**: Design documents from `/specs/001-archive-ads-injection/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, quickstart.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 Create `template-parts/ads/ad-404.php` placeholder file
- [x] T002 [P] Register new ad slot `ad_slot_404_banner` in `inc/scf-fields.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

- [x] T003 Refine `template-parts/ads/ad-grid.php` to handle archive grid col-spans correctly
- [x] T004 [P] Update `inc/ads.php` to ensure `mazaq_render_ad` handles new slots correctly

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Archive Grid Ad Injection (Priority: P1) 🎯 MVP

**Goal**: Inject ads every 6 posts in category, author, and archive grids.

**Independent Test**: Visit a category page with 7+ posts and verify ad after 6th post.

### Implementation for User Story 1

- [x] T005 [P] [US1] Implement post counter and ad injection in `category.php`
- [x] T006 [P] [US1] Implement post counter and ad injection in `author.php`
- [x] T007 [P] [US1] Implement post counter and ad injection in `archive.php`
- [x] T008 [US1] Verify grid layout responsiveness with injected ads in all three archive templates

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently.

---

## Phase 4: User Story 2 - 404 Page Monetization (Priority: P2)

**Goal**: Display an ad banner on the 404 page.

**Independent Test**: Access a non-existent URL and verify the ad appears below the CTA button.

### Implementation for User Story 2

- [x] T009 [US2] Create ad component UI in `template-parts/ads/ad-404.php`
- [x] T010 [US2] Inject the ad component into `404.php` below the home button
- [x] T011 [US2] Verify ad visibility and styling on mobile and desktop 404 pages

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently.

---

## Phase 5: User Story 3 - Visual Consistency across Archives (Priority: P2)

**Goal**: Ensure all archive types have consistent top banners.

**Independent Test**: Verify `author.php` and `archive.php` have top banners matching `category.php`.

### Implementation for User Story 3

- [x] T012 [P] [US3] Add `mazaq_render_ad('ad_slot_archive_banner', 'horizontal')` to `archive.php`
- [x] T013 [P] [US3] Add `mazaq_render_ad('ad_slot_archive_banner', 'horizontal')` to `author.php`
- [x] T014 [US3] Verify consistent banner placement across all archive templates

**Checkpoint**: All user stories should now be independently functional.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T015 [P] Performance optimization: Ensure `mazaq_render_ad` calls are efficient
- [x] T016 [P] Code cleanup: Remove any temporary bypasses or dummy flags
- [x] T017 Run `quickstart.md` validation across all scenarios

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
- **Polish (Final Phase)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Phase 2 - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Phase 2 - Independent of US1
- **User Story 3 (P3)**: Can start after Phase 2 - Independent of US1/US2

---

## Parallel Example: User Story 1

```bash
# Launch grid injections in parallel:
Task: "Implement post counter and ad injection in category.php"
Task: "Implement post counter and ad injection in author.php"
Task: "Implement post counter and ad injection in archive.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 & 2
2. Complete Phase 3 (Archive Grid Injection)
3. **STOP and VALIDATE**: Test archive grids independently

### Incremental Delivery

1. Foundation ready
2. Add US1 → Test (MVP!)
3. Add US2 → Test
4. Add US3 → Test
