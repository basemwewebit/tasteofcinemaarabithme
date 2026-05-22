---
name: "Mazaq Cinema"
description: "A dark, poster-led Arabic cinema magazine system for cinematic discovery and long-form reading."
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
  paper-muted: "#ECE7DC"
  warm-ash: "#ECE7DC"
  mist: "#E2E8F0"
  pewter: "#94A3B8"
  charcoal: "#475569"
  text-on-ink: "#EAE6DC"
  text-on-ink-muted: "#9C988E"
  text-on-paper: "#0F0E0C"
  text-on-paper-muted: "#4E4A40"
  soft-white: "#FAFBFE"
typography:
  display:
    fontFamily: "Tajawal, Reem Kufi, IBM Plex Sans Arabic, system-ui, sans-serif"
    fontSize: "clamp(2.75rem, 6vw, 5.5rem)"
    fontWeight: 800
    lineHeight: 1.08
    letterSpacing: "0"
  headline:
    fontFamily: "Tajawal, Reem Kufi, IBM Plex Sans Arabic, system-ui, sans-serif"
    fontSize: "clamp(1.5rem, 3vw, 2.25rem)"
    fontWeight: 800
    lineHeight: 1.25
    letterSpacing: "0"
  title:
    fontFamily: "Tajawal, Reem Kufi, IBM Plex Sans Arabic, system-ui, sans-serif"
    fontSize: "1.25rem"
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
    fontSize: "0.875rem"
    fontWeight: 700
    lineHeight: 1.4
    letterSpacing: "0"
  numeric:
    fontFamily: "IBM Plex Mono, JetBrains Mono, ui-monospace, monospace"
    fontSize: "0.875rem"
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
    textColor: "{colors.ink}"
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
    backgroundColor: "{colors.warm-ash}"
    textColor: "{colors.charcoal}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "0.45rem 0.9rem"
    height: "2.5rem"
  search-input:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.text-on-ink}"
    typography: "{typography.body}"
    rounded: "{rounded.lg}"
    padding: "1rem 3.5rem 1rem 1.25rem"
    height: "3.75rem"
---

# Design System: Mazaq Cinema

## 1. Overview

**Creative North Star: "The Streaming Editorial House"**

Mazaq Cinema is a dark, poster-led Arabic-first editorial system. It should feel like entering a curated cinema room: warm gold glints, near-black surfaces, cream text, bold Arabic titles, and film imagery that carries the atmosphere rather than decorating the page.

The system is brand-register, not utility-register. Design creates the publication's credibility. It uses streaming-platform immediacy for discovery, magazine pacing for reading, and Arabic typographic care for trust. It rejects generic WordPress news and blog templates, Netflix-copycat red and black cliches, cold SaaS styling, weak Arabic typography, cluttered entertainment portals, clickbait visual systems, and film imagery treated as filler.

**Key Characteristics:**
- Dark-first cinematic surfaces with warm, tinted neutrals.
- Poster and still imagery as primary editorial material.
- Tajawal for confident display, Amiri for long-form editorial headlines, IBM Plex Sans Arabic for body clarity.
- Gold used as a rare editorial signal, not decoration.
- Rounded but not soft: 8px to 12px corners, pill chips only where the affordance is label-like.
- Motion is atmospheric and restrained, using scale, opacity, and image treatment rather than layout movement.

## 2. Colors

The palette is a committed cinematic system: near-black ink and warm paper carry most surfaces, refined gold marks importance, and claret exists only for sharp editorial or semantic moments.

### Primary
- **Projector Gold**: the primary accent for CTAs, category emphasis, focus glow, ranks, and selected editorial signals. Its value comes from rarity.
- **Lamp Gold**: the lighter gold for dark surfaces, loader glow, and small text that needs stronger contrast.

### Secondary
- **Censor Claret**: a deep red reserved for spoilers, warnings, destructive states, or editorial moments that need tension. Never use it as a Netflix imitation.
- **Celluloid Beige**: a secondary warm accent for borders, separators, and quiet editorial framing.

### Neutral
- **Nocturnal Ink**: the default dark-stage background.
- **Midnight Surface**: elevated dark panels, cards, overlays, and newsletter containers.
- **Deep Shadow**: hero gradients, image fallbacks, and cinematic depth.
- **Warm Paper**: light-mode editorial surface, not generic white.
- **Warm Ash**: muted light containers, tags, and quiet content blocks.
- **Text Cream**: primary text on dark surfaces.
- **Pewter and Charcoal**: metadata, captions, and supporting text.

### Named Rules
**The Gold Is Rare Rule.** Gold is a signal, not trim. If every card, heading, and icon is gold, nothing is important.

**The No Netflix Costume Rule.** Claret may create editorial tension, but never combine it with black as a red-streaming-service costume.

**The Warm Neutral Rule.** New neutrals should stay warm and cinematic. Do not add cold slate surfaces unless preserving an existing legacy state.

## 3. Typography

**Display Font:** Tajawal, with Reem Kufi and IBM Plex Sans Arabic fallbacks.  
**Body Font:** IBM Plex Sans Arabic, with Noto Sans Arabic and system fallbacks.  
**Editorial Font:** Amiri, with Aref Ruqaa, IBM Plex Serif, Georgia, and serif fallbacks.  
**Numeric Font:** IBM Plex Mono, with JetBrains Mono and ui-monospace fallbacks.

**Character:** The pairing is cinematic but legible. Tajawal gives section titles and hero lines a contemporary Arabic poster voice; Amiri slows article titles into magazine mode; IBM Plex Sans Arabic keeps body text and UI clear.

