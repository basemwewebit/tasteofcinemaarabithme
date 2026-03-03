# Feature Specification: Archive and 404 Ad Injection

**Feature Branch**: `001-archive-ads-injection`  
**Created**: 2026-03-03  
**Status**: Draft  
**Input**: User description: "بدنا نشتغل هون @[/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/archive.php] @[/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/404.php] @[/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/category.php] @[/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/author.php] بدنا نحقن الاعلانات في هاي الصفحات بشكل مرتب ومنسق"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Archive Grid Ad Injection (Priority: P1)

As a site owner, I want to display ads within the post grid on category, author, and archive pages every 6 posts, so that I can monetize archive traffic while maintaining a clean, organized layout.

**Why this priority**: High impact on revenue and user experience consistency with the home page.

**Independent Test**: Visit a category or author page with at least 7 posts and verify that an ad block appears after the 6th post in the grid.

**Acceptance Scenarios**:

1. **Given** a category page with multiple posts, **When** scrolling through the grid, **Then** an ad block should appear seamlessly after every 6th post.
2. **Given** a mobile view, **When** viewing the archive grid, **Then** the ad blocks should be responsive and centered.

---

### User Story 2 - 404 Page Monetization (Priority: P2)

As a site owner, I want to show a banner ad on the 404 page below the "Return Home" call-to-action, so that I don't lose monetization opportunities even when users land on invalid URLs.

**Why this priority**: Minor but important monetization touchpoint.

**Independent Test**: Access a non-existent URL (e.g., `/non-existent-page`) and verify an ad banner is displayed below the main error message and button.

**Acceptance Scenarios**:

1. **Given** a user lands on the 404 page, **When** the page loads, **Then** an ad banner is visible below the "Return Home" button but above the footer.

---

### User Story 3 - Visual Consistency across Archives (Priority: P2)

As a site owner, I want all archive-type pages (General Archive, Category, Author) to have a consistent ad layout, including a top banner below the header, to ensure a professional look.

**Why this priority**: Consistency is key for brand trust and professional aesthetics.

**Independent Test**: Navigate between a category page and an author page and verify they both have the same ad banner placement at the top.

**Acceptance Scenarios**:

1. **Given** an author page, **When** it loads, **Then** it should show the same top banner ad slot as the category page.

---

### Edge Cases

- **What happens when there are fewer than 6 posts?** No grid-injected ad should appear, only the top banner.
- **How does system handle ad blockers?** The ad container should collapse or show a tidy "Advertisement" label with a consistent background color to avoid "missing tooth" look.
- **What if an ad slot is empty in the theme options?** The `mazaq_render_ad` function should handle this by either showing a placeholder (for admins) or staying hidden (for users).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST inject ad blocks into the post loops of `archive.php`, `category.php`, and `author.php`.
- **FR-002**: Injection frequency MUST be set to every 6 posts globally for archive grids.
- **FR-003**: System MUST provide a dedicated ad slot for the 404 page (`ad_slot_404_banner`).
- **FR-004**: Ad containers MUST match the theme's design system, specifically using the same padding, border-radius, and background colors as post cards.
- **FR-005**: Ad blocks inside grids MUST take up a single column space in the grid (match `card-category` or `card-author` sizing).
- **FR-006**: System MUST ensure that top banner ads are present and consistent across `archive.php`, `category.php`, and `author.php`.

### Key Entities *(include if feature involves data)*

- **Ad Slot**: A configuration setting in the theme options containing the AdSense slot ID.
- **Archive Grid**: The UI component displaying posts in a 3-column (desktop) or 1-column (mobile) layout.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of archive-type pages (category, author, date, etc.) display ads in the expected locations.
- **SC-002**: Ad containers maintain 100% responsiveness and do not cause horizontal scrolling on mobile (viewport width < 480px).
- **SC-003**: Ad injection logic does not increase page load time by more than 50ms (server-side processing).
- **SC-004**: Cumulative Layout Shift (CLS) for ad containers is kept below 0.1 by reserving space for ads.
