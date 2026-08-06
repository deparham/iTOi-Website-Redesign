# ITOI content style guide

Reference for anyone writing or editing copy on this site — homepage sections,
solution/industry/product pages, case studies, nav labels, CTAs. Pair with
`PROJECT.md` (content model, do-not-invent list) and `CLAUDE.md` (build
rules). Where this guide and `PROJECT.md` §6 disagree on a *fact* (a client
name, a metric, a contact detail), §6 wins — this guide governs *positioning
and wording*, not sourcing.

Established 2026-08-05 as part of the homepage positioning pass (see
`NOTES.md`).

## Company, in one sentence

> ITOI helps multi-site organisations turn cameras, sensors and operational
> data into real-time intelligence.

Longer version, for About/homepage intros:

> ITOI connects cameras, sensors and business systems into one platform,
> helping retail, hospitality, banking, government and logistics teams
> strengthen security, understand what's happening on site, and act on it in
> real time.

## Primary category

**Physical intelligence and operational analytics.** Broad enough to cover
cameras, access control, retail analytics, automation and integrations under
one coherent business — not "a security company" and not "an analytics
company" alone. Use this framing on the homepage hero, About page, and in
sales language ahead of any specific product name.

## Primary audience

Enterprise buyers evaluating a vision/analytics/security vendor — retail
operations directors, security executives, facilities managers, council
procurement — across ITOI's seven confirmed industries: retail, hospitality,
casinos & gaming, banking & finance, government & councils, logistics &
warehousing, stadiums & events (`PROJECT.md` §4 `industry` CPT).

## Three pillars

A presentation framework for grouping ITOI's real capabilities under one
story — **not** a new content-model taxonomy. The 8 `solution` CPT
categories, the `product` CPT (Aurora and future hardware), and the 7
industries keep their existing slugs, URLs and structure; this is how we
*talk about* them, layered on top.

**Observe** — capture activity.
`cctv-video-loss-prevention`, `security-access-inventory` (video, access
control, biometric access), `sensory-intelligence` (site sensors), the
Aurora/Xovis product line (anonymous people-counting hardware).

**Understand** — turn activity into operational information.
`intelligence-analytics` (foot-traffic, conversion, cross-site reporting),
`back-of-house-integration` (POS/accounting/inventory integrations).

**Act** — use intelligence to improve operations.
`workforce-ops-robotics` (automation, cleaning/security robotics),
`customer-engagement-signage` (acting on insight to engage customers),
alerts and incident response surfaced across the platform.

`it-network-infrastructure` underpins all three — it's the enabling layer,
not a fourth pillar.

This mapping is a starting framework, not final content-owner sign-off —
flag any category that reads wrong under its pillar to a content owner
before publishing pillar-labelled copy site-wide.

## Approved product & solution names

Use the CPT's own current name, verbatim — never an old pre-2026-07-23 slug
(`retail-analytics`, `smart-security`, `facial-recognition`, `security-robot`,
`cleaning-robots`, `liquor-management`, `customer-engagement` — all
redirected, see `PROJECT.md` §5):

`intelligence-analytics`, `customer-engagement-signage`,
`sensory-intelligence`, `workforce-ops-robotics`,
`cctv-video-loss-prevention`, `security-access-inventory`,
`back-of-house-integration`, `it-network-infrastructure`.

Products: **Aurora**, **Xovis** (per `product` CPT — real hardware SKUs,
distinct from the software/service solution categories above).

## Approved industry names

retail · hospitality · casinos & gaming · banking & finance ·
government & councils · logistics & warehousing · stadiums & events

## Tone of voice

- Outcome-first, not technology-first. Lead with what the visitor gets, then
  name the capability that delivers it.
- Plain, confident, concrete. No filler adjectives ("innovative",
  "cutting-edge", "world-class") standing in for a real specific.
- Short sentences in hero/CTA copy; longer, more technical sentences are fine
  once the reader has opted into a solution/product page.
- Never write copy that implies the site itself is watching the visitor —
  ITOI sells facial recognition; the marketing site must not read as if it's
  surveilling its own visitors (`CLAUDE.md`'s signature-layer guardrail).

## Capitalisation rules

- "ITOI" — all caps, never "Itoi" or "iTOi" in running copy (note: some
  historical field instructions in `acf-json/` use "iTOi Solutions" as a
  partner-lockup wordmark; that's a logo treatment, not the copy standard).
- Pillar names (Observe / Understand / Act) — capitalised when used as labels
  ("Under **Observe**, ITOI captures…"), lowercase in running prose ("we
  observe, understand and act on site data").
- Solution category names — lowercase in prose ("our video and loss
  prevention capability"), Title Case only when referencing the literal page
  title.
- Industry names — lowercase in prose except where a proper noun applies
  ("government and councils", "Brisbane City Council").

## CTA wording

Primary CTA, used consistently everywhere: **"Book a site assessment."**
Do not introduce a second, differently-worded primary CTA on the same page.
Secondary CTA where one is needed: **"View case studies."**

Near any CTA, it's fine to add one reassurance line explaining what happens
next (e.g. "Tell us about your locations and current systems — we'll
recommend a next step without requiring a full replacement of what you
already run"), but don't invent a specific process, timeline or team
commitment that hasn't been confirmed.

## Terminology — one preferred term per concept

| Avoid | Preferred term |
|---|---|
| Smart surveillance / intelligent surveillance / smart camera / smart security (as a copy phrase) | Video intelligence |
| Footfall / people count / visitor traffic | Foot-traffic analytics |
| Site optimisation / operational optimisation | Operational intelligence |
| Customer stories / customer success / projects | Case studies |
| Platform / ecosystem / system (used interchangeably for the same thing) | Platform |

Note: "smart security" and "intelligent surveillance" were both found in the
pre-2026-08-05 hero fallback copy (`front-page.php`) and have been replaced.
`security-access-inventory` and `cctv-video-loss-prevention` remain the
correct CPT/URL slugs — only the *prose* term changes, never the slug.

## Terms to avoid entirely

- Statistics that aren't in `PROJECT.md` §6's do-not-invent list or otherwise
  confirmed by a content owner. Use a precise capability statement instead
  ("multi-site reporting", "Australian deployment support") rather than a
  plausible-sounding number. Tag anything genuinely uncertain
  `TODO(metric)` / `TODO(fact-check)` — never publish a placeholder as fact.
- Vague enterprise filler as a stand-in for a statistic: "millions", "enterprise-grade",
  "world-class", "cutting-edge" used alone with no supporting specific.
- Any client, contact, address or metric beyond `PROJECT.md` §6's list.
