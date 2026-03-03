# Research & Technical Decisions: Fix Single Post Template Issues

## 1. Breadcrumbs Implementation
- **Decision**: Implement a custom breadcrumb function within `functions.php` rather than relying on a 3rd party plugin like SEO plugins.
- **Rationale**: Keeps the theme dependencies low and provides full control over the specific markup required by the slice design.
- **Alternatives considered**: Using Yoast SEO or RankMath breadcrumbs. Rejected because we want to minimize plugin dependencies for Core Web Vitals and performance, and the design requires specific HTML classes.

## 2. Author Link
- **Decision**: Use standard WordPress `the_author_posts_link()` or `get_author_posts_url()` to link to the author's archive. Handle empty archives by falling back to `get_the_author()` as plain text if no valid archive exists.
- **Rationale**: Native WordPress function, lightweight and reliable.
- **Alternatives considered**: Custom routing for authors. Not necessary since WP handles author archives natively.

## 3. Estimated Reading Time Calculation
- **Decision**: Create a custom helper function `toc_estimated_reading_time()` that counts the words in `$post->post_content` using `str_word_count(strip_tags($content))` divided by 200 (standard Arabic reading speed) to return minutes. Handle less than 1 minute by returning "1 دقيقة".
- **Rationale**: Simple calculation that can be done on the server-side, ensuring the reading time is always available on page load without JavaScript overhead.
- **Alternatives considered**: JavaScript-based calculation. Rejected because it causes layout shifts and is not SEO/performance friendly.

## 4. In-Content Advertisements Injection
- **Decision**: Use the `the_content` filter hook to parse the HTML string, find `</p>` tags, and inject standard ad placeholders (or actual AdSense code) after every 3 paragraphs.
- **Rationale**: This is the standard, safest way to modify post content in WordPress without altering the underlying database or requiring editors to use shortcodes.
- **Alternatives considered**: Shortcodes. Rejected because it forces the content editor to manually insert them. Gutenberg blocks. Rejected because it doesn't automatically apply to old posts.

## 5. Related Articles Retrieval
- **Decision**: Use a custom `WP_Query` at the end of `single.php`. The query will fetch posts (excluding the current one) that share at least one category with the current post. Fall back to tags or recent posts if no categories match. Cache the query results using Transients API for 12 hours for performance.
- **Rationale**: Provides highly relevant articles. Using the Transients API adheres to `wordpress-performance-best-practices` skill to prevent heavy, repetitive database queries on highly trafficked single post pages.
- **Alternatives considered**: Jetpack Related Posts or similar plugins. Rejected due to performance overhead and lack of styling control.
