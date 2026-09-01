<?php
/**
 * Core Outcomes. Added 2026-08-05, external improvement plan Phase 3, item 5
 * — leads with results instead of technology. bg-hero-bg (not bg-white) to
 * keep the section-background alternation rhythm (PROJECT.md §3) since the
 * section immediately above is bg-white. Icon function: itoi_outcome_icon()
 * (inc/home-icons.php). Split out of front-page.php 2026-08-06
 * (template-parts split).
 *
 * 2026-08-21 ("make this section smaller and something like a roller bar"):
 * condensed from a 2x3 grid of full cards (icon + title + description, each
 * ~200px tall) down to a single compact auto-scrolling pill row. Reuses the
 * exact same marquee mechanic already established for client-logo rows
 * (itoi_render_client_logo_row(), inc/customers-section.php;
 * single-industry.php's Customers section) — .longform-marquee-viewport/
 * -track/-group classes + .animate-itoi-marquee + initLongformMarquees()
 * (core.js, already loaded sitewide) — rather than inventing a second
 * scrolling mechanism. Section padding dropped from py-section-lg to
 * py-section-sm and the heading a size step smaller to match the section's
 * now much smaller footprint.
 *
 * Unlike the client-logo marquee's plain-text pills, these pills are real
 * links to each solution page (same hrefs the old card grid used) — the
 * duplicate aria-hidden copy (second half of the seamless loop) gets
 * tabindex="-1" on each of its links so a keyboard user doesn't tab through
 * two identical stops for the same destination; the visible/AT-reachable
 * copy is the primary group. Descriptions from the old cards are dropped —
 * they don't fit a scannable ticker item — titles alone stay descriptive
 * enough on their own (e.g. "Improve site security").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 2026-08-05 ("10/10" pass, see NOTES.md): retitled to match the exact
// wording requested — outcome-first, not category-first ("Improve site
// security" rather than "Strengthen site security", etc.) — real solution
// links unchanged.
$itoi_outcome_cards = array(
	array( 'icon' => 'security', 'title' => 'Improve site security', 'url' => '/solutions/security-access-inventory/' ),
	array( 'icon' => 'conversion', 'title' => 'Understand visitor behaviour', 'url' => '/solutions/intelligence-analytics/' ),
	array( 'icon' => 'compare', 'title' => 'Compare location performance', 'url' => '/solutions/intelligence-analytics/' ),
	array( 'icon' => 'staffing', 'title' => 'Optimise staffing and resources', 'url' => '/solutions/workforce-ops-robotics/' ),
	array( 'icon' => 'automate', 'title' => 'Automate operational responses', 'url' => '/solutions/back-of-house-integration/' ),
	array( 'icon' => 'blind-spots', 'title' => 'Reduce unnecessary alerts', 'url' => '/solutions/cctv-video-loss-prevention/' ),
);

/**
 * Renders the pill row once, either as the primary (tabbable, AT-reachable)
 * copy or the duplicate (tabindex="-1", the group div carries aria-hidden)
 * — see docblock above for why the two copies can't share identical markup
 * the way the plain-text client-logo pills do.
 */
function itoi_render_outcome_pills( $cards, $is_duplicate ) {
	foreach ( $cards as $itoi_oc ) :
		?>
		<a href="<?php echo esc_url( home_url( $itoi_oc['url'] ) ); ?>" <?php echo $is_duplicate ? 'tabindex="-1"' : ''; ?> class="inline-flex flex-none items-center gap-2 whitespace-nowrap rounded-full border border-line bg-white px-5 py-2.5 text-[13.5px] font-bold text-ink shadow-sm transition-colors duration-200 ease-out hover:border-ink hover:bg-hero-bg">
			<span class="flex h-4 w-4 flex-none items-center justify-center text-text-muted"><?php itoi_outcome_icon( $itoi_oc['icon'], 'h-4 w-4' ); ?></span>
			<?php echo esc_html( $itoi_oc['title'] ); ?>
		</a>
		<?php
	endforeach;
}
?>
<section class="border-b border-line bg-hero-bg px-5 py-section-sm min-[640px]:px-8" id="coreOutcomes">
	<div class="mx-auto max-w-[1280px]">
		<div class="relative mb-6 <?php echo esc_attr( itoi_reveal_class() ); ?>">
			<h2 class="m-0 max-w-[30ch] text-[clamp(22px,2.4vw,30px)] min-[640px]:max-w-none min-[640px]:whitespace-nowrap">Outcomes, not just technology.</h2>
		</div>
		<div class="longform-marquee-viewport overflow-hidden">
			<div class="longform-marquee-track flex w-max animate-itoi-marquee">
				<div class="longform-marquee-group flex flex-none gap-3 pr-3" data-copy="primary">
					<?php itoi_render_outcome_pills( $itoi_outcome_cards, false ); ?>
				</div>
				<div class="longform-marquee-group flex flex-none gap-3 pr-3" data-copy="duplicate" aria-hidden="true">
					<?php itoi_render_outcome_pills( $itoi_outcome_cards, true ); ?>
				</div>
			</div>
		</div>
	</div>
</section>
