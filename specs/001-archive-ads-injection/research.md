# Research: Archive and 404 Ad Injection

## Research Tasks

### 1. WordPress Loop Injection Pattern
**Question**: How to inject ads into the main loop of archive templates (`archive.php`, `category.php`, `author.php`) every 6 posts?
**Decision**: Use a counter variable `$post_index` within the `while (have_posts())` loop. After every 6th post, call `get_template_part('template-parts/ads/ad-grid')`.
**Rationale**: This is the most straightforward and controllable method for theme-level injection without over-complicating with hooks that might trigger in unwanted places.
**Alternatives considered**: Using `the_post` hook, but it can be global and affect unintended areas if not carefully scoped. Simple loop counters are safer for theme templates.

### 2. SCF (Secure Custom Fields) Integration
**Question**: How to add the 404 ad slot and ensure it shows up in the "Theme Options" page?
**Decision**: Add `ad_slot_404_banner` to the `$option_fields` array in `inc/scf-fields.php`.
**Rationale**: Consistency with the existing theme options structure.
**Alternatives considered**: None, this is the project standard.

### 3. Grid Ad Component Sizing
**Question**: How should the injected ad block be styled for the archive grid?
**Decision**: The grid in archives is 3 columns on desktop. The injected ad should probably span all 3 columns or match the size of a single post card. 
- The spec says: "Ad blocks inside grids MUST take up a single column space in the grid (match `card-category` or `card-author` sizing)."
- `template-parts/ads/ad-grid.php` currently uses `col-span-1 md:col-span-2`. I should adjust this or create a specific one for archives if they use 3 columns.
- On archives, it's `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`.
**Rationale**: Matching the card size ensures the grid flow is not disrupted.

## Findings Summary

- **Top Banners**: `category.php` already has `ad_slot_archive_banner`. This should be replicated in `archive.php` and `author.php`.
- **Injection frequency**: Every 6 posts as per spec.
- **404 Ad**: Needs `ad_slot_404_banner` registration and template placement.
- **Responsive Classes**: Use `w-full` and appropriate aspect ratios to avoid CLS.
