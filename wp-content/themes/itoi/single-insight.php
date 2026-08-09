<?php
/**
 * Single insight — standard WP post fields (title/editor/thumbnail) plus
 * ACF dek + author (PROJECT.md §4). Author is a `team_member` relationship;
 * team_member has no public single template yet (out of Phase 7 scope),
 * so the byline renders as plain name/photo, not a link.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$itoi_dek          = get_field( 'dek' );
	$itoi_author_id    = get_field( 'author' );
	$itoi_author_id    = ! empty( $itoi_author_id ) ? $itoi_author_id[0] : null;
	$itoi_author       = $itoi_author_id ? get_field( 'name', $itoi_author_id ) : '';
	$itoi_author_role  = $itoi_author_id ? get_field( 'role', $itoi_author_id ) : '';
	$itoi_photo_id     = $itoi_author_id ? get_field( 'photo', $itoi_author_id ) : null;
	$itoi_photo        = $itoi_photo_id ? wp_get_attachment_image_url( $itoi_photo_id, 'thumbnail' ) : '';
	$itoi_video        = $itoi_author_id ? get_field( 'video', $itoi_author_id ) : null;
	$itoi_author_media = itoi_media_cover( $itoi_photo, $itoi_video, $itoi_author, 'h-10 w-10 rounded-full object-cover' );
	$itoi_hero         = get_the_post_thumbnail_url( get_the_ID(), 'large' );
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
		<div class="mx-auto max-w-[760px]">
			<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Insights</div>
			<h1 class="max-w-[22ch]"><?php the_title(); ?></h1>
			<?php if ( $itoi_dek ) : ?>
				<p class="mt-4 text-[17px] text-text-muted"><?php echo esc_html( $itoi_dek ); ?></p>
			<?php endif; ?>
			<div class="mt-6 flex items-center gap-3">
				<?php if ( $itoi_author_media ) : ?>
					<?php echo $itoi_author_media; // phpcs:ignore -- itoi_media_cover() already escapes. ?>
				<?php endif; ?>
				<div class="text-[13.5px]">
					<?php if ( $itoi_author ) : ?>
						<div class="font-bold"><?php echo esc_html( $itoi_author ); ?><?php echo $itoi_author_role ? ', ' . esc_html( $itoi_author_role ) : ''; ?></div>
					<?php endif; ?>
					<div class="text-text-muted"><?php echo esc_html( get_the_date() ); ?></div>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $itoi_hero ) : ?>
		<section class="px-8 pt-16 min-[980px]:pt-[70px]">
			<div class="relative mx-auto aspect-[16/9] max-w-[900px] overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)]">
				<?php
				echo get_the_post_thumbnail(
					get_the_ID(),
					'large',
					array(
						'class' => 'absolute inset-0 h-full w-full object-cover',
						'alt'   => get_the_title_attribute( array( 'echo' => false ) ),
					)
				);
				?>
			</div>
		</section>
	<?php endif; ?>

	<section class="px-8 py-16 min-[980px]:py-[70px]">
		<div class="prose mx-auto max-w-[720px] text-[16px] leading-[1.7] [&_p]:mb-4 [&_strong]:font-bold">
			<?php the_content(); ?>
		</div>
	</section>

	<section class="border-t border-line bg-hero-bg px-8 py-16 min-[980px]:py-[70px]">
		<div class="mx-auto max-w-[720px]">
			<h2 class="mb-6 text-2xl">Continue reading</h2>
			<a href="<?php echo esc_url( home_url( '/insights/' ) ); ?>" class="rounded-full border-[1.5px] border-ink bg-white px-[22px] py-[11px] text-sm font-bold hover:bg-hero-bg">All insights</a>
		</div>
	</section>

	<?php
endwhile;

get_footer();