### Hierarchy
- **Display** (800, fluid hero scale, 1.08): home hero titles and high-drama entry points only.
- **Headline** (800, fluid headline scale, 1.25): section titles, related content groups, and editorial feature headings.
- **Title** (700, 1.25rem, 1.4): card titles and compact editorial modules.
- **Editorial** (700, fluid display scale, 1.16): single article H1s and long-form title moments.
- **Body** (400, 1.125rem, 1.85): article prose, descriptions, summaries, and reading surfaces. Keep long-form text near 65 to 75ch.
- **Label** (700, 0.875rem, no tracking): Arabic labels, eyebrows, chips, and categories. Do not letter-space Arabic.
- **Numeric** (500, tabular, isolated bidi): dates, ranks, view counts, and reading time.

### Named Rules
**The Arabic Letterforms Rule.** Arabic labels do not get tracked uppercase treatment. Preserve connected forms with zero letter-spacing, contextual alternates, ligatures, and RTL-aware spacing.

**The Reading Room Rule.** Long-form pages prioritize measure, line-height, and quiet hierarchy over decorative flourishes.

## 4. Elevation

The system uses hybrid elevation: dark mode relies on tonal layering and subtle inset light, while light mode allows soft cinematic lift for image-led editorial cards. Shadows should feel like theater ambient light, not app chrome.

### Shadow Vocabulary
- **Card Shadow** (`0 1px 0 rgba(255,255,255,0.04) inset, 0 8px 24px rgba(0,0,0,0.4)`): dark elevated cards and panels.
- **Editor Card Lift** (`0 18px 45px rgba(15, 23, 42, 0.14)`): light-mode editor picks and featured cards.
- **Focus Glow** (`0 0 0 3px rgba(230, 203, 106, 0.42)`): keyboard focus and critical interactive outlines.

### Named Rules
**The Screen Depth Rule.** Depth comes from imagery, tonal layers, and controlled shadows. Do not stack nested cards to fake hierarchy.

**The Focus Is Not Optional Rule.** Focus rings must remain visible on both warm paper and nocturnal ink.

## 5. Components

### Buttons
- **Shape:** Gently curved, editorial-control shape (8px radius).
- **Primary:** Projector Gold background with Nocturnal Ink text, heavy label weight, and at least 3rem height.
- **Hover / Focus:** Use subtle brightness and 1px to 2px translate movement. Keep focus outline distinct from hover.
- **Secondary / Ghost:** Use text or border treatments when an action is not primary. Do not create a row of gold buttons.

### Chips
- **Style:** Pill labels for categories, tags, and search suggestions. Light mode uses Warm Ash and Charcoal; dark mode uses Midnight Surface and Text Cream or Pewter.
- **State:** Hover changes border or text color toward gold. Selected chips may use a gold tint, but should not become miniature buttons.

### Cards / Containers
- **Corner Style:** 12px for editorial cards, 8px for smaller category tiles.
- **Background:** Light cards use translucent warm surfaces; dark cards use Midnight Surface or Ink Elevated.
- **Shadow Strategy:** Dark cards use Card Shadow. Light feature cards may use Editor Card Lift.
- **Border:** Quiet warm or pewter border. Gold border appears only on hover, focus, or selected editorial emphasis.
- **Internal Padding:** 1rem to 1.25rem for compact cards, 1.5rem to 2rem for newsletter and feature containers.

### Inputs / Fields
- **Style:** Search and newsletter inputs use rounded rectangular fields, warm border, and high-contrast text.
- **Focus:** Gold focus ring or border shift, never outline removal.
- **Error / Disabled:** Disabled states reduce opacity only when the control remains understandable. Error states should use claret with text, not color alone.

### Navigation
- **Style:** Sticky header, compact text, strong focus states, and logo-centered identity. Dark and light headers stay slightly translucent but should not become decorative glass.
- **Mobile:** Menu and search interactions must preserve focus trap behavior, 44px touch targets, and clear close affordances.

### Feature Hero
The hero is the signature component. It uses a full-bleed image, dark gradient shade, subtle grain, a single editorial title, and a restrained CTA. The image may scale and brighten on hover, but the text remains steady.

### Editor Picks
Editor picks break the equal-card grid with one larger lead card and supporting cards. This is the preferred pattern for curation: editorial hierarchy first, grid regularity second.

### Search Overlay
Search is theatrical but functional: large Arabic title, high-contrast input, suggested chips, and live suggestions with thumbnails. The overlay should feel like browsing an archive, not opening a utility modal.

## 6. Do's and Don'ts

### Do:
- **Do** keep cinema imagery dominant on discovery surfaces; posters and stills are editorial evidence.
- **Do** use Projector Gold sparingly for CTAs, ranks, focus, and category emphasis.
- **Do** preserve Arabic typography with zero tracking, strong line-height, and RTL-aware layout.
- **Do** use numeric isolation and tabular numerals for dates, ranks, views, and reading time.
- **Do** use curation patterns: featured article, editor picks, most-read strip, category paths, and related-reading modules.
- **Do** keep reduced-motion support for loaders, image movement, overlays, and scroll interactions.

### Don't:
- **Don't** use generic WordPress news and blog templates.
- **Don't** use Netflix-copycat red and black cliches.
- **Don't** use cold SaaS styling.
- **Don't** use weak Arabic typography, tracked Arabic labels, or cramped reading measures.
- **Don't** use cluttered entertainment portals or clickbait visual systems.
- **Don't** treat film imagery as decoration instead of editorial substance.
- **Don't** add side-stripe borders, gradient text, default glassmorphism, identical card grids everywhere, or nested cards.
- **Don't** make modals the first answer. Exhaust inline, overlay, and progressive alternatives first.
