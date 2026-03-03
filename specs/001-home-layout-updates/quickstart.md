# Quickstart: Home Layout Updates

This feature refines three main areas of the homepage without introducing new database tables or complex plugins. 

1. **Extract Hero Post Selection Logic**: Move the logic that finds the featured hero article to an accessible function: `mazaq_get_hero_post_id()`. Use this to exclude the hero from the grid.
2. **Infinite Scroll Ad Units**: Using `global_index = (($paged - 1) * $posts_per_page) + $index`, inject an ad template inside the `front-page.php` loop when `global_index % 8 === 0`.
3. **Most Read This Week**: Update `mazaq_get_most_read_posts()` by adding a `date_query` limiting the results to posts published from `1 week ago` to now, sorting by views and rejecting 0-view posts with a simple condition.

## Requirements Check
- PHP 8+ environment processing a WordPress Theme.
- Tailwind class rendering relies strictly on existing HTML wrapper classes.
