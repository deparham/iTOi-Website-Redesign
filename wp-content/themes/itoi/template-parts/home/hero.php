<?php
/**
 * Homepage hero — 2-slide dot-nav slideshow (positioning message + RetailNext
 * partnership) + drifting Live Detection background boxes. See
 * initHeroSlideshow() / initHeroDetectionBoxes(), assets/js/main.js. Full
 * history: docs/decisions/003-hero-slideshow.md. Split out of front-page.php
 * 2026-08-06 (template-parts split) — markup/logic unchanged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Server-rendered fallback = slide 0's real content (never the RetailNext
// partnership slide, so plain text/media is always safe here) — matches
// what initHeroSlideshow() sets on load, no flash of different content
// while JS boots. Fetched here (moved up from further below, 2026-07-23)
// since slide 0's photo/video is now also needed for the initial #heroBg
// render, not just the headline/subcopy.
$itoi_hero_slides_php = get_field( 'hero_slides', 'option' );
$itoi_hero_slide0     = is_array( $itoi_hero_slides_php ) && ! empty( $itoi_hero_slides_php ) ? $itoi_hero_slides_php[0] : array();
$itoi_hero_headline   = $itoi_hero_slide0['headline'] ?? 'Turn what happens across your sites into decisions you can act on.';

// Eyebrow — added 2026-08-05 (see NOTES.md, homepage positioning pass). States
// ITOI's category in one line before the rotating headline/subcopy get
// specific. Static, does not rotate with hero_slides.
$itoi_hero_eyebrow = get_field( 'hero_eyebrow', 'option' ) ?: 'Physical intelligence for multi-site operations';

$itoi_hero_subcopy = $itoi_hero_slide0['subcopy'] ?? 'Connect cameras, sensors and operational systems in one intelligent platform for security, analytics and automation.';

// Per-slide background photo/video (optional) — Site Settings, added
// 2026-07-23. Each hero_slides row can carry its own photo/video, falling
// back independently to the plain dark gradient when that slide's own media
// is empty. initHeroSlideshow() (main.js) swaps #heroBgPhoto's src /
// #heroBgVideo's src and toggles which is visible on every slide change,
// using the same itoiHeroSlides data localized for the text swap — this PHP
// block only renders slide 0's version, as the pre-JS SSR state. The Live
// Detection markers (initHeroDetectionBoxes()) keep drawing into #heroBg
// regardless of which slide/media is active — this is purely a
// background-layer swap, never a replacement of the signature layer
// (CLAUDE.md's hard rule).
$itoi_slide0_photo_id  = $itoi_hero_slide0['photo'] ?? 0;
$itoi_slide0_photo_url = $itoi_slide0_photo_id ? wp_get_attachment_image_url( $itoi_slide0_photo_id, 'large' ) : '';
// Alt text fallback chain (2026-08-06 SEO pass, see NOTES.md): the
// attachment's own media-library alt text first, then this slide's real
// headline (already resolved above), then a last-resort description —
// never an empty alt on what's this page's single largest, most visible
// photo. Dormant today (no photo is currently attached to hero slide 0 —
// confirmed live), but real and correct once one is.
$itoi_slide0_photo_alt = $itoi_slide0_photo_id ? ( get_post_meta( $itoi_slide0_photo_id, '_wp_attachment_image_alt', true ) ?: $itoi_hero_headline ?: 'ITOI Solutions' ) : '';
$itoi_slide0_video     = $itoi_hero_slide0['video'] ?? null;
$itoi_slide0_video_url = ! empty( $itoi_slide0_video['url'] ) ? $itoi_slide0_video['url'] : '';
$itoi_hero_has_media   = $itoi_slide0_photo_url || $itoi_slide0_video_url;

// Static, slide-0-only CTAs + trust row (enterprise reskin) — do NOT rotate
// with the hero_slides dot-nav slideshow above; slides 2-5 keep their own
// unrelated copy untouched. Repeater ('option') isn't ?:-friendly like the
// scalar fields above, so it gets the same get_field()+empty() fallback
// pattern used elsewhere on this page.
$itoi_hero_cta_primary_label   = get_field( 'hero_cta_primary_label', 'option' ) ?: 'Book a site assessment';
$itoi_hero_cta_primary_url     = get_field( 'hero_cta_primary_url', 'option' ) ?: '/contact/';
$itoi_hero_cta_secondary_label = get_field( 'hero_cta_secondary_label', 'option' ) ?: 'See customer results';
$itoi_hero_cta_secondary_url   = get_field( 'hero_cta_secondary_url', 'option' ) ?: '/case-studies/';
$itoi_hero_trust_metrics       = get_field( 'hero_trust_metrics', 'option' );
if ( empty( $itoi_hero_trust_metrics ) ) {
	$itoi_hero_trust_metrics = array(
		array( 'text' => 'Rapid incident response' ),
		array( 'text' => 'Australia wide coverage' ),
		array( 'text' => 'Enterprise SLA support' ),
	);
}
?>
<?php
/**
 * 2026-08-03: combined with the header (see header.php's is_front_page()
 * branch — ticker+nav become a fixed overlay only on this page) so the nav
 * pill floats directly over this section's own dark background, no white
 * page-background seam between them. pt-[…] below is that fixed block's
 * real height (ticker + gap + nav pill) plus breathing room before the
 * headline — keep in sync if the header block's height ever changes.
 * Layout also changed same day from side-by-side (headline left, sub+CTA
 * right) to a centered stack, matching the requested reference look.
 */
