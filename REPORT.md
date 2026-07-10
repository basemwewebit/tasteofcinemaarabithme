# REPORT.md — Codebase Architecture & Quality Review

**Project:** مذاق السينما (Mazaq Cinema) — "Taste of Cinema Arabic"
**Type:** Classic (non-block) WordPress theme, RTL Arabic film magazine
**Version:** 1.0.55 · Requires WP 6.4+ · Requires PHP 8.1 · Text domain `mazaq`
**Reviewed at commit:** `05df9f3` (main)

---

## 1. Repository Structure

The theme follows a **modular classic-theme** layout: the standard WordPress template hierarchy at the root, all business logic pushed into single-responsibility modules under `inc/`, and presentation fragments under `template-parts/`. `functions.php` is a thin bootstrap — it does nothing but a manifest-driven `require` loop plus a handful of small filters.

```
tasteofcinemaarabithme/
├── functions.php            # Bootstrap: require-loop over inc/ manifest + small filters
├── style.css                # Theme header + a few hand-written keyframes (Tailwind is the real CSS)
│
├── {front-page,single,archive,category,tag,author,search,404,index}.php   # Template hierarchy
├── page-{about,contact,films,privacy,ad-support}.php                       # Page templates
├── {header,footer,sidebar,sidebar-single,searchform}.php                   # Chrome
│
├── inc/                     # ── Business logic, one concern per file ──
│   ├── theme-setup.php          # add_theme_support, menus, image sizes
│   ├── enqueue.php              # Context-aware, conditional asset loading
│   ├── scf-fields.php           # SCF/ACF field registration (film_* metadata, ad slots)
│   ├── schema-film.php          # Yoast @graph extension → Movie/Review/ItemList JSON-LD
│   ├── helpers.php              # Reading time, dates, hero selection, AJAX search/newsletter
│   ├── ads.php                  # AdSense slot rendering + head script
│   ├── infinite-scroll.php      # AJAX load-more
│   ├── breadcrumb.php · analytics.php · post-views.php · contact-form.php
│   ├── browser-notifications.php    # ⚠ 1,675 lines — Web Push subsystem (VAPID, REST, admin)
│   ├── random-film-popup.php · content-rotation-settings.php
│   ├── admin-hero-daily.php · admin-social-reminder.php · admin-toc-missing-widget.php
│   ├── toc-source-monitor.php       # Monitors an upstream TOC source for missing posts
│   ├── post-types/contact-message.php
│   └── recaptcha/                   # reCAPTCHA Enterprise: admin / verify / hooks (class-based)
│
├── template-parts/          # ── Presentational fragments ──
│   ├── content/   (cards, hero, film-infobox, series-nav, author-box …)
│   ├── ads/       (ad-vertical, ad-in-article, ad-grid, ad-404 …)
│   ├── navigation/ (mobile-menu, search-overlay, pagination)
│   └── common/    (reading-progress, font-controls, listicle-toc, newsletter …)
│
├── assets/
│   ├── css/src/style.css     # Tailwind source (5,486 lines) → css/style.css (built, minified)
│   ├── js/  app.js, app-single.js, app-archive.js, app-notifications.js, app-adblock.js …
│   └── fonts/                # Self-hosted woff2 (IBM Plex Sans Arabic, Tajawal, Amiri)
│
├── composer.json            # google/cloud-recaptcha-enterprise, minishlink/web-push
├── package.json             # Tailwind build pipeline + @fontsource + impeccable(!)
│
├── PRODUCT.md · DESIGN.md · REDESIGN-PLAN.md   # Living design/spec docs
└── .claude/ · .agents/ · .impeccable/          # ⚠ ~8 MB of AI-tooling committed into the theme
```

### Architectural patterns

- **Modular monolith via a require manifest.** `functions.php` holds an ordered `$mazaq_includes` array; each file self-registers its hooks on include. Adding a feature = drop a file in `inc/`, add one manifest line. Clean, discoverable, and order-explicit.
- **Hook-oriented, procedural-first.** The vast majority of logic is namespaced-by-prefix procedural functions attached to WordPress actions/filters. The only OO in the codebase is the reCAPTCHA subsystem (`TOC_Recaptcha_*` classes), which genuinely benefits from it (shared state, admin/verify/hooks separation).
- **Template-part composition.** Templates stay declarative; `get_template_part()` pulls in reusable card/ad/nav fragments. Good separation of markup from query logic.
- **Progressive enhancement on the front end.** JS is split per page-context and conditionally enqueued (`enqueue.php` only loads `app-single.js` on singular posts, `app-archive.js` on the front page, adblock/notification bundles only when the relevant feature is configured). This is the strongest single quality signal in the repo.

---

## 2. Overall Purpose & Functionality

