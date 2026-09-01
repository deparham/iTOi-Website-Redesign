<?php
/**
 * Insights archive — plain list, not a tile grid: PROJECT.md §3 reserves
 * the image-tile/arrow-nav mechanics for solutions/case-studies/industries
 * specifically; insights are a standard blog-style listing.
 *
 * Zero insight posts are published as of Phase 5 (see NOTES.md) — that's
 * a real, honest empty state (matches the live site having no blog
 * section yet), not a bug, so it's rendered as a message, not hidden.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_insights_query = new WP_Query(
	array(
		'post_type'      => 'insight',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
?>

<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
	<div class="mx-auto max-w-[1280px] <?php echo esc_attr( itoi_reveal_class() ); ?>">
		<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Insights</div>
		<h1 class="max-w-[20ch]">Ideas on vision, security and operations</h1>
	</div>
</section>

<section class="px-8 py-16 min-[980px]:py-[70px]">
	<div class="mx-auto max-w-[840px]">
		<h2 class="mb-8 text-2xl">Latest</h2>
		<?php if ( $itoi_insights_query->have_posts() ) : ?>
			<div class="divide-y divide-line border-t border-line">
				<?php
				while ( $itoi_insights_query->have_posts() ) :
					$itoi_insights_query->the_post();
					$itoi_dek       = get_field( 'dek' );
					$itoi_author_id = get_field( 'author' );
					$itoi_author_id = ! empty( $itoi_author_id ) ? $itoi_author_id[0] : null;
					$itoi_author    = $itoi_author_id ? get_field( 'name', $itoi_author_id ) : '';
					$itoi_thumb     = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
					?>
					<a href="<?php the_permalink(); ?>" class="group flex flex-col gap-5 py-8 min-[640px]:flex-row min-[640px]:items-center">
						<div class="relative aspect-[4/3] w-full flex-none overflow-hidden rounded-xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)] min-[640px]:w-[220px]">
							<?php if ( $itoi_thumb ) : ?>
								<?php echo get_the_post_thumbnail( get_the_ID(), 'medium', array( 'class' => 'absolute inset-0 h-full w-full object-cover', 'alt' => get_the_title_attribute( array( 'echo' => false ) ) ) ); ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php the_title_attribute(); ?> (TODO)</div>
							<?php endif; ?>
						</div>
						<div>
							<h2 class="text-[19px] font-extrabold group-hover:underline"><?php the_title(); ?></h2>
							<?php if ( $itoi_dek ) : ?>
								<p class="mt-1.5 text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_dek ); ?></p>
							<?php endif; ?>
							<div class="mt-2.5 text-[13px] font-semibold text-text-muted">
								<?php echo esc_html( get_the_date() ); ?><?php echo $itoi_author ? ' · ' . esc_html( $itoi_author ) : ''; ?>
							</div>
						</div>
					</a>
				<?php endwhile; ?>
			</div>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="text-text-muted">No insights published yet — check back soon.</p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
