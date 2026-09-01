# Testing & quality gates

Added 2026-08-05 ("10/10" pass, see `NOTES.md`). Every tool referenced here
was actually run against this codebase before being documented — none of
this is a config written on faith.

## PHP standards — PHP_CodeSniffer + WordPress Coding Standards

```bash
composer install
vendor/bin/phpcs          # report violations
vendor/bin/phpcbf         # auto-fix what it can
```

Config: `phpcs.xml` (WordPress ruleset + PHPCompatibilityWP, PHP 8.1+).
`vendor/` is gitignored — CI installs from `composer.lock`.

**Real baseline, measured 2026-08-05:** 656 errors, 524 warnings across 48
files, 935 of them auto-fixable via `phpcbf`. **Not fixed in this pass** —
running `phpcbf` across the entire codebase in one shot, with no time to
review a ~900-change diff by hand, carries real risk of a subtle auto-fix
bug slipping through unreviewed. Treat this as the honest starting point
for a dedicated coding-standards cleanup, not a claim that the codebase is
already compliant.

**Re-measured 2026-08-06** after the `front-page.php` → `template-parts/home/*.php`
split (see NOTES.md): **630 errors, 480 warnings across 61 files**, 850
auto-fixable. File count went up (one large file became many small ones);
the totals went down slightly, not up — the split didn't introduce new
debt, it mostly just redistributed the same pre-existing style issues
across more, smaller files. Still not auto-fixed, same reasoning as above.

## JavaScript — ESLint

```bash
npm run lint:js
```

Config: `eslint.config.js` (flat config, ESLint 9+). Deliberately light —
catches real bugs (undefined globals, redeclared variables) without
re-litigating this codebase's existing style (tabs, `var` over
`let`/`const`, single quotes) as lint rules. Confirmed working: an early
version of this config was missing several real browser globals
(`getComputedStyle`, `URLSearchParams`, the wp-admin `wp` global) — found
because the first real run against `assets/js/*.js` reported them as
undefined, not because they were anticipated in advance.

**Baseline:** 0 errors, 5 warnings (unused variables) across
`assets/js/*.js` as of 2026-08-05.

## Accessibility — axe-core (not pa11y-ci)

```bash
npm run test:a11y                              # against this project's dev sandbox by default
A11Y_BASE_URL=https://staging.example.com npm run test:a11y
```

`scripts/a11y-check.js` — Puppeteer + axe-core directly, run against a
fixed list of representative pages (home, contact, a solution page, an
industry page, a case study, a product page, solution-builder, about,
insights).

**Why not `pa11y-ci`, which is what was actually asked for:** tried it
first. Its default runner (HTML_CodeSniffer) reported 3 "insufficient
contrast" violations on hero-style sections (`/`, `/industries/retail/`,
`/products/aurora/`) that axe-core — run independently, dozens of times,
throughout this whole session — consistently reported as zero violations
on the exact same pages. Traced the actual cause before deciding which
tool to trust: this theme's hero/funnel sections render their dark
background via a separate, absolutely-positioned sibling `<div>`
(`#heroBg` etc.), not a CSS `background-color` on an ancestor of the text.
HTML_CodeSniffer's contrast check walks the DOM ancestor chain looking for
a `background-color` and never finds one until it reaches `<body>`
(white) — a false positive from that algorithm, not a real bug.
axe-core's contrast rule accounts for the actual rendered/stacked
background correctly. Tried pointing `pa11y-ci` at axe as its runner
instead, but that needs the separate `pa11y-runner-axe` package, which
wasn't available to verify working in this environment — rather than
document a fix that was never actually confirmed, used axe-core directly
via this project's own already-installed Puppeteer dependency (verified
end-to-end, `axe-core` added to `package.json`/`package-lock.json` as a
real declared dependency, not just something that happened to be present
transitively).

**One real, tool-independent finding this pass did fix:** `/products/aurora/`'s
comparison table (`single-product.php`) was missing `scope="col"` on its
header row — a genuine structural issue, unrelated to the contrast
false-positive above. Fixed.

**Baseline:** 0 violations across the 9 tested pages as of 2026-08-05.

## CI — GitHub Actions

`.github/workflows/quality.yml`, runs on every PR and push to `main`:

- Installs Node + PHP dependencies.
- Builds CSS (`npm run build`) and fails if the compiled output doesn't
  match what's committed (`git diff --exit-code`) — catches an editor
  forgetting to rebuild before committing a `src/tailwind.css` change.
- `npm run lint:js`.
- `vendor/bin/phpcs`.
- A syntax-only `php -l` pass over every tracked `.php` file (fast,
  catches a typo even before phpcs's fuller ruleset runs).

The accessibility job is present but disabled (`if: false`) — it needs a
real reachable staging URL as a GitHub Actions secret (`A11Y_BASE_URL`),
which doesn't exist yet for this repo. Flip it on once one does.

## Not done in this pass

- **Visual regression testing.** `scripts/screenshot.js` already exists
  for manual screenshot capture — turning that into an automated
  before/after comparison (Phase 12.4 of the original plan) wasn't built.
- **Lighthouse CI.** `docs/performance-baseline.md` has a one-off manual
  Lighthouse baseline; wiring it into CI as a recurring gate wasn't done.
- **Browser matrix testing** (Safari/Firefox/Edge, real iOS Safari) —
  everything in this project was verified via headless Chromium
  (Puppeteer) only.
