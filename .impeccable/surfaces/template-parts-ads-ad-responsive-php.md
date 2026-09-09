---
version: 1
slug: "template-parts-ads-ad-responsive-php"
primary_target: "template-parts/ads/ad-responsive.php"
related_targets: ["inc/ads.php","inc/enqueue.php","assets/js/app-adblock.js","assets/css/src/style.css","front-page.php","single.php","template-parts/archive/archive-feed.php","sidebar.php","sidebar-single.php","404.php"]
---

# Surface: template-parts/ads — The Sponsor's Plate

Mode: Read. Ads live inside reading and discovery surfaces; they succeed by never breaking the read.

## Direction

Every ad slot renders as a mounted plate in the publication's grammar: a quiet stage with a celluloid hairline (inset, not dashed), an honest «إعلان» note in caption voice (muted ink, small celluloid square mark), and a reserved stage per format so a late creative never shifts the page. The stage carries a faint celluloid idle-light wash (7% light / 5% dark over its base) — projector light waiting on a mounted screen — deepening one step (12% / 9%) with a warmer hairline when the stage speaks in the publication's voice (`.ad-container--fallback`). Gold never appears on a plate; the plate itself is inert — no hover, no motion; the creative (and the fallback CTA) are the only live elements.

## States

- **Filled creative:** arbitrary third-party imagery inside the reserved stage (hostile colors stay inside the frame).
- **Unfilled:** app-adblock swaps the stage's contents for the in-plate support invitation; the plate, its note, and the reservation floor remain (the stage may grow to host the card — see Polish contract).
- **Unconfigured slot:** production silence — nothing renders (`.archive-ad:empty` depends on this).
- **Dev dummy:** the same plate with a «مساحة إعلانية» stage caption.

## Full stack

`inc/ads.php` owns the plate markup, deterministic CLS reservation keyed off `data-ad-format` (horizontal clamp 5.5–7.5rem, rectangle 1:1 capped, vertical 1:2, fluid clamp 9–13.5rem, responsive clamp 14–16rem), and lazy promotion of creatives via one shared IntersectionObserver over `ins[data-ad-pending]` (120% rootMargin, MutationObserver rescans for infinite-scroll nodes). The JS contract `[data-ad-container][data-expects-network-ad="1"]` + `ins[data-ad-ins]` is preserved; the adblock module must skip pending creatives and render fallback inside `.ad-container__stage`.

## Hardening contract

- Formats are whitelisted (horizontal/rectangle/vertical/fluid/responsive); any other value folds onto the responsive reservation so CLS space always exists and `data-ad-format` stays a valid AdSense value.
- The publisher id is stripped to `[A-Za-z0-9-]` in one helper (`mazaq_get_adsense_publisher_id`) shared by render, head script, and lazy push; the head script URL is `esc_url`+`rawurlencode`. A malformed option degrades to production silence.
- In-article injection is fenced to real single-post HTML views (no feed/embed/AJAX/REST) and counts paragraphs via `wp_strip_all_tags`.
- `ad-grid` rejects non-string/empty `$args['slot']`; app-adblock guards `document.body` and treats `window.FocusTrap` as optional (prompt stays dismissible without it).
- Forced-colors mode redraws the plate boundary as a real `CanvasText` border (the inset hairline and washes are stripped); fallback text wraps via `overflow-wrap: break-word`.

## Polish contract

- Fallback-card inks flow from the plate's scoped tokens — `--ad-ink` (body), `--ad-ink-strong` (title, light CTA), `--ad-cta-ink` (gold-tint in dark) — flipping at the `.ad-container` root; no per-element `.dark` rules inside the card.
- The support appeal never clips: in the fallback state, fixed-height reservations (horizontal/fluid/responsive, and the archive banner override) relax to `height: auto; min-height: 5.5rem`. Once no creative is coming, the reservation's CLS job is done; the stage grows honestly instead of cutting the title or CTA.

## Placement notes

- Archive rows: injected cell spans the row; stage switches to banner proportion (scoped rule).
- Front page: the external `.screening-home__ad-label` is dead — the plate self-labels.
- In-article: plate gets breathing room via `.article-content > .ad-container` margins.
