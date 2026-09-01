<?php
/**
 * Product link resolution — shared by every place sitewide that links to a
 * `product` post (Products archive, Real Use Cases carousel, Partners
 * section, and the product's own single page's self-redirect).
 *
 * 2026-08-26: added `external_link_url` (acf-json/group_f0b5edf92aed.json,
 * "Product Page Content") — explicit instruction: not every product needs
 * a full dedicated page built from its Page Sections; a product with this
 * field set links straight to that URL everywhere instead of its own
 * /products/{slug}/ permalink, and single-product.php itself redirects
 * there rather than rendering an empty/unfinished page at the real
 * permalink. Leave empty (the default) for completely unchanged behavior
 * — a real, normal product page.
 *
 * 2026-08-31: added `product_enabled` (same field group), a plain
 * on/off — off hides the product everywhere (Products archive, Real Use
 * Cases carousel, Partners section) and its own page redirects to the
 * Products archive instead of rendering. Same show/hide-toggle pattern
 * already used elsewhere on this site (the Portfolio page's
 * enable_portfolio_page, Real Use Cases' show_in_use_cases_carousel) —
 * an explicit field, not repurposing WordPress's own Draft/Publish
 * status, for consistency with those.
 *
 * Real gotcha, confirmed by testing before relying on it anywhere: for a
 * product that has never had this field saved (i.e. every product that
 * existed before this field did), get_field('product_enabled', $id)
 * returns NULL, not the field's default_value of 1/true — ACF only
 * applies a field's default when rendering the edit-screen form, not
 * when reading an unset value back. itoi_is_product_enabled() below is
 * the one place that decision lives; every check in this codebase must
 * go through it (or the equivalent meta_query shape, see
 * archive-product.php / inc/product-carousel.php) rather than reading
 * the raw field directly, or every pre-existing product silently reads
 * as disabled.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param int $product_id A `product` post ID.
 * @return string The URL every link to this product should point to.
 */
function itoi_get_product_destination_url( $product_id ) {
	$itoi_external_url = get_field( 'external_link_url', $product_id );
	return $itoi_external_url ?: get_permalink( $product_id );
}

/**
 * @param int $product_id A `product` post ID.
 * @return bool True unless this product's `product_enabled` field has
 *              been explicitly switched off — never-saved (NULL) counts
 *              as enabled, matching the field's own documented default.
 */
function itoi_is_product_enabled( $product_id ) {
	$itoi_value = get_field( 'product_enabled', $product_id );
	return null === $itoi_value || (bool) $itoi_value;
}

/**
 * The same "enabled unless explicitly turned off" rule as
 * itoi_is_product_enabled(), expressed as a meta_query fragment for
 * WP_Query — SQL-side filtering can't call get_field() per row, so this
 * has to independently match "no row saved yet" OR "saved and not '0'".
 * Nest this into a query's own meta_query (with 'relation' => 'AND'
 * alongside any other conditions) rather than duplicating it ad hoc.
 *
 * @return array A meta_query clause (itself has 'relation' => 'OR').
 */
function itoi_product_enabled_meta_query() {
	return array(
		'relation' => 'OR',
		array(
			'key'     => 'product_enabled',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => 'product_enabled',
			'value'   => '0',
			'compare' => '!=',
		),
	);
}