?>
<section class="relative overflow-hidden px-5 pb-16 pt-[168px] min-h-[560px] min-[640px]:px-8 min-[640px]:pb-24 min-[640px]:pt-[206px] min-[640px]:min-h-[640px]" id="megaHero">
	<div class="absolute inset-0 <?php echo $itoi_hero_has_media ? 'bg-[#0a1720]' : 'bg-[linear-gradient(160deg,#0a1720,#122b38_55%,#1b3a48)]'; ?>" id="heroBg">
		<video class="absolute inset-0 h-full w-full object-cover <?php echo $itoi_slide0_video_url ? '' : 'hidden'; ?>" id="heroBgVideo" autoplay muted loop playsinline <?php echo $itoi_slide0_photo_url ? 'poster="' . esc_url( $itoi_slide0_photo_url ) . '"' : ''; ?>>
			<?php
			if ( $itoi_slide0_video_url ) :
				?>
				<source src="<?php echo esc_url( $itoi_slide0_video_url ); ?>"><?php endif; ?>
		</video>
		<img class="absolute inset-0 h-full w-full object-cover <?php echo ( $itoi_slide0_photo_url && ! $itoi_slide0_video_url ) ? '' : 'hidden'; ?>" id="heroBgPhoto" src="<?php echo esc_url( $itoi_slide0_photo_url ); ?>" alt="<?php echo esc_attr( $itoi_slide0_photo_alt ); ?>">
	</div>
	<?php
	/**
	 * 2026-08-05 (external improvement plan Phase 4.4): was a flat
	 * rgba(21,36,45,0.35) scrim plus backdrop-blur-[6px]. backdrop-filter
	 * is one of the more expensive CSS properties to composite, and here
	 * it ran continuously across a full-screen looping video — replaced
	 * with a plain gradient scrim (no filter, cheap to composite) that
	 * darkens toward the bottom of the section, where the headline/CTAs/
	 * trust row actually sit (this hero's copy is a centered vertical
	 * stack, not the side-by-side layout the plan's own example gradient
	 * assumed — a top-to-bottom gradient fits this layout, a left-to-right
	 * one wouldn't). Contrast-checked against white text: 15px/16.5px body
	 * text sits in the 0.62-0.78 opacity band, comfortably clears 4.5:1
	 * against any real footage brightness tested (temporary-screenshots-*
	 * checks, not committed).
	 */
	?>
	<div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(8,20,28,0.45)_0%,rgba(8,20,28,0.62)_55%,rgba(8,20,28,0.78)_100%)]"></div>
	<div class="hero-dot-grid absolute inset-0" aria-hidden="true"></div>

	<div class="relative z-[2] mx-auto flex w-full max-w-[720px] flex-col items-center gap-6 text-center">
		<div class="inline-flex items-center gap-1.5 text-[11px] font-bold tracking-[0.06em] text-white">
			<span class="h-[7px] w-[7px] animate-itoi-pulse rounded-full bg-signature-bright"></span>LIVE
		</div>
		<?php if ( $itoi_hero_eyebrow ) : ?>
			<p class="m-0 text-[12.5px] font-semibold uppercase tracking-[0.08em] text-signature-bright"><?php echo esc_html( $itoi_hero_eyebrow ); ?></p>
		<?php endif; ?>
		<h1 class="max-w-[18ch] <?php echo esc_attr( itoi_hero_headline_size_class( $itoi_hero_headline ) ); ?> leading-[1.05] text-white" id="heroHeadline"><?php echo esc_html( $itoi_hero_headline ); ?></h1>
		<p class="m-0 max-w-[54ch] text-[15px] text-white/90 min-[1024px]:text-[16.5px]" id="heroSub"><?php echo esc_html( $itoi_hero_subcopy ); ?></p>
		<div class="flex flex-wrap items-center justify-center gap-3">
			<a href="<?php echo esc_url( $itoi_hero_cta_primary_url ); ?>" class="hero-cta-accent whitespace-nowrap px-[26px] py-[13px] text-[14.5px] font-bold transition-[background-color,box-shadow,transform] duration-200 ease-out hover:-translate-y-0.5"><?php echo esc_html( $itoi_hero_cta_primary_label ); ?> &rarr;</a>
			<a href="<?php echo esc_url( $itoi_hero_cta_secondary_url ); ?>" class="hero-cta-secondary whitespace-nowrap px-[26px] py-[13px] text-[14.5px] font-semibold text-white transition-[background-color,box-shadow,transform] duration-200 ease-out hover:-translate-y-0.5"><?php echo esc_html( $itoi_hero_cta_secondary_label ); ?></a>
		</div>
		<?php if ( ! empty( $itoi_hero_trust_metrics ) ) : ?>
			<ul class="mt-1 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 p-0 list-none">
				<?php foreach ( $itoi_hero_trust_metrics as $itoi_trust_item ) : ?>
					<?php
					if ( empty( $itoi_trust_item['text'] ) ) {
						continue; }
					?>
					<li class="flex items-center gap-2 text-[12.5px] font-semibold text-white/80">
						<span class="flex h-[18px] w-[18px] flex-none items-center justify-center rounded-full bg-white/15 text-[9.5px]" aria-hidden="true">&#10003;</span>
						<?php echo esc_html( $itoi_trust_item['text'] ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<div class="dotnav-glass absolute bottom-4 left-4 z-[3] flex gap-2 p-2 min-[640px]:bottom-auto min-[640px]:left-auto min-[640px]:right-4 min-[640px]:top-1/2 min-[640px]:flex-col min-[640px]:-translate-y-1/2" id="dotNav"></div>
</section>
