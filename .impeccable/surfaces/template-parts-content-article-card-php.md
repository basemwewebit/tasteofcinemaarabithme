---
version: 1
slug: "template-parts-content-article-card-php"
primary_target: "template-parts/content/article-card.php"
related_targets: ["front-page.php","archive.php","search.php","single.php","page-films.php"]
---

# Surface brief: shared article card (template-parts/content/article-card.php)

## Mode
Read (the card serves reading surfaces: archive, search, single-related; front-page consumes it as part of its Experience-mode world).

## Strategy
Shared component redesign inside the committed world. All five layouts (standard, wide, poster, compact, related) rebuilt on the "programme note" pattern; every consuming surface's override CSS updated in the same pass.

## Direction contract

**THESIS** — Every article card is a programme note pulled from the critic's filing cabinet: the still, the headline, and one mono metadata line. Nothing else is on duty.

**OWN-WORLD** — The Critic's Screening Room, paired rooms. Cards are Paper Elevated under a hairline Celluloid Beige frame, 12px corners. Gold is rare: 0.55rem square markers, micro-dot separators, and the resolution of hover/focus — never chrome, never decoration. Index rows carry Celluloid Beige rules, not boxes.

**STORY** — The drawer opens: a category marker (per-category tint at rest, own hue ignition on hover — refined from the original gold-marker spec) and category label lead into a Tajawal headline; the eye drops to the bidi-isolated mono programme line (reading time · date). On hover the whole note resolves as one gesture — still sharpens, shade deepens, marker fills its own hue, title takes the tint. No lift, no float.

**FIRST VIEWPORT** — A wide lead card split asymmetrically in RTL (still ~7/12, note ~5/12) under a display-scale Tajawal headline, flanked by ruled index rows: judgment, then the cabinet.

**FORM** — Position: shared card component consumed by front-page (Experience), archive (Read), search (Read), single/films surfaces. Seed key: none — pinned committed world, precisely specified narrow request; no concept-seed roll.

**FINISH** — unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.

## Anti-goals
Side-stripes, gradient text, glass/blur, nested cards, identical card grids, red/black costume, tracked or uppercased Arabic, decorative 3px lift.
