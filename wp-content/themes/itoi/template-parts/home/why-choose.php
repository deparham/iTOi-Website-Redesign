<?php
/**
 * Why Choose ITOI — pill-tab click swaps left panel + right caption, fully
 * ACF-driven (why_choose_photos). Full rationale:
 * docs/decisions/004-why-choose-and-delivery-model.md. Split out of
 * front-page.php 2026-08-06 (template-parts split) — markup/logic unchanged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_why_rows = get_field( 'why_choose_photos', 'option' );
if ( empty( $itoi_why_rows ) ) {
	$itoi_why_rows = array(
		array(
			'tab_label'   => 'Plug-and-play setup',
			'title'       => 'Plug-and-play setup',
			'description' => 'Works with cameras you already have — no forklift upgrade required.',
			'bullets'     => array(
				array( 'text' => 'Bridges to existing CCTV, alarms and door hardware' ),
				array( 'text' => 'Cloud + on-device storage' ),
				array( 'text' => 'Automatic updates, no on-site maintenance visits' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
		array(
			'tab_label'   => 'Scalability',
			'title'       => 'Scales with every new site',
			'description' => 'Add a store or a stadium without adding a new system to manage.',
			'bullets'     => array(
				array( 'text' => 'One dashboard across every location' ),
				array( 'text' => 'No per-site licensing headaches' ),
				array( 'text' => 'Roll out in days, not months' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
		array(
			'tab_label'   => 'Integrations',
			'title'       => 'Integrates with what you run today',
			'description' => 'POS, staffing, and access systems connect without custom dev work.',
			'bullets'     => array(
				array( 'text' => 'Open API and FTP upload for POS data' ),
				array( 'text' => 'SSO / SAML for staff access' ),
				array( 'text' => 'Works alongside existing alarm panels' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
		array(
			'tab_label'   => 'Security & privacy',
			'title'       => 'Built with privacy in mind',
			'description' => 'Data handling designed around the Australian Privacy Act and the APPs.',
			'bullets'     => array(
				array( 'text' => 'Data stored in Australia' ),
				array( 'text' => 'Role-based access controls' ),
				array( 'text' => 'Clear retention and deletion policies' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
		array(
			'tab_label'   => '24/7 support',
			'title'       => '24/7 specialist support',
			'description' => 'A real person, day or night, when something needs attention.',
			'bullets'     => array(
				array( 'text' => 'In-house support, not outsourced' ),
				array( 'text' => 'Responses in minutes, not days' ),
				array( 'text' => 'Dedicated account specialist per client' ),
			),
			'cta_label'   => 'Learn more',
			'cta_url'     => '#',
		),
	);
}
$itoi_why_headline = get_field( 'why_choose_headline', 'option' ) ?: 'Why teams choose ITOI';
$itoi_why_first     = $itoi_why_rows[0] ?? array();
?>
<section class="bg-teal-900 px-8 py-section-lg">
	<div class="mx-auto max-w-[1280px]">
		<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
			<h2 class="mb-8 text-center text-[clamp(26px,3vw,38px)] text-white"><?php echo esc_html( $itoi_why_headline ); ?></h2>
		</div>

		<div class="mb-9 flex flex-wrap justify-center gap-2.5" id="pillTabs">
			<?php foreach ( $itoi_why_rows as $itoi_why_i => $itoi_why_row ) : ?>
				<button class="pill-tab<?php echo 0 === $itoi_why_i ? ' active bg-white border-white text-teal-900' : ' border-white/25 text-white/70'; ?> rounded-[24px] border-[1.5px] px-[22px] py-[11px] text-[13.5px] font-bold transition-all hover:border-white/50 hover:text-white" data-tab="<?php echo (int) $itoi_why_i; ?>"><?php echo esc_html( $itoi_why_row['tab_label'] ?? '' ); ?></button>
			<?php endforeach; ?>
		</div>

		<div class="grid min-h-[420px] grid-cols-1 overflow-hidden rounded-[20px] min-[901px]:grid-cols-2">
			<div class="flex flex-col justify-center bg-teal-700 p-12 text-white" id="whyLeft">
				<h3 class="text-[clamp(22px,2.4vw,28px)] text-white"><?php echo esc_html( $itoi_why_first['title'] ?? '' ); ?></h3>
				<p class="text-white/80"><?php echo esc_html( $itoi_why_first['description'] ?? '' ); ?></p>
				<?php $itoi_why_first_bullets = $itoi_why_first['bullets'] ?? array(); ?>
				<?php if ( ! empty( $itoi_why_first_bullets ) ) : ?>
					<ul class="my-4 grid list-none gap-2.5 p-0">
						<?php foreach ( $itoi_why_first_bullets as $itoi_why_bullet ) : ?>
							<?php if ( empty( $itoi_why_bullet['text'] ) ) { continue; } ?>
							<li class="flex items-start gap-2.5 text-sm text-white/90">
								<span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-white/15 text-[11px]">&#10003;</span>
								<?php echo esc_html( $itoi_why_bullet['text'] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( ! empty( $itoi_why_first['cta_label'] ) ) : ?>
					<a href="<?php echo esc_url( $itoi_why_first['cta_url'] ?: '#' ); ?>" class="w-fit rounded-full border-[1.5px] border-white bg-white px-5 py-2.5 text-sm font-bold text-teal-900"><?php echo esc_html( $itoi_why_first['cta_label'] ); ?></a>
				<?php endif; ?>
			</div>
			<div class="relative flex min-h-[220px] items-center justify-center overflow-hidden bg-hero-bg p-6 text-center text-xs uppercase tracking-[0.06em] text-[#8b95a2]" id="whyRight">
				<?php
				$itoi_why_first_photo_id  = $itoi_why_first['photo'] ?? 0;
				$itoi_why_first_url       = $itoi_why_first_photo_id ? wp_get_attachment_image_url( $itoi_why_first_photo_id, 'large' ) : '';
				$itoi_why_first_alt       = $itoi_why_first_photo_id ? get_post_meta( $itoi_why_first_photo_id, '_wp_attachment_image_alt', true ) : '';
				$itoi_why_first_video     = $itoi_why_first['video'] ?? null;
				$itoi_why_first_video_url = ! empty( $itoi_why_first_video['url'] ) ? $itoi_why_first_video['url'] : '';
				?>
				<?php if ( $itoi_why_first_video_url ) : ?>
					<!-- autoplay attribute is paused by JS on load for a reduce-motion
					     visitor (initWhyChooseTabs() in main.js), same convention as
					     #heroBgVideo — the poster (or a static frame) shows instead. -->
					<video id="whyRightImg" class="absolute inset-0 h-full w-full object-cover" autoplay muted loop playsinline <?php echo $itoi_why_first_url ? 'poster="' . esc_url( $itoi_why_first_url ) . '"' : ''; ?>>
						<source src="<?php echo esc_url( $itoi_why_first_video_url ); ?>">
					</video>
				<?php elseif ( $itoi_why_first_url ) : ?>
					<img src="<?php echo esc_url( $itoi_why_first_url ); ?>" alt="<?php echo esc_attr( $itoi_why_first_alt ?: ( $itoi_why_first['title'] ?? '' ) ); ?>" id="whyRightImg" class="absolute inset-0 h-full w-full object-cover">
				<?php else : ?>
					<span id="whyRightImg">Photo — <?php echo esc_html( $itoi_why_first['tab_label'] ?? '' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
