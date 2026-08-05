<?php
/**
 * Scroll-triggered "detection reveal" markup helper — signature-navy
 * corner brackets + confidence tag on major content blocks, signature
 * layer (see CLAUDE.md's signature-color usage rule, updated alongside
 * this feature).
 *
 * The two helpers are INDEPENDENT — this is the load-bearing fact that
 * three prior sessions each missed in a different place, producing the
 * same "stray amber tag/bracket" bug four times over (see NOTES.md,
 * 2026-07-23 entry, for the full root-cause writeup):
 *
 * - `itoi_reveal_class()` alone gives a block the fade/rise-on-scroll
 *   motion (opacity + translateY via `.itoi-reveal`/`.itoi-reveal.is-visible`).
 *   Safe on ANY block, including bare headings/text — this has no
 *   visible chrome of its own.
 * - `itoi_reveal_markup()` additionally prints the four corner-bracket
 *   spans + the confidence-tag span. These are absolutely positioned to
 *   trace the edge of a real visible box — a photo tile with its own
 *   border-radius/overflow-hidden, or a bordered card. Call this ONLY on
 *   elements like that (tile-grid photos, metric cards). Calling it on a
 *   bare heading/paragraph wrapper — anything with no border, background,
 *   or photo of its own — is what produced every occurrence of the bug:
 *   the brackets render with nothing to visibly frame, reading as
 *   disconnected debris rather than a "detected" object. This is true
 *   even when the reveal is scoped to the correct element and not
 *   accidentally nested around something interactive (that was a real,
 *   separate bug the first two fixes correctly addressed — but rows of
 *   plain section headings across the theme were still calling this and
 *   were never audited, since each prior fix only chased its one
 *   reported instance).
 *
 * Usage: add `itoi_reveal_class()` to a block's own class attribute for
 * the fade-in alone. Only add `itoi_reveal_markup()` as the first thing
 * inside it if that same block is itself a framed/bordered/photo element
 * — never on a heading or paragraph wrapper.
 *
 * Purely decorative (aria-hidden) — the confidence percentage is an
 * illustrative UI ornament, not a claimed metric (PROJECT.md §3's
 * existing "Live Detection" hero visualization already uses the same
 * drifting-confidence-label device; this extends it, doesn't invent it).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function itoi_reveal_class() {
	return 'itoi-reveal';
}

function itoi_reveal_markup( $index = 0 ) {
	$values = array( 96, 97, 98, 99 );
	$value  = $values[ abs( (int) $index ) % count( $values ) ];
	?>
	<span class="itoi-reveal-bracket itoi-reveal-bracket--tl" aria-hidden="true"></span>
	<span class="itoi-reveal-bracket itoi-reveal-bracket--tr" aria-hidden="true"></span>
	<span class="itoi-reveal-bracket itoi-reveal-bracket--bl" aria-hidden="true"></span>
	<span class="itoi-reveal-bracket itoi-reveal-bracket--br" aria-hidden="true"></span>
	<span class="itoi-reveal-tag" aria-hidden="true"><?php echo (int) $value; ?>%</span>
	<?php
}
