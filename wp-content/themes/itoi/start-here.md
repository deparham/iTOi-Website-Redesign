# START-HERE.md — how to run this project with Claude Code

Four files drive this build: `PROJECT.md` (what), `CLAUDE.md` (how), `preview-verkada-match.html` (the verified visual/interaction reference — ground truth for Phase 2 onward), and this file (the prompts). Put all four in your theme repo root, and delete any older design docs (Astro PROJECT/CLAUDE, DESIGN-PIVOT, DESIGN-SIGNATURE, editorial/HUD specs, any earlier draft of `preview-verkada-match.html`) so nothing stale is lying around to confuse a session.

---

## Before you touch Claude Code (manual setup — Claude Code can't do these for you)

1. **Install WAMP** and start it — tray icon green (Apache + MySQL up).
2. **Install WordPress** locally, e.g. at `C:\wamp64\www\itoi\`. Note the local URL (usually `http://localhost/itoi/`).
3. **Confirm the PHP version** WAMP is running matches (or is close to) your production host. Write it down — Claude Code will ask.
4. **Set permalinks** to "Post name" (WP Admin → Settings → Permalinks → Save) so `/solutions/{slug}/` URLs work.
5. **Install and activate ACF Pro** (you need a license key — Claude Code can't buy/activate it). Same for your chosen SEO, caching, and form plugins.
6. **Create the theme folder** `wp-content/themes/itoi/`, `git init` inside it, and drop `PROJECT.md`, `CLAUDE.md`, `START-HERE.md`, and `preview-verkada-match.html` in.
7. Open a terminal in that folder, run `claude`, paste the Phase 1 prompt below.

You'll do a few manual WP-admin steps between phases (activating plugins, creating pages, setting a page's template). That's normal — just tell Claude Code what you did so it picks up from there.

---

## Phase 1 prompt

```
Read PROJECT.md and CLAUDE.md end to end before doing anything. They are the spec and rules; if either contradicts your defaults, the file wins.

First, confirm: is WAMP running (Apache + MySQL), is WordPress installed and reachable, and what local URL am I working against? Tell me the URL and the PHP version before proceeding. If WordPress isn't reachable, stop and tell me what you need.

We're at Phase 1 of PROJECT.md §10 — local env + theme scaffold. Do only Phase 1.

1. Note the PHP version in NOTES.md.
2. Scaffold a custom theme here: style.css (proper WP theme header), functions.php bootstrapping inc/ (theme-setup.php, enqueue.php), blank front-page.php, header.php, footer.php.
3. Set up the Tailwind build: package.json, tailwind.config.js with the §3 tokens (BOTH base and signature tokens), a source CSS file, npm run build producing compiled CSS enqueued via inc/enqueue.php. Self-host Inter or Manrope (your pick — note which in NOTES.md). Confirm it builds.
4. Confirm ACF Pro is active (ask me for the license key if needed — do not fall back to the free version).
5. Build a minimal front-page.php proving the pipeline: WordPress renders, Tailwind applies, the sans font loads, base + signature tokens are both visible on a test element.
6. Set up the screenshot loop per CLAUDE.md: puppeteer as devDependency, a screenshot script, confirm it hits the local URL and saves a PNG.
7. Turn on WP_DEBUG + WP_DEBUG_LOG (tell me if you need me to edit wp-config.php).
8. Screenshot the check page at 375px and 1440px; read them back and confirm fonts/colors render (not system fallbacks).
9. Create NOTES.md, log the session.
10. STOP. Show me git status, screenshots, confirmation the Tailwind build works and ACF Pro is active, and the NOTES.md entry. Don't start Phase 2. Don't commit — I will.

Ask before assuming anything about DB creds, WP admin access, or the local URL.
```

---

## Later-phase prompts (one per checkpoint)

**Phase 2 — design system + base templates**
```
Phase 1 checkpointed. Proceed to Phase 2 — design system + base templates per PROJECT.md §3 and §10.

Attached/referenced: preview-verkada-match.html — this is a verified working mockup checked directly against real Verkada markup and screenshots. It is ground truth for colors, type, and layout. Build header.php and footer.php to match it exactly: black primary CTA (not blue), the sticky nav with Solutions/Use Cases/Industries dropdowns, dense multi-column footer. Build a real front-page.php structure with placeholder content so the look is visible.

Screenshot and compare directly against preview-verkada-match.html at both breakpoints — call out any pixel-level mismatch. Confirm no amber appears yet outside where the mega-hero and traffic widget will go.
```

