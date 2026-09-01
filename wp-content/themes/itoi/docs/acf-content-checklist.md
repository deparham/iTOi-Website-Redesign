# wp-admin content checklist

A deployment/onboarding checklist for staff — everything a content editor
should check or fill in after this theme is deployed to a real site.
Companion to `docs/acf-fields.md` (the technical field-by-field map).

Every field listed here already has a sensible fallback baked into the
template that reads it — nothing on the site will look broken if a field
below is left empty. This checklist exists so real content replaces those
fallbacks, not because the fallbacks are broken.

## Navigation menus

Checked directly against the live database as of this audit (2026-08-06):

- **Primary** (`Appearance → Menus`) — already exists and is assigned to
  the Primary Navigation location. 6 top-level items: Solutions,
  Products, Industries, Customers, Resources, Company. Nothing to set up
  here — if this ever needs restructuring, see `docs/navigation.md`.
- **Footer — Company column** / **Footer — Support column** — two *new*
  locations added by this audit (`inc/theme-setup.php`), replacing what
  used to be 4+3 hardcoded links in `footer.php`. **No menu is assigned to
  either yet** — the footer currently shows a built-in fallback
  (`itoi_footer_company_fallback_menu()` / `itoi_footer_support_fallback_menu()`
  in `inc/nav-walker.php`) that reproduces the previous hardcoded links
  exactly, so nothing is broken, but a site editor should:
  1. Go to `Appearance → Menus → create a new menu`, name it e.g.
     "Footer — Company", add the links it should show (About, Case
     Studies, Careers, Contact, or whatever the real set should be), and
     assign it to the **Footer — Company column** location.
  2. Repeat for **Footer — Support column**.
  3. Once a real menu is assigned to a location, the fallback stops being
     used automatically — no code change needed.
- **Footer Navigation** (the original, single `footer` location,
  registered before this audit) — still registered but nothing in
  `footer.php` reads it. Left in place rather than removed, in case a
  menu is already assigned to it that a future template wants to use.

## Site Settings (`wp-admin/admin.php?page=site-settings`)

- [ ] Hero eyebrow, hero slides (headline/subcopy/photo/video per slide,
  max 2 rows), hero CTA labels + URLs, hero trust metrics (exactly 3).
- [ ] Ticker messages (rotating announcement banner) — currently renders
  only the first row (`header.php`, 2026-08-05 — no longer rotates).
- [ ] Why Choose ITOI headline + 5 tabs (label/title/description/
  bullets/CTA/photo or video each) — **the 5 tabs' `cta_url` fields are
  still the placeholder `#`** (flagged in the field's own `instructions`
  in `acf-json/group_73b8c1766c9f.json` since before this audit) —
  confirm real destinations and update.
- [ ] Mega menu category previews — **must have exactly one row per
  top-level Primary menu item, in the same order** (index-matched, not
  label-matched — see `docs/navigation.md`). Currently 6 rows for a
  6-item menu; re-check this any time the Primary menu's top-level items
  change count or order.
- [ ] Platform demo teaser image, `show_traffic_demo` toggle, proof
  stats, trust & credibility heading + exactly 4 metrics.
- [ ] Company address, both managers' name/email/phone, support
  phone/email, office hours, support hours — used across the footer,
  contact page, and the Solution Builder proposal/admin footer text.
- [ ] **New fields added by this audit** (all currently on their
  fallback — the exact previous hardcoded text):
  - `company_name` — public-facing brand name (footer + admin footer
    text). Deliberately separate from `Settings → General → Site Title`,
    which on this install is "I TO I Web", not the public brand name.
  - `company_tagline`, `footer_location_line` — the two lines under/next
    to the footer brand name.
  - `integrated_platform_headline`, `integrated_platform_body`,
    `integrated_platform_cta_label` — homepage "One integrated platform"
    section.
  - `error_404_heading`, `error_404_description`,
    `error_404_button_primary_label`, `error_404_button_secondary_label`
    — the 404 page.

## Solution Builder Settings (`wp-admin/admin.php?page=find-your-fit-settings`)

