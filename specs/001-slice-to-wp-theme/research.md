# Research: Slice to WordPress Theme Conversion

**Branch**: `001-slice-to-wp-theme` | **Date**: 2026-03-03

## Research Summary

All technical unknowns from the Technical Context have been resolved. No NEEDS CLARIFICATION items remain.

---

## R-001: Tailwind CSS Build Pipeline for WordPress

**Decision**: Use Tailwind CSS v3 with PostCSS CLI for production builds, compiled into a single `style.css` file.

**Rationale**: The slice uses Tailwind CSS CDN with inline config. For production WordPress, we need a compiled CSS file that includes only the classes used in templates. Tailwind v3's `content` scanning will purge unused classes automatically, resulting in a minimal production CSS file. The `darkMode: 'class'` strategy (or `'selector'` in v3.4+) matches the slice's jQuery-based `.dark` class toggling.

**Alternatives considered**:
- Tailwind CDN in production — Rejected: large file size (~300KB), blocks rendering, not recommended for production.
- Tailwind v4 — Rejected: too new, different config syntax, unnecessary migration risk.
- Pure CSS without Tailwind — Rejected: would require massive manual CSS rewrite of all slice classes.

**Implementation notes**:
- `tailwind.config.js` extends colors: `primary: '#D4AF37'`, `secondary: '#E50914'`, `dark: '#0F172A'`
- Font family: `sans: ['"IBM Plex Sans Arabic"', 'sans-serif']`
- `darkMode: 'class'` (compatible with Tailwind v3)
- Content scanning: `['./**/*.php']` to scan all PHP template files
- Build command: `npx tailwindcss -i ./assets/css/src/style.css -o ./assets/css/style.css --minify`

---

## R-002: Secure Custom Fields (SCF) Registration Pattern

**Decision**: Use `acf_add_local_field_group()` in PHP to register all field groups programmatically in `inc/scf-fields.php`, hooked to `acf/init`.

**Rationale**: Programmatic registration keeps field definitions in version control, makes the theme portable, and doesn't require database exports/imports. SCF (fork of ACF) uses the same API — `acf_add_local_field_group()` and `get_field()` / `the_field()` functions.

**Alternatives considered**:
- GUI-only field creation — Rejected: not version-controlled, lost on database reset.
- JSON sync — Considered but adds complexity; PHP registration is simpler for theme-bundled fields.

**Field groups needed**:
1. **Theme Options** (options page): GA4 Measurement ID, AdSense Publisher ID, AdSense slot IDs (7 slots), social links
2. **Author Profile** (user): role/title, Twitter URL, website URL
3. **Category Settings** (taxonomy: category): background image
4. **Hero Article** (options page): featured post selector for homepage hero

---

## R-003: Post View Counter Implementation

**Decision**: Use `update_post_meta()` / `get_post_meta()` with a custom meta key `_post_views_count` incremented on each single post visit via `wp_head` action, excluding logged-in admin users to avoid inflating counts.

**Rationale**: Lightweight, no external plugin dependency, stores data in standard `wp_postmeta` table. Querying "most read this week" requires a date-based approach — we'll use a separate meta key `_post_views_weekly` reset via a weekly wp_cron event, or simply query by total views as a simpler approach (most viewed overall, not strictly "this week").

**Alternatives considered**:
- WP-PostViews plugin — Rejected: external dependency, project aims for self-contained theme.
- Google Analytics API — Rejected: requires API credentials, complex setup, latency.
- Comment count proxy — Rejected: inaccurate popularity signal.

**Implementation notes**:
- Increment on `wp_head` in `single.php` context only
- Use `wp_cache` to avoid double-counting on page refresh within same session
- Query: `WP_Query` ordered by `meta_value_num` on `_post_views_count`, limited to 3 posts

---

## R-004: Infinite Scroll AJAX Architecture

**Decision**: Use WordPress `admin-ajax.php` with a custom action `load_more_posts` that accepts `page` parameter, returns rendered HTML of article cards, and uses `wp_ajax_nopriv_` for public access.

**Rationale**: Standard WordPress AJAX pattern, works with jQuery already in the slice, no REST API overhead needed for a simple paginated post fetch.

**Alternatives considered**:
- WP REST API — Considered but requires client-side templating; returning pre-rendered HTML from the server is simpler and guarantees slice-identical markup.
- Fetch API — Rejected: slice uses jQuery; keeping jQuery AJAX maintains consistency.

**Implementation notes**:
- PHP handler in `inc/infinite-scroll.php`, registered via `wp_ajax_nopriv_load_more_posts`
- Uses `WP_Query` with `paged` parameter
- Returns `template-parts/content/card.php` rendered HTML
- JavaScript sends `$.ajax()` POST request on scroll trigger
- Response includes HTML + `has_more` boolean flag to stop loading indicator

---

## R-005: In-Article Ad Auto-Injection

**Decision**: Use `the_content` filter to split post content by `</p>` tags and inject an ad template (`template-parts/ads/ad-in-article.php`) after every 3rd paragraph.

**Rationale**: Server-side content filtering ensures ads are placed consistently across all articles without editor involvement. The `the_content` filter is the standard WordPress approach for modifying output.

**Alternatives considered**:
- JavaScript DOM injection — Rejected: causes layout shifts (CLS), bad for Core Web Vitals.
- Gutenberg block/shortcode — Rejected: requires editor action per post, doesn't scale for 600+ imported articles.

**Implementation notes**:
- Filter registered in `inc/ads.php` at priority 20 (after wpautop)
- Does not inject if less than 4 paragraphs in the article
- Skips injection if AdSense credentials are not configured

---

## R-006: Contact Form Server-Side Handling

**Decision**: Custom PHP form handler with WordPress nonce verification, honeypot field check, input sanitization (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`), and `wp_mail()` for delivery.

**Rationale**: No external plugin dependency, minimal code, fully controlled within the theme. The honeypot approach (hidden field that bots fill but humans ignore) provides friction-free spam protection.

**Alternatives considered**:
- Contact Form 7 plugin — Rejected: external dependency, overkill for a single contact form.
- WPForms Lite — Rejected: adds admin UI complexity, not needed.

**Implementation notes**:
- Form `action` points to the same page (self-referencing)
- PHP handler in `inc/contact-form.php` processes on `template_redirect` hook
- Honeypot field: hidden `<input>` with CSS `display: none` — if filled, submission is silently rejected
- Success/error messages rendered via `$_GET` parameter redirect (PRG pattern)

---

## R-007: Google AdSense & Analytics Script Injection

**Decision**: Inject scripts via `wp_head` action based on theme options (SCF options page values). AdSense uses the standard auto-ads script + manual ad unit placement. GA4 uses the standard `gtag.js` snippet.

**Rationale**: Standard injection points, no plugin overhead, configurable via SCF options page in WP admin.

**Alternatives considered**:
- Google Site Kit plugin — Rejected: heavy, installs multiple dashboards, overkill for script injection.
- Tag Manager — Considered but adds a layer of indirection; direct gtag.js is simpler.

**Implementation notes**:
- `inc/analytics.php`: Hooks into `wp_head`, outputs GA4 script if Measurement ID is set
- `inc/ads.php`: Hooks into `wp_head` for the AdSense auto-ads script; individual ad slots rendered via template parts
- All script output uses `esc_attr()` for IDs to prevent XSS
