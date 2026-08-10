<?php
/**
 * Fallback template (required by WordPress for a theme to be valid).
 * Real archive/single templates arrive in later phases per PROJECT.md §5.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="min-h-screen bg-bg px-6 pt-[168px] pb-16 min-[640px]:pt-[206px]">
	<div class="mx-auto max-w-3xl">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<h1 class="font-sans text-2xl font-extrabold text-ink"><?php the_title(); ?></h1>
				<div class="mt-4 text-text"><?php the_content(); ?></div>
			<?php endwhile; ?>
		<?php else : ?>
			<p class="text-text"><?php esc_html_e( 'Nothing found.', 'itoi' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
