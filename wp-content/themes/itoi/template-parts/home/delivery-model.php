<?php
/**
 * Delivery Model — static 4-step rail, no auto-advance/JS. Full rationale
 * (incl. why this replaced an earlier auto-rotating carousel, and the 6->4
 * step merge): docs/decisions/004-why-choose-and-delivery-model.md. Split
 * out of front-page.php 2026-08-06 (template-parts split) — markup/logic
 * unchanged.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_dm_eyebrow  = get_field( 'delivery_model_eyebrow', 'option' ) ?: 'THE DELIVERY MODEL';
$itoi_dm_headline = get_field( 'delivery_model_headline', 'option' ) ?: 'Every engagement, end-to-end.';
$itoi_dm_steps    = get_field( 'delivery_model_steps', 'option' );
$itoi_dm_count    = is_array( $itoi_dm_steps ) ? count( $itoi_dm_steps ) : 0;

if ( 0 === $itoi_dm_count ) {
	return;
}
?>
<!-- "Liquid glass" dark styling — see docs/decisions/004-why-choose-and-delivery-model.md -->
<section class="no-detect-reveal aurora-bg border-b border-line bg-teal-900 px-8 py-section-lg" id="deliveryModel" aria-label="Delivery model">
	<div class="mx-auto w-full max-w-[1280px]">
		<div class="mb-2 flex items-center gap-2">
			<span class="h-2 w-2 rounded-full bg-signature-bright"></span>
			<span class="text-xs font-bold uppercase tracking-wide text-signature-bright"><?php echo esc_html( $itoi_dm_eyebrow ); ?></span>
		</div>
		<h2 class="mb-10 max-w-[20ch] text-[clamp(26px,3vw,38px)] text-white"><?php echo esc_html( $itoi_dm_headline ); ?></h2>

		<ol class="delivery-rail grid grid-cols-1 gap-x-6 md:grid-cols-4">
			<?php
			foreach ( $itoi_dm_steps as $itoi_dm_i => $itoi_dm_step ) :
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
