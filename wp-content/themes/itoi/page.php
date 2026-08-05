<?php
/**
 * Generic page template — fallback for any WP Page without a dedicated
 * page-{slug}.php (PROJECT.md §5: privacy/terms/cookies use this one).
 * Renders the page_builder flexible content (§4) after the standard
 * title/content, so editors can append sections without needing a
 * page-builder plugin — currently empty on every page that uses this
 * template, so that loop is a real no-op, not a broken block.
 *
 * A draft page (the three legal pages are intentionally kept as drafts
 * pending legal review, per this phase's brief) gets a visible banner
 * when previewed by an editor — a generic "you're viewing a draft"
 * signal, not hardcoded to specific legal-page slugs.
 *
 * `page_builder` (§4) is registered on every page but has zero rows
 * populated anywhere in this install right now — rendering its 10
 * layout types is explicitly deferred by the field group's own
 * description ("later-phase work"), so it's left unrendered here
 * rather than wired to template parts that would go untested and
 * unused. Revisit once a real page actually uses it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$itoi_is_draft = 'draft' === get_post_status();
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px]">
		<div class="mx-auto max-w-[840px]">
			<?php if ( $itoi_is_draft ) : ?>
				<div class="mb-6 rounded-xl border-2 border-signature-dim bg-white px-5 py-3.5 text-[13.5px] font-bold text-ink">
					Draft &mdash; pending legal review. Not visible to site visitors.
				</div>
			<?php endif; ?>
			<h1 class="max-w-[24ch]"><?php the_title(); ?></h1>
		</div>
	</section>

	<section class="px-8 py-section-md">
		<div class="mx-auto max-w-[720px]">
			<h2 class="mb-6 text-2xl sr-only">Details</h2>
			<div class="prose text-[16px] leading-[1.7] [&_p]:mb-4 [&_strong]:font-bold">
				<?php the_content(); ?>
			</div>
		</div>
	</section>

<?php
endwhile;

get_footer();
