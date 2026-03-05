# Feature Specification: Hero Carousel for Multiple Sticky Posts

**Feature Branch**: `001-hero-carousel`
**Created**: 2026-03-05
**Status**: Draft
**Input**: User description: "هون اذا عنا اكثر من بوست ستيكي بوست بدنا اياه يصير كاروسيل انيق واذا كان بوست واحد يظل شغال زي ما هو"

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Single Sticky Post (Priority: P1)

As a reader visiting the homepage when only one article is pinned to the top, I should see the same elegant full-width hero section I have today, so that nothing about my current experience changes.

**Why this priority**: The existing single-post experience is already polished and must remain completely unaffected.

**Independent Test**: Set exactly one sticky post in WordPress. Load the homepage. The hero section renders as a single static card with no carousel controls.

**Acceptance Scenarios**:

1. **Given** WordPress has exactly 1 sticky post, **When** the homepage loads, **Then** the hero section renders as a single static full-width card — no dots, arrows, or auto-advance behaviour appears.
2. **Given** the single sticky post has no featured image, **When** the homepage loads, **Then** the fallback gradient background is shown, same as the current behaviour.

---

### User Story 2 — Multiple Sticky Posts: Elegant Carousel (Priority: P1)

As a reader visiting the homepage when two or more articles are pinned to the top, I should see all pinned articles presented as a smooth, auto-advancing carousel inside the hero area, so that I can discover all of the editors' highlighted content without scrolling.

**Why this priority**: The carousel is the core deliverable of this feature and unlocks the ability to promote multiple articles at once.

**Independent Test**: Pin at least 2 posts in WordPress. Load the homepage. The hero section cycles through all sticky posts automatically. Navigation dots and optional arrow controls allow manual switching.

**Acceptance Scenarios**:

1. **Given** WordPress has 2 or more sticky posts, **When** the homepage loads, **Then** the hero section displays them as a carousel — one slide visible at a time — with dot indicators at the bottom.
2. **Given** the carousel is active, **When** the auto-advance timer fires, **Then** the next slide transitions in with a smooth, cinematic animation (fade or slide).
3. **Given** the carousel is active, **When** the reader clicks a dot indicator, **Then** the carousel immediately jumps to that specific slide and resets the auto-advance timer.
4. **Given** the carousel is active, **When** a slide is displayed, **Then** it shows the same information as the single-post hero: featured image, category badge, post title, excerpt, author, and date.
5. **Given** the page is loaded on a touch device, **When** the reader swipes left or right on the hero, **Then** the carousel advances or retreats to the adjacent slide.

---

### Edge Cases

- What happens if a sticky post has no featured image? → The existing gradient fallback renders for that slide.
- What if all sticky posts are unpublished or deleted between page loads? → Falls back to the single-hero logic using the next available hero post.
- What if there is only 1 sticky post but it gets a second post stickied later? → On the next page load the carousel activates automatically, no manual intervention needed.
- Does the carousel affect SEO? → Each slide's title and content remain in the HTML; the carousel is a visual layer only.

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST detect whether the number of sticky posts is 1 or more than 1 before rendering the hero section.
- **FR-002**: When exactly 1 sticky post exists, the system MUST render the existing single-post hero layout unchanged.
- **FR-003**: When 2 or more sticky posts exist, the system MUST render a carousel inside the hero area that displays each sticky post as a full-width slide.
- **FR-004**: Each carousel slide MUST contain all of the same content elements as the current single-post hero: featured image (or fallback gradient), category badge, post title, excerpt, author name, and publication date.
- **FR-005**: The carousel MUST auto-advance through slides at a configurable interval (default: 6 seconds per slide).
- **FR-006**: The carousel MUST include visible dot indicators that reflect the total number of slides and highlight the currently active slide.
- **FR-007**: Clicking a dot indicator MUST navigate directly to the corresponding slide and reset the auto-advance timer.
- **FR-008**: The carousel MUST support swipe gestures for touch/mobile navigation (swipe left → next, swipe right → previous).
- **FR-009**: The carousel transition animation MUST be smooth and visually polished (fade-through or cinematic slide).
- **FR-010**: The carousel MUST pause auto-advance while the user is hovering over the hero area (desktop) or has manually interacted with it.
- **FR-011**: The overall hero section dimensions and visual style MUST remain consistent whether 1 or many sticky posts are present.

### Key Entities

- **Sticky Post**: A WordPress post marked as "sticky", surfaced at the top of the homepage post loop.
- **Hero Slide**: A single full-width panel inside the carousel, containing a sticky post's content.
- **Carousel**: The animated container that cycles through multiple hero slides.
- **Dot Indicator**: A small interactive navigation element that maps to a specific slide.

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: When 1 sticky post is configured, the homepage hero renders identically to its current state — 0 visible changes in layout, controls, or behaviour.
- **SC-002**: When 2 or more sticky posts are configured, the hero section displays all of them as carousel slides with no slide omitted.
- **SC-003**: The carousel auto-advances within ±500 ms of the configured interval on desktop and mobile browsers.
- **SC-004**: Dot indicators accurately reflect the active slide at all times — including after manual interaction and auto-advance events.
- **SC-005**: Swipe gestures (left/right) trigger slide transitions on touch devices without requiring any extra taps.
- **SC-006**: The hero section visual footprint (height, padding, typography scale) does not change between the single-post and multi-post carousel states.
- **SC-007**: The feature introduces no visible layout shift or flash of unstyled content during page load on any viewport width.

---

## Assumptions

- The WordPress theme already uses Tailwind CSS utility classes; carousel styling will follow the same design system.
- The function `mazaq_get_hero_post_id()` currently returns a single post ID; the implementation will require a companion function that returns all sticky post IDs.
- Auto-advance interval (6 s) and the maximum number of carousel slides (all sticky posts, no hard cap) are reasonable defaults; they may be adjusted with no spec change needed.
- JavaScript (vanilla or already-bundled library in the theme) is available for carousel interactivity; no new external library dependency is required if a lightweight custom implementation is feasible.
