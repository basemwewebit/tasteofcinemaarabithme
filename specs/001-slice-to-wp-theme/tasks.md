# Tasks: Slice to WordPress Theme Conversion

**Input**: Design documents from `/specs/001-slice-to-wp-theme/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Tests**: Not explicitly requested — test tasks omitted.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Theme root**: `/home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme/`
- All paths below are relative to the theme root

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Initialize the WordPress theme project structure, Tailwind CSS build pipeline, and npm dependencies.

- [ ] T001 Create theme declaration file with required WordPress headers (theme name "مذاق السينما", RTL, text domain) in `style.css`
- [ ] T002 Create `package.json` with Tailwind CSS 3.x, PostCSS, Autoprefixer, and cssnano dependencies, plus `dev` and `build` npm scripts
- [ ] T003 Create `tailwind.config.js` with darkMode 'class', custom colors (primary #D4AF37, secondary #E50914, dark palette), IBM Plex Sans Arabic font family, and content scanning for `**/*.php` files
- [ ] T004 Create Tailwind source file at `assets/css/src/style.css` with @tailwind directives, custom component styles, scrollbar styling, lazy image placeholders, ad container styling, and article-content typography — porting all custom CSS from `slice/assets/css/style.css`
- [ ] T005 Run `npm install` and `npm run build` to verify Tailwind CSS compiles to `assets/css/style.css`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core theme files that ALL pages depend on — header, footer, functions.php, menus, enqueues, SCF field groups, and helper functions. MUST complete before any user story.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [ ] T006 Create `functions.php` as the central include loader — require all files from `inc/` directory (theme-setup.php, enqueue.php, scf-fields.php, ads.php, analytics.php, post-views.php, contact-form.php, infinite-scroll.php, breadcrumb.php, helpers.php)
- [ ] T007 [P] Create `inc/theme-setup.php` — register `after_setup_theme` hook with: add_theme_support (post-thumbnails, title-tag, html5, custom-logo, automatic-feed-links), register 3 nav menus (primary-menu, footer-sections, footer-links), register 5 custom image sizes (hero-image 1600×700, card-thumbnail 800×500, card-wide-thumbnail 800×500, sidebar-thumbnail 150×150, search-poster 400×533), set content width, load theme text domain
- [ ] T008 [P] Create `inc/enqueue.php` — enqueue compiled Tailwind CSS from `assets/css/style.css`, Google Fonts (IBM Plex Sans Arabic 300,400,500,600,700), jQuery (WP bundled), and `assets/js/app.js` with jQuery dependency. Use `wp_enqueue_style` and `wp_enqueue_script` with proper versioning. Localize script with `wp_localize_script` passing `ajax_url`, `nonce`, and `home_url` to JavaScript
- [ ] T009 [P] Create `inc/helpers.php` — implement helper functions: `mazaq_reading_time($post_id)` to calculate Arabic reading time (word count / 200 WPM), `mazaq_relative_date($date)` for Arabic relative dates (أمس, منذ أسبوع, etc.), `mazaq_get_excerpt($length)` for custom excerpt length
- [ ] T010 [P] Create `inc/scf-fields.php` — register all 4 SCF field groups via `acf_add_local_field_group()` on `acf/init` hook: (1) Theme Options page with GA4 ID, AdSense publisher ID, 7 ad slot IDs, hero featured post selector, contact email, contact address, social links; (2) Author Profile fields on users: role_title (text), twitter_url (url), website_url (url); (3) Category Settings on category taxonomy: category_bg_image (image); (4) Register SCF options page under "Theme Options" menu via `acf_add_options_page()`
- [ ] T011 [P] Create `inc/ads.php` — implement `mazaq_render_ad($slot_name, $format)` helper function that outputs an ad container `<div>` with Google AdSense `<ins>` tag using slot ID from SCF options, or a placeholder `<div>` if no slot configured. Implement `mazaq_adsense_head_script()` hooked to `wp_head` to output the AdSense auto-ads `<script>` tag using publisher ID from SCF options. Implement `mazaq_inject_in_article_ads($content)` filter on `the_content` at priority 20 to auto-insert ad after every 3rd `</p>` paragraph
- [ ] T012 [P] Create `inc/analytics.php` — implement `mazaq_ga4_tracking_script()` hooked to `wp_head` that outputs the Google Analytics GA4 `gtag.js` script tag with the Measurement ID from SCF options. Output nothing if no ID configured. Escape the ID with `esc_attr()`
- [ ] T013 [P] Create `inc/post-views.php` — implement `mazaq_track_post_views($post_id)` hooked to `wp_head` for single posts only (exclude logged-in admins), incrementing `_post_views_count` post meta. Implement `mazaq_get_post_views($post_id)` getter. Implement `mazaq_get_most_read_posts($count)` that returns WP_Query of top posts ordered by `_post_views_count` meta_value_num
- [ ] T014 [P] Create `inc/breadcrumb.php` — implement `mazaq_breadcrumb()` function that outputs breadcrumb HTML matching the slice design: الرئيسية / التصنيف / عنوان المقال with proper links and separators
- [ ] T015 [P] Create `inc/contact-form.php` — implement contact form handler hooked to `template_redirect`: verify nonce with `wp_verify_nonce()`, check honeypot field is empty, sanitize inputs (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`), send email via `wp_mail()` to admin email, redirect back with success/error GET parameter (PRG pattern)
- [ ] T016 [P] Create `inc/infinite-scroll.php` — register `wp_ajax_nopriv_load_more_posts` and `wp_ajax_load_more_posts` AJAX actions. Handler accepts `page` POST parameter, runs `WP_Query` with `paged`, renders article cards via `template-parts/content/card.php` into output buffer, returns JSON with `html` and `has_more` boolean
- [ ] T017 Create `header.php` — convert `slice/index.html` header section to WordPress template: sticky header with backdrop blur, text logo "مذاقالسينما" linked to `home_url()`, `wp_nav_menu()` for primary-menu, search toggle button, dark/light mode toggle button, mobile hamburger button. Include `wp_head()` action. Add Open Graph meta tags via dynamic PHP. Include Google Fonts preconnect `<link>` tags. Set `<html dir="rtl" lang="ar">` with conditional `class="dark"` for initial theme state
- [ ] T018 Create `footer.php` — convert `slice/index.html` footer section to WordPress template: 4-column footer grid with site logo + description, `wp_nav_menu()` for footer-sections, `wp_nav_menu()` for footer-links, social links from SCF options. Include copyright year via `date('Y')`. Include `wp_footer()` action
- [ ] T019 Create `template-parts/navigation/mobile-menu.php` — off-canvas mobile menu sliding from the right with overlay, containing `wp_nav_menu()` for primary-menu and an ad container via `mazaq_render_ad('ad_slot_mobile_menu', 'responsive')`
- [ ] T020 Create `template-parts/navigation/search-overlay.php` — full-screen search overlay with `get_search_form()`, smooth fade animation, and autofocus on input. Include close button
- [ ] T021 Create `searchform.php` — custom WordPress search form with rounded input matching the slice design, RTL-appropriate placeholder text "ابحث عن فيلم أو مقال..."
- [ ] T022 Create `assets/js/app.js` — port and adapt JavaScript from `slice/assets/js/app.js` to work with WordPress: dark/light mode toggle with localStorage + OS preference detection (no FOUC), mobile menu open/close with body scroll lock, search overlay toggle with autofocus, lazy image loading via IntersectionObserver with opacity transition, infinite scroll AJAX calls using localized `mazaq_ajax.ajax_url` with nonce, reading progress bar calculation (single post only), font size A+/A- controls (14px–26px range, single post only), archive filter functionality. Use `jQuery` wrapper compatible with WordPress no-conflict mode
- [ ] T023 [P] Create all ad template parts: `template-parts/ads/ad-responsive.php` (responsive horizontal 728×90), `template-parts/ads/ad-square.php` (sidebar 300×250), `template-parts/ads/ad-vertical.php` (sidebar 300×600), `template-parts/ads/ad-in-article.php` (in-content ad), `template-parts/ads/ad-mobile-menu.php` (mobile menu ad) — each calling `mazaq_render_ad()` with appropriate slot name and format

**Checkpoint**: Foundation ready — all shared infrastructure, header/footer, SCF fields, helper functions, JavaScript, and ad templates in place. User story implementation can now begin.

---

## Phase 3: User Story 1 — Browse Homepage with Hero & Latest Articles (Priority: P1) 🎯 MVP

**Goal**: Deliver the complete homepage with hero section, article grid with infinite scroll, sidebar with most-read widget and ads — pixel-perfect match to `slice/index.html`.

**Independent Test**: Load the homepage and verify hero section, article cards, sidebar, infinite scroll, dark mode, mobile menu, search overlay, and ad placements match the slice at 375px, 768px, 1280px, 1440px viewports.

### Implementation for User Story 1

- [ ] T024 [US1] Create `template-parts/content/hero.php` — hero section with full-width cinematic featured image (from SCF `hero_featured_post` or latest sticky post), gradient overlay, category badge, title, excerpt, author name + avatar, and date. Use `hero-image` image size. Link to single post. Match `slice/index.html` hero section exactly
- [ ] T025 [P] [US1] Create `template-parts/content/card.php` — standard article card for 2-column grid: lazy-loaded thumbnail (`card-thumbnail` size), category badge with link, title (line-clamp-2), excerpt (line-clamp-2), author avatar + name + date. All elements linked appropriately. Match `slice/index.html` card design exactly
- [ ] T026 [P] [US1] Create `template-parts/content/card-wide.php` — wide horizontal article card for the 3rd position in the grid: horizontal layout with image on one side and content on the other. Use `card-wide-thumbnail` image size. Match `slice/index.html` third card layout exactly
- [ ] T027 [P] [US1] Create `template-parts/widgets/most-read.php` — "الأكثر قراءة هذا الأسبوع" sidebar widget displaying top 3 posts from `mazaq_get_most_read_posts(3)` with numbered entries (1, 2, 3), post title, view count, and date. Match `slice/index.html` sidebar widget exactly
- [ ] T028 [US1] Create `sidebar.php` — homepage sidebar containing: responsive ad via `template-parts/ads/ad-square.php` and most-read widget via `template-parts/widgets/most-read.php`. Apply sticky positioning on scroll. Match `slice/index.html` sidebar layout
- [ ] T029 [US1] Create `front-page.php` — main homepage template: `get_header()`, hero section via `get_template_part('template-parts/content/hero')`, responsive ad banner after hero via `template-parts/ads/ad-responsive.php`, main content area with 2-column article grid using WP_Query (first 2 cards standard, 3rd card wide, repeating pattern), `get_sidebar()`, infinite scroll trigger container with loading spinner, `get_footer()`. Pass initial page number for AJAX pagination
- [ ] T030 [US1] Create `index.php` — fallback template that mirrors `front-page.php` structure for non-static-front-page configurations

**Checkpoint**: Homepage fully functional — hero, article grid, infinite scroll, sidebar, ads, dark mode, mobile menu, search overlay all working. This is the MVP.

---

## Phase 4: User Story 2 — Read a Single Article (Priority: P1)

**Goal**: Deliver the complete single post page with reading progress bar, breadcrumbs, font size controls, content styling, in-article ads, tags, and sidebar with related articles — pixel-perfect match to `slice/single.html`.

**Independent Test**: Navigate to any single post and verify reading progress bar, breadcrumb, article header, font controls, content typography, in-article ads, tags, sidebar (search, ad, related articles) match the slice.

### Implementation for User Story 2

- [ ] T031 [P] [US2] Create `template-parts/common/reading-progress.php` — golden progress bar fixed at top of viewport, width calculated by JavaScript scroll position. Match `slice/single.html` progress bar styling
- [ ] T032 [P] [US2] Create `template-parts/common/font-controls.php` — A+ and A- buttons that adjust `.article-content` font size between 14px–26px. Match `slice/single.html` font control design
- [ ] T033 [P] [US2] Create `template-parts/widgets/sidebar-search.php` — sidebar search widget with `get_search_form()` styled to match `slice/single.html` sidebar search
- [ ] T034 [P] [US2] Create `template-parts/content/card-related.php` — related article item for sidebar: small thumbnail (`sidebar-thumbnail` size), title, and date. Match `slice/single.html` related articles widget design
- [ ] T035 [US2] Create `sidebar-single.php` — single post sidebar containing: search widget (`template-parts/widgets/sidebar-search.php`), vertical ad (`template-parts/ads/ad-vertical.php`), related articles section (query 4 posts from same category excluding current, render via `template-parts/content/card-related.php`). Apply sticky positioning. Match `slice/single.html` sidebar layout
- [ ] T036 [US2] Create `single.php` — single post template: `get_header()`, reading progress bar (`template-parts/common/reading-progress.php`), breadcrumb (`mazaq_breadcrumb()`), category badge, post title, author info (avatar, name, role from SCF `author_role_title`, date, `mazaq_reading_time()`), font size controls (`template-parts/common/font-controls.php`), featured image with caption, article content in `<div class="article-content">`, tags as rounded badges with links, bottom ad (`template-parts/ads/ad-responsive.php`), sidebar (`get_sidebar('single')`), `get_footer()`. In-article ads are auto-injected by the `the_content` filter from `inc/ads.php`

**Checkpoint**: Single post pages fully functional with all interactive features (progress bar, font controls), content styling, ads, and sidebar.

---

## Phase 5: User Story 9 — Google AdSense Integration (Priority: P1)

**Goal**: Ensure all 7 ad placements render correctly with configured AdSense credentials, and show graceful placeholders when unconfigured.

**Independent Test**: Configure AdSense IDs in Theme Options and verify ads render in homepage hero banner, homepage sidebar, mobile menu, single post in-article, single post bottom, single post sidebar vertical, and archive banner. Test with no credentials to verify placeholders.

### Implementation for User Story 9

- [ ] T037 [US9] Verify and refine all 7 ad template parts created in T023 — ensure each ad container has correct responsive sizing, proper `data-ad-slot` attributes, and matches the exact dimensions and spacing from the corresponding slice pages. Ensure placeholder styling (gray background, dashed border, "مساحة إعلانية" text) displays when no slot ID is configured
- [ ] T038 [US9] Verify `inc/ads.php` AdSense head script outputs correctly — test with a sample publisher ID, ensure script tag appears in `<head>`, ensure no output when unconfigured. Verify in-article ad injection at every 3rd paragraph with proper spacing and no injection for articles with fewer than 4 paragraphs

**Checkpoint**: All ad placements verified and functional.

---

## Phase 6: User Story 10 — Google Analytics Integration (Priority: P1)

**Goal**: GA4 tracking script loads on every page when configured, and produces no output when unconfigured.

**Independent Test**: Set GA4 Measurement ID in Theme Options and verify `gtag.js` script appears in page source `<head>`. Remove the ID and verify no script output and no console errors.

### Implementation for User Story 10

- [ ] T039 [US10] Verify and test `inc/analytics.php` GA4 script injection — ensure the complete `gtag('config', 'G-XXXXXXX')` script block outputs in `<head>` with proper escaping. Verify no output when Measurement ID is empty. Verify script loads on all page types (homepage, single, category, search, author, contact, privacy, 404)

**Checkpoint**: Analytics integration verified.

---

## Phase 7: User Story 3 — Browse Category Archive (Priority: P2)

**Goal**: Deliver category archive pages with cinematic header, article grid, and pagination — pixel-perfect match to `slice/category.html`.

**Independent Test**: Navigate to any category and verify the cinematic header, article count, ad banner, 3-column article grid, and numbered pagination match the slice.

### Implementation for User Story 3

- [ ] T040 [P] [US3] Create `template-parts/content/card-category.php` — category page article card: thumbnail, category badge, title (line-clamp-2), excerpt (line-clamp-3), author, date. Match `slice/category.html` card design (may be identical to `card.php` or slightly different layout)
- [ ] T041 [P] [US3] Create `template-parts/navigation/pagination.php` — WordPress numbered pagination using `paginate_links()` styled as gold circles with active page highlighted, RTL-appropriate arrows (← →). Match `slice/category.html` pagination design
- [ ] T042 [US3] Create `category.php` — category archive template: `get_header()`, cinematic dark header with category background image (from SCF `category_bg_image` or fallback gradient), "قسم التصنيفات" badge, category name, category description, article count (`$wp_query->found_posts`), responsive ad banner, 3-column article grid using the loop with `template-parts/content/card-category.php`, pagination via `template-parts/navigation/pagination.php`, `get_footer()`
- [ ] T043 [US3] Create `archive.php` — generic archive fallback template that mirrors `category.php` structure for tag and date archives

**Checkpoint**: Category and archive pages fully functional with header, grid, and pagination.

---

## Phase 8: User Story 4 — Search for Content (Priority: P2)

**Goal**: Deliver search results page with dark header, search form, and 4-column poster grid — pixel-perfect match to `slice/search.html`.

**Independent Test**: Perform a search query and verify the header, search form, result count, and 4-column poster-style result grid match the slice.

### Implementation for User Story 4

- [ ] T044 [P] [US4] Create `template-parts/content/card-search.php` — search result poster card: portrait aspect ratio (3:4) thumbnail (`search-poster` size), category/rating label overlay, title, date. Match `slice/search.html` poster card design
- [ ] T045 [US4] Create `search.php` — search results template: `get_header()`, dark header with "نتائج البحث عن:" + search query in gold (`get_search_query()`), search form (`get_search_form()`), result count (`$wp_query->found_posts`), 4-column responsive grid of results using `template-parts/content/card-search.php`, no-results message ("لم يتم العثور على نتائج") when empty, `get_footer()`

**Checkpoint**: Search results page fully functional.

---

## Phase 9: User Story 5 — View Author Profile (Priority: P2)

**Goal**: Deliver author archive pages with profile header, statistics, social links, and article grid with pagination — pixel-perfect match to `slice/author.html`.

**Independent Test**: Navigate to any author page and verify the avatar, name, bio, stats, social links, article grid, and pagination match the slice.

### Implementation for User Story 5

- [ ] T046 [P] [US5] Create `template-parts/content/card-author.php` — author page article card (may be identical to category card). Match `slice/author.html` card design
- [ ] T047 [US5] Create `author.php` — author archive template: `get_header()`, author profile section with large circular avatar (`get_avatar()`), display name, bio (`get_the_author_meta('description')`), role/title from SCF (`get_field('author_role_title', 'user_' . $author_id)`), stats (reviews count + lists count computed from `count_user_posts()`), social links from SCF (twitter, website), 3-column article grid of author's posts, pagination via `template-parts/navigation/pagination.php`, `get_footer()`

**Checkpoint**: Author pages fully functional with profile and articles.

---

## Phase 10: User Story 11 — Dark/Light Mode Persistence (Priority: P2)

**Goal**: Theme toggle responds instantly, persists across navigations and sessions, and respects OS preference on first visit.

**Independent Test**: Toggle dark mode, navigate to different pages, close and reopen the browser — verify theme persists. Test with OS dark mode preference on first visit.

### Implementation for User Story 11

- [ ] T048 [US11] Verify and refine dark mode implementation in `assets/js/app.js` and `header.php` — ensure inline `<script>` in `<head>` (before body render) sets the `.dark` class based on localStorage or OS `prefers-color-scheme` to prevent FOUC. Verify toggle button updates icon (sun ↔ moon) immediately. Verify all color transitions are smooth (300ms CSS transitions). Test persistence across homepage, single, category, search, author pages

**Checkpoint**: Dark/light mode fully functional with zero FOUC.

---

## Phase 11: User Story 6 — Contact the Team (Priority: P3)

**Goal**: Deliver the contact page with form, contact information sidebar, and server-side submission handling — pixel-perfect match to `slice/contact.html`.

**Independent Test**: Navigate to the contact page, fill out the form, submit it, and verify the layout matches the slice, the form validates correctly, honeypot blocks bots, and email is delivered to admin.

### Implementation for User Story 6

- [ ] T049 [US6] Create `page-contact.php` — contact page template: `get_header()`, dark header with "اتصل بنا" title and description, 2/3 column layout with contact form (name, email, subject, message fields + honeypot hidden field + nonce field via `wp_nonce_field()` + submit button), 1/3 column sidebar with contact info (email from SCF `contact_email`, address from SCF `contact_address`) and social links. Display success/error messages from GET parameter redirect. All form styling matches `slice/contact.html` exactly. `get_footer()`

**Checkpoint**: Contact page fully functional with form submission.

---

## Phase 12: User Story 7 — View Privacy Policy (Priority: P3)

**Goal**: Deliver the privacy policy page with structured content and gold-bordered headings — pixel-perfect match to `slice/privacy.html`.

**Independent Test**: Navigate to the privacy page and verify content layout, heading styles, and CTA box match the slice.

### Implementation for User Story 7

- [ ] T050 [US7] Create `page-privacy.php` — privacy policy page template: `get_header()`, centered title, last update date, WordPress `the_content()` output styled with `.article-content` class for consistent typography, gold-bordered section headings (border-right in RTL), CTA box at bottom linking to contact page. `get_footer()`

**Checkpoint**: Privacy policy page fully functional.

---

## Phase 13: User Story 8 — 404 Error Page (Priority: P3)

**Goal**: Deliver the cinematic 404 error page with film-themed design — pixel-perfect match to `slice/404.html`.

**Independent Test**: Navigate to a non-existent URL and verify the cinematic background, film reel icon, gradient 404 text, humor message, and gold CTA button match the slice.

### Implementation for User Story 8

- [ ] T051 [US8] Create `404.php` — custom 404 page: `get_header()`, full-viewport cinematic section with grayscale background image + dark overlay, centered content: SVG film reel icon with CSS pulse animation, large "404" text with gold gradient (`bg-gradient-to-l`), humor message "عذراً، يبدو أن المخرج قد استغنى عن هذا المشهد!", secondary message, gold CTA button linking to `home_url()` with text "العودة للصفحة الرئيسية". `get_footer()`. Match `slice/404.html` exactly

**Checkpoint**: 404 page fully functional.

---

## Phase 14: Polish & Cross-Cutting Concerns

**Purpose**: Final refinements affecting multiple user stories, SEO, performance, and validation.

- [ ] T052 [P] Add theme screenshot — create or capture `screenshot.png` (1200×900) showing the homepage design for WordPress admin theme selector
- [ ] T053 [P] Implement Open Graph meta tags in `header.php` — dynamic `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:locale` based on current page type (single post, category, author, homepage). Use `wp_get_attachment_image_src()` for featured images
- [ ] T054 [P] Add edge case handling across all templates — empty category message "لا توجد مقالات في هذا التصنيف", no search results message "لم يتم العثور على نتائج", hero fallback gradient when no featured image, infinite scroll end message "تم الوصول إلى نهاية المقالات"
- [ ] T055 [P] Verify RTL layout consistency across all pages — check text alignment, border directions (right borders in RTL become left visually), arrow directions in pagination, email field LTR override, and margin/padding directions
- [ ] T056 Run final Tailwind CSS production build (`npm run build`) and verify all utility classes used in PHP templates are included in the compiled output
- [ ] T057 Visual regression testing — compare each page template against its slice counterpart at 4 viewport widths (375px, 768px, 1280px, 1440px) and fix any deviations
- [ ] T058 Cross-browser testing — verify all interactive features (dark mode, mobile menu, search overlay, infinite scroll, progress bar, font controls) work on Chrome, Firefox, Safari, and Edge
- [ ] T059 Run `quickstart.md` verification checklist — walk through all 13 verification items and confirm each passes
- [ ] T060 Performance check — verify LCP < 3s, CLS < 0.1, and all images use lazy loading. Check that AdSense does not cause layout shifts. Verify no render-blocking scripts

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 completion — BLOCKS all user stories
- **User Stories (Phases 3–13)**: All depend on Phase 2 completion
  - P1 stories (US1, US2, US9, US10) should be completed first
  - P2 stories (US3, US4, US5, US11) can proceed after P1 or in parallel
  - P3 stories (US6, US7, US8) can proceed after P2 or in parallel
- **Polish (Phase 14)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (Homepage)**: After Phase 2 — No dependencies on other stories
- **US2 (Single Post)**: After Phase 2 — No dependencies on other stories
- **US9 (AdSense)**: After Phase 2 — Verifies ad templates created in Phase 2, best done after US1+US2 to test all placements
- **US10 (Analytics)**: After Phase 2 — Verifies analytics script, can run anytime after Phase 2
- **US3 (Category)**: After Phase 2 — Uses pagination template (created in US3, shared with US5)
- **US4 (Search)**: After Phase 2 — Independent
- **US5 (Author)**: After Phase 2 — Reuses pagination from US3 (if US3 done first) or creates it
- **US11 (Dark Mode)**: After Phase 2 — Verification/refinement of JS created in Phase 2
- **US6 (Contact)**: After Phase 2 — Uses contact form handler from Phase 2
- **US7 (Privacy)**: After Phase 2 — Independent
- **US8 (404)**: After Phase 2 — Independent

### Within Each User Story

- Template parts before page templates
- Models/data queries before rendering

### Parallel Opportunities

- All Phase 2 tasks marked [P] (T007–T016, T023) can run in parallel
- Within US1: T025, T026, T027 can run in parallel (independent template parts)
- Within US2: T031, T032, T033, T034 can run in parallel (independent template parts)
- US1 and US2 can run in parallel after Phase 2
- US3, US4, US5 can run in parallel with each other
- US6, US7, US8 can run in parallel with each other
- All Phase 14 tasks marked [P] can run in parallel

---

## Parallel Example: Phase 2 Foundation

```text
# These can all run simultaneously (different files):
Task T007: inc/theme-setup.php
Task T008: inc/enqueue.php
Task T009: inc/helpers.php
Task T010: inc/scf-fields.php
Task T011: inc/ads.php
Task T012: inc/analytics.php
Task T013: inc/post-views.php
Task T014: inc/breadcrumb.php
Task T015: inc/contact-form.php
Task T016: inc/infinite-scroll.php
Task T023: template-parts/ads/*.php
```

## Parallel Example: User Story 1

```text
# These template parts can run simultaneously:
Task T025: template-parts/content/card.php
Task T026: template-parts/content/card-wide.php
Task T027: template-parts/widgets/most-read.php

# Then assemble:
Task T028: sidebar.php (needs T027)
Task T029: front-page.php (needs T024, T025, T026, T028)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001–T005)
2. Complete Phase 2: Foundational (T006–T023)
3. Complete Phase 3: User Story 1 — Homepage (T024–T030)
4. **STOP and VALIDATE**: Test homepage independently
5. Deploy if ready — homepage is functional with all interactive features

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. US1 (Homepage) → Test → **MVP!**
3. US2 (Single Post) → Test → Articles readable
4. US9 + US10 (AdSense + Analytics) → Test → Revenue + tracking active
5. US3 (Category) + US4 (Search) + US5 (Author) → Test → Full navigation
6. US11 (Dark Mode) → Test → UX polished
7. US6 (Contact) + US7 (Privacy) + US8 (404) → Test → All pages complete
8. Polish → Final validation → **Complete!**

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- All template parts use `get_template_part()` for WordPress standard loading
- All output must use `esc_html()`, `esc_attr()`, `esc_url()` for proper escaping
- All inputs must use `sanitize_text_field()`, `sanitize_email()` for proper sanitization
