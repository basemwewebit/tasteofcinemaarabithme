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
