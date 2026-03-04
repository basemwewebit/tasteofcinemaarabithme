---
description: "Task list template for feature implementation"
---

# Tasks: Google reCAPTCHA v3 Security Integration

**Input**: Design documents from `/specs/001-google-recaptcha-v3/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Manual verification via WordPress Dashboard and form submissions as per testing strategy in spec.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- Paths shown below assume single project - mapping directly to `tasteofcinemaarabithme`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 Create `inc/recaptcha/` directory structure and blank module files per implementation plan
- [x] T002 Include the root recaptcha module config inside `functions.php` to ensure files are loaded.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T003 Implement backend verification HTTP API class `class-recaptcha-verify.php` to handle communication with `google.com/recaptcha/api/siteverify` using `wp_remote_post`. Ensure timeout is set to 3 seconds.
- [x] T004 Build fail-open logic inside the verification method (FR-006).

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 3 - Admin Configuration (Priority: P3 mapped to Phase 3) 

**Goal**: Admins configure Site Key, Secret Key, and threshold score. We build this First so subsequent US1 and US2 have data to pull.

**Independent Test**: Forms save accurately to `wp_options`.

### Implementation for User Story 3

- [x] T005 [P] [US3] Create `class-recaptcha-admin.php` and register settings for `toc_recaptcha_site_key`, `toc_recaptcha_secret_key`, and `toc_recaptcha_score_threshold`.
- [x] T006 [P] [US3] Build the settings page UI logic in `class-recaptcha-admin.php` to render input fields and save values (default threshold 0.5).

**Checkpoint**: Admin Configuration completed and keys exist.

---

## Phase 4: User Story 1 - Secure Contact Form (Priority: P1) 🎯 MVP

**Goal**: Automatically secure standard contact forms without user friction.

**Independent Test**: Submitting contact form calls Google's API; gets correct resolution based on score.

### Implementation for User Story 1

- [x] T007 [P] [US1] Create frontend script `assets/js/recaptcha-handler.js` that intercepts contact form submissions, injects the `g-recaptcha-response` token, and submits.
- [x] T008 [US1] Create `class-recaptcha-hooks.php` to selectively enqueue the Google script URL and `recaptcha-handler.js` on the contact page.
- [x] T009 [US1] Update `inc/contact-form.php` backend handler to integrate with `class-recaptcha-verify.php` and validate token score against the db threshold before processing.

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 5: User Story 2 - Secure Authentication Forms (Priority: P2)

**Goal**: Forms for login and registration or password resets must be protected against brute-force attacks invisibly.

**Independent Test**: Login and registration screens properly inject frontend tokens and backend verifies them.

### Implementation for User Story 2

- [x] T010 [P] [US2] Update `assets/js/recaptcha-handler.js` to correctly target standard WP login, registration, and password reset form IDs.
- [x] T011 [US2] Update `class-recaptcha-hooks.php` to hook into WordPress core hooks: `wp_authenticate_user` (Login verification), `registration_errors` (Registration), and `allow_password_reset`.
- [x] T012 [US2] Wire the authentication hooks to `class-recaptcha-verify.php` to reject logins/registrations where the score falls below the threshold. 

**Checkpoint**: All user stories should now be independently functional

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T013 [P] Add debug logging if a request fails for tracking purposes without disrupting user experience.
- [x] T014 Review scripts to ensure `wp_enqueue_script` targets are exactly strict to prevent CLS or performance drops on unrelated archives/single posts.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: Setup API Admin config FIRST (US3) -> Then US1 -> Then US2
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 3 (Admin Config)**: Built first to serve as the baseline for the others.
- **User Story 1 (Contact Form)**: Must be able to read settings from US3.
- **User Story 2 (Auth Forms)**: Must be able to read settings from US3. Can be built parallel with US1.

### Parallel Opportunities

- Admin GUI (T005/T006) can execute parallel with JS scaffolding (T007).
- Contact logic and Auth logic can be tested simultaneously.
