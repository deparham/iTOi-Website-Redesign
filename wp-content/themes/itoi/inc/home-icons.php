<?php
/**
 * Inline SVG icon helpers used only by the homepage sections
 * (template-parts/home/how-it-works.php, template-parts/home/core-outcomes.php).
 * Moved out of front-page.php 2026-08-06 (template-parts split) — same
 * functions, same output, just relocated alongside this theme's existing
 * per-concern icon files (inc/longform-icons.php, inc/solution-spec-icons.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "How the platform works" (Observe / Understand / Act) pillar icons.
 * 24x24, stroke="currentColor", stroke-width 1.6, --ink monochrome — ordinary
 * content icons, not the Live Detection signature layer.
 */
function itoi_pillar_icon( $key, $classes = 'h-6 w-6' ) {
	$common = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';
	$paths  = array(
		'observe'    => '<path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
		'understand' => '<path d="M4 19h16"/><rect x="6" y="12" width="3" height="7" rx="0.5"/><rect x="10.5" y="8" width="3" height="11" rx="0.5"/><rect x="15" y="4" width="3" height="15" rx="0.5"/>',
		'act'        => '<path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/>',
	);
	if ( ! isset( $paths[ $key ] ) ) {
		return;
	}
	printf( '<svg class="%s" viewBox="0 0 24 24" %s>%s</svg>', esc_attr( $classes ), $common, $paths[ $key ] ); // phpcs:ignore -- $common/$paths are hardcoded above, not user input
}

/** "Core outcomes" card icons. Same convention as itoi_pillar_icon() above. */
function itoi_outcome_icon( $key, $classes = 'h-6 w-6' ) {
	$common = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';
	$paths  = array(
		'conversion'  => '<path d="M3 17 9 11l4 4 8-8"/><path d="M15 7h6v6"/>',
		'blind-spots' => '<path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><path d="m4 4 16 16"/>',
		'security'    => '<path d="M12 3 4 6v6c0 5 3.5 7.7 8 9 4.5-1.3 8-4 8-9V6l-8-3Z"/>',
		'staffing'    => '<circle cx="9" cy="8" r="3"/><path d="M3 19c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16.5 6.5A3 3 0 1 1 17 12.4"/><path d="M18.5 13.6c2 .6 3.5 2.4 3.5 4.4v1"/>',
		'compare'     => '<rect x="3" y="10" width="4" height="10" rx="1"/><rect x="10" y="5" width="4" height="15" rx="1"/><rect x="17" y="13" width="4" height="7" rx="1"/>',
		'automate'    => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.6V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.6 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.6 1Z"/>',
	);
	if ( ! isset( $paths[ $key ] ) ) {
		return;
	}
	printf( '<svg class="%s" viewBox="0 0 24 24" %s>%s</svg>', esc_attr( $classes ), $common, $paths[ $key ] ); // phpcs:ignore -- $common/$paths are hardcoded above, not user input
}
