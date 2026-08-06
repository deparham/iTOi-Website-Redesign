<?php
/**
 * /use-cases/ — the central Use Cases hub. Added 2026-07-23 (see NOTES.md).
 *
 * Converted 2026-07-30 (see NOTES.md) from a `use_case` CPT archive
 * template (archive-use_case.php, now deleted) to a real WP Page — that
 * CPT archive had no WP Page behind it, so the page's own headline/intro
 * copy had nowhere to live except a buried Site Settings options field.
 * H1 is this Page's own title, intro paragraph is this Page's own content
 * (the_content()) — both edited the normal way, like any other Page.
 * `use_case` is now `public => false` (inc/post-types.php) — no more
 * archive URL to conflict with this Page's own /use-cases/ slug.
 *
 * Still goes through itoi_get_industry_use_cases() (inc/use-cases.php)
 * rather than a plain `post_type=use_case` WP_Query, since that helper
 * also resolves each use case's linked industry/solution data and is
 * shared with the nav dropdown and homepage teaser.
 *
 * Client-side filter-pill mechanic matches archive-guide.php / page-customers.php
 * (data-filter attribute + active-state toggle, all filtering done in
 * assets/js/main.js's initUseCaseFilter() — no page reload). Industry-only
 * filtering for v1; a secondary solution-category filter was considered and
 * deferred as scope creep for a first pass — see NOTES.md.
 *
 * Light-glass test, 2026-07-27 (see NOTES.md): aurora-bg-light on the main
 * content section, .glass-element-light on the resting/inactive filter
 * pills (the active pill stays solid --ink — see the CSS comment above
 * .use-case-filter-pill.active in src/tailwind.css) and on the cards
 * (photo untouched/opaque on top, the info-bar below it is the glass
 * surface).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_all_use_cases = itoi_get_industry_use_cases();

// Build the industry filter pill list (with counts) only from industries
// that actually have at least one qualifying use case in the aggregated
// list — same "don't render a dead-end pill" rule as archive-guide.php.
$itoi_industry_counts = array();
foreach ( $itoi_all_use_cases as $itoi_uc ) {
	$itoi_key = $itoi_uc['industry_id'];
	if ( ! isset( $itoi_industry_counts[ $itoi_key ] ) ) {
		$itoi_industry_counts[ $itoi_key ] = array(
			'name'  => $itoi_uc['industry_name'],
			'slug'  => $itoi_uc['industry_slug'],
			'count' => 0,
		);
	}
	++$itoi_industry_counts[ $itoi_key ]['count'];
}
uasort(
	$itoi_industry_counts,
	function ( $itoi_a, $itoi_b ) {
		return strcasecmp( $itoi_a['name'], $itoi_b['name'] );
	}
);

/**
 * Highlight panel — 2026-07-28 (see NOTES.md). Unlike the solution pages'
 * version, this page has no `specs`-style field to curate from — its real
 * data IS the aggregated use-case list already built above, so every
 * number here is computed live from $itoi_all_use_cases (never hardcoded),
 * confirmed fresh on every page load rather than assumed from an earlier
 * count. Full-width, no photo — see NOTES.md for why (a single photo would
 * arbitrarily over-represent one of the 7 industries this page aggregates
 * across).
 */
$itoi_hl_total       = count( $itoi_all_use_cases );
$itoi_hl_industries  = count( $itoi_industry_counts );
$itoi_hl_by_solution = array();
foreach ( $itoi_all_use_cases as $itoi_hl_uc ) {
	$itoi_hl_key                         = $itoi_hl_uc['solution_title'];
	$itoi_hl_by_solution[ $itoi_hl_key ] = ( $itoi_hl_by_solution[ $itoi_hl_key ] ?? 0 ) + 1;
}
arsort( $itoi_hl_by_solution );
$itoi_hl_top_solution = array_key_first( $itoi_hl_by_solution );
$itoi_hl_top_count    = $itoi_hl_by_solution[ $itoi_hl_top_solution ] ?? 0;

