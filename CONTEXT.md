# Mazaq Cinema Theme

This context names the editorial surfaces and ownership boundaries that shape the Arabic cinema magazine theme.

## Editorial surfaces

**Continuous Programme**:
The homepage’s ordered set of featured articles presented as a single editorial programme.
_Avoid_: generic carousel, when referring to the editorial set.

**Programme slide**:
One article within the Continuous Programme, retaining its own title, summary, and link as part of the page’s reading sequence.
_Avoid_: decorative slide, card-only item.

**Editorial card**:
A reusable presentation of an article whose imagery and text communicate its editorial priority.
_Avoid_: generic tile, thumbnail-only card.

**Article section**:
A second- or third-level article heading carrying a stable anchor id and an optional listicle rank.
_Avoid_: raw heading, anchor id.

## Editorial mechanics

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
