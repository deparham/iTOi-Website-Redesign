<?php
/**
 * Core theme supports and setup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Self-hosted typeface. Was Inter (PROJECT.md §3's original "Inter or Manrope",
// see NOTES.md for that choice) until 2026-08-21, when an explicit
// instruction swapped the sitewide font to Lora (a serif) — a deliberate,
// confirmed override of that original rule, not a drift; both
// CLAUDE.md/PROJECT.md §3 updated to match. See src/tailwind.css's Lora
// @font-face block for the actual self-hosted asset.
define( 'ITOI_FONT_LABEL', 'Lora' );

function itoi_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'custom-logo', array(
		'height'      => 94,
		'width'       => 279,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// 2026-08-06 (wp-admin content audit): 'footer' was registered but never
	// actually used by footer.php — its Company/Support columns were
	// hardcoded <a> tags instead. Rather than force those two columns into
	// one shared 'footer' menu (which would need menu-item-parent grouping
	// logic to know which items belong under which heading), each column
	// gets its own location so staff can manage them as two independent,
	// flat menus in Appearance -> Menus. 'footer' itself is left registered
	// unused rather than removed — deregistering it would silently unassign
	// any menu a site editor may have already attached to it.
	register_nav_menus(
		array(
			'primary'        => __( 'Primary Navigation', 'itoi' ),
			'footer'         => __( 'Footer Navigation', 'itoi' ),
			'footer-company' => __( 'Footer — Company column', 'itoi' ),
			'footer-support' => __( 'Footer — Support column', 'itoi' ),
		)
	);

	// team_member `photo` renders in an aspect-square card (page-team.php,
	// front-page.php team teaser) that reaches ~410px CSS width at the
	// 1280px-container desktop breakpoint — 'medium' (300px max) was
	// upscaling and blurring even before accounting for retina. Hard-cropped
	// to match the aspect-square display exactly; 800px covers ~2x that
	// widest known display width for crisp rendering on retina screens.
	add_image_size( 'team-photo', 800, 800, true );
}
add_action( 'after_setup_theme', 'itoi_theme_setup' );
