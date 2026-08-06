<?php
/**
 * Industries — arrow-nav carousel + flip-card tiles (hover/tap reveals real
 * industry summary + Learn more on the back face). Full rationale:
 * docs/decisions/002-industries-carousel-flip-cards.md. Split out of
 * front-page.php 2026-08-06 (template-parts split) — markup/logic unchanged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="bg-hero-bg px-8 py-section-lg">
	<div class="mx-auto max-w-[1280px]">
		<div class="mb-[34px] flex items-end justify-between max-[980px]:flex-col max-[980px]:items-start max-[980px]:gap-4">
			<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
				<h2 class="max-w-[12ch] text-[clamp(26px,3vw,38px)]">Modern protection for any space</h2>
			</div>
			<div class="no-detect-reveal flex gap-2">
				<button class="flex h-11 w-11 items-center justify-center rounded-full border-[1.5px] border-ink bg-white text-base hover:bg-hero-bg" id="indPrev" aria-label="Previous industry">&larr;</button>
				<button class="flex h-11 w-11 items-center justify-center rounded-full border-[1.5px] border-ink bg-white text-base hover:bg-hero-bg" id="indNext" aria-label="Next industry">&rarr;</button>
			</div>
		</div>

		<div class="no-scrollbar flex gap-4 overflow-x-auto pb-9 [scroll-snap-type:x_mandatory]" id="indCarousel">
			<?php
			// PROJECT.md §4 locked slug order — pulled from the real `industry` CPT
			// (each entry's hero_image ACF field) rather than hardcoded here.
			$itoi_industry_slugs = array( 'retail', 'hospitality', 'casinos-gaming', 'banking-finance', 'government-councils', 'logistics-warehousing', 'stadiums-events' );
			// Per-photo object-position, checked individually against each real
			// image (not a blanket "center" guess) once the tiles were
			// standardized to one aspect ratio — see NOTES.md. Only
			// government-councils' native crop (a tall 3:4 chamber interior)
			// needed an override to keep the flag/upper gallery in frame
			// instead of centering into the empty floor; every other photo
			// reads correctly centered at 4:5.
			$itoi_object_position = array(
				'government-councils' => 'object-top',
			);
			foreach ( $itoi_industry_slugs as $itoi_slug ) :
				$itoi_post = get_page_by_path( $itoi_slug, OBJECT, 'industry' );
				if ( ! $itoi_post || 'publish' !== $itoi_post->post_status ) {
					continue;
				}
				$itoi_name       = get_field( 'name', $itoi_post->ID ) ?: get_the_title( $itoi_post );
				$itoi_summary    = get_field( 'summary', $itoi_post->ID );
				$itoi_photo_id   = get_field( 'hero_image', $itoi_post->ID );
				$itoi_photo_url  = $itoi_photo_id ? wp_get_attachment_image_url( $itoi_photo_id, 'large' ) : '';
				$itoi_photo_alt  = $itoi_photo_id ? get_post_meta( $itoi_photo_id, '_wp_attachment_image_alt', true ) : '';
				$itoi_hero_video = get_field( 'hero_video', $itoi_post->ID );
				$itoi_permalink  = get_permalink( $itoi_post );
				$itoi_obj_pos    = isset( $itoi_object_position[ $itoi_slug ] ) ? $itoi_object_position[ $itoi_slug ] : 'object-center';
				?>
				<div class="flip-card shrink-0 basis-[300px] aspect-[4/5] min-[640px]:basis-[320px]" data-href="<?php echo esc_url( $itoi_permalink ); ?>">
					<div class="flip-card-inner">
						<div class="flip-card-front relative overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)] [scroll-snap-align:start] after:absolute after:inset-0 after:bg-[linear-gradient(to_top,rgba(14,17,22,0.65),rgba(14,17,22,0)_50%)]">
							<?php
							$itoi_carousel_media = itoi_media_cover(
								$itoi_photo_url,
								$itoi_hero_video,
								$itoi_photo_alt ?: $itoi_name,
								'absolute inset-0 h-full w-full object-cover ' . $itoi_obj_pos,
								'loading="lazy"'
							);
							?>
							<?php if ( $itoi_carousel_media ) : ?>
								<?php echo $itoi_carousel_media; ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo — <?php echo esc_html( $itoi_name ); ?></div>
							<?php endif; ?>
							<div class="absolute bottom-5 left-5 z-[2] text-[19px] font-extrabold text-white"><?php echo esc_html( $itoi_name ); ?></div>
						</div>
						<div class="flip-card-back flex flex-col items-center justify-center gap-2.5 rounded-2xl bg-teal-900 px-5 py-6 text-center">
							<h3 class="m-0 text-[17px] text-white"><?php echo esc_html( $itoi_name ); ?></h3>
							<?php if ( $itoi_summary ) : ?>
								<p class="m-0 text-[13px] leading-[1.55] text-white/85"><?php echo esc_html( $itoi_summary ); ?></p>
							<?php endif; ?>
							<a href="<?php echo esc_url( $itoi_permalink ); ?>" class="mt-1 text-xs font-bold text-white underline">Learn more &rarr;</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
