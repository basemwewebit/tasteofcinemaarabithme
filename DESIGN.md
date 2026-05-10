---
name: مذاق السينما
description: A cinematic Arabic editorial experience where light sculpts content out of darkness.
colors:
  primary: "#D4AF37"
  primary-hover: "#C5A028"
  primary-tint: "#F8E4A1"
  primary-border: "#F5E5A7"
  primary-cream: "#FFF8E1"
  secondary: "#E50914"
  nocturnal-slate: "#0F172A"
  midnight: "#1E293B"
  deep-shadow: "#020617"
  void: "#030712"
  screen-light: "#F8FAFC"
  warm-ash: "#F1F5F9"
  mist: "#E2E8F0"
  pewter: "#94A3B8"
  charcoal: "#475569"
  nocturnal: "#0F172A"
  slate-50: "#F8FAFC"
  slate-100: "#F1F5F9"
  slate-200: "#E2E8F0"
  slate-300: "#CBD5E1"
  slate-400: "#94A3B8"
  slate-500: "#64748B"
  slate-600: "#475569"
  slate-700: "#334155"
  slate-800: "#1E293B"
  slate-900: "#0F172A"
  slate-950: "#020617"
typography:
  display:
    fontFamily: '"IBM Plex Sans Arabic", sans-serif'
    fontSize: "clamp(2.1rem, 4.5vw, 4.25rem)"
    fontWeight: 700
    lineHeight: 1.08
    letterSpacing: "normal"
  headline:
    fontFamily: '"IBM Plex Sans Arabic", sans-serif'
    fontSize: "clamp(1.5rem, 3vw, 2.25rem)"
    fontWeight: 700
    lineHeight: 1.25
    letterSpacing: "normal"
  title:
    fontFamily: '"IBM Plex Sans Arabic", sans-serif'
    fontSize: "1.25rem"
    fontWeight: 700
    lineHeight: 1.4
    letterSpacing: "normal"
  body:
    fontFamily: '"IBM Plex Sans Arabic", sans-serif'
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: 2
    letterSpacing: "normal"
  label:
    fontFamily: '"IBM Plex Sans Arabic", sans-serif'
    fontSize: "0.875rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "0.08em"
rounded:
  sm: "0.5rem"
  md: "0.75rem"
  lg: "1rem"
  xl: "1.75rem"
  full: "9999px"
spacing:
  xs: "0.5rem"
  sm: "1rem"
  md: "1.5rem"
  lg: "2.5rem"
  xl: "4rem"
  2xl: "6rem"
  section: "clamp(3rem, 6vw, 5rem)"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.nocturnal-slate}"
    rounded: "{rounded.md}"
    padding: "0.5rem 0.85rem"
  button-primary-hover:
    backgroundColor: "#C5A028"
  button-ghost:
    backgroundColor: "rgba(15, 23, 42, 0.34)"
    textColor: "{colors.screen-light}"
    rounded: "{rounded.full}"
    padding: "0.85rem"
  button-ghost-hover:
    backgroundColor: "rgba(212, 175, 55, 0.14)"
  card-article:
    backgroundColor: "{colors.slate-100}"
    textColor: "{colors.slate-800}"
    rounded: "{rounded.lg}"
    padding: "0"
  card-article-dark:
    backgroundColor: "{colors.slate-800}"
    textColor: "{colors.slate-200}"
  badge:
    backgroundColor: "rgba(212, 175, 55, 0.16)"
    textColor: "#F8E4A1"
    rounded: "{rounded.full}"
    padding: "0.4rem 0.9rem"
  nav-link:
    textColor: "{colors.slate-600}"
    typography: "{typography.label}"
    padding: "0.5rem 0"
  nav-link-hover:
    textColor: "{colors.primary}"
---

# Design System: مذاق السينما

## 1. Overview: The Chiaroscuro Gallery

**Creative North Star: "The Chiaroscuro Gallery"**

The interface is not a container for content; it is the lighting that sculpts content out of darkness. Every surface exists in a state of controlled luminance: deep shadows provide rest for the eye, while precise, warm highlights draw attention to what matters. The mood is intentional, expensive, and reverent.

This system rejects the clutter of generic news portals and the raw utility of database-driven layouts. It also rejects neon saturation and aggressive motion. The aesthetic philosophy is one of quiet authority: the design knows it is beautiful and does not need to shout. The Arab cinephile enters a lean-back, winding-down experience where discovery feels like wandering a curated gallery at dusk.

