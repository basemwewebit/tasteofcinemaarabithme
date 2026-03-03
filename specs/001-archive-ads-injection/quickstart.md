# Quickstart: Archive and 404 Ad Injection

## Overview
This feature adds automated ad placements to archive pages (Category, Author, Date) and the 404 error page.

## Configuration
1. Go to **WP Admin > Theme Options**.
2. Fill in the **404 Banner Slot** and **Archive Banner Slot** with the appropriate AdSense IDs.
3. Save changes.

## Verification
- **Archives**: Navigate to any category or author page with 7+ posts. An ad should appear after the 6th post.
- **404 Page**: Visit any invalid URL on the site (e.g., `/test-404`). An ad should appear below the main action button.

## Files Modified
- `archive.php`, `category.php`, `author.php`, `404.php`: Template files for injection.
- `inc/scf-fields.php`: For registering new ad slot fields.
- `template-parts/ads/ad-grid.php`: Refined for grid use.
- `template-parts/ads/ad-404.php`: New component for 404 page.
