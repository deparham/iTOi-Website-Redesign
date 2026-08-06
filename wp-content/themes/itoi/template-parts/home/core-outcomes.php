<?php
/**
 * Core Outcomes. Added 2026-08-05, external improvement plan Phase 3, item 5
 * — leads with results instead of technology, directly below the pillar
 * section above. Same static/hardcoded pattern and icon convention as How
 * The Platform Works. bg-hero-bg (not bg-white) to keep the section-
 * background alternation rhythm (PROJECT.md §3) since the section
 * immediately above is bg-white. Icon function: itoi_outcome_icon()
 * (inc/home-icons.php). Split out of front-page.php 2026-08-06
 * (template-parts split).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 2026-08-05 ("10/10" pass, see NOTES.md): retitled to match the exact
// wording requested — outcome-first, not category-first ("Improve site
// security" rather than "Strengthen site security", etc.) — real solution
// links unchanged.
$itoi_outcome_cards = array(
	array( 'icon' => 'security', 'title' => 'Improve site security', 'desc' => 'Manage access, inventory and biometric entry from one system.', 'url' => '/solutions/security-access-inventory/' ),
	array( 'icon' => 'conversion', 'title' => 'Understand visitor behaviour', 'desc' => 'Connect foot-traffic and activity data to see what\'s actually happening on site.', 'url' => '/solutions/intelligence-analytics/' ),
	array( 'icon' => 'compare', 'title' => 'Compare location performance', 'desc' => 'Report across every location from a single, consistent dashboard.', 'url' => '/solutions/intelligence-analytics/' ),
	array( 'icon' => 'staffing', 'title' => 'Optimise staffing and resources', 'desc' => 'Match rosters to real occupancy and activity patterns, not guesswork.', 'url' => '/solutions/workforce-ops-robotics/' ),
	array( 'icon' => 'automate', 'title' => 'Automate operational responses', 'desc' => 'Hand off routine facility and back-of-house tasks to the platform.', 'url' => '/solutions/back-of-house-integration/' ),
	array( 'icon' => 'blind-spots', 'title' => 'Reduce unnecessary alerts', 'desc' => 'Cover every zone with video and loss-prevention monitoring that cuts false positives.', 'url' => '/solutions/cctv-video-loss-prevention/' ),
);
?>
<section class="border-b border-line bg-hero-bg px-5 py-section-lg min-[640px]:px-8" id="coreOutcomes">
	<div class="mx-auto max-w-[1280px]">
		<div class="relative mb-10 <?php echo esc_attr( itoi_reveal_class() ); ?> min-[640px]:mb-12">
			<h2 class="m-0 max-w-[30ch] text-[clamp(26px,3vw,38px)] min-[640px]:max-w-none min-[640px]:whitespace-nowrap">Outcomes, not just technology.</h2>
		</div>
		<div class="grid grid-cols-1 gap-5 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3 min-[980px]:gap-6">
			<?php foreach ( $itoi_outcome_cards as $itoi_oc ) : ?>
				<a href="<?php echo esc_url( home_url( $itoi_oc['url'] ) ); ?>" class="group flex flex-col items-start gap-4 rounded-2xl border border-line bg-white p-7 transition-[border-color,box-shadow,transform] duration-200 ease-out hover:-translate-y-1 hover:border-ink hover:shadow-[0_12px_32px_-12px_rgba(14,17,22,0.14)]">
					<span class="flex h-12 w-12 flex-none items-center justify-center rounded-xl bg-hero-bg text-ink transition-colors duration-200 ease-out group-hover:bg-ink group-hover:text-white">
						<?php itoi_outcome_icon( $itoi_oc['icon'], 'h-6 w-6' ); ?>
					</span>
					<div>
						<h3 class="m-0 mb-1.5 text-[17px] font-extrabold leading-snug"><?php echo esc_html( $itoi_oc['title'] ); ?></h3>
						<p class="m-0 text-[13.5px] leading-[1.5] text-text-muted"><?php echo esc_html( $itoi_oc['desc'] ); ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
