# Mazaq Cinema Theme

This context names the editorial surfaces and ownership boundaries that shape the Arabic cinema magazine theme.

## Editorial surfaces

**Continuous Programme**:
The homepage’s ordered set of exactly four featured articles presented as a single editorial programme: one feature followed by three Programme slides. An editor’s featured-post pick pins the feature; Content rotation deals the rest; the latest articles fill any gap. No article in the Continuous Programme repeats elsewhere on the homepage or in its continued Latest feed.
_Avoid_: generic carousel, when referring to the editorial set; hero ids, when referring to the set.

**Latest feed**:
The front page's reverse-chronological run of articles below the Continuous Programme, excluding the Programme's four, delivered in pages of six: page 1 with the page, later pages on demand.
_Avoid_: infinite scroll, load-more posts, latest posts grid.

**Programme slide**:
One article within the Continuous Programme, retaining its own title, summary, and link as part of the page’s reading sequence.
_Avoid_: decorative slide, card-only item.

**Editorial card**:
A reusable presentation of an article whose imagery and text communicate its editorial priority.
_Avoid_: generic tile, thumbnail-only card.

**Article section**:
A second- or third-level article heading carrying a stable anchor id and an optional listicle rank.
_Avoid_: raw heading, anchor id.

**Ranked list**:
An article whose three or more H2 Article sections form its body list. When its sections carry their own ranks (counting up or down), those ranked sections are its items at those ranks; otherwise every H2 section is an item, by position.
_Avoid_: listicle; ItemList (that is its schema wire format).

## Editorial mechanics

**Article pool**:
Every published article that editorial picks draw from — Content rotation batches, the Daily suggestion, and the random film discovery panel. It can be narrowed to one list (category).
_Avoid_: candidate ids, published post ids.

**Content rotation**:
The daily no-repeat batch mechanic that selects which articles surface in rotating editorial slots.
_Avoid_: daily rotation, random picks, shuffle.

## Notification mechanics

**Push notification**:
A daily suggestion or new-post alert delivered to a subscriber's browser.
_Avoid_: payload, when referring to the thing rather than its wire shape.

**Daily suggestion**:
The once-daily article pick sent to subscribers.
_Avoid_: Content rotation pick (an independent random pick, not a rotation batch), daily_random.

**Recent notifications**:
The latest push notifications surfaced on-site for visitors without push subscriptions.
_Avoid_: fallback feed.

## Advertising

**Ad placement**:
A fixed position in the theme where an advertisement may appear, each holding a reserved format so a late creative never shifts the page.
_Avoid_: ad slot, when referring to the position (an ad network's "slot" is a unit id, not a position).

**In-feed placement**:
The Ad placement that recurs every Nth item of a feed (the Latest feed, an archive, a film page), counted across pages. Only its first plate in a feed may carry the Direct ad, however many pages the reader loads.
_Avoid_: grid ad, feed slot.

**Direct ad**:
An image linked to another website that the editor places in an Ad placement. Where both exist, it takes the Ad placement over the Network ad. It shows once per Ad placement per page view; a repeat falls back to the Network ad. In a feed, that is the In-feed placement's first plate.
_Avoid_: house ad (that is our own promotion), sponsor ad.

**Network ad**:
An advertisement filled by the ad network (AdSense) for an Ad placement.
_Avoid_: dummy ad, when referring to the real thing.

## Ownership boundaries

**Theme-owned finding**:
A markup, behavior, asset, or style issue that can be corrected within this theme without relying on a hosting, plugin, or third-party service change.
_Avoid_: site-wide finding.

**Plugin-owned finding**:
A markup or configuration issue emitted by a WordPress plugin or third-party integration; its remediation belongs to that integration or its settings, not to theme templates.
_Avoid_: theme-owned finding.
The current WPConsent logo aspect-ratio finding is plugin-owned and needs a WPConsent configuration or plugin-output change after this theme release.

**Edge security policy**:
The site-wide transport, framing, isolation, and content-security policy applied by the hosting/CDN or a dedicated WordPress security layer.
_Avoid_: theme header policy, template-only security policy.
