<?php
/**
 * Industries archive — image-tile grid per PROJECT.md §3.
 * Same tile visual language as the homepage industries carousel
 * (front-page.php), but a full non-scrolling grid since this page's job
 * is to show every industry, not tease a few.
 *
 * Liquid glass wave 6, 2026-07-28 (see NOTES.md): tiles restructured to the
 * same photo-on-top / light-glass-panel-below shape the Solutions archive
 * got in the light-glass test wave, for the same reason — the old
 * eyebrow/headline-on-photo-scrim layout has no light surface for the new
 * on-light tokens to sit on. The "Learn more" pill switches from the dark
 * `.pill-glass` (correct while it sat on the photo) to a plain solid white
 * pill (matching the Solutions archive's restructured pill) now that it
 * sits in the light panel instead — `.pill-glass`'s white text would be
 * illegible there.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_industries_query = new WP_Query(
	array(
		'post_type'      => 'industry',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);
?>

<section class="aurora-bg-light border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
	<div class="mx-auto max-w-[1280px] <?php echo esc_attr( itoi_reveal_class() ); ?>">
		<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Industries</div>
		<h1 class="max-w-[20ch]">Modern protection for any space</h1>
	</div>
</section>

<section class="aurora-bg-light px-8 py-16 min-[980px]:py-[70px]">
	<div class="mx-auto max-w-[1280px]">
		<?php if ( $itoi_industries_query->have_posts() ) : ?>
			<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
				<?php
				$itoi_tile_index = 0;
				while ( $itoi_industries_query->have_posts() ) :
					$itoi_industries_query->the_post();
					$itoi_id      = get_the_ID();
					$itoi_name    = itoi_or( get_field( 'name' ), get_the_title() );
					$itoi_summary = get_field( 'summary' );
					$itoi_hero_id = get_field( 'hero_image' );
					$itoi_hero    = $itoi_hero_id ? wp_get_attachment_image_url( $itoi_hero_id, 'medium_large' ) : '';
					// Prefer the real Media Library alt text over the bare
					// industry name — see single-industry.php for the same fix.
					$itoi_hero_alt   = $itoi_hero_id ? itoi_or( get_post_meta( $itoi_hero_id, '_wp_attachment_image_alt', true ), $itoi_name ) : $itoi_name;
					$itoi_hero_video = get_field( 'hero_video' );
					++$itoi_tile_index;
					?>
					<a href="<?php the_permalink(); ?>" class="group glass-element-light block rounded-2xl <?php echo esc_attr( itoi_reveal_class() ); ?>" style="--reveal-radius:16px">
						<?php itoi_reveal_markup( $itoi_tile_index - 1 ); ?>
						<div class="relative aspect-[4/5] w-full overflow-hidden rounded-t-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
							<?php
							$itoi_tile_media = itoi_media_cover(
								$itoi_hero,
								$itoi_hero_video,
								$itoi_hero_alt,
								'absolute inset-0 h-full w-full object-cover',
								1 === $itoi_tile_index ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'
							);
							?>
							<?php if ( $itoi_tile_media ) : ?>
								<?php echo $itoi_tile_media; ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_name ); ?> (TODO)</div>
							<?php endif; ?>
						</div>
						<div class="relative px-5 pb-5 pt-4">
							<div class="text-[19px] font-extrabold text-ink"><?php echo esc_html( $itoi_name ); ?></div>
							<span class="relative mt-2 inline-flex items-center gap-2 rounded-[24px] bg-white px-[18px] py-2.5 text-[13px] font-bold text-ink shadow-[0_10px_24px_-10px_rgba(0,0,0,0.3)] transition-transform group-hover:-translate-y-0.5">Learn more &rarr;</span>
							<?php if ( $itoi_summary ) : ?>
								<p class="mt-2 line-clamp-2 text-[13.5px] text-text-muted"><?php echo esc_html( $itoi_summary ); ?></p>
							<?php endif; ?>
						</div>
					</a>
				<?php endwhile; ?>
			</div>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="text-text-muted">No industries published yet.</p>
		<?php endif; ?>
	</div>
</section>

<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
	<div>
		<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Don't see your industry listed?</h2>
		<p class="m-0 max-w-[34ch] text-white/60">Talk to us about your business or organisation's pain points &mdash; we build tailored solutions across retail, hospitality, government and more.</p>
	</div>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Contact us</a>
</div>

<?php
get_footer();
