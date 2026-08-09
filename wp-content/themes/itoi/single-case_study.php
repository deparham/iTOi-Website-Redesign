<?php
/**
 * Single case study — pulls every ACF Case Study field (PROJECT.md §4).
 * Metrics and pull_quote are frequently empty or explicitly TODO-tagged
 * this early (see NOTES.md) — both render as real empty states:
 * - A metric row is skipped if its value is blank or contains "TODO"
 *   (PROJECT.md §6: tag unsourced figures TODO(metric) rather than
 *   publish invented numbers — that tag must never leak to visitors).
 * - The whole Metrics section is omitted if no row survives that filter.
 * - The quote block only prints when pull_quote itself is non-empty;
 *   attribution is optional within that and checked independently of
 *   ACF's own conditional_logic, since templates don't enforce it.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$itoi_client         = get_field( 'client_name' );
	$itoi_headline       = get_field( 'headline' ) ?: get_the_title();
	$itoi_title          = $itoi_client ?: $itoi_headline;
	$itoi_narrative      = get_field( 'narrative' );
	$itoi_metrics_raw    = get_field( 'metrics' );
	$itoi_pull_quote     = get_field( 'pull_quote' );
	$itoi_attribution    = get_field( 'quote_attribution' );
	$itoi_hero_id        = get_field( 'hero_image' );
	$itoi_hero           = $itoi_hero_id ? wp_get_attachment_image_url( $itoi_hero_id, 'large' ) : '';
	$itoi_hero_video     = get_field( 'hero_video' );
	$itoi_hero_video_url = ! empty( $itoi_hero_video['url'] ) ? $itoi_hero_video['url'] : '';
	// Prefer real Media Library alt text; fall back to a descriptive phrase
	// rather than repeating the H1 (which is just $itoi_title) verbatim.
	$itoi_hero_alt = $itoi_hero_id ? ( get_post_meta( $itoi_hero_id, '_wp_attachment_image_alt', true ) ?: $itoi_title . ' case study photo' ) : '';
	// TEMPORARY: while hero_image is a stock stand-in (site-wide stock
	// sourcing pass, see NOTES.md), the page must visibly disclose that —
	// not just via an admin-only note — so nobody mistakes it for a real
	// photo of this client's site. Editors uncheck the field once
	// hero_image is swapped for real photography, and this disappears.
	$itoi_hero_is_stock = (bool) get_field( 'hero_image_is_stock' );
	$itoi_gallery_ids   = get_field( 'gallery' );
	$itoi_industry_ids  = get_field( 'industry' );
	$itoi_solution_ids  = get_field( 'related_solution' );

	$itoi_industry_id = ! empty( $itoi_industry_ids ) ? $itoi_industry_ids[0] : null;
	$itoi_solution_id = ! empty( $itoi_solution_ids ) ? $itoi_solution_ids[0] : null;

	// Filter out blank or TODO-tagged metric rows before deciding whether
	// to render the section at all.
	$itoi_metrics = array();
	if ( ! empty( $itoi_metrics_raw ) ) {
		foreach ( $itoi_metrics_raw as $itoi_row ) {
			$itoi_value = trim( (string) ( $itoi_row['value'] ?? '' ) );
			if ( '' === $itoi_value || false !== stripos( $itoi_value, 'todo' ) ) {
				continue;
			}
			$itoi_metrics[] = $itoi_row;
		}
	}
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px]">
		<div class="mx-auto grid max-w-[1280px] gap-10 min-[980px]:grid-cols-[1.1fr_1fr] min-[980px]:items-center">
			<div>
				<?php if ( $itoi_industry_id && 'publish' === get_post_status( $itoi_industry_id ) ) : ?>
					<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( get_the_title( $itoi_industry_id ) ); ?></div>
				<?php endif; ?>
				<h1 class="max-w-[18ch]"><?php echo esc_html( $itoi_title ); ?></h1>
				<?php if ( $itoi_client && $itoi_headline && $itoi_headline !== $itoi_client ) : ?>
					<p class="mt-4 max-w-[46ch] text-[17px] text-text-muted"><?php echo esc_html( $itoi_headline ); ?></p>
				<?php endif; ?>
				<div class="mt-7 flex flex-wrap gap-2.5">
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover">Get demo</a>
					<a href="<?php echo esc_url( home_url( '/case-studies/' ) ); ?>" class="rounded-full border-[1.5px] border-ink bg-white px-[22px] py-[11px] text-sm font-bold hover:bg-hero-bg">All case studies</a>
				</div>
			</div>
			<div class="case-study-hero-glass relative aspect-[4/3] w-full overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
				<?php
				$itoi_hero_media = itoi_media_cover( $itoi_hero, $itoi_hero_video, $itoi_hero_alt, 'absolute inset-0 h-full w-full object-cover' );
				?>
				<?php if ( $itoi_hero_media ) : ?>
					<?php echo $itoi_hero_media; ?>
					<?php // Stock-photo disclaimer only applies to the placeholder photo, never to an uploaded video. ?>
					<?php if ( $itoi_hero_is_stock && ! $itoi_hero_video_url ) : ?>
						<!-- Mandatory honesty safeguard (PROJECT.md) — glass treatment
							must not reduce legibility vs. the plain bg-black/70 bar it
							replaces. Kept full-width (an already-established "unobtrusive"
							caption placement, per the wave-3 brief's own "or similar"
							allowance) rather than shrunk to a small corner pill, so
							coverage/visibility isn't traded away for the glass look —
							the icon + bold weight + defined top border add distinctness
							on top of that, not instead of it. See NOTES.md. -->
						<div class="disclaimer-glass absolute inset-x-0 bottom-0 z-[2] flex items-center justify-center gap-2 px-4 py-3 text-center text-[12.5px] font-bold leading-snug">
							<svg class="h-4 w-4 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
							<span>Representative image &mdash; not an actual photo of <?php echo esc_html( $itoi_title ); ?>&rsquo;s site</span>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_title ); ?> (TODO)</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( $itoi_narrative ) : ?>
		<section class="px-8 py-section-md">
			<div class="prose mx-auto max-w-[720px] text-[16px] leading-[1.7] [&_p]:mb-4 [&_strong]:font-bold">
				<?php echo wp_kses_post( $itoi_narrative ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $itoi_metrics ) ) : ?>
		<section class="border-t border-line bg-hero-bg px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-8 text-2xl">Results</h2>
				<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
					<?php foreach ( $itoi_metrics as $itoi_metric_index => $itoi_row ) : ?>
						<div class="rounded-xl border border-line bg-white px-6 py-7 text-center <?php echo esc_attr( itoi_reveal_class() ); ?>" style="--reveal-radius:12px">
							<?php itoi_reveal_markup( $itoi_metric_index ); ?>
							<div class="text-[32px] font-extrabold text-teal-800"><?php echo esc_html( $itoi_row['value'] ); ?></div>
							<?php if ( ! empty( $itoi_row['label'] ) ) : ?>
								<div class="mt-1.5 text-[13.5px] text-text-muted"><?php echo esc_html( $itoi_row['label'] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $itoi_pull_quote ) : ?>
		<section class="px-8 py-section-md">
			<blockquote class="mx-auto max-w-[720px] text-center">
				<p class="text-[22px] font-semibold leading-[1.5]">&ldquo;<?php echo esc_html( $itoi_pull_quote ); ?>&rdquo;</p>
				<?php if ( $itoi_attribution ) : ?>
					<footer class="mt-4 text-[13.5px] font-bold uppercase tracking-wide text-text-muted"><?php echo esc_html( $itoi_attribution ); ?></footer>
				<?php endif; ?>
			</blockquote>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $itoi_gallery_ids ) ) : ?>
		<section class="border-t border-line bg-hero-bg px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-8 text-2xl">Gallery</h2>
				<div class="grid grid-cols-1 gap-4 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
					<?php
					$itoi_gallery_index = 0;
					foreach ( $itoi_gallery_ids as $itoi_img_id ) :
						$itoi_img_url = wp_get_attachment_image_url( $itoi_img_id, 'large' );
						if ( ! $itoi_img_url ) {
							continue;
						}
						++$itoi_gallery_index;
						// Prefer real Media Library alt text per photo; fall back to a
						// numbered, non-duplicate description rather than repeating the
						// same title on every gallery image.
						$itoi_img_alt = get_post_meta( $itoi_img_id, '_wp_attachment_image_alt', true ) ?: ( $itoi_title . ' — gallery photo ' . $itoi_gallery_index );
						?>
						<div class="aspect-[4/3] overflow-hidden rounded-xl">
							<?php
							echo wp_get_attachment_image(
								$itoi_img_id,
								'large',
								false,
								array(
									'class' => 'h-full w-full object-cover',
									'alt'   => $itoi_img_alt,
								)
							);
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $itoi_solution_id && 'publish' === get_post_status( $itoi_solution_id ) ) : ?>
		<section class="border-t border-line px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<h2 class="mb-6 text-2xl">Solution used</h2>
				<a href="<?php echo esc_url( get_permalink( $itoi_solution_id ) ); ?>" class="rounded-full border border-line bg-white px-5 py-2.5 text-[13.5px] font-semibold hover:border-ink"><?php echo esc_html( get_the_title( $itoi_solution_id ) ); ?></a>
			</div>
		</section>
	<?php endif; ?>

	<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
		<div>
			<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Ready to see it for your team?</h2>
			<p class="m-0 max-w-[34ch] text-white/60">Talk to us about a pilot for your own site.</p>
		</div>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Contact us</a>
	</div>

	<?php
endwhile;

get_footer();
