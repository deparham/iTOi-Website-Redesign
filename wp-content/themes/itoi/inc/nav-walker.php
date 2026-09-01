<?php
/**
 * Renders the primary nav menu's markup exactly matching preview-verkada-match.html's
 * dropdown structure, but driven by a real, editable WP nav menu (Appearance -> Menus)
 * instead of hardcoded links.
 *
 * 2026-08-06 (wp-admin content audit): Itoi_Nav_Walker below is NOT
 * currently used — header.php's primary nav (both the desktop flat bar and
 * the mobile #megaMenu) builds its markup inline instead of calling
 * wp_nav_menu() with this walker (see header.php's $itoi_mega_items loop).
 * That inline approach already reads from the same real WP menu this
 * walker was written for, handles the header's specific two-tier
 * desktop/mobile output, and works correctly — refactoring it onto this
 * walker isn't attempted here, since it risks the exact dropdown/mega-menu
 * structure both templates depend on for no functional gain. Kept, not
 * deleted, in case a future template wants a plain wp_nav_menu() dropdown
 * matching the original mockup's markup.
 *
 * Itoi_Footer_Nav_Walker (below it) IS used, by footer.php's Company and
 * Support columns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Itoi_Nav_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="invisible absolute left-0 top-full mt-1.5 min-w-[250px] translate-y-1.5 rounded-[10px] border border-line bg-white p-2.5 opacity-0 shadow-[0_24px_48px_-20px_rgba(14,17,22,0.18)] transition-all duration-150 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</div>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$has_children = in_array( 'menu-item-has-children', $item->classes, true );

		if ( 0 === $depth ) {
			if ( $has_children ) {
				$output .= '<div class="group relative">';
				$output .= '<button class="flex items-center gap-1 rounded-md px-3.5 py-3 text-[14.5px] font-semibold text-text hover:bg-hero-bg" aria-haspopup="true">'
					. esc_html( $item->title ) . ' <span class="text-[9px] opacity-50">&#9662;</span></button>';
			} else {
				$output .= '<div class="relative">';
				$output .= '<a href="' . esc_url( $item->url ) . '" class="flex items-center gap-1 rounded-md px-3.5 py-3 text-[14.5px] font-semibold text-text hover:bg-hero-bg">'
					. esc_html( $item->title ) . '</a>';
			}
		} else {
			$output .= '<a href="' . esc_url( $item->url ) . '" class="block rounded-md px-3 py-2.5 text-sm font-semibold hover:bg-hero-bg">'
				. esc_html( $item->title ) . '</a>';
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</div>';
		}
	}
}

/**
 * Footer Company/Support columns — flat list of <a> tags, no <ul>/<li>
 * wrapper, matching the hardcoded markup these columns used before this
 * walker existed exactly. Single depth only (footer columns aren't nested);
 * any child items a staff member adds in Appearance -> Menus are silently
 * skipped via 'depth' => 1 on the wp_nav_menu() call, not rendered oddly.
 */
class Itoi_Footer_Nav_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$output .= '<a href="' . esc_url( $item->url ) . '" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">' . esc_html( $item->title ) . '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/**
 * fallback_cb for the 'footer-company' location — renders while no menu has
 * been assigned yet in Appearance -> Menus, so the footer never renders
 * empty/broken before a site editor sets one up. Same links/order/wording
 * footer.php hardcoded before this location existed (see NOTES.md,
 * "UX: fix dead footer links" — Careers has no dedicated page yet, so it
 * routes to Contact same as it did hardcoded).
 */
function itoi_footer_company_fallback_menu() {
	$links = array(
		array( 'label' => 'About', 'url' => home_url( '/about/' ) ),
		array( 'label' => 'Case Studies', 'url' => home_url( '/case-studies/' ) ),
		array( 'label' => 'Careers', 'url' => home_url( '/contact/' ) ),
		array( 'label' => 'Contact', 'url' => home_url( '/contact/' ) ),
	);
	foreach ( $links as $link ) {
		echo '<a href="' . esc_url( $link['url'] ) . '" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">' . esc_html( $link['label'] ) . '</a>';
	}
}

/**
 * fallback_cb for the 'footer-support' location — see
 * itoi_footer_company_fallback_menu() above for why this exists.
 */
function itoi_footer_support_fallback_menu() {
	$links = array(
		array( 'label' => 'Contact Support', 'url' => home_url( '/contact/' ) ),
		array( 'label' => 'Product Updates', 'url' => home_url( '/contact/' ) ),
	);
	foreach ( $links as $link ) {
		echo '<a href="' . esc_url( $link['url'] ) . '" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">' . esc_html( $link['label'] ) . '</a>';
	}
}
