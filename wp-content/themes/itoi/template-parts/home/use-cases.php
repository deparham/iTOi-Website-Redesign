<?php
/**
 * Real Use Cases — arrow-nav carousel of flip-card tiles, added 2026-08-21
 * per an exact supplied spec ("3D flip-card carousel for the product/
 * use-case section").
 *
 * 2026-08-24 — data source switched to `product` posts directly
 * (itoi_get_product_showcase_cards(), inc/product-carousel.php), replacing
 * the earlier `use_case` CPT source — explicit instruction, so editors
 * manage these cards from each Product's own edit screen ("Real Use Cases
 * Card" tab) instead of a separate CPT. See that file's docblock for the
 * full rationale and the real tradeoff it accepted (only products with
 * that tab filled in show here — 2 today, not the 9 the use_case-driven
 * version had). inc/use-cases.php / itoi_get_industry_use_cases() is
 * untouched and still powers the /use-cases/ hub, the nav dropdown, and
 * each industry's long-form Use Cases tab — unrelated to this section now.
 *
 * Every card now inherently has a real product behind it (that's the
 * whole point of the switch), so the old "solution fallback" branch this
 * template used to need is gone — one straightforward render path.
 *
 * Flip mechanic: reuses the theme's existing shared .flip-card component
 * (src/tailwind.css, first built for the industries carousel — see
 * docs/decisions/002-industries-carousel-flip-cards.md — and initFlipCards()
 * in core.js, sitewide) rather than a second parallel implementation. That
 * shared component already provides everything the spec asks for:
 * perspective:1200px, preserve-3d, backface-visibility:hidden both faces,
 * :hover + :focus-within on (hover:hover) devices, .is-flipped tap-toggle
 * on touch (initFlipCards() detects (hover:none) and wires the tap
 * itself — no new JS needed here for the flip), and reduced-motion via the
 * sitewide `*,*::before,*::after{transition:none}` rule (hover/tap still
 * swaps to the back face, just instantly instead of animated). The spec's
 * own 0.7s cubic-bezier easing is a scoped override,
 * .usecase-flip-card .flip-card-inner (src/tailwind.css) — the shared
 * component's default 0.6s stays unchanged for its other 2 existing
 * usages (About page, industries carousel).
 *
 * Card sizing: fixed 656×396 (border-radius 20px), 24px gap (`gap-6`),
 * deliberately NOT shrunk to fit any particular viewport — the row is
 * meant to overflow so a partial 4th card peeks at the right edge as a
 * scroll affordance. `snap-x snap-mandatory` on the track + `snap-start`
 * on each card (Tailwind's built-in scroll-snap utilities) makes both the
 * arrow-nav clicks (initUseCasesCarousel(), assets/js/homepage.js) and
 * manual swipe/scroll land cleanly on card boundaries — no bespoke
 * scroll-snap CSS needed. DOM ids (ucCarousel/ucProgressFill/ucPrev/
 * ucNext) are unchanged from before the data-source switch, so none of
 * that JS needed touching either.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_uc_heading  = get_field( 'use_cases_teaser_heading', 'option' ) ?: 'Real use cases, built for your industry.';
$itoi_uc_featured = itoi_get_product_showcase_cards();
?>
<?php if ( ! empty( $itoi_uc_featured ) ) : ?>
	<section class="border-b border-line bg-white px-5 py-section-lg min-[640px]:px-8" id="useCasesCarousel">
		<div class="mx-auto max-w-[1280px]">
			<div class="relative mb-10 <?php echo esc_attr( itoi_reveal_class() ); ?> min-[640px]:mb-12">
				<h2 class="m-0 max-w-[26ch] text-[clamp(26px,3vw,38px)]"><?php echo esc_html( $itoi_uc_heading ); ?></h2>
			</div>
		</div>

		<?php /* Track is intentionally NOT wrapped in the max-w-[1280px] container
		above — fixed 656px cards need room to overflow past a typical reading
		column so the "peeking 4th card" scroll affordance actually shows on
		real viewports. Only the heading stays width-capped. */ ?>
		<div class="no-scrollbar flex snap-x snap-mandatory gap-6 overflow-x-auto pb-2" id="ucCarousel">
			<?php foreach ( $itoi_uc_featured as $itoi_uc ) :
				$itoi_uc_photo_url = $itoi_uc['image_id'] ? wp_get_attachment_image_url( $itoi_uc['image_id'], 'large' ) : '';
				$itoi_uc_media     = itoi_media_cover(
					$itoi_uc_photo_url,
					$itoi_uc['video'],
					$itoi_uc['label'],
					'absolute inset-0 h-full w-full object-cover',
					'loading="lazy"'
				);

				$itoi_uc_back_photo_url = $itoi_uc['product_photo_id'] ? wp_get_attachment_image_url( $itoi_uc['product_photo_id'], 'medium' ) : '';
				$itoi_uc_back_media     = itoi_media_cover(
					$itoi_uc_back_photo_url,
					$itoi_uc['product_video'],
					$itoi_uc['product_name'],
					'h-full w-full object-cover',
					'loading="lazy"'
				);
				?>
				<?php /* w-[min(82vw,656px)] + aspect-ratio (no fixed height) —
				2026-08-31 mobile fix: this card was a flat 656×396 at every
				viewport. On a 375px phone the visible track is ~335px, so a
				656px card showed barely half of itself with no way to tell a
				"peek of the next card" from "the card just got cut off" —
				confirmed via a real 375px capture before fixing, not a
				guess. min() keeps it fluid up to the exact original 656px on
				anything wide enough, so desktop is byte-for-byte unchanged. */ ?>
				<div class="flip-card usecase-flip-card aspect-[656/396] w-[min(82vw,656px)] shrink-0 snap-start" data-href="<?php echo esc_url( $itoi_uc['product_url'] ); ?>">
					<div class="flip-card-inner">
						<div class="flip-card-front relative overflow-hidden rounded-[20px] bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
							<?php if ( $itoi_uc_media ) : ?>
								<?php echo $itoi_uc_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo — <?php echo esc_html( $itoi_uc['label'] ); ?></div>
							<?php endif; ?>
							<div class="absolute inset-0 bg-[linear-gradient(to_top,rgba(0,0,0,0.7),rgba(0,0,0,0)_60%)]" aria-hidden="true"></div>
							<div class="absolute bottom-5 left-5 z-[2] max-w-[70%] text-[19px] font-extrabold leading-[1.2] text-white"><?php echo esc_html( $itoi_uc['label'] ); ?></div>
							<span class="absolute bottom-4 right-4 z-[2] flex h-9 w-9 items-center justify-center rounded-full bg-white text-ink" aria-hidden="true">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
									<path d="M2 6.5A5.5 5.5 0 0 1 12.9 4.5M14 2v3h-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M14 9.5A5.5 5.5 0 0 1 3.1 11.5M2 14v-3h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</div>
						<div class="flip-card-back relative flex overflow-visible rounded-[20px] bg-[#FAFAFA] p-7">
							<div class="relative z-[1] flex h-full w-[55%] flex-none flex-col items-start justify-between">
								<div>
									<div class="mb-2 text-[13px] uppercase tracking-[0.06em] text-[#8f99a6]"><?php echo esc_html( $itoi_uc['label'] ); ?></div>
									<h3 class="m-0 mb-2 text-[24px] font-bold leading-[1.15] text-[#111]"><?php echo esc_html( $itoi_uc['product_name'] ); ?></h3>
									<?php if ( $itoi_uc['product_description'] ) : ?>
										<p class="m-0 line-clamp-4 text-[14px] leading-[1.5] text-[#6B7280]"><?php echo esc_html( $itoi_uc['product_description'] ); ?></p>
									<?php endif; ?>
								</div>
								<a href="<?php echo esc_url( $itoi_uc['product_url'] ); ?>" class="inline-block whitespace-nowrap rounded-full bg-ink px-5 py-[10px] text-[14px] font-semibold text-white transition-colors hover:bg-black">Find out more</a>
							</div>
							<?php if ( $itoi_uc_back_media ) : ?>
								<?php // 2026-08-31: bleeds to the card's own right/top/bottom edges
								// and fills its full height (rounded-r-[20px] mirrors the card's
								// own corner radius) instead of floating as a small inset tile
								// with a shadow — reads as the photo/video filling the card, not
								// a thumbnail sitting on top of it. A left-edge fade into the
								// card's own background keeps the text column legible at the
								// seam instead of a hard vertical edge. ?>
								<div class="pointer-events-none absolute inset-y-0 right-0 z-0 w-[48%] overflow-hidden rounded-r-[20px]">
									<?php echo $itoi_uc_back_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
									<div class="absolute inset-y-0 left-0 w-14 bg-[linear-gradient(to_right,#FAFAFA,rgba(250,250,250,0))]" aria-hidden="true"></div>
								</div>
							<?php endif; ?>
							<span class="absolute bottom-4 right-4 z-[2] flex h-9 w-9 items-center justify-center rounded-full bg-ink text-white" aria-hidden="true">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
									<path d="M2 6.5A5.5 5.5 0 0 1 12.9 4.5M14 2v3h-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M14 9.5A5.5 5.5 0 0 1 3.1 11.5M2 14v-3h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $itoi_uc_featured ) > 1 ) : ?>
			<div class="mx-auto mt-6 flex max-w-[1280px] items-center gap-6">
				<div class="relative h-[3px] flex-1 rounded-full bg-line" id="ucProgressTrack">
					<div class="absolute inset-y-0 left-0 rounded-full bg-ink" id="ucProgressFill"></div>
				</div>
				<div class="flex flex-none gap-2">
					<button type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-hero-bg text-ink transition-colors hover:bg-line" id="ucPrev" aria-label="Previous use case">&larr;</button>
					<button type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-hero-bg text-ink transition-colors hover:bg-line" id="ucNext" aria-label="Next use case">&rarr;</button>
				</div>
			</div>
		<?php endif; ?>
	</section>
<?php endif; ?>
