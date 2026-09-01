<?php
/**
 * Video Showcase — full-bleed, edge-to-edge section right after Trust &
 * Stats (front-page.php). One background photo/video doubles as both the
 * section's own moving background (muted/looped, via the shared
 * itoi_media_cover() — same helper/behavior used everywhere else a
 * photo-or-video field exists on this site) AND the source the "Play
 * video" button opens in a lightbox (unmuted, with controls) — one asset
 * uploaded in Site Settings does both jobs, not two separate fields to
 * fill in. No video uploaded yet → the poster photo alone is the static
 * background and the "Play video" button/lightbox simply don't render
 * (nothing to play) — same honest-placeholder discipline as every other
 * optional media field on this site, not a broken button. Neither photo
 * nor video set → the section still renders (heading + dot-grid over a
 * plain honest placeholder), same as every other homepage section; it is
 * NOT conditional on having media, only individual pieces within it are.
 *
 * Dot-grid overlay: same radial-gradient texture mechanism as the hero's
 * .hero-dot-grid (src/tailwind.css), just white/larger/wider-spaced (per
 * spec) and confined to the bottom third. The one "active" node is a real
 * element with a sonar-ping box-shadow pulse (.video-showcase-dot--active)
 * — decorative, represents a live sensor detection point; reduced-motion
 * gets a static glow instead of the animation (CSS handles both, see that
 * class's own comment).
 *
 * Lightbox: a new, minimal video-only overlay (.video-lightbox-*,
 * src/tailwind.css) — not a reuse of the existing platform-demo modal
 * (template-parts/platform-demo-modal.php), which is a 6-tab data
 * dashboard, not a video player, and not a fit here. Open/close JS
 * mirrors the exact same overlay.classList.toggle('open') +
 * body-scroll-lock shape that modal already uses, just for a <video>
 * instead — see initVideoShowcase() (assets/js/homepage.js).
 *
 * 2026-09-01: the scroll-driven "door reveal" section
 * (template-parts/home/hero-door-reveal.php) took this section's homepage
 * slot. Nothing here was removed — markup, ACF fields and the uploaded
 * video are all intact — it's just gated behind a Site Settings toggle
 * (show_video_showcase, default OFF), exactly the same "kept as a real
 * toggle rather than deleted outright, so it can be switched back on with
 * no code change" pattern template-parts/home/traffic-demo.php already
 * uses. Switch it on in Site Settings ▸ Video showcase (homepage).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Default OFF: never-saved returns NULL, which is falsy, so a site that has
// never opened this toggle gets the off state — no NULL-vs-default handling
// needed here (unlike a default-ON flag such as product_enabled).
if ( ! get_field( 'show_video_showcase', 'option' ) ) {
	return;
}

$itoi_vs_heading    = get_field( 'video_showcase_heading', 'option' ) ?: 'ITOI in action';
$itoi_vs_poster_id  = get_field( 'video_showcase_poster', 'option' );
$itoi_vs_poster_url = $itoi_vs_poster_id ? wp_get_attachment_image_url( $itoi_vs_poster_id, 'full' ) : '';
$itoi_vs_video      = get_field( 'video_showcase_video', 'option' );
$itoi_vs_cta_label  = get_field( 'video_showcase_cta_label', 'option' ) ?: 'Play video';

$itoi_vs_bg_media = itoi_media_cover(
	$itoi_vs_poster_url,
	$itoi_vs_video,
	$itoi_vs_heading,
	'absolute inset-0 h-full w-full object-cover',
	'loading="lazy"'
);
?>
<?php // Always renders, same as every other homepage section — no
// upstream media/content dependency to gate on (the heading has a real
// fallback default, and a missing photo/video gets the same honest
// placeholder every other optional media field on this site falls back
// to, not a hidden section). ?>
<section class="video-showcase relative isolate flex aspect-[2/1] max-h-[840px] min-h-[460px] w-full items-end overflow-hidden" id="videoShowcase" aria-label="<?php echo esc_attr( $itoi_vs_heading ); ?>">
	<?php if ( $itoi_vs_bg_media ) : ?>
		<?php echo $itoi_vs_bg_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
	<?php else : ?>
		<div class="absolute inset-0 flex items-center justify-center bg-[linear-gradient(135deg,#1b2a35,#0e1116)] p-4 text-center text-[11px] uppercase tracking-[0.06em] text-white/40">Photo — <?php echo esc_html( $itoi_vs_heading ); ?></div>
	<?php endif; ?>

	<?php // "Slight dark overlay... ~10-15% black... image should stay mostly visible/bright" — a flat scrim, not the strong top-fade gradient other media tiles on this site use, since those need heavier legibility for corner-anchored labels; this heading is centered lower-mid with room around it. ?>
	<div class="absolute inset-0 bg-black/[0.12]" aria-hidden="true"></div>

	<div class="video-showcase-dot-grid pointer-events-none absolute inset-x-0 bottom-0 z-[1] h-1/3" aria-hidden="true">
		<?php /* 2026-08-31 mobile fix: this was a single hardcoded
		left:132px;bottom:66px — tuned to land on a real grid intersection
		of the 66px desktop grid, but the mobile override below
		(.video-showcase-dot-grid's own 40px grid, src/tailwind.css) never
		got a matching position, so on a phone the dot sat off-grid and
		directly under the "Play video" button (confirmed via a real
		375px capture). Bottom-left corner, on the 40px grid, clear of the
		centered button either way; unchanged 132/66 position from
		640px up. */ ?>
		<span class="video-showcase-dot--active left-10 bottom-10 min-[640px]:bottom-[66px] min-[640px]:left-[132px]"></span>
	</div>

	<div class="relative z-[2] mx-auto flex w-full max-w-[720px] flex-col items-center px-5 pb-16 pt-16 text-center min-[640px]:pb-20">
		<h2 class="m-0 max-w-[20ch] text-[clamp(24px,4vw,36px)] text-white"><?php echo esc_html( $itoi_vs_heading ); ?></h2>
		<?php if ( ! empty( $itoi_vs_video['url'] ) ) : ?>
			<button type="button" class="mt-5 inline-flex items-center gap-2.5 rounded-full border border-white/70 px-7 py-[14px] text-[14px] font-semibold text-white transition-colors hover:bg-white/15" id="videoShowcasePlayBtn">
				<?php echo esc_html( $itoi_vs_cta_label ); ?>
				<svg width="10" height="12" viewBox="0 0 10 12" fill="none" aria-hidden="true"><path d="M0 0.75L9.5 6L0 11.25V0.75Z" fill="currentColor"/></svg>
			</button>
		<?php endif; ?>
	</div>
</section>

<?php if ( ! empty( $itoi_vs_video['url'] ) ) : ?>
	<div class="video-lightbox-overlay" id="videoShowcaseOverlay" aria-hidden="true">
		<div class="video-lightbox-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $itoi_vs_heading ); ?>">
			<button type="button" class="video-lightbox-close" id="videoShowcaseClose" aria-label="Close video">&times;</button>
			<video class="video-lightbox-video" id="videoShowcasePlayer" controls playsinline preload="none" <?php echo $itoi_vs_poster_url ? 'poster="' . esc_url( $itoi_vs_poster_url ) . '"' : ''; ?>>
				<source src="<?php echo esc_url( $itoi_vs_video['url'] ); ?>" type="<?php echo esc_attr( $itoi_vs_video['mime_type'] ?? 'video/mp4' ); ?>">
			</video>
		</div>
	</div>
<?php endif; ?>
