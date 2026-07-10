# HISTORY.md — Development Biography & Git Archaeology

*A reconstruction of Mazaq Cinema's development, read from 95 commits across `main`, the merged PR history, and the `001-site-ui-updates` branch. Sole author throughout: **Basem** (committing as both `Basem` and `Basem Nassar`/`basemwewebit`). Timeline: **2026-03-02 → 2026-06-24**.*

---

## Act I — Genesis: from a design slice to a working theme (Mar 2–5)

The project does not begin with code. It begins with a **specification**. The first commit, `b7bc831 "add-spec"` (Mar 2), lays down intent before implementation — and the next two, `1ca88de`/`51ddb72 "add-spec-plan-slice"`, add a plan. This is a **spec-driven workflow** (the "001-" branch prefixes and `docs(...): generate implementation tasks` messages are the fingerprints of a spec-kit-style process), and it sets the tone for the whole project: nearly every feature that follows arrives on its own `001-<feature>` branch through a pull request.

The foundational leap is `e3c1880 "001-slice-to-wp-theme"`, merged as **PR #1** on Mar 3 — a static design "slice" converted into a live WordPress theme. Within the next 48 hours the theme's skeleton fills in through a rapid PR train:

- **PR #2** — first single-post fixes
- **PR #3** — home layout
- **PR #4** — archive ads injection
- **PR #5** — loader + ads
- **PR #6** — contact-form enhancements

Each is small, branch-isolated, and merged cleanly. This is a developer who has a plan and is executing it in disciplined slices. The commit messages here are the best they will ever be (`feat(hero): implement hero carousel...`, `fix(infinite-scroll): replace scroll event with IntersectionObserver for better mobile support`) — conventional-commit style, scoped, and explanatory.

Two engineering decisions from this act deserve a footnote because they signal care:
- **PR #7 (reCAPTCHA v3/Enterprise)** shipped with its own design docs *and a recorded clarification* — `1c86252 docs(...): record clarification to fail open on api timeout`. Someone thought about the failure mode (don't lock users out if Google is slow) before writing the verifier.
- **PR #8** swapped a scroll-event infinite-scroll for an `IntersectionObserver` specifically citing mobile behavior. **PR #9** generalized the single-hero into a multi-sticky carousel and — notably — fixed the *knock-on* bug in the same breath (`d692981 exclude all carousel sticky posts from articles grid`), showing the author traces a change to its side effects.

By `3a10dac` (Mar 5) the carousel merges and the foundation is essentially complete. Nine PRs in four days.

---

## Act II — The feature-accretion sprint (Mar 6–18)

With the skeleton standing, the project enters a fortnight of relentless feature addition. The tidy conventional-commit discipline of Act I now gives way to terse, imperative one-liners — `add-share-page`, `add-edit-date`, `add-tags-style`, `add-random-post`, `add-ad-support`, `add-widget-sittings` [sic], `add-browser-notifications`. The velocity is high and the messages get lazy, but the *direction* is coherent: every commit is adding a reader-facing or editor-facing surface.

Three currents run through this act:

1. **Growth & monetization surfaces** appear in a cluster: ad slots (`fix-ads` recurs five times — ads are fiddly), the loader-ads customizer, AdSense head script, and the "ad support" page.
2. **Engagement machinery**: random-film popup, share buttons, most-read widgets, edit dates, tag styling.
3. **The notification epic**: `274a631 add-browser-notifications` lands the single largest subsystem in the codebase (the 66 KB `browser-notifications.php`), followed the same day by the admin-side scaffolding (`ebb8290 Add TOC missing-posts dashboard widget`, `826b607 Fix SCF wp_version admin error and expand TOC widget remote pool`). This is the moment the theme stops being *just* a front end and grows an editorial back-office.

A recurring **security reflex** punctuates the sprint — `fix-report-security`, `fix-security`, `fix-security#1` appear repeatedly and interleaved with features, suggesting the author was responding to scanner reports or self-audits as they went rather than in a dedicated hardening pass. Healthy instinct; the scattered messages just make the history hard to read.

Act II closes on `606430f "Update theme bootstrap include and style adjustments"` (Mar 18) — a rare descriptive message, and a tell that the `functions.php` manifest-loader structure was being tidied around this point.

---

## Act III — The `fixes#6` crisis and the great revert (Mar 31 – Apr 4)

This is the dramatic core of the project's history, and the git log wears it on its sleeve.

After a quiet late-March stretch (`fix-security`, `add-backtotop`, `enhace-grid`, `enhace-hero` — note the repeated `enhace` typo, a signature of fast typing under momentum), the author opens a change tracked as **`fixes#6`**. It lands across three commits over two days:

