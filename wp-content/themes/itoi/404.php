<?php
/**
 * 404 template (PROJECT.md §5).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// 2026-08-06 (wp-admin content audit): heading/description/button labels
// were hardcoded — now Site Settings fields, with the exact previous copy
// as fallback so an unpopulated field changes nothing. Button destinations
// (home / contact) are structural navigation, not copy, and stay hardcoded.
$itoi_404_heading            = get_field( 'error_404_heading', 'option' ) ?: 'Page not found';
$itoi_404_description        = get_field( 'error_404_description', 'option' ) ?: "The page you're looking for doesn't exist or may have moved.";
$itoi_404_button_primary     = get_field( 'error_404_button_primary_label', 'option' ) ?: 'Back to home';
$itoi_404_button_secondary   = get_field( 'error_404_button_secondary_label', 'option' ) ?: 'Contact us';
?>

<section class="px-8 pt-[168px] pb-20 min-[640px]:pt-[206px] min-[980px]:pb-[110px]">
	<div class="mx-auto max-w-[560px] text-center">
		<div class="mb-3 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">404</div>
		<h1 class="mb-4"><?php echo esc_html( $itoi_404_heading ); ?></h1>
		<p class="mb-8 text-[16px] text-text-muted"><?php echo esc_html( $itoi_404_description ); ?></p>
		<div class="flex flex-wrap items-center justify-center gap-2.5">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover"><?php echo esc_html( $itoi_404_button_primary ); ?></a>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full border-[1.5px] border-ink bg-white px-[22px] py-[11px] text-sm font-bold hover:bg-hero-bg"><?php echo esc_html( $itoi_404_button_secondary ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
