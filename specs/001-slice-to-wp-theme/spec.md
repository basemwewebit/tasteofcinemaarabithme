# Feature Specification: Slice to WordPress Theme Conversion

**Feature Branch**: `001-slice-to-wp-theme`  
**Created**: 2026-03-03  
**Status**: Draft  
**Input**: User description: "تحويل السلايس إلى قالب ووردبريس مخصص مع التزام حرفي بالتصميم وتجهيز Google AdSense و Google Analytics باستخدام Secure Custom Fields"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Browse Homepage with Hero & Latest Articles (Priority: P1)

A visitor navigates to the "مذاق السينما" homepage and sees the exact design of the slice: a sticky header with logo, navigation links, search toggle, and dark/light mode toggle. Below the header, a large hero section showcases a featured (pinned) article with its category badge, title, excerpt, author name, and date overlaid on a full-width cinematic image. Below the hero, a responsive Google Ad banner appears. The main content area displays the latest articles in a two-column card grid (with the third card spanning full width in a horizontal layout), alongside a sticky sidebar containing a Google Ad slot and a "most read this week" widget. Infinite scroll loads more articles as the visitor reaches the bottom of the page. The footer displays site branding, section links, important links, and copyright information.

**Why this priority**: The homepage is the most visited page and first impression. It must perfectly replicate the slice design to establish brand identity and user engagement.

**Independent Test**: Can be tested by loading the homepage and verifying all visual elements, layout, interactions (dark mode, mobile menu, search overlay, infinite scroll), and ad placements match the original slice pixel-for-pixel.

**Acceptance Scenarios**:

1. **Given** the homepage is loaded, **When** the visitor sees the page, **Then** the header is sticky with backdrop blur, shows text logo "مذاقالسينما", navigation links (الرئيسية, المراجعات, القوائم, أخبار السينما), search icon, and theme toggle — matching the slice exactly.
2. **Given** the homepage is loaded, **When** the visitor scrolls down, **Then** the hero section displays a featured article (managed via SCF or sticky post) with category badge, title, excerpt, author, and date on a cinematic image with gradient overlay.
3. **Given** the homepage is loaded, **When** the visitor scrolls to the article grid, **Then** articles display in a 2-column grid on desktop with lazy-loaded images, category badges, titles, excerpts, author avatars, author names, and dates — exactly as in the slice.
4. **Given** the homepage is loaded on mobile, **When** the visitor taps the hamburger menu, **Then** an off-canvas mobile menu slides in from the right with navigation links and an ad container, matching the slice.
5. **Given** the homepage is loaded, **When** the visitor clicks the search icon, **Then** a full-screen search overlay appears with autofocus on the input field, matching the slice.
6. **Given** the homepage is loaded, **When** the visitor reaches the bottom of the articles, **Then** new articles are loaded via AJAX/infinite scroll with a loading spinner, stopping after reaching the end of available posts.
7. **Given** the homepage is loaded, **When** the visitor toggles dark mode, **Then** all colors, backgrounds, borders, and text switch to the dark theme palette defined in the slice.

---

### User Story 2 - Read a Single Article (Priority: P1)

A visitor clicks on an article from any listing page and arrives at the single post page. The page displays a reading progress bar at the top, the standard header, a breadcrumb trail, the article category badge, full title, author info with avatar and role, publication date, estimated reading time, font size controls (A+/A-), a featured image with caption, and the full article content with rich typography (headings, paragraphs, blockquotes, in-article ads). After the content, tags are displayed as clickable badges, and a bottom ad container appears. The sidebar includes a search widget, a vertical ad slot, and a related articles widget with thumbnails.

**Why this priority**: The single article page is the core content consumption experience and the primary revenue driver via in-content ad placements.

**Independent Test**: Can be tested by navigating to any single post and verifying all content elements, typography, reading progress bar, font size controls, ad slots, sidebar widgets, and design fidelity match the slice.

**Acceptance Scenarios**:

