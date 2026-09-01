<?php
/**
 * ACF Options Pages. Field groups themselves live in acf-json/ (local JSON,
 * committed) — ACF loads them automatically, nothing to register here for
 * the fields, only the options page they attach to.
 *
 * 2026-08-10 (pentest fix): capability raised from 'edit_posts' to
 * 'manage_options' on all four pages below. 'edit_posts' is held by every
 * Contributor, and several fields here (hero_slides / why_choose_photos)
 * are localized into front-end JS that renders them via innerHTML/attribute
 * concatenation (assets/js/homepage.js) — output is now escaped there too
 * (inc/enqueue.php), but the capability was the bigger gap: any
 * Contributor-level account could otherwise edit sitewide settings pages
 * meant for trusted staff only.
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
			'capability' => 'manage_options',
			'icon_url'   => 'dashicons-search',
			'position'   => 59,
		)
	);

	acf_add_options_page(
		array(
			'page_title' => 'Site Settings',
			'menu_title' => 'Site Settings',
			'menu_slug'  => 'site-settings',
			'capability' => 'manage_options',
			'icon_url'   => 'dashicons-admin-generic',
			'position'   => 60,
		)
	);

	acf_add_options_page(
		array(
			'page_title' => 'Partners, Not Vendors',
			'menu_title' => 'Partners, Not Vendors',
			'menu_slug'  => 'partners-not-vendors-settings',
			'capability' => 'manage_options',
			'icon_url'   => 'dashicons-groups',
			'position'   => 62,
		)
	);

	// 2026-08-24: sitewide "Our Partners" technology-partner logo grid
	// (footer.php, directly above <footer>). Deliberately a separate
	// options page from "Partners, Not Vendors" above — that one is the
	// About page's 4-card trust-philosophy section (leadership experience,
	// hardware-agnostic, account manager, support), unrelated content; two
	// pages named near-identically in the wp-admin sidebar is the price of
	// not conflating them.
	acf_add_options_page(
		array(
			'page_title' => 'Technology Partners',
			'menu_title' => 'Technology Partners',
			'menu_slug'  => 'technology-partners-settings',
			'capability' => 'manage_options',
			'icon_url'   => 'dashicons-share',
			'position'   => 63,
		)
	);
}
add_action( 'acf/init', 'itoi_acf_options_pages' );
