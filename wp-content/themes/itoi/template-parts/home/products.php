<?php
/**
 * Meet Our Products — compact carousel of real `product` posts. Restored to
 * the homepage 2026-08-05 ("10/10" pass) — briefly moved to a standalone
 * /platform/ page, reverted per explicit instruction: "keep platform
 * overview on the main page... take product back to where it was." Split
 * out of front-page.php 2026-08-06 (template-parts split) — same markup/PHP,
 * same #productsCompactCarousel id initProductsCarousel() (main.js) targets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_pr_query = new WP_Query(
	array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	)
);
$itoi_pr_count = $itoi_pr_query->post_count;
?>
<?php if ( $itoi_pr_count > 0 ) : ?>
	<section class="border-b border-line bg-white px-8 py-section-sm">
		<div class="relative mx-auto max-w-[1280px]" id="productsCompactCarousel">
			<?php
			$itoi_pr_i = 0;
			while ( $itoi_pr_query->have_posts() ) :
				$itoi_pr_query->the_post();
				$itoi_pr_id        = get_the_ID();
				$itoi_pr_eyebrow   = get_field( 'teaser_eyebrow', $itoi_pr_id );
				$itoi_pr_headline  = get_field( 'teaser_headline', $itoi_pr_id ) ?: get_the_title();
				$itoi_pr_line      = get_field( 'teaser_supporting_line', $itoi_pr_id );
				$itoi_pr_link      = get_field( 'teaser_link_label', $itoi_pr_id ) ?: 'See product';
				$itoi_pr_photo     = get_field( 'teaser_photo', $itoi_pr_id );
				$itoi_pr_video     = get_field( 'teaser_video', $itoi_pr_id );
				$itoi_pr_caption   = get_field( 'teaser_placeholder_caption', $itoi_pr_id ) ?: ( get_the_title() . ' product photo — pending' );
				$itoi_pr_photo_url = $itoi_pr_photo ? wp_get_attachment_image_url( $itoi_pr_photo, 'large' ) : '';
				$itoi_pr_media     = itoi_media_cover( $itoi_pr_photo_url, $itoi_pr_video, $itoi_pr_headline, 'absolute inset-0 h-full w-full object-cover' );
				?>
				<a href="<?php the_permalink(); ?>" class="products-compact-card group flex flex-col overflow-hidden rounded-2xl border border-line transition-all hover:-translate-y-0.5 hover:border-ink min-[780px]:flex-row min-[780px]:items-stretch<?php echo 0 === $itoi_pr_i ? '' : ' is-hidden'; ?>">
					<div class="aurora-device-stage relative flex h-[180px] w-full flex-none items-center justify-center overflow-hidden min-[780px]:h-auto min-[780px]:w-[320px]">
						<?php if ( $itoi_pr_media ) : ?>
							<?php echo $itoi_pr_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
						<?php else : ?>
							<div class="relative z-[1] flex flex-col items-center gap-1.5 rounded-lg border border-dashed border-white/25 bg-black/20 px-4 py-4 text-center">
								<span class="text-[10px] font-bold uppercase tracking-[0.08em] text-signature-bright">TODO(photo)</span>
								<p class="m-0 text-[11.5px] leading-snug text-white/65"><?php echo esc_html( $itoi_pr_caption ); ?></p>
							</div>
						<?php endif; ?>
					</div>
					<div class="flex flex-1 flex-col items-start justify-center gap-2.5 px-6 py-6 min-[780px]:px-9">
						<?php if ( $itoi_pr_eyebrow ) : ?>
							<div class="flex items-center gap-2">
								<span class="h-2 w-2 rounded-full bg-teal-700"></span>
								<span class="text-xs font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_pr_eyebrow ); ?></span>
							</div>
						<?php endif; ?>
						<h2 class="m-0 max-w-[24ch] text-[22px]"><?php echo esc_html( $itoi_pr_headline ); ?></h2>
						<?php if ( $itoi_pr_line ) : ?>
							<p class="m-0 max-w-[52ch] text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_pr_line ); ?></p>
						<?php endif; ?>
						<span class="mt-1 text-[14px] font-bold text-ink underline underline-offset-4 transition-opacity group-hover:opacity-70"><?php echo esc_html( $itoi_pr_link ); ?> &rarr;</span>
					</div>
				</a>
				<?php
				$itoi_pr_i++;
			endwhile;
			wp_reset_postdata();
			?>

			<?php if ( $itoi_pr_count > 1 ) : ?>
				<button type="button" class="products-compact-arrow products-compact-prev flex h-9 w-9 items-center justify-center rounded-full border-[1.5px] border-ink bg-white text-sm hover:bg-hero-bg" aria-label="Previous product">&larr;</button>
				<button type="button" class="products-compact-arrow products-compact-next flex h-9 w-9 items-center justify-center rounded-full border-[1.5px] border-ink bg-white text-sm hover:bg-hero-bg" aria-label="Next product">&rarr;</button>
				<div class="products-compact-dots">
					<?php for ( $itoi_pr_dot_i = 0; $itoi_pr_dot_i < $itoi_pr_count; $itoi_pr_dot_i++ ) : ?>
						<button type="button" class="products-compact-dot<?php echo 0 === $itoi_pr_dot_i ? ' is-current' : ''; ?>" aria-label="Go to product <?php echo (int) ( $itoi_pr_dot_i + 1 ); ?>"></button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
