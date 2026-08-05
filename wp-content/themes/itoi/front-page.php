<?php
/**
 * Homepage. Ground truth: preview-verkada-match.html.
 *
 * Phase 2 scope: static structure + first-state content for every section,
 * matched pixel-for-pixel against the mockup. The JS-driven mechanics (hero
 * slideshow + Live Detection background, ticker rotation, why-choose tab
 * swap, industries carousel arrows, traffic-demo widget) are Phase 3 work
 * per PROJECT.md §10 — this proves the layout, not the interaction.
 *
 * All copy here is placeholder pending the ACF content model (Phase 4/5).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<!-- ================= MEGA HERO ================= -->
<!-- Interactive: headline/sub slideshow + dot-nav auto-advance/ring-fill/
     click, drifting signature-navy detection boxes (Live Detection
     background). See assets/js/main.js. Dark hero background — bright
     variant throughout.
     2026-07-23 correction: an earlier session shrunk this section's height
     AND incorrectly removed the multi-slide dot-nav slideshow entirely,
     collapsing it to one fixed headline. Only the height reduction was
     actually requested — the slideshow removal was not, and is reverted
     here (see NOTES.md for both the original shrink entry and this
     correction). The reduced height/padding below is intentionally KEPT;
     everything else in this section is restored to its pre-shrink
     multi-slide behaviour, now with 5 slides (a new RetailNext
     co-branding slide added — see initHeroSlideshow() in main.js for the
     partnership-lockup treatment). -->
<?php
// Server-rendered fallback = slide 0's real content (never the RetailNext
// partnership slide, so plain text/media is always safe here) — matches
// what initHeroSlideshow() sets on load, no flash of different content
// while JS boots. Fetched here (moved up from further below, 2026-07-23)
// since slide 0's photo/video is now also needed for the initial #heroBg
// render, not just the headline/subcopy.
$itoi_hero_slides_php = get_field( 'hero_slides', 'option' );
$itoi_hero_slide0     = is_array( $itoi_hero_slides_php ) && ! empty( $itoi_hero_slides_php ) ? $itoi_hero_slides_php[0] : array();
$itoi_hero_headline   = $itoi_hero_slide0['headline'] ?? 'Enterprise security systems that eliminate operational blind spots.';

/**
 * Content-aware headline sizing (see src/tailwind.css's "Hero headline
 * content-aware sizing" comment for the full rationale) — picks one of 4
 * font-size tiers by character count instead of one fixed clamp(), so a
 * long editor-entered headline shrinks instead of just wrapping onto many
 * lines and blowing out the hero's height. Thresholds mirrored exactly in
 * heroHeadlineSizeClass() (assets/js/main.js) for slides 2-5, which render
 * client-side on slide change — keep both in sync if these ever change.
 */
function itoi_hero_headline_size_class( $headline ) {
	$length = mb_strlen( (string) $headline );
	if ( $length <= 60 ) {
		return 'hero-headline-size-1';
	} elseif ( $length <= 85 ) {
		return 'hero-headline-size-2';
	} elseif ( $length <= 110 ) {
		return 'hero-headline-size-3';
	}
	return 'hero-headline-size-4';
}
$itoi_hero_subcopy    = $itoi_hero_slide0['subcopy'] ?? 'Integrated facial recognition, ANPR, access control, and intelligent surveillance for schools, healthcare facilities, logistics sites, and commercial property across Australia.';

// Per-slide background photo/video (optional) — Site Settings, added
// 2026-07-23. Each of the 5 hero_slides rows can carry its own photo/video,
// falling back independently to the plain dark gradient when that slide's
// own media is empty. initHeroSlideshow() (main.js) swaps #heroBgPhoto's
// src / #heroBgVideo's src and toggles which is visible on every slide
// change, using the same itoiHeroSlides data localized for the text swap —
// this PHP block only renders slide 0's version, as the pre-JS SSR state.
// The Live Detection markers (initHeroDetectionBoxes()) keep drawing into
// #heroBg regardless of which slide/media is active — this is purely a
// background-layer swap, never a replacement of the signature layer
// (CLAUDE.md's hard rule).
$itoi_slide0_photo_id  = $itoi_hero_slide0['photo'] ?? 0;
$itoi_slide0_photo_url = $itoi_slide0_photo_id ? wp_get_attachment_image_url( $itoi_slide0_photo_id, 'large' ) : '';
$itoi_slide0_photo_alt = $itoi_slide0_photo_id ? ( get_post_meta( $itoi_slide0_photo_id, '_wp_attachment_image_alt', true ) ?: '' ) : '';
$itoi_slide0_video     = $itoi_hero_slide0['video'] ?? null;
$itoi_slide0_video_url = ! empty( $itoi_slide0_video['url'] ) ? $itoi_slide0_video['url'] : '';
$itoi_hero_has_media   = $itoi_slide0_photo_url || $itoi_slide0_video_url;

// Static, slide-0-only CTAs + trust row (enterprise reskin) — do NOT rotate
// with the hero_slides dot-nav slideshow above; slides 2-5 keep their own
// unrelated copy untouched. Repeater ('option') isn't ?:-friendly like the
// scalar fields above, so it gets the same get_field()+empty() fallback
// pattern already used for $itoi_proof_stats further down this file.
$itoi_hero_cta_primary_label   = get_field( 'hero_cta_primary_label', 'option' ) ?: 'Book a site assessment';
$itoi_hero_cta_primary_url     = get_field( 'hero_cta_primary_url', 'option' ) ?: '/contact/';
$itoi_hero_cta_secondary_label = get_field( 'hero_cta_secondary_label', 'option' ) ?: 'View case studies';
$itoi_hero_cta_secondary_url   = get_field( 'hero_cta_secondary_url', 'option' ) ?: '/case-studies/';
$itoi_hero_trust_metrics       = get_field( 'hero_trust_metrics', 'option' );
if ( empty( $itoi_hero_trust_metrics ) ) {
	$itoi_hero_trust_metrics = array(
		array( 'text' => 'Under 2 hour response time' ),
		array( 'text' => 'Australia wide coverage' ),
		array( 'text' => 'Enterprise SLA support' ),
	);
}
?>
<?php
/**
 * 2026-08-03: combined with the header (see header.php's is_front_page()
 * branch — ticker+nav become a fixed overlay only on this page) so the nav
 * pill floats directly over this section's own dark background, no white
 * page-background seam between them. pt-[…] below is that fixed block's
 * real height (ticker + gap + nav pill) plus breathing room before the
 * headline — keep in sync if the header block's height ever changes.
 * Layout also changed same day from side-by-side (headline left, sub+CTA
 * right) to a centered stack, matching the requested reference look.
 */
