<?php
/**
 * Solution/Use-Cases-hub "highlight panel" — 2026-07-28 (see NOTES.md).
 * Curated preview of real, already-published data (a page's own `specs`
 * repeater, or its `capability_cards` names when `specs` is empty/thin) —
 * never a replacement for the existing full-detail Specs section, never
 * invented data. The curated selection (which entries, in what order, and
 * whether each renders as a big-number stat or a plain name/label card) is
 * editorial and lives in single-solution.php itself; this file only holds
 * the lookup helper, since the same "find a real row by its exact stored
 * label/name" logic is needed for every page's selection.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Finds one row in a real ACF repeater array by an exact match on a given
 * key (e.g. a `specs` row's `label`, or a `capability_cards` row's `name`).
 * Returns null if no row matches — callers must skip silently rather than
 * render a blank/broken tile, since a mismatch here means the curated
 * selection has drifted from the live field data (e.g. a content editor
 * renamed a spec label), not that placeholder content should appear.
 *
 * @param array  $rows   The ACF repeater rows to search.
 * @param string $key    The row key to match against, e.g. 'label' or 'name'.
 * @param string $needle The exact value to match.
 * @return array|null The matching row, or null if none matched.
 */
function itoi_highlight_find_row( $rows, $key, $needle ) {
	if ( empty( $rows ) ) {
		return null;
	}
	foreach ( $rows as $row ) {
		if ( isset( $row[ $key ] ) && trim( (string) $row[ $key ] ) === $needle ) {
			return $row;
		}
	}
	return null;
}

/**
 * The "big number" text for a highlight-panel "stat" tile. Mechanically
 * strips a trailing " (...)" parenthetical detail (e.g. "60+ (day/night
 * cameras, 360° cameras...)" -> "60+", "99.87% (LFW)" -> "99.87%") — a
 * literal prefix split on a fixed character sequence, never a reword or
 * paraphrase of the value, so it can't misrepresent the fact and won't
 * silently go stale if the source value's shape changes later. Safe to
 * drop the parenthetical here specifically because the untouched Specs
 * section immediately below always shows the complete, unmodified value —
 * this tile is a curated preview, not the only place the full detail
 * lives. Values with no " (" are returned unchanged.
 *
 * @param string $value The raw spec/capability value.
 * @return string $value with any trailing " (...)" parenthetical stripped.
 */
function itoi_highlight_stat_display( $value ) {
	$paren_pos = strpos( $value, ' (' );
	return false !== $paren_pos ? substr( $value, 0, $paren_pos ) : $value;
}

/**
 * Font-size class for a highlight-panel "stat" tile's big number, scaled to
 * the DISPLAY text's own length (i.e. after itoi_highlight_stat_display()),
 * never a truncated/reworded version of the value itself beyond that one
 * mechanical paren-strip.
 *
 * @param string $value The display text (already run through itoi_highlight_stat_display()).
 * @return string A text-size utility class, scaled to $value's length.
 */
function itoi_highlight_stat_size( $value ) {
	$len = strlen( $value );
	if ( $len <= 8 ) {
		return 'text-[32px]';
	} elseif ( $len <= 16 ) {
		return 'text-[22px]';
	} elseif ( $len <= 28 ) {
		return 'text-[16px]';
	}
	return 'text-[13px]';
}
