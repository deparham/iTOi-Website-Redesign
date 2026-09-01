<?php
/**
 * Portfolio page (slug `portfolio`, page ID 23524 — was a placeholder
 * "coming soon" page, then built out as "Customers"; renamed back to
 * "Portfolio" 2026-08-10, see NOTES.md and inc/redirects.php for the old
 * /customers/ -> /portfolio/ redirect). `client` is a non-public CPT
 * (same pattern as team_member) queried directly here, filtered
 * client-side by the `client_category` taxonomy — the live portfolio
 * page's own 6 section headings, not the locked 7-slug `industry` CPT
 * (PROJECT.md §4 addendum). Only clients with a real `case_study`
 * relationship link through; everyone else is a name/wordmark card only.
 *
 * 2026-08-26: disabled per explicit instruction, kept as a real Site
 * Settings toggle (enable_portfolio_page, default OFF — Site Settings →
 * "Portfolio page" tab) rather than deleted outright, so it can be
 * switched back on later with no code change — same pattern as
 * show_traffic_demo (template-parts/home/traffic-demo.php). While off,
 * every request to /portfolio/ (including the /customers/ and
 * /our-portfolio/ old-URL redirects that land here, inc/redirects.php)
 * bounces straight to the homepage instead of rendering the client
 * directory below. Does NOT affect the separate "Portfolio" sub-section
 * on each Industry long-form page (single-industry.php) — different code,
 * left untouched.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! get_field( 'enable_portfolio_page', 'option' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
		<div class="mx-auto max-w-[840px]">
			<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Portfolio</div>
			<h1 class="max-w-[22ch]"><?php the_title(); ?></h1>
			<p class="mt-4 max-w-[62ch] text-text-muted">Retail chains, shopping centres, councils, hotels and venues across Australia and New Zealand run on ITOI. Filter by category below, or read a full case study where one exists.</p>
		</div>
	</section>

	<section class="px-8 py-16 min-[980px]:py-[70px]">
		<div class="mx-auto max-w-[1280px]">
			<?php
			$itoi_client_terms = get_terms(
				array(
					'taxonomy'   => 'client_category',
					'hide_empty' => true,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);

			$itoi_clients_query = new WP_Query(
				array(
					'post_type'      => 'client',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
			$itoi_total_clients = $itoi_clients_query->found_posts;
			?>

			<h2 class="sr-only">Client directory</h2>

			<?php if ( ! is_wp_error( $itoi_client_terms ) && $itoi_client_terms ) : ?>
				<div class="mb-10 flex flex-wrap gap-2.5" id="clientFilterRow" role="group" aria-label="Filter portfolio by category">
					<button type="button" class="client-filter-pill active rounded-full border-[1.5px] border-ink bg-ink px-5 py-2.5 text-[13.5px] font-bold text-white transition-all" data-filter="all" aria-pressed="true">
						All <span class="text-white/60">(<?php echo (int) $itoi_total_clients; ?>)</span>
					</button>
					<?php foreach ( $itoi_client_terms as $itoi_term ) : ?>
						<button type="button" class="client-filter-pill rounded-full border-[1.5px] border-line px-5 py-2.5 text-[13.5px] font-bold text-ink transition-all hover:border-ink" data-filter="<?php echo esc_attr( $itoi_term->slug ); ?>" aria-pressed="false">
							<?php echo esc_html( $itoi_term->name ); ?> <span class="text-text-muted">(<?php echo (int) $itoi_term->count; ?>)</span>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $itoi_clients_query->have_posts() ) : ?>
				<div class="grid grid-cols-2 gap-4 min-[640px]:grid-cols-3 min-[980px]:grid-cols-4" id="clientGrid">
					<?php
					while ( $itoi_clients_query->have_posts() ) :
						$itoi_clients_query->the_post();
						$itoi_client_name  = get_the_title();
						$itoi_client_terms_obj = get_the_terms( get_the_ID(), 'client_category' );
						$itoi_client_cat   = ( $itoi_client_terms_obj && ! is_wp_error( $itoi_client_terms_obj ) ) ? $itoi_client_terms_obj[0] : null;
						$itoi_logo_id      = get_field( 'logo' );
						$itoi_case_study_id = get_field( 'case_study' );
						$itoi_case_study_url = ( $itoi_case_study_id && get_post_status( $itoi_case_study_id ) === 'publish' ) ? get_permalink( $itoi_case_study_id ) : '';
						?>
						<div
							class="client-card group relative flex flex-col items-center justify-center gap-2 rounded-xl border border-line bg-white px-4 py-7 text-center transition-all hover:-translate-y-[3px] hover:border-ink focus-within:-translate-y-[3px] focus-within:border-ink"
							data-category="<?php echo $itoi_client_cat ? esc_attr( $itoi_client_cat->slug ) : ''; ?>"
						>
							<?php if ( $itoi_logo_id ) : ?>
								<?php echo wp_get_attachment_image( $itoi_logo_id, 'medium', false, array( 'class' => 'mb-1 h-8 max-w-full object-contain', 'alt' => $itoi_client_name ) ); ?>
							<?php else : ?>
								<div class="text-[14.5px] font-extrabold leading-snug"><?php echo esc_html( $itoi_client_name ); ?></div>
							<?php endif; ?>

							<?php if ( $itoi_client_cat ) : ?>
								<div class="text-[10.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_client_cat->name ); ?></div>
							<?php endif; ?>

							<?php if ( $itoi_case_study_url ) : ?>
								<a href="<?php echo esc_url( $itoi_case_study_url ); ?>" class="mt-1 inline-flex items-center gap-1 text-[12px] font-bold text-ink underline underline-offset-4 transition-all group-hover:gap-1.5">
									View case study &rarr;
								</a>
							<?php else : ?>
								<div class="mt-1 text-[11.5px] text-text-muted opacity-0 transition-opacity group-hover:opacity-100 group-focus-within:opacity-100">Logo &amp; name only</div>
							<?php endif; ?>
						</div>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="text-text-muted">No portfolio entries published yet.</p>
			<?php endif; ?>
		</div>
	</section>

	<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
		<div>
			<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">See what ITOI can do for your business</h2>
			<p class="m-0 max-w-[34ch] text-white/60">Join the retailers, councils and venues already running on ITOI.</p>
		</div>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Contact us</a>
	</div>

<?php
endwhile;

get_footer();
