# Data Model: Archive and 404 Ad Injection

## Key Entities

### Ad Slot Configuration
Represents the mapping of theme-specific locations to AdSense slot IDs.

| Field | Type | Description |
|-------|------|-------------|
| `ad_slot_404_banner` | string | The AdSense slot ID for the 404 page banner. |
| `ad_slot_archive_banner` | string | (Existing) The AdSense slot ID for the top of archive/category pages. |

## UI Components (Ad Containers)

### Grid Ad Block
- **Location**: Injected into the `grid` container of archive templates.
- **Rules**:
    - Must appear after every 6th post.
    - Must match the dimensions of a standard post card (`card-category`).
    - Must handle empty slot states gracefully (placeholder or hidden).

### 404 Banner
- **Location**: Below the "Return Home" button on the 404 page.
- **Rules**:
    - Horizontal format.
    - Centered alignment.
