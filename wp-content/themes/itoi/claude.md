CLAUDE.md — ITOI Solutions Rebuild

How Claude Code works in this repository. Read alongside PROJECT.md at the start of every session. This is the current, consolidated ruleset — it replaces any earlier CLAUDE.md (Astro or otherwise). Ignore and delete older design docs (DESIGN-PIVOT, DESIGN-SIGNATURE, editorial/HUD specs); everything current is in PROJECT.md and this file.

Always do first, every session


Read PROJECT.md in full — stack, design system, content model, page list, phases.
Read this file in full.
git status and git log --oneline -10.
Check NOTES.md for the last checkpoint if a phase is in progress.
Confirm you're actually connected to the remote sandbox server (VS Code shows "SSH: <host>" in the bottom-left corner) before assuming any command will work — this project's dev environment is a remote server reached via VS Code Remote-SSH, not a local WAMP install. Confirm Node is v22+ (node --version) — the server's default Node was v12 and Claude Code requires 22+.


Stack rules (non-negotiable)


WordPress custom theme. No page builders — not even to prototype. They fight the design and wreck performance.
ACF Pro for all structured content. CPTs in inc/post-types.php. Field groups synced to acf-json/ and committed — never UI-only.
Tailwind compiled at build time. Production depends on the compiled CSS, never a running Node process.
No new JS frameworks. Vanilla JS for the mobile nav and the signature widget. Ask before adding React/Vue/Alpine.
PHP version matches the production host — ask if it's not recorded in PROJECT.md/NOTES.md.


Design rules (non-negotiable)


Match PROJECT.md §3 exactly, and match preview-verkada-match.html pixel-for-pixel where they describe the same thing. The mockup is verified against real Verkada markup/screenshots — it is ground truth, not a rough sketch. Reread §3 before building any template.
Primary CTA color is black (--cta), not blue. This was corrected after checking real markup (bg-black) — don't reintroduce a blue accent anywhere as the primary action color.
Two accents, two jobs. Black is every ordinary button/link/nav-active. ITOI's brand navy (--signature, #004B89 — migrated 2026-07-22 from an earlier placeholder amber) is reserved for the signature "Live Detection" layer specifically — as of 2026-07-20 that layer has four expressions, not two: the mega-hero background, the interactive traffic widget, the site-wide scroll-triggered detection-reveal (corner brackets + confidence tag on major content blocks — see NOTES.md), and the full-screen mega menu's numbered index labels. Signature navy still never appears on an ordinary CTA, ordinary nav link, or as a teal-panel accent outside those four (plus the per-industry interactive mechanics and long-form pages built since) — if you catch it there, that's a bug, fix it. Two tokens, not one: --signature is for light backgrounds; --signature-bright (a lighter tint of the same hue) is for dark backgrounds (hero scrim, teal-900 sections, dark modal chrome) where plain navy would be dark-on-dark and unreadable — see NOTES.md's 2026-07-22 migration entry for the full per-component audit before assuming which one a new dark-background instance needs.
Build all 10 verified homepage mechanics from PROJECT.md §3 as real, working components — the rotating ticker, the mega-hero with dot-nav slideshow, the use-cases pill row, the "why choose" pill-tabs with split-panel swap, the industries arrow-nav carousel with floating pill buttons, etc. These are not static approximations; each one is interactive in the reference mockup and must be interactive in the real theme too.
The signature layer is the differentiator. The mega-hero's Live Detection background and the interactive traffic widget are what make this ITOI and not a Verkada clone. Build them as real, working components — not static images. Don't cut corners here to save time; this is the point of the whole design.
Signature guardrail is a hard line: both signature pieces stay visibly stylized vector, never photorealistic surveillance, always explicitly illustrative data. ITOI sells facial recognition — the site must not look like it surveils its own visitors.
Single sans-serif (Manrope or Inter), no serif. Compact SaaS type scale, not oversized editorial. Hero/page H1s are left-aligned, not centered — verified in real screenshots.
No stock photography that depicts a specific ITOI product/dashboard/install — real or TODO(photo) placeholder only. Generic texture imagery may use stock but must never be captioned/implied as a real ITOI customer site.
Use Cases content stays placeholder ("Use case placeholder 1", etc.) until real use-case content is supplied from the live site — do not invent use-case names to fill the section. Superseded 2026-07-23 (see NOTES.md, use-cases consolidation): real content now exists — 42 industry-linked use cases on the `industry` CPT's `use_cases` repeater, wired into the homepage teaser, nav dropdown, and the new `/use-cases/` hub (PROJECT.md §4/§5). This rule still holds for any *new* use case row: never invent one, only add rows sourced from real content.
Animate only transform/opacity, never transition-all. Respect prefers-reduced-motion, including pausing the hero animation and the ticker.


