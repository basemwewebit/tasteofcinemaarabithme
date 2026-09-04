---
name: "Mazaq Cinema"
description: "A warm-paper and nocturnal-ink Arabic cinema magazine system for curated discovery and long-form reading."
colors:
  primary: "#C9A227"
  primary-hover: "#B88F1E"
  gold-tint: "#E6CB6A"
  claret: "#8E2A2A"
  celluloid: "#D4C9A8"
  ink: "#0B0B0E"
  ink-elevated: "#16161B"
  ink-overlay: "#1E1E25"
  deep-shadow: "#020617"
  paper: "#F7F4ED"
  paper-elevated: "#FFFFFF"
  paper-muted: "#ECE7DC"
  mist: "#E2E8F0"
  pewter: "#94A3B8"
  charcoal: "#475569"
  text-on-ink: "#EAE6DC"
  text-on-ink-muted: "#9C988E"
  text-on-paper: "#0F0E0C"
  text-on-paper-muted: "#4E4A40"
typography:
  display:
    fontFamily: "Tajawal, Reem Kufi, IBM Plex Sans Arabic, system-ui, sans-serif"
    fontSize: "clamp(2.35rem, 4.8vw, 4.75rem)"
    fontWeight: 800
    lineHeight: 1.08
    letterSpacing: "0"
  headline:
    fontFamily: "Tajawal, Reem Kufi, IBM Plex Sans Arabic, system-ui, sans-serif"
    fontSize: "clamp(1.65rem, 3.2vw, 2.6rem)"
    fontWeight: 700
    lineHeight: 1.25
    letterSpacing: "0"
  title:
    fontFamily: "Tajawal, Reem Kufi, IBM Plex Sans Arabic, system-ui, sans-serif"
    fontSize: "clamp(1.2rem, 1.45vw, 1.45rem)"
    fontWeight: 700
    lineHeight: 1.4
    letterSpacing: "0"
  body:
    fontFamily: "IBM Plex Sans Arabic, Noto Sans Arabic, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: 1.85
    letterSpacing: "0"
  editorial:
    fontFamily: "Amiri, Aref Ruqaa, IBM Plex Serif, Georgia, serif"
    fontSize: "clamp(2.1rem, 4.5vw, 4.25rem)"
    fontWeight: 700
    lineHeight: 1.16
    letterSpacing: "0"
  label:
    fontFamily: "IBM Plex Sans Arabic, Noto Sans Arabic, system-ui, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "0"
  numeric:
    fontFamily: "IBM Plex Mono, JetBrains Mono, ui-monospace, monospace"
    fontSize: "0.8125rem"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "0"
rounded:
  sm: "4px"
  md: "8px"
  lg: "12px"
  pill: "9999px"
spacing:
  tight: "0.5rem"
  sm: "1rem"
  md: "1.5rem"
  lg: "2.5rem"
  xl: "4rem"
  2xl: "6rem"
  section: "clamp(3rem, 6vw, 5rem)"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.text-on-paper}"
    typography: "{typography.label}"
    rounded: "{rounded.md}"
    padding: "0.85rem 1rem"
    height: "3rem"
  card-editorial:
    backgroundColor: "{colors.ink-elevated}"
    textColor: "{colors.text-on-ink}"
    rounded: "{rounded.lg}"
    padding: "1.15rem"
  chip-category:
    backgroundColor: "{colors.paper-muted}"
    textColor: "{colors.charcoal}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "0.45rem 0.9rem"
    height: "2.5rem"
  input-editorial:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.text-on-paper}"
    typography: "{typography.body}"
    rounded: "{rounded.lg}"
    padding: "0.9rem 1rem"
    height: "3rem"
---

# Design System: Mazaq Cinema

## Overview

**Creative North Star: "The Critic's Screening Room"**

Mazaq Cinema is an Arabic-first editorial system built as a pair of cinematic rooms: warm paper for open, readable discovery and nocturnal ink for atmospheric focus. Cream text, disciplined Arabic type, and photographic material create the sense of a film-literate publication without turning every surface into a dark spectacle.

