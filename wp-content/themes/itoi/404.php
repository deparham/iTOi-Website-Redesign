<?php
/**
 * 404 template (PROJECT.md §5).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="px-8 pt-[168px] pb-20 min-[640px]:pt-[206px] min-[980px]:pb-[110px]">
	<div class="mx-auto max-w-[560px] text-center">
		<div class="mb-3 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">404</div>
		<h1 class="mb-4">Page not found</h1>
		<p class="mb-8 text-[16px] text-text-muted">The page you're looking for doesn't exist or may have moved.</p>
		<div class="flex flex-wrap items-center justify-center gap-2.5">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover">Back to home</a>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full border-[1.5px] border-ink bg-white px-[22px] py-[11px] text-sm font-bold hover:bg-hero-bg">Contact us</a>
		</div>
	</div>
</section>

<?php
get_footer();
