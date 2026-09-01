# ACF fields map

Complete map of every ACF field group in `acf-json/`, what it attaches to
(a CPT or an Options Page), and every field it defines. Generated as part
of the 2026-08-06 wp-admin content audit (see `docs/acf-content-checklist.md`
for the staff-facing "go fill these in" version of this same information).

Regenerate/verify this list at any time with:

```
php scripts/check-acf-fields.php
```

That script also cross-references every `get_field()`/`the_field()` call in
the theme's PHP against the field names below and fails (exit 1) if any
template references a field name that doesn't exist in any group here —
run it after adding/renaming any field.

**Do not edit field `key`s below by hand** — ACF/WordPress store submitted
values keyed to these, and changing one orphans whatever's already been
saved in wp-admin. Adding new fields, or changing a group's `location`
rules, is safe.

## Options Pages

These live under their own top-level wp-admin menu items (not attached to
any post) — see `inc/acf.php` for where each page itself is registered.

### Site Settings (`group_73b8c1766c9f.json`)
`wp-admin/admin.php?page=site-settings`

The largest and most load-bearing group — nearly every homepage section and
sitewide contact detail reads from here.

- `company_address`, `manager_1_name`, `manager_1_email`, `manager_1_phone`,
  `manager_2_name`, `manager_2_email`, `support_phone`, `support_email`,
  `office_hours`, `support_hours` — contact details used across the footer,
  contact page, and Solution Builder proposal.
- `og_default_image`, `site_tagline` — SEO/social defaults.
- `hero_eyebrow`, `hero_slides` (repeater: `headline`, `subcopy`,
  `is_partnership`, `partner_name`, `partner_logo`, `photo`, `video`) —
  homepage hero slideshow.
- `hero_cta_primary_label/url`, `hero_cta_secondary_label/url`,
  `hero_trust_metrics` (repeater: `text`) — hero's static CTAs/trust row.
- `ticker_messages` (repeater: `text`) — header announcement ticker.
- `why_choose_headline`, `why_choose_photos` (repeater: `tab_label`,
  `title`, `description`, `bullets`, `cta_label`, `cta_url`, `photo`,
  `video`) — "Why choose ITOI" homepage section.
- `mega_menu_previews` (repeater: `nav_label`, `eyebrow`, `headline`,
  `description`) — the hover-preview panel in the mobile mega menu.
  **Index-matched to the real Primary menu's top-level items, not
  label-matched** — see `docs/navigation.md`.
- `platform_demo_teaser_image` — homepage "One integrated platform" teaser.
- `show_traffic_demo` — toggles the homepage traffic-demo widget on/off.
- `proof_stats`, `trust_section_heading`, `trust_metrics` (repeater:
  `stat_value`, `stat_label`, `is_verified_stat`) — Trust & Credibility
  section.

### Solution Builder Settings (`group_bc153137ebbc.json`)
`wp-admin/admin.php?page=find-your-fit-settings`

menu_slug is `find-your-fit-settings` deliberately (see `inc/acf.php`'s
comment) — renaming it would orphan every value already saved under it, so
only the page/menu *title* were changed to "Solution Builder Settings",
not the slug. Same reasoning for the `fyf_` field-name prefix below — it's
a stored key, not user-facing text.

- `fyf_eyebrow`, `fyf_heading`, `fyf_subheading` — the sitewide popup's
  modal-shell copy (footer.php).
- `fyf_trigger_label` — label on both the floating bottom-right trigger
  button and the nav's "Build your solution" link (header.php).
- `sb_q1_text` … `sb_q7_text`, `sb_q7_hint` — the 7 question prompts shown
  by both the popup (footer.php) and the standalone `/solution-builder/`
  page (page-solution-builder.php) — single source, so the two can't drift.
- `sb_efficiency_hours_saved_per_employee_per_week`,
  `sb_efficiency_hourly_rate`, `sb_efficiency_weeks_per_year`,
  `sb_loss_reduction_per_site` — the ROI estimate's formula inputs
  (`inc/solution-builder.php`).

### Delivery Model (`group_13112f7e3503.json`)
`wp-admin/admin.php?page=delivery-model-settings`

- `delivery_model_eyebrow`, `delivery_model_headline`.
- `delivery_model_steps` (repeater: `number`, `step_name`, `description`) —
  the homepage's 4-step delivery rail.

### Partners, Not Vendors (`group_7f2a4c9e01d3.json`)
`wp-admin/admin.php?page=partners-not-vendors-settings`