?>
<section class="relative overflow-hidden px-5 pb-16 pt-[168px] min-h-[560px] min-[640px]:px-8 min-[640px]:pb-24 min-[640px]:pt-[206px] min-[640px]:min-h-[640px]" id="megaHero">
	<div class="absolute inset-0 <?php echo $itoi_hero_has_media ? 'bg-[#0a1720]' : 'bg-[linear-gradient(160deg,#0a1720,#122b38_55%,#1b3a48)]'; ?>" id="heroBg">
		<video class="absolute inset-0 h-full w-full object-cover <?php echo $itoi_slide0_video_url ? '' : 'hidden'; ?>" id="heroBgVideo" autoplay muted loop playsinline <?php echo $itoi_slide0_photo_url ? 'poster="' . esc_url( $itoi_slide0_photo_url ) . '"' : ''; ?>>
			<?php if ( $itoi_slide0_video_url ) : ?><source src="<?php echo esc_url( $itoi_slide0_video_url ); ?>"><?php endif; ?>
		</video>
		<img class="absolute inset-0 h-full w-full object-cover <?php echo ( $itoi_slide0_photo_url && ! $itoi_slide0_video_url ) ? '' : 'hidden'; ?>" id="heroBgPhoto" src="<?php echo esc_url( $itoi_slide0_photo_url ); ?>" alt="<?php echo esc_attr( $itoi_slide0_photo_alt ); ?>">
	</div>
	<div class="absolute inset-0 bg-[rgba(21,36,45,0.35)] backdrop-blur-[6px]"></div>
	<div class="hero-dot-grid absolute inset-0" aria-hidden="true"></div>

	<div class="relative z-[2] mx-auto flex w-full max-w-[720px] flex-col items-center gap-6 text-center">
		<div class="inline-flex items-center gap-1.5 text-[11px] font-bold tracking-[0.06em] text-white">
			<span class="h-[7px] w-[7px] animate-itoi-pulse rounded-full bg-signature-bright"></span>LIVE
		</div>
		<h1 class="max-w-[18ch] <?php echo esc_attr( itoi_hero_headline_size_class( $itoi_hero_headline ) ); ?> leading-[1.05] text-white" id="heroHeadline"><?php echo esc_html( $itoi_hero_headline ); ?></h1>
		<p class="m-0 max-w-[54ch] text-[15px] text-white/90 min-[1024px]:text-[16.5px]" id="heroSub"><?php echo esc_html( $itoi_hero_subcopy ); ?></p>
		<div class="flex flex-wrap items-center justify-center gap-3">
			<a href="<?php echo esc_url( $itoi_hero_cta_primary_url ); ?>" class="hero-cta-accent whitespace-nowrap px-[26px] py-[13px] text-[14.5px] font-bold transition-[background-color,box-shadow,transform] duration-200 ease-out hover:-translate-y-0.5"><?php echo esc_html( $itoi_hero_cta_primary_label ); ?> &rarr;</a>
			<a href="<?php echo esc_url( $itoi_hero_cta_secondary_url ); ?>" class="hero-cta-secondary whitespace-nowrap px-[26px] py-[13px] text-[14.5px] font-semibold text-white transition-[background-color,box-shadow,transform] duration-200 ease-out hover:-translate-y-0.5"><?php echo esc_html( $itoi_hero_cta_secondary_label ); ?></a>
		</div>
		<?php if ( ! empty( $itoi_hero_trust_metrics ) ) : ?>
			<ul class="mt-1 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 p-0 list-none">
				<?php foreach ( $itoi_hero_trust_metrics as $itoi_trust_item ) : ?>
					<?php if ( empty( $itoi_trust_item['text'] ) ) { continue; } ?>
					<li class="flex items-center gap-2 text-[12.5px] font-semibold text-white/80">
						<span class="flex h-[18px] w-[18px] flex-none items-center justify-center rounded-full bg-white/15 text-[9.5px]" aria-hidden="true">&#10003;</span>
						<?php echo esc_html( $itoi_trust_item['text'] ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<div class="dotnav-glass absolute bottom-4 left-4 z-[3] flex gap-2 p-2 min-[640px]:bottom-auto min-[640px]:left-auto min-[640px]:right-4 min-[640px]:top-1/2 min-[640px]:flex-col min-[640px]:-translate-y-1/2" id="dotNav"></div>
</section>

<!-- ================= TRUST & CREDIBILITY ================= -->
<!-- Directly below the mega-hero, enterprise CRO Step 2 — a monochrome
     metric-card row + the real client-logo marquee, distinct from PROOF
     (stat tiles, dark bg-ink glass cards, id-less, below Delivery Model)
     further down this page. This section intentionally doesn't reuse that
     one's visual language — light, bordered, soft-shadow cards per the
     monochrome enterprise brief, not dark glass.
     2026-08-05: the standalone "TRUSTED BY (logo band)" section that used
     to sit further down the page (itoi_render_trusted_by_band(), its own
     "Trusted by:" heading) is gone — consolidated here instead, since two
     separate "who trusts us" sections on one homepage was redundant. This
     section's own heading already carries that framing, so the logo row
     renders directly below the metric cards with no second heading above
     it. Same rendering mechanic as before (itoi_render_client_logo_row(),
     inc/customers-section.php — real, published `client` CPT posts,
     marquee if more than 5, static wrapped row otherwise), just relocated.
     The temporary ACF-driven placeholder-logo repeater this section
     originally shipped with (trust_placeholder_logos) is gone too — real
     logos now render here directly, no separate "placeholder vs real"
     split to maintain.
     Counters: when a card's stat_value starts with a digit, its leading
     numeric portion animates from 0 up on scroll-in
     (initTrustMetricsCounters(), assets/js/main.js) — any trailing
     non-numeric text (+, %, /7, " hour") is static, appended after the
     count finishes. A non-numeric stat_value (e.g. "Millions",
     "Real-time" — 2026-08-05 content edit) has nothing to count from, so
     it just renders as its real text with no animation; same card markup/
     styling either way, only the PHP-side is-it-numeric check (below)
     decides whether the initial server-rendered content is "0" (about to
     count up) or the real value (nothing to animate). Deliberately no
     --signature-navy anywhere in this
     section (checked against itoi_reveal_markup()'s "metric cards" use
     case, which would normally fit here — skipped specifically because it
     draws in signature-blue brackets/tag, which conflicts with the
     brief's explicit "monochrome" requirement; only the plain
     itoi_reveal_class() fade is used, on the heading only). -->
<?php
$itoi_trust_heading = get_field( 'trust_section_heading', 'option' ) ?: 'Trusted by retailers who measure performance, not guesswork.';
$itoi_trust_metrics  = get_field( 'trust_metrics', 'option' );
if ( empty( $itoi_trust_metrics ) ) {
	$itoi_trust_metrics = array(
		array( 'stat_value' => 'Millions', 'stat_label' => 'of visitors analysed' ),
		array( 'stat_value' => 'Real-time', 'stat_label' => 'analytics' ),
		array( 'stat_value' => 'Multi-site', 'stat_label' => 'deployments' ),
		array( 'stat_value' => 'Enterprise', 'stat_label' => 'reporting' ),
	);
}
?>
<section class="border-b border-line bg-white px-5 py-section-lg min-[640px]:px-8" id="trustCredibility">
	<div class="mx-auto max-w-[1280px]">
		<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
			<h2 class="mb-10 max-w-[22ch] text-[clamp(26px,3vw,38px)] min-[640px]:mb-12"><?php echo esc_html( $itoi_trust_heading ); ?></h2>
		</div>

		<?php if ( ! empty( $itoi_trust_metrics ) ) : ?>
			<div class="mb-14 grid grid-cols-2 gap-4 min-[640px]:gap-6 min-[980px]:grid-cols-4 min-[980px]:gap-7" id="trustMetricsGrid">
				<?php foreach ( $itoi_trust_metrics as $itoi_tm_row ) :
					if ( empty( $itoi_tm_row['stat_value'] ) ) {
						continue;
					}
					// Only a leading-digit value actually counts up
					// (initTrustMetricsCounters(), main.js) — render its
					// real starting point as "0" so the count-up reads
					// naturally. A non-numeric value (e.g. "Millions",
					// "Real-time") has nothing to count from, so it's
					// rendered as its real final text straight away — no
					// "0" flash before JS/scroll ever touches it.
					$itoi_tm_is_numeric = (bool) preg_match( '/^\d/', $itoi_tm_row['stat_value'] );
					?>
					<div class="group flex flex-col items-start gap-2.5 rounded-2xl border border-line bg-white p-6 shadow-[0_4px_24px_-8px_rgba(14,17,22,0.08)] transition-[border-color,box-shadow,transform] duration-200 ease-out hover:-translate-y-1 hover:border-ink hover:shadow-[0_12px_32px_-12px_rgba(14,17,22,0.14)] min-[640px]:p-8">
						<div class="text-[36px] font-extrabold leading-none tracking-[-0.01em] text-ink min-[640px]:text-[44px]" data-trust-counter data-target="<?php echo esc_attr( $itoi_tm_row['stat_value'] ); ?>"><?php echo $itoi_tm_is_numeric ? '0' : esc_html( $itoi_tm_row['stat_value'] ); ?></div>
						<div class="text-[13px] font-semibold text-text-muted"><?php echo esc_html( $itoi_tm_row['stat_label'] ?? '' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php itoi_render_client_logo_row(); ?>
	</div>
</section>

<!-- ================= INDUSTRY SELECTOR ================= -->
<!-- Homepage personalization. Fully client-side (localStorage under
     `itoi_industry_selection`, JS in initIndustrySelector(), main.js) —
     there's no visitor session/cookie layer on this WP install, so the
     server always renders the generic (no-selection) state below and JS
     swaps to the personalized state on load if a prior selection exists,
     or on a button click. Both states, and every one of the 7 possible
     personalized panels, are fully server-rendered up front (same "PHP
     renders every state, JS only toggles visibility" pattern already used
     for the hero slides and industries carousel elsewhere on this page) —
     JS never builds markup or strings from scratch, it only shows/hides
     what's already here, so there's zero risk of it drifting out of sync
     with real ACF content or inventing copy.
     Part 2's solutions-grid reorder and Part 4's use-case pill swap are
     handled by the same init function but live physically in their own
     sections further down the page (id="exploreSolutions" / the Explore-
     by-use-case section) — see the comments there. -->
<?php
// Exact button order + industry→display-label map, per the task's Part 1
// spec (used verbatim, not the different order the Industries carousel
// further down this page happens to use).
$itoi_is_industries = array(
	'retail'                => 'Retail',
	'hospitality'            => 'Hospitality',
	'banking-finance'        => 'Banking & Finance',
	'government-councils'    => 'Government & Councils',
	'logistics-warehousing'  => 'Logistics & Warehousing',
	'stadiums-events'        => 'Stadiums & Events',
	'casinos-gaming'         => 'Casinos & Gaming',
);
// Part 3's exact real-case-study mapping — the other 4 industries fall
// through to the honest "Growing our footprint" fallback below, never a
// borrowed case study from a different industry.
$itoi_is_case_study_map = array(
	'retail'               => 'drakes-supermarkets',
	'government-councils'  => 'brisbane-city-council',
	'banking-finance'      => 'macquarie-bank',
);
?>
<section class="border-b border-line bg-hero-bg px-8 py-section-sm" id="industrySelector">
	<div class="mx-auto max-w-[1280px]">

		<div id="industrySelectorGeneric">
			<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
				<h2>What industry are you in?</h2>
			</div>
			<p class="mt-2.5 max-w-[54ch] text-[15px] text-text-muted">See the solutions, use cases and results most relevant to you.</p>
			<div class="mt-6 flex flex-wrap gap-2.5" id="industrySelectorButtons" role="group" aria-label="Select your industry">
				<?php foreach ( $itoi_is_industries as $itoi_is_slug => $itoi_is_label ) : ?>
					<button type="button" class="industry-select-btn rounded-full border-[1.5px] border-line bg-white px-5 py-2.5 text-[13.5px] font-bold text-ink transition-all hover:border-ink" data-industry-slug="<?php echo esc_attr( $itoi_is_slug ); ?>" data-industry-label="<?php echo esc_attr( $itoi_is_label ); ?>"><?php echo esc_html( $itoi_is_label ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<div id="industrySelectorPersonalized" class="hidden" style="opacity:0">
			<div class="flex flex-wrap items-center gap-3">
				<span class="text-[14.5px] text-text-muted">Currently viewing: <strong class="text-ink" id="industrySelectorCurrentLabel"></strong></span>
				<button type="button" class="rounded-full border-[1.5px] border-line bg-white px-4 py-[7px] text-[12.5px] font-bold text-ink transition-all hover:border-ink" id="industrySelectorChange">Change</button>
			</div>

			<div class="mt-6" id="industrySpotlightPanels">
				<?php
				foreach ( $itoi_is_industries as $itoi_is_slug => $itoi_is_label ) :
					$itoi_is_cs_slug = $itoi_is_case_study_map[ $itoi_is_slug ] ?? null;
					?>
					<div class="industry-spotlight-panel hidden" data-industry-slug="<?php echo esc_attr( $itoi_is_slug ); ?>">
						<?php
						if ( $itoi_is_cs_slug ) :
							$itoi_is_cs_post = get_page_by_path( $itoi_is_cs_slug, OBJECT, 'case_study' );
							if ( $itoi_is_cs_post && 'publish' === $itoi_is_cs_post->post_status ) :
								$itoi_is_cs_id       = $itoi_is_cs_post->ID;
								$itoi_is_cs_client   = get_field( 'client_name', $itoi_is_cs_id ) ?: get_the_title( $itoi_is_cs_id );
								$itoi_is_cs_hero_id  = get_field( 'hero_image', $itoi_is_cs_id );
								$itoi_is_cs_hero_url = $itoi_is_cs_hero_id ? wp_get_attachment_image_url( $itoi_is_cs_hero_id, 'medium' ) : '';
								$itoi_is_cs_hero_alt = $itoi_is_cs_hero_id ? ( get_post_meta( $itoi_is_cs_hero_id, '_wp_attachment_image_alt', true ) ?: $itoi_is_cs_client ) : '';
								$itoi_is_cs_video    = get_field( 'hero_video', $itoi_is_cs_id );
								$itoi_is_cs_url      = get_permalink( $itoi_is_cs_id );
								$itoi_is_cs_media    = itoi_media_cover( $itoi_is_cs_hero_url, $itoi_is_cs_video, $itoi_is_cs_hero_alt, 'h-full w-full object-cover', 'loading="lazy"' );
								?>
								<div class="flex flex-col overflow-hidden rounded-2xl border border-line bg-white min-[720px]:flex-row min-[720px]:items-stretch">
									<?php if ( $itoi_is_cs_media ) : ?>
										<div class="h-[180px] w-full flex-none overflow-hidden min-[720px]:h-auto min-[720px]:w-[320px]">
											<?php echo $itoi_is_cs_media; ?>
										</div>
									<?php endif; ?>
									<div class="flex flex-col items-start justify-center gap-2.5 px-6 py-6">
										<div class="text-[12.5px] font-bold uppercase tracking-wide text-teal-800">Featured case study</div>
										<h3 class="m-0 text-[22px]"><?php echo esc_html( $itoi_is_cs_client ); ?></h3>
										<a href="<?php echo esc_url( $itoi_is_cs_url ); ?>" class="mt-1 rounded-full bg-ink px-5 py-2.5 text-[13.5px] font-bold text-white transition-opacity hover:opacity-85">Read case study &rarr;</a>
									</div>
								</div>
								<?php
							endif;
						else :
							$itoi_is_ind_post = get_page_by_path( $itoi_is_slug, OBJECT, 'industry' );
							$itoi_is_ind_name = $itoi_is_ind_post ? ( get_field( 'name', $itoi_is_ind_post->ID ) ?: get_the_title( $itoi_is_ind_post ) ) : $itoi_is_label;
							?>
							<div class="flex flex-col items-start gap-2.5 rounded-2xl border border-dashed border-line bg-white px-6 py-7">
								<div class="text-[12.5px] font-bold uppercase tracking-wide text-teal-800">Growing our footprint</div>
								<h3 class="m-0 text-[20px]">We're expanding our work in <?php echo esc_html( $itoi_is_ind_name ); ?></h3>
								<p class="m-0 max-w-[58ch] text-[14px] text-text-muted">While we don't have a published case study in this industry yet, our platform is built on the same proven foundation across every sector we serve.</p>
								<a href="<?php echo esc_url( home_url( '/solutions/' ) ); ?>" class="industry-fallback-cta mt-1 rounded-full border-[1.5px] border-ink px-5 py-2.5 text-[13.5px] font-bold text-ink transition-all hover:bg-ink hover:text-white">Explore our solutions &rarr;</a>
							</div>
							<?php
						endif;
						?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

	</div>
</section>

<!-- ================= USE CASES ================= -->
<!-- Real content as of 2026-07-23 (see NOTES.md) — sourced from
     itoi_get_industry_use_cases( array( 'featured_only' => true ) ), the
     same featured_in_nav-curated set the mega menu's "Use Cases" dropdown
     uses (inc/use-cases.php), so both surfaces stay in sync from one
     ACF-editable selection.
     2026-07-24 addendum (Industry Selector, Part 4 of that spec) — a
     hidden per-industry pill row (id="useCaseDefaultRow" sibling,
     data-industry-slug) is rendered for each of the 7 industries below,
     using that industry's own real 6 use_cases rows (same
     itoi_get_industry_use_cases() helper, just not featured_only-filtered
     and grouped by industry_id instead — see inc/use-cases.php).
     initIndustrySelector() (main.js)
     swaps to the matching row when an industry is selected, replacing the
     cross-industry curated set above — never inventing new pills, only
     toggling which already-real set is visible. -->
<?php
$itoi_home_use_cases = itoi_get_industry_use_cases( array( 'featured_only' => true ) );
$itoi_all_use_cases  = itoi_get_industry_use_cases();
$itoi_uc_by_industry = array();
foreach ( $itoi_all_use_cases as $itoi_uc_row ) {
	$itoi_uc_by_industry[ $itoi_uc_row['industry_slug'] ][] = $itoi_uc_row;
}
?>
<?php if ( ! empty( $itoi_home_use_cases ) ) : ?>
	<section class="border-b border-line bg-hero-bg px-8 py-section-sm">
		<div class="mx-auto max-w-[1280px]">
			<div class="mb-[22px] flex flex-wrap items-baseline justify-between gap-2.5">
				<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
					<h2 class="m-0 text-xl">Explore by use case</h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/use-cases/' ) ); ?>" class="no-detect-reveal text-[12.5px] font-bold text-ink hover:underline">View all use cases &rarr;</a>
			</div>
			<div class="no-scrollbar flex gap-3 overflow-x-auto pb-1" tabindex="0" role="region" aria-label="Use cases, scroll for more" id="useCaseDefaultRow">
				<?php foreach ( $itoi_home_use_cases as $itoi_home_uc ) : ?>
					<a href="<?php echo esc_url( $itoi_home_uc['solution_url'] ); ?>" class="flex flex-none items-center gap-2.5 whitespace-nowrap rounded-xl border border-line bg-white px-[18px] py-3.5 text-[13.5px] font-bold transition-all hover:-translate-y-0.5 hover:border-ink">
						<span class="flex h-[26px] w-[26px] flex-none items-center justify-center rounded-[7px] bg-hero-bg text-xs text-teal-800">&#9723;</span>
						<?php echo esc_html( $itoi_home_uc['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
			<?php foreach ( $itoi_uc_by_industry as $itoi_uc_ind_slug => $itoi_uc_rows ) : ?>
				<div class="no-scrollbar hidden gap-3 overflow-x-auto pb-1 use-case-industry-row" data-industry-slug="<?php echo esc_attr( $itoi_uc_ind_slug ); ?>" tabindex="0" role="region" aria-label="Use cases, scroll for more">
					<?php foreach ( $itoi_uc_rows as $itoi_uc_row ) : ?>
						<a href="<?php echo esc_url( $itoi_uc_row['solution_url'] ); ?>" class="flex flex-none items-center gap-2.5 whitespace-nowrap rounded-xl border border-line bg-white px-[18px] py-3.5 text-[13.5px] font-bold transition-all hover:-translate-y-0.5 hover:border-ink">
							<span class="flex h-[26px] w-[26px] flex-none items-center justify-center rounded-[7px] bg-hero-bg text-xs text-teal-800">&#9723;</span>
							<?php echo esc_html( $itoi_uc_row['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<!-- ================= DELIVERY MODEL ================= -->
<!-- Rebuilt 2026-08-03 (see NOTES.md) — replaced the auto-rotating turnstile
     carousel (one step visible at a time) with a static full-width rail:
     all 6 steps render at once in a single <ol>, connected by a decorative
     ::before line (src/tailwind.css .delivery-rail). Below md the same list
     lays out as a vertical timeline instead of six columns — no separate
     mobile markup, just a media query, so there's nothing duplicated for
     screen readers. No JS: nothing here auto-advances or needs a timer. -->
<?php
$itoi_dm_eyebrow  = get_field( 'delivery_model_eyebrow', 'option' ) ?: 'THE DELIVERY MODEL';
$itoi_dm_headline = get_field( 'delivery_model_headline', 'option' ) ?: 'Every engagement, end-to-end.';
$itoi_dm_steps    = get_field( 'delivery_model_steps', 'option' );
$itoi_dm_count    = is_array( $itoi_dm_steps ) ? count( $itoi_dm_steps ) : 0;
?>
<?php if ( $itoi_dm_count > 0 ) : ?>
	<!-- wave 4 (liquid glass, see NOTES.md): bg-white -> bg-teal-900 + aurora-bg
	     so the active step's glass card has an actual rich background to
	     show through it. Eyebrow dot/text and the headline switched from
	     their light-background tokens (--signature/text-signature-dim,
	     default dark body text) to the matching dark-background tokens
	     (--signature-bright, text-white). -->
	<section class="no-detect-reveal aurora-bg border-b border-line bg-teal-900 px-8 py-section-lg" id="deliveryModel" aria-label="Delivery model">
		<div class="mx-auto w-full max-w-[1280px]">
			<div class="mb-2 flex items-center gap-2">
				<span class="h-2 w-2 rounded-full bg-signature-bright"></span>
				<span class="text-xs font-bold uppercase tracking-wide text-signature-bright"><?php echo esc_html( $itoi_dm_eyebrow ); ?></span>
			</div>
			<h2 class="mb-10 max-w-[20ch] text-[clamp(26px,3vw,38px)] text-white"><?php echo esc_html( $itoi_dm_headline ); ?></h2>

			<ol class="delivery-rail grid grid-cols-1 gap-x-6 md:grid-cols-6">
				<?php foreach ( $itoi_dm_steps as $itoi_dm_i => $itoi_dm_step ) :
					$itoi_dm_number = $itoi_dm_step['number'] ?? sprintf( '%02d', $itoi_dm_i + 1 );
					$itoi_dm_name   = $itoi_dm_step['step_name'] ?? '';
					$itoi_dm_active = 0 === $itoi_dm_i;
					?>
					<li class="delivery-step<?php echo $itoi_dm_active ? ' delivery-step--active' : ''; ?>">
						<span class="delivery-step__num" aria-hidden="true"><?php echo esc_html( $itoi_dm_number ); ?></span>
						<h3 class="delivery-step__title"><?php echo esc_html( $itoi_dm_name ); ?></h3>
						<p class="delivery-step__desc"><?php echo esc_html( $itoi_dm_step['description'] ?? '' ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
<?php endif; ?>

<!-- ================= ONE INTEGRATED PLATFORM ================= -->
<!-- Bug fix (recurring — see NOTES.md, this is the second occurrence after
     the Retail industry funnel card): the section's headline/sub-copy stay
     inside the passive scroll-reveal wrapper below, but the interactive
     platform-demo teaser (media frame, play button) and its Learn More
     trigger are a SIBLING block marked .no-detect-reveal, never nested
     inside .itoi-reveal — that nesting is what produced the stray amber
     "99%" tag/corner-bracket floating outside the frame's real boundaries.
     Root-cause fix, not a one-off patch: see the site-wide audit in
     NOTES.md confirming this is the only remaining unguarded instance. -->
<section class="border-t border-line bg-hero-bg px-8 py-section-lg text-center">
	<div class="mx-auto max-w-[1280px] <?php echo esc_attr( itoi_reveal_class() ); ?>">
		<h2 class="mx-auto mb-3 max-w-[18ch]">One integrated platform</h2>
		<p class="mx-auto mb-[22px] max-w-[46ch]">Control analytics, access control and patrol from a single cloud-based console.</p>
	</div>

	<div class="no-detect-reveal mx-auto max-w-[1280px]">
		<?php
		$itoi_teaser_img_id  = get_field( 'platform_demo_teaser_image', 'option' );
		$itoi_teaser_img_url = $itoi_teaser_img_id
			? wp_get_attachment_image_url( $itoi_teaser_img_id, 'large' )
			: get_template_directory_uri() . '/assets/images/platform-demo-teaser.jpg';
		?>
		<div class="relative mx-auto mb-5 max-w-[900px] overflow-hidden rounded-2xl border border-line shadow-[0_30px_60px_-30px_rgba(14,17,22,0.3)]">
			<div class="flex items-center gap-1.5 bg-[#e8eaed] px-3.5 py-2.5">
				<span class="h-[9px] w-[9px] rounded-full bg-[#ff5f57]" aria-hidden="true"></span>
				<span class="h-[9px] w-[9px] rounded-full bg-[#febc2e]" aria-hidden="true"></span>
				<span class="h-[9px] w-[9px] rounded-full bg-[#28c840]" aria-hidden="true"></span>
			</div>
			<div class="relative">
				<img src="<?php echo esc_url( $itoi_teaser_img_url ); ?>" alt="ITOI Platform dashboard preview, Operations view" class="block w-full" width="1600" height="900" loading="lazy">
				<button type="button" class="pd-play-btn absolute left-1/2 top-1/2 flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-2 border-white bg-[rgba(14,17,22,0.75)]" id="platformDemoPlayBtn" aria-label="Play &mdash; open the ITOI platform demo"></button>
			</div>
		</div>
		<button type="button" class="inline-block rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover" id="platformDemoLearnMoreBtn">Learn more</button>
	</div>
</section>

<?php get_template_part( 'template-parts/platform-demo-modal' ); ?>

<!-- ================= TRAFFIC-DEMO WIDGET (signature layer, §3) ================= -->
<!-- Interactive: dragging/clicking the slider updates the simulated hourly
     foot-traffic bars + labels below. Explicitly illustrative — never a live
     feed. ITOI's brand navy marks the active hour only; this is the second
     of the two permitted signature-color spots. -->
<section class="border-b border-line bg-white px-8 py-section-lg">
	<div class="mx-auto max-w-[1280px]">
		<div class="mb-2 flex items-center gap-2">
			<span class="h-2 w-2 rounded-full bg-signature"></span>
			<span class="text-xs font-bold uppercase tracking-wide text-signature-dim">Live Detection &mdash; illustrative</span>
		</div>
		<h2 class="mb-2 text-[clamp(26px,3vw,38px)]">See simulated foot-traffic by time of day</h2>
		<p class="mb-8 max-w-[46ch]">Illustrative data for demonstration purposes only &mdash; not a live feed from any ITOI site.</p>

		<div class="rounded-2xl border border-line bg-hero-bg p-6 min-[640px]:p-8">
			<div class="mb-6 flex items-end justify-between gap-6">
				<div>
					<div class="text-xs uppercase tracking-wide text-text-muted">Selected time</div>
					<div class="text-2xl font-extrabold text-ink" id="trafficTimeLabel">9:00 AM</div>
				</div>
				<div class="text-right">
					<div class="text-xs uppercase tracking-wide text-text-muted">Estimated density</div>
					<div class="text-2xl font-extrabold text-signature-dim" id="trafficDensityLabel">Moderate</div>
				</div>
			</div>

			<div class="mb-6 flex h-40 items-end gap-1.5" id="trafficBars" aria-hidden="true"></div>

			<input type="range" id="trafficSlider" min="0" max="17" value="3" step="1" class="w-full accent-signature" aria-label="Time of day, 6 AM to 11 PM">
			<div class="mt-2 flex justify-between text-[11px] text-text-muted">
				<span>6 AM</span><span>11 PM</span>
			</div>
		</div>
	</div>
</section>

<!-- ================= MEET OUR PRODUCTS (homepage) ================= -->
<!-- Rebuilt 2026-07-31, same day as the original turnstile version above
     (see NOTES.md, "Products turnstile — compact redesign" entry) — moved
     back to its original position (directly before "Why teams choose
     ITOI") and redesigned as a compact single-card-visible carousel
     instead of the full dark turnstile section, which took up too much
     homepage space. With exactly one product published (Aurora, as of
     this entry) this renders pixel-equivalent to the original single-card
     Aurora teaser it replaced back in the 2026-07-30 "Meet Aurora" build —
     prev/next arrows and dots only render once a 2nd product exists
     (initProductsCarousel() in main.js no-ops entirely below 2 cards, and
     PHP only prints the controls when $itoi_pr_count > 1), so there's zero
     visual/interaction cost today and it scales automatically once more
     products are added in wp-admin. Reuses each product's "Homepage
     Turnstile Card" fields (teaser_eyebrow/teaser_headline/
     teaser_supporting_line/teaser_photo/teaser_video/
     teaser_placeholder_caption/teaser_link_label) — same fields
     archive-product.php's tiles use. -->
<?php
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

<!-- ================= WHY CHOOSE ITOI ================= -->
<!-- Interactive: clicking a pill tab swaps the left panel content + right panel
     caption (itoiWhyTabs, localized from the why_choose_photos ACF repeater in
     inc/enqueue.php, read by initWhyChooseTabs() in main.js). Fully ACF-driven
     2026-08-04 (see NOTES.md) — every tab's label/title/description/bullets/
     CTA used to be hardcoded here + in main.js's old `whyData` array; both are
     gone, this repeater (+ why_choose_headline) is now the only source. PHP
     fallback array below matches the field's own ACF default_value exactly,
     same defensive pattern as $itoi_proof_stats further down this file, so
     this section still renders correctly even before Site Settings has ever
     been saved. -->
<?php
$itoi_why_rows = get_field( 'why_choose_photos', 'option' );
if ( empty( $itoi_why_rows ) ) {
	$itoi_why_rows = array(
		array(
			'tab_label'   => 'Plug-and-play setup',
			'title'       => 'Plug-and-play setup',
			'description' => 'Works with cameras you already have — no forklift upgrade required.',
			'bullets'     => array(
				array( 'text' => 'Bridges to existing CCTV, alarms and door hardware' ),
				array( 'text' => 'Cloud + on-device storage' ),
				array( 'text' => 'Automatic updates, no on-site maintenance visits' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
		array(
			'tab_label'   => 'Scalability',
			'title'       => 'Scales with every new site',
			'description' => 'Add a store or a stadium without adding a new system to manage.',
			'bullets'     => array(
				array( 'text' => 'One dashboard across every location' ),
				array( 'text' => 'No per-site licensing headaches' ),
				array( 'text' => 'Roll out in days, not months' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
		array(
			'tab_label'   => 'Integrations',
			'title'       => 'Integrates with what you run today',
			'description' => 'POS, staffing, and access systems connect without custom dev work.',
			'bullets'     => array(
				array( 'text' => 'Open API and FTP upload for POS data' ),
				array( 'text' => 'SSO / SAML for staff access' ),
				array( 'text' => 'Works alongside existing alarm panels' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
		array(
			'tab_label'   => 'Security & privacy',
			'title'       => 'Built with privacy in mind',
			'description' => 'Data handling designed around the Australian Privacy Act and the APPs.',
			'bullets'     => array(
				array( 'text' => 'Data stored in Australia' ),
				array( 'text' => 'Role-based access controls' ),
				array( 'text' => 'Clear retention and deletion policies' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
		array(
			'tab_label'   => '24/7 support',
			'title'       => '24/7 specialist support',
			'description' => 'A real person, day or night, when something needs attention.',
			'bullets'     => array(
				array( 'text' => 'In-house support, not outsourced' ),
				array( 'text' => 'Responses in minutes, not days' ),
				array( 'text' => 'Dedicated account specialist per client' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
	);
}
$itoi_why_headline = get_field( 'why_choose_headline', 'option' ) ?: 'Why teams choose ITOI';
$itoi_why_first     = $itoi_why_rows[0] ?? array();
?>
<section class="bg-teal-900 px-8 py-section-lg">
	<div class="mx-auto max-w-[1280px]">
		<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
			<h2 class="mb-8 text-center text-[clamp(26px,3vw,38px)] text-white"><?php echo esc_html( $itoi_why_headline ); ?></h2>
		</div>

		<div class="mb-9 flex flex-wrap justify-center gap-2.5" id="pillTabs">
			<?php foreach ( $itoi_why_rows as $itoi_why_i => $itoi_why_row ) : ?>
				<button class="pill-tab<?php echo 0 === $itoi_why_i ? ' active bg-white border-white text-teal-900' : ' border-white/25 text-white/70'; ?> rounded-[24px] border-[1.5px] px-[22px] py-[11px] text-[13.5px] font-bold transition-all hover:border-white/50 hover:text-white" data-tab="<?php echo (int) $itoi_why_i; ?>"><?php echo esc_html( $itoi_why_row['tab_label'] ?? '' ); ?></button>
			<?php endforeach; ?>
		</div>

		<div class="grid min-h-[420px] grid-cols-1 overflow-hidden rounded-[20px] min-[901px]:grid-cols-2">
			<div class="flex flex-col justify-center bg-teal-700 p-12 text-white" id="whyLeft">
				<h3 class="text-[clamp(22px,2.4vw,28px)] text-white"><?php echo esc_html( $itoi_why_first['title'] ?? '' ); ?></h3>
				<p class="text-white/80"><?php echo esc_html( $itoi_why_first['description'] ?? '' ); ?></p>
				<?php $itoi_why_first_bullets = $itoi_why_first['bullets'] ?? array(); ?>
				<?php if ( ! empty( $itoi_why_first_bullets ) ) : ?>
					<ul class="my-4 grid list-none gap-2.5 p-0">
						<?php foreach ( $itoi_why_first_bullets as $itoi_why_bullet ) : ?>
							<?php if ( empty( $itoi_why_bullet['text'] ) ) { continue; } ?>
							<li class="flex items-start gap-2.5 text-sm text-white/90">
								<span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-white/15 text-[11px]">&#10003;</span>
								<?php echo esc_html( $itoi_why_bullet['text'] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( ! empty( $itoi_why_first['cta_label'] ) ) : ?>
					<a href="<?php echo esc_url( $itoi_why_first['cta_url'] ?: '#' ); ?>" class="w-fit rounded-full border-[1.5px] border-white bg-white px-5 py-2.5 text-sm font-bold text-teal-900"><?php echo esc_html( $itoi_why_first['cta_label'] ); ?></a>
				<?php endif; ?>
			</div>
			<div class="relative flex min-h-[220px] items-center justify-center overflow-hidden bg-hero-bg p-6 text-center text-xs uppercase tracking-[0.06em] text-[#8b95a2]" id="whyRight">
				<?php
				$itoi_why_first_photo_id  = $itoi_why_first['photo'] ?? 0;
				$itoi_why_first_url       = $itoi_why_first_photo_id ? wp_get_attachment_image_url( $itoi_why_first_photo_id, 'large' ) : '';
				$itoi_why_first_alt       = $itoi_why_first_photo_id ? get_post_meta( $itoi_why_first_photo_id, '_wp_attachment_image_alt', true ) : '';
				$itoi_why_first_video     = $itoi_why_first['video'] ?? null;
				$itoi_why_first_video_url = ! empty( $itoi_why_first_video['url'] ) ? $itoi_why_first_video['url'] : '';
				?>
				<?php if ( $itoi_why_first_video_url ) : ?>
					<!-- autoplay attribute is paused by JS on load for a reduce-motion
					     visitor (initWhyChooseTabs() in main.js), same convention as
					     #heroBgVideo — the poster (or a static frame) shows instead. -->
					<video id="whyRightImg" class="absolute inset-0 h-full w-full object-cover" autoplay muted loop playsinline <?php echo $itoi_why_first_url ? 'poster="' . esc_url( $itoi_why_first_url ) . '"' : ''; ?>>
						<source src="<?php echo esc_url( $itoi_why_first_video_url ); ?>">
					</video>
				<?php elseif ( $itoi_why_first_url ) : ?>
					<img src="<?php echo esc_url( $itoi_why_first_url ); ?>" alt="<?php echo esc_attr( $itoi_why_first_alt ?: ( $itoi_why_first['title'] ?? '' ) ); ?>" id="whyRightImg" class="absolute inset-0 h-full w-full object-cover">
				<?php else : ?>
					<span id="whyRightImg">Photo — <?php echo esc_html( $itoi_why_first['tab_label'] ?? '' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<!-- ================= PROOF (stat tiles) ================= -->
<!-- Added 2026-07-30 (see NOTES.md), Phase 3 "strengthen credibility" —
     replaces the old "Our Clients" dark wordmark scroller that lived in
     this exact slot (removed; the full client roster is still browsable
     at /customers/, this section was never the only place logos lived).
     Numbers over logos: reuses the .highlight-panel-glass tile pattern
     already shipping on page-use-cases.php and single-solution.php (same
     visual language, no new CSS), editable via Site Settings → "Proof
     stats". PHP-level fallback array below matches that field's own ACF
     default_value exactly — same defensive pattern as inc/enqueue.php's
     hero_slides fallback, so this section renders correctly even before
     an admin has ever saved the Site Settings screen (options-page repeater
     fields don't populate wp_options until first saved). 2 of the 4
     default stats are already-confirmed real figures (PROJECT.md §6:
     facial recognition accuracy, detection speed); 2 are placeholders
     pending real figures — see the field's own instructions. -->
<?php
$itoi_proof_stats = get_field( 'proof_stats', 'option' );
if ( empty( $itoi_proof_stats ) ) {
	$itoi_proof_stats = array(
		array(
			'stat_value' => '99.87%',
			'stat_label' => 'Facial recognition accuracy',
		),
		array(
			'stat_value' => '<100ms',
			'stat_label' => 'Detection speed',
		),
		array(
			'stat_value' => '500+',
			'stat_label' => 'Sites protected',
		),
		array(
			'stat_value' => '24/7',
			'stat_label' => 'Real-time monitoring',
		),
	);
}
?>
<?php if ( ! empty( $itoi_proof_stats ) ) : ?>
	<section class="bg-ink px-8 py-section-md">
		<div class="mx-auto max-w-[1280px]">
			<div class="grid grid-cols-1 gap-4 min-[640px]:grid-cols-2 min-[980px]:grid-cols-4">
				<?php foreach ( $itoi_proof_stats as $itoi_stat ) : ?>
					<div class="highlight-panel-glass flex flex-col justify-center p-6 text-center min-[980px]:text-left">
						<div class="text-[36px] font-extrabold leading-tight text-white"><?php echo esc_html( $itoi_stat['stat_value'] ); ?></div>
						<div class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-white/60"><?php echo esc_html( $itoi_stat['stat_label'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ================= PARTNERS TEASER (homepage) ================= -->
<!-- Short, lightweight teaser for the About page's "Partners, not vendors"
     trust section (page-about.php, 4-flip-card grid) — NOT a duplicate of
     that section. Moved here (directly below the mega-hero, above "Explore
     by use case") per explicit instruction — was originally placed just
     above "Why Choose ITOI" further down the page; see NOTES.md for both
     placements' reasoning. Reuses partners_headline verbatim from the
     About page's own "Partners, Not Vendors" options page group (so the
     two headlines can never drift out of sync) and adds one new field on
     that SAME group, partners_teaser_line, for a condensed one-sentence
     version of the About page's fuller partners_intro paragraph —
     intentionally a different, shorter string, not that paragraph reused
     verbatim (see NOTES.md). -->
<?php
$itoi_pt_headline = get_field( 'partners_headline', 'option' ) ?: 'Partners, not vendors.';
$itoi_pt_line      = get_field( 'partners_teaser_line', 'option' );
?>
<?php if ( $itoi_pt_line ) : ?>
	<section class="border-b border-line bg-white px-8 py-section-sm">
		<div class="mx-auto flex max-w-[1280px] flex-col gap-3 min-[860px]:flex-row min-[860px]:items-center min-[860px]:justify-between min-[860px]:gap-8">
			<div class="flex flex-col gap-1 min-[860px]:flex-row min-[860px]:items-baseline min-[860px]:gap-3">
				<h2 class="m-0 whitespace-nowrap text-[18px] font-extrabold text-ink"><?php echo esc_html( $itoi_pt_headline ); ?></h2>
				<p class="m-0 max-w-[62ch] text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_pt_line ); ?></p>
			</div>
			<a href="<?php echo esc_url( home_url( '/about/#partners-not-vendors' ) ); ?>" class="flex-none whitespace-nowrap text-[14px] font-bold text-ink underline underline-offset-4 transition-opacity hover:opacity-70">Learn how we work &rarr;</a>
		</div>
	</section>
<?php endif; ?>

<!-- ================= TEAM TEASER (homepage) ================= -->
<!-- Added 2026-07-28 (see NOTES.md) — placed directly after the Partners-
     not-vendors teaser above since no "Our Story" section exists on this
     homepage to anchor next to; the two are both lightweight "who we are"
     trust teasers linking out to a fuller page, so grouping them back-to-
     back right after the hero reads better than splitting one near the
     top and the other after Why Choose ITOI further down.
     Reuses the `team_member` CPT's own fields (name, role, bio, photo) —
     same data page-team.php reads, so editing a member once updates both
     places. Only the 2 confirmed real members (Sean Kiely, Michael Stark
     — PROJECT.md §4/§6) are queried; no fabricated 3rd/4th person to force
     a denser grid like the reference. `post_status => 'any'` for the same
     reason page-team.php uses it: both posts are still `draft` pending
     real bio/photo, which here means "profile incomplete," not "hide this
     confirmed person." Photo/name/role-overlay treatment matches
     page-team.php's own cards exactly (gradient scrim + `.team-badge-glass`
     pill) so the two surfaces read as the same component family. -->
<?php
$itoi_tt_names = array( 'Sean Kiely', 'Michael Stark' );

$itoi_tt_all = new WP_Query(
	array(
		'post_type'      => 'team_member',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);
$itoi_tt_by_name = array();
if ( $itoi_tt_all->have_posts() ) {
	while ( $itoi_tt_all->have_posts() ) {
		$itoi_tt_all->the_post();
		$itoi_tt_by_name[ get_the_title() ] = get_the_ID();
	}
	wp_reset_postdata();
}
?>
<?php if ( ! empty( $itoi_tt_by_name ) ) : ?>
	<section class="border-b border-line bg-white px-8 py-section-md" id="teamTeaser">
		<div class="mx-auto max-w-[1280px]">
			<div class="grid grid-cols-1 gap-10 min-[900px]:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] min-[900px]:items-center min-[900px]:gap-16">
				<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
					<div class="mb-3 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Team</div>
					<h2 class="mb-4 max-w-[16ch] text-[clamp(26px,3vw,38px)]">The people <span class="text-text-muted">behind ITOI.</span></h2>
					<p class="mb-6 max-w-[46ch] text-text-muted">Our leadership brings 30+ years of retail operations experience, built on the store floor and in the boardroom &mdash; not just behind a sales desk. When you talk to ITOI, you&rsquo;re talking directly to the people running the deployment.</p>
					<a href="<?php echo esc_url( home_url( '/team/' ) ); ?>" class="no-detect-reveal w-fit text-[14px] font-bold text-ink underline underline-offset-4 transition-opacity hover:opacity-70">Meet the full team &rarr;</a>
				</div>

				<div class="no-detect-reveal grid grid-cols-2 gap-4 min-[640px]:gap-5">
					<?php foreach ( $itoi_tt_names as $itoi_tt_name ) : ?>
						<?php
						if ( ! isset( $itoi_tt_by_name[ $itoi_tt_name ] ) ) {
							continue;
						}
						$itoi_tt_id       = $itoi_tt_by_name[ $itoi_tt_name ];
						$itoi_tt_role     = get_field( 'role', $itoi_tt_id );
						$itoi_tt_photo_id = get_field( 'photo', $itoi_tt_id );
						$itoi_tt_photo    = $itoi_tt_photo_id ? wp_get_attachment_image_url( $itoi_tt_photo_id, 'team-photo' ) : '';
						$itoi_tt_video    = get_field( 'video', $itoi_tt_id );
						$itoi_tt_media    = itoi_media_cover( $itoi_tt_photo, $itoi_tt_video, $itoi_tt_name, 'absolute inset-0 h-full w-full object-cover' );
						?>
						<div class="relative aspect-square w-full overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)] after:absolute after:inset-0 after:bg-[linear-gradient(to_top,rgba(14,17,22,0.55)_0%,rgba(14,17,22,0)_45%)]">
							<?php if ( $itoi_tt_media ) : ?>
								<?php echo $itoi_tt_media; ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center text-center text-[9px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo TODO</div>
							<?php endif; ?>
							<div class="team-badge-glass absolute bottom-2.5 left-2.5 z-[2] max-w-[calc(100%-20px)] rounded-lg px-3 py-2">
								<h3 class="m-0 text-[15px] font-extrabold leading-tight"><?php echo esc_html( $itoi_tt_name ); ?></h3>
								<?php if ( $itoi_tt_role ) : ?>
									<div class="mt-0.5 text-[12.5px] font-semibold leading-tight"><?php echo esc_html( $itoi_tt_role ); ?></div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ================= INDUSTRIES ================= -->
<!-- Interactive: prev/next arrow buttons smooth-scroll the carousel by one
     tile (unchanged) — independent from the flip-card mechanic below, which
     triggers on hover/tap of an individual tile, not on the row scroll.
     Tile sizing standardized 2026-07-23 (see NOTES.md): every tile now uses
     the same aspect-[4/5] AND the same basis-width — previously alternated
     basis-[340px]/basis-[400px] by array index, which is why Casinos &
     Gaming rendered narrower than its neighbours despite the shared aspect
     ratio. Flip-card mechanic reuses the shared .flip-card component (see
     src/tailwind.css + initFlipCards() in assets/js/main.js — same
     component as the About page's "Partners, not vendors" section and the
     homepage solutions grid, not a new implementation). Front face: same
     photo + name-overlay look as before; the floating "Learn more" pill
     that used to straddle the tile's bottom edge is gone — that CTA now
     lives on the back face only, since a live front-face link would let a
     single accidental tap on touch carry a visitor straight to the
     industry page before they'd seen the back-face summary at all. Back
     face: the industry CPT's own `summary` field (already-populated real
     copy, not rewritten) + a "Learn more" link. `data-href` opts these
     cards into the click-through extension in initFlipCards() — the two
     other flip-card usages don't set it, so they're unaffected. -->
<section class="bg-hero-bg px-8 py-section-lg">
	<div class="mx-auto max-w-[1280px]">
		<div class="mb-[34px] flex items-end justify-between max-[980px]:flex-col max-[980px]:items-start max-[980px]:gap-4">
			<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
				<h2 class="max-w-[12ch] text-[clamp(26px,3vw,38px)]">Modern protection for any space</h2>
			</div>
			<div class="no-detect-reveal flex gap-2">
				<button class="flex h-11 w-11 items-center justify-center rounded-full border-[1.5px] border-ink bg-white text-base hover:bg-hero-bg" id="indPrev" aria-label="Previous industry">&larr;</button>
				<button class="flex h-11 w-11 items-center justify-center rounded-full border-[1.5px] border-ink bg-white text-base hover:bg-hero-bg" id="indNext" aria-label="Next industry">&rarr;</button>
			</div>
		</div>

		<div class="no-scrollbar flex gap-4 overflow-x-auto pb-9 [scroll-snap-type:x_mandatory]" id="indCarousel">
			<?php
			// PROJECT.md §4 locked slug order — pulled from the real `industry` CPT
			// (each entry's hero_image ACF field) rather than hardcoded here.
			$itoi_industry_slugs = array( 'retail', 'hospitality', 'casinos-gaming', 'banking-finance', 'government-councils', 'logistics-warehousing', 'stadiums-events' );
			// Per-photo object-position, checked individually against each real
			// image (not a blanket "center" guess) once the tiles were
			// standardized to one aspect ratio — see NOTES.md. Only
			// government-councils' native crop (a tall 3:4 chamber interior)
			// needed an override to keep the flag/upper gallery in frame
			// instead of centering into the empty floor; every other photo
			// reads correctly centered at 4:5.
			$itoi_object_position = array(
				'government-councils' => 'object-top',
			);
			foreach ( $itoi_industry_slugs as $itoi_slug ) :
				$itoi_post = get_page_by_path( $itoi_slug, OBJECT, 'industry' );
				if ( ! $itoi_post || 'publish' !== $itoi_post->post_status ) {
					continue;
				}
				$itoi_name      = get_field( 'name', $itoi_post->ID ) ?: get_the_title( $itoi_post );
				$itoi_summary   = get_field( 'summary', $itoi_post->ID );
				$itoi_photo_id  = get_field( 'hero_image', $itoi_post->ID );
				$itoi_photo_url = $itoi_photo_id ? wp_get_attachment_image_url( $itoi_photo_id, 'large' ) : '';
				$itoi_photo_alt = $itoi_photo_id ? get_post_meta( $itoi_photo_id, '_wp_attachment_image_alt', true ) : '';
				$itoi_hero_video = get_field( 'hero_video', $itoi_post->ID );
				$itoi_permalink = get_permalink( $itoi_post );
				$itoi_obj_pos   = isset( $itoi_object_position[ $itoi_slug ] ) ? $itoi_object_position[ $itoi_slug ] : 'object-center';
				?>
				<div class="flip-card shrink-0 basis-[300px] aspect-[4/5] min-[640px]:basis-[320px]" data-href="<?php echo esc_url( $itoi_permalink ); ?>">
					<div class="flip-card-inner">
						<div class="flip-card-front relative overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)] [scroll-snap-align:start] after:absolute after:inset-0 after:bg-[linear-gradient(to_top,rgba(14,17,22,0.65),rgba(14,17,22,0)_50%)]">
							<?php
							$itoi_carousel_media = itoi_media_cover(
								$itoi_photo_url,
								$itoi_hero_video,
								$itoi_photo_alt ?: $itoi_name,
								'absolute inset-0 h-full w-full object-cover ' . $itoi_obj_pos,
								'loading="lazy"'
							);
							?>
							<?php if ( $itoi_carousel_media ) : ?>
								<?php echo $itoi_carousel_media; ?>
							<?php else : ?>
								<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo — <?php echo esc_html( $itoi_name ); ?></div>
							<?php endif; ?>
							<div class="absolute bottom-5 left-5 z-[2] text-[19px] font-extrabold text-white"><?php echo esc_html( $itoi_name ); ?></div>
						</div>
						<div class="flip-card-back flex flex-col items-center justify-center gap-2.5 rounded-2xl bg-teal-900 px-5 py-6 text-center">
							<h3 class="m-0 text-[17px] text-white"><?php echo esc_html( $itoi_name ); ?></h3>
							<?php if ( $itoi_summary ) : ?>
								<p class="m-0 text-[13px] leading-[1.55] text-white/85"><?php echo esc_html( $itoi_summary ); ?></p>
							<?php endif; ?>
							<a href="<?php echo esc_url( $itoi_permalink ); ?>" class="mt-1 text-xs font-bold text-white underline">Learn more &rarr;</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ================= PROBLEM-FIRST (pain points) ================= -->
<!-- Replaces the old "Explore our solutions" carousel (id="exploreSolutions",
     8-slide sliding track of generic solution-category tiles — see NOTES.md
     for that component's own history) — removed entirely per explicit
     instruction (Step 3, enterprise CRO rebuild), not just visually hidden:
     initSolutionsCarousel() and its call site are gone from main.js, the
     .solutions-carousel* CSS block is gone from src/tailwind.css, same
     "remove the whole mechanic, not just the markup" precedent the Delivery
     Model turnstile removal set (NOTES.md, 2026-08-03).
     Static, non-interactive by design — this section's job is to state the
     problem before the rest of the page states the solution, not to browse
     a catalog. 4 fixed cards, not ACF-driven (like the hero CTA copy before
     it became editable, these 4 pain points are the literal brief content,
     not placeholder — a future session can move this to Site Settings if
     it needs to be editable, same pattern already used for the hero/trust
     sections' fields).
     Icons: 4 new simple line-art SVGs (itoi_problem_card_icon() below),
     matching the existing itoi_solution_builder_icon() convention exactly
     (24x24 viewBox, stroke="currentColor", stroke-width 1.6, round
     linecap/linejoin, no fill) — not reusing that function directly since
     none of its existing slugs (solution-category icons) map to these 4
     pain-point concepts; a new, narrowly-scoped icon set for this section
     only, same style language.
     Icon color is --ink (plain, monochrome), not --signature — these are
     ordinary content icons, not the Live Detection signature layer
     (claude.md's hard rule: signature navy is reserved for that layer's 4
     specific expressions only).
     The former #exploreSolutions in-page anchor (Industry Selector's
     "Explore our solutions →" fallback CTA, further up this file) now
     points at the real /solutions/ archive instead — see that CTA's own
     href below; initIndustrySelector()'s click-intercept in main.js
     already degrades gracefully when its target id doesn't exist (no code
     change needed there, confirmed). -->
<?php
/**
 * Simple line-art icon for this section's 4 pain-point cards. Narrowly
 * scoped to these 4 keys only — not a general-purpose icon registry, see
 * the section comment above for why this isn't itoi_solution_builder_icon().
 */
function itoi_problem_card_icon( $key, $classes = 'h-6 w-6' ) {
	$common = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';
	$paths  = array(
		'unauthorized-access'  => '<rect x="5" y="11" width="14" height="9" rx="1.5"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'vehicle-intelligence' => '<path d="M3 16v-3.5L5 8h14l2 4.5V16"/><path d="M3 16h18"/><circle cx="7.5" cy="17.5" r="1.5"/><circle cx="16.5" cy="17.5" r="1.5"/>',
		'false-alarm-reduction' => '<path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
		'multi-site-visibility' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
	);
	if ( ! isset( $paths[ $key ] ) ) {
		return;
	}
	printf( '<svg class="%s" viewBox="0 0 24 24" %s>%s</svg>', esc_attr( $classes ), $common, $paths[ $key ] ); // phpcs:ignore -- $common/$paths are hardcoded above, not user input
}

$itoi_problem_cards = array(
	array(
		'icon'  => 'unauthorized-access',
		'title' => 'Unauthorized access',
		'desc'  => 'Prevent access before incidents occur.',
	),
	array(
		'icon'  => 'vehicle-intelligence',
		'title' => 'Vehicle intelligence',
		'desc'  => 'Identify and track vehicles in real time.',
	),
	array(
		'icon'  => 'false-alarm-reduction',
		'title' => 'False alarm reduction',
		'desc'  => 'Reduce unnecessary security responses.',
	),
	array(
		'icon'  => 'multi-site-visibility',
		'title' => 'Multi-site visibility',
		'desc'  => 'Monitor every location from one platform.',
	),
);
?>
<section class="border-t border-line bg-white px-5 py-section-md min-[640px]:px-8" id="securityFailures">
	<div class="mx-auto max-w-[1280px]">
		<div class="relative mb-10 inline-block max-w-[46ch] <?php echo esc_attr( itoi_reveal_class() ); ?> min-[640px]:mb-12">
			<h2 class="mb-3 text-[clamp(26px,3vw,38px)]">Security failures are operational failures.</h2>
			<p class="m-0 text-[15px] text-text-muted min-[640px]:text-[16px]">Every access breach, false alarm, and visibility gap creates operational risk.</p>
		</div>

		<div class="grid grid-cols-1 gap-5 min-[640px]:grid-cols-2 min-[980px]:grid-cols-4 min-[980px]:gap-6">
			<?php foreach ( $itoi_problem_cards as $itoi_pc ) : ?>
				<div class="group flex flex-col items-start gap-4 rounded-2xl border border-line bg-white p-7 transition-[border-color,box-shadow,transform] duration-200 ease-out hover:-translate-y-1 hover:border-ink hover:shadow-[0_12px_32px_-12px_rgba(14,17,22,0.14)]">
					<span class="flex h-12 w-12 flex-none items-center justify-center rounded-xl bg-hero-bg text-ink transition-colors duration-200 ease-out group-hover:bg-ink group-hover:text-white">
						<?php itoi_problem_card_icon( $itoi_pc['icon'], 'h-6 w-6' ); ?>
					</span>
					<div>
						<h3 class="m-0 mb-1.5 text-[17px] font-extrabold leading-snug"><?php echo esc_html( $itoi_pc['title'] ); ?></h3>
						<p class="m-0 text-[13.5px] leading-[1.5] text-text-muted"><?php echo esc_html( $itoi_pc['desc'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ================= FINAL CTA BAND ================= -->
<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
	<div>
		<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Not sure where to start? Build your solution</h2>
		<p class="m-0 max-w-[34ch] text-white/60">Answer a few quick questions and get a tailored recommendation, ROI estimate and implementation timeline in minutes.</p>
	</div>
	<a href="<?php echo esc_url( home_url( '/solution-builder/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Build your solution &rarr;</a>
</div>

<?php
get_footer();
