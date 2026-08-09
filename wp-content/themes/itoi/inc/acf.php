<?php
/**
 * ACF Options Pages. Field groups themselves live in acf-json/ (local JSON,
 * committed) — ACF loads them automatically, nothing to register here for
 * the fields, only the options page they attach to.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function itoi_acf_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => 'Find Your Fit Settings',
			'menu_title' => 'Find Your Fit',
			'menu_slug'  => 'find-your-fit-settings',
			'capability' => 'edit_posts',
			'icon_url'   => 'dashicons-search',
			'position'   => 59,
		)
	);

	acf_add_options_page(
		array(
			'page_title' => 'Site Settings',
			'menu_title' => 'Site Settings',
			'menu_slug'  => 'site-settings',
			'capability' => 'edit_posts',
			'icon_url'   => 'dashicons-admin-generic',
			'position'   => 60,
		)
	);

	acf_add_options_page(
		array(
			'page_title' => 'Delivery Model',
			'menu_title' => 'Delivery Model',
			'menu_slug'  => 'delivery-model-settings',
			'capability' => 'edit_posts',
			'icon_url'   => 'dashicons-networking',
			'position'   => 61,
		)
	);

	acf_add_options_page(
		array(
			'page_title' => 'Partners, Not Vendors',
			'menu_title' => 'Partners, Not Vendors',
			'menu_slug'  => 'partners-not-vendors-settings',
			'capability' => 'edit_posts',
			'icon_url'   => 'dashicons-groups',
			'position'   => 62,
		)
	);
}
add_action( 'acf/init', 'itoi_acf_options_pages' );