1. **Given** a single post is loaded, **When** the visitor scrolls, **Then** a golden progress bar at the top of the viewport shows reading progress percentage.
2. **Given** a single post is loaded, **When** the visitor views the article header, **Then** breadcrumb, category badge, title, author (avatar + name + role), date, and reading time are displayed exactly as in the slice.
3. **Given** a single post is loaded, **When** the visitor clicks A+ or A-, **Then** the article body font size increases or decreases within defined limits (14px–26px).
4. **Given** a single post is loaded, **When** the visitor reads the article content, **Then** the content features styled headings, blockquotes with right border in gold, and in-article Google Ad slots automatically injected after every 3rd paragraph.
5. **Given** a single post is loaded, **When** the visitor sees the sidebar, **Then** it displays a search widget, a vertical banner ad (300×600), and related articles with thumbnails — all sticky on scroll.

---

### User Story 3 - Browse Category Archive (Priority: P2)

A visitor navigates to a category page (e.g., "الخيال العلمي") and sees a cinematic header with a background image, category badge, title, and description. Below the header, the article count is displayed. A responsive ad banner appears. Articles are displayed in a 3-column grid with cards identical to the homepage card design. At the bottom, numbered pagination with RTL-appropriate arrows allows navigation between pages.

**Why this priority**: Category pages are key navigation pathways and SEO landing pages that organize content for discovery.

**Independent Test**: Can be tested by clicking on any category and verifying the header design, article grid, pagination, and ad placements match the slice.

**Acceptance Scenarios**:

1. **Given** a category page is loaded, **When** the visitor views the page, **Then** a dark cinematic header displays the category name, description, and a badge reading "قسم التصنيفات" — matching the slice design.
2. **Given** a category page is loaded, **When** the visitor scrolls to articles, **Then** articles display in a 3-column responsive grid with cards matching the slice (thumbnail, category badge, title, excerpt).
3. **Given** a category page is loaded, **When** the visitor scrolls to the bottom, **Then** WordPress pagination displays as numbered circles with the current page highlighted in gold — matching the slice.

---

### User Story 4 - Search for Content (Priority: P2)

A visitor navigates to the search results page or uses the search overlay. The search page displays a dark header with the search query highlighted in gold, a search form with a rounded input, and a count of results. Results display in a 4-column poster-style grid with portrait aspect ratios (3:4), showing the article thumbnail, a rating or category label, title, and date.

**Why this priority**: Search is essential for content discoverability and user engagement beyond browsing.

**Independent Test**: Can be tested by performing a search and verifying the results page header, search form, result grid layout, and card design match the slice.

**Acceptance Scenarios**:

1. **Given** the search page is loaded with a query, **When** the visitor sees the page, **Then** a dark header shows "نتائج البحث عن:" with the query in gold, a search form, and result count.
2. **Given** search results exist, **When** displayed, **Then** results show in a 4-column grid with poster-style portrait cards (3:4 aspect ratio), matching the slice.

---

### User Story 5 - View Author Profile (Priority: P2)

A visitor clicks on an author name and arrives at the author page. The page displays a large avatar, author name, biography, statistics (number of reviews and lists), social links, and a grid of articles by that author with pagination.

**Why this priority**: Author pages build contributor credibility and enable visitors to discover more content from writers they enjoy.

**Independent Test**: Can be tested by navigating to any author page and verifying the profile header, statistics, article grid, and pagination match the slice.

**Acceptance Scenarios**:

1. **Given** an author page is loaded, **When** the visitor sees the profile, **Then** a large circular avatar, author name, bio, stats (reviews count, lists count), and social links display exactly as in the slice.
2. **Given** an author page is loaded, **When** the visitor scrolls, **Then** the author's articles display in a 3-column grid with pagination, matching the slice.

---

### User Story 6 - Contact the Team (Priority: P3)

A visitor navigates to the "اتصل بنا" page and sees a dark header with title and description. A contact form (name, email, subject, message) is displayed in a 2/3 column layout with a sidebar showing contact information (email, address) and social links.

**Why this priority**: The contact page enables visitor engagement but is lower priority than content consumption pages.

**Independent Test**: Can be tested by navigating to the contact page, filling out the form, submitting it, and verifying the layout matches the slice.

