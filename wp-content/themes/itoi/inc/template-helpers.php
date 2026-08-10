<?php
/**
 * Small, general-purpose template helpers shared across theme templates —
 * as distinct from inc/media.php (media-rendering) and the per-section icon
 * files (inc/home-icons.php etc.). First occupant moved out of front-page.php
 * 2026-08-06 (template-parts split); add future cross-template helpers here
 * rather than growing a page template's own file.
 *
 * @package ITOI
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
 *
 * @param string $headline The hero headline text to measure.
 * @return string One of 'hero-headline-size-1' through 'hero-headline-size-4'.
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

/**
 * `$value ?: $fallback` as a function call instead of the short ternary
 * operator (phpcs's Universal.Operators.StrictComparisons ruleset disallows
 * `?:` — it's genuinely easy to misread/misuse — but this codebase relies on
 * that exact "falsy ACF field -> fallback" pattern in well over 100 places,
 * mostly `get_field(...) ?: 'default'`). A plain `$x ? $x : $y` rewrite
 * would double-evaluate the left side, which matters for `get_field()`/
 * `get_the_title()`/`get_post_meta()` calls (wasteful, and one instance
 * away from a real bug if a future caller ever passes something with a side
 * effect). This function's own arguments are each evaluated exactly once by
 * PHP regardless, so `itoi_or( EXPR, FALLBACK )` reproduces `EXPR ?: FALLBACK`
 * exactly — single evaluation of both sides, same falsy check — and works
 * as a drop-in replacement inside any expression, including nested/chained
 * fallbacks and inline template output.
 *
 * @param mixed $value    The primary value.
 * @param mixed $fallback Used only when $value is falsy.
 * @return mixed $value if truthy, otherwise $fallback.
 */
function itoi_or( $value, $fallback ) {
	return $value ? $value : $fallback;
}
