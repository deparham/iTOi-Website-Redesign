<?php
/**
 * Solution Builder (/solution-builder/) — PART 1-8 of the build spec.
 *
 * A multi-step, one-question-per-screen quiz (progress dots styled after
 * the "Find Your Fit" modal, footer.php/.finder-*) that ends in a rules-based
 * recommendation + ROI/timeline estimate, a lead-capture gate, and a
 * print-optimized proposal (window.print(), no server-side PDF dependency).
 * All scoring happens in PHP (inc/solution-builder.php) via AJAX — this
 * template only renders the option lists and the empty result containers
 * that assets/js/solution-builder.js fills in from the AJAX response.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Step 1 options — shared with the popup (footer.php) via inc/solution-builder.php.
$itoi_sb_industries        = itoi_solution_builder_industries();
$itoi_sb_employee_options  = itoi_solution_builder_employee_options();
$itoi_sb_site_options      = itoi_solution_builder_site_options();
$itoi_sb_challenge_options = itoi_solution_builder_challenge_options();

$itoi_sb_total_steps = 7;
?>

<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px]">
	<div class="mx-auto max-w-[840px]">
		<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Solution Builder</div>
		<h1 class="max-w-[22ch]">Build a solution shaped around your sites</h1>
		<p class="mt-4 max-w-[62ch] text-text-muted">Answer 7 quick questions about your business and we'll recommend the ITOI solutions most relevant to you, with an illustrative ROI estimate and implementation timeline &mdash; then send you a proposal.</p>
	</div>
</section>

<section class="px-8 py-section-md">
	<div class="mx-auto max-w-[720px]">

		<!-- ================= MULTI-STEP FORM ================= -->
		<div id="sbForm" class="rounded-2xl border border-line bg-white p-6 min-[640px]:p-10">

			<div class="sb-progress" id="sbProgress" role="progressbar" aria-valuemin="1" aria-valuemax="<?php echo (int) $itoi_sb_total_steps; ?>" aria-valuenow="1" aria-label="Solution Builder progress"></div>

			<!-- Step 1 — Business type -->
			<div class="sb-step active" data-step="0">
				<div class="sb-question">What type of business are you?</div>
				<div class="sb-options grid grid-cols-1 gap-2.5 min-[560px]:grid-cols-2" data-group="business_type" data-select="single">
					<?php foreach ( $itoi_sb_industries as $itoi_sb_industry ) : ?>
						<button type="button" class="sb-opt" data-value="<?php echo esc_attr( $itoi_sb_industry['slug'] ); ?>"><?php echo esc_html( $itoi_sb_industry['title'] ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Step 2 — Employees -->
			<div class="sb-step" data-step="1">
				<div class="sb-question">How many employees do you have?</div>
				<div class="sb-options grid grid-cols-2 gap-2.5" data-group="employees" data-select="single">
					<?php foreach ( $itoi_sb_employee_options as $itoi_sb_value => $itoi_sb_opt ) : ?>
						<button type="button" class="sb-opt" data-value="<?php echo esc_attr( $itoi_sb_value ); ?>"><?php echo esc_html( $itoi_sb_opt['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Step 3 — Sites -->
			<div class="sb-step" data-step="2">
				<div class="sb-question">How many sites do you operate?</div>
				<div class="sb-options grid grid-cols-2 gap-2.5" data-group="sites" data-select="single">
					<?php foreach ( $itoi_sb_site_options as $itoi_sb_value => $itoi_sb_opt ) : ?>
						<button type="button" class="sb-opt" data-value="<?php echo esc_attr( $itoi_sb_value ); ?>"><?php echo esc_html( $itoi_sb_opt['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Step 4 — Existing CCTV -->
			<div class="sb-step" data-step="3">
				<div class="sb-question">Do you have existing CCTV?</div>
				<div class="sb-options grid grid-cols-2 gap-2.5" data-group="existing_cctv" data-select="single">
					<button type="button" class="sb-opt" data-value="yes">Yes</button>
					<button type="button" class="sb-opt" data-value="no">No</button>
				</div>
			</div>

			<!-- Step 5 — Existing POS -->
			<div class="sb-step" data-step="4">
				<div class="sb-question">Do you have an existing POS system?</div>
				<div class="sb-options grid grid-cols-2 gap-2.5" data-group="existing_pos" data-select="single">
					<button type="button" class="sb-opt" data-value="yes">Yes</button>
					<button type="button" class="sb-opt" data-value="no">No</button>
				</div>
			</div>

			<!-- Step 6 — Cloud-based -->
			<div class="sb-step" data-step="5">
				<div class="sb-question">Are your operations cloud-based?</div>
				<div class="sb-options grid grid-cols-2 gap-2.5" data-group="cloud_based" data-select="single">
					<button type="button" class="sb-opt" data-value="yes">Yes</button>
					<button type="button" class="sb-opt" data-value="no">No</button>
				</div>
			</div>

			<!-- Step 7 — Challenges (multi-select) -->
			<div class="sb-step" data-step="6">
				<div class="sb-question">What challenges are you currently facing?</div>
				<p class="mb-5 -mt-3 text-center text-[13px] text-text-muted">Choose any that apply.</p>
				<div class="sb-options grid grid-cols-1 gap-2.5 min-[560px]:grid-cols-2" data-group="challenges" data-select="multi">
					<?php foreach ( $itoi_sb_challenge_options as $itoi_sb_value => $itoi_sb_opt ) : ?>
						<button type="button" class="sb-opt" data-value="<?php echo esc_attr( $itoi_sb_value ); ?>"><?php echo esc_html( $itoi_sb_opt['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="sb-nav mt-8 flex items-center justify-between" id="sbNav">
				<button type="button" id="sbBack" class="rounded-full border-[1.5px] border-line px-5 py-2.5 text-sm font-bold text-ink transition-colors hover:bg-hero-bg disabled:cursor-not-allowed disabled:opacity-40" disabled>Back</button>
				<button type="button" id="sbNext" class="rounded-full bg-cta px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-cta-hover disabled:cursor-not-allowed disabled:opacity-40" disabled>Next</button>
			</div>

			<div class="mt-4 text-center text-[13px] text-text-muted" id="sbLoading" hidden>Calculating your recommendation&hellip;</div>
		</div>

	</div>
</section>

<!-- ================= RESULTS ================= -->
<!-- Liquid glass, wave 5 (see NOTES.md): was plain --bg white; converted
	to the same dark-teal-900 + aurora-bg base wave 4 established for
	Delivery Model/Partners/Why ITOI. Own <section>, separate from the
	input-form steps above (#sbForm, still on plain white) — the two
	never render at once (solution-builder.js hides one before showing
	the other), so there's no moment where the form sits on this dark
	background. #sbResults IS the aurora element (JS toggles `hidden` on
	it directly, unchanged), so its own direct child picks up
	`.aurora-bg > *`'s position/z-index automatically. -->
<section id="sbResults" class="aurora-bg border-b border-line bg-teal-900 px-8 py-section-md" hidden>
	<div class="mx-auto max-w-[720px]">

		<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-signature-bright">Your Recommendation</div>
		<h2 class="mb-8 max-w-[24ch] text-white">Recommended solutions for your business</h2>

		<!-- Recommended-solution cards stay on their current solid styling
			(no glass, per the brief) — now with an explicit bg-white
			(solution-builder.js) since the section behind them is no
			longer implicitly white. -->
		<div id="sbRecommendedCards" class="mb-14 grid grid-cols-1 gap-5 min-[640px]:grid-cols-2"></div>

		<!-- Architecture visual — container is `.sb-flow`, rendered by
			renderArchitecture() in solution-builder.js; now glass
			(src/tailwind.css). -->
		<div class="mb-14">
			<h3 class="mb-5 text-lg text-white">How it fits together</h3>
			<div id="sbArchitecture" class="sb-architecture" aria-label="Solution architecture diagram"></div>
		</div>

		<!-- ROI estimate — light glass (.sb-roi-glass, rgba(255,255,255,0.75)),
			"the payoff moment," stays highly legible. Card headline/figure
			colors unchanged (text-ink). The mandatory disclaimer
			(#sbRoiDisclaimer) deliberately switched from text-text-muted
			to text-ink: worked the contrast math for the worst case
			(0.75-opacity white glass over this section's aurora backdrop,
			reverse-derived from wave 4's own documented blend) and
			text-muted (#616B78) landed close to/under the 4.5:1 AA floor
			for 14px text — text-ink clears it with a large margin
			(~12:1). Same "most conservative option for the one
			non-negotiable disclaimer" precedent as wave 3's case-study
			disclaimer. Weight/size/placement otherwise unchanged —
			still font-semibold, directly under the figure. -->
		<div class="sb-roi-glass mb-14 rounded-2xl p-7 min-[640px]:p-9">
			<h3 class="mb-1 text-lg">Estimated Annual Value</h3>
			<div class="text-[clamp(34px,5vw,48px)] font-extrabold text-ink" id="sbRoiTotal">&mdash;</div>
			<p class="sb-roi-disclaimer mt-4 text-[14px] font-semibold leading-[1.6] text-ink" id="sbRoiDisclaimer"></p>
		</div>

		<!-- Timeline — no card existed before this wave; added one
			(.sb-timeline-glass) since "convert the timeline card to
			glass" needs an actual card. Dark aurora default variant, text
			recolored for it (was text-ink/text-muted, calibrated for the
			old plain white section). -->
		<div class="sb-timeline-glass mb-14 rounded-2xl p-7 min-[640px]:p-9">
			<h3 class="mb-1 text-lg text-white">Implementation Timeline</h3>
			<div class="text-2xl font-extrabold text-white" id="sbTimelineRange">&mdash;</div>
			<p class="mt-2 text-[14px] text-white/70" id="sbTimelineCaption"></p>
		</div>

		<!-- Lead capture — untouched, no glass (form fields/download button
			explicitly excluded). Stays its own solid bg-white card, which
			reads fine as a light "ghost" card against the dark aurora
			backdrop — same precedent as wave 4's turnstile-compact cards. -->
		<div class="rounded-2xl border border-line bg-white p-6 min-[640px]:p-9" id="sbLeadCard">
			<h3 class="mb-1 text-lg">Get your full proposal</h3>
			<p class="mb-6 max-w-[54ch] text-[14.5px] text-text-muted">Enter your details and we'll send a copy your way &mdash; you can also download it now.</p>

			<form id="sbLeadForm" novalidate>
				<div class="grid grid-cols-1 gap-4 min-[640px]:grid-cols-2">
					<div>
						<label for="sbName" class="mb-1.5 block text-[13px] font-bold">Name <span aria-hidden="true">*</span></label>
						<input type="text" id="sbName" name="name" required class="w-full rounded-xl border-[1.5px] border-line px-4 py-3 text-[15px] focus:border-ink focus:outline-none">
					</div>
					<div>
						<label for="sbEmail" class="mb-1.5 block text-[13px] font-bold">Email <span aria-hidden="true">*</span></label>
						<input type="email" id="sbEmail" name="email" required class="w-full rounded-xl border-[1.5px] border-line px-4 py-3 text-[15px] focus:border-ink focus:outline-none">
					</div>
					<div>
						<label for="sbCompany" class="mb-1.5 block text-[13px] font-bold">Company <span aria-hidden="true">*</span></label>
						<input type="text" id="sbCompany" name="company" required class="w-full rounded-xl border-[1.5px] border-line px-4 py-3 text-[15px] focus:border-ink focus:outline-none">
					</div>
					<div>
						<label for="sbPhone" class="mb-1.5 block text-[13px] font-bold">Phone <span class="font-normal text-text-muted">(optional)</span></label>
						<input type="tel" id="sbPhone" name="phone" class="w-full rounded-xl border-[1.5px] border-line px-4 py-3 text-[15px] focus:border-ink focus:outline-none">
					</div>
				</div>

				<p class="mt-4 hidden text-[13.5px] font-semibold text-red-600" id="sbLeadError" role="alert"></p>

				<div class="mt-6 flex flex-wrap items-center gap-3.5">
					<button type="submit" class="rounded-full bg-cta px-6 py-3 text-sm font-bold text-white transition-colors hover:bg-cta-hover" id="sbLeadSubmit">Get my proposal</button>
					<button type="button" class="hidden rounded-full border-[1.5px] border-ink px-6 py-3 text-sm font-bold text-ink transition-colors hover:bg-hero-bg" id="sbDownloadBtn">Download as PDF</button>
				</div>
				<p class="mt-3 hidden text-[13.5px] font-semibold text-teal-800" id="sbLeadSuccess">Thanks &mdash; your proposal is ready below.</p>
			</form>
		</div>

	</div>
</section>

<!-- ================= PRINT-ONLY PROPOSAL ================= -->
<!-- Populated by JS only after a successful lead submit; invisible on
	screen (.sb-print-only), shown only inside @media print (src/tailwind.css)
	which also hides the header/ticker/nav/mega-menu/form chrome so
	window.print() produces a clean proposal document, not a screenshot
	of the whole page. -->
<div id="sbProposalPrint" class="sb-print-only"></div>

<?php
get_footer();
