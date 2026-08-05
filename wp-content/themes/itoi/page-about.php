<?php
/**
 * About page (PROJECT.md §5). Real narrative already lives in the page's
 * own post_content (Phase 5) — this template renders it as-is, plus a
 * bottom CTA band that also guarantees a real h2 in the page regardless
 * of how post_content is edited (the Phase 7 heading-order lesson).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px]">
		<div class="mx-auto max-w-[840px]">
			<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">About</div>
			<h1 class="max-w-[20ch]"><?php the_title(); ?></h1>
		</div>
	</section>

	<?php
	// "Partners, not vendors" trust section — flip-card grid, real content
	// from the client's capability deck (PROJECT.md/NOTES.md). Placed here,
	// About page over the homepage — see NOTES.md for the placement
	// rationale. ACF: Partners, Not Vendors options page.
	$itoi_partners_headline = get_field( 'partners_headline', 'option' ) ?: 'Partners, not vendors.';
	$itoi_partners_intro    = get_field( 'partners_intro', 'option' );
	$itoi_partners_cards    = get_field( 'partners_cards', 'option' );
	?>
	<?php if ( ! empty( $itoi_partners_cards ) ) : ?>
		<section class="aurora-bg border-b border-line bg-teal-900 px-8 py-section-md min-[980px]:scroll-mt-[88px]" id="partners-not-vendors">
			<div class="mx-auto max-w-[1080px]">
				<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
					<h2 class="mb-4 max-w-[20ch] text-[clamp(26px,3vw,38px)] text-white"><?php echo esc_html( $itoi_partners_headline ); ?></h2>
				</div>
				<?php if ( $itoi_partners_intro ) : ?>
					<p class="mb-10 max-w-[80ch] text-[16px] leading-[1.7] text-white/80"><?php echo esc_html( $itoi_partners_intro ); ?></p>
				<?php endif; ?>

				<div class="grid grid-cols-1 gap-5 min-[640px]:grid-cols-2">
					<?php foreach ( $itoi_partners_cards as $itoi_pc ) : ?>
						<div class="flip-card aspect-[4/3] min-[640px]:aspect-[16/10]">
							<div class="flip-card-inner">
								<div class="flip-card-front flex flex-col items-center justify-center gap-3 rounded-2xl border border-line bg-white px-6 py-8 text-center">
									<h3 class="max-w-[18ch] text-[20px]"><?php echo esc_html( $itoi_pc['front_title'] ); ?></h3>
									<span class="flip-card-hint text-[11px] font-bold uppercase tracking-wide text-text-muted" aria-hidden="true">Hover / tap to flip</span>
								</div>
								<div class="partners-flip-back-glass flip-card-back flex items-center justify-center rounded-2xl px-6 py-8 text-center">
									<p class="m-0 max-w-[34ch] text-[15px] leading-[1.6] text-white"><?php echo esc_html( $itoi_pc['back_description'] ); ?></p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="px-8 py-section-md">
		<div class="prose mx-auto max-w-[720px] text-[16px] leading-[1.7] [&_h2]:mt-14 [&_h2]:border-t [&_h2]:border-line [&_h2]:pt-10 [&_h2]:text-2xl [&_h2:first-child]:mt-0 [&_h2:first-child]:border-0 [&_h2:first-child]:pt-0 [&_h3]:mt-6 [&_p]:mb-4 [&_strong]:font-bold">
			<?php the_content(); ?>
		</div>
	</section>

	<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
		<div>
			<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Meet the team behind ITOI</h2>
			<p class="m-0 max-w-[34ch] text-white/60">See who's building and supporting the platform.</p>
		</div>
		<a href="<?php echo esc_url( home_url( '/team/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Meet the team</a>
	</div>

<?php
endwhile;

get_footer();
