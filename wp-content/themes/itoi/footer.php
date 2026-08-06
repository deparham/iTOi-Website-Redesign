<?php
/**
 * Footer: dense 5-column footer. Ground truth: preview-verkada-match.html <footer>.
 * The black "final CTA" band above the footer is homepage-specific markup
 * (§3 mechanic #10) and lives in front-page.php, not here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<footer class="border-t border-line px-8 pb-[30px] pt-14">
	<div class="mx-auto max-w-[1280px]">
		<div class="mb-11 grid grid-cols-1 gap-8 max-[980px]:grid-cols-2 min-[981px]:grid-cols-[1.4fr_1fr_1fr_1fr_1fr]">
			<div>
				<div class="mb-3 flex items-center gap-2 text-lg font-extrabold text-ink">
					<span class="h-2 w-2 rounded-full bg-signature"></span>ITOI Solutions
				</div>
				<p class="mb-2.5 text-[13.5px] text-text-muted">Image to intelligence — vision systems for retail, venues and security across Australia.</p>
			</div>

			<div>
				<?php
				/**
				 * 2026-08-05 (external improvement plan Phase 5.7): this
				 * column label and the 3 below it were <h3> — real document
				 * headings with no consistent H1/H2 lead-in of their own,
				 * since whatever heading a given page happens to end its
				 * main content on (which varies per template) sits directly
				 * above them. axe's heading-order rule flagged this on
				 * /solution-builder/ specifically (its own last content
				 * heading is an h3, so footer's h3 followed it with no
				 * step back down) — passed by coincidence on other pages
				 * only where their own last heading happened to be h2.
				 * <footer> is already a real landmark a screen reader user
				 * can jump to directly; these labels are visual link-group
				 * headers within it, not page-content headings, so <p> is
				 * the more accurate tag — same classes, zero visual change.
				 */
				?>
				<p class="mb-4 text-xs uppercase tracking-wider text-text-muted">Solutions</p>
				<?php
				$itoi_footer_solutions = new WP_Query( array(
					'post_type'      => 'solution',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
				) );
				while ( $itoi_footer_solutions->have_posts() ) :
					$itoi_footer_solutions->the_post();
					?>
					<a href="<?php the_permalink(); ?>" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink"><?php echo esc_html( get_field( 'headline' ) ?: get_the_title() ); ?></a>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<div>
				<p class="mb-4 text-xs uppercase tracking-wider text-text-muted">Company</p>
				<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">About</a>
				<a href="<?php echo esc_url( home_url( '/case-studies/' ) ); ?>" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">Case Studies</a>
				<a href="#" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">Careers</a>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">Contact</a>
			</div>

			<div>
				<p class="mb-4 text-xs uppercase tracking-wider text-text-muted">Support</p>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">Contact Support</a>
				<a href="#" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">Find an Installer</a>
				<a href="#" class="mb-2.5 block text-[13.5px] text-text-muted hover:text-ink">Product Updates</a>
			</div>

			<div>
				<p class="mb-4 text-xs uppercase tracking-wider text-text-muted">Head Office</p>
				<?php $itoi_footer_address = get_field( 'company_address', 'option' ); ?>
				<?php if ( $itoi_footer_address ) : ?>
					<p class="mb-2.5 text-[13.5px] text-text-muted"><?php echo esc_html( $itoi_footer_address ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="flex flex-wrap justify-between gap-2.5 border-t border-line pt-5 text-[12.5px] text-text-muted">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> ITOI Solutions. All rights reserved.</span>
			<span>Sydney, Australia &middot; 24/7 support</span>
		</div>
	</div>
</footer>

<?php
// ============ SOLUTION BUILDER POPUP — data ============
// Superseded 2026-07-24 (see NOTES.md, "Solution Builder popup replaces
// Find Your Fit" entry): this used to be the standalone "Find Your Fit"
// 3-question quiz with its own inline case-study/solution routing/results
// screen. That entire flow (questions + routing + results markup) has been
// removed, not left dormant — inc/finder.php (the routing resolver) is
// deleted and the ACF field group behind fyf_step1/2/3_* + the routing
// repeaters no longer exists (acf-json/group_bc153137ebbc.json). The modal
// shell itself (overlay, open/close, auto-open timing, floating trigger,
// progress-dot styling, amber active-state) is unchanged — only the
// questions and end behavior changed. The 7 questions below are the exact
// same ones as the dedicated /solution-builder/ page, from the exact same
// PHP source (inc/solution-builder.php) so the two never drift apart;
// completing this popup hands off to that page instead of showing results
// inline (see initFinder() in assets/js/main.js).
$fyf_eyebrow       = get_field( 'fyf_eyebrow', 'option' );
$fyf_heading       = get_field( 'fyf_heading', 'option' );
$fyf_subheading    = get_field( 'fyf_subheading', 'option' );
$fyf_trigger_label = get_field( 'fyf_trigger_label', 'option' );

$fyf_industries        = itoi_solution_builder_industries();
$fyf_employee_options  = itoi_solution_builder_employee_options();
$fyf_site_options      = itoi_solution_builder_site_options();
$fyf_challenge_options = itoi_solution_builder_challenge_options();
?>
<?php
// 2026-07-27 (whole-site visual consistency audit, see NOTES.md): suppressed
// entirely on /solution-builder/ itself — a floating "Build your solution"
// trigger (this popup's fyf_trigger_label) plus its own auto-opening 7-step
// quiz modal is a redundant, confusing CTA on the one page whose entire
// purpose already is that exact flow. Every other page is unaffected.
if ( ! is_page( 'solution-builder' ) ) :
	?>
<!-- ============ SOLUTION BUILDER POPUP — markup (shared, every page except /solution-builder/ itself) ============ -->
<!-- role="dialog"/aria-modal/aria-hidden added 2026-08-05 (Phase 5) — see
     #megaMenu's identical fix above for the reasoning (a closed overlay's
     heading otherwise still sits in the accessible tree). initFinder()
     (assets/js/main.js) toggles aria-hidden and now also traps focus
     while open. -->
