---
version: 1
slug: "template-parts-ads-ad-responsive-php"
primary_target: "template-parts/ads/ad-responsive.php"
related_targets: ["inc/ads.php","inc/enqueue.php","assets/js/app-adblock.js","assets/css/src/style.css","front-page.php","single.php","template-parts/archive/archive-feed.php","sidebar.php","sidebar-single.php","404.php"]
---

# Surface: template-parts/ads — The Sponsor's Plate

Mode: Read. Ads live inside reading and discovery surfaces; they succeed by never breaking the read.

## Direction

Every ad slot renders as a mounted plate in the publication's grammar: a quiet stage with a celluloid hairline (inset, not dashed), an honest «إعلان» note in caption voice (muted ink, small celluloid square mark), and a reserved stage per format so a late creative never shifts the page. Gold never appears on a plate; the plate itself is inert — no hover, no motion; the creative is the only live element.

## States

- **Filled creative:** arbitrary third-party imagery inside the reserved stage (hostile colors stay inside the frame).
- **Unfilled:** app-adblock swaps the stage's contents for the in-plate support invitation; the plate, its note, and reserved height remain.
- **Unconfigured slot:** production silence — nothing renders (`.archive-ad:empty` depends on this).
- **Dev dummy:** the same plate with a «مساحة إعلانية» stage caption.

## Full stack

`inc/ads.php` owns the plate markup, deterministic CLS reservation keyed off `data-ad-format` (horizontal clamp 5.5–7.5rem, rectangle 1:1 capped, vertical 1:2, fluid clamp 9–13.5rem, responsive clamp 14–16rem), and lazy promotion of creatives via one shared IntersectionObserver over `ins[data-ad-pending]` (120% rootMargin, MutationObserver rescans for infinite-scroll nodes). The JS contract `[data-ad-container][data-expects-network-ad="1"]` + `ins[data-ad-ins]` is preserved; the adblock module must skip pending creatives and render fallback inside `.ad-container__stage`.

## Placement notes

- Archive rows: injected cell spans the row; stage switches to banner proportion (scoped rule).
- Front page: the external `.screening-home__ad-label` is dead — the plate self-labels.
- In-article: plate gets breathing room via `.article-content > .ad-container` margins.
