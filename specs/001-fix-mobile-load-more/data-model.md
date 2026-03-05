# Data Model: Mobile Load More Issue

**Entities**:
- **Post**: WordPress `WP_Post` entity. No changes to the data model. The feature simply fetches subsequent pages of the standard `post` post-type archive.

**Relationships**:
None changed.

**Validation Rules**:
N/A

**State Transitions**:
- Current Page: Increments by 1 upon successful AJAX fetch.
- Has More Posts: Boolean flag tracking whether the WP_Query has additional pages (`max_num_pages > paged`).
- Is Loading: Boolean flag preventing concurrent AJAX requests.