**Key Characteristics:**
- **Chiaroscuro-first:** Contrast is not an afterthought; it is the primary design grammar.
- **Ambient depth:** Elevation is conveyed through atmosphere (glow, haze, blur), not structural stacking.
- **Whisper-light precision:** Interactions are sharp, fast, and subtle. No chunky buttons, no heavy shadows.
- **RTL-native:** Arabic right-to-left reading order is the default, not an adaptation.
- **Respectful motion:** Cinematic motion serves the experience, never overrides `prefers-reduced-motion`.

## 2. Colors: The Palette of Prestige

The palette is built on a single principle: darkness is the default state, and light is earned. Aged Gold Leaf is the sole emotional accent; its rarity is the point.

### Primary
- **Aged Gold Leaf** (`#D4AF37`): The emotional and functional accent. Used for focus rings, active navigation states, progress bars, loader glows, and the rare primary CTA. On dark backgrounds, it reads as warm luminescence. On light backgrounds, it reads as craft and permanence. It must never exceed 10% of any given viewport.

### Secondary
- **Featured Red** (`#E50914`): A sparing, functional accent reserved for "featured" or "new" badges and urgent calls-to-action only. It carries the energy of a premiere red carpet, not a warning sign. Use it on ≤2% of any screen.

### Neutral
- **Screen Light** (`#F8FAFC`): The light-mode ground. A very pale slate with a cool undertone, evoking the faint glow of a projector on a white screen. Used for body backgrounds in light mode.
- **Warm Ash** (`#F1F5F9`): Secondary light surface. Used for cards, input backgrounds, and subtle container differentiation in light mode.
- **Mist** (`#E2E8F0`): Borders, dividers, and disabled states in light mode. Hairline only.
- **Pewter** (`#94A3B8`): Secondary text, meta information, captions, and placeholders.
- **Charcoal** (`#475569`): Primary body text in light mode.
- **Nocturnal Slate** (`#0F172A`): The dark-mode ground. The deepest surface, used for hero shells, dark body backgrounds, and cinematic voids.
- **Midnight** (`#1E293B`): Secondary dark surface. Used for cards, elevated containers, and input backgrounds in dark mode.
- **Deep Shadow** (`#020617`): The absolute dark. Reserved for vignettes, cinematic overlays, and the deepest possible surface.

### Named Rules
**The One Accent Rule.** Aged Gold Leaf is used on ≤10% of any given screen. Its rarity is the point. If you find yourself using it for more than focus states, active indicators, and one primary CTA per view, you have diluted the prestige.

**The Dark-First Rule.** Design for the dark palette first, then derive the light palette. The physical scene is an Arab cinephile browsing on a tablet at night; the default ambient light is low.

### Semantic Tokens

The primitive colors above compose into semantic tokens that flip automatically between light and dark mode. New components should use these instead of referencing primitives directly.

| Token | Light | Dark |
|---|---|---|
| `--text-heading` | Nocturnal Slate | Screen Light |
| `--text-body` | Charcoal | Slate 300 |
| `--text-meta` | Slate 500 | Pewter |
| `--text-on-dark` | Screen Light | Nocturnal Slate |
| `--text-on-dark-secondary` | Slate 300 | Charcoal |
| `--text-on-dark-muted` | Pewter | Slate 500 |
| `--surface-body` | Screen Light | Nocturnal Slate |
| `--surface-card` | Warm Ash | Midnight |
| `--surface-dark` | Nocturnal Slate | Screen Light |
| `--surface-dark-elevated` | Midnight | Warm Ash |
| `--border-light` | Mist | Slate 700 |
| `--border-dark` | Slate 700 | Mist |

## 3. Typography

**Display & Body Font:** IBM Plex Sans Arabic (with system-ui, -apple-system, sans-serif fallback)

**Character:** A single sans-serif family with humanist proportions and Arabic-specific refinements. It is warm enough for long-form reading yet geometric enough for precise UI labels. The personality is confident and contemporary, never decorative.

