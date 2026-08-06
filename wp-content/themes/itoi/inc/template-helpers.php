<?php
/**
 * Small, general-purpose template helpers shared across theme templates —
 * as distinct from inc/media.php (media-rendering) and the per-section icon
 * files (inc/home-icons.php etc.). First occupant moved out of front-page.php
 * 2026-08-06 (template-parts split); add future cross-template helpers here
 * rather than growing a page template's own file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content-aware headline sizing (see src/tailwind.css's "Hero headline
 * content-aware sizing" comment for the full rationale) — picks one of 4
 * font-size tiers by character count instead of one fixed clamp(), so a
 * long editor-entered headline shrinks instead of just wrapping onto many
 * lines and blowing out the hero's height. Thresholds mirrored exactly in
 * heroHeadlineSizeClass() (assets/js/main.js) for slides 2-5, which render
 * client-side on slide change — keep both in sync if these ever change.
 */
function itoi_hero_headline_size_class( $headline ) {
	$length = mb_strlen( (string) $headline );
	if ( $length <= 60 ) {
		return 'hero-headline-size-1';
	} elseif ( $length <= 85 ) {
		return 'hero-headline-size-2';
	} elseif ( $length <= 110 ) {
		return 'hero-headline-size-3';
	}
	return 'hero-headline-size-4';
}