The system uses magazine pacing rather than a uniform content grid. Large and small image-led modules may sit asymmetrically to express editorial judgment, then collapse into an intentional single-column reading sequence. The exact composition of any one hero belongs to its surface brief; the durable rule is that hierarchy and imagery reveal what the editors value.

**Key Characteristics:**
- Warm-paper and nocturnal-ink themes are equal expressions of one system.
- Film stills and posters are editorial material; branded initial plates preserve composition when imagery fails or is absent.
- Tajawal carries display hierarchy, while IBM Plex Sans Arabic keeps body copy and controls clear.
- Projector Gold is a rare signal for actions, focus, ranks, and selected editorial emphasis.
- Editorial frames use restrained 8px to 12px corners; pill shapes are reserved for labels and circular controls.
- Every asynchronous interaction exposes understandable loading, success, error, retry, empty, and end states as applicable.

## Colors

The palette pairs warm paper with nocturnal ink; both modes retain cream, celluloid, and restrained gold so theme changes never feel like a change of publication.

### Primary
- **Projector Gold:** the scarce action and attention color for primary controls, keyboard focus, ranks, and selected editorial cues.
- **Lamp Gold:** the lighter supporting gold for dark-stage legibility and small luminous details.

### Secondary
- **Censor Claret:** reserved for warnings, destructive states, and rare editorial tension; it is not a general brand accent.
- **Celluloid Beige:** a quiet framing tone for borders, separators, and image-adjacent surfaces.

### Neutral
- **Nocturnal Ink and Midnight Surface:** dark-stage foundations and raised dark panels.
- **Warm Paper, Paper Elevated, and Warm Ash:** light-stage foundations, lifted surfaces, and muted containers.
- **Text Cream and Ink Text:** primary readable text chosen as a pair with their surface.
- **Pewter and Charcoal:** metadata, captions, helper copy, and subdued controls.

### Named Rules
**The Paired Rooms Rule.** A component is not complete until its hierarchy, contrast, borders, and states work on both Warm Paper and Nocturnal Ink.

**The Gold Is Rare Rule.** Gold is a signal, not trim. If every card, heading, and icon is gold, nothing is important.

**The No Netflix Costume Rule.** Claret may create editorial tension, but never combine it with black as a red-streaming-service costume.

## Typography

**Display Font:** Tajawal, with Reem Kufi and IBM Plex Sans Arabic fallbacks.  
**Body Font:** IBM Plex Sans Arabic, with Noto Sans Arabic and system fallbacks.  
**Editorial Font:** Amiri, with Aref Ruqaa, IBM Plex Serif, Georgia, and serif fallbacks.  
**Numeric Font:** IBM Plex Mono, with JetBrains Mono and ui-monospace fallbacks.

**Character:** Tajawal gives discovery surfaces a confident contemporary Arabic poster voice; IBM Plex Sans Arabic sustains calm reading and reliable UI. Amiri is reserved for long-form editorial moments, while isolated monospaced numerals keep metadata steady in RTL layouts.

### Hierarchy
- **Display** (800, fluid display scale, 1.08): the strongest editorial entry points; keep it rare.
- **Headline** (700, fluid headline scale, 1.25): section titles and feature headings.
- **Title** (700, fluid title scale, 1.4): cards and compact editorial modules.
- **Editorial** (700, fluid editorial scale, 1.16): long-form article titles and deliberate magazine moments.
- **Body** (400, 1.125rem, 1.85): prose, summaries, descriptions, and UI copy; keep long-form measure near 65 to 75ch.
- **Label** (600, 0.9375rem, zero tracking): Arabic labels, eyebrows, chips, and controls.
- **Numeric** (500, tabular and bidi-isolated): dates, ranks, view counts, and reading time.

### Named Rules
**The Arabic Letterforms Rule.** Arabic labels do not get tracked uppercase treatment. Preserve connected forms, contextual alternates, ligatures, and RTL-aware spacing.

**The Reading Room Rule.** Long-form pages prioritize measure, line-height, and quiet hierarchy over decorative flourishes.

