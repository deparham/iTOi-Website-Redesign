<?php
/**
 * Hand-authored outline icons for the industry long-form "Why ITOI" grid
 * (single-industry.php). No icon font/library added — six inline SVGs,
 * ~40px, stroke=currentColor so ITOI's brand-navy signature color comes
 * from the wrapping element's text color class. This grid sits on the dark
 * bg-teal-900 section, so it uses the bright variant, not the plain navy
 * token (see NOTES.md's 2026-07-22 amber-to-navy migration entry).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function itoi_longform_icon( $key ) {
	$common = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';

	$paths = array(
		'handshake'  => '<circle cx="8" cy="7.5" r="3"/><path d="M2.5 19.5c0-3.6 2.46-6 5.5-6s5.5 2.4 5.5 6"/><circle cx="16" cy="7.5" r="3"/><path d="M10.5 19.5c0-3.6 2.46-6 5.5-6s5.5 2.4 5.5 6"/>',
		'shield'     => '<path d="M12 2.5 20 5.5V11c0 5-3.5 9-8 10.5-4.5-1.5-8-5.5-8-10.5V5.5L12 2.5Z"/><path d="M9.5 10V8a2.5 2.5 0 0 1 5 0v2"/><rect x="9" y="10" width="6" height="5" rx="1"/>',
		'viewfinder' => '<path d="M4 8V5.5A1.5 1.5 0 0 1 5.5 4H8"/><path d="M16 4h2.5A1.5 1.5 0 0 1 20 5.5V8"/><path d="M4 16v2.5A1.5 1.5 0 0 0 5.5 20H8"/><path d="M16 20h2.5a1.5 1.5 0 0 0 1.5-1.5V16"/><circle cx="12" cy="12" r="2"/>',
		'headset'    => '<path d="M4 14a8 8 0 0 1 16 0"/><rect x="2" y="14" width="4" height="6" rx="2"/><rect x="18" y="14" width="4" height="6" rx="2"/><path d="M6 20v.5A2.5 2.5 0 0 0 8.5 23H11"/>',
		'plug'       => '<path d="M8 2v5M16 2v5"/><rect x="6" y="7" width="12" height="7" rx="2"/><path d="M12 14v2a5 5 0 0 1-5 5H5.5"/>',
		'layers'     => '<path d="M12 3 21 8 12 13 3 8 12 3Z"/><path d="M3 12 12 17 21 12"/><path d="M3 16 12 21 21 16"/>',
	);

	if ( ! isset( $paths[ $key ] ) ) {
		return;
	}

	printf( '<svg class="h-10 w-10 text-signature-bright" viewBox="0 0 24 24" %s>%s</svg>', $common, $paths[ $key ] );
}
