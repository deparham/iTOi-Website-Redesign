<?php
/**
 * Renders the primary nav menu's markup exactly matching preview-verkada-match.html's
 * dropdown structure, but driven by a real, editable WP nav menu (Appearance -> Menus)
 * instead of hardcoded links.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Walker_Nav_Menu for the primary nav — outputs the dropdown markup
 * matching preview-verkada-match.html rather than core's default <ul>/<li>
 * structure, driven by a real, editable WP nav menu.
 */
class Itoi_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Opens a dropdown level's wrapping <div>.
	 *
	 * @param string        $output Passed by reference, appended to.
	 * @param int           $depth  Menu item depth.
	 * @param stdClass|null $args Nav menu args (unused here).
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="invisible absolute left-0 top-full mt-1.5 min-w-[250px] translate-y-1.5 rounded-[10px] border border-line bg-white p-2.5 opacity-0 shadow-[0_24px_48px_-20px_rgba(14,17,22,0.18)] transition-all duration-150 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">';
	}

	/**
	 * Closes a dropdown level's wrapping <div>.
	 *
	 * @param string        $output Passed by reference, appended to.
	 * @param int           $depth  Menu item depth.
	 * @param stdClass|null $args Nav menu args (unused here).
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</div>';
	}

	/**
	 * Renders one menu item — a top-level button/link (with dropdown toggle
	 * markup when it has children) at depth 0, or a plain dropdown link at
	 * any deeper level.
	 *
	 * @param string        $output Passed by reference, appended to.
	 * @param WP_Post       $item   The menu item being rendered.
	 * @param int           $depth  Menu item depth.
	 * @param stdClass|null $args Nav menu args (unused here).
	 * @param int           $id     Menu item ID (unused here).
	 */
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

	/**
	 * Closes out a depth-0 menu item's wrapping <div> (button/link items at
	 * deeper levels have no wrapper to close).
	 *
	 * @param string        $output Passed by reference, appended to.
	 * @param WP_Post       $item   The menu item being closed (unused here).
	 * @param int           $depth  Menu item depth.
	 * @param stdClass|null $args Nav menu args (unused here).
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</div>';
		}
	}
}
