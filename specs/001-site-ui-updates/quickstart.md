# Quickstart: Website Development & UI Updates

**Branch**: `001-site-ui-updates` | **Date**: 2026-03-10

## Prerequisites

- WordPress installation with the `tasteofcinemaarabithme` theme active
- Node.js and npm (for TailwindCSS compilation)
- PHP 8.0+ with `declare(strict_types=1)` support

## Setup

```bash
# Switch to feature branch
cd /home/basem/sites/tasteofcinemaarabi/wp-content/themes/tasteofcinemaarabithme
git checkout 001-site-ui-updates

# Install dependencies (if not already)
npm install

# Start Tailwind watcher
npm run dev
```

## Key Files to Modify

### Sticky Header
- `header.php` — Add scroll-state data attribute and compact-state CSS classes
- `assets/js/app.js` — Add scroll direction detection logic
- `assets/css/src/style.css` — Add header transition styles

### Thumbnail Enhancement
- `assets/css/src/style.css` — Add thumbnail shadow, border, and hover effect styles
- Card templates affected (CSS-only, no PHP changes expected):
  - `template-parts/content/card.php`
  - `template-parts/content/card-wide.php`
  - `template-parts/content/card-category.php`
  - `template-parts/content/card-search.php`
  - `template-parts/content/card-related.php`

### ReCaptcha Removal
- **Delete**: `inc/recaptcha/` directory (3 PHP files)
- **Delete**: `assets/js/recaptcha-handler.js`
- **Delete**: `test-recaptcha-settings.php`
- **Edit**: `functions.php` — Remove 3 require_once entries (lines 20-22)
- **Edit**: `inc/contact-form.php` — Remove reCAPTCHA verification block (lines 38-43)
- **Create**: One-time cleanup script/function to delete `wp_options` rows

### Homepage Modularization
- **Create**: `inc/homepage-customizer.php` — Customizer panel/section/setting registrations
- **Create**: `template-parts/homepage/section-hero.php` — Extracted hero section
- **Create**: `template-parts/homepage/section-articles.php` — Extracted articles grid
- **Create**: `template-parts/homepage/section-categories.php` — New category highlights
- **Create**: `template-parts/homepage/section-banner.php` — New promotional banner
- **Create**: `template-parts/homepage/section-sidebar.php` — Sidebar wrapper
- **Refactor**: `front-page.php` — Dynamic section rendering loop
- **Edit**: `functions.php` — Add require_once for homepage-customizer.php

## Build & Verify

```bash
# Compile CSS
npm run build

# Verify no PHP errors
php -l header.php
php -l front-page.php
php -l functions.php
php -l inc/homepage-customizer.php
php -l inc/contact-form.php

# Verify reCAPTCHA fully removed
grep -r "recaptcha" --include="*.php" --include="*.js" .
# Expected: zero matches (excluding specs/ directory)
```

## Testing Checklist

1. **Sticky Header**: Scroll down any page — header should hide. Scroll up — header should reveal with compact state.
2. **Thumbnails**: Hover over post cards on homepage — smooth shadow/scale transition visible.
3. **ReCaptcha**: Load contact page, check Network tab — no Google reCAPTCHA requests. Submit form — succeeds.
4. **Homepage**: Open Customizer → Homepage Sections → toggle sections on/off, change priorities. Preview changes.
