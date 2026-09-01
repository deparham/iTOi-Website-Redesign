<?php
/**
 * Technology Partners — logo carousel. Content: inc/partners.php
 * (itoi_get_technology_partners()), editable via the Technology Partners
 * options page in wp-admin.
 *
 * 2026-08-26: moved from footer.php (rendered sitewide, on every page) to
 * front-page.php, right before get_footer() — this section is homepage
 * content, not a global footer element. Its carousel JS moved out of
 * assets/js/core.js to assets/js/homepage.js to match (see
 * initPartnersCarousel()'s comment there).
 *
 * 2026-08-24 correction — matched to the Real Use Cases carousel pattern
 * exactly (template-parts/home/use-cases.php): a horizontal single-row
 * track (`snap-x snap-mandatory` / `snap-start`, no wrapping grid) with a
 * progress bar + prev/next arrows beneath it. Card shell unchanged: F4F3F6
 * panel, small gray "Partner" eyebrow, logo box, dark circle double-arrow
 * icon bottom-right (decorative — these cards don't flip, no second face
 * to show). Since this section is sitewide, not homepage-only, its
 * carousel JS lives in core.js (initPartnersCarousel()) rather than
 * homepage.js (front-page-only bundle) — see that function's own comment.
 *
 * CTA: every card now gets the same outline-pill "Learn more" button
 * (fills solid black + label swaps to "Find out more" on hover), even
 * partners with no dedicated page yet — real per-card links (Xovis today)
 * use their own page; everyone else falls back to /products/ (the
 * closest real, relevant destination on this site) rather than a dead
 * '#' href, so the uniform button is never actually broken.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_partners_heading = get_field( 'technology_partners_heading', 'option' ) ?: 'Our Partners';
$itoi_partners          = itoi_get_technology_partners();
$itoi_partners_fallback_url = home_url( '/products/' );
?>
<?php if ( ! empty( $itoi_partners ) ) : ?>
	<section class="border-t border-line bg-white px-8 py-section-lg" id="technologyPartners">
		<div class="mx-auto max-w-[1280px]">
			<h2 class="mx-auto mb-10 max-w-[24ch] text-center text-[clamp(26px,3vw,38px)] <?php echo esc_attr( itoi_reveal_class() ); ?> min-[640px]:mb-12"><?php echo esc_html( $itoi_partners_heading ); ?></h2>

			<div class="no-scrollbar flex snap-x snap-mandatory gap-6 overflow-x-auto pb-2" id="partnersCarousel">
				<?php foreach ( $itoi_partners as $itoi_partner ) :
					$itoi_logo_url = $itoi_partner['logo_id'] ? wp_get_attachment_image_url( $itoi_partner['logo_id'], 'medium' ) : '';
					$itoi_link_url = $itoi_partner['link'] ?: $itoi_partners_fallback_url;
					?>
					<?php /* w-[min(78vw,340px)] — 2026-08-31 mobile fix: was a
					flat 340px card on a track that's only ~311px visible on a
					375px phone, cutting the card off before a full one could
					even be seen. min() stays fluid on narrow screens, exactly
					340px (unchanged) everywhere it already fit. */ ?>
					<div class="flex h-full w-[min(78vw,340px)] shrink-0 snap-start flex-col justify-between rounded-[18px] bg-[#F4F3F6] p-6 font-ui-sans <?php echo esc_attr( itoi_reveal_class() ); ?>">
						<div>
							<div class="mb-4 text-[13px] font-bold uppercase tracking-wide text-text-muted">Partner</div>

							<div class="mb-5 flex h-[120px] w-full items-center justify-center">
								<?php if ( $itoi_logo_url ) : ?>
									<img src="<?php echo esc_url( $itoi_logo_url ); ?>" alt="<?php echo esc_attr( $itoi_partner['name'] ); ?>" class="max-h-[120px] max-w-full object-contain" loading="lazy">
								<?php else : ?>
									<div class="flex h-full w-full items-center justify-center rounded-md bg-[#e4e3e8]">
										<span class="px-2 text-center text-[11px] font-bold uppercase tracking-wide text-[#9a99a3]"><?php echo esc_html( $itoi_partner['name'] ); ?> logo</span>
									</div>
								<?php endif; ?>
							</div>

							<h3 class="m-0 mb-1 text-[24px] font-bold leading-[1.15] text-ink"><?php echo esc_html( $itoi_partner['name'] ); ?></h3>
							<?php if ( $itoi_partner['description'] ) : ?>
								<p class="m-0 text-[14px] leading-[1.5] text-text-muted"><?php echo esc_html( $itoi_partner['description'] ); ?></p>
							<?php endif; ?>
						</div>

						<div class="mt-6 flex items-center justify-between">
							<a href="<?php echo esc_url( $itoi_link_url ); ?>" class="group inline-flex w-fit items-center rounded-full border border-ink px-5 py-[10px] text-[14px] font-semibold text-ink transition-colors hover:bg-ink hover:text-white">
								<span class="group-hover:hidden">Learn more</span>
								<span class="hidden group-hover:inline">Find out more</span>
							</a>
							<span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-ink text-white" aria-hidden="true">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
									<path d="M2 6.5A5.5 5.5 0 0 1 12.9 4.5M14 2v3h-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M14 9.5A5.5 5.5 0 0 1 3.1 11.5M2 14v-3h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( count( $itoi_partners ) > 1 ) : ?>
				<div class="mt-6 flex items-center gap-6">
					<div class="relative h-[3px] flex-1 rounded-full bg-line" id="partnersProgressTrack">
						<div class="absolute inset-y-0 left-0 rounded-full bg-ink" id="partnersProgressFill"></div>
					</div>
					<div class="flex flex-none gap-2">
						<button type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-hero-bg text-ink transition-colors hover:bg-line" id="partnersPrev" aria-label="Previous partner">&larr;</button>
						<button type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-hero-bg text-ink transition-colors hover:bg-line" id="partnersNext" aria-label="Next partner">&rarr;</button>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
