<?php
/**
 * Trust & Credibility — eyebrow badge + heading, partner-logo row, then a
 * 4-column stat-card grid. Deliberately monochrome (distinct from the
 * dark-glass PROOF section that used to exist). Full design rationale and
 * implementation notes: docs/decisions/001-trust-credibility-section.md.
 * Split out of front-page.php 2026-08-06 (template-parts split).
 *
 * 2026-08-21 — rebuilt to an exact supplied spec, three passes same day:
 *
 * Pass 1: eyebrow badge added, section order changed to
 * eyebrow→heading→logos→stat-grid, stat cards restyled (light-gray fill,
 * no border/shadow, fixed min-height, space-between layout), scroll-
 * triggered reveal added. Kept itoi_render_client_logo_row()'s
 * auto-scrolling marquee (114 real client posts) for the logo row,
 * revealed as one fade-in unit.
 *
 * Pass 2 — exact spec for a single static 6-logo row, evenly spaced,
 * each with real content: checked first, and NONE of the 114 published
 * `client` posts has an image in its `logo` ACF field
 * (group_d803f591e25d.json — the field exists, just empty everywhere).
 * Per explicit instruction (asked rather than fabricating logo graphics
 * for real companies, or reusing unlicensed real-world logo images): the
 * 6 featured clients render as styled monochrome text wordmarks — real
 * client names, first 6 alphabetically (same ordering
 * itoi_render_client_logo_row() already uses) — and itoi_trust_logo_mark()
 * below DOES check each client's real `logo` field first, so this starts
 * rendering actual logo images the moment any of the 6 gets one uploaded
 * in wp-admin, with zero further template changes needed. Pass 2 also
 * asked about the heading font (spec wanted serif) and kept sans-serif
 * per CLAUDE.md's hard rule, pending confirmation.
 *
 * Pass 3 — a concrete reference screenshot supplied, confirmed: (a) yes,
 * add a real serif font for this one heading, explicit deliberate
 * exception to the sans-serif-only rule this time, not a guess — added a
 * scoped font-trust-serif utility (tailwind.config.js) and the
 * self-hosted Lora @font-face (src/tailwind.css); (b) no eyebrow badge in
 * the reference — removed, trust_section_eyebrow ACF field stays
 * registered, just unused here now.
 *
 * Pass 4 (same day, separate follow-up) — "change the font of the whole
 * website to that": Lora went from this one heading's contained exception
 * to the sitewide default (tailwind.config.js `sans` token itself, plus
 * CLAUDE.md/PROJECT.md §3 updated to match). font-trust-serif no longer
 * exists — this heading just inherits the sitewide font now, same as
 * everything else, so the class is gone from the markup below too.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_trust_heading = get_field( 'trust_section_heading', 'option' ) ?: 'Trusted by teams who measure performance, not guesswork.';
// itoi_get_trust_metrics() (inc/trust.php) — ACF's "Trust metrics" repeater
// is no longer capped at 4 (acf-json/group_73b8c1766c9f.json), so an editor
// can add as many as they want. Only the first 4 render here; if there are
// more, initTrustMetricsRotation() (homepage.js, fed the full list as
// itoiTrustMetrics via inc/enqueue.php) cross-fades the grid through the
// rest, 4 at a time — same "curated grid first, JS rotates through the
// rest" pattern already used for the client-logo row just above.
$itoi_trust_metrics = array_slice( itoi_get_trust_metrics(), 0, 4 );

// Client logo row. The editor's explicit pick comes first — Site Settings
// ▸ Trust & Stats ▸ "Client logos shown" (trust_logo_clients), rendered in
// the order they dragged them into. With nothing picked it falls back to
// the first 6 `client` posts alphabetically — same `orderby title ASC`
// convention as itoi_render_client_logo_row() (inc/customers-section.php),
// not hand-picked, so there's no "favourites" judgment call baked into a
// template file. Only the first 6 render server-side either way; when more
// than 6 are picked, initTrustLogoRotation() (homepage.js) cross-fades the
// row through the rest, 6 at a time (itoiTrustClients — inc/enqueue.php).
$itoi_trust_logo_ids = get_field( 'trust_logo_clients', 'option' );
if ( ! empty( $itoi_trust_logo_ids ) ) {
	$itoi_trust_logo_clients = get_posts(
		array(
			'post_type'      => 'client',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'post__in'       => array_map( 'intval', (array) $itoi_trust_logo_ids ),
			'orderby'        => 'post__in',
		)
	);
} else {
	$itoi_trust_logo_clients = get_posts(
		array(
			'post_type'      => 'client',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
}

/**
 * Real logo image if this client has one in its `logo` field; otherwise a
 * monochrome text wordmark badge as the honest fallback for a client with
 * no logo asset. Both share a fixed ~40px-tall bounding box and a common
 * baseline, so a row can mix real logos and wordmarks without the items
 * jumping in size, and swapping a client from one to the other later
 * doesn't shift the row's alignment. The image is height-locked (not
 * width-locked) with a generous max-width, so tall and wide logos both
 * land at the same optical height instead of a wide one being clamped
 * small by an aggressive max-width — that mismatch was the old look.
 * mix-blend-multiply drops the white/near-white matte that logo assets
 * uploaded without a transparent background carry, so they read as part
 * of the row rather than as a pale grey rectangle on the white section.
 *
 * 2026-08-31 follow-up — logos are rendered at full strength (no
 * grayscale, no opacity dimming) and a bit larger, per explicit request
 * to make them "more visible and bold". This is a deliberate step away
 * from this section's original "deliberately monochrome" brief; dial the
 * `grayscale opacity-*` classes back on here (and in homepage.js's
 * itemHtml) to restore the muted treatment.
 */
