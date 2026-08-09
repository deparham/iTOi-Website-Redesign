<?php
/**
 * Solutions archive — image-tile grid per PROJECT.md §3 ("image-tile grids
 * or arrow-nav carousels, not plain card grids"). The homepage's compact
 * icon row (front-page.php "products" section) is explicitly a smaller,
 * separate teaser per §3 — this archive uses the fuller tile treatment,
 * same visual language as archive-industry.php.
 *
 * Light-glass test, 2026-07-27 (see NOTES.md) — tile cards restructured
 * from "eyebrow/headline on the photo over a dark scrim" (archive-industry.php's
 * still-unchanged pattern) to "photo on top, light-glass info panel below."
 * The new on-light glass tokens are for genuinely light surfaces, not a
 * dark photo scrim, so this page no longer shares that exact markup with
 * archive-industry.php — a deliberate, approved divergence for this test
 * only. Section-level aurora-bg-light added to the tile-grid section.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_solutions_query = new WP_Query(
	array(
		'post_type'      => 'solution',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);
?>

<section class="aurora-bg-light border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
	<div class="mx-auto max-w-[1280px] <?php echo esc_attr( itoi_reveal_class() ); ?>">
		<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Solutions</div>
		<h1 class="max-w-[20ch]">One integrated platform for cameras, access and analytics</h1>
	</div>
</section>

<section class="aurora-bg-light px-8 py-16 min-[980px]:py-[70px]">
	<div class="mx-auto max-w-[1280px]">
		<?php if ( $itoi_solutions_query->have_posts() ) : ?>
			<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
				<?php
				$itoi_tile_index = 0;
				while ( $itoi_solutions_query->have_posts() ) :
					$itoi_solutions_query->the_post();
					$itoi_eyebrow    = get_field( 'eyebrow' );
					$itoi_headline   = itoi_or( get_field( 'headline' ), get_the_title() );
					$itoi_dek        = get_field( 'dek' );
					$itoi_tile_id    = get_field( 'tile_image' );
					$itoi_tile_img   = $itoi_tile_id ? wp_get_attachment_image_url( $itoi_tile_id, 'medium_large' ) : '';
					$itoi_tile_video = get_field( 'tile_video' );
					++$itoi_tile_index;
					?>
					<a href="<?php the_permalink(); ?>" class="group glass-element-light block rounded-2xl <?php echo esc_attr( itoi_reveal_class() ); ?>" style="--reveal-radius:16px">
						<?php itoi_reveal_markup( $itoi_tile_index - 1 ); ?>
						<div class="relative aspect-[4/5] w-full overflow-hidden rounded-t-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
							<?php
							$itoi_tile_media = itoi_media_cover(
								$itoi_tile_img,
								$itoi_tile_video,
								$itoi_headline,
								'absolute inset-0 h-full w-full object-cover',
								1 === $itoi_tile_index ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'
							);
							?>
							<?php if ( $itoi_tile_media ) : ?>
								<?php echo $itoi_tile_media; ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_headline ); ?> (TODO)</div>
							<?php endif; ?>
						</div>
						<div class="relative px-5 pb-5 pt-4">
							<?php if ( $itoi_eyebrow ) : ?>
								<div class="mb-1 text-[11px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_eyebrow ); ?></div>
							<?php endif; ?>
							<div class="text-[19px] font-extrabold text-ink"><?php echo esc_html( $itoi_headline ); ?></div>
							<span class="relative mt-2 inline-flex items-center gap-2 rounded-[24px] bg-white px-[18px] py-2.5 text-[13px] font-bold text-ink shadow-[0_10px_24px_-10px_rgba(0,0,0,0.3)] transition-transform group-hover:-translate-y-0.5">Learn more &rarr;</span>
							<?php if ( $itoi_dek ) : ?>
								<p class="mt-2 line-clamp-2 text-[13.5px] text-text-muted"><?php echo esc_html( $itoi_dek ); ?></p>
							<?php endif; ?>
						</div>
					</a>
				<?php endwhile; ?>
			</div>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="text-text-muted">No solutions published yet.</p>
		<?php endif; ?>
	</div>
</section>

<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
	<div>
		<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Try ITOI on your first site, free</h2>
		<p class="m-0 max-w-[34ch] text-white/60">Our pilot program includes one site, full dashboard access, and a dedicated specialist.</p>
	</div>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Contact us</a>
</div>

<?php
get_footer();
