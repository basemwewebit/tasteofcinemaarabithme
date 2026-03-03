# Data Model: Home Layout Updates

## Core Entities

### Article

The existing WordPress `post` object representing cinematic content.
- ID: `bigint(20)`
- post_title: `text`
- post_date: `datetime`

Modifications: No structural changes to the database.

### Ad Block

A layout placeholder for ad networks (like AdSense).
- Component: `template-parts/ads/ad-grid.php` (created if not existing, or inline). It will be injected dynamically into the article list query loop.
- Rule: Insert when `Global Index % 8 === 0` inside an ongoing paginated query.

### View Metric

A simple custom meta field tracked on single post views.
- Meta Key: `_post_views_count`
- Type: `int` (stored as `string` in `wp_postmeta`)
- Range Query Limit: `date_query` > '1 week ago' coupled with this metadata to exclude 0 views or sort descending. Requirements call for eliminating non-viewed items if viewed items exist. By ensuring a meta_value strictly > 0 via a regular expression or numeric comparison, we satisfy this rule efficiently.
