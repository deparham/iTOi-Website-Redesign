<?php
/**
 * Door Reveal — scroll-scrubbed section that opens like a pair of doors.
 * Added 2026-09-01, in the homepage slot the Video Showcase used to hold
 * (that section is untouched, just toggled off — see its own docblock).
 *
 * Mechanic: the outer <section> is ~3 viewport-heights tall and the inner
 * .door-hero-pin is `position: sticky`, so the frame stays put while the
 * page scrolls past it. Scroll position through that tall container is
 * normalised to a single 0→1 progress value in assets/js/door-hero.js,
 * and every piece of the animation is a pure function of it — so it
 * scrubs backwards as readily as forwards and tracks how fast the user
 * actually scrolls, rather than firing a fixed-length timed transition on
 * a trigger.
 *
 * Content lives on the Home page's own edit screen (ACF group "Hero —
 * Door Reveal", acf-json/group_7c53f40c5e38.json) — deliberately NOT in
 * Site Settings ▸ (homepage) tabs where the other homepage sections keep
 * their content; that location was specified for this section explicitly.
 *
 * Progressive enhancement, both directions:
 *  - No JS / JS failed: the CSS default IS the finished open state (doors
 *    already slid off, everything at full opacity), so the content is
 *    readable rather than a screenful of invisible cards. door-hero.js
 *    adds .door-hero--scrub, and only that class switches on the tall
 *    scroll container, the sticky pin and the closed starting state.
 *  - prefers-reduced-motion: the class is never added, so the exact same
 *    open state stands and the section collapses to one normal-height
 *    block — no 3 viewport-heights of pinned dead scrolling to get past.
 *
 * The brackets are the only image assets; the navy panels and their dot
 * grid are CSS (see .door-hero-door, src/tailwind.css) so they stay sharp
 * at any viewport and any DPR. With no bracket uploaded, each door falls
 * back to a flat vector stand-in built from three divs rather than a
 * stretched placeholder image — same honest-placeholder discipline as the
 * rest of this theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_dr_page_id  = get_queried_object_id();
$itoi_dr_eyebrow  = get_field( 'door_eyebrow', $itoi_dr_page_id ) ?: 'Problems we solve';
$itoi_dr_headline = get_field( 'door_headline', $itoi_dr_page_id ) ?: "We take the\nguesswork out of it.";
$itoi_dr_left_id  = get_field( 'door_bracket_left', $itoi_dr_page_id );
$itoi_dr_right_id = get_field( 'door_bracket_right', $itoi_dr_page_id );
$itoi_dr_cards    = get_field( 'door_cards', $itoi_dr_page_id );

if ( empty( $itoi_dr_cards ) ) {
	// Same fallback discipline as every other homepage section: real,
	// on-brand copy so the section is never a broken empty frame before
	// anyone has opened the Home page in wp-admin. Mirrors this field's own
	// intended content, 6 rows — the count the 3-column grid is designed
	// around.
	$itoi_dr_cards = array(
		array(
			'card_question' => '“How many people walked past versus how many came in?”',
			'card_answer'   => 'Footfall and capture rate at every entrance, so you can tell a quiet day from a bad window display.',
		),
		array(
			'card_question' => '“Where do people go, and where do they stop?”',
			'card_answer'   => 'Journey paths, zone dwell and heat mapping from door to till, across every floor and every store.',
		),
		array(
			'card_question' => '“Did that campaign actually work?”',
			'card_answer'   => 'Traffic, demographic mix and conversion measured before, during and after — by site, not by gut feel.',
		),
		array(
			'card_question' => '“Why are we losing people at the queue?”',
			'card_answer'   => 'Live queue length and wait-time alerts that open a register before the customer walks out.',
		),
		array(
			'card_question' => '“What’s leaving without being paid for?”',
			'card_answer'   => 'Loss prevention and exception reporting that ties video to transactions and flags the pattern, not just the incident.',
		),
		array(
			'card_question' => '“Are we staffed for the peak or the average?”',
			'card_answer'   => 'Hour-by-hour demand profiles per site, so rosters match the traffic instead of the timetable.',
		),
	);
}

// 4 cards read best as 2×2; 5 and 6 as three across. Driven by a custom
// property rather than a class-per-count so the grid handles any of the
// repeater's allowed 4-6 with one rule and no template change.
$itoi_dr_count = count( $itoi_dr_cards );
$itoi_dr_cols  = 4 === $itoi_dr_count ? 2 : 3;

/**
 * One door. $side is 'left'|'right'; $image_id is that side's bracket, 0 or
 * empty for the vector stand-in. Kept local to this template — it exists
 * only to avoid writing the same 20 lines twice, mirrored.
 */
