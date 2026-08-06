<?php
/**
 * One Integrated Platform — teaser image/video + "Learn more"/play-button
 * triggers for the platform demo modal (template-parts/platform-demo-modal.php).
 * Restored to the homepage 2026-08-05 ("10/10" pass), same reasoning as
 * "Meet Our Products". Split out of front-page.php 2026-08-06
 * (template-parts split).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="border-t border-line bg-hero-bg px-8 py-section-lg text-center" id="integratedPlatform">
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
