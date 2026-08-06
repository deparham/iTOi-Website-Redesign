<?php
/**
 * ACF Options Pages. Field groups themselves live in acf-json/ (local JSON,
 * committed) — ACF loads them automatically, nothing to register here for
 * the fields, only the options page they attach to.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function itoi_acf_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	// 2026-08-06 (wp-admin content audit): "Find Your Fit" was the popup's
	// name before the 2026-07-24 rework that made it run the Solution
	// Builder's 7 questions and hand off to /solution-builder/ instead of
	// its own inline results (see footer.php, NOTES.md) — the options page
	// label never caught up, so it still read "Find Your Fit" in wp-admin
	// long after nothing on the front end was called that anymore.
	// page_title/menu_title changed to match; menu_slug deliberately left
	// as 'find-your-fit-settings' — ACF stores every field on this page
	// under that slug as its option_name prefix, so changing it would
	// orphan every value staff have already saved here (they'd all need
	// re-entering under the new slug). The fyf_ field-name prefix
	// (fyf_eyebrow, fyf_heading, etc.) is the same story and is left alone
	// for the same reason — it's a field key, not user-facing text.
	acf_add_options_page(
		array(
			'page_title' => 'Solution Builder Settings',
			'menu_title' => 'Solution Builder',
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