Content rules (non-negotiable)


All copy from ACF/WP, never hardcoded in templates. Catch yourself typing a sentence into a .php file → move it to a field.
PROJECT.md §6 do-not-invent list is exhaustive. No client, contact, address, or metric beyond it. Tag placeholder metrics TODO(metric), uncertain facts TODO(fact-check); never publish either as fact.
Empty states are real states. Case studies may have no pull_quote and all-TODO metrics — templates must render gracefully when those are empty/null, not throw or show broken blocks.
Reuse the Astro prototype's content rather than re-researching — the copy and TODO tags port directly onto the ACF model.


Code style rules


functions.php stays thin — bootstraps inc/*.php (post-types, acf, theme-setup, enqueue).
Follow the WP template hierarchy exactly (single-solution.php, archive-case_study.php, etc.).
If Timber: thin PHP controllers, markup in .twig. If plain PHP: clear logic/markup separation with comments marking the boundary.
Enqueue styles/scripts via wp_enqueue_* in inc/enqueue.php — no raw <link>/<script> in header.php/footer.php.
Escape all output (esc_html/esc_attr/esc_url) on every dynamic value. Sanitize all input on forms. Non-negotiable WP security baseline.


Workflow rules


Follow PROJECT.md §10 phases in order. Stop and checkpoint after each — don't roll forward silently.
Phase 3 (signature components) gets its own checkpoint — it's the differentiator, review it in isolation before it's buried in page work.
Definition of done (PROJECT.md §9) applies per page. Rendering without error ≠ done. All 9 checks pass.
Phase 10 (non-technical staff editing walkthrough) is mandatory and not skippable. If you reach the end without it, say so — don't mark the project done.


Screenshot + verify loop


Confirm the sandbox server's actual reachable URL before screenshotting — don't assume localhost applies, since this environment is a remote server, not a local WAMP install. Check wp-config.php or ask if unsure.
Puppeteer as a devDependency (npm i -D puppeteer), no hardcoded machine paths.
Screenshot every built template at 375px and 1440px → ./temporary-screenshots/, auto-incremented, never overwritten.
Compare against PROJECT.md §3 with specifics ("H1 renders 40px, token clamp should hit ~56px here"; "amber appears on a nav link — should be blue only"). Vague "looks fine" is not acceptable.
For the signature widget: actually test the interaction (drag/click the slider), don't just confirm it's visually present.
≥2 comparison rounds per template. Clear temporary-screenshots/ at session end unless asked to keep.


Verification gates (paste outputs into the phase's final message)


WP_DEBUG on; check debug.log after each template, resolve notices.
npx lighthouse http://<local-url>/<page> --preset=desktop --quiet --chrome-flags="--headless" on every page in the phase — ≥90 across the board (don't chase an unrealistic 95+ on WordPress, but don't accept <90).
npx @axe-core/cli http://<local-url>/<page> — zero violations.
From Phase 4 on, confirm acf-json/ changes are staged (git status) before checkpointing.


NOTES.md

Append a dated entry each session: phase + position in it, what was built, any deviation from PROJECT.md (with human approval noted), any TODO(photo)/TODO(metric)/TODO(fact-check) added, anything surprising (plugin conflict, WAMP quirk, PHP-version issue).

Hard rules — do not violate


No page builder plugins.
No blue accent color anywhere as the primary action color — primary CTA is black (--cta), verified against real markup. This still holds even though --signature is itself a navy blue as of 2026-07-22: the CTA and the signature layer are two different roles that must never merge into "blue is now the accent color" everywhere.
No signature navy (--signature/--signature-bright) on ordinary buttons/links/nav — signature layer only (mega-hero background, traffic widget, scroll-triggered detection-reveal, mega menu index labels, plus the per-industry interactive mechanics/long-form pages — see line 30).
No photorealistic surveillance imagery in the signature pieces; keep them stylized + illustrative.
No stock photo standing in for a real ITOI product/install.
No invented use-case names — real content now exists (42 industry-linked use cases, see NOTES.md 2026-07-23), so this now means: never add a use-case row that isn't sourced from real content.
No hardcoded copy, contacts, or client names in templates — pull from ACF.
No client/metric/contact beyond PROJECT.md §6.
No plugins beyond the four named in PROJECT.md §2 without asking.
No hardcoded local paths (C:\wamp64\..., localhost, or the sandbox server's literal IP address) in committed/deployed files.
Don't edit itoiweb or astra (the current live site's existing theme and its base theme) — the new theme is an independent build at wp-content/themes/itoi/.
Don't skip the Phase 10 staff walkthrough.
Don't git commit unless explicitly asked.
Don't roll between phases without checkpointing.
Don't remove TODO(...) markers unless substituting the real asset/value.