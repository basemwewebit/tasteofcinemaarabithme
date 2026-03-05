# Implementation Plan: Hero Carousel for Multiple Sticky Posts

**Branch**: `001-hero-carousel` | **Date**: 2026-03-05 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-hero-carousel/spec.md`

---

## Summary

When WordPress has a single sticky post, the hero section on the front page remains a full-width static card (no changes to existing behaviour). When two or more sticky posts are configured, the hero section becomes a cinematic cross-fade carousel that auto-advances every 6 seconds, supports dot-indicator navigation, touch swipe on mobile, and hover-pause on desktop. Implementation requires: (1) a new `mazaq_get_hero_post_ids()` PHP helper that returns all eligible hero IDs, (2) a refactored `hero.php` template that branches between the static card and the carousel markup, and (3) a new carousel controller appended to the existing `assets/js/app.js` — no new dependencies, no bundler changes.

---

## Technical Context

**Language/Version**: PHP 8.0+ / JavaScript (ES2017, no transpile)
**Primary Dependencies**: WordPress 6.0+, Tailwind CSS (JIT via `tailwind.config.js`), jQuery (already enqueued)
**Storage**: N/A — features uses only existing WordPress post data (`sticky_posts` option, post meta)
**Testing**: Local WordPress environment (browser + WP Admin)
**Target Platform**: Desktop + Mobile browsers (Chrome, Firefox, Safari, Edge; iOS Safari, Android Chrome)
**Project Type**: Custom WordPress theme (single-site, no plugin)
**Performance Goals**: Zero additional HTTP requests; carousel JS <100 lines (~3 kB min); no new database queries beyond the existing `mazaq_get_hero_post_ids()` call (one `get_option` call, already cached by WordPress object cache)
**Constraints**:
- Must not break `mazaq_get_hero_post_id()` signature — all three call-sites (`hero.php`, `front-page.php`, `inc/infinite-scroll.php`) must continue to work unchanged.
- RTL layout (Arabic) — slide transition must be direction-agnostic (cross-fade chosen; see research.md §3).
- No new NPM/CDN dependencies.
**Scale/Scope**: Front-page hero template only — no impact on archive, category, or single post pages.

---

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- ✅ **Minimal footprint**: New function added to existing `inc/helpers.php`; no new files in `inc/`.
- ✅ **No plugin-ification**: Feature lives entirely within the theme's template layer.
- ✅ **WordPress coding standards**: All outputs escaped (`esc_html`, `esc_url`); no direct DB calls; helper function follows `mazaq_` prefix convention.
- ✅ **No new external dependencies**: Carousel built in vanilla JS appended to `app.js`.
- ✅ **Backward compatibility**: Existing single-post hero path completely preserved.
- ✅ **Performance**: One extra `get_option('sticky_posts')` call is already in `mazaq_get_hero_post_id()` — no additional queries.

*Post-Phase 1 re-check*: ✅ Data model confirms no new storage, no schema changes, no custom tables.

---

## Project Structure

### Documentation (this feature)

```text
specs/001-hero-carousel/
├── plan.md              ← This file
├── research.md          ← Phase 0: Architecture decisions
├── data-model.md        ← Phase 1: Entity definitions & contracts
├── quickstart.md        ← Phase 1: How to test locally
├── checklists/
│   └── requirements.md  ← Spec quality checklist
└── tasks.md             ← Phase 2 output (/speckit.tasks)
```

### Source Code (repository root)

```text
inc/
└── helpers.php          # ADD: mazaq_get_hero_post_ids() + refactor mazaq_get_hero_post_id()

template-parts/
└── content/
    └── hero.php         # MODIFY: branch between single-card and carousel markup

assets/
└── js/
    └── app.js           # APPEND: HeroCarousel vanilla JS controller class
```

**Structure Decision**: All changes are confined to the theme's existing file layout. No new files are created in `inc/`, `template-parts/`, or `assets/` — all modifications are additive within existing files. This keeps the diff minimal and easily reversible.

---

## Complexity Tracking

> No constitution violations. The feature is fully contained within the existing theme architecture.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|--------------------------------------|
| *(none)* | — | — |