## Layout

Use an image-led editorial grid with deliberate changes in scale: one lead item may span columns while supporting items remain compact. Asymmetry communicates curation, but alignment, reading order, and whitespace remain disciplined. Use the established spacing rhythm from tight inline gaps through generous section spacing; do not fill every open area with another module.

At narrower widths, multi-column compositions collapse to one clear RTL sequence, actions become comfortably full-width when needed, and interactive targets remain at least 44px. Photography keeps purposeful aspect ratios and responsive sources rather than being stretched to fit arbitrary boxes.

**The Composition Is Local Rule.** Reuse asymmetric hierarchy and editorial pacing, but keep exact hero splits, rails, section order, and first-viewport choreography in each surface brief.

## Elevation & Depth

Depth is hybrid and restrained. Dark surfaces rely on tonal layering, image gradients, and a faint inset highlight; light surfaces may use a soft ambient lift. Hover can raise an editorial card by a few pixels, but hierarchy must still be legible without motion or shadow.

### Shadow Vocabulary
- **Card Shadow** (`0 1px 0 rgba(255,255,255,0.04) inset, 0 8px 24px rgba(0,0,0,0.4)`): dark elevated cards and panels.
- **Editorial Card Lift** (`0 18px 45px rgba(15,23,42,0.14)`): selected light-mode features and image-led editorial containers.
- **Focus Glow** (`0 0 0 3px rgba(230,203,106,0.42)`): visible keyboard focus on both theme surfaces.

### Named Rules
**The Screen Depth Rule.** Depth comes from imagery, tonal layers, and controlled shadows; do not stack nested cards to manufacture hierarchy.

**The Focus Is Not Optional Rule.** Focus rings remain visible on both Warm Paper and Nocturnal Ink and are never removed without an equivalent replacement.

## Shapes

The core form language is a restrained editorial frame: small utility details may use 4px corners, controls use 8px, and cards use 12px. Pills belong only to category labels, tags, and truly circular controls. Large atmospheric panels may exceed the core radius when their scale justifies it, but repeated content cards stay within the 8px to 12px system.

Borders are hairline and warm or softly translucent. Gold borders appear in active, hover, or focus states rather than outlining every object at rest. Image crops inherit their container shape and keep a consistent silhouette even while a fallback is showing.

## Components

### Buttons
- **Shape:** restrained editorial controls with 8px to 12px corners and a minimum 44px touch target.
- **Primary:** Projector Gold with dark ink text, heavy label weight, and one clear action per group.
- **Hover / Focus:** a slight brightness or 1px lift may confirm hover; a separate gold focus outline remains unmistakable.
- **Secondary / Ghost:** warm hairline borders and theme-aware text for lower-priority actions.
- **State:** loading disables duplicate submission without erasing the label's meaning; errors offer a clear retry path.

### Chips
- **Style:** pill labels for categories, tags, and suggestions; Warm Ash and Charcoal on light surfaces, tonal ink with cream or pewter on dark surfaces.
- **State:** hover, focus, and selection may shift toward gold, but chips should not read like miniature primary buttons.

### Cards / Containers
- **Corner Style:** 12px for recurring editorial cards and 8px for smaller tiles.
- **Background:** paired translucent warm surfaces and Midnight Surface treatments.
- **Image:** use a real poster or still whenever available. Keep a branded, title-derived initial plate beneath remote or lazy imagery and as the final no-image fallback.
- **Hierarchy:** standard, wide, poster, compact, and related variants share type, tint, and state behavior while changing composition.
- **State:** image failure reveals the prepared fallback without collapsing the card or exposing broken-image chrome.

### Inputs / Fields
- **Style:** warm hairline border, theme-aware surface and text, restrained corners, and high-contrast placeholder treatment.
- **Focus:** visible Projector Gold ring and border shift.
- **Error / Disabled:** communicate with live text as well as color; disabled controls remain understandable.

