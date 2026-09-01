<?php
/**
 * Products archive (/products/) — image-tile grid, same visual language as
 * archive-solution.php (PROJECT.md §3: "image-tile grids... not plain card
 * grids"). New 2026-07-31 alongside the `product` CPT becoming publicly
 * routable (inc/post-types.php) — see NOTES.md "Products turnstile +
 * per-product pages" entry. Reuses each product's "Homepage Turnstile Card"
 * fields (teaser_eyebrow/teaser_headline/teaser_supporting_line/teaser_photo/
 * teaser_video) for the tile — the same short summary already built for the
 * homepage turnstile, not a separate set of archive-specific fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_products_query = new WP_Query(
	array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		// "Enabled" toggle (product_enabled, inc/products.php) — see
		// itoi_product_enabled_meta_query()'s own comment for why this
		// specific NOT-EXISTS-OR-not-'0' shape, not a simpler equals check.
		'meta_query'     => array( itoi_product_enabled_meta_query() ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- handful of `product` posts total, no viable non-meta alternative for a true_false field.
	)
);
?>

<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
	<div class="mx-auto max-w-[1280px] <?php echo esc_attr( itoi_reveal_class() ); ?>">
		<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Products</div>
		<h1 class="max-w-[20ch]">The hardware behind ITOI's platform</h1>
	</div>
</section>

<section class="px-8 py-16 min-[980px]:py-[70px]">
	<div class="mx-auto max-w-[1280px]">
		<?php if ( $itoi_products_query->have_posts() ) : ?>
			<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
				<?php
				$itoi_tile_index = 0;
				while ( $itoi_products_query->have_posts() ) :
					$itoi_products_query->the_post();
					$itoi_id           = get_the_ID();
					$itoi_eyebrow      = get_field( 'teaser_eyebrow', $itoi_id );
					$itoi_headline     = get_field( 'teaser_headline', $itoi_id ) ?: get_the_title();
					$itoi_dek          = get_field( 'teaser_supporting_line', $itoi_id );
					$itoi_photo_id     = get_field( 'teaser_photo', $itoi_id );
					$itoi_photo_url    = $itoi_photo_id ? wp_get_attachment_image_url( $itoi_photo_id, 'medium_large' ) : '';
					$itoi_video        = get_field( 'teaser_video', $itoi_id );
					$itoi_placeholder  = get_field( 'teaser_placeholder_caption', $itoi_id ) ?: ( get_the_title() . ' product photo — pending' );
					$itoi_tile_index++;
					?>
					<a href="<?php echo esc_url( itoi_get_product_destination_url( $itoi_id ) ); ?>" class="group glass-element-light block overflow-hidden rounded-2xl <?php echo esc_attr( itoi_reveal_class() ); ?>" style="--reveal-radius:16px">
						<?php itoi_reveal_markup( $itoi_tile_index - 1 ); ?>
						<div class="aurora-device-stage relative flex aspect-[4/3] w-full items-center justify-center overflow-hidden">
							<?php
							$itoi_tile_media = itoi_media_cover(
								$itoi_photo_url,
								$itoi_video,
								$itoi_headline,
								'absolute inset-0 h-full w-full object-cover',
								1 === $itoi_tile_index ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'
							);
							?>
							<?php if ( $itoi_tile_media ) : ?>
								<?php echo $itoi_tile_media; ?>
							<?php else : ?>
								<div class="relative z-[1] flex max-w-[280px] flex-col items-center gap-1.5 rounded-lg border border-dashed border-white/25 bg-black/20 px-4 py-4 text-center">
									<span class="text-[10px] font-bold uppercase tracking-[0.08em] text-signature-bright">TODO(photo)</span>
									<p class="m-0 text-[11.5px] leading-snug text-white/65"><?php echo esc_html( $itoi_placeholder ); ?></p>
								</div>
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
			<p class="text-text-muted">No products published yet.</p>
		<?php endif; ?>
	</div>
</section>

<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
	<div>
		<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">See any product on your own floor plan.</h2>
		<p class="m-0 max-w-[34ch] text-white/60">Get a demo with real deployment data, not just a spec sheet.</p>
	</div>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Get demo &rarr;</a>
</div>

<?php
get_footer();
