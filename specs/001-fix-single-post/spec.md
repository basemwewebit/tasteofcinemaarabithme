# Feature Specification: Fix Single Post Template Issues

**Feature Branch**: `001-fix-single-post`  
**Created**: 2026-03-03  
**Status**: Draft  
**Input**: User description: "1- البريدكرمب يجب اصلاحه ويصير يشتغل صح 2- الكاتب يجب ان يكون رابط - عدد دقائق القراءة يجب ان يكون صحيح بشكل يناسب المحتوى 3- الاعلامات تطلع بشكل مناسب ايضا داخل المحتوى مثلا كل 3 فقرات 4- يجب ان يعمل مقالات ذات صلة بشكل اكثر فعالية"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Working Breadcrumbs (Priority: P1)

Users should be able to navigate the website hierarchy using breadcrumbs on the single post page, allowing them to easily return to the category or homepage.

**Why this priority**: Breadcrumbs are crucial for site navigation and SEO. If they don't work, users might get lost or leave the site.

**Independent Test**: Can be fully tested by clicking on any breadcrumb link and verifying it redirects to the correct page.

**Acceptance Scenarios**:

1. **Given** a user is viewing a single post, **When** they look at the top of the content, **Then** they should see a correct breadcrumb trail (Home > Category > Post Title).
2. **Given** a user clicks on the category link in the breadcrumb, **When** the page loads, **Then** they should be taken to the category archive page.

---

### User Story 2 - Author Link and Accurate Reading Time (Priority: P2)

Users should be able to click on the author's name to view all posts by that author. They should also see an accurate estimated reading time that reflects the length of the post content.

**Why this priority**: This improves user engagement by allowing them to discover more content from authors they like, and manages their expectations regarding the time needed to read the post.

**Independent Test**: Can be tested by verifying the author name is clickable and links to an author archive, and by verifying the reading time calculation seems proportional to the word count.

**Acceptance Scenarios**:

1. **Given** a user is viewing a single post, **When** they look at the author metadata, **Then** the author name should be a clickable link.
2. **Given** a user clicks the author link, **When** the page loads, **Then** they should see the author's archive page with their posts.
3. **Given** a user views a post, **When** they check the reading time, **Then** it should accurately reflect the length of the content (e.g., calculating based on average reading speed).

---

### User Story 3 - In-Content Advertisements (Priority: P1)

Users should see advertisements automatically placed within the article content at regular intervals (e.g., every 3 paragraphs) without disrupting the reading flow too abruptly.

**Why this priority**: This is a direct revenue-generating feature that needs to be implemented correctly to maximize earnings without ruining the user experience.

**Independent Test**: Can be fully tested by viewing a post with more than 3 paragraphs and verifying an ad appears in the correct position.

**Acceptance Scenarios**:

1. **Given** a post with more than 3 paragraphs, **When** a user reads through the content, **Then** they should see an ad inserted after the 3rd paragraph.
2. **Given** a post with 6 paragraphs, **When** a user reads through the content, **Then** they should see ads inserted appropriately (e.g., after paragraph 3 and paragraph 6).

---

### User Story 4 - Effective Related Articles (Priority: P2)

Users should see highly relevant "Related Articles" at the end of the post, encouraging them to continue reading other content on the site.

**Why this priority**: This increases page views per session and keeps users on the site longer.

**Independent Test**: Can be tested by evaluating the relevance of the suggested articles to the current post's content or category.

**Acceptance Scenarios**:

1. **Given** a user reaches the end of a single post, **When** they look at the "Related Articles" section, **Then** they should see a list of articles that share the same category, tags, or topic.
2. **Given** a user clicks on a related article, **When** the page loads, **Then** they should be smoothly transitioned to the selected article.

### Edge Cases

- What happens if a post is too short (e.g., less than 3 paragraphs)? Ads should not be injected, or only one ad should be placed at the very end of the content.
- What happens if the post has no categories or tags? The related articles should fall back to recent posts or popular posts to ensure the section is never empty.
- What happens if the calculated reading time is less than 1 minute? It should gracefully handle it by displaying "1 min read" or a similar fallback message.
- What happens if the author has no archive page or is a guest author? The author name should gracefully degrade to being displayed as plain text instead of a broken link.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST display a functional breadcrumb navigation accurately reflecting the post's hierarchy.
- **FR-002**: The system MUST render the author's name as a hyperlinked text pointing to the corresponding author archive page.
- **FR-003**: The system MUST dynamically calculate and display an accurate estimated reading time based on the post's word count.
- **FR-004**: The system MUST automatically insert advertisement blocks within the post content at specified intervals (e.g., every 3 paragraphs).
- **FR-005**: The system MUST fetch and display a list of valid, highly-relevant related articles at the bottom of the post.

### Key Entities *(include if feature involves data)*

- **Single Post**: The main content entity, containing title, content, author, categories, tags, and calculated reading time.
- **Advertisement Block**: The ad script or generic HTML block to be injected into the content.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Breadcrumb links correctly resolve to their intended archive or home pages 100% of the time without 404 errors.
- **SC-002**: Author links click-through to valid author pages 100% of the time.
- **SC-003**: Reading time is accurately calculated based on a standard adult reading speed metric (e.g., ~200-250 words per minute) for all posts.
- **SC-004**: Ads are successfully injected into the content stream exactly at the configured paragraph intervals (e.g. every 3 paragraphs) without breaking HTML structure.
- **SC-005**: Click-through rate (CTR) on the "Related Articles" section increases compared to the previous implementation.
