# Feature Specification: Website Development & UI Updates

**Feature Branch**: `001-site-ui-updates`  
**Created**: 2026-03-10  
**Status**: Draft  
**Input**: User description: "Thumbnail Enhancement, ReCaptcha Removal, Sticky Navigation, Homepage Modularization"

## Clarifications

### Session 2026-03-10

- Q: Should the sticky header always remain visible, or hide on scroll-down and reveal on scroll-up (smart sticky)? → A: Hide on scroll-down, reveal on scroll-up (smart sticky)
- Q: Which specific homepage sections should be pre-built as part of this feature? → A: 5 sections — Hero carousel, Latest articles grid, Category highlights, Promotional banner, Sidebar
- Q: Should stored reCAPTCHA option values in the database be deleted, or just the code that reads them? → A: Full cleanup — delete all reCAPTCHA-related option values from the database as part of the removal
- Q: How should homepage sections be re-ordered in the Customizer—drag-and-drop or numeric priority? → A: Numeric priority fields (1–5) for each section

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Sticky Navigation While Scrolling (Priority: P1)

A visitor lands on the site and begins scrolling down to read content. As they scroll past the header area, the navigation bar smoothly hides itself to maximize reading space. When the visitor scrolls back up—even slightly—the header smoothly slides back into view, giving instant access to navigation, search, and dark-mode toggle without needing to scroll to the top. The transition between states is smooth and subtle—the bar may slightly reduce in height and the background opacity may shift to signal the "scrolled" state.

**Why this priority**: Navigation is the single most-used interactive element. A sticky header eliminates the friction of scrolling back to the top, directly improving usability and reducing bounce rate across every single page on the site.

**Independent Test**: Can be fully tested by loading any page, scrolling down at least one viewport height, and confirming the header stays visible with a smooth animated transition. Delivers immediate usability value without any other feature being complete.

**Acceptance Scenarios**:

1. **Given** a visitor is at the top of any page, **When** they scroll down past a threshold (e.g., 80px), **Then** the header smoothly slides out of view to maximize content reading space, with the animation completing within 300ms.
2. **Given** the header is hidden due to scrolling down, **When** the visitor scrolls upward (even slightly), **Then** the header smoothly slides back into view in its compact "scrolled" state (reduced height and/or adjusted background opacity), with the animation completing within 300ms.
3. **Given** the header is in its "scrolled/sticky" state, **When** the visitor scrolls back to the very top of the page, **Then** the header gracefully returns to its original full-height appearance.
4. **Given** the visitor is on a mobile device, **When** they scroll down then up, **Then** the smart sticky header works identically to desktop, without causing layout shifts or obscuring content.
5. **Given** the page contains a site loader overlay, **When** the loader is present, **Then** the sticky header does not interfere with or appear above the loader.

---

### User Story 2 - Professional Thumbnail Visual Effects (Priority: P1)

A visitor browses the homepage or any archive/category page and sees the post thumbnail images presented with professional visual polish—subtle shadows, clean rounded borders, and smooth hover transitions (such as a slight zoom, overlay tint, or lift effect). This creates a modern, premium editorial feel that differentiates the site from generic blogs.

**Why this priority**: Thumbnails are the primary visual element visitors interact with on listing pages. Polished thumbnails directly influence perceived site quality and click-through rates, making this a high-impact visual improvement.

**Independent Test**: Can be fully tested by loading the homepage or any archive page, visually inspecting thumbnail presentation at rest, hovering over thumbnails, and confirming the transition effects are smooth. Delivers immediate visual polish.

**Acceptance Scenarios**:

1. **Given** a listing page (homepage, category, archive) is loaded, **When** the visitor views post cards, **Then** each thumbnail displays a subtle drop shadow, clean borders, and a refined resting state.
2. **Given** a visitor is viewing a post card, **When** they hover over the thumbnail, **Then** a smooth visual transition occurs (e.g., slight scale-up, shadow deepening, or subtle overlay) completing within 400ms.
3. **Given** a visitor is on a mobile/touch device, **When** they tap a post card, **Then** the visual enhancement does not cause layout shift or jank; the card remains visually polished without requiring hover.
4. **Given** the site is in dark mode, **When** viewing thumbnails, **Then** shadow and border styles adapt appropriately to the dark theme (e.g., darker shadows, adjusted border colors).

---

### User Story 3 - ReCaptcha Removal for Streamlined Forms (Priority: P2)

