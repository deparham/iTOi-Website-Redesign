<?php
/**
 * Single solution detail — pulls every ACF Solution field (PROJECT.md §4).
 * Empty repeaters/relationships render nothing (real empty states, not
 * broken blocks) — e.g. every solution currently has zero FAQs sourced
 * from the live site, so that section simply doesn't print.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$itoi_id                    = get_the_ID();
	$itoi_eyebrow               = get_field( 'eyebrow' );
	$itoi_headline              = itoi_or( get_field( 'headline' ), get_the_title() );
	$itoi_dek                   = get_field( 'dek' );
	$itoi_tagline               = get_field( 'tagline' );
	$itoi_hero_id               = get_field( 'hero_image' );
	$itoi_hero                  = $itoi_hero_id ? wp_get_attachment_image_url( $itoi_hero_id, 'large' ) : '';
	$itoi_hero_video            = get_field( 'hero_video' );
	$itoi_highlight_photo_id    = get_field( 'highlight_photo' );
	$itoi_highlight_photo       = $itoi_highlight_photo_id ? wp_get_attachment_image_url( $itoi_highlight_photo_id, 'large' ) : '';
	$itoi_highlight_video       = get_field( 'highlight_video' );
	$itoi_intro_paragraph       = get_field( 'intro_paragraph' );
	$itoi_spec_stat_number      = get_field( 'spec_strip_stat_number' );
	$itoi_spec_stat_label       = get_field( 'spec_strip_stat_label' );
	$itoi_spec_items            = get_field( 'spec_strip_items' );
	$itoi_narrative             = get_field( 'narrative' );
	$itoi_process_diagram_steps = get_field( 'process_diagram_steps' );
	$itoi_process_diagram_style = itoi_or( get_field( 'process_diagram_style' ), 'lines' );
	$itoi_capability_cards      = get_field( 'capability_cards' );
	$itoi_specs                 = get_field( 'specs' );
	$itoi_integrations          = get_field( 'integrations' );
	$itoi_faqs                  = get_field( 'faqs' );
	$itoi_rel_industries        = get_field( 'related_industries' );
	$itoi_rel_use_cases         = get_field( 'related_use_cases' );
	$itoi_rel_cases             = get_field( 'related_case_studies' );
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px]">
		<div class="mx-auto grid max-w-[1280px] gap-10 min-[980px]:grid-cols-[1.1fr_1fr] min-[980px]:items-center">
			<div>
				<?php if ( $itoi_eyebrow ) : ?>
					<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_eyebrow ); ?></div>
				<?php endif; ?>
				<h1 class="max-w-[16ch]"><?php echo esc_html( $itoi_headline ); ?></h1>
				<?php if ( $itoi_tagline ) : ?>
					<p class="mt-2 text-[17px] font-semibold italic text-teal-800">&ldquo;<?php echo esc_html( $itoi_tagline ); ?>&rdquo;</p>
				<?php endif; ?>
				<?php if ( $itoi_dek ) : ?>
					<p class="mt-4 max-w-[46ch] text-[17px] text-text-muted"><?php echo esc_html( $itoi_dek ); ?></p>
				<?php endif; ?>
				<div class="mt-7 flex flex-wrap gap-2.5">
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover">Get demo</a>
					<a href="<?php echo esc_url( home_url( '/solutions/' ) ); ?>" class="rounded-full border-[1.5px] border-ink bg-white px-[22px] py-[11px] text-sm font-bold hover:bg-hero-bg">All solutions</a>
				</div>
			</div>
			<div class="relative aspect-[4/3] w-full overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
				<?php
				$itoi_hero_media = itoi_media_cover( $itoi_hero, $itoi_hero_video, $itoi_headline, 'absolute inset-0 h-full w-full object-cover' );
				?>
				<?php if ( $itoi_hero_media ) : ?>
					<?php echo $itoi_hero_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
				<?php else : ?>
					<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_headline ); ?> (TODO)</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
	// Cross-link callout — security-access-inventory only. The old
	// `smart-security` solution's CCTV/theft-detection content moved to a
	// separate `cctv-video-loss-prevention` page (2026-07-23 restructure,
	// see NOTES.md); this page was chosen as `smart-security`'s primary
	// redirect destination, so a visitor arriving here looking specifically
	// for CCTV/loss-prevention needs an explicit way back out.
	if ( 'security-access-inventory' === get_post_field( 'post_name', $itoi_id ) ) :
		$itoi_cctv_post = get_page_by_path( 'cctv-video-loss-prevention', OBJECT, 'solution' );
		if ( $itoi_cctv_post && 'publish' === $itoi_cctv_post->post_status ) :
			?>
			<section class="px-8 pt-10">
				<div class="mx-auto flex max-w-[1280px] flex-wrap items-center justify-between gap-4 rounded-2xl border border-line bg-hero-bg px-6 py-5">
					<p class="m-0 text-[14.5px] font-semibold">Looking for CCTV, video, and loss prevention specifically?</p>
					<a href="<?php echo esc_url( get_permalink( $itoi_cctv_post ) ); ?>" class="whitespace-nowrap rounded-full bg-ink px-5 py-2.5 text-[13.5px] font-bold text-white transition-colors hover:bg-cta-hover">CCTV, Video &amp; Loss Prevention &rarr;</a>
				</div>
			</section>
			<?php
		endif;
	endif;
	?>

	<?php
	/**
	 * Highlight panel — 2026-07-28 (see NOTES.md). A curated preview of a
	 * subset of this page's own real data — its `specs` repeater where
	 * populated, or `capability_cards` names where `specs` is empty/thin
	 * (fewer than 2 usable entries) — never the full-detail Specs section
	 * further down, which is untouched. Reviewed and approved page-by-page
	 * before this was built (see NOTES.md for the full selection table).
	 * `it-network-infrastructure` is intentionally absent from this config
	 * — it has neither a populated `specs` field nor 2 genuinely distinct
	 * capability names (one of its 2 capability cards just restates the
	 * page's own headline), so per the approved rule this page gets no
	 * panel at all rather than a padded/weak one.
	 *
	 * `stat`-style items show the real field VALUE verbatim (font-size
	 * scaled down for longer values via itoi_highlight_stat_size() below,
	 * never truncated/reworded) — kept fully mechanical and staleness-proof
	 * rather than hand-shortened, so an editor updating the real spec
	 * later can't silently desync this preview from the source of truth.
	 * `label`-style items show the real LABEL first (bold) then the real
	 * value below (muted) for descriptive/longer values. `name`-style
	 * items (the capability_cards fallback) show only the real capability
	 * name — there's no numeric value to pair it with.
	 */
	$itoi_hl_slug   = get_post_field( 'post_name', $itoi_id );
	$itoi_hl_config = array(
		'security-access-inventory'   => array(
			'headline' => "Verify who's on site. Control what they reach. Know what's on the shelf.",
			'source'   => 'specs',
			'items'    => array(
				array(
					'match' => 'Accuracy',
					'style' => 'stat',
				),
				array(
					'match' => 'Recognition speed',
					'style' => 'stat',
				),
				array(
					'match' => 'False Non-Match Rate',
					'style' => 'stat',
				),
				array(
					'match' => 'Encryption',
					'style' => 'stat',
				),
			),
		),
		'workforce-ops-robotics'      => array(
			'headline' => 'One connected operations layer — robots and rostered teams, working together.',
			'source'   => 'specs',
			'items'    => array(
				array(
					'match' => 'Sensors',
					'style' => 'stat',
				),
				array(
					'match' => 'Scrubber 75',
					'style' => 'label',
				),
				array(
					'match' => 'Phantas',
					'style' => 'label',
				),
				array(
					'match' => '40 Sprayer',
					'style' => 'label',
				),
			),
		),
		'back-of-house-integration'   => array(
			'headline' => 'Every system behind the counter, working as one.',
			'source'   => 'specs',
			'items'    => array(
				array(
					'match' => 'Spirits/wine brands controlled',
					'style' => 'stat',
				),
				array(
					'match' => 'Free-pour spout capacity',
					'style' => 'stat',
				),
				array(
					'match' => 'Wireless communication',
					'style' => 'stat',
				),
				array(
					'match' => 'Reporting',
					'style' => 'label',
				),
			),
		),
		'intelligence-analytics'      => array(
			'headline' => 'Real traffic data. Real decisions. One dashboard.',
			'source'   => 'specs',
			'items'    => array(
				array(
					'match' => 'Accuracy',
					'style' => 'stat',
				),
				array(
					'match' => 'Dashboard modules',
					'style' => 'label',
				),
			),
		),
		'cctv-video-loss-prevention'  => array(
			'headline' => 'AI-driven theft detection — automated, exception-based, camera to case.',
			'source'   => 'capabilities',
			'items'    => array(
				array(
					'match' => 'AI Threat Detection',
					'style' => 'name',
				),
				array(
					'match' => 'POS-Linked Video',
					'style' => 'name',
				),
				array(
					'match' => 'Exception-Based Reporting',
					'style' => 'name',
				),
				array(
					'match' => 'Theft Pattern Detection',
					'style' => 'name',
				),
			),
		),
		'customer-engagement-signage' => array(
			'headline' => 'One platform. Every guest touchpoint.',
			'source'   => 'capabilities',
			'items'    => array(
				array(
					'match' => 'Commercial Displays & E-Ink',
					'style' => 'name',
				),
				array(
					'match' => 'Real-Time Content Triggers',
					'style' => 'name',
				),
				array(
					'match' => 'Lift-and-Learn & QR Journeys',
					'style' => 'name',
				),
				array(
					'match' => 'Guest Wi-Fi & Occupancy',
					'style' => 'name',
				),
			),
		),
		'sensory-intelligence'        => array(
			'headline' => 'The store that feels everything.',
			'source'   => 'capabilities',
			'items'    => array(
				array(
					'match' => 'Smart Visual Displays',
					'style' => 'name',
				),
				array(
					'match' => 'Signature Brand Scent',
					'style' => 'name',
				),
				array(
					'match' => 'Real-Time Audio Environment',
					'style' => 'name',
				),
				array(
					'match' => 'Proximity & Beacon',
					'style' => 'name',
				),
			),
		),
	);

	$itoi_hl             = $itoi_hl_config[ $itoi_hl_slug ] ?? null;
	$itoi_hl_source_rows = $itoi_hl ? ( 'specs' === $itoi_hl['source'] ? $itoi_specs : $itoi_capability_cards ) : array();
	$itoi_hl_source_key  = $itoi_hl && 'specs' === $itoi_hl['source'] ? 'label' : 'name';
	$itoi_hl_resolved    = array();
	if ( $itoi_hl ) {
		foreach ( $itoi_hl['items'] as $itoi_hl_item ) {
			$itoi_hl_row = itoi_highlight_find_row( $itoi_hl_source_rows, $itoi_hl_source_key, $itoi_hl_item['match'] );
			if ( $itoi_hl_row ) {
				$itoi_hl_resolved[] = array(
					'style' => $itoi_hl_item['style'],
					'label' => $itoi_hl_row[ $itoi_hl_source_key ],
					'value' => $itoi_hl_row['value'] ?? '',
				);
			}
		}
	}
	?>
	<?php if ( ! empty( $itoi_hl_resolved ) ) : ?>
		<section class="bg-ink relative overflow-hidden px-8 py-section-md <?php echo esc_attr( itoi_reveal_class() ); ?>">
			<div class="relative z-[1] mx-auto max-w-[1280px]">
				<h2 class="max-w-[26ch] text-[clamp(24px,3vw,34px)] text-white"><?php echo esc_html( $itoi_hl['headline'] ); ?></h2>
				<div class="mt-9 grid grid-cols-1 gap-8 min-[900px]:grid-cols-[1fr_1.2fr] min-[900px]:items-center">
					<div class="relative aspect-[4/3] w-full" style="--reveal-radius:16px">
						<?php itoi_reveal_markup(); ?>
						<div class="absolute inset-0 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#1a2530,#0e1620)]">
							<?php
							$itoi_highlight_media = itoi_media_cover( $itoi_highlight_photo, $itoi_highlight_video, $itoi_headline . ' highlight', 'absolute inset-0 h-full w-full object-cover' );
							?>
							<?php if ( $itoi_highlight_media ) : ?>
								<?php echo $itoi_highlight_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-white/40">Photo &mdash; <?php echo esc_html( $itoi_headline ); ?> highlight (TODO)</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="grid grid-cols-2 gap-4">
						<?php foreach ( $itoi_hl_resolved as $itoi_hl_row ) : ?>
							<?php if ( 'stat' === $itoi_hl_row['style'] ) : ?>
								<?php $itoi_hl_stat_display = itoi_highlight_stat_display( $itoi_hl_row['value'] ); ?>
								<div class="highlight-panel-glass flex flex-col justify-center p-5">
									<div class="<?php echo esc_attr( itoi_highlight_stat_size( $itoi_hl_stat_display ) ); ?> font-extrabold leading-tight text-white"><?php echo esc_html( $itoi_hl_stat_display ); ?></div>
									<div class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-white/60"><?php echo esc_html( $itoi_hl_row['label'] ); ?></div>
								</div>
							<?php elseif ( 'label' === $itoi_hl_row['style'] ) : ?>
								<div class="highlight-panel-glass flex flex-col justify-center p-5">
									<div class="text-[13px] font-bold uppercase tracking-wide text-white"><?php echo esc_html( $itoi_hl_row['label'] ); ?></div>
									<div class="mt-1.5 text-[13px] leading-snug text-white/70"><?php echo esc_html( $itoi_hl_row['value'] ); ?></div>
								</div>
							<?php else : ?>
								<div class="highlight-panel-glass flex items-center justify-center p-5 text-center">
									<div class="text-[14px] font-bold leading-snug text-white"><?php echo esc_html( $itoi_hl_row['label'] ); ?></div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// Overview — 2026-07-29 (see NOTES.md, "solution page content quality"
	// entry). Real narrative copy, sourced from ITOI's own live site content
	// per page (never competitor wording — Kepler/Verkada were reviewed for
	// structure only). Sits between the highlight panel and Capabilities —
	// confirmed placement, not the original pre-2026-07-29 spot (which was
	// between the header and the panel, removed as part of the highlight-
	// panel cleanup). Renders only when `narrative` is populated — same
	// real-empty-state pattern as every other optional section here.
	// Restructured 2026-07-31 (see NOTES.md) to two-column (heading left,
	// body right — container widened from the old centered max-w-[720px]
	// to max-w-[1280px] to match every other section width in this file,
	// since the optional process diagram below needs to span full section
	// width, not the old narrow prose column) with an optional CAPTURE →
	// ANALYSE → ACT-style diagram (itoi_render_process_diagram(),
	// inc/process-diagram.php — shared with single-industry.php's own
	// Overview section) below the body text.
	?>
	<?php if ( $itoi_narrative ) : ?>
		<section class="aurora-bg-light px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<div class="grid gap-8 min-[980px]:grid-cols-[1.1fr_1fr] min-[980px]:gap-14">
					<h2 class="text-2xl">Overview</h2>
					<div class="prose text-[16px] leading-[1.7] [&_p]:mb-4 [&_strong]:font-bold">
						<?php echo wp_kses_post( $itoi_narrative ); ?>
					</div>
				</div>
				<?php itoi_render_process_diagram( $itoi_process_diagram_steps, $itoi_process_diagram_style ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// Capability breakdown — flip-card grid (SEO/content pass, 2026-07-23).
	// Reuses the shared .flip-card mechanic verbatim (src/tailwind.css +
	// initFlipCards() in main.js — same component as the About page's
	// "Partners, not vendors" section and the homepage solutions/industries
	// grids, not a new implementation). Front face: photo (or a TODO(photo)
	// placeholder naming the subject) + capability name as the card's real
	// heading. Back face: description, an optional stat sentence, and an
	// optional illustrative-data disclaimer — name is repeated as plain
	// bold text there (not a second <h3>) so flipping doesn't duplicate a
	// heading node for the same capability.
	// prefers-reduced-motion: the sitewide `transition:none` rule alone
	// would still require a hover/tap to ever reveal the back face's real
	// information, which would hide it from reduced-motion users entirely
	// on touch devices with no hover. `.capability-flip-card` opts into an
	// additional override (src/tailwind.css) that unstacks both faces into
	// normal document flow under `prefers-reduced-motion: reduce`, so both
	// are always visible with no interaction required — see NOTES.md.
	?>
	<?php if ( ! empty( $itoi_capability_cards ) ) : ?>
		<section class="border-t border-line bg-hero-bg px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-8 text-2xl">Capabilities</h2>
				<div class="grid grid-cols-1 gap-5 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
					<?php
					foreach ( $itoi_capability_cards as $itoi_cc ) :
						$itoi_cc_photo_id    = $itoi_cc['photo'];
						$itoi_cc_photo_url   = $itoi_cc_photo_id ? wp_get_attachment_image_url( $itoi_cc_photo_id, 'medium_large' ) : '';
						$itoi_cc_placeholder = trim( (string) $itoi_cc['photo_placeholder_alt'] );
						$itoi_cc_alt         = $itoi_cc_photo_id ? itoi_or( get_post_meta( $itoi_cc_photo_id, '_wp_attachment_image_alt', true ), $itoi_cc_placeholder ) : $itoi_cc_placeholder;
						$itoi_cc_media       = itoi_media_cover( $itoi_cc_photo_url, $itoi_cc['video'] ?? null, $itoi_cc_alt, 'absolute inset-0 h-full w-full object-cover', 'loading="lazy"' );
						?>
						<div class="flip-card capability-flip-card aspect-[4/5]">
							<div class="flip-card-inner">
								<div class="flip-card-front flex flex-col overflow-hidden rounded-2xl border border-line bg-white">
									<!-- Liquid glass, wave 3 Part 2 (see NOTES.md): the capability
										name moved from a separate white strip below the photo onto
										the photo itself, bottom-left, as a glass badge — the
										pattern this exact task described, matching how the same
										"name over photo" treatment already works elsewhere in this
										theme (archive-solution.php, single-industry.php Solutions
										grid). The after: gradient is the same dark bottom-fade
										those other tiles already use — added here specifically so
										the light glass badge (--glass-bg-on-dark, white text) stays
										legible regardless of how bright an individual capability
										photo is, not just on the darker ones. -->
									<div class="relative aspect-[4/3] w-full flex-none overflow-hidden bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)] after:absolute after:inset-0 after:bg-[linear-gradient(to_top,rgba(14,17,22,0.6)_0%,rgba(14,17,22,0)_45%)]">
										<?php if ( $itoi_cc_media ) : ?>
											<?php echo $itoi_cc_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
										<?php else : ?>
											<div class="absolute inset-0 flex items-center justify-center p-3 text-center text-[10.5px] uppercase tracking-[0.06em] text-[#8f99a6]"><?php echo esc_html( $itoi_cc_placeholder ); ?> (TODO)</div>
										<?php endif; ?>
										<h3 class="capability-badge-glass absolute bottom-2.5 left-2.5 z-[2] m-0 max-w-[calc(100%-20px)] rounded-lg px-2.5 py-1.5 text-[13px] font-bold leading-tight"><?php echo esc_html( $itoi_cc['name'] ); ?></h3>
									</div>
									<div class="flex flex-1 flex-col items-center justify-center gap-2 px-4 py-4 text-center">
										<span class="flip-card-hint text-[10.5px] font-bold uppercase tracking-wide text-text-muted" aria-hidden="true">Hover / tap to flip</span>
									</div>
								</div>
								<div class="flip-card-back flex flex-col items-center justify-center gap-2.5 overflow-y-auto rounded-2xl bg-teal-900 px-6 py-7 text-center">
									<p class="m-0 text-[15px] font-bold text-white"><?php echo esc_html( $itoi_cc['name'] ); ?></p>
									<?php if ( ! empty( $itoi_cc['description'] ) ) : ?>
										<p class="m-0 max-w-[34ch] text-[13.5px] leading-[1.55] text-white/90"><?php echo esc_html( $itoi_cc['description'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $itoi_cc['stat'] ) ) : ?>
										<p class="m-0 max-w-[34ch] text-[13.5px] leading-[1.55] text-white/90"><?php echo esc_html( $itoi_cc['stat'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $itoi_cc['has_disclaimer'] ) ) : ?>
										<p class="m-0 max-w-[36ch] text-[10.5px] leading-[1.45] text-white/60">Illustrative only, based on industry research and general case studies, not guaranteed results. Actual performance will vary by store, location, creative quality, product mix, staffing, and external factors.</p>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// The full-detail Specs section (was here) was removed 2026-07-29 (see
	// NOTES.md) — the highlight panel above now covers that role. $itoi_specs
	// itself is still fetched at the top of this template (not removed):
	// the highlight panel's config still reads from it for pages whose
	// panel source is 'specs'.
	?>

	<?php if ( ! empty( $itoi_integrations ) ) : ?>
		<section class="border-t border-line bg-hero-bg px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-6 text-2xl">Integrations</h2>
				<div class="flex flex-wrap gap-2.5">
					<?php foreach ( $itoi_integrations as $itoi_row ) : ?>
						<span class="rounded-full border border-line bg-white px-4 py-2 text-[13.5px] font-semibold"><?php echo esc_html( $itoi_row['text'] ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $itoi_faqs ) ) : ?>
		<section class="px-8 py-section-md">
			<div class="mx-auto max-w-[720px]">
				<h2 class="mb-6 text-2xl">FAQs</h2>
				<div class="divide-y divide-line border-t border-line">
					<?php foreach ( $itoi_faqs as $itoi_row ) : ?>
						<details class="group py-4">
							<summary class="cursor-pointer list-none text-[15px] font-bold marker:content-none">
								<?php echo esc_html( $itoi_row['q'] ); ?>
							</summary>
							<p class="mt-2.5 text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_row['a'] ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// Related industries / use cases / case studies — filtered to published only,
	// since a real visitor should never land on a draft they can't view.
	$itoi_related_sections = array(
		array(
			'label' => 'Related industries',
			'ids'   => $itoi_rel_industries,
			'base'  => '/industries/',
		),
		array(
			'label' => 'Related use cases',
			'ids'   => $itoi_rel_use_cases,
			'base'  => '/use-cases/',
		),
		array(
			'label' => 'Related case studies',
			'ids'   => $itoi_rel_cases,
			'base'  => '/case-studies/',
		),
	);
	foreach ( $itoi_related_sections as $itoi_section ) :
		if ( empty( $itoi_section['ids'] ) ) {
			continue;
		}
		$itoi_published = array_filter(
			$itoi_section['ids'],
			function ( $itoi_pid ) {
				return 'publish' === get_post_status( $itoi_pid );
			}
		);
		if ( empty( $itoi_published ) ) {
			continue;
		}
		?>
		<section class="border-t border-line bg-hero-bg px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-6 text-2xl"><?php echo esc_html( $itoi_section['label'] ); ?></h2>
				<div class="flex flex-wrap gap-2.5">
					<?php foreach ( $itoi_published as $itoi_pid ) : ?>
						<a href="<?php echo esc_url( get_permalink( $itoi_pid ) ); ?>" class="rounded-full border border-line bg-white px-5 py-2.5 text-[13.5px] font-semibold hover:border-ink"><?php echo esc_html( get_the_title( $itoi_pid ) ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	endforeach;
	?>

	<?php
endwhile;

get_footer();
