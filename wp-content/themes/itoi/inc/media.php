<?php
/**
 * Shared photo-or-video cover render, used everywhere a CPT lets an editor
 * choose either (industry hero, solution hero/tile, use-case card — see
 * PROJECT.md §4 field notes on each). Video wins when both are set; the
 * photo doubles as its poster frame and as the reduce-motion fallback.
 * Playback itself is NOT done here — every video this function returns
 * carries the shared .itoi-media-video class and no `autoplay`, lazily
 * played only once actually scrolled into view (and paused again once
 * scrolled out) by initLazyMediaVideos() in main.js, so callers don't each
 * need their own JS. `preload="none"` for the same reason: a page can carry
 * several of these (an industry long-form page easily has 5-6), and having
 * every one of them start downloading its full video on page load — one
 * measured over 18MB — was real, reported lag ("the website is laggy"),
 * confirmed via Lighthouse (2026-08-04, see NOTES.md): 34.6MB total page
 * weight, 4.4s of main-thread work, LCP 5.1s, performance score 0.62
 * (PROJECT.md §1 requires ≥90). Never autoplays at all for a reduce-motion
 * visitor (initLazyMediaVideos() skips observing entirely) — the poster
 * frame is the correct, final state for them, not a loading placeholder.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $photo_url  Already-sized image URL, or '' if none.
 * @param array|null $video_field Raw ACF file-field value (array with 'url'), or null/[]/'' if none.
 * @param string $alt        Alt text (video ignores this — it's decorative/muted, no captions).
 * @param string $classes    Space-separated class list applied to whichever tag renders.
 * @param string $img_attrs  Extra raw attributes (e.g. loading="lazy") applied only to the <img> branch.
 * @return string Markup for the <video>/<img>, or '' if neither photo nor video is set — callers keep
 *                their own placeholder markup for that empty-state branch.
 */
function itoi_media_cover( $photo_url, $video_field, $alt, $classes, $img_attrs = '' ) {
	$video_url = ! empty( $video_field['url'] ) ? $video_field['url'] : '';

	if ( $video_url ) {
		$poster = $photo_url ? ' poster="' . esc_url( $photo_url ) . '"' : '';
		return '<video class="' . esc_attr( $classes ) . ' itoi-media-video" muted loop playsinline preload="none"' . $poster . '><source src="' . esc_url( $video_url ) . '"></video>';
	}

	if ( $photo_url ) {
		return '<img src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $alt ) . '" class="' . esc_attr( $classes ) . '" ' . $img_attrs . '>';
	}

	return '';
}
