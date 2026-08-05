<?php
/**
 * Renders the primary nav menu's markup exactly matching preview-verkada-match.html's
 * dropdown structure, but driven by a real, editable WP nav menu (Appearance -> Menus)
 * instead of hardcoded links.
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
			$output .= '<a href="' . esc_url( $item->url ) . '" class="block rounded-md px-3 py-2.5 text-sm font-medium hover:bg-hero-bg">'
				. esc_html( $item->title ) . '</a>';
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</div>';
		}
	}
}