**Acceptance Scenarios**:

1. **Given** the contact page is loaded, **When** the visitor views the form, **Then** it displays name, email, subject, and message fields with the exact styling from the slice.
2. **Given** the contact page form is filled and submitted, **When** the submission is processed, **Then** the visitor receives confirmation and the message is delivered to the site administrator.

---

### User Story 7 - View Privacy Policy (Priority: P3)

A visitor navigates to the privacy policy page and sees a centered title, last update date, and structured content with section headings highlighted by gold left (right in RTL) borders. A call-to-action at the bottom links to the contact page.

**Why this priority**: Legal/compliance page required for Google AdSense approval and privacy compliance.

**Independent Test**: Can be tested by navigating to the privacy page and verifying the content layout and styling match the slice.

**Acceptance Scenarios**:

1. **Given** the privacy page is loaded, **When** the visitor reads it, **Then** the page displays structured sections with gold-bordered headings, prose content, and a CTA box linking to the contact page.

---

### User Story 8 - 404 Error Page (Priority: P3)

A visitor navigates to a non-existent URL and sees a cinematic 404 page with a film-themed design: a grayscale background image with overlay, film reel icon with pulse animation, large "404" text with gradient, a humor-infused Arabic message ("عذراً، يبدو أن المخرج قد استغنى عن هذا المشهد!"), and a prominent gold CTA button to return to the homepage.

**Why this priority**: Error pages are important for user experience but visitors don't intentionally visit them.

**Independent Test**: Can be tested by navigating to a non-existent URL and verifying the 404 page design matches the slice.

**Acceptance Scenarios**:

1. **Given** a 404 error occurs, **When** the page loads, **Then** the visitor sees the cinematic 404 design with film reel icon, gradient text, humor message, and a gold CTA button.

---

### User Story 9 - Google AdSense Integration (Priority: P1)

The site administrator can configure Google AdSense by entering the publisher ID and ad slot IDs through the WordPress Customizer or theme options. Ads are rendered in the exact placements defined in the slice: responsive horizontal banner after hero (homepage), sidebar square ad (homepage), mobile menu ad, in-article ad (single post), bottom-of-article ad (single post), vertical sidebar banner (single post), and horizontal responsive ads on category and search pages. Ad containers render correctly without breaking the layout, and display placeholder styling when no ad code is configured.

**Why this priority**: Ad revenue is a primary business objective and ads must be placed precisely as designed in the slice.

**Independent Test**: Can be tested by configuring AdSense IDs in the admin and verifying ads render in all specified placements without layout issues.

**Acceptance Scenarios**:

1. **Given** the admin has entered AdSense credentials, **When** the site loads, **Then** Google AdSense scripts load and ads render in all designated placements matching the slice.
2. **Given** no AdSense credentials are configured, **When** the site loads, **Then** ad containers display graceful placeholder styling without layout shifts.

---

### User Story 10 - Google Analytics Integration (Priority: P1)

The site administrator can configure Google Analytics by entering the Measurement ID (e.g., G-XXXXXXXXXX) through the WordPress Customizer or theme options. The GA4 script loads on every page for tracking visitor behavior, pageviews, and events.

**Why this priority**: Analytics tracking is essential for understanding audience behavior and measuring content performance from day one.

**Independent Test**: Can be tested by configuring the GA4 ID and verifying the tracking script is present in the page source and sending data to Google Analytics.

**Acceptance Scenarios**:

1. **Given** the admin has entered a GA4 Measurement ID, **When** any page loads, **Then** the Google Analytics tracking script is included in the <head> and begins tracking.
2. **Given** no GA4 ID is configured, **When** any page loads, **Then** no analytics script is loaded and no errors occur.

---

### User Story 11 - Dark/Light Mode Persistence (Priority: P2)

A visitor toggles between dark and light mode using the theme toggle button in the header. The preference is saved to localStorage and persists across page navigation and browser sessions. The default mode respects the visitor's system preference (prefers-color-scheme).

