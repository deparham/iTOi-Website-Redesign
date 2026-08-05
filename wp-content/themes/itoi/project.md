PROJECT.md — ITOI Solutions Website Rebuild

The single source of truth for what to build. Pair with CLAUDE.md (how to build). This is the current, consolidated spec — it supersedes every earlier design document (the dark "detection HUD" build, the Palantir-editorial direction, and the separate DESIGN-PIVOT / DESIGN-SIGNATURE addenda). If you find those older files, delete them; everything current lives here.


1. What we're building

A full re-theme of itoisolutions.com.au. Same WordPress core the site already runs on, entirely new custom theme, hosted on WAMP (Windows/Apache/MySQL/PHP). Content is edited by non-technical staff, so the CMS layer is required — this is not a static-site build.

Design target: a clean, modern, conversion-oriented enterprise SaaS site in the spirit of Verkada.com and Kepler Analytics (the two direct-competitor references), executed to Verkada's quality bar — but with one signature element that makes it unmistakably ITOI's, not a competitor reskin (see §3).

Note on prior work: an Astro prototype of this site was built through its content-modelling phase. That prototype is now reference-only. Its content (7 solutions, 3 case studies, 7 industries, 2 team members, 3 insights, 3 legal drafts, all with real copy and TODO-tagged placeholders) maps almost directly onto the ACF field model in §4 — reuse that content, don't re-research it.

Audience: enterprise buyers evaluating a vision/analytics/security vendor — retail operations directors, security executives, facilities managers, council procurement. They should understand what ITOI does in under a minute and have an obvious path to "book a demo."

Success criteria:


Reads as a modern enterprise SaaS vendor, not a WordPress template or a page-builder site
Editable by non-technical staff without touching code
Lighthouse ≥ 90 across the board on every page
WCAG 2.2 AA
Runs on standard WAMP hosting — no Node runtime dependency in production



2. Stack (locked — do not substitute)