Mazaq Cinema is an **Arabic-language cinema magazine** whose core content is film reviews, ranked "listicles" (top-N lists), and editorial criticism. The theme's job is to make that content feel like a credible film publication rather than a generic WP blog. Core capabilities:

| Domain | What it does |
|---|---|
| **Editorial film metadata** | SCF/ACF fields (`film_title`, `film_year`, `film_director`, `film_rating`) render as a poster-style **infobox** with a visible ⭐ star rating (`template-parts/content/film-infobox.php`, `helpers.php:mazaq_film_rating_stars`). |
| **Structured data / SEO** | `schema-film.php` *extends* Yoast's existing `@graph` (never competes with it) with `Movie`, `Review`, and `ItemList` nodes, making reviews eligible for star rich-results and listicles self-describing to search engines — all from data editors already enter. |
| **Discovery & engagement** | Hero carousel with daily auto-rotation, AJAX infinite scroll, live search suggestions, "random film" popup, most-read widget, reading progress + reading-time, adjustable article font size. |
| **Growth surfaces** | AdSense integration with anti-CLS reservations + an adblock-detection "please support us" prompt; a self-hosted **Web Push** notification system (VAPID, `minishlink/web-push`); newsletter capture stored as private `contact_message` posts. |
| **Trust / anti-spam** | Contact form protected by **reCAPTCHA Enterprise** + honeypot + nonce; the same reCAPTCHA gate can be hooked onto login. |
| **Editorial ops (admin)** | Dashboard widgets: daily hero picker, social-reminder scheduler, and a "TOC missing-posts" monitor that reconciles against an upstream source. |
| **Localization** | Deep RTL support, self-hosted Arabic fonts, Arabic-Indic digit normalization, and **Levantine month-name** substitution on front-end dates (يناير → كانون الثاني). |

---

## 3. Coding Conventions

**Consistent and above-average for a WordPress theme:**

- `declare(strict_types=1);` at the top of essentially every modern module.
- **Typed signatures** throughout (`function mazaq_reading_time(int $post_id): string`), return types, and typed array-shape PHPDoc (`@return array{value:int|float,best:int|float,...}`).
- Disciplined output escaping (`esc_html`, `esc_attr`, `esc_url`) and input sanitization (`sanitize_text_field`, `wp_unslash`) at the boundaries.
- All user-facing strings run through `__()` / `_n()` on the `mazaq` text domain — no hardcoded UI copy.
- Descriptive, intent-revealing PHPDoc on newer modules (`schema-film.php` is exemplary — its header explains *why* the module exists and how it degrades if Yoast is absent).

**Inconsistencies (technical debt):**

- **Two function prefixes coexist.** `toc_*` (older: `toc_breadcrumbs`, `toc_estimated_reading_time`, `toc_get_related_posts`) vs `mazaq_*` (current standard). The `toc_` functions in `functions.php` are legacy holdovers that predate the rename and were never migrated.
- **Duplicated logic across the two eras.** `toc_estimated_reading_time()` in `functions.php` and `mazaq_calculate_reading_time_minutes()` in `helpers.php` are two independent reading-time implementations with different word-counting regexes and different WPM constants (200 vs 180). Only one is authoritative; the other is dead weight that will drift.
- **`T00x:` task-ID comments** (`/* T001: ... */`, `T004`, `T007`) leak spec-kit task identifiers into shipped code. Harmless but meaningless to a future maintainer.
- Mixed CSS strategy: `style.css` contains a few hand-authored keyframes while the real styling is Tailwind-built into `assets/css/style.css`. Fine, but the split isn't documented.

---

## 4. Code Quality & "Code Smells"

Ranked by leverage:

1. **`browser-notifications.php` is a 1,675-line / 66 KB god-module.** VAPID key management, REST endpoints, subscription storage, admin settings UI, and send-scheduling all live in one file. It dwarfs every other module (next largest is 582 lines). This is the highest-risk file to change and the hardest to test. Should be decomposed like `recaptcha/` already is (`class-push-rest.php`, `class-push-admin.php`, `class-push-vapid.php`).

2. **Write-on-read side effect in a getter.** `mazaq_reading_time()` (`helpers.php:28`) performs `update_post_meta()` during a front-end GET when the cached value is missing. A getter that writes to the DB on cache-miss will fire an extra write for every post whose meta was cleared, on the first (often uncached-page) visitor. The `save_post` hook already backfills this meta — the read path should read-only and fall back to computing *without* persisting.

3. **Duplicated reading-time + duplicated related-posts logic.** As noted in §3, two reading-time implementations. `toc_get_related_posts()` is the only consumer of its transient key; the newer helpers own everything else. Consolidate to one implementation.

4. **Repeated inline `<script>` per ad slot.** `ads.php:mazaq_render_ad()` emits an inline `(adsbygoogle = window.adsbygoogle||[]).push({})` `<script>` block for *every* rendered slot. On a listicle with 6+ in-content ads that's 6+ inline scripts and 6+ CSP-hostile inline blocks. One delegated initializer that scans `[data-ad-ins]` on load would replace all of them.

