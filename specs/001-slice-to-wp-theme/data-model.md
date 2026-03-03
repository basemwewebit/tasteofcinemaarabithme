# Data Model: Slice to WordPress Theme

**Branch**: `001-slice-to-wp-theme` | **Date**: 2026-03-03

## Entity Overview

```
┌─────────────────┐     ┌──────────────┐     ┌───────────────┐
│   Post (Article) │────▶│   Category   │     │  Author (User)│
│                  │     │              │     │               │
│  wp_posts        │     │ wp_terms     │     │ wp_users      │
│  wp_postmeta     │     │ wp_termmeta  │     │ wp_usermeta   │
└────────┬─────────┘     └──────────────┘     └───────────────┘
         │
         ▼
┌─────────────────┐     ┌──────────────────┐
│   Tag            │     │  Theme Options   │
│                  │     │  (SCF Options)   │
│  wp_terms        │     │  wp_options      │
└─────────────────┘     └──────────────────┘
```

---

## Entity: Post (Article)

**WordPress table**: `wp_posts` + `wp_postmeta`

| Field | Source | Storage | Notes |
|-------|--------|---------|-------|
| Title | `post_title` | wp_posts | Article headline |
| Content | `post_content` | wp_posts | Full article body (HTML) |
| Excerpt | `post_excerpt` | wp_posts | Short summary for cards |
| Featured Image | `_thumbnail_id` | wp_postmeta | Post thumbnail ID |
| Category | taxonomy | wp_term_relationships | Primary category |
| Tags | taxonomy | wp_term_relationships | Article tags |
| Author | `post_author` | wp_posts | User ID reference |
| Date Published | `post_date` | wp_posts | Publication timestamp |
| Slug | `post_name` | wp_posts | URL-friendly identifier |
| Status | `post_status` | wp_posts | publish, draft, etc. |
| View Count | `_post_views_count` | wp_postmeta | Integer, auto-incremented |

**Derived Fields** (computed, not stored):
- **Reading Time**: Calculated from `post_content` word count ÷ 200 WPM (Arabic reading speed)
- **Relative Date**: Computed from `post_date` (e.g., "أمس", "منذ أسبوع")

**Image Sizes** (registered via `add_image_size()`):
- `hero-image`: 1600×700 (hard crop) — hero section
- `card-thumbnail`: 800×500 (hard crop) — article cards
- `card-wide-thumbnail`: 800×500 (hard crop) — wide card horizontal layout
- `sidebar-thumbnail`: 150×150 (hard crop) — related articles sidebar
- `search-poster`: 400×533 (hard crop, 3:4 ratio) — search results

---

## Entity: Category

**WordPress table**: `wp_terms` + `wp_termmeta` + SCF fields

| Field | Source | Storage | Notes |
|-------|--------|---------|-------|
| Name | `name` | wp_terms | Category display name (Arabic) |
| Slug | `slug` | wp_terms | URL-friendly identifier |
| Description | `description` | wp_term_taxonomy | Category description text |
| Background Image | `category_bg_image` | wp_termmeta (SCF) | Image ID for category header |

**Constraints**:
- Maximum 15 categories (per project taxonomy constraint)
- Each post belongs to at least 1 category

---

## Entity: Author (User)

**WordPress table**: `wp_users` + `wp_usermeta` + SCF fields

| Field | Source | Storage | Notes |
|-------|--------|---------|-------|
| Display Name | `display_name` | wp_users | Author's public name |
| Avatar | Gravatar/user_meta | wp_usermeta | Profile picture |
| Biography | `description` | wp_usermeta | Author bio text |
| Role/Title | `author_role_title` | wp_usermeta (SCF) | e.g., "ناقدة سينمائية" |
| Twitter URL | `author_twitter` | wp_usermeta (SCF) | Social link |
| Website URL | `author_website` | wp_usermeta (SCF) | Personal website link |

**Derived Fields**:
- **Article Count**: Computed via `count_user_posts($user_id)`
- **Lists Count**: Computed via `count_user_posts($user_id)` filtered by specific category

---

## Entity: Tag

**WordPress table**: `wp_terms` (taxonomy: `post_tag`)

| Field | Source | Storage | Notes |
|-------|--------|---------|-------|
| Name | `name` | wp_terms | Tag display name (Arabic, prefixed with #) |
| Slug | `slug` | wp_terms | URL-friendly identifier |

---

## Entity: Theme Options (SCF Options Page)

**WordPress table**: `wp_options` (via SCF options page)

### Group: Google Integration
| Field | SCF Name | Type | Notes |
|-------|----------|------|-------|
| GA4 Measurement ID | `ga4_measurement_id` | text | Format: G-XXXXXXXXXX |
| AdSense Publisher ID | `adsense_publisher_id` | text | Format: ca-pub-XXXXXXXXXXXXXXXX |

### Group: Ad Slot Configuration
| Field | SCF Name | Type | Notes |
|-------|----------|------|-------|
| Hero Banner Ad Slot | `ad_slot_hero_banner` | text | Homepage after-hero responsive ad |
| Sidebar Square Ad Slot | `ad_slot_sidebar_square` | text | Homepage sidebar 300×250 |
| Mobile Menu Ad Slot | `ad_slot_mobile_menu` | text | Off-canvas mobile menu ad |
| In-Article Ad Slot | `ad_slot_in_article` | text | Single post in-content ad |
| Bottom Article Ad Slot | `ad_slot_bottom_article` | text | Single post bottom ad |
| Sidebar Vertical Ad Slot | `ad_slot_sidebar_vertical` | text | Single post sidebar 300×600 |
| Archive Banner Ad Slot | `ad_slot_archive_banner` | text | Category/search page horizontal ad |

### Group: Hero Article
| Field | SCF Name | Type | Notes |
|-------|----------|------|-------|
| Featured Article | `hero_featured_post` | post_object | Select post for homepage hero |

### Group: Contact Information
| Field | SCF Name | Type | Notes |
|-------|----------|------|-------|
| Contact Email | `contact_email` | email | e.g., hello@mazaqcinema.com |
| HQ Address | `contact_address` | textarea | Physical address |
| Twitter URL | `social_twitter` | url | Site Twitter account |
| Website URL | `social_website` | url | Additional social link |

---

## Relationships

```
Post ──(many-to-many)──▶ Category     (wp_term_relationships)
Post ──(many-to-many)──▶ Tag          (wp_term_relationships)
Post ──(many-to-one)───▶ Author       (post_author → wp_users.ID)
Post ──(one-to-one)────▶ Featured Img (wp_postmeta._thumbnail_id → wp_posts.ID)
Post ──(one-to-one)────▶ View Count   (wp_postmeta._post_views_count)
```

---

## WordPress Menu Registrations

| Menu Location | Slug | Used In |
|---------------|------|---------|
| Primary Navigation | `primary-menu` | Header desktop nav + mobile menu |
| Footer Sections | `footer-sections` | Footer "الأقسام" column |
| Footer Links | `footer-links` | Footer "روابط هامة" column |
