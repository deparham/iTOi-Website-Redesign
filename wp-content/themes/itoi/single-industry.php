<?php
/**
 * Single industry detail — pulls every ACF Industry field (PROJECT.md §4).
 * client_examples/related_solutions render only what's real — empty for
 * industries with no live-site-supported evidence (casinos-gaming had
 * none at all as of Phase 5).
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$itoi_name    = itoi_or( get_field( 'name' ), get_the_title() );
	$itoi_summary = get_field( 'summary' );
	$itoi_hero_id = get_field( 'hero_image' );
	$itoi_hero    = $itoi_hero_id ? wp_get_attachment_image_url( $itoi_hero_id, 'large' ) : '';
	// Optional — takes priority over the photo above wherever the hero
	// renders (both the funnel photo-hero-with-inset-card layout below and
	// the plain side-by-side hero); the photo still doubles as its poster.
	$itoi_hero_video = get_field( 'hero_video' );
	// Prefer the real, descriptive alt text set on the attachment in the
	// Media Library over the bare industry name — every current hero photo
	// already has one (e.g. "Generic retail store interior with merchandise
	// displays (temporary stock photo)"); only fall back to the name if an
	// editor ever uploads one without setting alt text.
	$itoi_hero_alt      = $itoi_hero_id ? itoi_or( get_post_meta( $itoi_hero_id, '_wp_attachment_image_alt', true ), $itoi_name ) : $itoi_name;
	$itoi_clients       = get_field( 'client_examples' );
	$itoi_rel_solutions = get_field( 'related_solutions' );

	// Foot-traffic funnel — new component, not in PROJECT.md's original §10
	// phase plan (added 2026-07-20, see NOTES.md). Field group is available
	// on every industry so it's reusable without a code change, but only
	// renders where an editor has explicitly turned it on and filled in a
	// headline — currently just Retail.
	//
	// 2026-07-20 revision: when the funnel is enabled, its headline/intro
	// now ARE the page's single hero headline/description (merged per
	// explicit instruction — previously the page had two back-to-back
	// "big headline" moments: a generic industry hero, then a second
	// headline directly above the funnel). Fetched here, before the hero
	// section, so both sections can use them.
	// "funnel_enabled"/"funnel_headline"/"funnel_intro" are the generic
	// hero-interaction toggle/headline/intro fields, reused across every
	// industry's version of this card (the field names are a holdover
	// from Retail being built first — renaming them now would be a wider,
	// riskier change than needed; the ACF group itself was already
	// retitled "Industry — Hero Interaction" to reflect the real scope).
	$itoi_funnel_enabled  = get_field( 'funnel_enabled' );
	$itoi_funnel_headline = get_field( 'funnel_headline' );
	$itoi_show_funnel     = $itoi_funnel_enabled && $itoi_funnel_headline;
	$itoi_slug            = get_post_field( 'post_name' );

	if ( $itoi_show_funnel ) {
		$itoi_funnel_intro      = get_field( 'funnel_intro' );
		$itoi_funnel_disclaimer = get_field( 'funnel_disclaimer' );
	}

	// Retail: foot-traffic funnel (built first, see NOTES.md history).
	if ( 'retail' === $itoi_slug && $itoi_show_funnel ) {
		$itoi_funnel_traffic = (int) itoi_or( get_field( 'funnel_default_traffic' ), 10000 );
		$itoi_funnel_rate    = (float) itoi_or( get_field( 'funnel_conversion_rate' ), 0 );
		$itoi_funnel_value   = (float) itoi_or( get_field( 'funnel_value_per_lead' ), 0 );
	}

	// Hospitality: click-through guest-journey timeline.
	if ( 'hospitality' === $itoi_slug && $itoi_show_funnel ) {
		$itoi_hosp_stages       = itoi_or( get_field( 'hospitality_stages' ), array() );
		$itoi_hosp_completeness = itoi_or( get_field( 'hospitality_completeness_label' ), 'Guest Profile Completeness' );
		$itoi_hosp_reset        = itoi_or( get_field( 'hospitality_reset_label' ), 'Start over' );
	}

	// Banking & Finance: access scenario simulator.
	if ( 'banking-finance' === $itoi_slug && $itoi_show_funnel ) {
		$itoi_bank_scenarios = itoi_or( get_field( 'banking_scenarios' ), array() );
	}

	// Government & Councils: live funding-allocation chart.
	if ( 'government-councils' === $itoi_slug && $itoi_show_funnel ) {
		$itoi_gov_categories = itoi_or( get_field( 'government_categories' ), array() );
		$itoi_gov_checkboxes = itoi_or( get_field( 'government_checkboxes' ), array() );
	}

	// Logistics & Warehousing: drag-to-compare incident timeline. Gap-label
	// positions are the midpoint between the first two events (Incident
	// occurs → Discovered) on each line — computed once here rather than
	// in the template.
	if ( 'logistics-warehousing' === $itoi_slug && $itoi_show_funnel ) {
		$itoi_log_events        = itoi_or( get_field( 'logistics_events' ), array() );
		$itoi_log_without_label = itoi_or( get_field( 'logistics_without_label' ), 'Without ITOI' );
		$itoi_log_with_label    = itoi_or( get_field( 'logistics_with_label' ), 'With ITOI' );
		$itoi_log_gap_without   = get_field( 'logistics_gap_label_without' );
		$itoi_log_gap_with      = get_field( 'logistics_gap_label_with' );

		$itoi_log_gap_pos_without = 0;
		$itoi_log_gap_pos_with    = 0;
		if ( count( $itoi_log_events ) >= 2 ) {
			$itoi_log_gap_pos_without = ( (float) $itoi_log_events[0]['without_position'] + (float) $itoi_log_events[1]['without_position'] ) / 2;
			$itoi_log_gap_pos_with    = ( (float) $itoi_log_events[0]['with_position'] + (float) $itoi_log_events[1]['with_position'] ) / 2;
		}
		$itoi_log_gap_anchor_without = $itoi_log_gap_pos_without <= 5 ? '0%' : ( $itoi_log_gap_pos_without >= 95 ? '-100%' : '-50%' );
		$itoi_log_gap_anchor_with    = $itoi_log_gap_pos_with <= 5 ? '0%' : ( $itoi_log_gap_pos_with >= 95 ? '-100%' : '-50%' );
	}

	// Stadiums & Events: live density visualization. Marker pools are
	// generated client-side (JS), this just passes the zone weights and
	// slider bounds through as data.
	if ( 'stadiums-events' === $itoi_slug && $itoi_show_funnel ) {
		$itoi_stadium_zones      = itoi_or( get_field( 'stadium_zones' ), array() );
		$itoi_stadium_min        = (int) itoi_or( get_field( 'stadium_min_attendees' ), 5000 );
		$itoi_stadium_max        = (int) itoi_or( get_field( 'stadium_max_attendees' ), 50000 );
		$itoi_stadium_default    = (int) itoi_or( get_field( 'stadium_default_attendees' ), 15000 );
		$itoi_stadium_max_marker = (int) itoi_or( get_field( 'stadium_max_markers' ), 50 );
	}

	// Casinos & Gaming: zone-selector floor map. Zone position on the
	// floor plan is fixed by row order (see the field's own instructions),
	// not stored separately — only the copy is ACF-driven.
	if ( 'casinos-gaming' === $itoi_slug && $itoi_show_funnel ) {
		$itoi_casino_zones = itoi_or( get_field( 'casino_zones' ), array() );
	}

	$itoi_hero_headline = $itoi_show_funnel ? $itoi_funnel_headline : $itoi_name;
	$itoi_hero_desc     = $itoi_show_funnel ? $itoi_funnel_intro : $itoi_summary;

	// Photo-hero-with-inset-card layout — Retail + Hospitality for now,
	// pending review before wider rollout (see NOTES.md). Every other
	// industry keeps the original side-by-side hero below.
	$itoi_use_photo_hero = $itoi_show_funnel && $itoi_hero;
	?>

	<?php if ( $itoi_use_photo_hero ) : ?>

		<!-- One continuous rounded card (photo top + funnel bottom), inset
			within the page's normal container width — not full-bleed. A
			single shadow wraps the whole block; overflow-hidden + one
			border-radius on this outer wrapper clips the photo to rounded
			top corners and the funnel section to rounded bottom corners
			automatically, with a flat/square seam where they meet (no
			separate overlap mechanic anymore — that was the previous
			"floating card over full-bleed photo" version; this replaces
			it, see NOTES.md). -->
		<section class="aurora-bg-light px-8 pt-[168px] pb-10 min-[640px]:pt-[206px] min-[980px]:pb-16">
			<div class="relative mx-auto max-w-[1280px] overflow-hidden rounded-[22px] shadow-[0_30px_60px_-30px_rgba(14,17,22,0.25)]" id="funnelSection" data-conversion-rate="<?php echo esc_attr( $itoi_funnel_rate ?? '' ); ?>" data-value-per-lead="<?php echo esc_attr( $itoi_funnel_value ?? '' ); ?>">

				<!-- Photo portion — height is content-driven at every breakpoint
					(top padding is the free variable): a strict CSS aspect-ratio
					box was tried here and rejected — with in-flow content plus
					overflow-hidden, the eyebrow/H1 got clipped whenever content
					needed more room than the ratio allowed (see NOTES.md). Mobile
					top padding is tuned to land near a 4:3-ish proportion in
					practice; ≥768px padding is tuned to land in the spec's
					55-60%-of-card-height band. -->
				<div class="relative overflow-hidden text-white">
					<div class="absolute inset-0">
						<?php if ( ! empty( $itoi_hero_video['url'] ) ) : ?>
							<video class="h-full w-full object-cover itoi-media-video" muted loop playsinline preload="none" <?php echo $itoi_hero ? 'poster="' . esc_url( $itoi_hero ) . '"' : ''; ?>>
								<source src="<?php echo esc_url( $itoi_hero_video['url'] ); ?>">
							</video>
						<?php else : ?>
							<?php
							echo wp_get_attachment_image(
								$itoi_hero_id,
								'1536x1536',
								false,
								array(
									'alt'           => $itoi_hero_alt,
									'class'         => 'h-full w-full object-cover',
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'sizes'         => '100vw',
								)
							);
							?>
						<?php endif; ?>
					</div>
					<!-- Heavier at the bottom, fading lighter toward the top — same
						legibility approach as the homepage mega-hero's dark scrim,
						adapted for a photo background. Also still doing the
						photo-to-white-card transition on its own at the very
						bottom (per the previous round's reference), now simply
						handing off to a flush seam instead of an overlap. -->
					<div class="absolute inset-0 bg-[linear-gradient(to_top,rgba(11,14,17,0.82)_0%,rgba(11,14,17,0.25)_55%,rgba(11,14,17,0.15)_100%)]"></div>

					<div class="relative z-[2] px-8 pb-9 pt-20 md:px-11 md:pb-11 md:pt-[100px]">
						<div class="max-w-[640px]">
							<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-signature-bright">Industry / <?php echo esc_html( $itoi_name ); ?></div>
							<h1 class="max-w-[16ch] text-white min-[980px]:max-w-[24ch]"><?php echo esc_html( $itoi_hero_headline ); ?></h1>
							<?php if ( $itoi_hero_desc ) : ?>
								<p class="mt-4 max-w-[60ch] text-[14.5px] text-white/85"><?php echo esc_html( $itoi_hero_desc ); ?></p>
							<?php endif; ?>
							<div class="mt-7 flex flex-wrap gap-2.5">
								<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover">Get demo</a>
								<a href="<?php echo esc_url( home_url( '/industries/' ) ); ?>" class="rounded-full border-[1.5px] border-white bg-transparent px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-white hover:text-ink">All industries</a>
							</div>
						</div>
					</div>
				</div>

				<!-- Interaction portion — flush against the photo above, no
					headline by design (direct payoff of the hero's claim). This
					whole block is .no-detect-reveal: it's an interactive tool a
					visitor engages with immediately, not a passive content block,
					so it must never pick up the scroll-triggered corner-bracket
					effect (see NOTES.md + the JS's :not(.no-detect-reveal) guard). -->
				<div class="no-detect-reveal glass-panel-light p-6 min-[900px]:p-8">
					<?php if ( 'retail' === $itoi_slug ) : ?>

						<!-- Label row: "Monthly foot traffic" left, "Illustrative
							estimate" right, same row — then the slider/number input
							directly below, unboxed. Still fully functional — both
							inputs, both wired, same JS as before. -->
						<div class="mb-3 flex items-center justify-between">
							<label for="funnelTrafficSlider" class="text-[14px] font-bold">Monthly foot traffic</label>
							<span class="text-[13px] text-text-muted">Illustrative estimate</span>
						</div>
						<div class="mb-8 flex flex-wrap items-center gap-4">
							<input type="range" id="funnelTrafficSlider" min="1000" max="50000" step="500" value="<?php echo esc_attr( $itoi_funnel_traffic ); ?>" class="h-1.5 w-full max-w-[300px] flex-1 accent-signature">
							<input type="number" id="funnelTrafficInput" min="0" step="500" value="<?php echo esc_attr( $itoi_funnel_traffic ); ?>" aria-label="Monthly foot traffic (exact number)" class="w-[110px] rounded-lg border border-line bg-white px-3 py-2 text-sm font-bold">
						</div>

						<!-- Three plain columns, no arrows/dividers/background-gradient,
							left-aligned throughout, signature navy on Qualified Leads + Revenue. -->
						<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-3" id="funnelViz">
							<div id="funnelZoneTraffic">
								<div class="funnel-zone-label">Traffic</div>
								<div class="funnel-zone-value" id="funnelTrafficValue"><?php echo esc_html( number_format_i18n( $itoi_funnel_traffic ) ); ?></div>
								<div class="funnel-zone-caption">monthly visitors</div>
							</div>
							<div id="funnelZoneLeads">
								<div class="funnel-zone-label">Qualified Leads</div>
								<div class="funnel-zone-value funnel-zone-value--amber" id="funnelLeadsValue">&mdash;</div>
								<div class="funnel-zone-caption">identified prospects</div>
							</div>
							<div id="funnelZoneRevenue">
								<div class="funnel-zone-label">Revenue</div>
								<div class="funnel-zone-value funnel-zone-value--amber" id="funnelRevenueValue">&mdash;</div>
								<div class="funnel-zone-caption">estimated impact</div>
							</div>
						</div>

						<?php if ( $itoi_funnel_disclaimer ) : ?>
							<p class="mx-auto mt-4 max-w-[720px] text-center text-[12.5px] text-text-muted" id="funnelDisclaimer"><?php echo esc_html( $itoi_funnel_disclaimer ); ?></p>
						<?php endif; ?>

					<?php elseif ( 'hospitality' === $itoi_slug ) : ?>

						<!-- Click-through guest-journey timeline. Stages toggle
							independently (any order, any combination) — clicking an
							active stage again turns it off. Completeness = (active
							stage count / total) * 100%, not tied to click order. -->
						<div id="hospitalityTimeline" data-stage-count="<?php echo esc_attr( count( $itoi_hosp_stages ) ); ?>">
							<div class="mb-5 flex flex-wrap gap-2.5" id="hospitalityStageRow">
								<?php foreach ( $itoi_hosp_stages as $itoi_stage_index => $itoi_stage ) : ?>
									<button
										type="button"
										class="hospitality-stage-pill rounded-full border-[1.5px] border-line px-5 py-2.5 text-[13.5px] font-bold text-text-muted transition-colors"
										data-stage-index="<?php echo esc_attr( $itoi_stage_index ); ?>"
										aria-pressed="false"
									>
										<?php echo esc_html( $itoi_stage['stage_label'] ); ?>
									</button>
								<?php endforeach; ?>
							</div>

							<div class="mb-6 flex flex-col gap-2.5" id="hospitalityRevealStack">
								<?php foreach ( $itoi_hosp_stages as $itoi_stage_index => $itoi_stage ) : ?>
									<div
										class="hospitality-reveal-card hidden rounded-xl border border-line bg-hero-bg px-5 py-3.5"
										data-stage-index="<?php echo esc_attr( $itoi_stage_index ); ?>"
									>
										<span class="text-[13px] font-bold text-signature-dim"><?php echo esc_html( $itoi_stage['stage_label'] ); ?>:</span>
										<span class="text-[13.5px] text-text-muted"><?php echo esc_html( $itoi_stage['reveal_text'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="mb-2 flex items-center justify-between">
								<span class="text-[13.5px] font-bold"><?php echo esc_html( $itoi_hosp_completeness ); ?></span>
								<span class="text-[13.5px] font-bold text-signature-dim" id="hospitalityCompletenessPct">0%</span>
							</div>
							<div class="mb-4 h-2 overflow-hidden rounded-full bg-hero-bg">
								<div class="h-full rounded-full bg-signature transition-[width] duration-300 ease-out" id="hospitalityCompletenessBar" style="width:0%"></div>
							</div>

							<button type="button" class="text-[13px] font-bold text-text-muted underline" id="hospitalityReset"><?php echo esc_html( $itoi_hosp_reset ); ?></button>

							<?php if ( $itoi_funnel_disclaimer ) : ?>
								<p class="mt-4 text-[12.5px] text-text-muted"><?php echo esc_html( $itoi_funnel_disclaimer ); ?></p>
							<?php endif; ?>
						</div>

					<?php elseif ( 'banking-finance' === $itoi_slug ) : ?>

						<!-- Access scenario simulator. Clicking a scenario card
							replaces whatever result is currently shown (never
							stacks) with a short animated response — icon state
							change + a fading-in log line. The log line's time
							is the live current time, appended client-side, not
							a stored/fabricated timestamp (see NOTES.md). -->
						<div id="bankingSimulator">
							<div class="mb-6 grid grid-cols-1 gap-3 min-[720px]:grid-cols-3">
								<?php foreach ( $itoi_bank_scenarios as $itoi_scenario_index => $itoi_scenario ) : ?>
									<button
										type="button"
										class="banking-scenario-card rounded-xl border-[1.5px] border-line px-5 py-4 text-left transition-colors"
										data-scenario-index="<?php echo esc_attr( $itoi_scenario_index ); ?>"
										data-result-type="<?php echo esc_attr( $itoi_scenario['result_type'] ); ?>"
										data-log-message="<?php echo esc_attr( $itoi_scenario['log_message'] ); ?>"
									>
										<span class="block text-[14px] font-bold"><?php echo esc_html( $itoi_scenario['scenario_label'] ); ?></span>
										<span class="mt-1 block text-[12.5px] text-text-muted"><?php echo esc_html( $itoi_scenario['scenario_description'] ); ?></span>
									</button>
								<?php endforeach; ?>
							</div>

							<div class="hidden rounded-xl border border-line bg-hero-bg px-6 py-6" id="bankingResultPanel">
								<div class="flex items-center gap-4">
									<span class="banking-result-icon flex h-12 w-12 flex-none items-center justify-center rounded-full text-2xl" id="bankingResultIcon" aria-hidden="true"></span>
									<div>
										<div class="text-[14px] font-bold" id="bankingResultLabel"></div>
										<div class="mt-1 font-mono text-[13px] text-text-muted" id="bankingResultLog"></div>
									</div>
								</div>
							</div>

							<?php if ( $itoi_funnel_disclaimer ) : ?>
								<p class="mt-4 text-[12.5px] text-text-muted"><?php echo esc_html( $itoi_funnel_disclaimer ); ?></p>
							<?php endif; ?>
						</div>

					<?php elseif ( 'government-councils' === $itoi_slug ) : ?>

						<!-- Live funding-allocation chart. Checkboxes toggle
							independently (any combination) — a category's bar
							height = baseline + sum of every currently-checked
							box's impact on that category (each checkbox's
							"impact_map" ACF field), capped at 100. Muted
							teal-gray = baseline, signature navy = boosted right now. -->
						<div id="governmentFundingChart">
							<div class="mb-7 grid grid-cols-1 gap-2.5 min-[640px]:grid-cols-2" id="governmentCheckboxRow">
								<?php foreach ( $itoi_gov_checkboxes as $itoi_checkbox_index => $itoi_checkbox ) : ?>
									<label class="government-checkbox-label flex cursor-pointer items-center gap-2.5 rounded-xl border-[1.5px] border-line px-4 py-3 text-[13.5px] font-bold transition-colors">
										<input
											type="checkbox"
											class="government-checkbox h-4 w-4 accent-signature"
											data-checkbox-index="<?php echo esc_attr( $itoi_checkbox_index ); ?>"
											data-impact-map="<?php echo esc_attr( $itoi_checkbox['impact_map'] ); ?>"
										>
										<?php echo esc_html( $itoi_checkbox['checkbox_label'] ); ?>
									</label>
								<?php endforeach; ?>
							</div>

							<div class="flex items-end gap-4 min-[640px]:gap-6" id="governmentChartBars" style="height:160px">
								<?php foreach ( $itoi_gov_categories as $itoi_category_index => $itoi_category ) : ?>
									<div class="government-chart-bar-wrap flex flex-1 items-end justify-center" data-category-index="<?php echo esc_attr( $itoi_category_index ); ?>" data-baseline="<?php echo esc_attr( $itoi_category['baseline_value'] ); ?>">
										<div class="government-chart-bar w-full rounded-t-md transition-[height] duration-300 ease-out" style="height:<?php echo esc_attr( round( $itoi_category['baseline_value'] / 100 * 160 ) ); ?>px"></div>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="mt-2 flex gap-4 min-[640px]:gap-6">
								<?php foreach ( $itoi_gov_categories as $itoi_category ) : ?>
									<div class="flex-1 text-center text-[12px] font-bold text-text-muted"><?php echo esc_html( $itoi_category['category_label'] ); ?></div>
								<?php endforeach; ?>
							</div>

							<?php if ( $itoi_funnel_disclaimer ) : ?>
								<p class="mt-6 text-[12.5px] text-text-muted"><?php echo esc_html( $itoi_funnel_disclaimer ); ?></p>
							<?php endif; ?>
						</div>

					<?php elseif ( 'logistics-warehousing' === $itoi_slug ) : ?>

						<!-- Drag-to-compare incident timeline. "Without ITOI" is the
							always-visible base layer; "With ITOI" is an overlay
							clipped to the handle's position — the classic
							before/after image-comparison-slider pattern, built with
							two DOM timelines and a JS-measured width clip instead of
							two images (see NOTES.md). Both timelines share the same
							4 event labels; only each event's horizontal position and
							the Incident→Discovered gap label differ between them. -->
						<div id="logisticsComparison">
							<div class="mb-3 flex items-center justify-between text-[12px] font-bold uppercase tracking-wide text-text-muted">
								<span><?php echo esc_html( $itoi_log_without_label ); ?></span>
								<span class="text-signature-dim"><?php echo esc_html( $itoi_log_with_label ); ?></span>
							</div>

							<div class="relative h-[150px] select-none overflow-hidden rounded-xl bg-hero-bg" id="logisticsCompareStage">
								<div class="absolute inset-0" id="logisticsWithoutLayer">
									<div class="logistics-track-area">
										<div class="logistics-timeline-track"></div>
										<?php if ( $itoi_log_gap_without ) : ?>
											<div class="logistics-gap-label" style="left:<?php echo esc_attr( $itoi_log_gap_pos_without ); ?>%;transform:translateX(<?php echo esc_attr( $itoi_log_gap_anchor_without ); ?>)"><?php echo esc_html( $itoi_log_gap_without ); ?></div>
										<?php endif; ?>
										<?php foreach ( $itoi_log_events as $itoi_event_index => $itoi_event ) : ?>
											<?php
											$itoi_pos         = (float) $itoi_event['without_position'];
											$itoi_anchor      = $itoi_pos <= 5 ? '0%' : ( $itoi_pos >= 95 ? '-100%' : '-50%' );
											$itoi_start_class = 0 === $itoi_event_index ? ' logistics-event--start' : '';
											?>
											<div class="logistics-event<?php echo esc_attr( $itoi_start_class ); ?>" style="left:<?php echo esc_attr( $itoi_pos ); ?>%">
												<span class="logistics-event-dot"></span>
												<span class="logistics-event-label" style="transform:translateX(<?php echo esc_attr( $itoi_anchor ); ?>)"><?php echo esc_html( $itoi_event['event_label'] ); ?></span>
											</div>
										<?php endforeach; ?>
									</div>
								</div>

								<div class="absolute inset-y-0 left-0 overflow-hidden" id="logisticsWithClip" style="width:50%">
									<div class="absolute inset-y-0 left-0" id="logisticsWithLayer" style="width:1200px">
										<div class="logistics-track-area">
											<div class="logistics-timeline-track logistics-timeline-track--with"></div>
											<?php if ( $itoi_log_gap_with ) : ?>
												<div class="logistics-gap-label logistics-gap-label--with" style="left:<?php echo esc_attr( $itoi_log_gap_pos_with ); ?>%;transform:translateX(<?php echo esc_attr( $itoi_log_gap_anchor_with ); ?>)"><?php echo esc_html( $itoi_log_gap_with ); ?></div>
											<?php endif; ?>
											<?php foreach ( $itoi_log_events as $itoi_event_index => $itoi_event ) : ?>
												<?php
												// Event 0 ("Incident occurs") is the same real-world moment on
												// both timelines — always at the same position, so it's only
												// rendered once (on the "without" layer below) to avoid two
												// identical labels stacking exactly on top of each other.
												if ( 0 === $itoi_event_index ) {
													continue;
												}
												$itoi_pos    = (float) $itoi_event['with_position'];
												$itoi_anchor = $itoi_pos <= 5 ? '0%' : ( $itoi_pos >= 95 ? '-100%' : '-50%' );
												?>
												<div class="logistics-event logistics-event--with" style="left:<?php echo esc_attr( $itoi_pos ); ?>%">
													<span class="logistics-event-dot logistics-event-dot--with"></span>
													<span class="logistics-event-label logistics-event-label--with" style="transform:translateX(<?php echo esc_attr( $itoi_anchor ); ?>)"><?php echo esc_html( $itoi_event['event_label'] ); ?></span>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>

								<div
									class="logistics-handle absolute top-0 bottom-0 flex items-center justify-center"
									id="logisticsHandle"
									role="slider"
									tabindex="0"
									aria-label="Drag to compare without and with ITOI"
									aria-valuemin="0"
									aria-valuemax="100"
									aria-valuenow="50"
									style="left:50%"
								>
									<span class="logistics-handle-grip" aria-hidden="true">&#8646;</span>
								</div>
							</div>

							<?php if ( $itoi_funnel_disclaimer ) : ?>
								<p class="mt-4 text-[12.5px] text-text-muted"><?php echo esc_html( $itoi_funnel_disclaimer ); ?></p>
							<?php endif; ?>
						</div>

					<?php elseif ( 'stadiums-events' === $itoi_slug ) : ?>

						<!-- Live density visualization. Reuses the site's signature-
							navy-bordered "detection marker" visual language (see the
							hero's detection boxes: rounded-[3px] border-[1.5px]
							border-signature) scaled down for density display.
							Marker positions are pre-generated once per zone (a
							stable pool sized to that zone's share of the total
							marker cap) and JS reveals a prefix of that pool as
							the slider increases — markers never re-shuffle
							position when the count changes, only appear or
							disappear (see NOTES.md). -->
						<div id="stadiumDensity" data-min="<?php echo esc_attr( $itoi_stadium_min ); ?>" data-max="<?php echo esc_attr( $itoi_stadium_max ); ?>" data-max-markers="<?php echo esc_attr( $itoi_stadium_max_marker ); ?>">
							<div class="mb-5 flex flex-wrap items-center gap-3">
								<label for="stadiumSlider" class="text-[14px] font-bold">Event size</label>
								<input type="range" id="stadiumSlider" min="<?php echo esc_attr( $itoi_stadium_min ); ?>" max="<?php echo esc_attr( $itoi_stadium_max ); ?>" step="1000" value="<?php echo esc_attr( $itoi_stadium_default ); ?>" class="h-1.5 w-full max-w-[280px] flex-1 accent-signature">
								<span class="text-[15px] font-bold text-signature-dim" id="stadiumSliderValue"><?php echo esc_html( number_format_i18n( $itoi_stadium_default ) ); ?></span>
								<span class="text-[12px] text-text-muted">attendees</span>
							</div>

							<div class="grid grid-cols-2 gap-3" id="stadiumZoneGrid">
								<?php foreach ( $itoi_stadium_zones as $itoi_zone_index => $itoi_zone ) : ?>
									<div class="stadium-zone relative h-[110px] overflow-hidden rounded-xl border border-line bg-hero-bg" data-zone-index="<?php echo esc_attr( $itoi_zone_index ); ?>" data-zone-weight="<?php echo esc_attr( $itoi_zone['zone_weight'] ); ?>">
										<span class="stadium-zone-label"><?php echo esc_html( $itoi_zone['zone_label'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>

							<?php if ( $itoi_funnel_disclaimer ) : ?>
								<p class="mt-4 text-[12.5px] text-text-muted"><?php echo esc_html( $itoi_funnel_disclaimer ); ?></p>
							<?php endif; ?>
						</div>

					<?php elseif ( 'casinos-gaming' === $itoi_slug ) : ?>

						<!-- Illustrative floor-plan zone selector. Clicking a zone
							highlights it (signature navy) and swaps in its description below —
							only one zone's description shows at a time (replaced,
							not stacked). Zone position on the grid is fixed by row
							order (see the ACF field's own instructions), matching
							the order the spec lists them in. -->
						<div id="casinoFloorMap">
							<div class="casino-floor-grid mb-5 grid gap-2.5" id="casinoZoneGrid">
								<?php foreach ( $itoi_casino_zones as $itoi_zone_index => $itoi_zone ) : ?>
									<?php
									$itoi_zone_solution_id    = $itoi_zone['zone_solution'];
									$itoi_zone_solution_url   = $itoi_zone_solution_id ? get_permalink( $itoi_zone_solution_id ) : '';
									$itoi_zone_solution_label = $itoi_zone_solution_id ? get_the_title( $itoi_zone_solution_id ) : '';
									// Position classes written as a literal lookup (not string-
									// concatenated) so Tailwind's content scanner — which matches
									// class names as literal text, not PHP output — can see them
									// and won't purge the grid-area rules as unused.
									$itoi_zone_position_classes = array( 'casino-zone--0', 'casino-zone--1', 'casino-zone--2', 'casino-zone--3' );
									$itoi_zone_position_class   = $itoi_zone_position_classes[ $itoi_zone_index ] ?? '';
									?>
									<button
										type="button"
										class="casino-zone <?php echo esc_attr( $itoi_zone_position_class ); ?> rounded-xl border-[1.5px] border-line bg-hero-bg px-4 py-4 text-left transition-colors"
										data-zone-index="<?php echo esc_attr( $itoi_zone_index ); ?>"
										data-label="<?php echo esc_attr( $itoi_zone['zone_label'] ); ?>"
										data-description="<?php echo esc_attr( $itoi_zone['zone_description'] ); ?>"
										data-solution-url="<?php echo esc_url( $itoi_zone_solution_url ); ?>"
										data-solution-label="<?php echo esc_attr( $itoi_zone_solution_label ); ?>"
										aria-pressed="false"
									>
										<span class="text-[13.5px] font-bold"><?php echo esc_html( $itoi_zone['zone_label'] ); ?></span>
									</button>
								<?php endforeach; ?>
							</div>

							<div class="hidden rounded-xl border border-line bg-hero-bg px-6 py-5" id="casinoDescriptionPanel">
								<div class="text-[14px] font-bold" id="casinoDescriptionLabel"></div>
								<p class="mt-1.5 text-[13.5px] text-text-muted" id="casinoDescriptionText"></p>
								<a href="#" class="mt-2 hidden text-[13px] font-bold text-signature-dim underline" id="casinoDescriptionSolutionLink"></a>
							</div>

							<?php if ( $itoi_funnel_disclaimer ) : ?>
								<p class="mt-4 text-[12.5px] text-text-muted"><?php echo esc_html( $itoi_funnel_disclaimer ); ?></p>
							<?php endif; ?>
						</div>

					<?php endif; ?>
				</div>
			</div>
		</section>

	<?php else : ?>

		<section class="aurora-bg-light border-b border-line bg-hero-bg px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px]">
			<div class="mx-auto grid max-w-[1280px] gap-10 min-[980px]:grid-cols-[1.1fr_1fr] min-[980px]:items-center">
				<div>
					<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Industry</div>
					<h1 class="max-w-[16ch] <?php echo $itoi_show_funnel ? 'min-[980px]:max-w-[26ch]' : ''; ?>"><?php echo esc_html( $itoi_hero_headline ); ?></h1>
					<?php if ( $itoi_hero_desc ) : ?>
						<p class="mt-4 max-w-[46ch] text-[17px] text-text-muted"><?php echo esc_html( $itoi_hero_desc ); ?></p>
					<?php endif; ?>
					<div class="mt-7 flex flex-wrap gap-2.5">
						<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover">Get demo</a>
						<a href="<?php echo esc_url( home_url( '/industries/' ) ); ?>" class="rounded-full border-[1.5px] border-ink bg-white px-[22px] py-[11px] text-sm font-bold hover:bg-hero-bg">All industries</a>
					</div>
				</div>
				<div class="relative aspect-[4/3] w-full overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
					<?php
					$itoi_hero_media = itoi_media_cover( $itoi_hero, $itoi_hero_video, $itoi_hero_alt, 'absolute inset-0 h-full w-full object-cover' );
					?>
					<?php if ( $itoi_hero_media ) : ?>
						<?php echo $itoi_hero_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
					<?php else : ?>
						<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_name ); ?> (TODO)</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ( $itoi_show_funnel ) : ?>
			<section class="aurora-bg-light border-b border-line bg-hero-bg px-8 py-section-md" id="funnelSection" data-conversion-rate="<?php echo esc_attr( $itoi_funnel_rate ?? '' ); ?>" data-value-per-lead="<?php echo esc_attr( $itoi_funnel_value ?? '' ); ?>">
				<div class="mx-auto max-w-[1280px]">
					<!-- No headline here by design — this is the direct payoff of
						the hero's claim above, not a second hero moment. -->

					<!-- Input — no-detect-reveal: interactive control, never the passive
						scroll-reveal treatment (same rule as the photo-hero branch's
						interaction card above; this branch is currently unreachable
						since every industry has both hero_image + funnel_enabled set,
						but was a landmine carrying the exact recurring bracket bug —
						see NOTES.md). Wave 6 (liquid glass, light variant): the
						section background got aurora-bg-light for consistency with
						the live photo-hero branch above, but this card stays plain
						bg-white, untouched — it's the funnel's own interactive
						control, explicitly excluded from the glass/aurora treatment
						per the wave-6 brief, same as the live branch's
						.glass-panel-light card is left alone. -->
					<div class="no-detect-reveal mx-auto mb-8 flex max-w-[720px] flex-wrap items-center justify-center gap-4 rounded-2xl border border-line bg-white px-6 py-5">
						<label for="funnelTrafficSlider" class="text-[13.5px] font-bold">Monthly foot traffic</label>
						<input type="range" id="funnelTrafficSlider" min="1000" max="50000" step="500" value="<?php echo esc_attr( $itoi_funnel_traffic ); ?>" class="h-1.5 w-full max-w-[260px] flex-1 accent-signature">
						<input type="number" id="funnelTrafficInput" min="0" step="500" value="<?php echo esc_attr( $itoi_funnel_traffic ); ?>" aria-label="Monthly foot traffic (exact number)" class="w-[110px] rounded-lg border border-line px-3 py-2 text-sm font-bold">
					</div>

					<!-- Funnel visualization: one continuous card, three zones, not
						three separate cards — no border/gap between zones, a single
						background gradient and connecting chevrons carry the flow. -->
					<div class="funnel-viz mx-auto flex max-w-[1100px] flex-col overflow-hidden rounded-2xl border border-line min-[900px]:flex-row" id="funnelViz">
						<div class="funnel-zone funnel-zone--traffic" id="funnelZoneTraffic">
							<div class="funnel-zone-label">Traffic</div>
							<div class="funnel-markers" id="funnelMarkersTraffic" aria-hidden="true"></div>
							<div class="funnel-zone-value" id="funnelTrafficValue"><?php echo esc_html( number_format_i18n( $itoi_funnel_traffic ) ); ?></div>
							<div class="funnel-zone-caption">monthly visitors</div>
						</div>
						<div class="funnel-chevron" aria-hidden="true">&rarr;</div>
						<div class="funnel-zone funnel-zone--leads" id="funnelZoneLeads">
							<div class="funnel-zone-label">Qualified Leads</div>
							<div class="funnel-markers" id="funnelMarkersLeads" aria-hidden="true"></div>
							<div class="funnel-zone-value" id="funnelLeadsValue">&mdash;</div>
							<div class="funnel-zone-caption">identified prospects</div>
						</div>
						<div class="funnel-chevron" aria-hidden="true">&rarr;</div>
						<div class="funnel-zone funnel-zone--revenue" id="funnelZoneRevenue">
							<div class="funnel-zone-label">Revenue</div>
							<div class="funnel-zone-value funnel-zone-value--revenue" id="funnelRevenueValue">&mdash;</div>
							<div class="funnel-zone-caption">estimated impact</div>
						</div>
					</div>

					<?php if ( $itoi_funnel_disclaimer ) : ?>
						<p class="mx-auto mt-4 max-w-[720px] text-center text-[12.5px] text-text-muted" id="funnelDisclaimer"><?php echo esc_html( $itoi_funnel_disclaimer ); ?></p>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

	<?php endif; ?>

	<?php
	// ================= LONG-FORM PAGE (below hero/funnel) =================
	// New below-the-fold structure — "Industry — Long-form Page" ACF field
	// group (acf-json/group_cb9a360b89a5.json), added 2026-07-21. Structurally
	// available on every industry (same precedent as the Hero Interaction
	// group) but built and enabled on Retail only this session — every other
	// industry leaves `longform_enabled` off, so this whole block (and the
	// sub-nav) renders nothing there and the page is byte-for-byte unchanged.
	// See NOTES.md for the full field list and flagged build decisions.
	$itoi_show_longform = (bool) get_field( 'longform_enabled' );

	if ( $itoi_show_longform ) :
		$itoi_lf_overview_headline      = get_field( 'overview_headline' );
		$itoi_lf_overview_sub           = get_field( 'overview_subheadline' );
		$itoi_lf_overview_visual_id     = get_field( 'overview_visual' );
		$itoi_lf_overview_visual_cap    = get_field( 'overview_visual_caption' );
		$itoi_lf_overview_video         = get_field( 'overview_video' );
		$itoi_lf_overview_process_steps = get_field( 'overview_process_diagram_steps' );
		$itoi_lf_overview_process_style = itoi_or( get_field( 'overview_process_diagram_style' ), 'lines' );
		$itoi_lf_feature_rows           = itoi_or( get_field( 'overview_feature_rows' ), array() );

		// Migrated 2026-07-30 (see NOTES.md): use cases are their own `use_case`
		// CPT now (itoi_get_industry_use_cases() in inc/use-cases.php), not a
		// repeater on this post — filter the sitewide list down to this
		// industry rather than reading a field that no longer exists here.
		$itoi_lf_use_cases_heading = get_field( 'use_cases_heading' );
		$itoi_lf_industry_id       = get_the_ID();
		$itoi_lf_use_cases         = array_values(
			array_filter(
				itoi_get_industry_use_cases(),
				function ( $itoi_uc ) use ( $itoi_lf_industry_id ) {
					return $itoi_uc['industry_id'] === $itoi_lf_industry_id;
				}
			)
		);

		$itoi_lf_why_heading = get_field( 'why_heading' );
		$itoi_lf_why_items   = itoi_or( get_field( 'why_items' ), array() );

		$itoi_lf_solutions_heading = get_field( 'solutions_heading' );
		$itoi_lf_solutions         = itoi_or( get_field( 'longform_solutions' ), array() );

		$itoi_lf_customers_heading   = get_field( 'customers_heading' );
		$itoi_lf_spotlight_client_id = get_field( 'spotlight_client' );
		$itoi_lf_spotlight_photo_id  = get_field( 'spotlight_photo' );
		$itoi_lf_spotlight_video     = get_field( 'spotlight_video' );
		// TEMPORARY: while spotlight_photo is a stock stand-in (2026-07-24
		// site-wide stock-sourcing follow-up, see NOTES.md), disclose that
		// visibly — same pattern as single-case_study.php's hero_image_is_stock,
		// since this photo is presented next to a specific real client's name.
		// Only applies to the photo, never to an uploaded spotlight_video.
		$itoi_lf_spotlight_is_stock      = (bool) get_field( 'spotlight_photo_is_stock' );
		$itoi_lf_logo_groups             = itoi_or( get_field( 'logo_strip_groups' ), array() );
		$itoi_lf_customers_empty_message = get_field( 'customers_empty_message' );

		// Resolution + case-study lookup extracted 2026-07-30 (see NOTES.md)
		// into inc/customers-section.php — shared with the homepage's own
		// Customers section. Resolved once here (not the render-only
		// wrapper) since the sub-nav below needs to know whether this
		// section will show *before* the section itself renders.
		$itoi_lf_customers_data         = itoi_get_customers_section_data(
			array(
				'heading'             => $itoi_lf_customers_heading,
				'spotlight_client_id' => $itoi_lf_spotlight_client_id,
				'spotlight_photo_id'  => $itoi_lf_spotlight_photo_id,
				'spotlight_video'     => $itoi_lf_spotlight_video,
				'spotlight_is_stock'  => $itoi_lf_spotlight_is_stock,
				'logo_strip_groups'   => $itoi_lf_logo_groups,
				'empty_message'       => $itoi_lf_customers_empty_message,
			)
		);
		$itoi_lf_show_customers_section = $itoi_lf_customers_data['show'];
		?>

		<!-- ================= LONG-FORM SUB-NAV =================
			Sticky, sits below the main header. Top offset updated
			2026-08-03 (see NOTES.md) when header.php's ticker+nav became
			fixed on every page (was previously sticky-in-flow here,
			hence the old 72px value matching just the nav pill's own
			height) — this now has to clear the FULL fixed stack: ticker
			(h-[38px]) + header's own mt-3/mt-4 margin (12px/16px) + the
			nav pill (h-[72px]), plus an 8px gap so it doesn't touch the
			pill's edge. 38+12+72+8=130 / 38+16+72+8=134.
			Scrollspy (IntersectionObserver, not scroll-position math) in
			assets/js/main.js initLongformScrollspy().

			2026-08-04 (see NOTES.md): once this sub-nav goes sticky, the
			fixed main nav (#siteHeaderFixed, header.php) now hides itself
			rather than sitting stacked on top of it — initLongformHeaderHide()
			in main.js watches the 1px sentinel immediately below via
			IntersectionObserver, reading this nav's own computed `top` so the
			two never drift out of sync with each other. -->
		<div id="longformSubnavSentinel" aria-hidden="true"></div>
		<nav class="longform-subnav sticky top-[130px] z-[45] border-b border-line bg-white min-[640px]:top-[134px]" aria-label="<?php echo esc_attr( $itoi_name ); ?> page sections">
			<div class="no-scrollbar mx-auto flex max-w-[1280px] items-center justify-center gap-1 overflow-x-auto px-8 min-[640px]:gap-3" id="longformSubnav">
				<?php
				$itoi_lf_nav_items = array(
					'overview'  => 'Overview',
					'use-cases' => 'Use Cases',
					'why-itoi'  => 'Why ITOI',
					'solutions' => 'Solutions',
				);
				// Omitted for any industry where Customers has no real
				// content at all — a nav link to an empty section is
				// broken UX (see NOTES.md, e.g. Casinos & Gaming).
				if ( $itoi_lf_show_customers_section ) {
					$itoi_lf_nav_items['customers'] = 'Customers';
				}
				foreach ( $itoi_lf_nav_items as $itoi_nav_id => $itoi_nav_label ) :
					?>
					<a href="#<?php echo esc_attr( $itoi_nav_id ); ?>" data-target="<?php echo esc_attr( $itoi_nav_id ); ?>" class="longform-subnav-link whitespace-nowrap border-b-2 border-transparent px-3 py-4 text-[13.5px] font-bold text-text-muted transition-colors hover:text-ink min-[640px]:px-4"><?php echo esc_html( $itoi_nav_label ); ?></a>
				<?php endforeach; ?>
			</div>
		</nav>

		<!-- ================= OVERVIEW ================= -->
		<!-- Headline/sub restructured 2026-07-31 (see NOTES.md) to two-column
			(heading left, body right) with an optional CAPTURE → ANALYSE →
			ACT-style diagram (itoi_render_process_diagram(),
			inc/process-diagram.php — shared with single-solution.php's own
			Overview section) below it. The hero visual and
			overview_feature_rows further down are unchanged, just now
			sitting below the diagram instead of directly below the
			headline. -->
		<section id="overview" class="aurora-bg-light scroll-mt-[128px] border-b border-line px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<?php if ( $itoi_lf_overview_headline || $itoi_lf_overview_sub ) : ?>
					<div class="grid gap-8 min-[980px]:grid-cols-[1.1fr_1fr] min-[980px]:gap-14">
						<?php if ( $itoi_lf_overview_headline ) : ?>
							<h2 class="max-w-[24ch]"><?php echo esc_html( $itoi_lf_overview_headline ); ?></h2>
						<?php endif; ?>
						<?php if ( $itoi_lf_overview_sub ) : ?>
							<p class="text-[16px] text-text-muted"><?php echo esc_html( $itoi_lf_overview_sub ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php itoi_render_process_diagram( $itoi_lf_overview_process_steps, $itoi_lf_overview_process_style ); ?>

				<?php
				$itoi_lf_overview_visual_url = $itoi_lf_overview_visual_id ? wp_get_attachment_image_url( $itoi_lf_overview_visual_id, 'large' ) : '';
				$itoi_lf_overview_media      = itoi_media_cover( $itoi_lf_overview_visual_url, $itoi_lf_overview_video, itoi_or( $itoi_lf_overview_visual_cap, $itoi_name ), 'absolute inset-0 h-full w-full object-cover', 'loading="lazy"' );
				?>
				<div class="relative mt-10 aspect-[16/9] w-full overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
					<?php if ( $itoi_lf_overview_media ) : ?>
						<?php echo $itoi_lf_overview_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
					<?php else : ?>
						<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]"><?php echo esc_html( $itoi_lf_overview_visual_cap ); ?> (TODO)</div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $itoi_lf_feature_rows ) ) : ?>
					<div class="mt-10 flex flex-col gap-10 min-[980px]:mt-14 min-[980px]:gap-14">
						<?php
						foreach ( $itoi_lf_feature_rows as $itoi_row_index => $itoi_row ) :
							$itoi_row_image_id  = $itoi_row['image'];
							$itoi_row_image_url = $itoi_row_image_id ? wp_get_attachment_image_url( $itoi_row_image_id, 'large' ) : '';
							$itoi_row_media     = itoi_media_cover( $itoi_row_image_url, $itoi_row['video'] ?? null, itoi_or( $itoi_row['image_caption'], $itoi_row['headline'] ), 'absolute inset-0 h-full w-full object-cover', 'loading="lazy"' );

							ob_start();
							?>
							<div>
								<?php if ( ! empty( $itoi_row['headline'] ) ) : ?>
									<h3 class="max-w-[20ch] text-[22px] min-[980px]:text-[26px]"><?php echo esc_html( $itoi_row['headline'] ); ?></h3>
								<?php endif; ?>
								<?php if ( ! empty( $itoi_row['body_paragraph_1'] ) ) : ?>
									<p class="mt-4 text-[15px] text-text-muted"><?php echo esc_html( $itoi_row['body_paragraph_1'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $itoi_row['body_paragraph_2'] ) ) : ?>
									<p class="mt-3 text-[15px] text-text-muted"><?php echo esc_html( $itoi_row['body_paragraph_2'] ); ?></p>
								<?php endif; ?>
							</div>
							<?php
							$itoi_row_text_html = ob_get_clean();

							ob_start();
							?>
							<div class="relative aspect-[4/3] w-full overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
								<?php if ( $itoi_row_media ) : ?>
									<?php echo $itoi_row_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
								<?php else : ?>
									<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]"><?php echo esc_html( $itoi_row['image_caption'] ); ?> (TODO)</div>
								<?php endif; ?>
							</div>
							<?php
							$itoi_row_visual_html = ob_get_clean();
							?>
							<div class="grid grid-cols-1 items-center gap-8 min-[980px]:grid-cols-2 min-[980px]:gap-14">
								<?php echo ( 1 === $itoi_row_index % 2 ) ? $itoi_row_visual_html . $itoi_row_text_html : $itoi_row_text_html . $itoi_row_visual_html; // phpcs:ignore -- both halves are ob_get_clean() of already-esc_html()'d/itoi_media_cover()-escaped markup above. ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- ================= USE CASES ================= -->
		<section id="use-cases" class="aurora-bg-light scroll-mt-[128px] border-b border-line bg-hero-bg px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-8 text-2xl"><?php echo esc_html( $itoi_lf_use_cases_heading ); ?></h2>
				<?php if ( ! empty( $itoi_lf_use_cases ) ) : ?>
					<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
						<?php
						foreach ( $itoi_lf_use_cases as $itoi_uc ) :
							$itoi_uc_image_url = $itoi_uc['image_id'] ? wp_get_attachment_image_url( $itoi_uc['image_id'], 'medium_large' ) : '';
							// 2026-08-05 (external improvement plan Phase 5.7, axe
							// image-redundant-alt): this card's own visible label
							// (rendered just below, same $itoi_uc['label']) already
							// says exactly what the alt text was repeating — empty
							// alt marks the image decorative here instead, so a
							// screen reader doesn't announce the same short phrase
							// twice per card. Only this call site changed; other
							// itoi_media_cover() callers keep real alt text where the
							// image is the only source of that information.
							$itoi_uc_media = itoi_media_cover( $itoi_uc_image_url, $itoi_uc['video'], '', 'absolute inset-0 h-full w-full object-cover', 'loading="lazy"' );
							?>
							<a href="<?php echo esc_url( $itoi_uc['solution_url'] ); ?>" class="use-case-card glass-element-light group block overflow-hidden rounded-2xl transition-all hover:-translate-y-[3px]">
								<div class="relative aspect-[16/10] w-full overflow-hidden bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
									<?php if ( $itoi_uc_media ) : ?>
										<?php echo $itoi_uc_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
									<?php else : ?>
										<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_uc['label'] ); ?> (TODO)</div>
									<?php endif; ?>
								</div>
								<div class="relative flex items-center justify-between gap-3 px-5 py-4">
									<span class="text-[14px] font-bold"><?php echo esc_html( $itoi_uc['label'] ); ?></span>
									<span class="text-base text-text-muted transition-transform group-hover:translate-x-0.5" aria-hidden="true">&rarr;</span>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- ================= WHY ITOI ================= -->
		<!-- wave 4 (liquid glass, see NOTES.md): added aurora-bg to this
			section (already bg-teal-900, no color-token fixes needed here
			unlike the other 2 wave-4 sections). Each of the 6 why_items
			previously sat directly on the section background with no card
			wrapper at all — added one (padding/border/radius) since
			"convert cards to glass" needs an actual card, then applied
			.why-benefit-card-glass to it. Icon/headline/body colors
			(already white/white-70 — this section was already dark)
			unchanged. -->
		<section id="why-itoi" class="aurora-bg scroll-mt-[128px] bg-teal-900 px-8 py-section-lg">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-10 text-center text-[clamp(26px,3vw,38px)] text-white"><?php echo esc_html( $itoi_lf_why_heading ); ?></h2>
				<?php if ( ! empty( $itoi_lf_why_items ) ) : ?>
					<div class="grid grid-cols-1 gap-8 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3 min-[980px]:gap-10">
						<?php foreach ( $itoi_lf_why_items as $itoi_why ) : ?>
							<div class="why-benefit-card-glass p-6">
								<?php itoi_longform_icon( $itoi_why['icon'] ); ?>
								<h3 class="mt-4 text-white"><?php echo esc_html( $itoi_why['headline'] ); ?></h3>
								<?php if ( ! empty( $itoi_why['body'] ) ) : ?>
									<p class="mt-2 text-[14.5px] text-white/80"><?php echo esc_html( $itoi_why['body'] ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- ================= SOLUTIONS =================
			Matches archive-solution.php's tile markup exactly (no shared
			component was extracted this session — see NOTES.md — so this
			duplicates that file's classes rather than diverging from them). -->
		<section id="solutions" class="aurora-bg-light scroll-mt-[128px] border-b border-line px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
					<h2 class="text-2xl"><?php echo esc_html( $itoi_lf_solutions_heading ); ?></h2>
					<a href="<?php echo esc_url( home_url( '/solutions/' ) ); ?>" class="w-fit whitespace-nowrap text-sm font-bold underline">View all solutions &rarr;</a>
				</div>
				<?php if ( ! empty( $itoi_lf_solutions ) ) : ?>
					<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-4">
						<?php
						foreach ( $itoi_lf_solutions as $itoi_sid ) :
							if ( 'publish' !== get_post_status( $itoi_sid ) ) {
								continue;
							}
							$itoi_s_eyebrow    = get_field( 'eyebrow', $itoi_sid );
							$itoi_s_headline   = itoi_or( get_field( 'headline', $itoi_sid ), get_the_title( $itoi_sid ) );
							$itoi_s_tile_id    = get_field( 'tile_image', $itoi_sid );
							$itoi_s_tile_url   = $itoi_s_tile_id ? wp_get_attachment_image_url( $itoi_s_tile_id, 'medium_large' ) : '';
							$itoi_s_tile_video = get_field( 'tile_video', $itoi_sid );
							?>
							<a href="<?php echo esc_url( get_permalink( $itoi_sid ) ); ?>" class="group glass-element-light block rounded-2xl">
								<div class="relative aspect-[4/5] w-full overflow-hidden rounded-t-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
									<?php
									$itoi_s_tile_media = itoi_media_cover( $itoi_s_tile_url, $itoi_s_tile_video, $itoi_s_headline, 'absolute inset-0 h-full w-full object-cover', 'loading="lazy"' );
									?>
									<?php if ( $itoi_s_tile_media ) : ?>
										<?php echo $itoi_s_tile_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
									<?php else : ?>
										<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_s_headline ); ?> (TODO)</div>
									<?php endif; ?>
								</div>
								<div class="relative px-5 pb-5 pt-4">
									<?php if ( $itoi_s_eyebrow ) : ?>
										<div class="mb-1 text-[11px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_s_eyebrow ); ?></div>
									<?php endif; ?>
									<div class="text-[19px] font-extrabold text-ink"><?php echo esc_html( $itoi_s_headline ); ?></div>
									<span class="relative mt-2 inline-flex items-center gap-2 rounded-[24px] bg-white px-[18px] py-2.5 text-[13px] font-bold text-ink shadow-[0_10px_24px_-10px_rgba(0,0,0,0.3)] transition-transform group-hover:-translate-y-0.5">Learn more &rarr;</span>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- ================= CUSTOMERS =================
			Only rendered at all when $itoi_lf_show_customers_section is
			true (real spotlight, real logo-strip rows, or an explicit
			empty-state message) — an industry with zero confirmed clients
			and no empty-state copy omits this section (and its sub-nav
			link above) entirely rather than showing a heading over
			nothing. See NOTES.md for which industries hit which branch.
			Render logic extracted 2026-07-30 into
			inc/customers-section.php — shared with the homepage's own
			Customers section (see NOTES.md). -->
		<?php if ( $itoi_lf_show_customers_section ) : ?>
			<?php itoi_render_customers_section_data( $itoi_lf_customers_data ); ?>
		<?php endif; ?>

	<?php endif; // $itoi_show_longform ?>

	<?php if ( ! $itoi_show_longform && ! empty( $itoi_clients ) ) : ?>
		<section class="aurora-bg-light px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-6 text-2xl">Trusted by</h2>
				<div class="flex flex-wrap gap-2.5">
					<?php foreach ( $itoi_clients as $itoi_row ) : ?>
						<span class="glass-element-light rounded-full px-5 py-2.5 text-[13.5px] font-semibold"><?php echo esc_html( $itoi_row['text'] ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! $itoi_show_longform && ! empty( $itoi_rel_solutions ) ) : ?>
		<section class="aurora-bg-light border-t border-line bg-hero-bg px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-8 text-2xl">Solutions for <?php echo esc_html( $itoi_name ); ?></h2>
				<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
					<?php
					foreach ( $itoi_rel_solutions as $itoi_sid ) :
						if ( 'publish' !== get_post_status( $itoi_sid ) ) {
							continue;
						}
						$itoi_s_headline = itoi_or( get_field( 'headline', $itoi_sid ), get_the_title( $itoi_sid ) );
						$itoi_s_dek      = get_field( 'dek', $itoi_sid );
						?>
						<a href="<?php echo esc_url( get_permalink( $itoi_sid ) ); ?>" class="glass-element-light block rounded-xl px-5 py-5 transition-all hover:-translate-y-[3px]">
							<div class="text-[15px] font-bold"><?php echo esc_html( $itoi_s_headline ); ?></div>
							<?php if ( $itoi_s_dek ) : ?>
								<p class="mt-1.5 line-clamp-2 text-[13.5px] text-text-muted"><?php echo esc_html( $itoi_s_dek ); ?></p>
							<?php endif; ?>
							<span class="mt-2.5 inline-block text-xs font-bold underline">Learn more</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<div class="mx-4 mb-[60px] mt-16 flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
		<div>
			<h2 class="mb-2 max-w-[20ch] text-[clamp(22px,2.6vw,32px)] text-white">Talk to us about <?php echo esc_html( $itoi_name ); ?></h2>
			<p class="m-0 max-w-[34ch] text-white/60">Our pilot program includes one site, full dashboard access, and a dedicated specialist.</p>
		</div>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Contact us</a>
	</div>

	<?php
endwhile;

get_footer();