**Why this priority**: Theme preference persistence is an important UX feature that prevents jarring theme flashes on page load.

**Independent Test**: Can be tested by toggling dark mode, navigating to another page, and verifying the theme persists.

**Acceptance Scenarios**:

1. **Given** a visitor is on any page, **When** they click the theme toggle, **Then** the icon switches and the entire site theme changes instantly — matching the slice behavior.
2. **Given** a visitor has set dark mode, **When** they navigate to another page or return later, **Then** dark mode is preserved.

---

### Edge Cases

- What happens when a category has no articles? → Display a message "لا توجد مقالات في هذا التصنيف" with encouragement to browse other categories.
- What happens when search returns no results? → Display a message "لم يتم العثور على نتائج" with suggestion to try different keywords.
- What happens when the hero/featured article has no featured image? → Display a fallback gradient or placeholder image maintaining the hero section dimensions.
- How does the infinite scroll behave when all posts are loaded? → Display "تم الوصول إلى نهاية المقالات" message and stop making requests.
- What happens with very long article titles? → Titles are clamped (line-clamp-2 on cards) as defined in the slice CSS.
- How does the contact form handle validation errors? → Display inline validation errors with red styling for required fields.
- What happens when JavaScript is disabled? → The site remains browsable with basic functionality; infinite scroll gracefully degrades to standard pagination.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Theme MUST replicate all 8 slice page templates exactly (index, single, category, search, author, contact, privacy, 404) with pixel-perfect design fidelity.
- **FR-002**: Theme MUST use Tailwind CSS (compiled/built) with the exact color palette, typography (IBM Plex Sans Arabic), and spacing defined in the slice.
- **FR-003**: Theme MUST implement dark/light mode toggle with localStorage persistence and system preference detection, using the exact icon swap and color transitions from the slice.
- **FR-004**: Theme MUST implement a responsive, off-canvas mobile menu sliding from the right side with overlay, matching the slice animation and content.
- **FR-005**: Theme MUST implement a full-screen search overlay with autofocus and smooth fade animation, matching the slice.
- **FR-006**: Theme MUST implement AJAX-based infinite scroll on the homepage that loads new article cards dynamically, with a loading spinner and end-of-content message.
- **FR-007**: Theme MUST implement reading progress bar on single post pages — a gold bar at the top of the viewport that fills based on scroll position.
- **FR-008**: Theme MUST implement font size controls (A+/A-) on single post pages that adjust article body text between 14px and 26px.
- **FR-009**: Theme MUST use Secure Custom Fields (SCF) for managing dynamic content such as: hero/featured article selection, author social links, author role/title, contact page information, and ad configuration.
- **FR-010**: Theme MUST implement Google AdSense integration with configurable publisher ID and ad slot IDs, rendering ads in all 7 placements defined in the slice. In-article ads MUST be automatically injected after every 3rd paragraph in single post content, scaling with article length.
- **FR-011**: Theme MUST implement Google Analytics (GA4) integration with a configurable Measurement ID.
- **FR-012**: Theme MUST implement lazy loading for article card images using IntersectionObserver, with opacity transition on load — matching the slice behavior.
- **FR-013**: Theme MUST implement breadcrumb navigation on single post pages (الرئيسية / التصنيف / عنوان المقال).
- **FR-014**: Theme MUST implement the "most read this week" sidebar widget on the homepage with numbered entries (1, 2, 3) and view counts, using a lightweight custom post meta view counter that increments on each post visit — matching the slice design.
- **FR-015**: Theme MUST implement related articles sidebar widget on single post pages with thumbnails and dates, matching the slice.
- **FR-016**: Theme MUST implement WordPress standard pagination (numbered circles with active gold highlight) on category and author pages, matching the slice.
- **FR-017**: Theme MUST implement an RTL (right-to-left) layout as the primary direction, with appropriate LTR overrides for email fields and similar content.
- **FR-018**: Theme MUST implement the footer across all pages with consistent design: site logo, description, section links, important links, copyright, and credits — matching the slice.
- **FR-019**: Theme MUST implement tags display on single post pages as clickable rounded badges with hover effects, matching the slice.
- **FR-020**: Theme MUST implement the contact form with fields (name, email, subject, message), a honeypot hidden field for spam protection, WordPress nonce for CSRF protection, and handle submissions server-side.
- **FR-021**: Theme MUST implement proper Open Graph meta tags for social sharing on all pages.
- **FR-022**: Theme MUST implement custom scrollbar styling (thin gold-toned) as defined in the slice CSS.
- **FR-023**: Theme MUST implement the article content styling (article-content class) with proper paragraph spacing, heading sizes, and blockquote design — matching the slice.
- **FR-024**: Theme MUST register WordPress menus for header navigation and footer section links.
- **FR-025**: Theme MUST support featured images (post thumbnails) and generate appropriate image sizes for hero, card thumbnails, and sidebar thumbnails.

