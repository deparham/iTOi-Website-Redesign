<?php
/**
 * Traffic-Demo Widget (signature layer, PROJECT.md §3). Restored to the
 * homepage 2026-08-05 ("10/10" pass), same reasoning as "Meet Our Products"
 * — then removed from view again same day, per explicit instruction, but
 * kept as a real Site Settings toggle (show_traffic_demo, default OFF)
 * rather than deleted outright, so it can be switched back on later with no
 * code change. Split out of front-page.php 2026-08-06 (template-parts
 * split).
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! get_field( 'show_traffic_demo', 'option' ) ) {
	return;
}
?>
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