A site administrator wants to simplify the user experience on the contact form by removing the Google reCAPTCHA verification step. After this change, the contact form loads faster (no external Google scripts), submits without a reCAPTCHA challenge, and the admin panel no longer shows reCAPTCHA configuration settings. Existing anti-spam measures (honeypot field, rate limiting, nonce verification) remain active.

**Why this priority**: While important for streamlining UX and reducing page load weight, the existing honeypot + rate-limiting + nonce verification provides adequate spam protection. Removing reCAPTCHA is a cleanup that improves speed and simplicity but does not unlock new user-facing value.

**Independent Test**: Can be fully tested by submitting the contact form, verifying no reCAPTCHA scripts load on any page, confirming form submission succeeds without reCAPTCHA, and confirming the admin panel no longer contains reCAPTCHA settings.

**Acceptance Scenarios**:

1. **Given** a visitor loads the contact page, **When** the page finishes loading, **Then** no Google reCAPTCHA scripts or assets are loaded (verifiable via browser network tab).
2. **Given** a visitor fills out and submits the contact form, **When** they press "Send", **Then** the form processes successfully using remaining protections (honeypot, rate limiting, nonce) without any reCAPTCHA check.
3. **Given** an administrator navigates to the WordPress admin area, **When** they look for reCAPTCHA settings, **Then** no reCAPTCHA settings page or options exist.
4. **Given** the reCAPTCHA code is completely removed, **When** any page on the site is loaded, **Then** there are no references to reCAPTCHA in the HTML source, no reCAPTCHA-related scripts enqueued, and no PHP errors or warnings.

---

### User Story 4 - Modular Homepage Sections (Priority: P2)

A site administrator wants the ability to manage the homepage layout dynamically—toggling, reordering, or configuring five pre-built sections: (1) Hero carousel, (2) Latest articles grid, (3) Category highlights, (4) Promotional banner, and (5) Sidebar—from the WordPress Customizer without editing theme files. Each section is independently toggleable and configurable, allowing the homepage to evolve over time without developer intervention.

**Why this priority**: Modularization is a structural improvement that pays long-term dividends. While it does not immediately change the visitor experience, it empowers administrators to experiment with homepage layouts, test promotional sections, and adapt to seasonal content strategies independently.

**Independent Test**: Can be fully tested by toggling sections on/off in the Customizer, saving, and confirming the homepage reflects the changes. Demonstrates value by proving each section can be independently controlled.

**Acceptance Scenarios**:

1. **Given** an administrator opens the WordPress Customizer, **When** they navigate to the Homepage Sections panel, **Then** they see 5 sections (Hero carousel, Latest articles grid, Category highlights, Promotional banner, Sidebar), each with an enable/disable toggle.
2. **Given** an administrator disables a homepage section (e.g., the category highlights section), **When** they preview or publish the change, **Then** that section is completely absent from the rendered homepage—no empty containers, no placeholder space.
3. **Given** an administrator assigns numeric priority values (1–5) to sections and saves, **When** the homepage is loaded, **Then** the sections appear in ascending priority order.
4. **Given** the homepage has multiple sections enabled, **When** a visitor loads the page, **Then** all enabled sections render correctly with no layout breakage, consistent spacing, and proper responsive behavior.
5. **Given** a section has configurable options (e.g., number of posts to show, custom title), **When** the administrator updates those options, **Then** the section updates accordingly on the next page load.

---

### Edge Cases

- What happens when JavaScript is disabled? The sticky header should degrade gracefully to a static header with no broken layout.
- What happens when the homepage has no sections enabled? The page should still render cleanly with a fallback message or the main content area intact.
- How does the sticky header behave when the page content is shorter than the viewport? It should remain in its default (non-scrolled) state.
- What if a thumbnail image fails to load? The card should still look complete with a fallback background or placeholder styling.
- What happens to existing contact form submissions during the reCAPTCHA transition? Existing stored messages in `contact_message` post type are unaffected; only the validation path changes. All reCAPTCHA option values in the database are deleted as part of the cleanup.

## Requirements *(mandatory)*

### Functional Requirements

**Sticky Navigation**
- **FR-001**: The site header MUST use a smart-sticky behavior: it hides by sliding out of view when the user scrolls down, and slides back into view when the user scrolls up.
- **FR-002**: The header MUST transition between its default, scrolled-compact, and hidden states with smooth animations completing within 300ms.
- **FR-003**: The sticky header MUST work consistently across desktop and mobile devices without causing content to jump or layout to shift.
- **FR-004**: The sticky header MUST NOT visually interfere with the site loader overlay.