<div class="finder-overlay" id="finderOverlay" role="dialog" aria-modal="true" aria-label="Solution builder" aria-hidden="true">
	<div class="finder-card p-10 max-[640px]:p-6">
		<button type="button" class="absolute right-[18px] top-[18px] flex h-[34px] w-[34px] items-center justify-center rounded-full border border-line bg-white text-base text-text-muted hover:bg-hero-bg hover:text-ink" id="finderClose" aria-label="Close">&times;</button>

		<div class="mb-7 text-center">
			<div class="mb-2 text-[12.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $fyf_eyebrow ); ?></div>
			<h2 class="text-[clamp(20px,2.4vw,26px)]"><?php echo esc_html( $fyf_heading ); ?></h2>
			<p class="m-0 text-sm"><?php echo esc_html( $fyf_subheading ); ?></p>
		</div>

		<div class="finder-progress" id="finderProgress"></div>

		<!-- Step 1 — Business type -->
		<div class="finder-step active" data-step="0">
			<div class="mb-5 text-center text-[17px] font-bold">What type of business are you?</div>
			<div class="finder-options grid grid-cols-2 gap-2.5" data-group="business_type" data-select="single">
				<?php foreach ( $fyf_industries as $fyf_industry ) : ?>
					<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="<?php echo esc_attr( $fyf_industry['slug'] ); ?>"><?php echo esc_html( $fyf_industry['title'] ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Step 2 — Employees -->
		<div class="finder-step" data-step="1">
			<div class="mb-5 text-center text-[17px] font-bold">How many employees do you have?</div>
			<div class="finder-options grid grid-cols-2 gap-2.5" data-group="employees" data-select="single">
				<?php foreach ( $fyf_employee_options as $fyf_value => $fyf_opt ) : ?>
					<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="<?php echo esc_attr( $fyf_value ); ?>"><?php echo esc_html( $fyf_opt['label'] ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Step 3 — Sites -->
		<div class="finder-step" data-step="2">
			<div class="mb-5 text-center text-[17px] font-bold">How many sites do you operate?</div>
			<div class="finder-options grid grid-cols-2 gap-2.5" data-group="sites" data-select="single">
				<?php foreach ( $fyf_site_options as $fyf_value => $fyf_opt ) : ?>
					<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="<?php echo esc_attr( $fyf_value ); ?>"><?php echo esc_html( $fyf_opt['label'] ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Step 4 — Existing CCTV -->
		<div class="finder-step" data-step="3">
			<div class="mb-5 text-center text-[17px] font-bold">Do you have existing CCTV?</div>
			<div class="finder-options grid grid-cols-2 gap-2.5" data-group="existing_cctv" data-select="single">
				<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="yes">Yes</button>
				<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="no">No</button>
			</div>
		</div>

		<!-- Step 5 — Existing POS -->
		<div class="finder-step" data-step="4">
			<div class="mb-5 text-center text-[17px] font-bold">Do you have an existing POS system?</div>
			<div class="finder-options grid grid-cols-2 gap-2.5" data-group="existing_pos" data-select="single">
				<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="yes">Yes</button>
				<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="no">No</button>
			</div>
		</div>

		<!-- Step 6 — Cloud-based -->
		<div class="finder-step" data-step="5">
			<div class="mb-5 text-center text-[17px] font-bold">Are your operations cloud-based?</div>
			<div class="finder-options grid grid-cols-2 gap-2.5" data-group="cloud_based" data-select="single">
				<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="yes">Yes</button>
				<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="no">No</button>
			</div>
		</div>

		<!-- Step 7 — Challenges (multi-select) -->
		<div class="finder-step" data-step="6">
			<div class="mb-1 text-center text-[17px] font-bold">What challenges are you currently facing?</div>
			<p class="mb-5 text-center text-[13px] text-text-muted">Choose any that apply.</p>
			<div class="finder-options grid grid-cols-2 gap-2.5" data-group="challenges" data-select="multi">
				<?php foreach ( $fyf_challenge_options as $fyf_value => $fyf_opt ) : ?>
					<button type="button" class="finder-opt rounded-xl border-[1.5px] border-line bg-white px-[18px] py-3.5 text-left text-[14.5px] font-semibold transition-colors hover:border-ink" data-value="<?php echo esc_attr( $fyf_value ); ?>"><?php echo esc_html( $fyf_opt['label'] ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="mt-7 flex items-center justify-between" id="finderNav">
			<button type="button" class="text-[13.5px] font-bold text-text-muted hover:text-ink disabled:pointer-events-none disabled:opacity-0" id="finderBack" disabled>&larr; Back</button>
			<button type="button" class="rounded-[24px] bg-cta px-6 py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover disabled:cursor-not-allowed disabled:opacity-35" id="finderNext" disabled>Next</button>
		</div>

		<p class="mt-4 text-center text-[13px] text-text-muted" id="finderLoading" hidden>Taking you to your results&hellip;</p>
	</div>
</div>

<button type="button" class="finder-trigger trigger-glass fixed bottom-[22px] right-[22px] z-[80] flex items-center gap-2.5 rounded-[30px] px-5 py-3.5 text-sm font-bold text-white hover:-translate-y-0.5 max-[640px]:p-3.5" id="finderTrigger" aria-label="<?php echo esc_attr( $fyf_trigger_label ); ?>">
	<span class="h-2 w-2 flex-none rounded-full bg-signature-bright"></span><span class="txt max-[640px]:hidden"><?php echo esc_html( $fyf_trigger_label ); ?></span>
</button>
<?php endif; // ! is_page( 'solution-builder' ) ?>

<?php wp_footer(); ?>
</body>
</html>
