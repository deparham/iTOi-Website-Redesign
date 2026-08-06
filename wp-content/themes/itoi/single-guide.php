<?php
/**
 * Single guide detail (/education/guides/{slug}/). Empty related_solution
 * renders no CTA card (real empty state, not a broken block) — same
 * convention as single-solution.php's FAQ/related sections.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$itoi_id            = get_the_ID();
	$itoi_title         = get_field( 'title' ) ?: get_the_title();
	$itoi_dek           = get_field( 'dek' );
	$itoi_body          = get_field( 'body' );
	$itoi_industry_id   = get_field( 'industry' );
	$itoi_industry_name = $itoi_industry_id ? ( get_field( 'name', $itoi_industry_id ) ?: get_the_title( $itoi_industry_id ) ) : 'General';
	$itoi_read_time     = get_field( 'read_time_minutes' );
	$itoi_pub_date      = get_field( 'published_date' );
	$itoi_solution_id   = get_field( 'related_solution' );
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
		<div class="mx-auto max-w-[760px]">
			<div class="mb-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">
				<a href="<?php echo esc_url( home_url( '/education/guides/' ) ); ?>" class="hover:underline">Guides</a>
				<span aria-hidden="true">/</span>
				<span><?php echo esc_html( $itoi_industry_name ); ?></span>
			</div>
			<h1 class="max-w-[24ch]"><?php echo esc_html( $itoi_title ); ?></h1>
			<?php if ( $itoi_dek ) : ?>
				<p class="mt-4 max-w-[56ch] text-[17px] text-text-muted"><?php echo esc_html( $itoi_dek ); ?></p>
			<?php endif; ?>
			<div class="mt-5 flex flex-wrap items-center gap-3 text-[13px] text-text-muted">
				<?php if ( $itoi_pub_date ) : ?>
					<span><?php echo esc_html( gmdate( 'j F Y', strtotime( $itoi_pub_date ) ) ); ?></span>
					<span aria-hidden="true">&middot;</span>
				<?php endif; ?>
				<?php if ( $itoi_read_time ) : ?>
					<span><?php echo (int) $itoi_read_time; ?> min read</span>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( $itoi_body ) : ?>
		<section class="px-8 py-16 min-[980px]:py-[70px]">
			<div class="prose mx-auto max-w-[720px] text-[16px] leading-[1.7] [&_h2]:mt-10 [&_h2]:text-2xl [&_h2:first-child]:mt-0 [&_h3]:mt-6 [&_h3]:text-lg [&_p]:mb-4 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_li]:mb-1.5 [&_strong]:font-bold [&_a]:font-semibold [&_a]:text-ink [&_a]:underline [&_a]:underline-offset-4">
				<?php echo wp_kses_post( $itoi_body ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $itoi_solution_id && 'publish' === get_post_status( $itoi_solution_id ) ) : ?>
		<section class="border-t border-line bg-hero-bg px-8 py-16 min-[980px]:py-[70px]">
			<div class="mx-auto flex max-w-[720px] flex-wrap items-center justify-between gap-6 rounded-2xl border border-line bg-white p-7">
				<div>
					<div class="mb-1 text-[12.5px] font-bold uppercase tracking-wide text-text-muted">Related solution</div>
					<h2 class="text-xl"><?php echo esc_html( get_field( 'headline', $itoi_solution_id ) ?: get_the_title( $itoi_solution_id ) ); ?></h2>
				</div>
				<a href="<?php echo esc_url( get_permalink( $itoi_solution_id ) ); ?>" class="whitespace-nowrap rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover">View solution &rarr;</a>
			</div>
		</section>
	<?php endif; ?>

	<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
		<div>
			<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Keep exploring</h2>
			<p class="m-0 max-w-[34ch] text-white/60">More guides, the full glossary, and answers to common questions.</p>
		</div>
		<a href="<?php echo esc_url( home_url( '/education/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Education Hub</a>
	</div>

	<?php
endwhile;

get_footer();
