# Implementation Tasks: Fix Single Post Template Issues

## Phase 1: Setup
*(No specific setup tasks required as this is an enhancement to an existing theme).*

## Phase 2: Foundational
*(No specific foundational tasks required).*

## Phase 3: Working Breadcrumbs (P1)
- **Goal**: Implement dynamic breadcrumbs reflecting the actual site hierarchy, replacing static HTML.
- **Independent Test**: Clicking any generated breadcrumb URL correctly navigates to the intended page (Home/Category).

- [x] T001 [US1] Create custom `toc_breadcrumbs()` function in `wp-content/themes/tasteofcinemaarabithme/functions.php` to generate breadcrumb markup.
- [x] T002 [US1] Replace the static breadcrumb markup in `wp-content/themes/tasteofcinemaarabithme/single.php` with a call to the new `toc_breadcrumbs()` function.

## Phase 4: In-Content Advertisements (P1)
- **Goal**: Inject advertisement placeholders/blocks automatically every 3 paragraphs within the single post content.
- **Independent Test**: View a post with 6+ paragraphs and ensure an ad box appears closely after the 3rd and 6th paragraph.

- [x] T003 [US3] Implement `toc_inject_in_content_ads()` function hooking to `the_content` filter in `wp-content/themes/tasteofcinemaarabithme/functions.php` to insert ads exactly after every 3 `</p>` tags.

## Phase 5: Author Link and Accurate Reading Time (P2)
- **Goal**: Make the author name clickable and auto-calculate an accurate reading time based on content length.
- **Independent Test**: Hover/Click the author name correctly opens the archive. Reading time dynamically adjusts based on post word count (e.g. 200 words/min).

- [x] T004 [US2] Create helper function `toc_estimated_reading_time()` in `wp-content/themes/tasteofcinemaarabithme/functions.php` to calculate and return reading time based on `$post->post_content`.
- [x] T005 [P] [US2] Modify author metadata block in `wp-content/themes/tasteofcinemaarabithme/single.php` to use `the_author_posts_link()`.
- [x] T006 [P] [US2] Modify reading time placeholder in `wp-content/themes/tasteofcinemaarabithme/single.php` to dynamically fetch output from `toc_estimated_reading_time()`.

## Phase 6: Effective Related Articles (P2)
- **Goal**: Replace the static related articles section with a dynamic, highly relevant query.
- **Independent Test**: Related articles at the bottom of the post share the same categories and are not the current post. Transient caching works.

- [x] T007 [US4] Implement a cached `WP_Query` for related posts utilizing the Transients API (12 hr duration) at the bottom of `wp-content/themes/tasteofcinemaarabithme/single.php`, ensuring the current post id is excluded and the category matches.

## Phase 7: Polish & Cross-Cutting Concerns
- **Goal**: Final UI/UX review & Edge Cases Verification.

- [x] T008 Test all edge cases locally, such as posts without categories, short posts (< 3 paragraphs), and unassigned authors. Ensure fallback UI works smoothly without errors.

---

### Dependencies
- T002 depends on T001
- T006 depends on T004
- The phases (Phase 3 through Phase 6) are almost entirely decoupled and can technically be handled in parallel if needed, except they affect the same `single.php` and `functions.php` files slightly differently.

### Implementation Strategy
Start with **Phase 3 (Breadcrumbs)** and **Phase 4 (Ads)** to handle the high P1 priority features, committing them individually before moving down to the P2 requirements. Validating that `functions.php` is kept clean is key.