while ( have_posts() ) :
	the_post();
	?>

	<?php if ( $itoi_hl_total > 0 ) : ?>
	<section class="bg-ink relative overflow-hidden px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px] <?php echo esc_attr( itoi_reveal_class() ); ?>">
		<div class="relative z-[1] mx-auto max-w-[1280px]">
			<h2 class="max-w-[30ch] text-[clamp(24px,3vw,34px)] text-white"><?php echo (int) $itoi_hl_total; ?> real use cases. <?php echo (int) $itoi_hl_industries; ?> industries. One platform.</h2>
			<div class="mt-9 grid grid-cols-1 gap-4 min-[640px]:grid-cols-3">
				<div class="highlight-panel-glass flex flex-col justify-center p-6">
					<div class="text-[36px] font-extrabold leading-tight text-white"><?php echo (int) $itoi_hl_total; ?></div>
					<div class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-white/60">Real use cases across <?php echo (int) $itoi_hl_industries; ?> industries</div>
				</div>
				<?php if ( $itoi_hl_top_solution && $itoi_hl_top_count > 0 ) : ?>
					<div class="highlight-panel-glass flex flex-col justify-center p-6">
						<div class="text-[36px] font-extrabold leading-tight text-white"><?php echo (int) $itoi_hl_top_count; ?></div>
						<div class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-white/60"><?php echo esc_html( $itoi_hl_top_solution ); ?></div>
					</div>
				<?php endif; ?>
				<div class="highlight-panel-glass flex flex-col justify-center p-6">
					<div class="text-[36px] font-extrabold leading-tight text-white"><?php echo (int) $itoi_hl_industries; ?></div>
					<div class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-white/60">Industries covered</div>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>

<section class="aurora-bg-light border-b border-line bg-hero-bg px-8 py-16 min-[980px]:py-[70px]">
	<div class="mx-auto max-w-[840px] <?php echo esc_attr( itoi_reveal_class() ); ?>">
		<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Use Cases</div>
		<h1 class="max-w-[22ch]"><?php the_title(); ?></h1>
		<div class="prose mt-4 max-w-[62ch] text-text-muted [&_p]:m-0"><?php the_content(); ?></div>
	</div>
</section>

<section class="aurora-bg-light px-8 py-16 min-[980px]:py-[70px]">
	<div class="mx-auto max-w-[1280px]">
		<?php if ( ! empty( $itoi_all_use_cases ) ) : ?>
			<div class="mb-10 flex flex-wrap gap-2.5" id="useCaseFilterRow" role="group" aria-label="Filter use cases by industry">
				<button type="button" class="use-case-filter-pill glass-element-light active rounded-full px-5 py-2.5 text-[13.5px] font-bold text-white transition-all" data-filter="all" aria-pressed="true">
					All <span class="text-white/60">(<?php echo (int) count( $itoi_all_use_cases ); ?>)</span>
				</button>
				<?php foreach ( $itoi_industry_counts as $itoi_ind_id => $itoi_ind ) : ?>
					<button type="button" class="use-case-filter-pill glass-element-light rounded-full px-5 py-2.5 text-[13.5px] font-bold text-ink transition-all" data-filter="industry-<?php echo (int) $itoi_ind_id; ?>" aria-pressed="false">
						<?php echo esc_html( $itoi_ind['name'] ); ?> <span class="text-text-muted">(<?php echo (int) $itoi_ind['count']; ?>)</span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3" id="useCaseGrid">
				<?php
				foreach ( $itoi_all_use_cases as $itoi_uc ) :
					$itoi_image_url = $itoi_uc['image_id'] ? wp_get_attachment_image_url( $itoi_uc['image_id'], 'medium_large' ) : '';
					$itoi_uc_media  = itoi_media_cover( $itoi_image_url, $itoi_uc['video'], $itoi_uc['label'], 'absolute inset-0 h-full w-full object-cover', 'loading="lazy"' );
					?>
					<a href="<?php echo esc_url( $itoi_uc['solution_url'] ); ?>" class="use-case-card glass-element-light group block overflow-hidden rounded-2xl transition-all hover:-translate-y-[3px]" data-category="industry-<?php echo (int) $itoi_uc['industry_id']; ?>">
						<div class="relative aspect-[16/10] w-full overflow-hidden bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
							<?php if ( $itoi_uc_media ) : ?>
								<?php echo $itoi_uc_media; ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_uc['label'] ); ?> (TODO)</div>
							<?php endif; ?>
						</div>
						<div class="relative flex flex-col gap-2 px-5 py-4">
							<span class="inline-flex w-fit items-center rounded-full bg-teal-800/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_uc['industry_name'] ); ?></span>
							<div class="flex items-center justify-between gap-3">
								<span class="text-[14px] font-bold"><?php echo esc_html( $itoi_uc['label'] ); ?></span>
								<span class="text-base text-text-muted transition-transform group-hover:translate-x-0.5" aria-hidden="true">&rarr;</span>
							</div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
			<p class="mt-8 hidden text-text-muted" id="useCaseFilterEmpty">No use cases in this industry yet.</p>
		<?php else : ?>
			<p class="text-text-muted">No use cases published yet.</p>
		<?php endif; ?>
	</div>
</section>

<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
	<div>
		<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">See your use case in action</h2>
		<p class="m-0 max-w-[34ch] text-white/60">Book a demo and we'll walk through the exact solution for your industry.</p>
	</div>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Get demo</a>
</div>

	<?php
endwhile;

get_footer();