### Hierarchy
- **Display** (700, clamp(2.1rem, 4.5vw, 4.25rem), line-height 1.08): Hero headlines only. Reserved for the largest cinematic moments. Maximum one per viewport.
- **Headline** (700, clamp(1.5rem, 3vw, 2.25rem), line-height 1.25): Section titles, article headings, category headers. The workhorse of editorial hierarchy.
- **Title** (700, 1.25rem, line-height 1.4): Card titles, sidebar widget headings, sub-section labels.
- **Body** (400, 1.125rem, line-height 2): Article paragraphs, descriptions, long-form content. Capped at 65–75ch per line for optimal Arabic readability.
- **Label** (600, 0.875rem, letter-spacing 0.08em): Navigation links, badges, metadata, buttons. Always uppercase or small-caps in Latin contexts; Arabic labels maintain normal case with increased tracking for air.

### Named Rules
**The Breath Rule.** Arabic text needs more line-height than Latin. Body copy is set to line-height 2.0. Never compress Arabic body text below 1.75.

## 4. Elevation: Ambient Theater Haze

This system uses no structural, Material-style shadows. Depth is conveyed entirely through atmosphere: diffuse glows, layered backdrop blurs, and tonal separation. Elements do not "float" above surfaces; they emerge from shadow into light, as if moving through a haze-filled theater.

### Shadow Vocabulary
- **Hero Aura** (`0 30px 80px rgba(15, 23, 42, 0.26)`): The ambient field around the hero section. Diffuse, large-radius, low opacity. Creates a soft boundary between the cinematic hero and the content below.
- **Content Glow** (`0 20px 50px rgba(15, 23, 42, 0.2)`): The subtle lift under hero content panels and featured cards. Barely perceptible, but prevents flatness.
- **Accent Bloom** (`0 4px 12px rgba(212, 175, 55, 0.4)`): A warm, directional glow used exclusively for the floating "back to top" button and primary CTAs. This is the only gold-tinted shadow in the system.
- **Dark Vignette** (`inset 0 0 9rem rgba(2, 6, 23, 0.35)`): An inner shadow used inside hero shells to draw the eye toward the center of the frame. Cinematic, not decorative.

### Named Rules
**The No-Plastic Rule.** If a shadow looks like it could exist in a productivity app (Trello, Asana, Gmail), it is forbidden. Shadows must feel like light behavior, not elevation mechanics.

## 5. Components

Every component is whisper-light and precise. Because the imagery and color palette are heavy and dramatic, interactions provide balance through sharp borders, hairline strokes, and high-speed, subtle transitions.

### Buttons
- **Shape:** Pill (`9999px`) for icon-only ghost buttons; gently rounded (`0.75rem`) for text CTAs.
- **Primary:** Aged Gold Leaf background (`#D4AF37`), Nocturnal Slate text (`#0F172A`), padding `0.5rem 0.85rem`, font-weight 700, font-size `0.85rem`. Used for the dominant action only.
- **Hover / Focus:** Background shifts to a slightly deeper gold (`#C5A028`). Transition is `filter 0.2s ease` (brightness reduction) rather than a color swap. No transform scale.
- **Ghost:** Transparent background with `rgba(15, 23, 42, 0.34)` fill, 1px border in `rgba(255, 255, 255, 0.14)`, Screen Light icon/text. Used for carousel controls and secondary actions.
- **Ghost Hover:** Background shifts to `rgba(212, 175, 55, 0.14)`, border to `rgba(212, 175, 55, 0.38)`, text to pure white. Transition `250ms ease`.

### Badges / Chips
- **Style:** Pill shape (`9999px`), background `rgba(212, 175, 55, 0.16)`, text `#F8E4A1`, 1px border `rgba(212, 175, 55, 0.24)`. No backdrop-filter on card badges (flat tint is sufficient). Hero badges may use `backdrop-filter: blur(18px)` where they overlay cinematic imagery.
- **Typography:** Label style, font-size `0.76rem`, font-weight 600, letter-spacing `0.08em`.
- **Usage:** Category labels inside hero content, card thumbnails, "featured" markers, and editorial tags. Never used for interactive filters.

### Cards / Containers
- **Corner Style:** `1rem` radius for article cards. `1.75rem` for hero content panels.
- **Background:** Warm Ash (`#F1F5F9`) in light mode; Midnight (`#1E293B`) in dark mode.
- **Shadow Strategy:** None at rest. Cards are flat. On hover, a `0 20px 50px` Content Glow may appear if the card is featured. Otherwise, hover is signaled by a subtle border shift to Aged Gold Leaf (`1px solid rgba(212, 175, 55, 0.3)`) and a `0.3s ease` transition.
- **Border:** `1px solid #E2E8F0` (light) or `1px solid #334155` (dark) at rest.
- **Internal Padding:** `1.5rem` for standard cards; `clamp(1.25rem, 2vw, 2rem)` for hero panels.

