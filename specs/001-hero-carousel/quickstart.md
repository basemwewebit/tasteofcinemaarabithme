# Quickstart: Hero Carousel — Local Testing Guide

**Branch**: `001-hero-carousel` | **Feature**: Hero section carousel for multiple sticky posts

---

## Prerequisites

- Local WordPress environment running (e.g. LocalWP, MAMP, XAMPP)
- Admin access to WordPress dashboard
- At least 2 published posts with featured images

---

## Test Scenario A — Single Sticky Post (No Carousel)

1. Go to **WP Admin → Posts**
2. Hover over any post → click **Quick Edit**
3. Check **Make this post sticky** → **Update**
4. Ensure only ONE post is sticky (uncheck "sticky" on any others)
5. Visit the front page (homepage)
6. **Expected**: Full-width static hero card, no dots, no arrows, no animation

---

## Test Scenario B — Multiple Sticky Posts (Carousel Activates)

1. Go to **WP Admin → Posts**
2. Make **2 or more** posts sticky (via Quick Edit → "Make this post sticky")
3. Visit the front page
4. **Expected**:
   - Hero section shows first sticky post
   - Dot indicators appear at the bottom (one per sticky post)
   - After 6 seconds, carousel auto-advances to next slide (cross-fade transition)
   - Active dot updates to match current slide

---

## Test Scenario C — Swipe on Mobile

1. Open the front page on a mobile device (or DevTools → Responsive mode)
2. With 2+ sticky posts active, swipe LEFT on the hero
3. **Expected**: Next slide appears with fade transition
4. Swipe RIGHT → previous slide appears

---

## Test Scenario D — Dot Navigation

1. With 2+ sticky posts active on the front page
2. Click any dot indicator
3. **Expected**: Carousel immediately jumps to the corresponding slide; auto-advance timer resets

---

## Test Scenario E — Hover Pause

1. With 2+ sticky posts on desktop
2. Hover mouse over the hero section
3. **Expected**: Auto-advance stops (no slide change while hovering)
4. Move mouse out of hero
5. **Expected**: Auto-advance resumes

---

## Test Scenario F — No Featured Image Fallback

1. Make a post sticky that has **no featured image**
2. Visit the front page
3. **Expected**: The slide for that post shows the dark gradient fallback background (same as current single-hero behaviour)

---

## Verifying `post__not_in` Still Works

After enabling the carousel, scroll down to the **أحدث المقالات** grid on the homepage and confirm:
- The first sticky post (slide 0) does **not** appear in the grid below (excluded by `mazaq_get_hero_post_id()` which still returns `$ids[0]`)

---

## How to Adjust Auto-Advance Interval

The interval is set via the `data-interval` attribute on the `.hero-carousel` element (in milliseconds). To change it, edit `template-parts/content/hero.php` and update:

```php
data-interval="6000"   // default: 6000ms = 6 seconds
```