```
bde955c  2026-04-02  fixes#6
d5237b2  2026-04-03  fixes#6
5b4d46d  2026-04-03  fixes#6
```

And then, the same day, it comes apart:

```
d7b4dac  2026-04-03  Revert "fixes#6"
3c06255  2026-04-03  Revert "fixes#6"
2485561  2026-04-03  Revert "fixes#6"
8313ab6  2026-04-03  Revert "fixes"
4c32893  2026-04-03  Revert "remove-author"
```

**Five reverts in a single day.** Whatever `fixes#6` attempted, it was rolled back commit-by-commit, and the rollback swept up an earlier `fixes` change and the `remove-author` feature with it. This is the classic shape of a change that looked fine in isolation but broke something in production or review — and rather than patch forward, the author chose the safe path: unwind everything to a known-good state.

The `remove-author` thread is the clearest scar. Its life story across the log is a pendulum:

```
490209b  Mar 31  remove-author        (added)
   …                                   (caught in the fixes#6 blast radius)
4c32893  Apr 03  Revert "remove-author"   (undone)
d76b5c3  Apr 04  remove-author            (re-added, deliberately, after the dust settled)
```

Added → reverted → re-added. The feature itself was wanted; it was simply collateral in the `fixes#6` unwind and had to be re-applied cleanly afterward. `d76b5c3` on Apr 4 is the project catching its breath — a single, intentional re-commit that restores the desired end-state without the baggage.

**The lesson written into this act:** the repository's thin, one-line commit messages (`fixes#6` tells a future reader nothing) turned a routine bad-merge into an archaeology problem. The reverts worked, but only because the author still had the context in their head. This is the strongest argument in the whole history for the descriptive-commit discipline that Act I had and Act II abandoned.

---

## Act IV — The design pivot & the tooling era (May)

May is a deliberate change of gears — from *building features* to *making it beautiful and fast*. The author brings in an AI-assisted design toolchain and it reshapes both the theme and the repository:

```
58ea306  May 05  add-desgin-skills
bb9d8b2  May 10  fix-performance
37d8170  May 15  new-style#1
4d91441  May 21  add-skills-and-plan
f70bb89  May 22  copilot-theme
bcc6c42  May 22  copilot-theme#1
afffa03  May 23  copilot-theme#2
```

This is when `PRODUCT.md`, `DESIGN.md`, and the 38 KB `REDESIGN-PLAN.md` become first-class artifacts, and when the `impeccable` design-audit skill enters the tree — the ~8 MB of `.claude/` and `.agents/` tooling and the `impeccable` npm dependency all date from here. The `fix-performance` commit in the middle (May 10) shows the pivot wasn't purely cosmetic: the redesign was measured, not just styled. The `copilot-theme` trilogy is the visual redesign landing in three passes.

Strategically this is a maturation: the project stops accreting features and starts investing in *how it looks and how fast it loads* — exactly the "performance protects immersion" principle stated in `PRODUCT.md`. The cost, recorded permanently in the repo size, is that a large development toolchain got committed alongside the theme it was helping build.

---

## Act V — Maturity: the structured-data layer (Jun)

The final act is the most polished work in the entire history, and it reads like a different, more senior author than the `fixes#6` days:

```
5a7f3cc  Jun 24  update
5da9779  Jun 24  Add Yoast-integrated film-entity structured data layer
05df9f3  Jun 24  Show film_rating as a visible star rating in the infobox
```

`schema-film.php` (the "film-entity structured data layer") is exemplary — a module with a full explanatory header, defensive Yoast-absence handling, a rating parser that copes with `8/10`, `8 من 10`, star glyphs *and* Arabic-Indic digits, and clean `@id`-reference wiring into Yoast's existing graph. The follow-up commit closes the loop by surfacing that same parsed rating as a visible ⭐ infobox, deliberately sharing **one** parser between the schema layer and the display layer so they can never diverge (documented as "single source of truth" in `helpers.php`).

This is the project arriving at its thesis: an Arabic film magazine that describes itself — to both readers and search engines — from the metadata editors already enter. The version string had climbed to **1.0.55** by this point: fifty-five patch increments, a fitting number for a project built in many small, iterative slices.

---

## The arc, in one paragraph

Mazaq Cinema began **spec-first and disciplined** (Act I), accelerated into a **high-velocity feature sprint** whose sloppy commit messages planted a landmine (Act II), stepped on that landmine in the **`fixes#6` five-revert crisis** and recovered by unwinding to safety (Act III), pivoted from *features* to a **measured design-and-performance redesign** that permanently swelled the repo with tooling (Act IV), and matured into **careful, well-documented, single-source-of-truth engineering** (Act V). It is the recognizable biography of a solo developer's project that survived its one real crisis and came out the other side building better than it started.
