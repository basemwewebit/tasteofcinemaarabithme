# Research: Website Development & UI Updates

**Branch**: `001-site-ui-updates` | **Date**: 2026-03-10

## R1: Smart Sticky Header — Best Practices

**Decision**: Implement a scroll-direction-aware sticky header using a JavaScript scroll listener that tracks `window.scrollY` delta between frames. Use CSS `transform: translateY(-100%)` for hide and `translateY(0)` for reveal with `transition` for smooth animation.

**Rationale**:
- `transform: translateY` is GPU-accelerated and does not trigger layout reflows, making it ideal for scroll-driven animations.
- Tracking scroll direction via `lastScrollY` comparison is lightweight and widely supported.
- The existing header already uses `sticky top-0` with `backdrop-blur-md`, so we only need to add a JS-driven class toggle and corresponding CSS transitions.
- A `scroll-threshold` (e.g., 80px) prevents the header from hiding on minor scroll movements.

**Alternatives Considered**:
- **CSS `position: sticky` only** — Cannot hide-on-scroll-down natively; rejected.
- **`IntersectionObserver` on a sentinel element** — More complex, less precise for direction detection; rejected.
- **`scroll-timeline` CSS** — Not widely supported (lacks Safari support as of 2026); rejected for now.

**Key Implementation Notes**:
- Add `data-scroll-state` attribute (`top`, `scrolled-up`, `scrolled-down`) to `<header>` for CSS targeting.
- Compact state: reduce `h-20` → `h-14`, slightly darken backdrop opacity.
- The `z-40` on the existing header is below the site loader's `z-[100]`, so no interference.
- Must use `passive: true` on scroll listener for performance.
- RTL layout is unaffected since vertical scrolling is direction-agnostic.

---

## R2: Thumbnail Visual Enhancement — CSS Approach

**Decision**: Use CSS-only enhancements on the existing card templates' thumbnail containers. Apply `box-shadow`, `border-radius`, and `transition` for resting state, with `:hover` / `:focus-within` states for scale and shadow deepening.

**Rationale**:
- CSS-only approach has zero JavaScript overhead and works on all browsers.
- The existing card templates (`card.php`, `card-wide.php`, `card-category.php`, `card-search.php`, `card-related.php`) already have `overflow-hidden`, `rounded-2xl`, and `group-hover:scale-105` on images.
- Enhancement is additive — we refine existing shadows and add new subtle effects.

**Alternatives Considered**:
- **JavaScript-based parallax/tilt effects** — Too heavy for dozens of cards on a page; rejected for performance reasons.
- **CSS `filter` effects (blur, brightness on hover)** — Can look gimmicky on editorial sites; rejected in favor of shadow/scale refinement.

**Key Implementation Notes**:
- Target the `<a>` wrapping the thumbnail (`.relative.block.aspect-video.overflow-hidden`) for shadow and scale effects.
- Dark mode adaptation: use `dark:shadow-[0_4px_15px_rgba(0,0,0,0.4)]` vs `shadow-[0_4px_15px_rgba(0,0,0,0.08)]` for light.
- Add a subtle gradient overlay on hover for a cinematic feel: `::after` pseudo-element with `linear-gradient(transparent 60%, rgba(0,0,0,0.3))`.
- `transition-smooth` is already a custom utility in the theme — leverage it.
- Ensure thumbnail placeholder/fallback uses a neutral gradient background as the card's `aspect-video` container.

---

## R3: ReCaptcha Removal — Complete Inventory

**Decision**: Remove all reCAPTCHA code from the codebase and clean up database options. The existing honeypot + rate limiting (5/hour/IP) + nonce verification provides adequate spam protection.

**Rationale**:
- The current reCAPTCHA implementation loads 2 external scripts (Google reCAPTCHA API + handler JS), adding ~100KB+ and a third-party dependency.
- The contact form already has three layers of protection that are sufficient for the site's traffic level.
- Removing reCAPTCHA simplifies the codebase by eliminating 3 PHP classes + 1 JS file + database options.

