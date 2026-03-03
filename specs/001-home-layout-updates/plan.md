# Implementation Plan: Home Layout Updates

**Branch**: `001-home-layout-updates` | **Date**: 2026-03-03 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-home-layout-updates/spec.md`

## Summary

The goal of this update is to refine the homepage content layout and related widgets by preventing the main hero article from being duplicated in the recent posts grid, injecting ad units into the grid at regular intervals (e.g., every 8 posts), and fixing the "Most Read This Week" widget to correctly rank and display articles based on valid view counts accumulated recently.

## Technical Context

**Language/Version**: PHP 8.0+
**Primary Dependencies**: WordPress 6.0+, Tailwind CSS
**Storage**: MySQL/MariaDB (WordPress DB)
**Testing**: Local WP Environment
**Target Platform**: Web Browsers (Responsive Desktop/Mobile)
**Project Type**: Custom WordPress Theme
**Performance Goals**: Minimal impact on Time To First Byte (TTFB), efficient WP_Query caching, avoid layout shifts from ads
**Constraints**: 
- Must modify existing loops (`wp_query`) without breaking infinite scroll/pagination logic.
- "Most Read This Week" needs an efficient approach to handle time-decay views or filter by recent publish date, to avoid excessive database writes/reads that scale poorly.
**Scale/Scope**: Homepage layout and sidebar widget update

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*
- **Library-First / Modularity**: We will reuse existing card templates and define clean helper functions structure.
- **Performance**: We will ensure the view count logging does not introduce N+1 query problems and scaling issues.

## Project Structure

### Documentation (this feature)

```text
specs/001-home-layout-updates/
├── plan.md              
├── research.md          
├── data-model.md        
├── quickstart.md        
├── contracts/           
└── tasks.md             
```

### Source Code (repository root)

```text
inc/
├── post-views.php       # Updated Most Read logic and view counters
template-parts/
├── content/
│   ├── hero.php         # Updated to capture hero post ID globally
├── widgets/
│   └── most-read.php    # Refined Most Read layout and edge cases (0 views)
front-page.php           # Updated main loop to exclude hero AND inject ads
```

**Structure Decision**: We will update the existing WordPress theme structures directly in `inc/`, `template-parts/`, and `front-page.php`.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Infinite Scroll Ad Injection | To display ads every Nth item across paged responses | Simple loop index resets per page; global offset index calculation is strictly required. |
