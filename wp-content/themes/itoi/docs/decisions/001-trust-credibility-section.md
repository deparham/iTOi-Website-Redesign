# 001 — Trust & Credibility section design

**Date:** 2026-08-05 (moved from an inline HTML comment in `front-page.php`
during the "10/10" pass — see `NOTES.md` for that pass's own entry; this
file exists so visitors loading the homepage don't download a paragraph of
internal build history in the page source, per that pass's explicit
instruction not to output large historical comments to visitors).

## Context

Directly below the mega-hero (enterprise CRO Step 2) — a monochrome
metric-card row + the real client-logo marquee, distinct from the separate
`proof_stats` section further down the homepage (dark `bg-ink` glass cards).

A standalone "TRUSTED BY (logo band)" section used to sit further down the
page (`itoi_render_trusted_by_band()`, its own "Trusted by:" heading).

## Decision

Consolidated the logo band into this section instead of keeping it
separate — two distinct "who trusts us" moments on one homepage was
redundant. This section's own heading already carries that framing, so the
logo row renders directly below the metric cards with no second heading
above it. Same rendering mechanic as before
(`itoi_render_client_logo_row()`, `inc/customers-section.php` — real,
published `client` CPT posts, marquee if more than 5, static wrapped row
otherwise), just relocated. The temporary ACF-driven placeholder-logo
repeater this section originally shipped with (`trust_placeholder_logos`)
was removed too — real logos render here directly, no separate
"placeholder vs real" split to maintain.

This section intentionally doesn't reuse `proof_stats`' visual language —
light, bordered, soft-shadow cards per the monochrome enterprise brief, not
dark glass.

## Implementation notes

- **Counters:** when a card's `stat_value` starts with a digit, its leading
  numeric portion animates from 0 up on scroll-in
  (`initTrustMetricsCounters()`, `assets/js/main.js`) — any trailing
  non-numeric text (`+`, `%`, `/7`, `" hour"`) is static, appended after the
  count finishes. A non-numeric `stat_value` (e.g. "Multi-site") has
  nothing to count from, so it just renders as its real text with no
  animation — same card markup/styling either way, only a PHP-side
  is-it-numeric check decides whether the initial server-rendered content
  is `"0"` (about to count up) or the real value (nothing to animate).
- Deliberately no `--signature-navy` anywhere in this section — checked
  against `itoi_reveal_markup()`'s "metric cards" use case, which would
  normally fit here, skipped specifically because it draws in
  signature-blue brackets/tag, which conflicts with the brief's explicit
  "monochrome" requirement. Only the plain `itoi_reveal_class()` fade is
  used, on the heading only.

## Consequences

Any future change to this section's stat content should keep the
monochrome constraint in mind — it's a deliberate brief requirement, not an
oversight.
