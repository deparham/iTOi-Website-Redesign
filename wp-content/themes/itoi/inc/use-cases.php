<?php
/**
 * Sitewide "real use cases" aggregation.
 *
 * Migrated 2026-07-30 (see NOTES.md): the 42 real use cases now live as
 * their own `use_case` CPT posts (acf-json/group_34d1b14d47e5.json —
 * photo/video/industry/solution/featured_in_nav), giving editors one
 * central "Use Cases" admin list instead of editing them 7 Industry posts
 * at a time. This used to aggregate the `industry` CPT's own `use_cases`
 * repeater instead — that repeater field has been retired from the
 * Industry — Long-form field group; do not resurrect it as a second data
 * source. This file is still the single place that queries use cases so
 * the /use-cases/ hub, the nav dropdown, the homepage teaser, and each
 * industry's own long-form Use Cases tab all draw from one source.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flat list of every real, industry-linked use case sitewide.
 *
 * @param array $args {
 *     @type bool $featured_only Only rows with the "Featured in nav dropdown" flag set.
 * }
 * @return array[] Each row: key, label, image_id, video, solution_id, solution_title,
 *                  solution_url, industry_id, industry_name, industry_slug, featured_in_nav.
 */
function itoi_get_industry_use_cases( $args = array() ) {
	static $itoi_all_use_cases = null;

	if ( null === $itoi_all_use_cases ) {
		$itoi_all_use_cases = array();

		$itoi_use_case_query = new WP_Query(
			array(
				'post_type'      => 'use_case',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
			)
		);

		foreach ( $itoi_use_case_query->posts as $itoi_uc_post ) {
			$itoi_industry_id = get_field( 'industry', $itoi_uc_post->ID );
			$itoi_solution_id = get_field( 'solution', $itoi_uc_post->ID );

			if ( ! $itoi_industry_id || 'publish' !== get_post_status( $itoi_industry_id )
				|| ! $itoi_solution_id || 'publish' !== get_post_status( $itoi_solution_id ) ) {
				continue;
			}

			$itoi_industry_name = get_field( 'name', $itoi_industry_id ) ?: get_the_title( $itoi_industry_id );

			$itoi_all_use_cases[] = array(
				'key'             => 'use-case-' . $itoi_uc_post->ID,
				'label'           => get_the_title( $itoi_uc_post ),
				'image_id'        => get_field( 'photo', $itoi_uc_post->ID ) ?: 0,
				'video'           => get_field( 'video', $itoi_uc_post->ID ) ?: null,
				'solution_id'     => $itoi_solution_id,
				'solution_title'  => get_field( 'headline', $itoi_solution_id ) ?: get_the_title( $itoi_solution_id ),
				'solution_url'    => get_permalink( $itoi_solution_id ),
				'industry_id'     => $itoi_industry_id,
				'industry_name'   => $itoi_industry_name,
				'industry_slug'   => get_post_field( 'post_name', $itoi_industry_id ),
				'featured_in_nav' => ! empty( get_field( 'featured_in_nav', $itoi_uc_post->ID ) ),
			);
		}
		wp_reset_postdata();
	}

	if ( ! empty( $args['featured_only'] ) ) {
		return array_values(
			array_filter(
				$itoi_all_use_cases,
				function ( $itoi_uc ) {
					return $itoi_uc['featured_in_nav'];
				}
			)
		);
	}

	return $itoi_all_use_cases;
}

/**
 * The mega menu's "Use Cases" dropdown children — the featured subset (one
 * per industry, curated via each use_case post's own "Featured in nav
 * dropdown" checkbox) plus a trailing
 * "View all use cases" link to the /use-cases/ hub. header.php calls this
 * in place of the real WP nav-menu children for that one top-level item —
 * see the comment there for why.
 *
 * @return array[] Each row: title, url.
 */
function itoi_get_nav_use_case_children() {
	$itoi_featured = itoi_get_industry_use_cases( array( 'featured_only' => true ) );
	$itoi_children = array();

	foreach ( $itoi_featured as $itoi_uc ) {
		$itoi_children[] = array(
			'title' => $itoi_uc['label'],
			'url'   => $itoi_uc['solution_url'],
		);
	}

	$itoi_children[] = array(
		'title' => 'View all use cases →',
		'url'   => home_url( '/use-cases/' ),
	);

	return $itoi_children;
}
