<?php
/**
 * Technology Partners — sitewide logo grid (template-parts/partners.php),
 * rendered directly above the footer on every page (footer.php). Content
 * lives on its own ACF options page (Technology Partners, acf-json/
 * group_5a1f9c3e7d84.json) as a repeater — name/logo/description/link per
 * card, `min`/`max` both 0 so wp-admin can add/remove any number of
 * partners with no template change, same "repeatable component" pattern
 * as itoi_get_industry_use_cases() (inc/use-cases.php).
 *
 * Not to be confused with "Partners, Not Vendors" (inc/acf.php,
 * group_7f2a4c9e01d3) — that's the About page's 4-card trust-philosophy
 * section (leadership experience, hardware-agnostic, etc.), separate
 * content, separate options page, on purpose.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array[] Each row: name, logo_id, description, link.
 */
function itoi_get_technology_partners() {
	$itoi_rows = get_field( 'technology_partners', 'option' );

	if ( empty( $itoi_rows ) ) {
		// Nothing saved yet on the Technology Partners options page (fresh
		// install / not yet visited in wp-admin) — same fallback pattern
		// every other options-page section in this theme uses (e.g.
		// integrated-platform.php's `?: 'One integrated platform'`), just
		// as a whole array here since this field is a repeater. Mirrors
		// this field's own ACF default_value exactly. Xovis is the one
		// partner with a real page on this site today (the `product` CPT
		// post "Xovis PC2SE Outdoor") — link straight to it rather than
		// leaving a real, linkable destination unlinked; the other 4 have
		// no dedicated page yet, so their cards render with no CTA button
		// (by design — see template-parts/partners.php) until a real URL
		// is added here or in wp-admin.
		$itoi_xovis_product_id = 24357;
		// "Enabled" toggle (product_enabled, inc/products.php) — if Xovis
		// ever gets switched off, this fallback shouldn't keep pointing a
		// partner card at a page that now redirects away.
		$itoi_xovis_link = itoi_is_product_enabled( $itoi_xovis_product_id )
			? itoi_get_product_destination_url( $itoi_xovis_product_id )
			: '';
		$itoi_rows              = array(
			array( 'partner_name' => 'SAFR', 'partner_logo' => 0, 'partner_description' => '', 'partner_link' => '' ),
			array( 'partner_name' => 'RetailNext', 'partner_logo' => 0, 'partner_description' => '', 'partner_link' => '' ),
			array(
				'partner_name'        => 'Xovis',
				'partner_logo'        => 0,
				'partner_description' => '',
				'partner_link'        => $itoi_xovis_link,
			),
			array( 'partner_name' => 'Dahua', 'partner_logo' => 0, 'partner_description' => '', 'partner_link' => '' ),
			array( 'partner_name' => 'Hikvision', 'partner_logo' => 0, 'partner_description' => '', 'partner_link' => '' ),
		);
	}

	$itoi_out = array();
	foreach ( $itoi_rows as $itoi_row ) {
		$itoi_out[] = array(
			'name'        => $itoi_row['partner_name'] ?? '',
			'logo_id'     => $itoi_row['partner_logo'] ?? 0,
			'description' => $itoi_row['partner_description'] ?? '',
			'link'        => $itoi_row['partner_link'] ?? '',
		);
	}

	return $itoi_out;
}
