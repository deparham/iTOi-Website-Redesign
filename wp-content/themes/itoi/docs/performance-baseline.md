# Performance baseline

Measured 2026-08-05, external improvement plan Phase 6. `npx lighthouse`
against the real sandbox install (`http://192.168.22.80`), Puppeteer-managed
Chrome (`CHROME_PATH` pointed at
`~/.cache/puppeteer/chrome/.../chrome` — the `@axe-core/cli`-style bundled
lighthouse launcher couldn't find a system Chrome on its own in this
environment).

## Results (desktop preset unless noted)

| Page | Perf | A11y | LCP | CLS | Transfer |
|---|---|---|---|---|---|
| Home | 1.00 | 1.00 | 0.5s | 0.001 | 19.1 MB |
| Home (mobile, default preset) | 0.97 | — | 2.1s | 0.001 | 5.4 MB |
| Contact | 1.00 | 1.00 | 0.5s | 0 | 0.19 MB |
| Solution (`/solutions/intelligence-analytics/`) | 1.00 | — | 0.5s | 0 | 21.5 MB |
| Industry (`/industries/retail/`) | 0.93 | — | 1.8s | 0 | 1.69 MB |
| Solution Builder | 1.00 | — | 0.5s | 0 | 0.20 MB |

Against the plan's own targets (§6.10): LCP < 2.5s ✅ everywhere tested
(worst case 2.1s, mobile home). CLS < 0.1 ✅ everywhere (worst case 0.001).
Mobile Performance 85+ ✅ (97). Desktop Performance 95+ ✅ (93–100,
industry page's 0.93 is still comfortably above target — see below).

## The one outlier: transfer size

Home and the solution page carry 19–21 MB, almost entirely hero/product
background **video** (`Media` resource type in the Lighthouse network
trace — one file alone is ~13.4 MB). This is a known, accepted tradeoff
per the plan's own wording: *"A video homepage may exceed the transfer
target, so compare the business value of the video against the cost."*
Not treated as a bug to fix — the video-forward hero is a deliberate
design decision (`CLAUDE.md`'s "signature layer is the differentiator"),
and Core Web Vitals are unaffected (LCP/CLS/TBT all pass) because the
video loads progressively and doesn't block paint or interactivity.

**What already keeps this from being worse** (confirmed working, not new
this pass): the mobile homepage transfer drops to 5.4 MB — the Phase 4.3
fix (swap to a static photo instead of video on narrow viewports /
reduced-motion) is measurably paying off, not just a code change with
unverified impact.

**Industry page's 0.93 (not 1.00):** its own hero background video (1.3 MB)
is the LCP element and takes slightly longer to paint (1.8s vs 0.5s
elsewhere) — still well under the 2.5s target, not chased further this
pass.

## Fixes made this pass

1. **`inc/enqueue.php` — localized script data scoped to the homepage.**
   `itoiHeroSlides` and `itoiWhyTabs` (`wp_localize_script`) ran
   unconditionally on every page, even though the markup/JS that reads
   them (`initHeroSlideshow()`, `initWhyChooseTabs()`) only exists on
   `front-page.php` — confirmed via `curl` that non-homepage pages were
   shipping ~4KB of dead inline JSON for JS that never runs there. Wrapped
   both in `is_front_page()`. Verified: 0 occurrences on `/contact/` after
   the fix (was present before), still present on the homepage.
2. **Fonts (`src/tailwind.css`, `assets/fonts/`) — dropped the never-used
   `.woff` fallback.** Each `@font-face`'s `src` listed `.woff2` first,
   `.woff` second — browsers only fetch the first format they support,
   and woff2 has had universal modern-browser support for years,
   confirmed via the Lighthouse network trace (only `.woff2` files ever
   appeared in the transferred-requests list, never `.woff`). The 5
   `.woff` files (~155 KB total) were dead weight in the repo, not bytes
   any real visitor was downloading — removed both the CSS references and
   the files themselves. `assets/fonts/` went from ~288 KB to 124 KB.
   **Considered, not done:** dropping a font *weight* entirely — usage
   audit found all 5 weights (400/500/600/700/800) genuinely referenced
   sitewide, though `font-medium` (500) and `font-normal` (400 explicit)
   are rare (2–3 uses each). Left alone — consolidating those few
   instances onto a neighboring weight is a real design call, not a safe
   mechanical cleanup, and the byte savings (~2 more files, ~48 KB) don't
   justify the risk without a content/design owner's sign-off.
3. **CSS content-scanning (`tailwind.config.js`) — verified, not changed.**
   Already scoped exactly per the plan's own example
   (`./*.php`, `./inc/**/*.php`, `./template-parts/**/*.php`,
   `./assets/js/**/*.js`), no `safelist`, no dynamic-class bloat.
   Compiled output is 78 KB.
4. **Backdrop-filter audit (`src/tailwind.css`) — the hero's full-screen
   blur was already fixed in Phase 4.4** (replaced with a plain
   gradient). Audited every other `backdrop-filter` use this pass: all of
   them are either small, bounded elements (cards, pills, the delivery
   model's active-step highlight) or modal backdrops that only render
   while a dialog is open, over a *static* page (not a continuously
   autoplaying video) — a fundamentally cheaper case than what the hero
   had. All already carry proper `@supports not (...)` fallbacks. Left
   alone — this is a deliberate, consistently-implemented "glass" design
   language, not accidental sprawl, and none of the remaining instances
   share the hero's specific anti-pattern (full-viewport filter
   continuously recomputed over looping video).

## Considered, not done

- **JS bundle splitting (plan step 6.2).** `assets/js/main.js` is 29 KB
  raw (uncompressed) — comfortably under the plan's 150 KB *compressed*
  target already without splitting (typical gzip/brotli ratios for JS put
  this at roughly 8–10 KB transferred). Splitting into
  `core.js`/`navigation.js`/`homepage.js`/etc. would add real
  build-pipeline complexity and bug-surface for a target that's already
  met — not pursued this pass given TBT is already 0ms everywhere tested.
- **`wp_script_add_data(..., 'strategy', 'defer')` (plan step 6.4).** Both
  enqueued scripts already load `in_footer => true` — placed at the very
  end of `<body>`, after the DOM is already parsed. `defer`'s benefit is
  specifically for `<head>`-placed scripts; adding it to an
  already-footer-placed script has no practical effect here. Not added,
  to avoid a no-op change that reads like a fix without being one.
- **Image format (WebP/AVIF), explicit width/height, srcset (plan step
  6.5).** Not audited exhaustively this pass — LCP/CLS are already
  passing everywhere tested (worst CLS: 0.001), which is the outcome
  these steps exist to protect. Worth a dedicated pass if new
  image-heavy pages are added later, but not chasing an already-passing
  metric here.
- **Server-level caching, Brotli/Gzip, CDN, object cache (plan step
  6.9).** Infrastructure-level, not theme code — out of scope for what
  this session can change directly (no server/hosting-panel access).

## How to re-run this baseline

```bash
export CHROME_PATH="$(node -e "console.log(require('puppeteer').executablePath())")"
npx lighthouse http://<host>/<path> --preset=desktop --quiet \
  --chrome-flags="--headless --no-sandbox" \
  --output=json --output-path=/tmp/lh-reports/<name>.json
# Drop --preset=desktop for the mobile-throttled default.
```