function itoi_door_reveal_door( $side, $image_id ) {
	?>
	<div class="door-hero-door door-hero-door--<?php echo esc_attr( $side ); ?>" aria-hidden="true">
		<?php if ( $image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'large',
				false,
				array(
					'class'   => 'door-hero-bracket',
					'alt'     => '',
					'loading' => 'eager',
				)
			);
			?>
		<?php else : ?>
			<span class="door-hero-bracket door-hero-bracket--vector">
				<span class="door-hero-bracket-dot"></span>
				<span class="door-hero-bracket-shape">
					<i class="door-hero-bracket-top"></i>
					<i class="door-hero-bracket-stem"></i>
					<i class="door-hero-bracket-bot"></i>
				</span>
			</span>
		<?php endif; ?>
	</div>
	<?php
}
?>
<section class="door-hero" id="doorHero">
	<div class="door-hero-pin">

		<?php // Real content, in normal document order and never display-toggled —
		// only opacity/transform ever change, so it stays in the accessibility
		// tree and stays tabbable at every point of the animation. Tabbing into
		// it also forces the open state outright (see door-hero.js), so a
		// keyboard user never has to scroll an animation to read it. ?>
		<div class="door-hero-behind">
			<div class="door-hero-panel">
				<div class="door-hero-head" id="doorHeroHead">
					<p class="door-hero-eyebrow"><?php echo esc_html( $itoi_dr_eyebrow ); ?></p>
					<h2 class="door-hero-headline"><?php echo nl2br( esc_html( $itoi_dr_headline ) ); ?></h2>
				</div>

				<div class="door-hero-grid" style="--door-cols:<?php echo (int) $itoi_dr_cols; ?>">
					<?php foreach ( $itoi_dr_cards as $itoi_dr_card ) : ?>
						<?php if ( empty( $itoi_dr_card['card_question'] ) ) { continue; } ?>
						<article class="door-hero-card">
							<h3 class="door-hero-card-question"><?php echo esc_html( $itoi_dr_card['card_question'] ); ?></h3>
							<?php if ( ! empty( $itoi_dr_card['card_answer'] ) ) : ?>
								<?php // max-[860px]:sr-only, not display:none — visually gone on phones
								// so the grid still fits one screen (the spec's reason for hiding it),
								// but still in the accessibility tree, so a screen-reader user on a
								// phone doesn't silently lose half of every card. ?>
								<p class="door-hero-card-answer max-[860px]:sr-only"><?php echo esc_html( $itoi_dr_card['card_answer'] ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<?php itoi_door_reveal_door( 'left', $itoi_dr_left_id ); ?>
		<?php itoi_door_reveal_door( 'right', $itoi_dr_right_id ); ?>

		<?php // Decorative: the real site identity is already in the header, so this
		// is a duplicate for the closed-doors state only — aria-hidden rather than
		// read out a second time. ?>
		<div class="door-hero-lockup" id="doorHeroLockup" aria-hidden="true">
			<b>i <em>to</em> i solutions</b>
			<span><strong>image</strong> to intelligence</span>
		</div>
		<div class="door-hero-hint" id="doorHeroHint" aria-hidden="true">Scroll to open</div>
	</div>
</section>
