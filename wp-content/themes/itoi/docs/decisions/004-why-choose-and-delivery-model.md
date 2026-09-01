# 004 — Why Choose ITOI tabs & Delivery Model rail

**Date:** 2026-08-05 (moved from inline HTML comments in `front-page.php`
during the "10/10" pass — see `NOTES.md`).

## Why Choose ITOI (pill-tabs split panel)

Clicking a pill tab swaps the left panel content + right panel caption
(`itoiWhyTabs`, localized from the `why_choose_photos` ACF repeater in
`inc/enqueue.php`, read by `initWhyChooseTabs()` in `main.js`). Fully
ACF-driven as of 2026-08-04 — every tab's label/title/description/bullets/
CTA used to be hardcoded in the template *and* in `main.js`'s old `whyData`
array; both are gone, this repeater (+ `why_choose_headline`) is now the
only source. The PHP fallback array in the template matches the field's
own ACF `default_value` exactly, so this section still renders correctly
even before Site Settings has ever been saved.

## Delivery Model rail

**Removed 2026-08-24** — explicit instruction. `template-parts/home/delivery-model.php`,
its options page/ACF group, and its `.delivery-rail`/`.delivery-step*` CSS
are gone (not just unlinked). Section history kept below for context.

Rebuilt 2026-08-03 — replaced an auto-rotating turnstile carousel (one step
visible at a time) with a static full-width rail: all steps render at once
in a single `<ol>`, connected by a decorative `::before` line
(`src/tailwind.css` `.delivery-rail`). Below `md` the same list lays out as
a vertical timeline instead of columns — no separate mobile markup, so
there's nothing duplicated for screen readers. No JS: nothing here
auto-advances or needs a timer.

**"Wave 4" liquid-glass styling:** background switched from `bg-white` to
`bg-teal-900` + `aurora-bg` so the active step's glass card has an actual
rich background to show through it. The eyebrow dot/text and headline
switched from their light-background tokens (`--signature`/
`text-signature-dim`, default dark body text) to the matching
dark-background tokens (`--signature-bright`, `text-white`).

**2026-08-05 ("10/10" pass):** consolidated from 6 steps to 4 — see
`acf-json/group_13112f7e3503.json`'s own field instructions for the
non-invented merge rationale (real capability-deck content combined, not
replaced with generic labels).
