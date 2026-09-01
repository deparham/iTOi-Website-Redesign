# Deferred follow-ups

Running list of things the external improvement plan asks for that couldn't
be completed in-session — either because they need access this environment
doesn't have, or because they conflict with one of this project's own locked
rules and need an explicit decision before proceeding. Updated at the end of
each phase. Check this list before considering any phase "done."

## "10/10" pass — still open

- **PHP_CodeSniffer / WordPress Coding Standards baseline.** Tooling is
  real and working (`composer.json`/`phpcs.xml`, verified via
  `vendor/bin/phpcs`), but the codebase itself has never been run through
  it before this pass. Latest measurement (2026-08-06, after the
  `front-page.php` template-parts split): **630 errors, 480 warnings across
  61 files**, 850 auto-fixable via `phpcbf`. Not fixed — a whole-codebase
  `phpcbf` run is an 800+-change diff with no per-change review possible in
  this pass, a real risk of a subtle auto-fix bug shipping unreviewed for a
  benefit (style conformance) that wasn't itself requested. Pre-existing
  debt, not something this session introduced. See `docs/testing.md`.
- **HTML comment bloat beyond the homepage.** `front-page.php` was trimmed
  (10.1KB → 4.6KB of visitor-visible `<!-- -->` comments, moved to
  `docs/decisions/`). `single-industry.php` (~9.1KB) and other templates
  weren't — flagged, not done, given the scope of everything else open in
  this pass.
- **`font-bold` (700) weight consolidation.** Requested alongside dropping
  weight 500 (done). Not actioned — 218 real usages sitewide means this
  would be a genuine visual redesign, not a safe mechanical cleanup. Needs
  an explicit design decision, not an inferred one.
- **Real case-study content.** Still open from an earlier pass — the Case
  Study Spotlight section was removed from the homepage entirely (rather
  than shown with placeholder-quality content) since none of the 3 real
  case studies have a real quote or metrics yet.

## Phase 5 (accessibility) — still open

- **Manual screen-reader testing.** This pass used axe-core (automated) and
  Puppeteer-driven keyboard/focus checks (manual-equivalent, but scripted).
  Neither substitutes for actually testing with VoiceOver/NVDA per the
  plan's own Phase 12.3 — not done this session.
- **Lighthouse accessibility score.** Not captured this pass. Would pair
  well with Phase 6's performance baseline work if that gets picked up.
- **Coverage was 9 representative page templates**, not every page/post on
  the site. All 9 passed axe cleanly after fixes — see the Phase 5
  NOTES.md entry for the full list and what was fixed on each.

## Resolved 2026-08-05 (were open, now closed)

All items below were open as of the Phase 3 report and are now closed —
kept here as a record of what was blocked and how it got unblocked, per
the user's "do the stuff you can't do now for each step, at the end"
instruction.

- **Phase 2 — Primary menu restructure.** ✅ Done. User authorized
  installing WP-CLI (`php /tmp/wp-cli.phar`) with read+write access.
  Confirmed the real live menu had 8 top-level items (Solutions, Products,
  Use Cases, Industries, Customers, Resources, Education Hub, Solution
  Builder) and restructured to 6 (Solutions, Products, Industries,
  Customers, Resources, Company) via `wp menu item update/add-custom` —
  see `docs/navigation.md` for the final structure. All real content
  preserved, nothing deleted, only reparented/relabeled.
- **Phase 2 — `mega_menu_previews` missing row.** ✅ Done. Realigned to the
  new 6-item order via `wp eval-file` + `update_field()`, with real preview
  copy for the 2 new rows (Products, Company) grounded in actual product
  names (Aurora, PC2SE Outdoor) and pages (About, Team) — no invented
  claims.
- **Phase 1 — live hero content.** ✅ Done. Confirmed via `wp eval` that
  `hero_slides` DID have real saved DB content overriding the PHP
  fallback/ACF default_value edits from the earlier pass (unlike
  `hero_eyebrow`/`trust_metrics`/etc., which were genuinely null and thus
  already correctly served by the PHP fallback). Synced the live DB value
  to match via `update_field()`, preserving every slide's attached
  background video exactly as-is — only text fields changed.
