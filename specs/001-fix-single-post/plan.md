# Implementation Plan: Fix Single Post Template Issues

**Branch**: `001-fix-single-post` | **Date**: 2026-03-03 | **Spec**: [Link](./spec.md)
**Input**: Feature specification from `/specs/001-fix-single-post/spec.md`

## Summary

This feature resolves bugs and enhances user experience/monetization on the single post template (`single.php`). Specifically, it implements dynamic breadcrumbs, correctly hyperlinked author names, calculated reading time, dynamic in-content advertisement placement every 3 paragraphs, and robust, cached related articles logic. The approach relies heavily on native WordPress functionality and the Transients API for optimal performance.

## Technical Context

**Language/Version**: PHP 8.0+, HTML5, CSS3, JS (Vanilla)
**Primary Dependencies**: WordPress 6.0+, active theme (`tasteofcinemaarabithme`)
**Storage**: MySQL/MariaDB (standard WP database schema)
**Testing**: Manual frontend verification and Query Monitor for database hits (transients caching).
**Target Platform**: Linux server (staging/production), existing website.
**Project Type**: Custom WordPress Theme
**Performance Goals**: Time to First Byte (TTFB) under 500ms; avoid heavy, uncached `WP_Query` loops on the frontend.
**Constraints**: Must not interfere with the structure of the existing HTML/CSS provided in the slice; advertisements must not break underlying HTML `<p>` tags; implementation must use standard WordPress hooks/filters.
**Scale/Scope**: Impacts all `single.php` views across the entire site.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. WordPress Performance Best Practices**: The approach conforms by using native functions (`get_the_author()` etc.) and the Transients API for caching high-cost DB calls like Related Articles.
- **II. Minimum Plugin Dependency**: The breadcrumb and reading time features are implemented in the theme natively to minimize bloat and reduce dependencies on 3rd party plugins (like RankMath or Yoast), keeping the DOM layout consistent with the slice.
- **III. Native Functions First**: All enhancements hook into existing functions like `the_content` (for ads) and standard query parameters, aligning with core WP philosophy.

*(All Gates Passed)*

## Project Structure

### Documentation (this feature)

```text
specs/001-fix-single-post/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

*(Note: The contracts directory has been excluded as it does not apply to this WordPress frontend module enhancement.)*

### Source Code (repository root)

```text
wp-content/themes/tasteofcinemaarabithme/
├── single.php           # Entry point for single posts (needs modification)
└── functions.php        # Adding hook callbacks, reading time, breadcrumb logic, and ad injectors
```

**Structure Decision**: A standard WordPress theme modification. We will add custom functions to `functions.php` and update `single.php` to invoke those functions.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

*(No violations. Complexity is low and contained inside the active theme directory.)*
