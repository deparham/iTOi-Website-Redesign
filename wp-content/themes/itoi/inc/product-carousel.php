<?php
/**
 * "Real Use Cases" homepage carousel — Product-driven data source.
 *
 * 2026-08-24: this section (front-page.php → template-parts/home/use-cases.php,
 * id="useCasesCarousel") switched from being driven by the `use_case` CPT
 * to being driven directly by `product` posts — explicit instruction, so
 * editors manage these cards from each Product's own edit screen ("Real
 * Use Cases Card" tab, acf-json/group_f0b5edf92aed.json) instead of a
 * separate CPT. A product only appears here if its "Show in Real Use
 * Cases carousel" checkbox is on — off by default, so a half-built
 * product page doesn't show up before it's ready.
 *
 * `inc/use-cases.php` (the `use_case` CPT + itoi_get_industry_use_cases())
 * is untouched and still the single source for the /use-cases/ hub, the
 * mega-menu nav dropdown, and each industry's long-form Use Cases tab —
 * this is a genuinely separate, unrelated data source that happens to
 * feed a section that used to read "Real Use Cases" content. Real
 * consequence of the switch: only products with this tab filled in show
 * here at all — 2 today (Aurora, Xovis PC2SE Outdoor), not the 9 the
 * `use_case`-driven version had, until more products are added/populated
 * (explicit, confirmed tradeoff — not an oversight).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array[] Each row: key, label, image_id, video, product_id,
 *                  product_name, product_url, product_description,
 *                  product_photo_id, product_video.
 */
function itoi_get_product_showcase_cards() {
	$itoi_query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			// "Enabled" toggle (product_enabled, inc/products.php) AND'd
			// alongside the existing carousel opt-in below — a product
			// needs both to show here.
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- handful of `product` posts total, no viable non-meta alternative for a true_false field.
				'relation' => 'AND',
				array(
					'key'   => 'show_in_use_cases_carousel',
					'value' => '1',
				),
				itoi_product_enabled_meta_query(),
			),
		)
	);

	$itoi_cards = array();

	foreach ( $itoi_query->posts as $itoi_post ) {
		$itoi_id = $itoi_post->ID;

		$itoi_cards[] = array(
			'key'                  => 'product-card-' . $itoi_id,
			'label'                => get_field( 'use_case_category_label', $itoi_id ) ?: get_the_title( $itoi_id ),
			'image_id'             => get_field( 'use_case_front_photo', $itoi_id ) ?: 0,
			'video'                => get_field( 'use_case_front_video', $itoi_id ) ?: null,
			'product_id'           => $itoi_id,
			'product_name'         => get_the_title( $itoi_id ),
			'product_url'          => itoi_get_product_destination_url( $itoi_id ),
			// Reuses the existing "Homepage Turnstile Card" fields rather
			// than duplicating description/photo/video fields a second
			// time — same real content already used by the products
			// archive grid (archive-product.php) and the (retired)
			// homepage turnstile. Video (if set) takes priority over the
			// photo, same rule as everywhere else itoi_media_cover() is
			// used — the photo doubles as its poster frame.
			'product_description'  => get_field( 'teaser_supporting_line', $itoi_id ) ?: '',
			'product_photo_id'     => get_field( 'teaser_photo', $itoi_id ) ?: 0,
			'product_video'        => get_field( 'teaser_video', $itoi_id ) ?: null,
		);
	}

	wp_reset_postdata();

	return $itoi_cards;
}
