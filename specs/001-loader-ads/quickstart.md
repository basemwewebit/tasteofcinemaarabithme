# Quickstart & Documentation: loader-ads

## Overview

This feature introduces two key enhancements to the Taste of Cinema theme:
1. **Premium Brand Loader**: A session-based, CSS-animated loader featuring the site logo that appears only on a visitor's first page load.
2. **Ad Injection System**: A configurable system for automatically injecting advertisements into post grids and archive pages at regular intervals.

## How to Configure Ad Injection

The ad injection interval can be configured directly from the WordPress admin dashboard using the standard Customizer.

1. Log in to the WordPress Admin dashboard.
2. Navigate to **Appearance > Customize**.
3. Locate the **Site Ads / Monetization** panel.
4. Toggle **Enable Grid Ads** to turn the feature on or off.
5. Adjust the **Ad Insertion Interval** slider/input to determine how frequently ads appear (default is every 6 posts).
6. Click **Publish** to save your changes.

## Developer Guide

### The Loader

- **Location**: The loader HTML is injected via `header.php` to ensure it is the first thing rendered.
- **Logic**: The JavaScript logic resides in `footer.php` (or a deferred script). It checks `sessionStorage.getItem('toc_loader_seen')`. If not present, it plays the animation and sets the flag. If present, it immediately applies a `hidden` class to prevent a flash of the loader.
- **Styling**: Animations use CSS `transform` and `opacity` for hardware acceleration. Styles are located in `style.css`.

### The Ad System

- **Template Part**: `template-parts/ad-slot.php` handles the actual rendering of the ad markup. Ensure this file contains appropriate fallback styling so empty ads don't break the layout.
- **Grid Injection**: In templates like `template-parts/post-grid.php`, a counter is used within the WordPress Loop. Assuming the loop counter is `$i`:
  ```php
  $interval = get_option('toc_ad_injection_interval', 6);
  if ( get_option('toc_ad_injection_enabled') && $i % $interval === 0 ) {
      get_template_part('template-parts/ad-slot', null, ['context' => 'grid']);
  }
  ```

## Troubleshooting

- **Loader appears on every page**: Check if the user's browser is blocking `sessionStorage` or if third-party scripts are clearing it.
- **Ads break the grid layout**: Ensure that `template-parts/ad-slot.php` outputs a container element that matches the grid item structure (e.g., has the correct classes to span rows/columns).
- **Layout Shift (CLS) is high**: Make sure the ad container has a predefined `min-height` in CSS so that when the external ad script loads and injects the iframe, it doesn't push content down.
