<?php
/**
 * Homepage hero — full-bleed slideshow (positioning message + RetailNext
 * partnership). See initHeroSlideshow(), assets/js/homepage.js.
 * Full history: docs/decisions/003-hero-slideshow.md. Split out of
 * front-page.php 2026-08-06 (template-parts split).
 *
 * 2026-08-26: the "Live Detection" bounding-box visualization (5 drifting
 * boxes with fake PERSON/confidence labels, drawn into #heroBg by
 * initHeroDetectionBoxes()) removed — explicit instruction. The
 * background-video static-media guard that lived inside that same
 * function (unrelated to the boxes) survives under its own name,
 * initHeroStaticMediaGuard().
 *
 * 2026-08-21 — rebuilt to an exact supplied layout spec: full-bleed
 * container (~85-90vh, no outer padding on the media itself), a bottom
 * gradient band (~35-38% of the hero height, NOT the full height like the
 * previous version) with two-column content bottom-aligned inside it
 * (headline ~45% width left, description+CTA ~38% width right, CTA
 * positioned to start near viewport-center), and a right-edge dot-nav
 * whose active slide renders as a numbered circular badge in place of a
 * plain dot. Two real, deliberate content changes from the previous
 * version, both because the spec is a single-CTA, no-trust-row design —
 * flagged here, not silently dropped:
 *   - hero_cta_secondary_label/url (still real ACF fields, still used
 *     elsewhere if ever needed again) is no longer rendered in the hero —
 *     the spec shows exactly one "Learn more"-style pill.
 *   - hero_trust_metrics is no longer rendered in the hero — the spec's
 *     content layout is headline + description + CTA only, nothing below.
 * The photo/video background swap and the itoi_hero_headline_size_class()
 * content-aware sizing carry over unchanged in mechanism, just retuned
 * (see src/tailwind.css) to the spec's smaller ~40-44px desktop /
 * ~28-32px mobile headline range.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Server-rendered fallback = slide 0's real content (never the RetailNext
// partnership slide, so plain text/media is always safe here) — matches
// what initHeroSlideshow() sets on load, no flash of different content
// while JS boots. Fetched here since slide 0's photo/video is also needed
// for the initial #heroBg render, not just the headline/subcopy.
$itoi_hero_slides_php = get_field( 'hero_slides', 'option' );
$itoi_hero_slide0     = is_array( $itoi_hero_slides_php ) && ! empty( $itoi_hero_slides_php ) ? $itoi_hero_slides_php[0] : array();
$itoi_hero_headline   = $itoi_hero_slide0['headline'] ?? 'Turn what happens across your sites into decisions you can act on.';

$itoi_hero_subcopy = $itoi_hero_slide0['subcopy'] ?? 'Connect cameras, sensors and operational systems in one intelligent platform for security, analytics and automation.';

// Per-slide background photo/video (optional) — Site Settings, added
// 2026-07-23. Each hero_slides row can carry its own photo/video, falling
// back independently to the plain dark gradient when that slide's own media
// is empty. initHeroSlideshow() (homepage.js) swaps #heroBgPhoto's src /
// #heroBgVideo's src and toggles which is visible on every slide change,
// using the same itoiHeroSlides data localized for the text swap — this PHP
// block only renders slide 0's version, as the pre-JS SSR state.
$itoi_slide0_photo_id  = $itoi_hero_slide0['photo'] ?? 0;
$itoi_slide0_photo_url = $itoi_slide0_photo_id ? wp_get_attachment_image_url( $itoi_slide0_photo_id, 'large' ) : '';
// Alt text fallback chain: the attachment's own media-library alt text
// first, then this slide's real headline, then a last-resort description —
// never an empty alt on what's this page's single largest, most visible
// photo.
$itoi_slide0_photo_alt = $itoi_slide0_photo_id ? ( get_post_meta( $itoi_slide0_photo_id, '_wp_attachment_image_alt', true ) ?: $itoi_hero_headline ?: 'ITOI Solutions' ) : '';
$itoi_slide0_video     = $itoi_hero_slide0['video'] ?? null;
$itoi_slide0_video_url = ! empty( $itoi_slide0_video['url'] ) ? $itoi_slide0_video['url'] : '';
$itoi_hero_has_media   = $itoi_slide0_photo_url || $itoi_slide0_video_url;

// Static, slide-0-only CTA (enterprise reskin) — does NOT rotate with the
// hero_slides dot-nav slideshow above; slides 2-5 keep their own unrelated
// copy untouched.
$itoi_hero_cta_primary_label = get_field( 'hero_cta_primary_label', 'option' ) ?: 'Book a site assessment';
$itoi_hero_cta_primary_url   = get_field( 'hero_cta_primary_url', 'option' ) ?: '/contact/';
?>
<section class="relative overflow-hidden h-[85vh] min-h-[520px] min-[640px]:h-[90vh]" id="megaHero">
	<div class="absolute inset-0 <?php echo $itoi_hero_has_media ? 'bg-[#0a1720]' : 'bg-[linear-gradient(160deg,#0a1720,#122b38_55%,#1b3a48)]'; ?>" id="heroBg">
		<video class="absolute inset-0 h-full w-full object-cover <?php echo $itoi_slide0_video_url ? '' : 'hidden'; ?>" id="heroBgVideo" autoplay muted loop playsinline <?php echo $itoi_slide0_photo_url ? 'poster="' . esc_url( $itoi_slide0_photo_url ) . '"' : ''; ?>>
			<?php if ( $itoi_slide0_video_url ) : ?><source src="<?php echo esc_url( $itoi_slide0_video_url ); ?>"><?php endif; ?>
		</video>
		<img class="absolute inset-0 h-full w-full object-cover <?php echo ( $itoi_slide0_photo_url && ! $itoi_slide0_video_url ) ? '' : 'hidden'; ?>" id="heroBgPhoto" src="<?php echo esc_url( $itoi_slide0_photo_url ); ?>" alt="<?php echo esc_attr( $itoi_slide0_photo_alt ); ?>">
	</div>
	<?php if ( ! $itoi_hero_has_media ) : ?>
		<div class="hero-dot-grid absolute inset-0" aria-hidden="true"></div>
	<?php endif; ?>

	<?php
	/**
	 * Bottom gradient band — spec's exact stops, a separate ~37%-tall
	 * absolutely-positioned panel (NOT a full-height scrim like the
	 * previous version) so the top/middle of the hero stays completely
	 * untinted and only this band darkens, bottom-up, for the content
	 * sitting inside it. rgba(0,0,0,...) per spec, literal black rather
	 * than this theme's usual navy-tinted scrim (rgba(8,20,28,...)) —
	 * kept as given since it's a small, deliberate, spec-matched value,
	 * not a "no new colors" violation (it's a shadow/overlay, not a UI
	 * accent color). Content lives inside this same div, bottom-aligned
	 * via flex + padding-bottom, per spec.
	 */
	?>
	<div class="absolute inset-x-0 bottom-0 z-[2] flex h-[37%] min-h-[220px] flex-col justify-end bg-[linear-gradient(to_top,rgba(0,0,0,0.78)_0%,rgba(0,0,0,0.45)_55%,rgba(0,0,0,0)_100%)] px-6 pb-12 min-[900px]:px-20 min-[900px]:pb-16 min-[1280px]:px-[100px]">
		<?php
		/**
		 * 2026-08-21 (same day, follow-up — "book assessment to go to the
		 * right and text to the very left"): dropped the mx-auto
		 * max-w-[1440px] wrapper this row used to have — on any viewport
		 * wider than 1440px it centered the whole row, adding equal empty
		 * margin on both sides on top of the px-20/[100px] padding, so the
		 * headline sat well short of the section's actual left edge. w-full
		 * against the parent's own padding puts the headline flush to it
		 * instead. Right column also gets ml-auto now (pushes it — and the
		 * CTA inside it — to the row's real right edge, not just wherever
		 * the 45%+gap math happened to land it).
		 */
		?>
		<div class="flex w-full flex-col gap-8 min-[900px]:flex-row min-[900px]:items-end min-[900px]:gap-10">
			<div class="min-[900px]:w-[45%] min-[900px]:flex-none">
				<h1 class="itoi-stagger-item <?php echo esc_attr( itoi_hero_headline_size_class( $itoi_hero_headline ) ); ?> line-clamp-3 leading-[1.15] text-white" id="heroHeadline" style="--stagger-distance:10px"><?php echo esc_html( $itoi_hero_headline ); ?></h1>
			</div>
			<div class="flex flex-col items-start gap-5 min-[900px]:w-[38%] min-[900px]:flex-none min-[900px]:ml-auto">
				<p class="itoi-stagger-item m-0 max-w-[420px] text-[16px] leading-[1.6] text-white/90" id="heroSub" style="--stagger-distance:10px"><?php echo esc_html( $itoi_hero_subcopy ); ?></p>
				<div class="itoi-stagger-item min-[900px]:self-end" id="heroCtaRow" style="--stagger-distance:10px">
					<a href="<?php echo esc_url( $itoi_hero_cta_primary_url ); ?>" class="hero-cta-solid inline-block whitespace-nowrap rounded-full px-[28px] py-[14px] text-[15px] font-semibold transition-[background-color,transform] duration-200 ease-out hover:-translate-y-0.5"><?php echo esc_html( $itoi_hero_cta_primary_label ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div class="dotnav-glass absolute bottom-4 left-4 z-[3] hidden gap-4 p-2 min-[640px]:bottom-auto min-[640px]:left-auto min-[640px]:right-6 min-[640px]:top-1/2 min-[640px]:flex min-[640px]:-translate-y-1/2 min-[640px]:flex-col" id="dotNav"></div>
</section>