WordPress (match the version on the live host — confirm before starting, don't assume latest)
Custom theme, built from scratch. No page builders (Elementor, Divi, Beaver Builder, WPBakery) — they produce bloated markup that fights the design and tanks performance.
Advanced Custom Fields (ACF) Pro for the content model in §4 (Flexible Content, Repeater, Relationship, Options Pages). This is the CMS layer non-technical staff use.
Timber/Twig recommended for template/logic separation; plain PHP templates acceptable if the team prefers no extra dependency — decide once, be consistent.
Tailwind CSS, compiled at build time via a local Node toolchain (npm run build outputs one compiled CSS file; production needs no Node).
Vanilla JS for interactivity (mobile nav, the signature widget in §3). No React/Vue/Alpine unless explicitly approved. Don't fight WordPress core's jQuery, but don't build on it either.
Plugins assumed: ACF Pro, one SEO plugin (Yoast or RankMath, not both), one caching plugin (WP Rocket or W3 Total Cache), one form plugin (Contact Form 7 or Fluent Forms). Ask before adding any other.
Dev environment: remote sandbox server (SSH / VS Code Remote-SSH), not a local WAMP install on the developer's own machine — see §7 for full detail. Production hosting remains WAMP regardless.


Not using: Astro or any JS framework runtime, page builders, headless/decoupled setup. Classic server-rendered WordPress theme.


3. Design system

The look is clean modern enterprise SaaS, matched directly against real Verkada markup, screenshots, and a saved page (not guessed from memory) — plus one signature layer ("Live Detection") that makes it unmistakably ITOI's, not a reskin.

Ground truth: preview-verkada-match.html — a working static mockup built and iterated in this project, verified section-by-section against real Verkada files and screenshots. Treat this file as the literal visual/interaction target for Phase 2 onward. Where this spec and the mockup ever disagree, the mockup wins — update this doc to match it, not the other way around.

Colors — verified against real markup, not guessed

css--bg:          #FCFCFB;
--hero-bg:     #F2F4F3;   /* section alternation, very light neutral */
--ink:         #0E1116;   /* near-black — the PRIMARY CTA color, verified bg-black in real markup */
--line:        #E6E9EE;
--text:        #0E1116;
--text-muted:  #616B78;

--cta:         #000000;   /* primary buttons are solid BLACK, not blue — confirmed via bg-black class in real markup */
--cta-hover:   #1F2328;

--teal-900:    #0B3A44;   /* "why choose" section outer background — confirmed bg-cyan-900-equivalent in real markup */
--teal-800:    #0F4A57;
--teal-700:    #13606F;   /* "why choose" split-panel left-side fill */

--signature:        #004B89;  /* ITOI's own accent — brand navy, sampled from the real logo. Migrated 2026-07-22 from an earlier placeholder amber (#FF8A1E); see NOTES.md for the full migration entry. Light-background instances only — ~8.9:1 on white. */
--signature-dim:    #003665;  /* darker navy for hover/pressed states on light backgrounds */
--signature-bright: #5AACFF;  /* lighter, more saturated tint of the same hue, for dark-background instances (hero scrim, teal-900 sections, dark modal chrome) — plain --signature would be dark-on-dark there */
--signature-glow:   rgba(0,75,137,0.16);

Correction from earlier drafts: the primary ACTION/CTA color is black, not blue — this still holds even though --signature is itself navy blue as of 2026-07-22. Don't reintroduce a blue CTA — it was an early guess before real markup was available and is wrong. --signature's navy is a separate, later, deliberate brand decision (sampled from ITOI's real logo) for the Live Detection accent role specifically, not a re-litigation of the CTA-color correction above.

Typography

Single sans-serif family (Manrope or Inter, self-hosted, no Google Fonts CDN in production). Bold weights, tight tracking, compact SaaS scale:


H1 (mega-hero): clamp(30px, 5.5vw, 58px), weight 800
H1 (page-level): clamp(30px, 4vw, 46px), weight 800, left-aligned (not centered — verified in real screenshots)
H2: clamp(26px, 3vw, 38px), weight 800
H3: 20–22px, weight 700
Body: 15–16.5px, weight 400
Pill/tab labels: 13.5–14.5px, weight 700


Layout


Section padding 70–100px desktop, tighter mobile. Alternate --bg/--hero-bg for rhythm.
Solutions/case-studies/industries listings use image-tile grids or arrow-nav carousels (see mechanics below), not plain card grids.
Real photography for anything depicting an actual ITOI product/dashboard/install, or a clearly TODO(photo)-tagged placeholder. Generic texture imagery may bridge with stock but must never be captioned as a real ITOI site.


Verified real-site mechanics — build these as working components, not static approximations

These were extracted directly from saved Verkada markup/screenshots, not invented:


Rotating ticker banner at the very top of every page — cycles 2–3 short messages every ~4s with a smooth vertical slide, plus a white pill link. (.promo / .promo-track in the mockup.)
Full-bleed mega-hero directly under the sticky nav — dark blur scrim over an animated background, split layout (large left-aligned headline; right column with sub-copy + two CTAs: solid black "Get demo" pill, black-outline "Learn more" pill that inverts on hover). A vertical progress-dot nav on the right edge auto-advances through the headline/sub-copy variants below (each dot's ring filling over ~5s); clicking a dot jumps directly. **Height reduced 2026-07-23** (see NOTES.md): the section's vertical padding/min-height was cut down from a near-viewport-height block to a fixed, shorter size — this part stands. A same-day follow-up session incorrectly also removed the dot-nav slideshow itself down to one static headline; that removal was never requested and was reverted the same day — the multi-slide dot-nav is the correct, current, permanent behavior, not the single-headline version. 5 slides as of the correction: the original 4, plus a RetailNext co-branding slide ("RetailNext × iTOi Solutions" lockup treatment, slide 5) — see NOTES.md for the partnership-slide mechanism. The Live Detection background, both CTAs, and the scrim/photo treatment are unchanged throughout. (.mega-hero, .dot-nav in the mockup.)

ITOI's Live Detection visualization fills this hero's background — drifting signature-navy bounding-box markers with confidence labels, a pulsing LIVE indicator — in place of a real video (which the real site uses; ITOI doesn't have hero video assets yet). This is the primary home of the signature layer — see below.



"Explore by use case" section, directly below the hero (top of scrollable content) — a horizontally scrollable row of pill-style tiles. Distinct from Industries: Use Cases are the problem being solved (e.g. "Access Control", "Incident Response", "Video Search"), Industries are who buys it (Retail, Hospitality, etc.). Superseded 2026-07-23 (see NOTES.md, use-cases consolidation): the placeholder content described above is gone — this section now pulls real, industry-linked use cases (a curated 7-item featured subset, one per industry) via `itoi_get_industry_use_cases( array( 'featured_only' => true ) )` (inc/use-cases.php), the same helper/selection the nav's "Use Cases" dropdown uses, with a "View all use cases" link to the new `/use-cases/` hub (§5).
"The Delivery Model" section — **added 2026-07-23** (see NOTES.md), directly below "Explore by use case." Real content from the client's capability deck: eyebrow "THE DELIVERY MODEL", headline "Every engagement, end-to-end.", six steps (Supply, Stage, Install, Handover, Manage, SLA) each with a one-sentence description. Vertical scroll-triggered stepper — numbered circle + step name + description per row, a vertical connecting line down the left edge. At rest: outline circles, dashed/dim line, ~40–50%-opacity headline text (description stays full-opacity — see NOTES.md's WCAG contrast note). Each step activates independently (filled signature-navy circle, line segment above it draws in, text reaches full opacity) as it crosses roughly the viewport's vertical center, via its own IntersectionObserver entry, and stays activated on scroll-up. `prefers-reduced-motion`: all 6 steps render fully activated immediately. Content is an ACF repeater (Delivery Model options page), not hardcoded.
"One integrated platform" CTA band — centered headline, one paragraph, one black CTA.
"Why choose ITOI" section — full-width dark teal (--teal-900) background, centered white headline, a row of fully-rounded pill tabs (white/active vs. outline/inactive), and beneath it one card split into two panels: left panel solid --teal-700 with white headline/paragraph/checklist/white "Learn more" pill; right panel a photo. Clicking a pill tab swaps both panels' content. (.why, .pill-tabs, .why-card in the mockup.)
Scrolling logo marquee — continuous horizontal auto-scroll of real client names.
Reserved review-badge slot — styled like a real G2/Capterra badge row but intentionally left empty/labeled as reserved, since ITOI has no third-party review listing yet. Never fabricate a score.
Industries section — heading + a pair of functional circular prev/next arrow buttons, a horizontally scrollable row of varying-width photo tiles (not a fixed grid), each with a white label overlay and a "Learn more" pill button that floats/straddles the tile's bottom edge.
Compact product/solutions icon row, separate and smaller than the industries tiles.
Black bottom CTA band + dense 5-column footer.


Signature layer — "Live Detection"

The one thing competitors don't have, and ITOI's answer to "every security vendor uses blue/black as the trust color." Two expressions:


The mega-hero's background visualization (see mechanic #2 above) — the primary showcase of this.
An interactive traffic-demo widget (time-of-day slider updating simulated foot-traffic density) — placed between the "One integrated platform" band and the "Why choose" section. Explicitly illustrative data, captioned as such, signature navy for the active state.


Guardrail (hard line, not a nicety): both stay visibly stylized vector/illustration, never photorealistic surveillance footage. ITOI sells facial recognition — the marketing site must not itself look like it's live-surveilling visitors.

Motion

Subtle fade/rise on scroll-in, gentle hover transitions. Animate only transform/opacity, never transition-all. Respect prefers-reduced-motion — including pausing the hero visualization and the ticker.


4. Content model (ACF field groups)

Each below is an ACF Field Group on a Custom Post Type or Options Page. Register CPTs in the theme (inc/post-types.php), not via plugin, for portability. Sync field groups to ACF JSON (acf-json/) and commit them — the DB copy alone isn't version-controlled.

The Astro prototype's content collections map onto these directly; reuse that copy and its TODO tags.

CPT solution (8 entries — slugs locked, current structure)

Superseded 2026-07-23: the original 7-slug structure (retail-analytics, facial-recognition, customer-engagement, smart-security, security-robot, cleaning-robots, liquor-management) was restructured into the 8 categories below, per a real capability deck the client provided — a genuine content-architecture change, not a rename. Kept here for history since old URLs still redirect against this mapping (see §5); do not build against the old 7 slugs.

intelligence-analytics, customer-engagement-signage, sensory-intelligence, workforce-ops-robotics, cctv-video-loss-prevention, security-access-inventory, back-of-house-integration, it-network-infrastructure.

Mapping from the old 7 (full rationale + per-row content assignment logged in NOTES.md, 2026-07-22/23 entries): retail-analytics → intelligence-analytics; customer-engagement → customer-engagement-signage; security-robot + cleaning-robots → workforce-ops-robotics (merged); liquor-management → back-of-house-integration (reframed under a broader inventory/stock-control angle); smart-security split — its CCTV/theft-detection content → cctv-video-loss-prevention, its access-control content → security-access-inventory; facial-recognition folded entirely into security-access-inventory as the biometric-access capability (no longer its own page — the 99.87% LFW accuracy / sub-100ms figures from §6 live there now). sensory-intelligence and it-network-infrastructure are entirely new, no old-page equivalent. security-access-inventory carries a visible cross-link callout to cctv-video-loss-prevention (the smart-security split's two destinations), since it's the primary redirect target for the old `smart-security` slug.

sensory-intelligence carries a hard content rule: any uplift statistic used on that page (the 4 confirmed ones: ~100x brand-recall lift from scent, ~33% purchase-behaviour shift from music, 3–5x proximity-triggered conversion lift, ~8% average sales uplift from coordinated sensory elements) must carry this exact disclaimer inline wherever it's stated, not just once in a footnote: "Illustrative only, based on industry research and general case studies, not guaranteed results. Actual performance will vary by store, location, creative quality, product mix, staffing, and external factors." Never present these as ITOI's own client results.

Fields (unchanged from the original 7-slug structure): eyebrow (text), headline (text), dek (textarea, one-sentence summary), hero_image (image), tile_image (image — for the solutions grid), narrative (WYSIWYG), features (repeater: text), specs (repeater: label, value), integrations (repeater: text), faqs (repeater: q, a), related_industries (relationship → industry), related_use_cases (relationship → use_case), related_case_studies (relationship → case_study).

Addendum, 2026-07-23 (see NOTES.md, "capability breakdown flip-cards" entry): a new `capability_cards` repeater (name, photo, photo_placeholder_alt, description, stat, has_disclaimer) was added to this field group — the flip-card grid that replaced the old plain `features` checklist as the on-page "Capabilities" section on all 8 solution pages. `features` itself was kept, not deleted (its data is retained but no longer rendered on the front end) — flagged here since this reconciliation audit found the field group had drifted ahead of this doc.

CPT use_case (legacy — real use-case content lives elsewhere, see below)

The "Use Cases" nav item and homepage section (mechanic #3 above). Fields: name (text), icon (image or icon field), summary (textarea), related_solutions (relationship → solution). Do not populate with invented use-case names — leave as generic placeholders ("Use case placeholder 1", etc.) until real content is provided from the live site, then replace before launch.

Superseded 2026-07-23 (see NOTES.md, "use-cases consolidation" entry): real use-case content was never actually added to this CPT (it still holds only a handful of sparse posts). Instead, 42 real, industry-specific use cases were added as a repeater field (`use_cases`) directly on the `industry` CPT itself (acf-json/group_cb9a360b89a5.json — 6 rows per industry × 7 industries, each row: label, image, and a relationship to its `solution`). This CPT's `has_archive` slug (`use-cases`, rewrite in inc/post-types.php) is still what routes `/use-cases/`, but `archive-use_case.php` (the new hub template) ignores this CPT's own posts entirely and aggregates the industry repeater instead via `itoi_get_industry_use_cases()` (inc/use-cases.php). This CPT is kept registered (its 3 sparse posts are unrelated legacy data, not deleted) but is no longer the intended home for use-case content — don't add new use cases here; add them to the relevant industry's `use_cases` repeater instead.

CPT case_study

client_name (text — must be on §6 list or anonymized), industry (relationship), related_solution (relationship), headline (text), narrative (WYSIWYG), metrics (repeater: value, label — real sourced figures only; tag unsourced TODO(metric) in an admin note, don't publish invented numbers), pull_quote (text) + quote_attribution (text) — leave blank unless a real quote exists (do not invent a testimonial attributed to a real person), hero_image, gallery.

CPT industry (7 entries)

retail, hospitality, casinos-gaming, banking-finance, government-councils, logistics-warehousing, stadiums-events. Fields: name, summary (textarea), related_solutions (relationship), hero_image, client_examples (repeater: text — only where the live site's own categorization supports it, else leave empty).

Addendum, 2026-07-23 (see NOTES.md, reconciliation audit entry): two more ACF field groups attach to this CPT, built across the 2026-07-21 "per-industry hero interactions" and long-form-page sessions, never folded back into this doc until now. Neither replaces the fields above — both are additive, per-industry content:
- "Industry — Hero Interaction" (acf-json/group_9f3a1c7e5d20.json): the funnel/simulator/chart/comparison/map/zone-selector fields behind each industry's distinct interactive hero mechanic — funnel_enabled/funnel_headline/funnel_intro/funnel_default_traffic/funnel_conversion_rate/funnel_value_per_lead/funnel_disclaimer (Retail); hospitality_stages repeater + 2 labels (Hospitality); banking_scenarios repeater (Banking); government_categories + government_checkboxes repeaters (Government); logistics_events repeater + 4 labels (Logistics); stadium_zones repeater + 4 numbers (Stadiums); casino_zones repeater (Casinos).
- "Industry — Long-form Page" (acf-json/group_cb9a360b89a5.json): the 5-section long-form page structure (Overview, Use Cases, Why ITOI, Solutions, Customers) below each industry's hero — longform_enabled toggle, overview_headline/subheadline/visual/visual_caption + overview_feature_rows repeater, use_cases_heading + the 42-row use_cases repeater (label/image/solution/featured_in_nav — this is the real Use Cases hub data source, see the CPT use_case entry above), why_heading + why_items repeater, solutions_heading + longform_solutions relationship, customers_heading/spotlight_client/spotlight_photo/customers_empty_message + logo_strip_groups repeater.

CPT team_member (2 confirmed)

Sean Kiely, Michael Stark. Fields: name, role, bio (textarea — placeholder until real bios provided), photo (placeholder until real photos), email, linkedin_url (optional). Real people only.

CPT insight

Standard post fields + dek (textarea), author (relationship → team_member). 3 drafted articles exist from the prototype; publish only when reviewed.

CPT guide + CPT glossary_term (new — added 2026-07-22, outside the original CPT list above, for the Education Hub — see NOTES.md, "Education Hub" entries; not previously folded into this doc, added here 2026-07-23 during a reconciliation audit)

`guide` (acf-json/group_b295a4c975ec.json, routes at /education/guides/{slug}/): title (text — separate from post_title so the on-page H1 can differ from the admin list label), industry (post_object → industry), dek (textarea), body (WYSIWYG), related_solution (post_object → solution), read_time_minutes (number), published_date (date_picker, return_format Ymd). No author field — every guide's `post_author` is 0 (no real WP user attached), so its schema.org `BlogPosting` markup (`inc/schema.php`) correctly attributes authorship to the ITOI Solutions Organization rather than inventing a byline.

`glossary_term` (acf-json/group_7e5d515c4ec7.json, not publicly routed — same "pure data" pattern as team_member/client, listed on one alphabetical /education/glossary/ page): term (text), definition (textarea), related_guides (relationship → guide).

CPT client (new — added 2026-07-20, outside the original CPT list above, for the Customers page/homepage section)

Not publicly routed (same pattern as team_member — pure data, no single-client page). Fields: name (post_title), logo (image, optional — TODO(photo)-style empty state, no third-party logo scraped/hotlinked from the live site), case_study (post_object → case_study, optional, only for Drakes Supermarkets/Brisbane City Council/Macquarie Bank). Taxonomy client_category (6 terms, mirroring the live portfolio page's own section headings — Shopping Centres, Retail, Hospitality & Industry, Councils & Commercial, Financial Institutions, Hotels) — intentionally not a relationship to the industry CPT above; the two taxonomies serve different purposes and don't map cleanly onto each other. See §6's amended client list for sourcing.

Options Page Site Settings

company_address, manager_phone, manager_email ×2, support_phone, support_email, office_hours, support_hours, og_default_image, site_tagline, hero_slides (repeater: headline, subcopy, is_partnership, partner_name, partner_logo — the mega-hero's dot-nav slideshow, mechanic #2 above; 5 rows as of 2026-07-23, the 5th being the RetailNext co-branding slide. Briefly collapsed to two plain fields the same day, then reverted — this repeater is the correct, current structure. Actually wired to the front end via wp_localize_script, inc/enqueue.php — the pre-2026-07-23 version of this same field existed but no template ever read it), ticker_messages (repeater: text — for the rotating banner, mechanic #1) — populate from §6, pull into templates so they're editable in one place, never hardcoded.

Delivery Model Options Page (separate options page, added 2026-07-23 — see NOTES.md): delivery_model_eyebrow (text), delivery_model_headline (text), delivery_model_steps (repeater, exactly 6 rows: number, step_name, description) — content for the homepage's "Delivery Model" section, mechanic above. Addendum, 2026-07-23 (reconciliation audit): this section's interaction was rebuilt twice since first documented — a pinned one-step scroll slideshow, then the current, final turnstile auto-rotating carousel (`initDeliveryTurnstile()`, `assets/js/main.js`) — the ACF field structure above is unchanged by either rebuild.

Two more options pages exist, also never folded into this doc — added here 2026-07-23 during a reconciliation audit:
- "Partners, Not Vendors" (acf-json/group_7f2a4c9e01d3.json, see NOTES.md "Partners, not vendors" entries): partners_headline (text), partners_intro (textarea), partners_teaser_line (text — the homepage teaser's one-line summary linking to the About page section), partners_cards (repeater, min/max 4: front_title, back_description) — real capability-deck copy, rendered as a flip-card grid on the About page (`#partners-not-vendors`), plus a plain-text teaser on the homepage linking to it.
- "Find Your Fit Settings" (acf-json/group_bc153137ebbc.json, see NOTES.md "Find Your Fit quiz popup" entry): fyf_eyebrow/heading/subheading/trigger_label, fyf_step1_question + fyf_step1_options repeater (label_/value_), same pattern for step2/step3, fyf_case_study_routes repeater (industry_value, case_study, result_tag), fyf_solution_routes repeater (need_value, solution), fyf_fallback_tag, fyf_case_study_cta_label, fyf_solution_cta_label — content for the floating 3-question quiz modal that routes a visitor to a relevant case study or solution.

Pages (standard WP Pages) with a Flexible Content page_builder field

Home, About, Team, Contact, Privacy, Terms, Cookies. Layout options: Live-Data Hero (§3 signature), Traffic Demo Widget (§3 signature), Logo Strip, Stat Band, Solutions Tile Grid, Industries Tile Grid, Case Study Feed, Text + Image, Quote, CTA Band. Editors rearrange sections without a page builder plugin.


5. Page inventory

URLTemplateNotes/front-page.phpFlexible Content; includes both signature sections, use-cases section, mega-hero/solutions/archive-solution.phpTile grid/solutions/{slug}/single-solution.php/use-cases/archive-use_case.phpBuilt 2026-07-23 (see NOTES.md) — the central use-cases hub, aggregating all 42 real industry-linked use cases via itoi_get_industry_use_cases() (inc/use-cases.php); client-side industry filter pills, no CPT loop against the sparse use_case posts — see §3/§4/use-cases/{slug}/single-use_case.php/industries/archive-industry.phpTile grid/industries/{slug}/single-industry.php/case-studies/archive-case_study.phpTile grid/case-studies/{slug}/single-case_study.phpHandle pull_quote empty + metrics TODO gracefully/insights/archive-insight.php/insights/{slug}/single-insight.php/about/page-about.php/team/page-team.php/contact/page-contact.phpForm plugin/privacy/, /terms/, /cookies/page.phpDrafts flagged "needs legal review"404.phpsitemap.xmlSEO pluginrobots.txtpublic/manual

Redirect old URLs (/retail-and-venue-analytics/ → /solutions/intelligence-analytics/, etc.) via the Redirection plugin or .htaccess — not hardcoded in PHP. Deviation, in effect since Phase 9: neither was available in this dev environment (no writable .htaccess, no Redirection-style plugin installed), so this lives in inc/redirects.php as an explicit PHP fallback instead — move it once one of those becomes available.

2026-07-23 solutions restructure — old /solutions/{slug}/ URLs now redirect to their new-category successor (full mapping in NOTES.md and inc/redirects.php): retail-analytics → intelligence-analytics; customer-engagement → customer-engagement-signage; smart-security → security-access-inventory; facial-recognition → security-access-inventory; security-robot → workforce-ops-robotics; cleaning-robots → workforce-ops-robotics; liquor-management → back-of-house-integration. All 7 confirmed live via inc/redirects.php's explicit map — 5 of them are also covered incidentally by WP core's own old-slug redirect (the in-place post renames), but the explicit map is what's relied on, not that implicit behaviour.

Addendum, 2026-07-30 — Aurora product page (/aurora/, page-aurora.php; see NOTES.md "Meet Aurora" entry for the full build log). New standalone WP Page (post_name `aurora`), NOT a `solution` CPT entry — Aurora is a hardware SKU (a 3D stereo depth sensor for anonymous, privacy-safe people counting, explicitly positioned against 2D/facial-recognition camera counting), distinct from the 8 software/service `solution` categories in §4. Nav: added as its own top-level custom link in the Primary menu (position 2, right after "Solutions"), not nested under the Solutions dropdown (which is CPT-driven from the 8 `solution` posts specifically) and not given its own new "Products" dropdown grouping for a single item — flagged as the recommendation, open to revisiting if more hardware products are added later. Homepage: a condensed teaser card links through to /aurora/, placed directly before "Why teams choose ITOI" (front-page.php).

Superseded 2026-07-31 (see NOTES.md, "Products admin" entry): "Copy is hardcoded in the template" above no longer holds — the user asked for everything Aurora-related (hero copy/CTAs, device-stage photo-or-video, stat strip, how-it-works steps with per-step photo-or-video, comparison table, specs, use-case chips, final CTA, plus the homepage teaser's own copy/photo-or-video) to be editable from a dedicated wp-admin screen. Added a new `product` CPT (inc/post-types.php) — not publicly routed itself (same "pure data" pattern as `team_member`/`client`: Aurora's real public URL stays the existing WP Page above), `show_ui`/`show_in_menu` true so it gets its own top-level "Products" admin menu (`edit.php?post_type=product`). One `product` post exists so far, titled "Aurora" (slug `aurora` — deliberately the same slug as the WP Page above; WP allows that across two different post types), holding every field via the new "Product Page Content" ACF group (acf-json/, tabs: Hero / Stat Strip / How It Works / Comparison / Specifications / Use Cases / Final CTA / Homepage Teaser). `page-aurora.php` and front-page.php's teaser both query this post by slug and read every field from it — no more hardcoded copy on either surface. If more hardware SKUs are ever added, they'd each be their own `product` post under this same CPT/admin screen.

Superseded 2026-07-31 (see NOTES.md, "Products turnstile + per-product pages" entry): the `aurora` WP Page + page-aurora.php pairing above is retired — `product` is now a real public, routable CPT (`/products/{slug}/`, archive `/products/`; inc/post-types.php), rendered by two new templates, single-product.php and archive-product.php. The old `/aurora/` URL 301s to `/products/aurora/` (inc/redirects.php); the old WP Page (ID 24268) is kept as a draft, not deleted, in case it's ever needed. The "Product Page Content" ACF group (acf-json/) was restructured from 7 fixed tabs into one Flexible Content field, `page_sections` — editors add/remove/reorder section blocks (same 7 types: Hero, Stat Strip, How It Works, Comparison Table, Specifications, Use Cases, Final CTA) per product in wp-admin, giving real page-layout flexibility rather than a fixed template. The "Homepage Turnstile Card" tab (teaser_eyebrow/headline/supporting_line/photo/video/placeholder_caption/link_label) is unchanged and now also feeds archive-product.php's tiles, not just the homepage. Aurora's existing content was migrated into `page_sections` verbatim (7 rows, same order) — zero visual regression, screenshot-confirmed.

Homepage — superseded same day (see NOTES.md, "Products turnstile compact redesign" entry): a first version replaced the old single-card Aurora teaser with a full dark turnstile section positioned right below the hero, reusing the Delivery Model's carousel mechanic. That took up too much homepage space, so it was rebuilt as a compact single-card carousel (`#productsCompactCarousel`, front-page.php) and moved back to its original position, directly before "Why teams choose ITOI" — with one product published it's pixel-equivalent to the original 2026-07-30 teaser. Prev/next arrows and dots only render once a 2nd `product` post exists; `initProductsCarousel()` (assets/js/main.js) is its own lightweight function (no side-peek, unlike Delivery Model), not a shared mechanic — the earlier `initTurnstile(config)` generalization was reverted since it no longer had a second real caller.

Nav — superseded same day: the flat top-level "Aurora" custom link is now "Products" (same menu position, now linking to /products/), with "Aurora" nested under it as a child link — the recommendation from the original addendum above ("revisit as its own 'Products' dropdown grouping if more hardware products are added") was acted on even before a second product exists, since the CPT/routing work made it easy. Flag: Site Settings' `mega_menu_previews` repeater is index-matched to top-level menu items and was never realigned after Aurora's original 2026-07-30 insertion — several mega-menu hover previews currently show the wrong item's copy (see NOTES.md for the exact list); needs a content-owner pass, not silently fixed.

**Specs are explicitly draft**, not real values — the Specifications section (10 cells as of 2026-07-30's RetailNext-sourced update: 6 confirmed, 4 still flagged — sensing type, data captured, storage, lighting range) carries a visible on-page pending-confirmation flag plus a "(draft)" tag on each unconfirmed cell; do not treat these as final until a content owner resolves the still-open sensing-type/video-vs-depth-only question logged in the field's own wp-admin instructions and in NOTES.md. Four TODO(photo)/TODO(illustration) placeholders also remain (hero device-stage, both "How it works" step visuals) — the homepage/archive teaser photo now has a real placeholder video attached (uploaded directly through the Products admin screen) — swap the rest for real assets before launch, don't silently fill with stock photography (§3 imagery rule).

The page's "Trust the data" comparison-section callout links to the real /privacy/ Privacy Policy page, not a dedicated "Trust & Privacy" marketing page — no such page exists in this theme (confirmed absent during the 2026-07-27 liquid-glass wave 5 session, see NOTES.md; that entry already flagged it as possibly-unbuilt content worth a real content-owner decision, still unresolved as of this addendum).


6. Do-not-invent list (exhaustive)


Clients — amended 2026-07-20 for the Customers page/section build, superseding the original 16-name list below. The live site's own portfolio page (itoisolutions.com.au/our-portfolio/) lists 113 real client/venue names across 6 of its own categories — Shopping Centres, Retail, Hospitality & Industry, Councils & Commercial, Financial Institutions, Hotels — verified directly against the page's raw markup (image filenames + alt text, not a paraphrased summary). The client `client` CPT (§4 addendum below) holds this full 113-name list, one per category term. User explicitly chose this scope over staying with the smaller list. The original 16-name list remains the authoritative spelling for names that appear on both: Sony, Samsung, HP, Coca-Cola, Nike, Brisbane City Council, Brisbane Parklands, Macquarie Bank, Armaguard, KFC, Drakes Supermarkets — use these exact spellings ("Macquarie Bank" not "Macquarie", "Armaguard" not "Arma Guard", "Drakes Supermarkets" not "Drakes") wherever they appear. For every other client, use the live portfolio page's own display name verbatim — no further invention beyond what's actually on that page.
Retail industry page marquees (2026-07-21) — full name lists for the record. The Retail industry page's Customers section (/industries/retail/) splits the `client` CPT's existing `Shopping Centres` and `Retail` `client_category` terms into two rolling marquees, relabeled "Shopping Centres & Malls" and "Retail Brands" for that page only. Membership is unchanged from the 113-name portfolio-sourced dataset above (no new names added, no re-categorization) — reproduced here in full so this list doesn't require a DB query to verify against:

Shopping Centres & Malls (27): Bonnyrigg Square Shopping Centre, Carmel Village, Casuarina Square, Chinatown Mall, Como Centre, Crossroads Homemaker Centre, Eastland Shopping Centre, Emporium, Epping Plaza, Grand Central Shopping Centre, Hoppers Crossing, Hyperdome, IGA Whittlesea, Market Lane, Morayfield Homemaker Shopping Centre, Parkmore, Post Office Square Shopping Centre, Prahran Market, Prospect Homemaker Center, Queen Street Mall, Summer Hill Shopping Centre, Summerhill, The District Docklands, Valley Metro, Wellington Square Shopping Centre, Werribee Plaza, Westpoint.

Retail Brands (38): Adventure Megastore, Alex and Ani, Asics, Bose, Build-A-Bear, Cable Melbourne, Camilla and Marc, Camper, Columbia, Dyson Outlets, Ermenegildo Zegna, Forty Winks, Fujifilm, GAZMAN, Happytel, HP, Levi's, Lorna Jane, Millennium Group, Move, Mr Price, Musos Corner, Nike, Onitsuka Tiger, Optus, Perri Cutten, Runway, Samsung, Save the Children, Sony, Sunseeker, Sushi Sushi Group, Swarovski, Swatch, The Smith Family, Tony Bianco, Universal Store, Vinnies.

Flagged, kept as-is (not moved between groups): IGA Whittlesea (a specific supermarket storefront — arguably "Retail Brands," kept under Shopping Centres since that's the live site's own categorization); Prahran Market and Market Lane (markets/precincts, not literally malls, but the closer fit of the two groups); Summerhill (possible near-duplicate of "Summer Hill Shopping Centre" — kept separate per the live site rather than merged, per this section's verbatim-name rule); Valley Metro (venue identity unclear beyond the name); Millennium Group, Move, Runway, Cable Melbourne, Sunseeker (plausible retail brands, not independently confirmed beyond the live portfolio page); Save the Children, The Smith Family, Vinnies (charity/NGO retail operations, categorically different from commercial brands like Nike, but genuine storefront retailers).
Flagged, not resolved: 5 names from the original 16-name list do NOT appear anywhere on the live portfolio page — Lasalle, Ashe Morgan, Mirvac, Pacific Group, BP. Confirmed absent by direct search of the page's raw HTML, not just its rendered summary. They remain real, confirmed clients (independently sourced from the About page's "Commitment from Leading Brands" paragraph per the 2026-07-17 Phase 5 NOTES.md entry) — just not part of the `client` CPT's portfolio-sourced dataset, and not assignable to any `client_category` term since the live site itself doesn't categorize them. Still needs a content-owner decision on whether/how to surface these 5 elsewhere.
Confirmed case-study clients: Drakes Supermarkets, Brisbane City Council, Macquarie Bank.
Contacts: Sean Kiely (sean.kiely@itoisolutions.com.au, +61 4040 90072), Michael Stark (michael.stark@itoisolutions.com.au), Support (support@itoisolutions.com.au, +61 468 765 815, 24/7).
Address: 1/249 ShellHarbour Rd, Port Kembla 2505. Updated 2026-07-28, explicit user correction — supersedes the earlier "206/1–3 Burbank Place, Baulkham Hills, Sydney, NSW 2153" address (which had already drifted out of sync between page-contact.php's ACF-driven card and footer.php's separately-hardcoded copy before this fix — see NOTES.md).
Real metrics: 99.87% LFW accuracy, sub-100ms recognition. No other invented figures — tag placeholders TODO(metric).
Hours: Office Mon–Fri 8:00–17:00 AEST, Support 24/7.


Anything fact-checkable that isn't here and isn't on the live site → tag TODO(fact-check), don't publish as fact.


7. Dev environment — remote sandbox server (not local WAMP)

This supersedes any earlier assumption of a local WAMP install on the developer's own PC. The actual dev environment is a remote Linux sandbox server, reached via SSH, edited via VS Code's Remote-SSH extension. Claude Code runs directly on that server through VS Code's remote integrated terminal — not on the developer's local Windows machine.


Existing install: WordPress already lives at /var/www/html/ on the sandbox server. The current live-site theme is itoiweb (a child theme built on the astra base theme), both present at /var/www/html/wp-content/themes/. The new theme is being built at /var/www/html/wp-content/themes/itoi/ — a fresh, independent theme folder, NOT a modification of itoiweb or an Astra child theme. Confirm this separation stays intact — don't let Claude Code start editing itoiweb or astra by mistake.
Access: SSH (ssh username@IP_ADDRESS), connected to from VS Code via Remote-SSH so the integrated terminal and file explorer both operate directly on the server's filesystem.
No localhost URL applies here — any instruction, script, or Claude Code output that references http://localhost/itoi/ is wrong for this setup and needs to target the sandbox server's actual URL/IP instead. Confirm the real URL WordPress is reachable at on this server (check wp-config.php or ask whoever manages the box) before running any screenshot/Lighthouse/axe commands.
Node.js on the server was outdated (v12) — Node 22+ is required for Claude Code itself; install via nvm (Node Version Manager) rather than the system package manager, to avoid touching whatever else on the server might depend on the old Node version. Confirm node --version reports 22+ before running any npm commands for this project.
Use get_template_directory_uri() etc. and relative paths — never hardcode C:\wamp64\..., localhost, or the sandbox server's specific IP into anything committed to the theme's git repo.
wp-config-local.php (gitignored) for any local DB creds if needed — never commit credentials.
WP_DEBUG + WP_DEBUG_LOG on for this environment; resolve notices, don't suppress them.
Sandbox confirmed safe to build/test on directly — this is not the production site, so mistakes here are recoverable. Still don't touch itoiweb/astra since those represent the current live site's actual theme, which may be a useful reference to look at but should not be edited.



8. SEO baseline


SEO plugin handles meta, sitemap, canonical, Open Graph — configure, don't hand-roll.
JSON-LD: Organization + LocalBusiness site-wide, Service on solutions, Article on case studies/insights.
Every image has real alt text — enforced as a required ACF field.



9. Definition of done per page/template


All content from ACF/WP — no hardcoded copy
Real or TODO(photo)-tagged imagery — never uncredited stock as an ITOI site
Editable end-to-end by a non-technical staffer — verify in the actual WP admin
Screenshot at 375px and 1440px, matches §3
Lighthouse ≥ 90 across the board
axe: zero violations
Keyboard navigable, visible focus states, reduced-motion respected
Meta + schema present (confirmed, not assumed)
Old URL redirect in place if applicable



10. Build phases (stop and checkpoint after each)


Dev environment + theme scaffold — remote sandbox server reachable via SSH/VS Code, Node 22+ confirmed, WP already installed, theme skeleton, Tailwind pipeline, ACF Pro active.
Design system + base templates — tokens (§3, both base and signature — note the corrected black CTA / teal why-choose colors), Manrope/Inter, header.php, footer.php, front-page.php skeleton. Build against preview-verkada-match.html directly — it is the verified reference, not a guess. Screenshot-verify pixel-by-pixel against it before continuing.
Homepage mechanics + signature components — build all 10 verified mechanics from §3 as real working components: rotating ticker, mega-hero with dot-nav slideshow and the Live Detection background visualization, use-cases pill row, "one platform" CTA, "why choose" pill-tabs + split-panel, logo marquee, reserved review slot, industries arrow-nav carousel, product icon row, black bottom CTA. Also build the interactive traffic-demo widget (§3 signature layer). This phase is the differentiator — get it right and checkpoint it in isolation, comparing directly against preview-verkada-match.html, before touching any other page.
Content model — register all CPTs + ACF groups (§4, including the new use_case CPT), confirm clean admin editing UX + ACF JSON sync committed.
Populate content — port the Astro prototype's real copy into the CPTs, respecting §6, carrying over every TODO tag. Use Cases stay as generic placeholders until real content is supplied — do not invent names.
Solutions + use cases + industries templates — archives (tile grids / arrow-nav carousels per §3 mechanics) + detail pages.
Case studies + insights templates — handle empty quotes / TODO metrics gracefully.
About, team, contact, legal.
SEO + performance + accessibility pass — Lighthouse/axe every URL, redirects, schema.
Non-technical staff editing walkthrough — mandatory. If the maintainer can't update a case study unaided, the CMS layer failed. Not skippable.
Deploy to production host, redirects live, DNS cutover.



11. What this project is not


Not Astro or any static/JS-framework build (that prototype is reference-only).
Not a page-builder site.
Not dark-themed (the HUD and editorial directions are both retired).
Not sparse editorial (the Palantir direction is retired) — it's clean conversion-focused SaaS.
Not static — content must be editable by non-technical staff.
Not multi-language for v1.