function itoi_trust_logo_mark( $client_id, $name ) {
	$logo_id = get_field( 'logo', $client_id );
	if ( $logo_id ) {
		$logo_url = wp_get_attachment_image_url( $logo_id, 'medium' );
		if ( $logo_url ) {
			printf(
				'<img src="%s" alt="%s" class="h-11 w-auto max-w-[240px] object-contain mix-blend-multiply min-[640px]:h-12">',
				esc_url( $logo_url ),
				esc_attr( $name )
			);
			return;
		}
	}
	// text-ink (not the old muted grey) to match the "more visible and
	// bold" pass on the real-logo branch above — full-contrast, so no
	// axe-core colour-contrast concern here. A client with no uploaded
	// logo still just shows its name, sized to sit level with the real
	// logos beside it.
	printf(
		'<span class="flex h-11 items-center whitespace-nowrap text-[18px] font-extrabold text-ink min-[640px]:h-12 min-[640px]:text-[20px]">%s</span>',
		esc_html( $name )
	);
}
?>
<section class="border-b border-line bg-white px-5 py-section-lg min-[640px]:px-8" id="trustCredibility">
	<div class="mx-auto max-w-[1280px]">
		<div class="relative mx-auto mb-10 max-w-[700px] text-center <?php echo esc_attr( itoi_reveal_class() ); ?> min-[640px]:mb-12">
			<?php // font-trust-serif class removed 2026-08-21 -- Lora is now the sitewide default (tailwind.config.js), no per-heading override needed any more. ?>
			<h2 class="mx-auto max-w-[26ch] text-[clamp(28px,3.4vw,36px)] font-semibold text-ink"><?php echo esc_html( $itoi_trust_heading ); ?></h2>
		</div>

		<?php if ( ! empty( $itoi_trust_logo_clients ) ) : ?>
			<?php // transition-opacity: initTrustLogoRotation() (homepage.js) cross-fades
			// this row's content between chunks of 6 real clients (all published
			// `client` posts, localized as itoiTrustClients — inc/enqueue.php) —
			// same 5000ms cadence the mega-hero slideshow already uses elsewhere on
			// this page, for one consistent "how long things sit before rotating"
			// feel rather than a second, different-feeling timing. No-op (row just
			// stays static) when there are 6 or fewer clients total, or under
			// prefers-reduced-motion. ?>
			<?php /* 2026-08-31 mobile fix: gap-x-10/gap-y-8 + pt-6/pb-6 per item
			were tuned for the desktop single-row layout (min-[640px]:flex-nowrap)
			— below that breakpoint the row wraps, and those same desktop-sized
			gaps/padding between 6 short text names stacked mostly one-per-line
			added up to ~400px of mostly-empty height on a 375px phone (confirmed
			via a real capture). Tighter gap/padding below 640px only; desktop
			(flex-nowrap, single row) is unchanged. */ ?>
			<div class="mb-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 transition-opacity duration-700 min-[640px]:mb-14 min-[640px]:flex-nowrap min-[640px]:justify-between min-[640px]:gap-x-10 min-[640px]:gap-y-8" id="trustLogoRow">
				<?php foreach ( $itoi_trust_logo_clients as $itoi_tl_index => $itoi_tl_client ) : ?>
					<div class="itoi-stagger-item relative flex flex-none items-center justify-center pb-2 pt-2 min-[640px]:pb-6 min-[640px]:pt-6" style="--stagger-distance:8px">
						<span class="absolute left-0 top-0 h-1 w-1 rounded-full bg-teal-500" aria-hidden="true"></span>
						<span class="absolute bottom-0 left-0 h-1 w-1 rounded-full bg-teal-500" aria-hidden="true"></span>
						<?php itoi_trust_logo_mark( $itoi_tl_client->ID, get_the_title( $itoi_tl_client ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $itoi_trust_metrics ) ) : ?>
			<?php // transition-opacity: initTrustMetricsRotation() (homepage.js)
			// cross-fades this grid's content between chunks of 4 real metrics
			// (the full "Trust metrics" repeater, localized as itoiTrustMetrics
			// — inc/enqueue.php) — same 5000ms cadence/700ms fade as the
			// client-logo row above. No-op (grid just stays static on this
			// first chunk) when there are 4 or fewer metrics total, or under
			// prefers-reduced-motion. ?>
			<div class="grid grid-cols-1 gap-6 transition-opacity duration-700 min-[640px]:grid-cols-2 min-[980px]:grid-cols-4 min-[980px]:gap-7" id="trustMetricsGrid">
				<?php foreach ( $itoi_trust_metrics as $itoi_tm_row ) :
					// Initial (pre-count-up) render — mirrors
					// animateCounter()'s regex exactly (assets/js/homepage.js):
					// a value with a digit anywhere renders as
					// prefix+"0"+suffix (e.g. "<100ms" -> "<0ms") so the
					// server-rendered state matches what JS animates FROM,
					// no flash of the final value before the count-up starts.
					// A value with no digits at all (e.g. "Multi-site") has
					// nothing to count from, so it renders as its real final
					// text straight away.
					$itoi_tm_initial = $itoi_tm_row['stat_value'];
					if ( preg_match( '/^([^\d]*)([\d,]*\.?\d+)(.*)$/', $itoi_tm_row['stat_value'], $itoi_tm_match ) ) {
						$itoi_tm_initial = $itoi_tm_match[1] . '0' . $itoi_tm_match[3];
					}
					?>
					<div class="itoi-stagger-item flex min-h-[150px] flex-col justify-between rounded-[14px] bg-[#F4F3F6] p-7" style="--stagger-distance:16px">
						<div class="text-[32px] font-extrabold leading-none tracking-[-0.01em] text-[#111]" data-trust-counter data-target="<?php echo esc_attr( $itoi_tm_row['stat_value'] ); ?>"><?php echo esc_html( $itoi_tm_initial ); ?></div>
						<div class="line-clamp-2 text-[14px] text-[#6B7280]"><?php echo esc_html( $itoi_tm_row['stat_label'] ?? '' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
