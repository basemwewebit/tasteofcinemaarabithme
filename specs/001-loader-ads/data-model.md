# Data Model: loader-ads

## WordPress Options (wp_options)

The ad injection system will store its configuration in the WordPress `wp_options` table to ensure global availability and easy access via the Customizer.

| Option Name | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `toc_ad_injection_interval` | Integer | `6` | The frequency at which an ad is injected into post grids (e.g., every N posts). |
| `toc_ad_injection_enabled` | Boolean | `false` | Global toggle to enable or disable grid ad injection. |

## Session Storage (Browser)

The loader uses browser session storage to determine if the user has already visited the site during their current session.

| Key | Type | Description |
| :--- | :--- | :--- |
| `toc_loader_seen` | String (`"true"`) | Set to "true" after the loader has finished its animation on the first page load of a session. If present, the loader immediately hides on subsequent page loads. |

## Ad Slot Variables (PHP/Frontend)

When rendering an ad slot template part (`template-parts/ad-slot.php`), the following variables are expected to be available or passed:

| Variable | Type | Description |
| :--- | :--- | :--- |
| `$ad_type` | String | Identifier for the specific ad zone (e.g., `'archive_grid'`, `'single_content'`). Used to fetch the correct ad code snippet if managing multiple zones. |
| `$layout_context` | String | Contextual class name for styling the ad slot wrapper (e.g., `'grid-span-2'`). |