Still under this URL/menu_slug — see `inc/acf.php`'s comment for why the
slug wasn't renamed even though the page now says "Solution Builder
Settings" everywhere in its own UI.

- [ ] Eyebrow, heading, subheading, floating trigger button label.
- [ ] **New fields added by this audit** (all on fallback):
  - `sb_q1_text` … `sb_q7_text`, `sb_q7_hint` — the 7 question prompts,
    shared by the popup (`footer.php`) and the standalone page
    (`page-solution-builder.php`).
  - `sb_efficiency_hours_saved_per_employee_per_week` (2),
    `sb_efficiency_hourly_rate` (35), `sb_efficiency_weeks_per_year` (52),
    `sb_loss_reduction_per_site` (8000) — the ROI estimate's formula. The
    disclaimer sentence shown with the estimate is generated from these
    same 4 values, so it updates itself if these change — no separate
    disclaimer field to keep in sync.

## Delivery Model (`wp-admin/admin.php?page=delivery-model-settings`)

- [ ] Eyebrow, headline, and the 4-step delivery rail (number/step
  name/description each).

## Partners, Not Vendors (`wp-admin/admin.php?page=partners-not-vendors-settings`)

- [ ] Headline, intro copy, homepage teaser line, and the flip-card
  repeater (front title + back description per card).

## Per-post-type content

These aren't Options Pages — check a representative sample of existing
posts of each type, not a single settings screen:

- [ ] **Solutions** (8 expected, one per category) — eyebrow/headline/
  dek/tagline, hero + tile + highlight media, narrative, process
  diagram, spec strip, features/capabilities/specs/integrations/FAQs,
  related industries/use cases/case studies.
- [ ] **Industries** (7 expected) — name/summary/hero media/client
  examples (base fields); long-form page content if `longform_enabled`;
  the per-industry interactive hero widget if `funnel_enabled` (fields
  vary by industry — hospitality/banking/government/logistics/stadiums/
  casinos each use a different repeater).
- [ ] **Case studies** — client name, editorial status, industry +
  related solution, headline, narrative, metrics, pull quote + hero
  media. `hero_image_is_stock` should be checked truthfully — it's an
  editorial disclosure, not cosmetic.
- [ ] **Products** — `page_sections` flexible content per product (hero,
  stat strip, how it works, comparison, specs, use cases, final CTA) plus
  the flat `teaser_*` fields used by the homepage carousel. Two more
  fields found empty by `scripts/check-acf-fields.php` during this audit
  and added: `dek` (SEO description) and `product_price` (numeric AUD,
  optional — leave empty for quote-only products) — both feed
  `inc/schema.php`'s Product structured data only, neither is shown on
  the page itself. Currently empty for both real products (Aurora, PC2SE
  Outdoor); fill in if/when SEO-visible pricing is wanted.
- [ ] **Insights** — dek + author (relationship to Team Member — the
  `dashboard_glance_items` count currently shows **0 published Insights**,
  confirmed live during this audit; if that's not expected, check draft
  status on existing posts).
- [ ] **Guides** (15 published, confirmed live) — dek, body, related
  solution, read time, published date.
- [ ] **Team members** — name/role/department/bio/photo/email/LinkedIn,
  referenced by Insights' author field and `/team/`.
- [ ] **Clients** — logo + case study link, referenced by
  `page-customers.php` and the industry long-form page's logo strips.
- [ ] **Use cases** (42, confirmed live) — photo/video, industry +
  solution links, `featured_in_nav` (controls whether it surfaces in the
  Solutions dropdown's Use Cases list).

## Verifying field coverage after any future ACF change

Run this any time a field is added, renamed, or a `get_field()` call is
added to a template:

```
php scripts/check-acf-fields.php
```

Exit code 0 means every string-literal `get_field()`/`the_field()` call in
the theme's PHP resolves to a real field in `acf-json/`. A non-zero exit
lists exactly which field name and file/line don't match anything — fix
by adding the missing field to the right group in `acf-json/`, or by
removing the stale `get_field()` call if the field was abandoned.
