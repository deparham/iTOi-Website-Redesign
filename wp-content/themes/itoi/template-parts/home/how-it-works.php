<?php
/**
 * "How the platform works" (Observe / Understand / Act). Added 2026-08-05,
 * external improvement plan Phase 3, item 4. Presentation layer only —
 * groups the real 8 `solution` CPT categories under a 3-pillar story, per
 * docs/content-style-guide.md's pillar mapping. No CPT/taxonomy/URL change:
 * every link below points at a real, existing /solutions/{slug}/ or
 * /products/ page. Static, hardcoded content, not ACF-driven — same "move to
 * Site Settings later if it needs to be editable" reasoning used elsewhere
 * on this page. Icon function: itoi_pillar_icon() (inc/home-icons.php).
 * Split out of front-page.php 2026-08-06 (template-parts split).
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_pillar_cards = array(
	array(
		'icon'  => 'observe',
		'title' => 'Observe',
		'desc'  => "Capture what's happening on site — video, access events and sensor data from cameras, door hardware and Aurora's privacy-safe people counters.",
		'links' => array(
			array(
				'label' => 'Video & loss prevention',
				'url'   => '/solutions/cctv-video-loss-prevention/',
			),
			array(
				'label' => 'Security, access & inventory',
				'url'   => '/solutions/security-access-inventory/',
			),
			array(
				'label' => 'Sensory intelligence',
				'url'   => '/solutions/sensory-intelligence/',
			),
		),
	),
	array(
		'icon'  => 'understand',
		'title' => 'Understand',
		'desc'  => 'Turn that activity into operational information — foot-traffic and conversion reporting, cross-site comparisons, and POS or accounting integrations.',
		'links' => array(
			array(
				'label' => 'Intelligence & analytics',
				'url'   => '/solutions/intelligence-analytics/',
			),
			array(
				'label' => 'Back-of-house integration',
				'url'   => '/solutions/back-of-house-integration/',
			),
		),
	),
	array(
		'icon'  => 'act',
		'title' => 'Act',
		'desc'  => "Use the intelligence to improve operations — alerts, workforce and facility automation, and customer engagement that responds to what's actually happening.",
		'links' => array(
			array(
				'label' => 'Workforce, ops & robotics',
				'url'   => '/solutions/workforce-ops-robotics/',
			),
			array(
				'label' => 'Customer engagement & signage',
				'url'   => '/solutions/customer-engagement-signage/',
			),
		),
	),
);
?>
<section class="border-b border-line bg-white px-5 py-section-lg min-[640px]:px-8" id="howItWorks">
	<div class="mx-auto max-w-[1280px]">
		<div class="relative mb-10 <?php echo esc_attr( itoi_reveal_class() ); ?> min-[640px]:mb-12">
			<p class="m-0 mb-2 max-w-[46ch] text-[12.5px] font-bold uppercase tracking-[0.08em] text-text-muted">How the platform works</p>
			<h2 class="m-0 max-w-[30ch] text-[clamp(26px,3vw,38px)] min-[640px]:max-w-none min-[640px]:whitespace-nowrap">Observe, understand, act — one connected platform.</h2>
		</div>
		<div class="grid grid-cols-1 gap-5 min-[768px]:grid-cols-3 min-[768px]:gap-6">
			<?php foreach ( $itoi_pillar_cards as $itoi_pl ) : ?>
				<div class="flex flex-col items-start gap-4 rounded-2xl border border-line bg-white p-7">
					<span class="flex h-12 w-12 flex-none items-center justify-center rounded-xl bg-hero-bg text-ink">
						<?php itoi_pillar_icon( $itoi_pl['icon'], 'h-6 w-6' ); ?>
					</span>
					<div>
						<h3 class="m-0 mb-1.5 text-[19px] font-extrabold leading-snug"><?php echo esc_html( $itoi_pl['title'] ); ?></h3>
						<p class="m-0 text-[13.5px] leading-[1.5] text-text-muted"><?php echo esc_html( $itoi_pl['desc'] ); ?></p>
					</div>
					<ul class="mt-1 flex flex-col gap-1.5 p-0 list-none">
						<?php foreach ( $itoi_pl['links'] as $itoi_pl_link ) : ?>
							<li><a href="<?php echo esc_url( home_url( $itoi_pl_link['url'] ) ); ?>" class="text-[12.5px] font-semibold text-ink underline decoration-line underline-offset-2 hover:decoration-ink"><?php echo esc_html( $itoi_pl_link['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
		<p class="mt-8 text-[13px] text-text-muted">IT & network infrastructure runs underneath all three. <a href="<?php echo esc_url( home_url( '/solutions/' ) ); ?>" class="font-semibold text-ink underline">See all solutions &rarr;</a></p>
	</div>
</section>
