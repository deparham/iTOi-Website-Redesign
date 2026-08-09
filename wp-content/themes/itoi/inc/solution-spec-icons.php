<?php
/**
 * Hand-authored outline icons for the solution-page intro "spec strip"
 * (single-solution.php, restructure pass 2026-07-27 — see NOTES.md). Same
 * convention as inc/longform-icons.php: no icon font/library, inline SVGs,
 * 24x24 viewBox, stroke=currentColor so color comes from the wrapping
 * element's text class. This strip sits on the light --hero-bg tiles, so it
 * uses the plain ink/teal text color, not the bright dark-bg variant.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders one spec-strip icon by key.
 *
 * @param string $key One of the spec keys defined in $paths below, e.g. 'camera', 'thermometer'.
 */
function itoi_solution_spec_icon( $key ) {
	$common = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';

	$paths = array(
		'camera'      => '<rect x="2.5" y="7" width="19" height="13" rx="2.5"/><circle cx="12" cy="13.5" r="3.5"/><path d="M8 7 9.3 4.5h5.4L16 7"/>',
		'camera-360'  => '<circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="9" ry="4"/><circle cx="12" cy="12" r="2.2"/>',
		'thermometer' => '<path d="M12 14.5V5a2 2 0 1 0-4 0v9.5a4 4 0 1 0 4 0Z"/><path d="M10 8h2"/>',
		'depth'       => '<rect x="3" y="3" width="12" height="12" rx="1.5"/><rect x="8" y="8" width="13" height="13" rx="1.5"/>',
		'radar'       => '<circle cx="12" cy="20" r="1.4"/><path d="M8 20a4 4 0 0 1 8 0"/><path d="M5 20a7 7 0 0 1 14 0"/><path d="M2.5 20a9.5 9.5 0 0 1 19 0"/>',
		'id-card'     => '<rect x="2.5" y="5" width="19" height="14" rx="2"/><circle cx="8" cy="12" r="2"/><path d="M5.5 16c0-1.7 1.2-3 2.5-3s2.5 1.3 2.5 3"/><path d="M14 10h5M14 13h5M14 16h3"/>',
		'rfid'        => '<rect x="4" y="8" width="16" height="10" rx="2"/><path d="M8 8V6.5A2.5 2.5 0 0 1 10.5 4h3A2.5 2.5 0 0 1 16 6.5V8"/><path d="M9 13h6"/>',
		'wifi'        => '<path d="M2.5 9a13.5 13.5 0 0 1 19 0"/><path d="M5.8 12.5a9 9 0 0 1 12.4 0"/><path d="M9 16a4.3 4.3 0 0 1 6 0"/><circle cx="12" cy="19.3" r="1.2" fill="currentColor" stroke="none"/>',
		'plug'        => '<path d="M8 2v5M16 2v5"/><rect x="6" y="7" width="12" height="7" rx="2"/><path d="M12 14v2a5 5 0 0 1-5 5H5.5"/>',
		'bottle'      => '<path d="M10 2h4v3.5l1.5 2V20a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 8.5 20V7.5L10 5.5Z"/><path d="M9 11h6"/>',
		'tap'         => '<path d="M5 12h9a4 4 0 0 1 4 4v1"/><path d="M5 8v10M18 15.5v3a1.5 1.5 0 0 1-3 0v-3"/>',
		'gauge'       => '<path d="M4 15.5a8 8 0 1 1 16 0"/><path d="M12 15.5 16 10"/><path d="M12 15.5h.01"/>',
		'document'    => '<path d="M6 2.5h9l4 4v14.5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1Z"/><path d="M14.5 2.5v4.5H19"/><path d="M8 13h8M8 16.5h8M8 9.5h4"/>',
		'network'     => '<circle cx="12" cy="4.5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 6.5v5M12 11.5 5 17M12 11.5l7 5.5"/>',
		'server'      => '<rect x="3" y="4" width="18" height="6.5" rx="1.5"/><rect x="3" y="13.5" width="18" height="6.5" rx="1.5"/><path d="M6.5 7.2h.01M6.5 16.7h.01"/>',
		'headset'     => '<path d="M4 14a8 8 0 0 1 16 0"/><rect x="2" y="14" width="4" height="6" rx="2"/><rect x="18" y="14" width="4" height="6" rx="2"/><path d="M6 20v.5A2.5 2.5 0 0 0 8.5 23H11"/>',
		'signal'      => '<path d="M4 20V13M9.5 20V9M15 20v-8M20 20V4"/>',
		'generic'     => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4.5"/><path d="M12 16h.01"/>',
	);

	if ( ! isset( $paths[ $key ] ) ) {
		$key = 'generic';
	}

	printf( '<svg class="mx-auto h-[18px] w-[18px] text-teal-700" viewBox="0 0 24 24" %s>%s</svg>', $common, $paths[ $key ] );
}
