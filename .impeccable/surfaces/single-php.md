---
version: 1
slug: "single-php"
primary_target: "single.php"
related_targets: ["template-parts/common/reading-progress.php","template-parts/common/reading-rail.php","template-parts/common/font-controls.php","template-parts/common/listicle-toc.php","template-parts/common/newsletter.php","template-parts/content/film-infobox.php","template-parts/content/series-nav.php","template-parts/content/author-box.php","assets/css/src/style.css","assets/js/app-single.js"]
---

# Surface brief — single.php (the article)

Mode: **Read**. Scope: single.php + its template parts + the single-page blocks in assets/css/src/style.css and assets/js/app-single.js.
Related targets: template-parts/common/reading-progress.php, template-parts/common/reading-rail.php, template-parts/common/font-controls.php, template-parts/common/listicle-toc.php, template-parts/common/newsletter.php, template-parts/content/film-infobox.php, template-parts/content/series-nav.php, template-parts/content/author-box.php.

Audience: Arabic-speaking film readers settling in for a long read.
Job: hold a feature-length read comfortably; surface every capability without leaving the page.
Action: read to the end, jump between sections, continue into series/related.
Proof: calm honest slots, working controls, WCAG 2.2 AA, robust RTL, graceful absence of any module.

## Direction contract
- **THESIS:** The article is a festival programme page — criticism in the main column; every capability is a programme note beside it.
- **OWN-WORLD:** The Critic's Screening Room (DESIGN.md), unchanged — paper/ink pair, gold kept rare, Tajawal/Plex Sans Arabic/Amiri/Plex Mono.
- **STORY:** arrival (entry block over framed still) → reading room (margin ticks sections as you pass) → exit (finale, author, related, newsletter).
- **FIRST VIEWPORT:** entry block and framed still own the first screen; the margin column is already alive at first paint.
- **FORM:** one adaptive system for review/listicle/analysis; margin column on wide screens, notes stack below on narrow.
- **FINISH:** unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.

Memorable moment: the margin as a living programme — scroll-spy ticks the current section like a "now showing" marker.

Open at build: exact margin breakpoint; author-box placement (margin note vs below body); finale treatment.

## Resolved decisions (2026-09-06)
- **Author-box placement:** main column, directly after the delight-finale stub — it is the article's *closing credit*, not a margin note. Pure credit only: avatar + "بقلم" label + name + optional role line (existing ACF user field `author_role_title`) + bio + quiet archive link (2px hairline → gold on hover). The "latest 3 posts" grid and its WP_Query are removed; inline-related and more-from-category own discovery. Renders only when an author exists; role/bio/avatar each degrade cleanly.
- **Film-infobox = "the screening ticket":** keeps the shared programme-note skin; film title set in Amiri (the programme's editorial voice); year/director as mono-numeral hairline rows; star rating moved to a perforated foot (2px dashed tear line + punched holes filled with `--surface-body`) echoing the ticket-stub finale; gold appears only in the rating. Field guards unchanged: early return when all four ACF fields empty; stars → numeric grade fallback. Redesigned in place — no ACF/backend changes.
- **Card wrappers:** `card-author.php` deleted (was unreferenced dead code). `card-category.php` kept — live sole call site is taxonomy-film.php.
