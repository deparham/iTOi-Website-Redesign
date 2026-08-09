<?php
/**
 * Trust & Credibility — metric-card row + real client-logo marquee.
 * Deliberately monochrome (distinct from the dark-glass PROOF section that
 * used to exist). Full design rationale and implementation notes:
 * docs/decisions/001-trust-credibility-section.md. Split out of
 * front-page.php 2026-08-06 (template-parts split) — markup/logic unchanged.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_trust_heading = get_field( 'trust_section_heading', 'option' ) ?: 'Trusted by teams who measure performance, not guesswork.';
$itoi_trust_metrics = get_field( 'trust_metrics', 'option' );
if ( empty( $itoi_trust_metrics ) ) {
	// 2026-08-05: replaced 4 vague descriptors ("Millions", "Real-time",
	// "Multi-site", "Enterprise" — none of them real numbers, none of them
	// specific capabilities either) with precise capability statements — no
	// fabricated figures, per PROJECT.md §6's do-not-invent rule. Same-day
	// follow-up: the separate "PROOF" stat-tiles section further down the
	// page (which held the site's only 2 real confirmed hard numbers) was
	// cut per the homepage consolidation pass (see NOTES.md, "PROOF" ->
	// "Case study spotlight") — those 2 real numbers moved up into this
	// section instead of being lost, so this is now the one section
	// carrying both the real numbers and the capability statements.
	$itoi_trust_metrics = array(
		array(
			'stat_value' => '99.87%',
			'stat_label' => 'facial recognition accuracy',
		),
		array(
			'stat_value' => '<100ms',
			'stat_label' => 'detection speed',
		),
		array(
			'stat_value' => 'Multi-site',
			'stat_label' => 'reporting across every location',
		),
		array(
			'stat_value' => 'Australian',
			'stat_label' => 'deployment & support',
		),
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
				<?php
				foreach ( $itoi_trust_metrics as $itoi_tm_row ) :
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
