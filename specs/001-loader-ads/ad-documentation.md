# Ad Management Documentation

This document explains how to configure and manage the ad injection system on the Taste of Cinema website.

## Enabling Grid Ads

The ad injection system is designed to seamlessly integrate ads within post grids on archive pages (e.g., Categories, Authors, and the Homepage).

To enable this feature:

1. Log in to the WordPress Admin Dashboard.
2. Navigate to **Appearance > Customize** ( المظهر > تخصيص ).
3. Locate the **Site Ads / Monetization** ( إعلانات الموقع / تحقيق الدخل ) panel.
4. Check the **Enable Grid Ads** ( تفعيل إعلانات الشبكة ) option.
5. Click **Publish** to save changes.

## Configuring the Ad Interval

You can control how frequently ads appear within the post grids.

1. In the same Customizer panel (**Site Ads / Monetization**).
2. Find the **Ad Insertion Interval** ( فترة إدراج الإعلان ) setting.
3. Enter a number. For example, setting this to `6` will insert an ad after every 6th post in the grid.
4. Click **Publish**.

*Note: The interval applies globally to all archive and grid templates utilizing the dynamic ad slot system.*

## Adding Ad Network Scripts (For Developers)

The actual ad code (e.g., Google AdSense `ins` tags or custom banner markup) needs to be placed in the ad slot template.

1. Open the theme file: `template-parts/ad-slot.php`.
2. Locate the HTML comment: `<!-- Ad Script Here -->`.
3. Replace the placeholder `<span class="ad-placeholder-text">...</span>` with the actual script provided by your ad network.

### Cumulative Layout Shift (CLS) Protection

The ad slot wrapper (`.ad-slot-wrapper` in `style.css`) is pre-configured with a `min-height: 250px`. This is critical for preventing content from jumping around while the ad loads. 

If your primary ad format requires a different height, ensure you update the `min-height` property in `style.css` to match the expected ad dimensions, maintaining a smooth user experience.

### Graceful Fallback

The ad slot includes a JavaScript failsafe (`template-parts/ad-slot.php`). If an ad fails to load (e.g., due to an ad blocker or network issue), the script detects the empty container and gracefully collapses it, preventing blank gaps in your content grid.
