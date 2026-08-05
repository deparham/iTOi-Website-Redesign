<?php
/**
 * Styles & scripts. All wp_enqueue_* — no raw <link>/<script> in header.php/footer.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function itoi_enqueue_assets() {
	$style_path = ITOI_THEME_DIR . '/assets/css/style.css';
	$style_ver  = file_exists( $style_path ) ? filemtime( $style_path ) : ITOI_THEME_VERSION;

	wp_enqueue_style(
		'itoi-style',
		ITOI_THEME_URI . '/assets/css/style.css',
		array(),
		$style_ver
	);

	$script_path = ITOI_THEME_DIR . '/assets/js/main.js';
	$script_ver  = file_exists( $script_path ) ? filemtime( $script_path ) : ITOI_THEME_VERSION;

	wp_enqueue_script(
		'itoi-main',
		ITOI_THEME_URI . '/assets/js/main.js',
		array(),
		$script_ver,
		true
	);

	// Solution Builder popup handoff (footer.php + initFinder() in main.js) —
	// sitewide, since the popup itself is sitewide. Superseded 2026-07-24:
	// this used to localize itoiFinderData (resolved case-study/solution
	// routing for the old inline-results Find Your Fit flow); that routing
	// no longer exists (inc/finder.php deleted, see NOTES.md) — the popup
	// now just needs the destination URL to redirect to after step 7.
	wp_localize_script( 'itoi-main', 'itoiSolutionBuilderConfig', array( 'url' => home_url( '/solution-builder/' ) ) );

	// Mega-hero dot-nav slideshow (PROJECT.md §3 mechanic #2). 2026-07-23:
	// restored after an earlier session incorrectly removed the multi-slide
	// behaviour entirely — this time actually wired to the front end via
	// localize (the field existed before but no template ever read it; see
	// NOTES.md). initHeroSlideshow() in main.js falls back to a small
	// hardcoded array if this is empty, same defensive pattern as the rest
	// of this file, so the hero never renders broken if the options page
	// hasn't been saved yet.
	$itoi_hero_slides = array();
	if ( function_exists( 'get_field' ) ) {
		$itoi_hero_rows = get_field( 'hero_slides', 'option' );
		if ( is_array( $itoi_hero_rows ) ) {
			foreach ( $itoi_hero_rows as $itoi_hero_row ) {
				$itoi_partner_logo_id = $itoi_hero_row['partner_logo'] ?? 0;
				// Per-slide background photo/video (added 2026-07-23, see
				// NOTES.md) — each slide falls back to the plain gradient
				// independently when its own photo/video is empty, so this
				// is real per-row data, not a single sitewide override.
				$itoi_slide_photo_id  = $itoi_hero_row['photo'] ?? 0;
				$itoi_slide_video     = $itoi_hero_row['video'] ?? null;
				$itoi_hero_slides[]   = array(
					'headline'      => $itoi_hero_row['headline'] ?? '',
					'subcopy'       => $itoi_hero_row['subcopy'] ?? '',
					'isPartnership' => ! empty( $itoi_hero_row['is_partnership'] ),
					'partnerName'   => $itoi_hero_row['partner_name'] ?? '',
					'partnerLogo'   => $itoi_partner_logo_id ? wp_get_attachment_image_url( $itoi_partner_logo_id, 'medium' ) : '',
					'photo'         => $itoi_slide_photo_id ? wp_get_attachment_image_url( $itoi_slide_photo_id, 'large' ) : '',
					'photoAlt'      => $itoi_slide_photo_id ? ( get_post_meta( $itoi_slide_photo_id, '_wp_attachment_image_alt', true ) ?: '' ) : '',
					'video'         => ! empty( $itoi_slide_video['url'] ) ? $itoi_slide_video['url'] : '',
				);
			}
		}
	}
	wp_localize_script( 'itoi-main', 'itoiHeroSlides', $itoi_hero_slides );

	// "Why choose ITOI" split-panel (PROJECT.md §3 mechanic #6) — one row per
	// pill tab, same order as front-page.php's #pillTabs. Fully ACF-driven as
	// of 2026-08-04 (see NOTES.md) — used to be photo/video only, with the
	// title/description/bullets/CTA hardcoded in main.js's old `whyData`
	// array; that array is gone, this is now the only source, for both the
	// server-rendered tab 0 (front-page.php) and every tab-click swap
	// (initWhyChooseTabs() in main.js). Video takes priority over photo when
	// both are set, same convention as itoiHeroSlides above; photo also
	// doubles as the video's poster/reduce-motion fallback.
	$itoi_why_tabs = array();
	if ( function_exists( 'get_field' ) ) {
		$itoi_why_rows = get_field( 'why_choose_photos', 'option' );
		if ( is_array( $itoi_why_rows ) ) {
			foreach ( $itoi_why_rows as $itoi_why_row ) {
				$itoi_why_photo_id = $itoi_why_row['photo'] ?? 0;
				$itoi_why_video    = $itoi_why_row['video'] ?? null;
				$itoi_why_bullets  = array();
				foreach ( (array) ( $itoi_why_row['bullets'] ?? array() ) as $itoi_why_bullet_row ) {
					if ( ! empty( $itoi_why_bullet_row['text'] ) ) {
						$itoi_why_bullets[] = $itoi_why_bullet_row['text'];
					}
				}
				$itoi_why_tabs[] = array(
					'tabLabel'    => $itoi_why_row['tab_label'] ?? '',
					'title'       => $itoi_why_row['title'] ?? '',
					'description' => $itoi_why_row['description'] ?? '',
					'bullets'     => $itoi_why_bullets,
					'ctaLabel'    => $itoi_why_row['cta_label'] ?? '',
					'ctaUrl'      => $itoi_why_row['cta_url'] ?? '#',
					'photo'       => $itoi_why_photo_id ? wp_get_attachment_image_url( $itoi_why_photo_id, 'large' ) : '',
					'video'       => ! empty( $itoi_why_video['url'] ) ? $itoi_why_video['url'] : '',
				);
			}
		}
	}
	wp_localize_script( 'itoi-main', 'itoiWhyTabs', $itoi_why_tabs );

	// Solution Builder (/solution-builder/) — its own script, only loaded on
	// that page rather than folded into main.js, since the multi-step
	// engine/print-proposal logic is sizeable and page-specific. Matched by
	// slug, not is_page_template(): that page's template comes from the
	// page-{slug}.php hierarchy fallback with no _wp_page_template meta set,
	// which is_page_template() can't see (it only reads that meta value).
	if ( is_page( 'solution-builder' ) ) {
		$sb_script_path = ITOI_THEME_DIR . '/assets/js/solution-builder.js';
		$sb_script_ver  = file_exists( $sb_script_path ) ? filemtime( $sb_script_path ) : ITOI_THEME_VERSION;

		wp_enqueue_script(
			'itoi-solution-builder',
			ITOI_THEME_URI . '/assets/js/solution-builder.js',
			array(),
			$sb_script_ver,
			true
		);

		wp_localize_script(
			'itoi-solution-builder',
			'itoiSolutionBuilder',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'itoi_solution_builder' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'itoi_enqueue_assets' );

/**
 * Preload the two most widely-used Inter weights (400 body, 800 headings —
 * every template uses both above the fold). Paired with font-display:
 * optional in src/tailwind.css: preloading gives the real font the best
 * chance of being ready before that swap-decision window closes, so most
 * visits still render Inter on the first paint rather than falling back.
 */
function itoi_preload_fonts() {
	$itoi_fonts = array( 'inter-latin-400-normal.woff2', 'inter-latin-800-normal.woff2' );
	foreach ( $itoi_fonts as $itoi_font_file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( ITOI_THEME_URI . '/assets/fonts/' . $itoi_font_file )
		);
	}
}
add_action( 'wp_head', 'itoi_preload_fonts', 1 );