### Key Entities

- **Article (Post)**: The primary content entity. Attributes include title, content, excerpt, featured image, category, tags, author, publication date, and estimated reading time.
- **Category**: Content classification entity. Attributes include name, slug, description, and optional background image (via SCF).
- **Author (User)**: Content creator with extended profile. Attributes include display name, avatar, biography, role/title (via SCF), social links (via SCF), article count, and list count.
- **Ad Placement**: Configuration entity managed via theme options/SCF. Attributes include placement position (7 defined positions), AdSense slot ID, and enable/disable toggle.
- **Site Settings**: Global configuration entity via Customizer/SCF. Attributes include Google Analytics Measurement ID, AdSense Publisher ID, hero article selection, and social links.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All 8 page templates visually match the original slice design with less than 5% deviation when compared side-by-side at standard viewport widths (375px, 768px, 1280px, 1440px).
- **SC-002**: Site administrators can publish, edit, and manage content through the standard WordPress dashboard without custom coding knowledge.
- **SC-003**: Dark/light mode toggle responds within 300ms and theme preference persists across all page navigations and browser restarts.
- **SC-004**: Infinite scroll loads new articles within 2 seconds of the user reaching the scroll trigger point.
- **SC-005**: Google AdSense ads render correctly in all 7 defined placements without causing layout shifts (CLS score < 0.1).
- **SC-006**: Google Analytics tracking code loads on every page and correctly records pageview events when a valid Measurement ID is configured.
- **SC-007**: Pages load in under 3 seconds on a standard broadband connection (LCP < 3s) with proper asset optimization.
- **SC-008**: Contact form submissions are successfully delivered to the site administrator's email with all form field data intact.
- **SC-009**: All interactive features (mobile menu, search overlay, font controls, reading progress, infinite scroll) function correctly on Chrome, Firefox, Safari, and Edge.
- **SC-010**: The theme passes WordPress theme check standards and follows WordPress coding conventions for template hierarchy and hook usage.

## Assumptions

- The site uses Tailwind CSS compiled into a production stylesheet (not the CDN version used in the prototype slice).
- jQuery will be used for JavaScript functionality to match the slice's existing jQuery implementation, leveraging WordPress's bundled jQuery.
- Secure Custom Fields (SCF) plugin is installed and activated in the WordPress environment.
- The site domain is mazaqcinema.com with Arabic (ar) as the primary language.
- Content categories will follow the established taxonomy (maximum 15 categories) as per the project constitution.
- Post featured images are required for proper card and hero display.
- The contact form will use WordPress's built-in wp_mail() function or a compatible form handling approach.
- Google AdSense and Analytics accounts are already set up; the theme only needs to inject the appropriate scripts with configurable IDs.
- The "most read this week" widget uses a simple post view counter stored in post meta (incremented on each visit), queried weekly to rank popular articles.

## Clarifications

### Session 2026-03-03

- Q: What spam protection method should the contact form use? → A: Honeypot hidden field technique (no CAPTCHA, no user friction).
- Q: What data source should power the "most read this week" widget? → A: Simple post view counter using custom post meta incremented on each visit (no external plugin).
- Q: How should in-article ads be placed in single post content? → A: Auto-insert after every 3rd paragraph (scales with content length, no editor effort).