**Phase 3 — homepage mechanics + signature components (the differentiator — its own checkpoint)**
```
Phase 2 checkpointed. Proceed to Phase 3 — all 10 verified homepage mechanics from PROJECT.md §3, matched against preview-verkada-match.html:

1. Rotating ticker banner (top of page, cycles messages every ~4s)
2. Full-bleed mega-hero: dark blur scrim, left-aligned headline + right-column sub-copy/CTAs (solid black "Get demo", black-outline "Learn more"), vertical progress-dot nav auto-advancing through headline variants — with ITOI's amber Live Detection visualization animating in the background (drifting bounding-box markers, confidence labels, pulsing LIVE indicator, all illustrative)
3. "Explore by use case" section directly below the hero — horizontally scrollable pill row, placeholder content only (do not invent real use-case names)
4. "One integrated platform" CTA band
5. Interactive traffic-demo widget (time-of-day slider, illustrative data, amber active state)
6. "Why choose ITOI" — dark teal section, pill tabs, split-panel card that swaps content on tab click
7. Scrolling client logo marquee
8. Reserved review-badge slot (styled but empty — no fabricated score)
9. Industries section — arrow-nav carousel, varying-width photo tiles, floating "Learn more" pill
10. Compact product icon row + black bottom CTA band

Build every one as a real working component, not a static approximation — the mockup file has working JS for all of these; match the behavior, not just the look. Screenshot at both widths AND actually test each interaction (ticker rotates, dot-nav advances and is clickable, pill tabs swap content, arrows scroll the industries carousel, traffic slider updates). Confirm amber only appears in the two signature spots. Stop and let me review before any other page work.
```

**Phase 4 — content model**
```
Phase 3 checkpointed. Proceed to Phase 4 — register all CPTs and ACF field groups per PROJECT.md §4. After each, log into WP admin yourself and confirm the editing UX is clean and well-labeled for a non-technical user. Confirm ACF JSON sync is working and the acf-json/ files are staged before checkpointing.
```

**Phase 5 — populate content**
```
Phase 4 checkpointed. Proceed to Phase 5 — populate the CPTs with the real copy from the Astro prototype (and the live site where needed), respecting PROJECT.md §6 exactly and carrying over every TODO(metric)/TODO(fact-check)/TODO(photo) tag. Do not invent clients, metrics, or quotes. Log in NOTES.md what was ported vs still missing.
```

**Phase 6 — solutions + industries templates**
```
Phase 5 checkpointed. Proceed to Phase 6 — archive-solution.php and archive-industry.php (tile grids per §3) plus single-solution.php and single-industry.php. Pull everything from ACF. Definition of done (§9) on all pages: screenshots both widths, lighthouse ≥90, axe clean. Stop and show results.
```

**Phase 7 — case studies + insights**
```
Phase 6 checkpointed. Proceed to Phase 7 — case study and insights archives + detail templates. Handle empty pull_quote and all-TODO metrics gracefully (conditional rendering, no broken/empty blocks). Definition of done on all pages. Stop and show results.
```

**Phase 8 — about, team, contact, legal**
```
Phase 7 checkpointed. Proceed to Phase 8 — about, team, contact (with the form plugin), and the legal pages (kept as clearly-flagged "needs legal review" drafts). Definition of done on all. Stop and show results.
```

**Phase 9 — SEO + performance + accessibility**
```
Phase 8 checkpointed. Proceed to Phase 9 — configure the SEO plugin (meta, sitemap, OG, JSON-LD per §8), set up old-URL redirects, and run lighthouse + axe on EVERY url. Fix anything under 90 or any axe violation now, not later. Paste all results.
```

**Phase 10 — staff walkthrough (mandatory)**
```
Phase 9 checkpointed. Phase 10 is the non-technical staff editing walkthrough. Produce a short plain-language editing guide (how to add a case study, edit a solution, change contact details, add a team member) and a checklist I can run through with the staff member. This phase is done only when a non-technical person can make each of those edits unaided.
```

**Phase 11 — deploy**
```
Phase 10 checkpointed. Proceed to Phase 11 — walk me through deploying the theme to the production WAMP host, activating redirects, and cutting over DNS. Flag anything you can't do remotely that I need to do on the host myself.
```

---

## If a session drifts
```
Stop. Re-read CLAUDE.md "Hard rules". Tell me which rule you just broke and why, then propose a fix — don't apply it yet.
```

## Tips
- Run `claude` in the same terminal each time so it inherits your environment.
- Answer clarifying questions specifically — "whatever you think" produces generic output.
- Review the diff yourself before every commit; the two-accent rule and the do-not-invent list are easy to drift on in a big diff.
- OneDrive-synced folders can choke on `node_modules` (thousands of files). If `npm install` hangs or errors oddly, exclude the theme's `node_modules` from OneDrive sync.
- Split any phase that drags — e.g. do the solutions archive + 2 detail pages, checkpoint, then the rest.