- `partners_headline`, `partners_intro`, `partners_teaser_line`.
- `partners_cards` (repeater: `front_title`, `back_description`).

## Custom Post Types

### Solution (`group_1e5502ad2d38.json`) — `solution`
Every solution detail page (`single-solution.php`): eyebrow/headline/dek,
hero + tile + highlight media, a WYSIWYG narrative, a process-diagram
repeater, spec strip, feature/capability/spec/integration/FAQ repeaters,
and relationship fields to related industries/use cases/case studies.

### Case Study (`group_1436e8de4e5f.json`) — `case_study`
`client_name`, `editorial_status`, `industry`/`related_solution`
relationships, `headline`, `narrative`, `metrics` repeater, pull quote,
hero image/video (+ `hero_image_is_stock` disclosure flag), `gallery`.

### Industry (3 groups, all `post_type == industry`)
Split across three field groups so the admin edit screen's tabs stay
manageable — all three attach to the same CPT and all show on every
`industry` post's edit screen simultaneously:
- `group_e0fc2e98feb6.json` ("Industry") — the base fields: `name`,
  `summary`, `related_solutions`, `hero_image`/`hero_video`,
  `client_examples` repeater.
- `group_cb9a360b89a5.json` ("Industry — Long-form Page") — the optional
  long-form page (`longform_enabled` gate): overview copy/media, process
  diagram, feature rows, use-cases/why/solutions/customers sections, logo
  strip rows.
- `group_9f3a1c7e5d20.json` ("Industry — Hero Interaction") — the
  per-industry interactive hero widget (`funnel_enabled` gate) — a
  different repeater set per industry type (hospitality/banking/
  government/logistics/stadiums/casinos), since each industry's homepage
  hero interaction is bespoke.

### Use Case (`group_34d1b14d47e5.json`) — `use_case`
`photo`/`video`, `industry` + `solution` (`post_object`, not
`relationship` — see `inc/use-case-bulk-edit.php`), `featured_in_nav`
(controls whether it appears in the Solutions dropdown's Use Cases list —
see `header.php`'s `itoi_get_nav_use_case_children()`).

### Product (`group_f0b5edf92aed.json`) — `product`
`page_sections` flexible content (layouts: `hero`, `stat_strip`,
`how_it_works`, `comparison`, `specifications`, `use_cases`, `final_cta`)
plus a flat set of `teaser_*` fields used by the homepage products
carousel and the Products archive. `dek` and `product_price` were added
in the 2026-08-06 audit — `inc/schema.php`'s Product structured data has
always read both, but neither field existed until now (found via
`scripts/check-acf-fields.php`); they feed SEO/JSON-LD only, nothing shown
on the page itself.

### Team Member (`group_e8cfa08ab699.json`) — `team_member`
`name`, `role`, `department`, `bio`, `photo`/`video`, `email`,
`linkedin_url`.

### Client (`group_d803f591e25d.json`) — `client`
`logo`, `case_study` (`post_object` link back to the case study this
client's logo represents).

### Insight (`group_e46fe029130b.json`) — `insight`
`dek`, `author` (`relationship` to `team_member`).

### Guide (`group_b295a4c975ec.json`) — `guide`
`title`, `industry` (`post_object`), `dek`, `body` (WYSIWYG),
`related_solution`, `read_time_minutes`, `published_date`.

### Glossary Term (`group_7e5d515c4ec7.json`) — `glossary_term`
`term`, `definition`, `related_guides` (`relationship`).

### Solution Builder Lead (`group_e887925f09b1.json`) — `sb_lead`
Write-only from the front end (`inc/solution-builder.php`'s AJAX handler) —
staff read these, never create them via "Add New" (blocked at the
capability level, see `inc/post-types.php`). `sb_name`, `sb_email`,
`sb_company`, `sb_phone`, `sb_submitted_at`, `sb_business_type`,
`sb_employees`, `sb_sites`, `sb_existing_cctv`, `sb_existing_pos`,
`sb_cloud_based`, `sb_challenges`, `sb_recommended_solutions`,
`sb_roi_total`, `sb_timeline`.

### Page Builder (`group_be65893ec9b5.json`) — `page`
Generic flexible-content builder available on any plain WP Page (not just
the theme's dedicated `page-*.php` templates): `live_data_hero`,
`traffic_demo_widget`, `logo_strip`, `stat_band`, `solutions_tile_grid`,
`industries_tile_grid`, `case_study_feed`, `text_image`, `quote`,
`cta_band` layouts.
