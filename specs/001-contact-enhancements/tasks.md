# Tasks: Contact Page Enhancements

**Input**: Design documents from `/specs/001-contact-enhancements/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/ui-structure.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 Create `inc/post-types` directory and empty `inc/post-types/contact-message.php` file structure

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 Include `inc/post-types/contact-message.php` inside the `$mazaq_includes` array in `functions.php`

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Store Contact Submissions in Dashboard (Priority: P1) 🎯 MVP

**Goal**: Save contact form submissions directly to the dashboard as a custom post type.

**Independent Test**: Submitting a contact form correctly creates a new entry in the WordPress dashboard containing the message details.

### Implementation for User Story 1

- [x] T003 [P] [US1] Register non-public `contact_message` Custom Post Type in `inc/post-types/contact-message.php`
- [x] T004 [US1] Update `mazaq_handle_contact_form()` in `inc/contact-form.php` to use `wp_insert_post()` and `add_post_meta()` for saving submissions
- [x] T005 [US1] Customize admin list columns for `contact_message` CPT in `inc/post-types/contact-message.php` to display Submitter Name and Email

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - Manage Social Media Links via Repeater Field (Priority: P2)

**Goal**: Manage social media links using a flexible ACF repeater field instead of static options.

**Independent Test**: Adding a new social link in the theme options with an SVG icon successfully updates the front-end contact page.

### Implementation for User Story 2

- [x] T006 [P] [US2] Remove static `social_twitter`/`social_website` fields and register `social_links` repeater field in `inc/scf-fields.php`
- [x] T007 [US2] Update `page-contact.php` to iterate over the `social_links` ACF repeater field and output the platform name/SVG icons and URLs

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T008 Run manual testing following the steps in `quickstart.md`
- [x] T009 Code cleanup, PHP strict typing checks, and styling adjustments

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User Story 1 and User Story 2 can proceed in parallel

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2)
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) (completely independent from US1)

### Parallel Opportunities

- T003 and T006 can be executed in parallel as they touch completely different files.

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational 
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test User Story 1 independently by submitting the form and checking WP Admin.

### Incremental Delivery

1. Deliver US1 (Form data saving).
2. Deliver US2 (Repeater fields for social links).