- **Header.php's 1180px nav breakpoint.** ✅ Done. With the real item count
  confirmed at 6, measured via a Puppeteer check that the flat nav fits on
  one row at 980px with no wrap, then dropped the breakpoint to match the
  sitewide `min-[980px]` convention and rebuilt Tailwind CSS.
- **Phase 3, sections 4–5 ("How the platform works" / "Core outcomes").**
  ✅ Built. See `front-page.php` `#howItWorks` / `#coreOutcomes` — real
  links only (verified every link 200s), no invented claims, static/
  hardcoded content following the same precedent as Problem-First.
- **Phase 3.4 — "max 3 prominent interaction types."** ✅ Actioned, on
  explicit user authorization to override `CLAUDE.md`'s "don't cut
  corners" mechanics rule for this specific item. See the Phase 3.4 NOTES.md
  entry for the exact scope: stopped auto-play on the ticker (now one
  static message), the products carousel (manual dots/arrows kept), and
  the trust-metrics count-up (now renders final value immediately). Did
  **not** remove any component's underlying functionality or content —
  only the ambient/unprompted auto-advance behaviour. The hero slideshow,
  Live Detection background, traffic-demo widget, and scroll reveals were
  left fully intact as the plan's "3 kept types." Manually-triggered
  mechanics not named in the plan's bullet list (Why Choose tabs, Industries
  carousel, Platform Demo modal, Solution Builder finder) were left
  untouched — none of them auto-play, so none competed for attention in
  the sense the plan describes.

## Resolved 2026-08-06 (JS bundle split)

- **JS bundle splitting (`assets/js/main.js`).** ✅ Done, after initially
  being skipped the same day ("skip JS bundle for now") then explicitly
  requested ("lets do the JS split as well"). `main.js` (100.7KB raw /
  29.2KB gzipped, shipped on every page) split into 4 bundles —
  `core.js` (sitewide: nav, Finder popup, flip-cards, scroll-reveal, lazy
  media, ~11KB gzipped), `homepage.js` (front page only, ~10.7KB gzipped),
  `industry-simulators.js` (the 7 per-industry mechanics + longform
  subnav, `single-industry.php` only, ~8.2KB gzipped), and
  `listing-filters.js` (the 4 filter-pill pages, ~2.5KB gzipped) — each
  enqueued only where its markup exists (`inc/enqueue.php`,
  `is_front_page()`/`is_singular('industry')`/`is_page([...])`). Real
  effect, not just a paper split: most of the site (product/solution/case-
  study/about/contact pages) now ships **~11KB gzipped of JS instead of
  29.2KB** — a real ~62% reduction for the majority of pageviews. See the
  2026-08-06 NOTES.md entry for the exact function-to-bundle mapping and
  how cross-cutting cases (`initLongformMarquees()`, needed by both the
  homepage and industry pages) were resolved.

## Resolved 2026-08-05 (second follow-up — homepage consolidation)

- **Phase 3.2 — merge the two homepage proof/stat sections.** ✅ Resolved.
  User asked for the full slots-6-17 consolidation (after asking "what
  happened to homepage hierarchy redesign in phase 3?"), approving a
  proposal that cut the "PROOF" stat-tiles section entirely and replaced it
  with a real Case Study Spotlight — its 2 real confirmed numbers moved up
  into Trust & Credibility, so there's only one proof section now, not two.
  Also cut Industry Selector (duplicated the Industries carousel),
  Use Cases, Partners Teaser, and Team Teaser — homepage went from 17
  sections to the plan's exact 10-slot structure. Full detail in the
  "Homepage consolidation" NOTES.md entry.

## Still open

- **Real case study content.** The new Case Study Spotlight section is
  honest about current reality (see NOTES.md) but would be stronger with
  real content: all 3 published `case_study` posts (Drakes Supermarkets,
  Brisbane City Council, Macquarie Bank) still have an empty `pull_quote`
  and no `metrics` filled in. Not something this session can fix — needs
  a content owner with real sourced quotes/figures. Not a blocker, just a
  flagged content gap the section is deliberately honest about rather than
  papering over with invented content.

## Format for new entries

```
- **Phase N — <short name>.** <what's blocked and why>. <where the
  target/spec lives, if written down>.
```
