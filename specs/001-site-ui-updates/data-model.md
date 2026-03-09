# Data Model: Website Development & UI Updates

**Branch**: `001-site-ui-updates` | **Date**: 2026-03-10

## Overview

This feature set primarily modifies presentation logic and theme configuration. There are no new database tables or custom post types. Data changes are limited to:
1. **New Customizer settings** stored in `wp_options` via the Customizer API.
2. **Deletion of existing reCAPTCHA options** from `wp_options`.

---

## Entity: Homepage Section (Customizer Settings)

Each homepage section is represented by a set of Customizer settings stored in `wp_options`. There is no separate entity table — WordPress serializes Customizer settings as `theme_mod` values.

### Common Fields (per section)

| Field | Type | Storage Key Pattern | Default | Validation |
|-------|------|---------------------|---------|------------|
| Enabled | boolean | `toc_hp_{slug}_enabled` | varies | `rest_sanitize_boolean` |
| Priority | integer (1–5) | `toc_hp_{slug}_priority` | varies | `absint`, clamped 1–5 |
| Title | string | `toc_hp_{slug}_title` | varies | `sanitize_text_field` |

### Section-Specific Fields

**Hero Carousel** (`slug: hero`)
| Field | Default |
|-------|---------|
| enabled | `true` |
| priority | `1` |
| title | _(empty — hero has no visible title)_ |

**Latest Articles Grid** (`slug: articles`)
| Field | Default | Notes |
|-------|---------|-------|
| enabled | `true` | |
| priority | `2` | |
| title | `أحدث المقالات المضافة` | |
| posts_count | `6` | `absint`, min 2, max 12 |

**Category Highlights** (`slug: categories`)
| Field | Default | Notes |
|-------|---------|-------|
| enabled | `false` | New section, disabled by default |
| priority | `3` | |
| title | `تصفح حسب الفئة` | |
| category_count | `6` | `absint`, min 3, max 12 |

**Promotional Banner** (`slug: banner`)
| Field | Default | Notes |
|-------|---------|-------|
| enabled | `false` | New section, disabled by default |
| priority | `4` | |
| title | _(empty)_ | |
| banner_image | _(empty)_ | `esc_url_raw` |
| banner_url | _(empty)_ | `esc_url_raw`, target link |
| banner_text | _(empty)_ | `sanitize_text_field` |

**Sidebar** (`slug: sidebar`)
| Field | Default | Notes |
|-------|---------|-------|
| enabled | `true` | |
| priority | `5` | |
| title | _(not applicable)_ | Sidebar has no heading |

---

## Entity: Sticky Header State (CSS/JS — No Database)

The sticky header state is ephemeral and managed entirely in the browser. No data is persisted.

| State | CSS Class | Trigger |
|-------|-----------|---------|
| `top` | _(default — no extra class)_ | `scrollY ≤ threshold` |
| `scrolled-up` | `header--scrolled-up` | User scrolling upward, `scrollY > threshold` |
| `scrolled-down` | `header--scrolled-down` | User scrolling downward, `scrollY > threshold` |

---

## Entity: Thumbnail Visual Effects (CSS — No Database)

Visual effects are CSS-only. No data model changes.

---

## Deleted Data: reCAPTCHA Options

The following `wp_options` rows will be deleted during deployment:

| Option Name | Current Type | Action |
|-------------|-------------|--------|
| `toc_recaptcha_site_key` | string | DELETE |
| `toc_recaptcha_secret_key` | string | DELETE |
| `toc_recaptcha_score_threshold` | float | DELETE |

---

## Relationships

```
front-page.php
    └── reads Customizer settings (toc_hp_*_enabled, toc_hp_*_priority)
        └── sorts enabled sections by priority
            └── renders template-parts/homepage/section-{slug}.php for each

header.php
    └── JS scroll listener manages header state classes
        └── CSS transitions handle visual state changes

contact-form.php
    └── validates via honeypot + rate limiting + nonce (reCAPTCHA removed)
```

## State Transitions

### Homepage Section Lifecycle

```
Section Registered (Customizer)
    ├── enabled=true  → Rendered on front page at assigned priority
    └── enabled=false → Skipped entirely (no HTML output)

Priority Change → Re-sort on next page load (no cache invalidation needed — Customizer saves trigger theme_mod update)
```

### Sticky Header State Machine

```
[top] ─── scroll down past threshold ───→ [scrolled-down] (header hidden)
  ↑                                              │
  │                                    scroll up  │
  │                                              ↓
  └──── scroll to top ──────────────── [scrolled-up] (header visible, compact)
```