### Navigation
- **Style:** compact Arabic type, robust focus states, and a strong brand anchor. Translucency may soften the header, but must not become decorative glass.
- **Responsive:** mobile navigation preserves logical focus, clear close affordances, and 44px targets.
- **Footer:** brand description and copyright form the resilient baseline. Optional menus and social links enhance it only when configured; their absence never leaves an empty or broken footer.

### Load More and Asynchronous States
- **Action:** use an explicit load-more control when content continues; do not rely on invisible infinite-scroll behavior.
- **Loading:** expose a live status, set busy state, and prevent duplicate requests.
- **Completion:** announce empty and end states clearly, then remove or disable actions that can no longer succeed.
- **Failure:** preserve loaded content and present a retryable error rather than replacing the feed.

### Newsletter
- **Layout:** a compact editorial invitation paired with an email field and one primary action; stack cleanly on narrow screens.
- **State:** submission feedback is announced in a live region. Loading, success, and error copy should never shift the panel into an unusable state.

### Single Article Surface
The article is a festival programme page: criticism holds the main column at a 66ch measure, and every capability is a programme note beside it. This surface owns these patterns:

- **Programme grid:** from 1360px a persistent 17.5rem margin column (sticky near the top) holds the notes beside the text; below that width the same notes stack ahead of the body, and a floating circular progress puck (conic gold ring, live percentage, jump sheet) carries navigation.
- **Programme note skin:** margin notes — the film card, the section index, the font controls — share one skin: hairline Celluloid Beige border, 12px corners, Paper Elevated surface (Ink Elevated with a faint cream hairline in dark mode). New margin capabilities wear this skin rather than inventing a new container.
- **Living section index:** the in-article table of contents reads as a "now showing" board. Each entry carries a small square marker (0.55rem) that fills Projector Gold with a soft halo for the section in view and a quiet gold tint for sections already read; a gold fill creeps along a 2px hairline track via transform scaleY and stops at the active tick — it tracks section position, not page scroll.
- **Entry block typography:** Display Tajawal title (800, balanced, up to ~3.8rem), an Amiri standfirst at reading sizes (1.95 leading), and a label-weight credit line whose items are separated by gold micro-dots (0.3rem); the author leads the line set heavier in Tajawal.
- **Note label as heading:** margin notes use no kicker — the note's own label is its heading ("بطاقة الفيلم", "في هذا المقال").
- **Ticket-stub finale:** the end-of-read panel is a stub with a dashed perforation and punched notches filled with the page surface color (`--surface-body`), so the punch reads as a real hole on both themes.

**The Gold Needs a Dark Room Rule.** On Warm Paper, small gold text falls to roughly 2:1 contrast. Small text on light paper uses warm muted ink; gold survives as borders, dots, and fills, and gold text is reserved for dark rooms and solid signal elements like the active tick.

## Do's and Don'ts

### Do:
- **Do** treat Warm Paper and Nocturnal Ink as a matched system, not primary and afterthought themes.
- **Do** let image scale and asymmetric placement express editorial priority while preserving a clear RTL reading order.
- **Do** keep real cinema imagery dominant and ship branded initial fallbacks beneath failure-prone images.
- **Do** use Projector Gold sparingly for primary actions, ranks, focus, and selected emphasis.
- **Do** preserve Arabic typography with zero tracking, generous line-height, and RTL-aware layout.
- **Do** make loading, success, error, retry, empty, and end states explicit and accessible.
- **Do** keep reduced-motion support for loaders, image movement, overlays, and entrance effects.

### Don't:
- **Don't** turn a surface-specific hero split, rail, or story sequence into a site-wide template.
- **Don't** use generic WordPress news grids, Netflix-copycat red and black, cold SaaS styling, or clickbait density.
- **Don't** use weak Arabic typography, tracked Arabic labels, or cramped reading measures.
- **Don't** treat film imagery as decoration or let a failed image collapse an editorial module.
- **Don't** add side-stripe borders, gradient text, default glassmorphism, identical card grids everywhere, or nested cards.
- **Don't** hide content continuation or asynchronous progress behind unreliable implicit behavior.