### Inputs / Fields
- **Style:** No visible background at rest. Bottom border only: `1px solid rgba(255, 255, 255, 0.14)` for search overlays on dark; `1px solid #E2E8F0` on light. Full-radius (`9999px`) for search fields.
- **Focus:** Border color shifts to Aged Gold Leaf. No glow, no ring. The transition is immediate and sharp.
- **Error / Disabled:** Not prominently used in this editorial surface. If needed, disabled uses Pewter text on transparent background.

### Navigation
- **Style:** Sticky header, `80px` height (`h-20`). Background is `rgba(255, 255, 255, 0.8)` in light mode, `rgba(15, 23, 42, 0.8)` in dark mode, with `backdrop-filter: blur(12px)`.
- **Typography:** Label style, font-weight 600, font-size `0.875rem`.
- **Default:** Pewter text (`#64748B` light / `#94A3B8` dark).
- **Hover:** Aged Gold Leaf text (`#D4AF37`), transition `0.2s ease`.
- **Active:** Aged Gold Leaf text with a `2px` bottom border indicator.
- **Mobile:** Hamburger trigger opens a full-screen overlay with large Display-style links, Nocturnal Slate background.

### Cinematic Hero (Signature Component)
- **Structure:** A full-bleed shell with `min-height: calc(100svh - 5rem)`. Contains a layered overlay system: shade gradient, glow accents, mesh texture, and vignette.
- **Media:** Featured image with `object-fit: cover`, desaturated and darkened via `filter: saturate(0.98) contrast(1.08) brightness(0.84)`. Ken Burns scale effect on active slides (`scale(1.09)` to `scale(1.14)`).
- **Content Panel:** Absolute-positioned at bottom, `1.75rem` radius, glass-like background (`linear-gradient(135deg, rgba(255,255,255,0.16), rgba(255,255,255,0.08))` over `rgba(15,23,42,0.22)`), `backdrop-filter: blur(4px)`.
- **Typography:** Display for headline, body size for excerpt, label style for metadata. All text is white or near-white with subtle text-shadow for legibility against variable imagery.
- **Controls:** Ghost-style circular buttons for prev/next. Progress bars with gold-to-white gradient fill. Rail navigation on desktop (left side, numbered thumbnails).

## 6. Do's and Don'ts

### Do:
- **Do** design for the dark palette first. The physical scene is low ambient light.
- **Do** use Aged Gold Leaf exclusively for focus rings, active states, one primary CTA per view, and progress indicators.
- **Do** maintain `line-height: 2` for all Arabic body text.
- **Do** use `backdrop-filter: blur()` for atmospheric panels, not for decorative glass cards.
- **Do** cap body text at 65–75ch per line.
- **Do** respect `prefers-reduced-motion` by disabling all cinematic animations and transitions.
- **Do** use diffuse, large-radius shadows that feel like theater haze, never sharp offset shadows.

### Don't:
- **Don't** use border-left or border-right greater than 1px as a colored accent on cards, callouts, or lists. This is an absolute ban. Use full borders, background tints, or leading icons instead.
- **Don't** use gradient text (`background-clip: text`). Decorative, never meaningful. Use a single solid color and emphasize via weight or size.
- **Don't** use glassmorphism as a default. Blurs and glass cards are rare and purposeful, or nothing.
- **Don't** use the hero-metric template (big number, small label, supporting stats, gradient accent). SaaS cliché.
- **Don't** create identical card grids (same-sized cards with icon + heading + text, repeated endlessly).
- **Don't** use modals as a first thought. Exhaust inline and progressive alternatives first.
- **Don't** design like a generic news portal. No cluttered sidebars, red breaking-news tickers, or clickbait density.
- **Don't** create a "Letterboxd" utility feel. No heavy grids of raw poster data, star ratings, or database aesthetics.
- **Don't** use neon or gamer aesthetics. No high-saturation neons, fast animations, or aggressive motion. Think opera house, not gaming lounge.
- **Don't** use em dashes. Use commas, colons, semicolons, periods, or parentheses.
