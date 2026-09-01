<?php
/**
 * Trust & Stats stat-card grid — shared getter for the "Trust metrics"
 * repeater (Site Settings ▸ Trust & Stats, acf-json/group_73b8c1766c9f.json).
 *
 * 2026-08-31: pulled out of template-parts/home/trust-credibility.php (which
 * used to inline its own empty-repeater fallback) so both consumers — the
 * template's first-4 server-rendered chunk and inc/enqueue.php's full list
 * localized to JS for initTrustMetricsRotation() (homepage.js) — read the
 * exact same data and the exact same fallback, rather than two copies that
 * could drift apart. Same "curated grid first, JS rotates through the rest"
 * pattern already used for the client-logo row (itoiTrustClients).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array[] Each row: stat_value, stat_label. Rows with no stat_value
 *                  are dropped here so neither consumer has to check again.
 */
function itoi_get_trust_metrics() {
	$itoi_rows = get_field( 'trust_metrics', 'option' );

	if ( empty( $itoi_rows ) ) {
		// 2026-08-05: replaced 4 vague descriptors ("Millions", "Real-time",
		// "Multi-site", "Enterprise" — none of them real numbers, none of
		// them specific capabilities either) with precise capability
		// statements — no fabricated figures, per PROJECT.md §6's
		// do-not-invent rule. Same-day follow-up: the separate "PROOF"
		// stat-tiles section further down the page (which held the site's
		// only 2 real confirmed hard numbers) was cut per the homepage
		// consolidation pass (see NOTES.md, "PROOF" -> "Case study
		// spotlight") — those 2 real numbers moved up into this section
		// instead of being lost.
		$itoi_rows = array(
			array( 'stat_value' => '99.87%', 'stat_label' => 'facial recognition accuracy' ),
			array( 'stat_value' => '<100ms', 'stat_label' => 'detection speed' ),
			array( 'stat_value' => 'Multi-site', 'stat_label' => 'reporting across every location' ),
			array( 'stat_value' => 'Australian', 'stat_label' => 'deployment & support' ),
		);
	}

	return array_values(
		array_filter(
			$itoi_rows,
			static function ( $itoi_row ) {
				return ! empty( $itoi_row['stat_value'] );
			}
		)
	);
}
