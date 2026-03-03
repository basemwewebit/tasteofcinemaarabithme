# Implementation Plan: Slice to WordPress Theme Conversion

**Branch**: `001-slice-to-wp-theme` | **Date**: 2026-03-03 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-slice-to-wp-theme/spec.md`

## Summary

Convert 8 static HTML/CSS/JS slice files (index, single, category, search, author, contact, privacy, 404) into a fully functional custom WordPress theme with pixel-perfect design fidelity. The theme uses Tailwind CSS (compiled), IBM Plex Sans Arabic typography, jQuery for interactions, Secure Custom Fields (SCF) for dynamic content management, and includes Google AdSense (7 placements) and Google Analytics (GA4) integration. All interactive features from the slices — dark/light mode, infinite scroll, mobile menu, search overlay, reading progress bar, font size controls — are preserved exactly.

## Technical Context

**Language/Version**: PHP 8.1+, JavaScript (ES6+/jQuery), HTML5, CSS3  
**Primary Dependencies**: WordPress 6.4+, Tailwind CSS 3.x (compiled), jQuery (WP bundled), Secure Custom Fields (SCF) plugin  
**Storage**: WordPress MySQL database (standard wp_posts, wp_postmeta, wp_options, wp_users, wp_usermeta)  
**Testing**: Manual visual comparison against slices at 375px, 768px, 1280px, 1440px viewports; WordPress Theme Check plugin; browser cross-compatibility testing (Chrome, Firefox, Safari, Edge)  
**Target Platform**: WordPress 6.4+ on LAMP/LEMP Linux server  
**Project Type**: WordPress custom theme  
**Performance Goals**: LCP < 3s, CLS < 0.1, dark mode toggle < 300ms, infinite scroll load < 2s  
**Constraints**: RTL-first Arabic layout, pixel-perfect slice fidelity, max 15 categories (taxonomy constraint), no external CAPTCHA dependencies  
**Scale/Scope**: ~600+ scraped/translated articles, 15 categories max, single-language (Arabic)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| Quality | ✅ PASS | Theme follows WPCS, uses proper sanitization/escaping, coded to professional standards |
| Maintainability | ✅ PASS | Clean template hierarchy, modular template parts, well-organized asset pipeline |
| Transparency | ✅ PASS | All decisions documented in spec clarifications, research, and this plan |
| Reliability & Security | ✅ PASS | Nonces + honeypot for forms, proper output escaping, capability checks |
| Clear Architecture | ✅ PASS | Standard WordPress template hierarchy, no unnecessary abstractions |
| Editorial Integrity | ✅ PASS | Slice design preserves cinematic branding exactly, content managed via standard WP editor |
| Code Standards | ✅ PASS | WPCS compliance, proper hook usage, wp_enqueue for assets |
| Testing | ✅ PASS | Visual regression testing, Theme Check validation, cross-browser testing planned |
| Data Protection | ✅ PASS | No personal data collected beyond contact form (delivered via wp_mail, not stored), cookies only for theme preference |

**Gate Result**: ✅ ALL PASSED — Proceeding to Phase 0.

**Post-Phase 1 Re-check (2026-03-03)**: ✅ ALL STILL PASS — Data model uses only standard WordPress tables + SCF meta. No new external dependencies introduced. All field groups registered in version-controlled PHP. Architecture follows standard WP template hierarchy without over-engineering.

## Project Structure

### Documentation (this feature)

```text
specs/001-slice-to-wp-theme/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
# WordPress Theme Structure
style.css                    # Theme declaration (required by WP)
functions.php                # Theme setup, hooks, enqueues, SCF config
screenshot.png               # Theme screenshot for WP admin

# Template Files
index.php                    # Main fallback template
front-page.php               # Homepage (hero + infinite scroll)
single.php                   # Single post page
archive.php                  # Category/tag archive
category.php                 # Category archive (overrides archive.php)
search.php                   # Search results
author.php                   # Author profile + articles
page-contact.php             # Contact page template
page-privacy.php             # Privacy policy page template
404.php                      # 404 error page
header.php                   # Global header (sticky, nav, logo)
footer.php                   # Global footer
sidebar.php                  # Sidebar (homepage)
sidebar-single.php           # Sidebar (single post)
searchform.php               # Search form override

# Template Parts
template-parts/
├── content/
│   ├── hero.php             # Hero/featured article section
│   ├── card.php             # Standard article card (2-col grid)
│   ├── card-wide.php        # Wide article card (full-width horizontal)
│   ├── card-category.php    # Category page article card
│   ├── card-search.php      # Search result poster card (3:4)
│   ├── card-author.php      # Author page article card
│   └── card-related.php     # Related article sidebar item
├── navigation/
│   ├── mobile-menu.php      # Off-canvas mobile menu
│   ├── search-overlay.php   # Full-screen search overlay
│   ├── breadcrumb.php       # Breadcrumb navigation
│   └── pagination.php       # Numbered pagination
├── widgets/
│   ├── most-read.php        # "Most read this week" sidebar widget
│   ├── sidebar-search.php   # Sidebar search widget
│   └── sidebar-ad.php       # Sidebar ad container
├── ads/
│   ├── ad-responsive.php    # Responsive horizontal ad (728x90/320x50)
│   ├── ad-square.php        # Square sidebar ad (300x250)
│   ├── ad-vertical.php      # Vertical sidebar ad (300x600)
│   ├── ad-in-article.php    # In-article ad unit
│   └── ad-mobile-menu.php   # Mobile menu ad container
└── common/
    ├── reading-progress.php # Reading progress bar
    └── font-controls.php    # Font size A+/A- controls

# Assets (source)
assets/
├── css/
│   ├── src/
│   │   └── style.css        # Tailwind source with @apply directives
│   └── style.css            # Compiled production CSS
├── js/
│   └── app.js               # Main JavaScript (dark mode, menu, scroll, etc.)
└── images/                  # Theme static images (fallbacks, etc.)

# Configuration
tailwind.config.js           # Tailwind configuration matching slice
package.json                 # Node dependencies (Tailwind build)

# Includes (PHP helpers)
inc/
├── theme-setup.php          # add_theme_support, menus, image sizes
├── enqueue.php              # Script/style enqueuing
├── scf-fields.php           # SCF field group registrations
├── ads.php                  # AdSense helper functions
├── analytics.php            # GA4 script injection
├── post-views.php           # Post view counter logic
├── contact-form.php         # Contact form handler (honeypot + nonce)
├── infinite-scroll.php      # AJAX endpoint for infinite scroll
├── breadcrumb.php           # Breadcrumb generation helper
└── helpers.php              # Utility functions (reading time, etc.)
```

**Structure Decision**: Standard WordPress classic theme structure with `template-parts/` for reusable components, `inc/` for PHP includes/helpers, and `assets/` for CSS/JS. This follows WordPress best practices and keeps the theme maintainable. No unnecessary abstraction layers — direct template hierarchy usage.

## Complexity Tracking

> No constitution violations detected. No complexity justifications needed.
