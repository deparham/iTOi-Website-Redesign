# 002 — Industries carousel flip-card interaction

**Date:** 2026-08-05 (moved from an inline HTML comment in `front-page.php`
during the "10/10" pass — see `NOTES.md`).

## Context

The homepage Industries carousel has two independent interactive layers:
prev/next arrow buttons that smooth-scroll the row by one tile, and a
flip-card mechanic that triggers on hover/tap of an individual tile — the
two don't interfere with each other.

## Decisions

- **Tile sizing** standardized 2026-07-23: every tile uses the same
  `aspect-[4/5]` *and* the same basis-width — previously alternated
  `basis-[340px]`/`basis-[400px]` by array index, which is why "Casinos &
  Gaming" rendered narrower than its neighbours despite the shared aspect
  ratio.
- **Flip-card mechanic** reuses the shared `.flip-card` component
  (`src/tailwind.css` + `initFlipCards()`, `assets/js/main.js`) — the same
  component as the About page's "Partners, not vendors" section and the
  homepage solutions grid, not a new implementation.
- **Front face:** photo + name-overlay. The floating "Learn more" pill that
  used to straddle the tile's bottom edge is gone — that CTA now lives on
  the back face only, since a live front-face link would let a single
  accidental tap on touch carry a visitor straight to the industry page
  before they'd seen the back-face summary at all.
- **Back face:** the industry CPT's own `summary` field (already-populated
  real copy, not rewritten) + a "Learn more" link. `data-href` opts these
  cards into the click-through extension in `initFlipCards()` — the two
  other flip-card usages (About page, solutions grid) don't set it, so
  they're unaffected by this behavior.
