# Data Model: Fix Single Post Template Issues

This feature primarily relies on existing WordPress functionality and does not introduce new persistent database tables or complex entity relationships.

## Key Entities

### WordPress Post (`WP_Post`)
The central entity for the single post page. The feature modifies how its content and metadata are displayed.

- **Attributes accessed**:
  - `ID`: Used for fetching current post, generating related articles exclusions.
  - `post_content`: Used to dynamically count words for reading time, and injected into for advertisements.
  - `post_author`: Used to fetch author metadata.
  - `post_title`: Used for breadcrumbs.

### Author (`WP_User`)
Represents the writer of the article.

- **Attributes accessed**:
  - `ID`: Used to get author link.
  - `display_name`: Displayed as hyperlinked text.

### Categories (`WP_Term` belonging to `category` taxonomy)
Used for breadcrumbs navigation and related posts lookup.

- **Attributes accessed**:
  - `term_id`: For querying related posts.
  - `name`: Breadcrumb label.
  - `slug`: Breadcrumb URLs.

### Advertisement Configuration (Database Options or Custom Fields)
If ads are managed via options or custom fields (e.g. SCF or ACF), these will be integrated.

- **Attributes required (Conceptual)**:
  - `ad_code_html`: The snippet to inject.
  - `injection_interval`: Global configuration (e.g., `3`) for paragraph counts.
  - **Note**: Will fall back to a hardcoded placeholder/interval if not configurable via backend yet.

## Caching Strategy (Transients)
- **Key**: `toc_related_posts_{post_id}_{category_id}`
- **Expiration**: 12 hours (43200 seconds)
- **Data**: Stored HTML fragment or array of `WP_Post` objects to render the "Related Articles" section, minimizing expensive redundant database queries per page load.
