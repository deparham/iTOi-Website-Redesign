# Navigation

Target structure and status for the Primary menu (`Appearance → Menus`).
Companion to `docs/content-style-guide.md`. Established 2026-08-05.

## Target: 6 top-level items

| Item | Suggested children |
|---|---|
| Platform | Platform overview · Products (Aurora, Xovis, future SKUs) |
| Solutions | The 8 real `solution` categories (`intelligence-analytics`, `customer-engagement-signage`, `sensory-intelligence`, `workforce-ops-robotics`, `cctv-video-loss-prevention`, `security-access-inventory`, `back-of-house-integration`, `it-network-infrastructure`) |
| Industries | The 7 real `industry` entries |
| Customers | Case studies · Customers/portfolio page |
| Resources | Insights · Guides · Glossary · FAQ |
| Company | About · Team · Contact |

Contact stays out of this list as the header's standalone CTA button
(`header.php`), not a nav item.

Do not invent a "Use Cases" as a 7th top-level item — the real 42
industry-linked use cases already surface via the homepage teaser, the
`/use-cases/` hub, and can live as children under Solutions or Industries
instead of their own top-level slot, per the plan this pass is implementing.

## What this session changed (code-side)

- `header.php`: the "Use Cases" dropdown's real-content detection no longer
  matches the menu item's visible title text (fragile — broke the moment
  anyone renamed the label). It now checks the item's destination URL
  (`/use-cases/`) or an optional `dynamic-use-cases` CSS class set on the
  menu item in `Appearance → Menus → Screen Options → CSS Classes`.
- `header.php` / `assets/js/main.js`: desktop dropdowns (`.nav-dropdown`,
  ≥1180px) gained a JS layer on top of the existing CSS hover/focus
  behaviour — Escape closes and returns focus to the trigger, a short delay
  before closing on mouseleave (was instant), and tap-to-reveal on
  touch-primary devices (`(hover: none)`). The CSS-only behaviour still
  works unassisted if the script fails to load.
- `acf-json/group_73b8c1766c9f.json`: `mega_menu_previews`' row count was
  hard-locked at exactly 5, silently misaligning every preview after
  "Products" was added as a 6th top-level item (flagged but never fixed —
  see `PROJECT.md`'s "Products admin" addendum). Relaxed to a flexible
  0–8 rows; **a content editor still needs to add the missing row(s) in
  wp-admin so the count matches the real Primary menu** — this fix only
  removes the artificial ceiling, it doesn't add the missing content.
- Dead `#` parent links: already handled defensively in `header.php` (a
  parent with no real URL falls back to its first child's URL) — this was
  correct before this pass and is unchanged.

## What still needs wp-admin access (not done here)

This session has no WP-CLI and no database write access, so the following
need a site editor in `Appearance → Menus`:

1. Confirm the Primary menu's actual current top-level item count and
   labels (this doc's target table is a recommendation, not a confirmed
   live state).
2. Restructure to the 6 items above — move existing child pages under the
   right parent, remove any placeholder/obsolete URLs.
3. Confirm every parent item resolves to a real page or a deliberate
   dropdown-only interaction (no editor-facing dead links).
4. Add/remove `mega_menu_previews` rows (Site Settings options page) to
   match the final item count and order exactly — this field is
   index-matched, not label-matched.
5. Once the count is 6 or fewer, re-test `header.php`'s `min-[1180px]`
   breakpoint (see its inline comment) — it may be able to drop to the
   sitewide `min-[980px]` breakpoint used everywhere else in this file.