**Thumbnail Enhancement**
- **FR-005**: Post card thumbnails MUST display subtle drop shadows and clean, rounded borders in their resting state.
- **FR-006**: Post card thumbnails MUST exhibit a smooth hover transition effect (scale, shadow depth, or overlay) completing within 400ms.
- **FR-007**: Thumbnail visual effects MUST adapt appropriately between light and dark mode.
- **FR-008**: Thumbnail enhancements MUST apply consistently across homepage cards, archive cards, category cards, and wide cards.

**ReCaptcha Removal**
- **FR-009**: All reCAPTCHA-related server-side code (classes, hooks, verification logic) MUST be completely removed from the theme.
- **FR-010**: All reCAPTCHA-related client-side scripts and assets MUST be removed and no longer enqueued on any page.
- **FR-011**: The reCAPTCHA admin settings page and all stored reCAPTCHA options MUST be removed from the WordPress admin area.
- **FR-011a**: All reCAPTCHA-related option values stored in the database (site key, secret key, configuration settings) MUST be deleted as a one-time cleanup during deployment.
- **FR-012**: The contact form MUST continue to function using remaining protections: honeypot field, rate limiting, and nonce verification.
- **FR-013**: No PHP errors, warnings, or notices MUST result from the removal of reCAPTCHA code.

**Homepage Modularization**
- **FR-014**: The homepage MUST be composed of 5 independent, self-contained template sections: Hero carousel, Latest articles grid, Category highlights, Promotional banner, and Sidebar.
- **FR-015**: Each homepage section MUST be individually toggleable (show/hide) through the WordPress Customizer.
- **FR-016**: Sections MUST be re-orderable via numeric priority fields (1–5) in the Customizer; sections render in ascending priority order.
- **FR-017**: Disabled sections MUST leave zero visual footprint on the rendered page (no empty wrappers or blank space).
- **FR-018**: Each section MUST support basic configuration options (e.g., section title, number of items to display).
- **FR-019**: The homepage MUST render gracefully when all sections are disabled—displaying a minimal fallback.

### Key Entities

- **Homepage Section**: A self-contained, toggleable content block on the front page—characterized by a unique identifier, numeric display priority (1–5), enabled/disabled state, a title, and section-specific settings (e.g., post count, category filter). The initial set of 5 sections is: Hero carousel, Latest articles grid, Category highlights, Promotional banner, and Sidebar.
- **Post Card**: A visual unit displaying a post's thumbnail, title, excerpt, author, and date—enhanced with professional shadow, border, and hover effects.
- **Sticky Header**: The site's navigation bar in its fixed/scrolled state—characterized by a reduced height, adjusted background opacity, and smooth transition timing.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of site pages display a sticky header that remains visible during scrolling, with a smooth animated transition completing within 300ms.
- **SC-002**: All post card thumbnails across listing pages display professional visual effects (shadows, borders, hover transitions), verified across both light and dark modes.
- **SC-003**: Zero reCAPTCHA-related scripts, HTML elements, or admin settings exist anywhere on the live site after the removal is complete.
- **SC-004**: The contact form submission success rate remains at or above pre-change levels (no regression in legitimate form submissions).
- **SC-005**: An administrator can toggle, re-order, and configure all 5 pre-built homepage sections (Hero carousel, Latest articles grid, Category highlights, Promotional banner, Sidebar) via the Customizer without editing theme code.
- **SC-006**: Pages with reCAPTCHA previously loaded show a measurably reduced number of external script requests (at least 2 fewer requests).
- **SC-007**: No layout shifts (CLS ≤ 0.1) occur on any page due to the sticky header transition or thumbnail hover effects.
- **SC-008**: All features degrade gracefully when JavaScript is disabled—static header, no broken layouts, forms still submit.

## Assumptions

- The existing header structure (currently using `sticky top-0` with backdrop blur at `header.php` line 64) will be enhanced with JavaScript-driven scroll-state transitions rather than replaced entirely.
- The thumbnail visual effects will be implemented primarily through CSS, augmenting the existing card templates (`card.php`, `card-wide.php`) without restructuring their markup.
- The three existing reCAPTCHA files (`class-recaptcha-admin.php`, `class-recaptcha-hooks.php`, `class-recaptcha-verify.php`) plus their references in `functions.php` and `contact-form.php` constitute the complete set of reCAPTCHA code to remove.
- TailwindCSS is the project's CSS framework, so visual enhancements will use Tailwind utility classes and custom CSS properties where necessary.
- The WordPress Customizer API will be used for homepage section controls (not a custom admin page), aligning with WordPress best practices.
- The homepage currently has a hardcoded structure (hero → ad → articles grid with sidebar), which will be refactored into modular template-parts.
