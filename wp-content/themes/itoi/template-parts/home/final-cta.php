<?php
/**
 * Final CTA band. id="finalCtaBand" — watched by initFinder()
 * (assets/js/main.js) so the floating "Build your solution" trigger
 * (footer.php) hides itself while this identically-worded band is on
 * screen, instead of the two stacking visually in the same bottom-right
 * corner. Split out of front-page.php 2026-08-06 (template-parts split) —
 * markup unchanged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="finalCtaBand" class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
	<div>
		<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Not sure where to start? Build your solution</h2>
		<p class="m-0 max-w-[34ch] text-white/60">Answer a few quick questions and get a tailored recommendation, ROI estimate and implementation timeline in minutes.</p>
	</div>
	<a href="<?php echo esc_url( home_url( '/solution-builder/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Build your solution &rarr;</a>
</div>