5. **`filemtime()` called on every request for cache-busting.** `enqueue.php` stats each CSS/JS file (`filemtime`) on every page load to build the version string. Cheap individually, but it's ~6 `stat()` syscalls per request that a build-time manifest or the theme version would avoid.

6. **`switch_to_locale(get_option('WPLANG'))`** in `infinite-scroll.php:19`. `WPLANG` is a legacy option; `get_locale()` is the correct source. Works today because the option happens to be set, but it's a latent bug on a fresh install.

7. **~8 MB of AI tooling committed into a shipped theme.** `.claude/` (3.9 MB) and `.agents/` (3.9 MB) are near-identical copies of the `impeccable` skill (10k-line JS detectors, 135 markdown files, 98 `.mjs`, 60 `.csv`), and `impeccable` is *also* a runtime `dependency` in `package.json`. None of it is theme code. It bloats every deploy, backup, and `git clone`, and should be `.gitignore`d or removed. (It's dev-only tooling — it does not execute at runtime — so this is hygiene, not a security issue.)

---

## 5. Potential Issues & Vulnerabilities

### 🔴 High — Application-password restriction is silently nullified
`functions.php:138-148`:

```php
add_filter('wp_is_application_passwords_available', function ($available, $user = null) {
    if (!$user) return (bool) $available;
    return user_can($user, 'edit_posts');   // intent: only editors get app passwords
}, 10, 2);

add_filter('wp_is_application_passwords_available', '__return_true');   // ← undoes the line above
```

The second filter runs at the same/default priority and unconditionally returns `true`, so the capability check on the line above **never has any effect** — application passwords are enabled for *every* user, including subscribers. This looks like a leftover debugging line. **Remove the `__return_true` filter.** This is the single most important fix in the review.

### 🟠 Medium — reCAPTCHA local-bypass trusts `HTTP_HOST`
`recaptcha/class-recaptcha-verify.php:67` folds the attacker-controllable `$_SERVER['HTTP_HOST']` into the set of hosts that decide `is_local_environment()` (which *bypasses* reCAPTCHA entirely). The impact is bounded — the host must sanitize-match and then end in `.local`/`.test` or equal `localhost`/`127.0.0.1`/`::1` — so a production domain can't be spoofed into a bypass unless the deployment also answers to a `.test`/`.local` Host header. Still, the local-env decision should rely only on the server-configured `home_url()`/`site_url()`, never on the request's `Host` header. Drop the `HTTP_HOST` branch.

### 🟡 Low / hygiene
- **`test-recaptcha-settings.php`** — a committed stub (`// Just a dummy file to ensure no commands are needed`) sitting in the web root. Dead, directly URL-addressable, and confusing. Delete it.
- **Unauthenticated AJAX cost.** `wp_ajax_nopriv_*` search-suggestions and load-more run a fresh `WP_Query` per request with no rate limiting. Nonces prevent CSRF but not a scripted flood; consider a short transient cache on popular search terms / pages. (`no_found_rows => true` is correctly set, which helps.)
- **Newsletter e-mail existence oracle.** `mazaq_ajax_newsletter_signup()` returns a distinct "you're already subscribed" message, which lets an unauthenticated caller test whether an address is on the list. Low severity for a newsletter; return the same success copy for new-and-existing to close it.
- **`force_balance_tags` at priority 999 on `the_content`.** Robust, but it re-parses the full post body on every render after every other filter; fine for now, worth remembering if content-render time becomes a concern.

### ✅ What's done well, security-wise
Nonces on every state-changing AJAX/admin action; consistent `wp_unslash` + sanitize on all superglobal reads; no `eval`/`base64_decode`/`shell_exec` anywhere; output consistently escaped; reCAPTCHA host string is validated against an RFC-shaped allowlist before use; app-password *intent* was correct (just overridden). The boundary discipline here is genuinely good — the high-severity finding is a single stray line, not a systemic pattern.

---

### Summary scorecard

| Dimension | Grade | Note |
|---|---|---|
| Architecture / modularity | **A−** | Manifest-driven modules, conditional enqueue; one god-file drags it down |
| Conventions / typing | **A−** | strict_types + typed signatures; two-prefix drift |
| Security | **B** | Strong boundary discipline; one nullified control + one Host-trust bypass |
| Performance | **B+** | Per-context JS, transient caching, `no_found_rows`; write-on-read + per-request `filemtime` |
| Repo hygiene | **C** | ~8 MB of committed AI tooling + dead files |

**Top three actions:** (1) delete the `__return_true` app-password filter; (2) split `browser-notifications.php`; (3) remove `.claude/`/`.agents/`/`test-recaptcha-settings.php` from the tracked tree.
