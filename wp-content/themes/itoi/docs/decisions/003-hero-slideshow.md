# 003 — Hero slideshow

**Date originally decided:** 2026-07-23. **Superseded:** 2026-08-05 ("10/10"
pass — see `NOTES.md`). Moved out of an inline HTML comment in
`front-page.php` as part of that same pass.

## Original decision (2026-07-23)

An earlier session shrunk the mega-hero section's height and, incorrectly,
also removed the multi-slide dot-nav slideshow entirely, collapsing it to
one fixed headline. Only the height reduction had actually been requested —
the slideshow removal was not, and was reverted. The reduced height/padding
was intentionally kept; everything else was restored to its pre-shrink
multi-slide behaviour, at 5 slides (including a RetailNext co-branding
slide).

## Current state (2026-08-05)

Trimmed from 5 slides to 2 — the site's single definitive positioning
message, plus the RetailNext partnership slide — per an explicit
instruction during the "10/10" pass. The slideshow *mechanism* itself
(dot-nav, auto-advance, pause controls) is unchanged; only the slide count
and copy changed. See that pass's `NOTES.md` entry for the full reasoning,
including the live back-and-forth that arrived at 2 slides specifically
rather than a single fully-static hero.

Mechanism: `initHeroSlideshow()`, `assets/js/main.js`. Live Detection
background (drifting signature-navy detection boxes): `initHeroDetectionBoxes()`,
same file — independent of slide count, kept as the signature layer per
`CLAUDE.md`.