**Complete File Inventory** (files to delete):
1. `inc/recaptcha/class-recaptcha-admin.php` (150 lines — admin settings page)
2. `inc/recaptcha/class-recaptcha-hooks.php` (122 lines — script enqueue + auth form hooks)
3. `inc/recaptcha/class-recaptcha-verify.php` (76 lines — token verification)
4. `assets/js/recaptcha-handler.js` (39 lines — client-side form interceptor)
5. `test-recaptcha-settings.php` (test file at theme root)
6. `inc/recaptcha/` directory (empty after file removal)

**Code References to Update**:
1. `functions.php` lines 20-22: Remove 3 `require_once` entries for recaptcha classes.
2. `inc/contact-form.php` lines 38-43: Remove reCAPTCHA token verification block.

**Database Options to Delete** (one-time cleanup):
- `toc_recaptcha_site_key`
- `toc_recaptcha_secret_key`
- `toc_recaptcha_score_threshold`

**Alternatives Considered**:
- **Keep reCAPTCHA but make optional** — Contradicts spec requirement FR-009; rejected.
- **Replace with hCaptcha** — Spec explicitly requests removal, not replacement; rejected.

---

## R4: Homepage Modularization — WordPress Customizer Pattern

**Decision**: Use the WordPress Customizer API with a dedicated "Homepage Sections" panel containing per-section settings (enabled toggle, priority number, section-specific config). Render sections via individual template parts sorted by priority.

**Rationale**:
- The Customizer API is the WordPress-standard approach for theme options with live preview.
- The existing theme already uses the Customizer for Ads settings (`inc/loader-ads-customizer.php`), establishing a consistent pattern.
- Numeric priority fields (1–5) are native `number` controls — no custom JS needed.
- Each section maps to a self-contained template part in `template-parts/homepage/`.

**Alternatives Considered**:
- **Custom admin page with React UI** — Overengineered for 5 toggles; rejected.
- **ACF Flexible Content** — Adds plugin dependency; rejected per constitution (minimize dependencies).
- **wp_customize `sortable` control** — Non-standard, requires custom JS; rejected in favor of numeric priority.

**Section Registration Pattern**:
```
Panel: toc_homepage_sections
├── Section: toc_hp_hero       → template-parts/homepage/section-hero.php
├── Section: toc_hp_articles   → template-parts/homepage/section-articles.php
├── Section: toc_hp_categories → template-parts/homepage/section-categories.php
├── Section: toc_hp_banner     → template-parts/homepage/section-banner.php
└── Section: toc_hp_sidebar    → template-parts/homepage/section-sidebar.php (wraps existing sidebar)
```

**Per-Section Settings**:
- `_enabled` (boolean, default: true for hero/articles/sidebar, false for categories/banner)
- `_priority` (number 1-5, unique defaults: hero=1, articles=2, categories=3, banner=4, sidebar=5)
- `_title` (text, section heading override)
- Section-specific: `_posts_count` (articles), `_category_count` (categories), `_banner_image`/`_banner_url` (banner)

**Front-page.php Refactor**:
- Replace hardcoded template-part calls with a dynamic loop:
  1. Collect all sections with `_enabled = true`
  2. Sort by `_priority` ascending
  3. Loop and `get_template_part('template-parts/homepage/section-' . $slug)` for each
- Sidebar is treated as a section but rendered within the content layout container alongside the main content area when enabled.

**Key Implementation Notes**:
- Create `inc/homepage-customizer.php` for all Customizer registrations.
- The `front-page.php` refactor extracts the current hero, ad, and articles grid into separate template parts.
- Sidebar section requires special handling: it wraps within the main flex layout rather than being a full-width section.
- Fallback when all sections disabled: display a minimal "Welcome" message with site description.
