<?php
/**
 * Guides index (/education/guides/) — tile grid, client-side filterable by
 * industry. Same filter-pill mechanic as page-customers.php's category
 * filter (data-filter attribute + active-state toggle), reused rather than
 * inventing a second pattern — wired up by initClientFilter-style logic
 * inline below since the pill/card ID pair (#guideFilterRow/#guideGrid) is
 * unique to this page.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_guides_query = new WP_Query(
	array(
		'post_type'      => 'guide',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);

// Build the industry filter pill list only from industries that actually
// have a published guide — an empty pill for an industry with zero guides
// would just be a dead end.
$itoi_industry_counts   = array();
$itoi_has_general_guide = false;
if ( $itoi_guides_query->have_posts() ) {
	foreach ( $itoi_guides_query->posts as $itoi_guide_post ) {
		$itoi_industry_id = get_field( 'industry', $itoi_guide_post->ID );
		if ( $itoi_industry_id ) {
			$itoi_industry_counts[ $itoi_industry_id ] = ( $itoi_industry_counts[ $itoi_industry_id ] ?? 0 ) + 1;
		} else {
			$itoi_has_general_guide = true;
		}
	}
}
?>

<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
	<div class="mx-auto max-w-[840px]">
		<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><a href="<?php echo esc_url( home_url( '/education/' ) ); ?>" class="hover:underline">Education Hub</a> / Guides</div>
		<h1 class="max-w-[20ch]">Guides</h1>
		<p class="mt-4 max-w-[62ch] text-text-muted">Plain-language explainers on retail analytics, security and the rest of the ITOI platform — filter by industry to find what's relevant to you.</p>
	</div>
</section>

<section class="px-8 py-16 min-[980px]:py-[70px]">
	<div class="mx-auto max-w-[1280px]">
		<?php if ( ! empty( $itoi_industry_counts ) || $itoi_has_general_guide ) : ?>
			<div class="mb-10 flex flex-wrap gap-2.5" id="guideFilterRow" role="group" aria-label="Filter guides by industry">
				<button type="button" class="guide-filter-pill active rounded-full border-[1.5px] border-ink bg-ink px-5 py-2.5 text-[13.5px] font-bold text-white transition-all" data-filter="all" aria-pressed="true">
					All <span class="text-white/60">(<?php echo (int) $itoi_guides_query->found_posts; ?>)</span>
				</button>
				<?php if ( $itoi_has_general_guide ) : ?>
					<button type="button" class="guide-filter-pill rounded-full border-[1.5px] border-line px-5 py-2.5 text-[13.5px] font-bold text-ink transition-all hover:border-ink" data-filter="general" aria-pressed="false">
						General
					</button>
				<?php endif; ?>
				<?php
				foreach ( $itoi_industry_counts as $itoi_ind_id => $itoi_count ) :
					$itoi_ind_name = itoi_or( get_field( 'name', $itoi_ind_id ), get_the_title( $itoi_ind_id ) );
					?>
					<button type="button" class="guide-filter-pill rounded-full border-[1.5px] border-line px-5 py-2.5 text-[13.5px] font-bold text-ink transition-all hover:border-ink" data-filter="industry-<?php echo (int) $itoi_ind_id; ?>" aria-pressed="false">
						<?php echo esc_html( $itoi_ind_name ); ?> <span class="text-text-muted">(<?php echo (int) $itoi_count; ?>)</span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $itoi_guides_query->have_posts() ) : ?>
			<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3" id="guideGrid">
				<?php
				while ( $itoi_guides_query->have_posts() ) :
					$itoi_guides_query->the_post();
					$itoi_id            = get_the_ID();
					$itoi_title         = itoi_or( get_field( 'title' ), get_the_title() );
					$itoi_dek           = get_field( 'dek' );
					$itoi_industry_id   = get_field( 'industry' );
					$itoi_industry_name = $itoi_industry_id ? itoi_or( get_field( 'name', $itoi_industry_id ), get_the_title( $itoi_industry_id ) ) : 'General';
					$itoi_read_time     = get_field( 'read_time_minutes' );
					$itoi_category      = $itoi_industry_id ? 'industry-' . $itoi_industry_id : 'general';
					?>
					<a href="<?php the_permalink(); ?>" class="guide-card group flex flex-col justify-between rounded-2xl border border-line bg-white p-6 transition-all hover:-translate-y-[3px] hover:border-ink" data-category="<?php echo esc_attr( $itoi_category ); ?>">
						<div>
							<div class="mb-3 flex items-center gap-2">
								<span class="rounded-full bg-teal-800/10 px-3 py-1 text-[11.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_industry_name ); ?></span>
								<?php if ( $itoi_read_time ) : ?>
									<span class="text-[12px] text-text-muted"><?php echo (int) $itoi_read_time; ?> min read</span>
								<?php endif; ?>
							</div>
							<h2 class="text-lg leading-snug"><?php echo esc_html( $itoi_title ); ?></h2>
							<?php if ( $itoi_dek ) : ?>
								<p class="mt-2 text-[14px] text-text-muted"><?php echo esc_html( $itoi_dek ); ?></p>
							<?php endif; ?>
						</div>
						<span class="mt-5 inline-flex items-center gap-1.5 text-[13px] font-bold text-ink">Read guide &rarr;</span>
					</a>
				<?php endwhile; ?>
			</div>
			<?php wp_reset_postdata(); ?>
			<p class="mt-8 hidden text-text-muted" id="guideFilterEmpty">No guides in this category yet.</p>
		<?php else : ?>
			<p class="text-text-muted">No guides published yet.</p>
		<?php endif; ?>
	</div>
</section>

<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
	<div>
		<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Ready to see it in action?</h2>
		<p class="m-0 max-w-[34ch] text-white/60">Book a demo and we'll show you exactly how ITOI applies to your business.</p>
	</div>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Get demo</a>
</div>

<?php
get_footer();
