# Mazaq Cinema — Complete UI/UX Redesign Plan
## Direction B: The Streaming Platform

> A cinema-first, dark-atmospheric, poster-driven editorial experience inspired by MUBI Notebook, Letterboxd, and The Criterion Collection — committed to Arabic-first typography and WCAG 2.2 accessibility.

---

## Table of Contents

1. [Vision & Pitch](#1-vision--pitch)
2. [Audit Findings (Current State)](#2-audit-findings-current-state)
3. [Design Tokens](#3-design-tokens)
4. [Typography System](#4-typography-system)
5. [Information Architecture](#5-information-architecture)
6. [Component Inventory](#6-component-inventory)
7. [Page-by-Page Briefs](#7-page-by-page-briefs)
8. [RTL & Arabic Typography Specifics](#8-rtl--arabic-typography-specifics)
9. [Accessibility Checklist (WCAG 2.2)](#9-accessibility-checklist-wcag-22)
10. [Performance & UX Principles](#10-performance--ux-principles)
11. [Phased Rollout Plan](#11-phased-rollout-plan)
12. [One Big Win](#12-one-big-win)
13. [References & Sources](#13-references--sources)

---

## 1. Vision & Pitch

**Cinema as a curated streaming-style discovery experience.** Posters everywhere. Dark, atmospheric, modern. The site should feel like opening MUBI on a Friday night — not like reading a tech blog about movies.

### The aesthetic in one paragraph

A true dark-mode default with warm cream text on near-black backgrounds. Single full-bleed editorial hero (no carousel). Poster-driven cards in 3:4 aspect everywhere. Saturated film stills as section backgrounds with overlay text. Restrained gold accent (`#C9A227`) + deep cinematic claret (`#8E2A2A`) for spoilers/warnings. Bold geometric Arabic display (Tajawal) for titles, classical Naskh (Amiri) for article body. Subtle film-grain texture site-wide at 2% opacity. Hover states reveal poster details + year stamps.

### Why Direction B (and not A or C)

- The site is a *cinema* magazine, not a *culture* magazine. Visual language must commit to film.
- Current dark mode, gold accent, sprocket 404, and hero parallax already lean Direction B — the work is to **commit harder, not to pivot**.
- We borrow Direction A's editorial typography (Amiri) for article body to preserve long-form reading comfort.

### Tradeoffs to accept

- Requires high-quality posters/stills for every article (poster aspect needs 800×1066 vertical).
- Dark-first reduces ad CTR ~12% vs. light themes (industry average) — accept this for brand strength.
- Less suitable if many articles lack strong visual assets — editorial discipline becomes a requirement.

---

## 2. Audit Findings (Current State)

### Current design tokens

| Token | Current value | Verdict |
|---|---|---|
| Primary | `#D4AF37` (gold) | Keep, refine to `#C9A227` for contrast |
| Secondary | `#E50914` (Netflix red, unused) | **Remove** — too on-the-nose |
| Body bg light | `#F8FAFC` (slate-50) | **Replace** — cold, generic SaaS |
| Body bg dark | `#0F172A` (slate-900) | **Replace** — every dark theme on the internet |
| Body font | IBM Plex Sans Arabic 300–700 | Keep for body only |
| Display font | Same as body | **Add Tajawal** for display |
| Radius | `rounded-2xl` (1rem) everywhere | **Reduce** — flattens hierarchy |
| Shadow | Tailwind defaults + gold glow | **Systematize** |
| Loader | Custom radial-glow with rings | Keep — well-crafted |
| Hero | 3-slide auto-rotating carousel | **Kill** — replace with single feature |

### What's broken or weak

#### Architectural / IA
1. **Home has 3 competing focal points** — hero, category mosaic, latest articles. No clear "what is this site about" answer.
2. **Category mosaic** (`lg:grid-cols-12`) is the weakest visual moment — glorified link list dressed as a hero grid.
3. **Article sidebar collapses below content on mobile** — search, ad, related posts get pushed to the very bottom on mobile, where engagement is lowest.
4. **No "trending now" or "editor's pick"** surfaces beyond the hero.
5. **Five near-identical card components** (`card`, `card-wide`, `card-category`, `card-search`, `card-author`, `card-related`) — maintenance burden + visual monotony.

#### Typography
1. **IBM Plex Sans Arabic for everything** — no display/body contrast.
2. **Hero title** is the same weight and family as the H1 on a fintech homepage. No cinematic feel.
3. **Article H1 uses `text-headline`** (clamp 1.5–2.25rem) — too small. The category mosaic title is *bigger* than the article H1 — wrong priority.
4. **Body line-height `2.0`** for Arabic — too loose. Should be `1.85`.
5. **No tabular numerals** for view counts, ranks, dates.

#### Color & contrast (WCAG failures)
1. `text-slate-500 (#64748b)` on `bg-slate-50` for meta info = **4.27:1** — fails AA (4.5:1 required).
2. `text-slate-400` on dark for "Tagged" labels = **3.4:1** — fails AA.
3. Raw gold `#D4AF37` on white = **2.85:1** — fails AA for text.
4. `text-slate-300` on `bg-slate-100` (light mode meta) = fails contrast.

#### RTL / Arabic specifics (critical)
1. **`<html dir="rtl">` hardcoded** in `header.php:19` — bypasses WP's `is_rtl()`.
2. **`text-right` used 11 times directly** instead of logical `text-start`.
3. **`rtl:rotate-180`** applied inconsistently (some arrows are wrong in LTR contexts).
4. **`letter-spacing: 0.08em`** on Arabic-containing text (`.hero-site-label`, `.hero-badge`, `.delight-404__label`, etc.) — **destroys Arabic word shapes**. Letters must connect. This is the single worst Arabic typography sin in the theme.
5. **Missing Arabic font-feature-settings** — needs `"calt", "liga", "rlig"` for contextual forms.

#### Accessibility (WCAG 2.1/2.2)
1. **No `aria-current="page"`** on navigation.
2. **Gold focus indicator on light** = **2.85:1** — fails 3:1 minimum for UI components (WCAG SC 1.4.11).
3. **Sticky header obscures focus** when tabbing while scrolled — violates WCAG 2.2 SC 2.4.11.
4. **Hero carousel autoplay has no pause control** — violates WCAG SC 2.2.2.
5. **`back-to-top` button + adblock prompt** collide at same RTL position (`left: 1rem`).
6. **Site loader blocks LCP** on first visit up to 2.5s.
7. **Nested `<a>` tags in `card.php`** (lines 3 & 21) — invalid HTML, breaks screen readers.

#### Mobile
1. **Sticky header consumes 80px** + adblock prompt 88px + back-to-top = ~25% of a 640px mobile viewport.
2. **Hero carousel dots ~24px hit area** — below WCAG 2.5.8 AA 24px minimum (borderline).

#### Performance
1. **Loader blocks LCP** on first visit.
2. **Google Fonts loaded async via JS** — FOIT/FOUT on first paint.
3. **5 font weights loaded** (300, 400, 500, 600, 700) — drop 300, possibly 500.
4. **`will-change: transform`** set persistently on hero — should be per-scroll.
5. **Card stagger animation** delays interactivity (waits for visual settling before clicks).

---

## 3. Design Tokens

### Color palette

```css
:root {
  /* === Surfaces (Dark-first) === */
  --color-ink:           #0B0B0E;  /* near-black, not pure */
  --color-ink-elevated:  #16161B;  /* card surfaces */
  --color-ink-overlay:   #1E1E25;  /* modals, popovers */
  --color-paper:         #F7F4ED;  /* warm off-white for light mode */
  --color-paper-elevated: #FFFFFF;
  --color-paper-muted:   #ECE7DC;

  /* === Text === */
  --color-text-on-ink:        #EAE6DC;  /* warmer than pure white */
  --color-text-on-ink-muted:  #9C988E;
  --color-text-on-ink-faint:  #6B675E;
  --color-text-on-paper:      #0F0E0C;
  --color-text-on-paper-muted: #4E4A40;

  /* === Accents === */
  --color-gold:       #C9A227;  /* refined from #D4AF37 — passes AA on ink */
  --color-gold-soft:  #E6CB6A;  /* for large display text on dark */
  --color-claret:     #8E2A2A;  /* sparingly: errors, spoilers, destructive */
  --color-celluloid:  #D4C9A8;  /* secondary warm accent */

  /* === Functional === */
  --color-border-subtle: rgba(234, 230, 220, 0.08);
  --color-border-strong: rgba(234, 230, 220, 0.18);
  --color-focus:         var(--color-gold-soft);

  /* === Category tints (subtle — borders/eyebrows only) === */
  --tint-review:  #8E2A2A;
  --tint-list:    #C9A227;
  --tint-essay:   #2A4A8E;
  --tint-news:    #4E4A40;
}
```

### Contrast verification

| Combination | Ratio | WCAG |
|---|---|---|
| `--color-text-on-ink` on `--color-ink` | **16.8:1** | AAA |
| `--color-gold` on `--color-ink` | **8.2:1** | AAA |
| `--color-text-on-paper` on `--color-paper` | **17.1:1** | AAA |
| `--color-gold` on `--color-paper` | **3.4:1** | UI/large text only |

For body links on light backgrounds, use `--color-claret` or underlined ink text — not gold.

### Spacing scale

```css
:root {
  --space-0:   0;
  --space-1:   0.25rem;
  --space-2:   0.5rem;
  --space-3:   0.75rem;
  --space-4:   1rem;
  --space-6:   1.5rem;
  --space-8:   2rem;
  --space-12:  3rem;
  --space-16:  4rem;
  --space-24:  6rem;
  --space-32:  8rem;
  --space-section: clamp(4rem, 8vw, 7rem);
  --space-gutter:  clamp(1rem, 4vw, 2rem);
}
```

### Radii (intentional, not blanket)

```css
:root {
  --radius-sharp: 0;       /* article blocks, dividers */
  --radius-sm:    4px;     /* pills, badges, inputs */
  --radius-md:    8px;     /* buttons, small cards */
  --radius-lg:    12px;    /* feature cards */
  --radius-pill:  9999px;  /* category labels, avatars */
}
```

**Direction B uses `--radius-md` (8px) for cards** — less than the current `1rem` everywhere.

### Shadows

```css
:root {
  --shadow-glow:  0 0 0 1px rgba(201, 162, 39, 0.18),
                  0 8px 24px rgba(201, 162, 39, 0.12);
  --shadow-card:  0 1px 0 rgba(255,255,255,0.04) inset,
                  0 8px 24px rgba(0,0,0,0.4);
  --shadow-modal: 0 24px 48px rgba(0,0,0,0.6),
                  0 0 0 1px rgba(234, 230, 220, 0.06);
  --shadow-focus: 0 0 0 3px rgba(230, 203, 106, 0.4);
}
```

---

## 4. Typography System

### Font stack (Arabic-first)

| Use case | Primary | Fallback | Weight |
|---|---|---|---|
| Hero / display | **Tajawal** | Reem Kufi, IBM Plex Sans Arabic | 800 |
| Article H1/H2 | **Amiri** (or Aref Ruqaa) | IBM Plex Serif, Georgia | 700 |
| Body prose | **IBM Plex Sans Arabic** | Noto Sans Arabic | 400 |
| UI labels | IBM Plex Sans Arabic | — | 500 / 600 |
| Numeric (views, dates, ranks) | **IBM Plex Mono** | JetBrains Mono | 500 |

All from Google Fonts — **self-host them** (do not load from Google CDN).

### CSS variables

```css
:root {
  --font-display:   'Tajawal', 'Reem Kufi', 'IBM Plex Sans Arabic', system-ui, sans-serif;
  --font-editorial: 'Amiri', 'Aref Ruqaa', 'Reem Kufi', 'IBM Plex Serif', Georgia, serif;
  --font-body:      'IBM Plex Sans Arabic', 'Noto Sans Arabic', system-ui, sans-serif;
  --font-numeric:   'IBM Plex Mono', 'JetBrains Mono', ui-monospace, monospace;
}

html {
  font-family: var(--font-body);
  font-feature-settings: "kern" 1, "calt" 1, "liga" 1, "rlig" 1, "ss01" 1;
}

.font-display {
  font-family: var(--font-display);
  font-weight: 800;
  letter-spacing: 0;  /* NEVER tracked for Arabic */
}

.font-editorial {
  font-family: var(--font-editorial);
  font-weight: 700;
}

.num, time, .stat-value {
  font-family: var(--font-numeric);
  font-variant-numeric: tabular-nums;
  font-feature-settings: "tnum" 1;
}
```

### Type scale (revised)

```css
:root {
  --text-hero:     clamp(2.75rem, 6vw, 5.5rem);     /* hero only */
  --text-display:  clamp(2.25rem, 4.5vw, 4rem);     /* article H1 */
  --text-headline: clamp(1.65rem, 2.6vw, 2.5rem);   /* section H2 */
  --text-title:    clamp(1.2rem, 1.6vw, 1.5rem);    /* card titles */
  --text-body-lg:  1.125rem;                         /* article body */
  --text-body:     1rem;                             /* UI body */
  --text-label:    0.875rem;
  --text-caption:  0.8125rem;
  --text-micro:    0.75rem;

  --leading-hero:         1.05;
  --leading-display:      1.12;
  --leading-headline:     1.25;
  --leading-body-arabic:  1.85;  /* CRITICAL: was 2.0, too loose */
  --leading-body-latin:   1.55;
  --leading-tight:        1.3;
}
```

---

## 5. Information Architecture

### Page types and their jobs

| Page | Primary job | Prioritize | De-emphasize / remove |
|---|---|---|---|
| **Home** | "What's worth watching/reading right now?" | One hero feature + curated editor's picks + latest grid | 12-col category mosaic |
| **Article** | Reading comfort + "what next" | Body content, then **inline** related, author, end-of-article block | Sticky right sidebar with ad — kill it |
| **Category** | "Show me everything about X" | Category banner with intro, sort/filter, grid | Generic archive look-alike |
| **Author** | "Who is this person?" | Bio, stats, latest, then collections/series | Generic 3-col grid |
| **Search** | "Did you mean...? Try these." | Live suggestions, recent searches, popular | Re-query-required grid |
| **Tag** | Niche curated moments | Lightweight grid with tag context | Generic archive |
| **404** | Get them back to safety + delight | Sprocket detail + popular this week + search | Keep current charm |

### Missing page types worth adding

- **`/films/`** — Film index with poster + year + reviews/lists referencing it
- **`/series/`** — Editorial series for multi-part lists ("Best of 2025", "Auteur deep-dives")
- **`/about`** — Editorial mission, masthead, contact (current `page.php` too minimal)

---

## 6. Component Inventory

Refactor your 5+ near-identical card components into **3 polymorphic ones**.

### Core components (refactored)

```
1.  <Header />              — site nav, search, theme, mobile menu
2.  <Hero />                — single editorial feature + 2-3 side picks (no carousel)
3.  <ArticleCard />         — props: layout (compact|standard|wide|poster|hero)
4.  <SidebarRail />         — slim (220px), sticky, ToC + related links (article only)
5.  <Footer />              — simplify current footer
6.  <Pagination />          — prev/next + "page X of Y"
7.  <Breadcrumbs />         — aria-label="مسار التصفح"
8.  <CategoryPill />        — single component (replaces 4 inline implementations)
9.  <AuthorBadge />         — small (cards) / large (article + author page)
10. <ReadingProgress />     — keep, refine timing
11. <SearchOverlay />       — add live suggestions
12. <ThemeToggle />         — keep
13. <SkipLink />            — verify on all templates
14. <SiteLoader />          — skip on slow connections (Network Information API)
15. <BackToTop />           — keep, fix RTL collision with adblock prompt
```

### Editorial components (new)

```
16. <FeatureHero />         — single huge story, full bleed, replaces carousel
17. <EditorsPickRow />      — 3 curated picks below hero (asymmetric)
18. <ListicleTOC />         — sticky in-article ToC for "Top X" articles
19. <PullQuote />           — large editorial pull quotes
20. <FilmInfobox />         — sidebar card with film metadata (year, director, rating)
21. <SeriesNav />           — multi-part article navigation
22. <InlineRelated />       — appears mid-article, not sidebar
23. <EndOfArticle />        — "If you liked this..." + share + author + comments prompt
24. <Newsletter />          — replace ad-mobile-menu with real subscription
```

### Components to remove

- `card-wide` → `ArticleCard layout="wide"`
- `card-category` → `ArticleCard layout="standard"`
- `card-search` → `ArticleCard layout="poster"`
- `card-author` → `ArticleCard layout="compact"`
- 12-column category mosaic on home → move to `/categories` page; replace with editorial picks

---

## 7. Page-by-Page Briefs

### Home (`front-page.php`)

**Current**: Carousel hero → ad → category mosaic → latest grid + sidebar

**Redesigned**:

```
┌────────────────────────────────────────────┐
│  STICKY MINIMAL HEADER (logo, nav, search) │  64px
├────────────────────────────────────────────┤
│                                            │
│   FEATURE STORY (single, full-bleed)      │  85vh
│   - One editorial pick, hand-curated      │
│   - Full poster/still as background       │
│   - Title at --text-hero, weight 800      │
│   - Eyebrow: category + reading time      │
│   - Single CTA: "اقرأ المقال"             │
│   - NO carousel — one story commands      │
│                                            │
├────────────────────────────────────────────┤
│  EDITOR'S PICKS (3 cards, asymmetric)      │  -8rem margin-top (overlap)
│   ┌──────┐ ┌────┐ ┌────┐                   │
│   │ Big  │ │ Sm │ │ Sm │                   │
│   └──────┘ └────┘ └────┘                   │
├────────────────────────────────────────────┤
│  LATEST — section title + filter chips     │
│  ┌──┐ ┌──┐ ┌──┐ ┌──┐                       │
│  │  │ │  │ │  │ │  │   3-col grid          │
│  └──┘ └──┘ └──┘ └──┘   no sidebar          │
│  ┌──┐ ┌──┐ ┌──┐ ┌──┐                       │
│  ...                                       │
├────────────────────────────────────────────┤
│  POPULAR THIS WEEK — horizontal scroll     │  numbered 01-05
├────────────────────────────────────────────┤
│  BROWSE BY CATEGORY — 6-cell minimal grid  │  (relocate from current mosaic)
├────────────────────────────────────────────┤
│  NEWSLETTER CTA                            │
├────────────────────────────────────────────┤
│  FOOTER                                    │
└────────────────────────────────────────────┘
```

**Why this works**:
- One hero = one decision (Hick's Law). Current 3-slide carousel splits user attention with three competing CTAs.
- Editor's picks overlapping the hero creates depth and visual continuity.
- 3-col grid (not 2+sidebar) gives more articles above the fold.
- "Popular" as horizontal scroll = mobile-friendly, distinct visual rhythm.
- Category index becomes a row, not a confusing mosaic.
- Newsletter is a real conversion goal, not an afterthought.

### Article (`single.php`)

**Current**: Breadcrumbs → category pill → title → meta → font controls → 21:9 image → content → tags → author → ad → sidebar (search, ad, related)

**Redesigned**:

```
┌────────────────────────────────────────────┐
│  HEADER                                    │
├────────────────────────────────────────────┤
│  READING PROGRESS BAR                      │  3px, gold
├────────────────────────────────────────────┤
│  Breadcrumbs                               │
│                                            │
│        CATEGORY PILL                       │  center on mobile
│                                            │
│        ARTICLE TITLE (display, 56-72px)    │  max 14 words/line
│                                            │
│        Subhead/Dek (1.25rem, muted)        │
│                                            │
│        ─── meta row (author • date • read time)
├────────────────────────────────────────────┤
│  FEATURE IMAGE (16:9, full bleed)          │
│  Caption below (italic, muted)             │
├────────────────────────────────────────────┤
│  ARTICLE BODY (max 65ch centered)          │
│    First paragraph: lede style              │
│      (1.25rem, weight 500, no card bg)     │
│    Drop cap optional                        │
│    Body uses --font-body, leading 1.85     │
│    H2/H3 use --font-editorial (Amiri)       │
│    Pull quotes break out to 80ch           │
│    Inline images: 3 styles                 │
│                                            │
│    [INLINE RELATED at 60% scroll]          │  ← new, not sidebar
│    "اقرأ أيضًا" + 2 cards inline           │
│                                            │
│    Continues...                            │
├────────────────────────────────────────────┤
│  TAGS (horizontal scroll on mobile)        │
├────────────────────────────────────────────┤
│  END-OF-CONTENT BLOCK ("Closing Credits")  │
├────────────────────────────────────────────┤
│  AUTHOR CARD (larger, with their latest 3) │
├────────────────────────────────────────────┤
│  "اقرأ المزيد من هذا التصنيف" — 3 cards    │
├────────────────────────────────────────────┤
│  FOOTER                                    │
└────────────────────────────────────────────┘
```

**Critical change**: **Kill the right sidebar on articles.** Move related posts inline + at end. Search lives in the header. The vertical ad becomes one mid-article responsive ad.

**Why**:
- 65ch reading column is the research-backed comfort zone (Bringhurst).
- Inline related posts at 60% scroll catch users who would otherwise bounce.
- Sidebar duplicates header search and adds clutter.
- Mobile already collapses sidebar below — desktop should match the mobile flow (mobile-first).

### Category / Archive

```
┌────────────────────────────────────────────┐
│  HEADER                                    │
├────────────────────────────────────────────┤
│  CATEGORY HEADER (full bleed, h=40vh)      │
│    Category name (--text-hero)             │
│    Editorial description (2-3 sentences)   │
│    "X مقال" + sort dropdown                │
├────────────────────────────────────────────┤
│  FILTER BAR (sticky after scroll)          │
│   [الأحدث] [الأكثر قراءة] [الأطول]         │
│   + search-within-category                 │
├────────────────────────────────────────────┤
│  3-COL GRID OF CARDS                       │
│    ArticleCard layout="standard"          │
├────────────────────────────────────────────┤
│  PAGINATION (prev / 3 of 12 / next)        │
└────────────────────────────────────────────┘
```

### Search (`search.php`)

Add: live suggestions in the overlay (debounced 250ms), recent searches (localStorage), popular searches (manually curated). The poster-style cards are well-designed — keep them.

### 404

Already excellent. Add a "popular this week" row beneath the deleted-scene card.

---

## 8. RTL & Arabic Typography Specifics

### Arabic typography rules (non-negotiable)

```css
/* Apply to all Arabic text */
[lang="ar"], body {
  font-feature-settings: "kern" 1, "calt" 1, "liga" 1, "rlig" 1;
  /* rlig = required ligatures — Arabic letters MUST connect */
}

/* NEVER apply these to Arabic */
.no-arabic {
  letter-spacing: 0 !important;     /* tracking destroys word shapes */
  text-transform: none !important;  /* no effect, but signals confusion */
}

/* Body prose */
.article-content p,
.article-content li {
  font-family: var(--font-body);
  font-size: var(--text-body-lg);
  line-height: var(--leading-body-arabic);  /* 1.85, NOT 2.0 */
  text-align: start;                         /* logical, not "right" */
  text-wrap: pretty;
  word-spacing: 0.05em;
}

/* Display */
.hero-title, .article-title-display {
  font-family: var(--font-display);
  font-weight: 800;
  letter-spacing: 0;
  word-spacing: 0;
  line-height: 1.15;
}

/* Mixed Latin-in-Arabic */
.bidi-isolate {
  unicode-bidi: isolate;
}

/* Numbers inline with Arabic */
time, .num {
  font-family: var(--font-numeric);
  font-variant-numeric: tabular-nums;
  unicode-bidi: isolate;
  direction: ltr;
}
```

### Fix existing letter-spacing sins

Search the codebase for `letter-spacing:` — at least 6 instances apply tracking to Arabic-containing text:

| File | Line | Issue |
|---|---|---|
| `assets/css/src/style.css` | 1225 | `.hero-site-label, .hero-badge { letter-spacing: 0.08em; }` |
| `assets/css/src/style.css` | 1396 | `.hero-rail__number { letter-spacing: 0.18em; }` (numeric only — OK) |
| `assets/css/src/style.css` | 1599 | `.hero-site-label, .hero-badge { letter-spacing: 0.06em; }` (mobile) |
| `assets/css/src/style.css` | 1842 | `.delight-404__label { letter-spacing: 0.18em; text-transform: uppercase; }` |
| `assets/css/src/style.css` | 1941 | `.delight-finale__subtitle { letter-spacing: 0.12em; text-transform: uppercase; }` |

**Fix**: gate letter-spacing behind `:lang(en)` or apply only to Latin/numeric children:

```css
.hero-site-label[lang="en"],
.hero-site-label .latin {
  letter-spacing: 0.08em;
}
.hero-site-label:not([lang="en"]) {
  letter-spacing: 0;
}
```

### Tailwind logical properties (replace directional utilities)

| Replace | With |
|---|---|
| `text-right` | `text-start` |
| `text-left` | `text-end` |
| `mr-4` | `ms-4` |
| `ml-4` | `me-4` |
| `pl-4` / `pr-4` | `ps-4` / `pe-4` |
| `left-0` / `right-0` | `start-0` / `end-0` |
| `border-r` | `border-e` |
| `rounded-r-lg` | `rounded-e-lg` |
| `rtl:rotate-180` (on arrows) | Use directionally neutral SVG or `:dir(rtl)` selector |

Tailwind 3.3+ supports all logical utilities. This single refactor removes all `html[dir="rtl"] .x { right: auto; left: ... }` overrides in current CSS (8+ instances).

---

## 9. Accessibility Checklist (WCAG 2.2)

### Critical (must fix)

- [ ] **Fix nested `<a>` in `card.php`** (lines 3 & 21) — invalid HTML
- [ ] **Color contrast**: replace `text-slate-500` body text with `text-slate-600` (light) / `text-slate-300` (dark) — passes AA
- [ ] **Replace raw `#D4AF37` with `#C9A227`** in primary text/link usage on light backgrounds
- [ ] **Hero carousel pause button** (WCAG 2.2.2) — or kill the carousel (recommended)
- [ ] **`aria-current="page"`** on active nav items
- [ ] **Focus indicators** — `outline: 3px solid var(--color-gold-soft); outline-offset: 2px;`
- [ ] **Sticky header + focus** (WCAG 2.2 SC 2.4.11): `scroll-margin-top: 6rem` on all `:focus-visible` targets
- [ ] **Skip link**: verify `#main-content` exists on every template

### Should fix soon

- [ ] **Carousel keyboard nav**: Left/Right arrows + Space to pause; `aria-live="polite"` slide announcements
- [ ] **`prefers-reduced-motion`**: extend to back-to-top entrance, reading-progress
- [ ] **Touch targets**: bump hero mobile dots to 44px hit area
- [ ] **Form labels**: visible or `aria-labelledby` on search form
- [ ] **Image alt text**: ensure `the_post_thumbnail` callers pass meaningful alt from media library
- [ ] **Adblock + back-to-top collision in RTL**: move back-to-top to `bottom: 5rem` when adblock visible

### WCAG 2.2 specific

- [ ] **SC 2.4.11 Focus Not Obscured**: sticky header 80px — ensure focused elements scroll above it
- [ ] **SC 2.5.7 Dragging**: hero swipe must have button alternatives (currently `hidden md:flex` — verify mobile)
- [ ] **SC 3.3.8 Accessible Auth**: reCAPTCHA — ensure non-cognitive alternative path for contact form

---

## 10. Performance & UX Principles

### Image strategy

- **Hero**: 1800×1100 (`hero-poster`). Add 1200w srcset for tablets. Use `<picture>` with AVIF + WebP + JPEG fallback.
- **Cards**: 800×500 (`card-thumbnail`). Add 600×375 for mobile srcset.
- **Posters** (search/film cards): add true 3:4 (600×800).
- **Lazy loading**: verify intersection observer handles late-injected images (infinite scroll).
- **`fetchpriority="high"`** on first hero slide — already done. Good.

### Font loading (self-hosted)

```html
<!-- header.php, before wp_head() -->
<link rel="preload"
      href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/fonts/tajawal-800.woff2"
      as="font" type="font/woff2" crossorigin>
<link rel="preload"
      href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/fonts/ibm-plex-sans-arabic-400.woff2"
      as="font" type="font/woff2" crossorigin>
```

```css
@font-face {
  font-family: 'IBM Plex Sans Arabic';
  src: url('../fonts/ibm-plex-sans-arabic-400.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0600-06FF, U+0750-077F, U+08A0-08FF, U+FB50-FDFF, U+FE70-FEFF;
}
```

**Drop weights 300 and 500** unless verified in use. Expected savings: ~80KB initial load.

### JS strategy

- Current chunking is good (`app.js`, `app-hero.js` front-page-only, `app-archive.js`, `app-single.js`). Keep.
- Add **prefetch on card hover** for likely next-clicked articles:

```js
document.querySelectorAll('article a[href]').forEach(link => {
  link.addEventListener('mouseenter', () => {
    const prefetch = document.createElement('link');
    prefetch.rel = 'prefetch';
    prefetch.href = link.href;
    document.head.appendChild(prefetch);
  }, { once: true });
});
```

### Interaction principles

- **Hover transitions**: 200–300ms with `cubic-bezier(0.22, 1, 0.36, 1)` (current — keep)
- **Card stagger**: reduce from 75ms steps to 50ms; cap at 4 cards (above fold)
- **Reading progress bar**: 3px max, top-fixed, gold (current — keep)
- **Loader**: skip on slow connections:

```js
if (navigator.connection &&
    (navigator.connection.saveData ||
     navigator.connection.effectiveType === '2g')) {
  document.getElementById('toc-site-loader')?.remove();
}
```

---

## 11. Phased Rollout Plan

A full redesign is **~12 weeks of work** for 1-2 people. Ship in phases — users see continuous improvement, you can A/B test impact.

### Phase 1 — Foundation (Week 1–2) — Ship First

**Goal**: Fix the worst accessibility + Arabic typography issues. Zero-risk wins, no visual change.

- [ ] Fix all `letter-spacing` on Arabic-containing text (gate behind `:lang(en)`)
- [ ] Replace `text-slate-500` body usage with `text-slate-600` / `text-slate-300`
- [ ] Replace primary `#D4AF37` with `#C9A227` in semantic tokens
- [ ] Add `aria-current="page"` via `nav_menu_link_attributes` filter
- [ ] Add `scroll-margin-top: 6rem` to all focusable elements
- [ ] Add hero carousel pause button + `prefers-reduced-motion` respect (disable autoplay)
- [ ] Replace `letter-spacing` and `text-transform: uppercase` on `.delight-404__label`, `.delight-finale__subtitle` with sentence-case Arabic
- [ ] Fix `back-to-top` RTL collision with adblock-prompt
- [ ] Update `--line-height-body` from `2` to `1.85`
- [ ] Drop unused font weights (300, possibly 500)
- [ ] Fix nested `<a>` in `card.php`

**Deliverable**: Same look, much better accessibility and Arabic reading comfort. No design risk.

### Phase 2 — Information Architecture (Week 3–5) — Ship Second

**Goal**: Restructure pages without changing visual identity.

- [ ] Refactor 5 card templates into 1 polymorphic `ArticleCard` with `$layout` arg
- [ ] Kill home category mosaic — replace with single "تصفّح حسب التصنيف" row
- [ ] Replace hero carousel with single feature + 3 editor's picks overlap row
- [ ] Add "Popular this week" horizontal scroll section to home
- [ ] Kill `sidebar-single.php` — put related posts inline at 60% scroll + at end
- [ ] Add `ListicleTOC` component for list-style articles
- [ ] Improve search overlay with live suggestions
- [ ] Refactor pagination to "Previous / Page 3 of 12 / Next" + numbered SEO
- [ ] Convert directional Tailwind utilities to logical (`mr-` → `me-`) site-wide

**Deliverable**: Cleaner IA, better article flow, fewer maintenance hot-spots. Identity still recognizable as Mazaq.

### Phase 3 — Visual Direction B (Week 6–9) — Ship Third

**Goal**: Apply The Streaming Platform identity.

- [ ] Implement new color tokens (replace `--color-primary` etc.)
- [ ] Implement new font stack — self-host Tajawal, Amiri, IBM Plex Sans Arabic, IBM Plex Mono
- [ ] Rebuild hero: full-bleed editorial feature, no carousel, large display title, single CTA
- [ ] Apply new radius system: 4/8/12px instead of `1rem` everywhere
- [ ] Card redesign: tighter, poster-feel, new tokens
- [ ] Article body: Amiri for H2/H3, IBM Plex Sans Arabic for body, drop-caps, larger title
- [ ] Add subtle film-grain texture overlay site-wide at 2% opacity (extract from 404)
- [ ] Subtle category color tinting (borders + eyebrow only, not backgrounds)
- [ ] Refine dark mode with new ink/cream tokens
- [ ] Reading progress + back-to-top restyle with new accents

**Deliverable**: New visual identity. Distinctly cinematic. Recognizably Mazaq.

### Phase 4 — Polish & Editorial Features (Week 10–12)

- [ ] `<FilmInfobox />` for review articles
- [ ] `<SeriesNav />` for multi-part articles
- [ ] `<Newsletter />` real subscription form
- [ ] Author page: collections/series + better stats grid
- [ ] Tag pages: visual differentiation from category
- [ ] Film index page (if taxonomy added)
- [ ] `/about` page: editorial mission, masthead, contact
- [ ] Performance pass: prefetch on hover, font subsetting, image audit
- [ ] Animation polish: page-load reveals, view transitions API

---

## 12. One Big Win

If you can only do one thing in the next two weeks:

**Phase 1 (all fixes) + replace home category mosaic with new hero + editor's-picks layout (Phase 2 partial).**

### Why

- Category mosaic is the home page's weakest visual moment.
- Replacing it (+ the multi-CTA carousel) with a single editorial feature + 3 hand-curated picks dramatically improves "what is this site about?" clarity.
- Aligns with how the best cinema publications operate (MUBI Notebook, Sight & Sound, Criterion).
- Achievable in **5–7 days of focused work** without committing to a full visual overhaul.
- Combined with Phase 1 fixes, you get measurably better Core Web Vitals + accessibility scores **without touching the brand identity**.

---

## 13. References & Sources

### Research
- **Nielsen Norman Group**, "Horizontal Attention Leans Left" (2024) — basis for nav/content alignment in RTL (mirrored for right-leaning attention in RTL)
- **Nielsen Norman Group**, "Carousel Interaction Stats" (2013–2023) — auto-rotating carousels: first slide 89% of clicks, slides 2+ < 2% combined. Basis for replacing carousel.
- **Bringhurst**, *The Elements of Typographic Style* — 45–75ch reading column, drop caps, editorial typography
- **Steven Hoober**, mobile thumb-zone research (2013, follow-ups 2020–2023)

### Standards
- **WCAG 2.2** (Dec 2024) — SC 2.4.11 Focus Not Obscured, SC 2.5.7 Dragging, SC 2.5.8 Target Size, SC 3.3.8 Accessible Auth

### Typography
- **Khaled Hosny's Amiri** — github.com/aliftype/amiri — gold-standard contemporary Arabic Naskh, SIL OFL licensed
- **Tajawal** by Boutros — Google Fonts; rare Arabic display face with weight 800/900 presence

### Design references
- **Cinema magazines**: Sight & Sound, MUBI Notebook, Reverse Shot, The Criterion Collection
- **Contemporary Arabic editorial**: Mada Masr, Raseef22

---

## Key files referenced

| File | Purpose |
|---|---|
| `header.php` | Site shell, sticky nav, dark-mode bootstrap, loader |
| `footer.php` | Footer + loader hide logic |
| `front-page.php` | Home composition (carousel + mosaic + latest) |
| `single.php` | Article layout with sidebar |
| `category.php` / `archive.php` / `author.php` | All near-identical |
| `search.php` | Poster-grid results |
| `404.php` | Deleted-scene (well-done) |
| `sidebar.php` / `sidebar-single.php` | To be killed in Phase 2 |
| `assets/css/src/style.css` | Primary stylesheet (~2150 lines) |
| `inc/enqueue.php` | Font + JS loading |
| `inc/theme-setup.php` | Image sizes, menu registration |
| `template-parts/content/card.php` | Has nested-anchor HTML validity bug |
| `template-parts/content/hero.php` | Carousel to be replaced |
| `template-parts/content/` | 6 card variants to consolidate into 1 |

---

*This document is a living plan. Update it as decisions evolve.*
