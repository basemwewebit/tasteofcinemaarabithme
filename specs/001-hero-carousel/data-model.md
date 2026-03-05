# Data Model: Hero Carousel for Multiple Sticky Posts

> This feature operates entirely on existing WordPress data structures. No new database tables or custom post meta keys are introduced.

---

## Entity: HeroPostIds (PHP array)

**Source**: `mazaq_get_hero_post_ids()` — new helper function in `inc/helpers.php`

| Field | Type | Description |
|-------|------|-------------|
| `$ids` | `int[]` | Ordered list of post IDs to display in the hero. Priority: ACF option > sticky posts > latest post. |

**Derivation Rules**:
1. If ACF field `hero_featured_post` (option page) returns a valid post ID → `$ids = [$acf_id]`
2. Else if `get_option('sticky_posts')` returns a non-empty array → `$ids = array_map('intval', $sticky_posts)`
3. Else → `$ids = [(int)get_posts(['post_type'=>'post','posts_per_page'=>1,'fields'=>'ids'])[0]]`
4. Filter out zero/falsy values before returning.

**State transitions**:
- `count($ids) === 1` → Hero renders as static single card (existing behaviour)
- `count($ids) >= 2` → Hero renders as carousel

---

## Entity: HeroSlide (per post)

All fields are derived on-the-fly from standard WordPress post data. No new storage.

| Field | Source | WP Function | Nullable |
|-------|--------|-------------|----------|
| `post_id` | Post ID | n/a | No |
| `permalink` | Post URL | `get_permalink($id)` | No |
| `featured_image` | Post thumbnail | `get_the_post_thumbnail($id, 'hero-image')` | Yes → fallback gradient |
| `category_name` | First category | `get_the_category($id)[0]->name` | Yes → badge hidden |
| `title` | Post title | `get_the_title($id)` | No |
| `excerpt` | Trimmed excerpt | `wp_trim_words(get_the_excerpt($id), 30)` | Yes → paragraph hidden |
| `author_name` | Display name | `get_the_author_meta('display_name', post_author)` | No |
| `date` | Publication date | `get_the_date('j F Y', $id)` | No |

---

## Entity: CarouselState (JS runtime, no persistence)

Managed in-memory by the vanilla JS carousel controller. Not stored in `localStorage` or cookies.

| Property | Type | Initial Value | Description |
|----------|------|---------------|-------------|
| `currentIndex` | `int` | `0` | Zero-based index of active slide |
| `totalSlides` | `int` | `slides.length` | Derived from DOM on init |
| `autoAdvanceMs` | `int` | `6000` | Milliseconds per slide (configurable via `data-interval` attribute) |
| `timer` | `ReturnType<setInterval>` | `null` | Reference to the auto-advance interval |
| `isPaused` | `bool` | `false` | True while hovering or touching |

**State transitions**:
- `goTo(n)` → sets `currentIndex = n`, updates active slide classes, resets `timer`
- `next()` → `goTo((currentIndex + 1) % totalSlides)`
- `prev()` → `goTo((currentIndex - 1 + totalSlides) % totalSlides)`
- `pause()` → `clearInterval(timer)`, sets `isPaused = true`
- `resume()` → restarts `timer`, sets `isPaused = false`

---

## Validation Rules

| Rule | Enforcement |
|------|-------------|
| `mazaq_get_hero_post_ids()` must always return at least 1 ID | PHP fallback chain guarantees this |
| Slide count used in carousel MUST equal length of `$ids` | PHP template iterates `$ids` directly |
| `data-interval` attribute on `.hero-carousel` MUST be a positive integer | JS defaults to `6000` if missing/invalid |
| Dot count MUST match total slide count | Dots generated from same `$ids` loop |

---

## Interface Contracts (PHP ↔ JS)

The PHP template communicates slide configuration to JavaScript via `data-*` attributes on the carousel root element:

```html
<section
  class="hero-carousel"
  data-interval="6000"
  data-total="3"
>
  <!-- slides rendered by PHP -->
</section>
```

| Attribute | Type | Responsibility |
|-----------|------|----------------|
| `data-interval` | `ms (int)` | PHP writes, JS reads for auto-advance |
| `data-total` | `int` | PHP writes, JS reads for validation |
